<div>
    @if($product)
        @php
            $variant = $product->variants->firstWhere('id', $variantId);
            $colors = $product->colors(); $sizes = $product->sizes();
            $unit = $variant ? app(\App\Services\PricingService::class)->unitPriceCents($variant) : null;
        @endphp
        <div class="fixed inset-0 z-[78] grid place-items-end sm:place-items-center" role="dialog" aria-modal="true" aria-label="Quick view: {{ $product->name }}"
             x-data x-trap.noscroll="true" @keydown.escape.window="$wire.close()">
            <div class="absolute inset-0 bg-ink/55 backdrop-blur-sm" wire:click="close"></div>
            <div class="relative grid max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-t-3xl bg-bg shadow-2xl sm:rounded-2xl md:grid-cols-2 animate-rise">
                <button type="button" wire:click="close" class="btn btn-icon btn-ghost absolute right-3 top-3 z-10 bg-bg/80 backdrop-blur" aria-label="Close quick view"><x-icon name="x" /></button>
                <div class="aspect-square bg-surface-2 md:aspect-auto"><x-img :image="$product->images->first()" :alt="$product->name" :seed="$product->slug.'-1'" :cat="$product->category?->slug ?? 'lifestyle'" /></div>
                <div class="flex flex-col p-6 sm:p-8">
                    <p class="eyebrow">{{ $product->brand?->name }}</p>
                    <h2 class="mt-1 font-display text-2xl font-bold">{{ $product->name }}</h2>
                    <div class="mt-2"><x-rating :value="$product->rating_avg" :count="$product->rating_count" /></div>
                    <div class="mt-3 text-lg">
                        @if($unit)<span class="font-semibold tabular">{{ \App\Support\Money::format($unit) }}</span>@else<x-price :product="$product" size="text-lg" />@endif
                        @if($product->list_price)<s class="ml-2 text-sm text-muted tabular">${{ number_format((float) $product->list_price, 2) }}</s>@endif
                    </div>
                    <p class="mt-3 text-sm text-muted">{{ $product->short_description }}</p>

                    <fieldset class="mt-5">
                        <legend class="label">Option</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->variants as $v)
                                <button type="button" wire:click="selectVariant({{ $v->id }})" @disabled($v->stock <= 0)
                                        class="chip {{ $v->id === $variantId ? 'is-on' : '' }} {{ $v->stock <= 0 ? 'line-through opacity-50' : '' }}" aria-pressed="{{ $v->id === $variantId ? 'true' : 'false' }}">
                                    @if($v->color_hex)<span class="size-3 rounded-full border border-line" style="background: {{ $v->color_hex }}"></span>@endif{{ $v->label() }}
                                </button>
                            @endforeach
                        </div>
                    </fieldset>

                    @if($variant)
                        <p class="mt-3 text-xs {{ $variant->stock <= config('vyora.low_stock_threshold') ? 'font-medium text-warn' : 'text-muted' }}">
                            {{ $variant->stock <= 0 ? 'Out of stock' : ($variant->stock <= config('vyora.low_stock_threshold') ? 'Only '.$variant->stock.' left in stock' : 'In stock') }}
                        </p>
                    @endif

                    <div class="mt-auto flex flex-col gap-2 pt-6 sm:flex-row">
                        <div class="inline-flex items-center justify-between rounded-full border border-line">
                            <button type="button" wire:click="$set('qty', {{ max(1, $qty - 1) }})" class="grid size-11 place-items-center rounded-full hover:bg-surface-2" aria-label="Decrease quantity"><x-icon name="minus" class="size-4" /></button>
                            <span class="w-8 text-center font-semibold tabular">{{ $qty }}</span>
                            <button type="button" wire:click="$set('qty', {{ min($variant?->stock ?? 1, config('vyora.max_cart_quantity'), $qty + 1) }})" class="grid size-11 place-items-center rounded-full hover:bg-surface-2" aria-label="Increase quantity"><x-icon name="plus" class="size-4" /></button>
                        </div>
                        <button type="button" wire:click="add" class="btn btn-primary btn-lg flex-1" @disabled(! $variant || $variant->stock <= 0)>Add to cart</button>
                    </div>
                    <a href="{{ route('product.show', $product) }}" class="mt-4 text-center text-sm font-medium underline underline-offset-4">View full details</a>
                </div>
            </div>
        </div>
    @endif
</div>
