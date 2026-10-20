<?php

namespace App\Services;

use App\Models\Address;
use App\Models\InventoryMovement;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(private PricingService $pricing, private OrderService $orders, private CartService $cart) {}

    /**
     * Create an order from the current cart. Every figure is recomputed from the database inside a transaction
     * while holding row locks on the variants, so concurrent buyers cannot oversell and the browser cannot
     * influence prices. The idempotency key prevents duplicate orders from double submits/refreshes.
     *
     * @param  array{email:string,address:array,shipping_method:string,coupon:?string,idempotency_key:string,payment_provider:string}  $data
     */
    public function placeOrder(array $data, ?User $user): Order
    {
        if ($existing = Order::where('idempotency_key', $data['idempotency_key'])->first()) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($data, $user) {
                $raw = $this->cart->raw();
                if (! $raw) {
                    throw new CheckoutException('Your cart is empty.');
                }

                $ids = array_keys($raw);
                sort($ids); // consistent lock order prevents deadlocks
                $variants = ProductVariant::with(['product.promotions'])->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                $lines = collect();
                foreach ($raw as $variantId => $qty) {
                    $variant = $variants->get($variantId);
                    if (! $variant || ! $variant->is_active || $variant->product?->status !== 'active' || $variant->product->trashed()) {
                        throw new CheckoutException('An item in your cart is no longer available. Please review your cart.');
                    }
                    if ($variant->stock < $qty) {
                        throw new CheckoutException("Not enough stock for {$variant->product->name} ({$variant->label()}). Only {$variant->stock} left.");
                    }
                    $lines->push(['variant' => $variant, 'quantity' => (int) $qty]);
                }

                $couponCode = $data['coupon'] ?? null;
                $totals = $this->pricing->totals($lines, $couponCode, $data['shipping_method']);
                if ($couponCode && ! $totals['coupon']) {
                    throw new CheckoutException($totals['coupon_error'] ?? 'Invalid coupon.');
                }
                if ($totals['coupon']) {
                    $coupon = Coupon::whereKey($totals['coupon']->id)->lockForUpdate()->first();
                    if (! $coupon->isCurrentlyValid()) {
                        throw new CheckoutException('That coupon is no longer available.');
                    }
                    $coupon->increment('used_count');
                }

                [$from, $to] = $this->pricing->deliveryWindow($data['shipping_method']);
                $provider = $data['payment_provider'];

                $order = Order::create([
                    'number' => $this->nextNumber(),
                    'idempotency_key' => $data['idempotency_key'],
                    'user_id' => $user?->id,
                    'email' => $data['email'],
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'payment_provider' => $provider,
                    'is_demo' => $provider === 'demo',
                    'currency' => config('vyora.currency'),
                    'subtotal_cents' => $totals['subtotal_cents'],
                    'discount_cents' => $totals['discount_cents'],
                    'shipping_cents' => $totals['shipping_cents'],
                    'tax_cents' => $totals['tax_cents'],
                    'total_cents' => $totals['total_cents'],
                    'coupon_code' => $totals['coupon']?->code,
                    'shipping_method' => $data['shipping_method'],
                    'shipping_address' => $data['address'],
                    'estimated_delivery_from' => $from,
                    'estimated_delivery_to' => $to,
                    'placed_at' => now(),
                ]);

                foreach ($lines as $line) {
                    /** @var ProductVariant $variant */
                    $variant = $line['variant'];
                    $unit = $this->pricing->unitPriceCents($variant);
                    $product = $variant->product;
                    $image = $product->images()->first();
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'name' => $product->name,
                        'sku' => $variant->sku,
                        'color' => $variant->color,
                        'size' => $variant->size,
                        'short_description' => $product->short_description,
                        'image_url' => $image?->src,
                        'unit_price_cents' => $unit,
                        'quantity' => $line['quantity'],
                        'line_total_cents' => $unit * $line['quantity'],
                    ]);
                    InventoryMovement::apply($variant, -$line['quantity'], 'order', $order);
                }

                Payment::create([
                    'order_id' => $order->id,
                    'provider' => $provider,
                    'status' => 'pending',
                    'amount_cents' => $order->total_cents,
                    'currency' => $order->currency,
                    'is_demo' => $provider === 'demo',
                ]);
                $this->orders->record($order, 'pending', 'Order placed — awaiting payment.', $user?->id);

                return $order;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // A concurrent request with the same idempotency key won the race.
            return Order::where('idempotency_key', $data['idempotency_key'])->firstOrFail();
        }
    }

    private function nextNumber(): string
    {
        do {
            $number = 'VY-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (Order::where('number', $number)->exists());

        return $number;
    }

    public function snapshotAddress(Address|array $address): array
    {
        return $address instanceof Address ? $address->toSnapshot() : $address;
    }
}
