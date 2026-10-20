<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\StripeEvent;
use App\Services\OrderService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeService
{
    public function __construct(private OrderService $orders) {}

    private function client(): StripeClient
    {
        return new StripeClient(config('vyora.stripe.secret'));
    }

    /** Create a hosted Stripe Checkout Session. Amounts come from the saved order, never from the browser. */
    public function createCheckoutSession(Order $order): string
    {
        $lineItems = $order->items->map(fn ($i) => [
            'quantity' => $i->quantity,
            'price_data' => [
                'currency' => strtolower($order->currency),
                'unit_amount' => $i->unit_price_cents,
                'product_data' => ['name' => $i->name.($i->color || $i->size ? ' ('.trim($i->color.' '.$i->size).')' : '')],
            ],
        ])->all();

        // Discounts, shipping and tax are folded into explicit lines so Stripe's total equals ours exactly.
        if ($order->shipping_cents > 0) {
            $lineItems[] = ['quantity' => 1, 'price_data' => ['currency' => strtolower($order->currency), 'unit_amount' => $order->shipping_cents, 'product_data' => ['name' => 'Shipping']]];
        }
        if ($order->tax_cents > 0) {
            $lineItems[] = ['quantity' => 1, 'price_data' => ['currency' => strtolower($order->currency), 'unit_amount' => $order->tax_cents, 'product_data' => ['name' => 'Tax']]];
        }
        $params = [
            'mode' => 'payment',
            'client_reference_id' => $order->number,
            'customer_email' => $order->email,
            'metadata' => ['order_number' => $order->number],
            'payment_intent_data' => ['metadata' => ['order_number' => $order->number]],
            'line_items' => $lineItems,
            'success_url' => route('checkout.return', ['order' => $order->number]).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancelled', ['order' => $order->number]),
            'expires_at' => now()->addMinutes(35)->timestamp,
        ];
        if ($order->discount_cents > 0) {
            $coupon = $this->client()->coupons->create([
                'amount_off' => $order->discount_cents,
                'currency' => strtolower($order->currency),
                'duration' => 'once',
                'name' => 'Discount '.$order->coupon_code,
                'max_redemptions' => 1,
            ]);
            $params['discounts'] = [['coupon' => $coupon->id]];
        }

        $session = $this->client()->checkout->sessions->create($params, ['idempotency_key' => 'order-'.$order->number]);
        $order->payments()->update(['provider_reference' => $session->id]);

        return $session->url;
    }

    /**
     * Verify the signature, then process each event at most once.
     *
     * @throws \Stripe\Exception\SignatureVerificationException|\UnexpectedValueException
     */
    public function handleWebhook(string $payload, ?string $signature): string
    {
        $event = Webhook::constructEvent($payload, (string) $signature, (string) config('vyora.stripe.webhook_secret'));

        try {
            $record = StripeEvent::create(['event_id' => $event->id, 'type' => $event->type]);
        } catch (UniqueConstraintViolationException) {
            return 'duplicate';
        }

        try {
            $this->dispatch($event);
            $record->update(['processed_at' => now()]);
        } catch (\Throwable $e) {
            // Let Stripe retry: remove the marker so the retry is processed.
            $record->delete();
            Log::error('Stripe webhook processing failed', ['event' => $event->id, 'error' => $e->getMessage()]);
            throw $e;
        }

        return 'processed';
    }

    private function dispatch(object $event): void
    {
        $object = $event->data->object;
        $order = $this->findOrder($object);
        if (! $order) {
            Log::warning('Stripe event for unknown order', ['event' => $event->id, 'type' => $event->type]);

            return;
        }

        match ($event->type) {
            'checkout.session.completed' => $object->payment_status === 'paid'
                ? $this->orders->markPaid($order, $object->payment_intent ?? null, $object->id)
                : null, // async method still pending — wait for async_payment_* events
            'checkout.session.async_payment_succeeded' => $this->orders->markPaid($order, $object->payment_intent ?? null, $object->id),
            'checkout.session.async_payment_failed' => $this->orders->markPaymentFailed($order, 'Payment failed.'),
            'checkout.session.expired' => $this->orders->markPaymentFailed($order, 'Checkout session expired.'),
            // A failed card attempt is not final inside Checkout (the customer can retry), so only note it.
            'payment_intent.payment_failed' => $order->payments()->update(['failure_reason' => $object->last_payment_error->message ?? 'Payment attempt failed.']),
            default => null,
        };
    }

    private function findOrder(object $object): ?Order
    {
        $number = $object->client_reference_id ?? ($object->metadata->order_number ?? null);

        return $number ? Order::where('number', $number)->first() : null;
    }
}
