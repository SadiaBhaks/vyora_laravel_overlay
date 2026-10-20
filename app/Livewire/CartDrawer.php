<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartDrawer extends Component
{
    public bool $open = false;

    #[On('cart-add')]
    public function add(int $variantId, int $qty = 1): void
    {
        $cart = app(CartService::class);
        $result = $cart->add($variantId, $qty);
        $this->announce($result['ok'], $result['message']);
        if ($result['ok']) {
            $this->open = true;
        }
    }

    #[On('cart-open')]
    public function openDrawer(): void { $this->open = true; }

    #[On('cart-updated')]
    public function refresh(): void {}

    public function increment(int $variantId): void
    {
        $cart = app(CartService::class);
        $current = $cart->raw()[$variantId] ?? 0;
        $r = $cart->setQuantity($variantId, $current + 1);
        $this->announce($r['ok'] && empty($r['clamped']), $r['ok'] ? ($r['clamped'] ?? false ? $r['message'] : 'Quantity updated.') : $r['message']);
    }

    public function decrement(int $variantId): void
    {
        $cart = app(CartService::class);
        $cart->setQuantity($variantId, ($cart->raw()[$variantId] ?? 1) - 1);
        $this->announce(true, 'Cart updated.');
    }

    public function remove(int $variantId): void
    {
        app(CartService::class)->remove($variantId);
        $this->announce(true, 'Removed from your cart.');
    }

    private function announce(bool $ok, string $message): void
    {
        $this->dispatch('toast', message: $message, type: $ok ? 'success' : 'error');
        $this->dispatch('cart-count', count: app(CartService::class)->count());
    }

    public function render()
    {
        return view('livewire.cart-drawer', ['summary' => app(CartService::class)->summary(session('vyora.coupon'))]);
    }
}
