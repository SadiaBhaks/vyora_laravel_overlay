@props(['product', 'trending' => false, 'rank' => null, 'layout' => 'grid'])
@php
    $first = $product->images->first();
    $second = $product->images->get(1);
    $variants = $product->variants;
    $available = $variants->where('stock', '>', 0);
    $soldOut = $available->isEmpty();
    $single = $variants->count() === 1;
    $discount = $product->discount_percent;
    $alt = $first?->alt ?? $product->name;
    $cat = $product->category?->slug ?? 'lifestyle';
    $list = $layout === 'list';
@endphp
<article {{ $attributes->merge(['class' => 'group relative '.($list ? 'grid grid-cols-[9rem_1fr] gap-4 sm:grid-cols-[12rem_1fr] sm:gap-6 card p-3' : '')]) }}>
    <div class="relative overflow-hidden rounded-[12px] bg-surface-2 {{ $list ? 'aspect-square' : 'aspect-[4/5]' }}">
        <a href="{{ route('product.show', $product) }}" class="absolute inset-0 z-0" aria-label="{{ $product->name }}">
            <x-img :image="$first" :alt="$alt" :seed="$product->slug.'-1'" :cat="$cat"
                   class="transition duration-700 [transition-timing-function:var(--ease-vyora)] group-hover:scale-[1.04] {{ $second ? 'group-hover:opacity-0' : '' }}" />
            @if($second)
                <x-img :image="$second" :alt="$second->alt" :seed="$product->slug.'-2'" :cat="$cat"
                       class="absolute inset-0 opacity-0 transition duration-700 [transition-timing-function:var(--ease-vyora)] group-hover:scale-[1.04] group-hover:opacity-100" />
            @endif
        </a>

        <div class="pointer-events-none absolute left-2.5 top-2.5 z-10 flex flex-col items-start gap-1.5">
            @if($discount > 0)<span class="badge badge-accent">−{{ $discount }}%</span>@endif
            @if($product->is_new_arrival)<span class="badge badge-ink">New</span>@endif
            @if($trending)<span class="badge badge-soft"><x-icon name="flame" class="mr-1 size-3" />Trending</span>@endif
            @if($soldOut)<span class="badge badge-soft">Sold out</span>@endif
        </div>
        @if($rank)
            <span class="pointer-events-none absolute bottom-2.5 left-3 z-10 font-display text-3xl font-extrabold tracking-tighter text-white mix-blend-difference tabular">#{{ str_pad($rank, 2, '0', STR_PAD_LEFT) }}</span>
        @endif

        <button type="button"
                x-data
                @click.prevent="vyora.toggleWishlist('{{ $product->slug }}', {{ $product->id }}, $el)"
                :aria-pressed="$store.wl.ids.includes({{ $product->id }})"
                aria-label="Save {{ $product->name }} to wishlist"
                class="absolute right-2.5 top-2.5 z-10 grid size-9 place-items-center rounded-full bg-bg/85 text-ink backdrop-blur transition hover:scale-110">
            <x-icon name="heart" class="size-[18px]" x-bind:class="$store.wl.ids.includes({{ $product->id }}) ? 'fill-current text-danger' : ''" />
        </button>

        <div class="absolute inset-x-2.5 bottom-2.5 z-10 flex gap-2 transition duration-300 sm:translate-y-2 sm:opacity-0 sm:group-hover:translate-y-0 sm:group-hover:opacity-100 sm:group-focus-within:translate-y-0 sm:group-focus-within:opacity-100">
            @if($soldOut)
                <button type="button" disabled class="btn btn-primary btn-sm flex-1">Sold out</button>
            @elseif($single)
                <button type="button" x-data @click.prevent="vyora.addToCart({{ $available->first()->id }})" class="btn btn-primary btn-sm flex-1">
                    <x-icon name="plus" class="size-4" /> Quick add
                </button>
            @else
                <button type="button" x-data @click.prevent="vyora.quickView({{ $product->id }})" class="btn btn-primary btn-sm flex-1">
                    <x-icon name="plus" class="size-4" /> Choose options
                </button>
            @endif
            <button type="button" x-data @click.prevent="vyora.quickView({{ $product->id }})" class="btn btn-sm btn-icon w-9 bg-bg/90 text-ink backdrop-blur" aria-label="Quick view {{ $product->name }}">
                <x-icon name="eye" class="size-4" />
            </button>
        </div>
    </div>

    <div class="{{ $list ? 'flex flex-col justify-center py-1' : 'pt-3' }}">
        <p class="eyebrow !text-[.66rem]">{{ $product->brand?->name }}</p>
        <h3 class="mt-1 text-[.95rem] font-semibold leading-snug tracking-normal">
            <a href="{{ route('product.show', $product) }}" class="hover:underline underline-offset-4">{{ $product->name }}</a>
        </h3>
        @if($list)<p class="mt-1.5 line-clamp-2 text-sm text-muted">{{ $product->short_description }}</p>@endif
        <div class="mt-1.5 flex items-center justify-between gap-2">
            <x-price :product="$product" />
        </div>
        <x-rating :value="$product->rating_avg" :count="$product->rating_count" class="mt-1.5" />
    </div>
</article>
