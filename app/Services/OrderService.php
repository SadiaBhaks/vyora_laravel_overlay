<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\InventoryMovement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public const FLOW = ['pending', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered'];

    public function record(Order $order, string $status, ?string $note = null, ?int $userId = null): void
    {
        OrderStatusHistory::create(['order_id' => $order->id, 'status' => $status, 'note' => $note, 'user_id' => $userId, 'created_at' => now()]);
    }

    /** Idempotent: a second call on an already-paid order is a no-op. */
    public function markPaid(Order $order, ?string $paymentIntent = null, ?string $reference = null): bool
    {
        $changed = DB::transaction(function () use ($order, $paymentIntent, $reference) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();
            if ($order->payment_status === 'paid') {
                return false;
            }
            if ($order->status === 'cancelled') {
                // Payment arrived after we released the stock. Flag it for manual attention rather than silently shipping.
                $this->record($order, 'cancelled', 'Payment received after cancellation — refund or reinstate manually.');

                return false;
            }
            $order->update(['payment_status' => 'paid', 'paid_at' => now(), 'status' => 'processing']);
            $payment = $order->payments()->first();
            $payment?->update(['status' => 'paid', 'payment_intent' => $paymentIntent ?? $payment->payment_intent]);
            $this->record($order, 'processing', $order->is_demo ? 'Demo payment confirmed (no real charge).' : 'Payment confirmed.');
            $order->items->each(fn ($i) => $i->product_id && Product::whereKey($i->product_id)->increment('sales_count', $i->quantity));

            return true;
        });

        if ($changed) {
            event(new \App\Events\OrderPaid($order->fresh()));
        }

        return $changed;
    }

    public function markPaymentFailed(Order $order, string $reason, bool $cancel = true): void
    {
        DB::transaction(function () use ($order, $reason, $cancel) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();
            if ($order->payment_status === 'paid') {
                return;
            }
            $order->payments()->update(['status' => $cancel ? 'cancelled' : 'failed', 'failure_reason' => $reason]);
            $order->update(['payment_status' => $cancel ? 'cancelled' : 'failed']);
            if ($cancel) {
                $this->cancel($order, $reason);
            }
        });
    }

    /** Cancel an order and return its stock exactly once. */
    public function cancel(Order $order, string $note = 'Order cancelled.', ?int $userId = null): void
    {
        DB::transaction(function () use ($order, $note, $userId) {
            $order = Order::with('items')->whereKey($order->id)->lockForUpdate()->first();
            if ($order->restocked_at || in_array($order->status, ['shipped', 'out_for_delivery', 'delivered'], true)) {
                return;
            }
            foreach ($order->items as $item) {
                if (! $item->product_variant_id) {
                    continue;
                }
                $variant = ProductVariant::whereKey($item->product_variant_id)->lockForUpdate()->first();
                if ($variant) {
                    InventoryMovement::apply($variant, $item->quantity, 'cancellation', $order, $userId, $note);
                }
            }
            $order->update(['status' => 'cancelled', 'restocked_at' => now(), 'payment_status' => $order->payment_status === 'paid' ? 'paid' : 'cancelled']);
            $this->record($order, 'cancelled', $note, $userId);
        });
    }

    public function advance(Order $order, string $status, ?int $userId = null, ?string $note = null, ?array $tracking = null): void
    {
        if (! in_array($status, self::FLOW, true)) {
            throw new \InvalidArgumentException('Unknown order status.');
        }
        if ($order->payment_status !== 'paid' && $status !== 'pending') {
            throw new \DomainException('Only paid orders can move through fulfilment.');
        }
        $attrs = ['status' => $status];
        if ($tracking) {
            $attrs += array_filter(['carrier' => $tracking['carrier'] ?? null, 'tracking_number' => $tracking['tracking_number'] ?? null]);
        }
        $order->update($attrs);
        $this->record($order, $status, $note, $userId);
    }

    /** Timeline built strictly from recorded history. Steps with no record are shown as not yet reached. */
    public function timeline(Order $order): array
    {
        $recorded = $order->histories->groupBy('status')->map(fn ($g) => $g->first()->created_at);
        $recorded->put('pending', $order->placed_at);

        return collect(Order::TIMELINE)->map(fn ($label, $key) => [
            'key' => $key,
            'label' => $label,
            'reached_at' => $recorded->get($key),
            'current' => $order->status === $key,
        ])->values()->all();
    }
}
