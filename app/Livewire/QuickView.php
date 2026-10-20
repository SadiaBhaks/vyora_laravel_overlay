<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class QuickView extends Component
{
    public ?int $productId = null;
    public ?int $variantId = null;
    public int $qty = 1;

    #[On('quick-view')]
    public function show(int $productId): void
    {
        $product = Product::active()->with('variants')->find($productId);
        if (! $product) {
            return;
        }
        $this->productId = $product->id;
        $this->variantId = optional($product->variants->firstWhere('stock', '>', 0))->id;
        $this->qty = 1;
    }

    public function close(): void { $this->productId = null; }

    public function selectVariant(int $id): void { $this->variantId = $id; $this->qty = 1; }

    public function add(): void
    {
        if (! $this->variantId) {
            $this->dispatch('toast', message: 'Please choose an available option.', type: 'error');

            return;
        }
        $this->dispatch('cart-add', variantId: $this->variantId, qty: max(1, $this->qty));
        $this->close();
    }

    public function render()
    {
        $product = $this->productId
            ? Product::with(['images', 'brand', 'variants', 'promotions'])->find($this->productId)
            : null;

        return view('livewire.quick-view', ['product' => $product]);
    }
}
