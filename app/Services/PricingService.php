<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Support\Money;
use Illuminate\Support\Collection;

/** Single source of truth for every price the shopper sees. All figures are integer cents from DB records. */
class PricingService
{
    public function unitPriceCents(ProductVariant $variant): int
    {
        $product = $variant->product;
        $base = Money::toCents($variant->price_override ?? $product->price);
        $pct = $product->activePromotion()?->discount_percent ?? 0;

        return (int) round($base * (100 - $pct) / 100);
    }

    public function validateCoupon(?string $code, int $subtotalCents): array
    {
        if (! $code) {
            return [null, null];
        }
        $coupon = Coupon::whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))])->first();
        if (! $coupon || ! $coupon->isCurrentlyValid()) {
            return [null, 'That coupon code is not valid or has expired.'];
        }
        if ($subtotalCents < Money::toCents($coupon->min_subtotal)) {
            return [null, 'Spend at least '.Money::format(Money::toCents($coupon->min_subtotal)).' to use this coupon.'];
        }

        return [$coupon, null];
    }

    public function couponDiscountCents(?Coupon $coupon, int $subtotalCents): int
    {
        if (! $coupon) {
            return 0;
        }
        $discount = $coupon->type === 'percent'
            ? (int) round($subtotalCents * ((float) $coupon->value) / 100)
            : Money::toCents($coupon->value);

        return min($discount, $subtotalCents);
    }

    public function shippingCents(string $method, int $subtotalAfterDiscountCents): int
    {
        $cfg = config("vyora.shipping_methods.$method");
        if (! $cfg) {
            throw new \InvalidArgumentException('Unknown shipping method.');
        }
        $threshold = Money::toCents(config('vyora.free_shipping_threshold'));
        if ($cfg['free_over_threshold'] && $threshold > 0 && $subtotalAfterDiscountCents >= $threshold) {
            return 0;
        }

        return Money::toCents($cfg['price']);
    }

    public function taxCents(int $taxableCents): int
    {
        return (int) round($taxableCents * (float) config('vyora.tax_rate'));
    }

    public function deliveryWindow(string $method): array
    {
        $cfg = config("vyora.shipping_methods.$method");
        $processing = (int) app(SettingsService::class)->get('processing_days');

        return [now()->addDays($processing + $cfg['days_min'])->toDateString(), now()->addDays($processing + $cfg['days_max'])->toDateString()];
    }

    /**
     * @param  Collection<int, array{variant: ProductVariant, quantity: int}>  $lines
     * @return array<string, mixed>
     */
    public function totals(Collection $lines, ?string $couponCode = null, string $shippingMethod = 'standard'): array
    {
        $subtotal = $lines->sum(fn ($l) => $this->unitPriceCents($l['variant']) * $l['quantity']);
        [$coupon, $couponError] = $this->validateCoupon($couponCode, $subtotal);
        $discount = $this->couponDiscountCents($coupon, $subtotal);
        $afterDiscount = $subtotal - $discount;
        $shipping = $lines->isEmpty() ? 0 : $this->shippingCents($shippingMethod, $afterDiscount);
        $tax = $this->taxCents($afterDiscount);
        $threshold = Money::toCents(config('vyora.free_shipping_threshold'));

        return [
            'subtotal_cents' => $subtotal,
            'discount_cents' => $discount,
            'shipping_cents' => $shipping,
            'tax_cents' => $tax,
            'total_cents' => $afterDiscount + $shipping + $tax,
            'coupon' => $coupon,
            'coupon_error' => $couponError,
            'free_shipping_remaining_cents' => $threshold > 0 ? max(0, $threshold - $afterDiscount) : 0,
            'free_shipping_threshold_cents' => $threshold,
        ];
    }
}
