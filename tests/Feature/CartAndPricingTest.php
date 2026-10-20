<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Promotion;
use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsStore;
use Tests\TestCase;

class CartAndPricingTest extends TestCase
{
    use RefreshDatabase, BuildsStore;

    public function test_cart_totals_use_integer_cents_tax_and_shipping(): void
    {
        $v = $this->makeProduct(['price' => 19.99]);
        $cart = app(CartService::class);
        $cart->add($v->id, 3);
        $s = $cart->summary();
        $this->assertSame(5997, $s['subtotal_cents']);
        $this->assertSame(600, $s['shipping_cents']);              // under the free-shipping threshold
        $this->assertSame((int) round(5997 * config('vyora.tax_rate')), $s['tax_cents']);
        $this->assertSame(5997 + 600 + $s['tax_cents'], $s['total_cents']);
    }

    public function test_free_shipping_over_threshold(): void
    {
        $v = $this->makeProduct(['price' => 120]);
        app(CartService::class)->add($v->id);
        $this->assertSame(0, app(CartService::class)->summary()['shipping_cents']);
    }

    public function test_quantity_is_clamped_to_stock_and_max(): void
    {
        $v = $this->makeProduct(stock: 3);
        $r = app(CartService::class)->add($v->id, 8);
        $this->assertSame(3, $r['quantity']);
        $out = $this->makeProduct(stock: 0);
        $this->assertFalse(app(CartService::class)->add($out->id)['ok']);
    }

    public function test_variant_price_override_applies(): void
    {
        $v = $this->makeProduct(['price' => 50]);
        $v->update(['price_override' => 65]);
        $this->assertSame(6500, app(PricingService::class)->unitPriceCents($v->fresh()->load('product')));
    }

    public function test_running_promotion_discounts_price_but_expired_does_not(): void
    {
        $v = $this->makeProduct(['price' => 100]);
        $promo = Promotion::create(['name' => 'P', 'slug' => 'p', 'discount_percent' => 20, 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'is_active' => true]);
        $promo->products()->attach($v->product_id);
        $this->assertSame(8000, app(PricingService::class)->unitPriceCents($v->fresh()->load('product.promotions')));
        $promo->update(['ends_at' => now()->subHour()]);
        $this->assertSame(10000, app(PricingService::class)->unitPriceCents($v->fresh()->load('product.promotions')));
    }

    public function test_coupon_rules(): void
    {
        $p = app(PricingService::class);
        Coupon::create(['code' => 'TEN', 'type' => 'percent', 'value' => 10, 'min_subtotal' => 40, 'is_active' => true]);
        [$ok] = $p->validateCoupon('ten', 5000);
        $this->assertNotNull($ok);
        $this->assertSame(500, $p->couponDiscountCents($ok, 5000));
        [$none, $err] = $p->validateCoupon('TEN', 3000);
        $this->assertNull($none);
        $this->assertStringContainsString('Spend at least', $err);
        $this->assertNull($p->validateCoupon('NOPE', 5000)[0]);
        Coupon::create(['code' => 'OLD', 'type' => 'fixed', 'value' => 5, 'is_active' => true, 'ends_at' => now()->subDay()]);
        $this->assertNull($p->validateCoupon('OLD', 5000)[0]);
        Coupon::create(['code' => 'BIG', 'type' => 'fixed', 'value' => 500, 'is_active' => true]);
        $this->assertSame(5000, $p->couponDiscountCents($p->validateCoupon('BIG', 5000)[0], 5000)); // never exceeds subtotal
    }

    public function test_guest_cart_merges_into_user_cart_on_login(): void
    {
        $v = $this->makeProduct();
        $user = $this->makeUser();
        app(CartService::class)->add($v->id, 2);
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertRedirect();
        $this->assertSame(2, app(CartService::class)->raw()[$v->id] ?? 0);
    }
}
