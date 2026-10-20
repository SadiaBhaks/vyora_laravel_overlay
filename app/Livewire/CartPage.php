<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use App\Services\RecommendationService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartPage extends Component
{
    public string $coupon = '';
    public ?string $couponMessage = null;

    public function mount(): void
    {
        $this->coupon = (string) session('vyora.coupon', '');
    }

    #[On('cart-updated')]
    public function refresh(): void {}

    public function applyCoupon(): void
    {
        $code = trim($this->coupon);
        if ($code === '') {
            session()->forget('vyora.coupon');
            $this->couponMessage = null;

            return;
        }
        $summary = app(CartService::class)->summary($code);
        if ($summary['coupon']) {
            session(['vyora.coupon' => $summary['coupon']->code]);
            $this->coupon = $summary['coupon']->code;
            $this->couponMessage = 'Coupon applied.';
            $this->dispatch('toast', message: 'Coupon applied.', type: 'success');
        } else {
            session()->forget('vyora.coupon');
            $this->couponMessage = $summary['coupon_error'];
        }
    }

    public function removeCoupon(): void
    {
        session()->forget('vyora.coupon');
        $this->coupon = '';
        $this->couponMessage = null;
    }

    public function increment(int $variantId): void
    {
        $cart = app(CartService::class);
        $r = $cart->setQuantity($variantId, ($cart->raw()[$variantId] ?? 0) + 1);
        $this->done($r);
    }

    public function decrement(int $variantId): void
    {
        $cart = app(CartService::class);
        $this->done($cart->setQuantity($variantId, ($cart->raw()[$variantId] ?? 1) - 1));
    }

    public function remove(int $variantId): void
    {
        app(CartService::class)->remove($variantId);
        $this->dispatch('toast', message: 'Removed from your cart.', type: 'success');
        $this->dispatch('cart-count', count: app(CartService::class)->count());
    }

    private function done(array $r): void
    {
        $this->dispatch('toast', message: $r['ok'] ? ($r['clamped'] ?? false ? $r['message'] : 'Cart updated.') : $r['message'], type: ($r['ok'] && ! ($r['clamped'] ?? false)) ? 'success' : 'error');
        $this->dispatch('cart-count', count: app(CartService::class)->count());
    }

    public function render()
    {
        $cart = app(CartService::class);
        $summary = $cart->summary(session('vyora.coupon'));
        $variantProductIds = $summary['lines']->pluck('variant.product_id')->all();

        $suggested = collect();
        if ($summary['lines']->isNotEmpty()) {
            $first = $summary['lines']->first()['variant']->product;
            $first->loadMissing('tags');
            $suggested = app(RecommendationService::class)->related($first, 4, $variantProductIds);
        }

        return view('livewire.cart-page', ['summary' => $summary, 'suggested' => $suggested]);
    }
}
