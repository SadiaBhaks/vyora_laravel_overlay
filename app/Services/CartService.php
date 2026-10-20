<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Guests: session cart (variant_id => qty). Customers: DB cart.
 * Only variant IDs and quantities are ever stored — prices are always recomputed server-side.
 */
class CartService
{
    private const SESSION_KEY = 'vyora.cart';

    public function __construct(private PricingService $pricing) {}

    private function user(): ?User { return Auth::user(); }

    /** @return array<int,int> variant_id => quantity */
    public function raw(): array
    {
        if ($user = $this->user()) {
            return $user->cart?->items()->pluck('quantity', 'product_variant_id')->all() ?? [];
        }

        return session(self::SESSION_KEY, []);
    }

    public function add(int $variantId, int $quantity = 1): array
    {
        return $this->setQuantity($variantId, ($this->raw()[$variantId] ?? 0) + max(1, $quantity));
    }

    /** @return array{ok: bool, message: string, quantity: int} */
    public function setQuantity(int $variantId, int $quantity): array
    {
        $variant = ProductVariant::with('product')->where('is_active', true)->find($variantId);
        if (! $variant || ! $variant->product || $variant->product->status !== 'active' || $variant->product->trashed()) {
            return ['ok' => false, 'message' => 'This product is no longer available.', 'quantity' => 0];
        }
        if ($quantity <= 0) {
            $this->remove($variantId);

            return ['ok' => true, 'message' => 'Removed from your cart.', 'quantity' => 0];
        }
        $max = min(config('vyora.max_cart_quantity'), $variant->stock);
        if ($max <= 0) {
            return ['ok' => false, 'message' => 'Sorry, this option is out of stock.', 'quantity' => $this->raw()[$variantId] ?? 0];
        }
        $clamped = min($quantity, $max);
        $this->write($variantId, $clamped);
        $message = $clamped < $quantity
            ? "Only $max available — quantity adjusted."
            : 'Added to your cart.';

        return ['ok' => true, 'message' => $message, 'quantity' => $clamped, 'clamped' => $clamped < $quantity];
    }

    private function write(int $variantId, int $quantity): void
    {
        if ($user = $this->user()) {
            $cart = $user->cart ?: Cart::create(['user_id' => $user->id]);
            CartItem::updateOrCreate(['cart_id' => $cart->id, 'product_variant_id' => $variantId], ['quantity' => $quantity]);
            $user->unsetRelation('cart');

            return;
        }
        $cart = session(self::SESSION_KEY, []);
        $cart[$variantId] = $quantity;
        session([self::SESSION_KEY => $cart]);
    }

    public function remove(int $variantId): void
    {
        if ($user = $this->user()) {
            $user->cart?->items()->where('product_variant_id', $variantId)->delete();

            return;
        }
        $cart = session(self::SESSION_KEY, []);
        unset($cart[$variantId]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        if ($user = $this->user()) {
            $user->cart?->items()->delete();
        }
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return (int) array_sum($this->raw());
    }

    /**
     * Resolve raw cart to purchasable lines. Unavailable variants are dropped, quantities clamped to stock.
     *
     * @return Collection<int, array{variant: ProductVariant, quantity: int, unit_cents: int, line_cents: int, warning: ?string}>
     */
    public function lines(): Collection
    {
        $raw = $this->raw();
        if (! $raw) {
            return collect();
        }
        $variants = ProductVariant::with(['product.images', 'product.brand', 'product.promotions'])
            ->whereIn('id', array_keys($raw))->where('is_active', true)->get()->keyBy('id');

        return collect($raw)->map(function ($qty, $variantId) use ($variants) {
            $variant = $variants->get($variantId);
            if (! $variant || ! $variant->product || $variant->product->status !== 'active' || $variant->product->trashed()) {
                return null;
            }
            $warning = null;
            $quantity = (int) $qty;
            if ($variant->stock <= 0) {
                $warning = 'Out of stock — remove to continue.';
            } elseif ($quantity > $variant->stock) {
                $quantity = $variant->stock;
                $warning = "Only {$variant->stock} left — quantity reduced.";
            }
            $unit = $this->pricing->unitPriceCents($variant);

            return ['variant' => $variant, 'quantity' => $quantity, 'unit_cents' => $unit, 'line_cents' => $unit * $quantity, 'warning' => $warning];
        })->filter()->values();
    }

    public function summary(?string $coupon = null, string $shipping = 'standard'): array
    {
        $lines = $this->lines();

        return ['lines' => $lines] + $this->pricing->totals($lines->filter(fn ($l) => $l['variant']->stock > 0), $coupon, $shipping);
    }

    /** Merge the guest session cart into the user's saved cart after login/registration. */
    public function mergeGuestCart(User $user, array $guest): void
    {
        if (! $guest) {
            return;
        }
        DB::transaction(function () use ($user, $guest) {
            $cart = $user->cart ?: Cart::create(['user_id' => $user->id]);
            $existing = $cart->items()->pluck('quantity', 'product_variant_id')->all();
            $variants = ProductVariant::with('product')->whereIn('id', array_keys($guest))->where('is_active', true)->get()->keyBy('id');

            foreach ($guest as $variantId => $qty) {
                $variant = $variants->get($variantId);
                if (! $variant || $variant->product?->status !== 'active' || $variant->stock <= 0) {
                    continue;
                }
                $merged = min(($existing[$variantId] ?? 0) + (int) $qty, $variant->stock, config('vyora.max_cart_quantity'));
                CartItem::updateOrCreate(['cart_id' => $cart->id, 'product_variant_id' => $variantId], ['quantity' => $merged]);
            }
        });
        $user->unsetRelation('cart');
    }
}
