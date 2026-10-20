@php
    $pricing = app(\App\Services\PricingService::class);
    $demoData = (string) app(\App\Services\SettingsService::class)->get('demo_data', '0') === '1';
    $settings = app(\App\Services\SettingsService::class)->all();
    $cat = $product->category?->slug ?? 'lifestyle';

    $gallery = $product->images->isNotEmpty()
        ? $product->images->map(fn ($i) => ['src' => $i->src, 'alt' => $i->alt])->values()->all()
        : collect([1, 2, 3])->map(fn ($n) => ['src' => route('art', ['seed' => $product->slug.'-'.$n, 'c' => $cat]), 'alt' => $product->name.' — view '.$n])->all();
    $fallbackSrc = route('art', ['seed' => $product->slug.'-1', 'c' => $cat]);

    $variantData = $product->variants->map(function ($v) use ($pricing, $product) {
        $unit = $pricing->unitPriceCents($v);
        $base = \App\Support\Money::toCents($v->price_override ?? $product->price);
        $list = $v->price_override ? $base : max($base, \App\Support\Money::toCents($product->compare_at_price ?? 0));

        return ['id' => $v->id, 'color' => $v->color, 'hex' => $v->color_hex, 'size' => $v->size, 'stock' => (int) $v->stock,
                'price' => \App\Support\Money::format($unit), 'list' => $list > $unit ? \App\Support\Money::format($list) : null,
                'label' => $v->label()];
    })->values()->all();
    $firstAvail = collect($variantData)->first(fn ($v) => $v['stock'] > 0) ?? ($variantData[0] ?? null);

    $inStockTotal = $product->total_stock;
    $ld = [
        '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $product->name, 'sku' => $product->sku,
        'description' => $product->short_description, 'image' => collect($gallery)->pluck('src')->all(),
        'brand' => ['@type' => 'Brand', 'name' => $product->brand?->name],
        'offers' => ['@type' => 'Offer', 'url' => route('product.show', $product), 'priceCurrency' => config('vyora.currency'),
            'price' => $product->effective_price, 'availability' => $inStockTotal > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'],
    ];
    // Never advertise seeded demo reviews to search engines as if they were genuine.
    if ($product->rating_count > 0 && ! $demoData) {
        $ld['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $product->rating_avg, 'reviewCount' => $product->rating_count];
    }
    $total = max(1, $breakdown->sum());
    $benefits = collect($product->features ?? [])->take(4);
@endphp
<x-layouts.app :title="$product->name" :description="$product->short_description" :image="$gallery[0]['src']" type="product" :jsonld="$ld" :canonical="route('product.show', $product)">
<div class="mx-auto max-w-[88rem] px-4 pt-6 md:px-8">
    <x-breadcrumbs :items="[['label' => $product->category->name, 'url' => route('categories.show', $product->category->slug)], ['label' => $product->name]]" />

    <div class="grid gap-8 lg:grid-cols-[1.15fr_1fr] lg:gap-14"
         x-data="{
            variants: @js($variantData), color: @js($firstAvail['color'] ?? null), size: @js($firstAvail['size'] ?? null), qty: 1,
            active: 0, zoom: false, lightbox: false, gallery: @js($gallery), fallback: @js($fallbackSrc), origin: '50% 50%', max: {{ (int) config('vyora.max_cart_quantity') }},
            get current() { return this.variants.find(v => v.color === this.color && v.size === this.size) || null },
            get limit() { return this.current ? Math.max(0, Math.min(this.max, this.current.stock)) : 0 },
            avail(color, size) { return this.variants.some(v => (color === undefined || v.color === color) && (size === undefined || v.size === size) && v.stock > 0) },
            pick(kind, val) {
                this[kind] = val;
                if (!this.current || this.current.stock <= 0) { const m = this.variants.find(v => v[kind] === val && v.stock > 0) || this.variants.find(v => v[kind] === val); if (m) { this.color = m.color; this.size = m.size; } }
                this.qty = Math.min(this.qty, Math.max(1, this.limit));
            },
            colors() { const s = new Set(), o = []; this.variants.forEach(v => { if (v.color && !s.has(v.color)) { s.add(v.color); o.push({ name: v.color, hex: v.hex }); } }); return o },
            sizes() { return [...new Set(this.variants.map(v => v.size).filter(Boolean))] },
            move(e) { const r = e.currentTarget.getBoundingClientRect(); this.origin = ((e.clientX - r.left) / r.width * 100) + '% ' + ((e.clientY - r.top) / r.height * 100) + '%' },
            share() { const d = { title: @js($product->name), url: location.href }; if (navigator.share) { navigator.share(d).catch(() => {}); } else { navigator.clipboard?.writeText(d.url).then(() => $store.toasts.push('Link copied to clipboard.')); } }
         }"
         @keydown.escape.window="lightbox = false" @keydown.arrow-right.window="lightbox && (active = (active + 1) % gallery.length)" @keydown.arrow-left.window="lightbox && (active = (active - 1 + gallery.length) % gallery.length)">

        {{-- ============ Gallery ============ --}}
        <div class="grid gap-3 sm:grid-cols-[5.5rem_1fr]">
            <ul class="order-2 flex gap-2.5 overflow-x-auto scrollbar-none sm:order-1 sm:flex-col sm:overflow-visible" aria-label="Product images">
                <template x-for="(g, i) in gallery" :key="i">
                    <li class="shrink-0"><button type="button" @click="active = i" class="block size-20 overflow-hidden rounded-xl border-2 bg-surface-2 transition sm:size-[5.5rem]" :class="active === i ? 'border-ink' : 'border-transparent opacity-70 hover:opacity-100'" :aria-label="'Show image ' + (i + 1)" :aria-current="active === i">
                        <img :src="g.src" alt="" width="88" height="88" class="size-full object-cover" loading="lazy" @@error="$el.src = fallback"></button></li>
                </template>
            </ul>
            <div class="order-1 sm:order-2">
                <div class="group relative aspect-[4/5] overflow-hidden rounded-[1.5rem] bg-surface-2 sm:aspect-square lg:aspect-[4/5]" @mousemove="zoom && move($event)" @mouseenter="zoom = true" @mouseleave="zoom = false">
                    <template x-for="(g, i) in gallery" :key="'m' + i">
                        <img :src="g.src" :alt="g.alt" width="1000" height="1000" x-show="active === i" x-transition.opacity.duration.300ms @@error="$el.src = fallback"
                             class="absolute inset-0 size-full cursor-zoom-in object-cover transition-transform duration-300 ease-out" :style="zoom ? { transform: 'scale(1.7)', transformOrigin: origin } : {}" @click="lightbox = true"
                             :loading="i === 0 ? 'eager' : 'lazy'" :fetchpriority="i === 0 ? 'high' : 'auto'">
                    </template>
                    <div class="pointer-events-none absolute left-4 top-4 flex flex-col items-start gap-1.5">
                        @if($product->discount_percent > 0)<span class="badge badge-accent">−{{ $product->discount_percent }}%</span>@endif
                        @if($product->is_new_arrival)<span class="badge badge-ink">New</span>@endif
                    </div>
                    <button type="button" @click="lightbox = true" class="btn btn-icon btn-sm absolute bottom-4 right-4 bg-bg/85 text-ink backdrop-blur" aria-label="Enlarge image"><x-icon name="plus" class="size-4" /></button>
                    <div class="absolute inset-x-0 bottom-4 flex justify-center gap-1.5 sm:hidden" aria-hidden="true"><template x-for="(g, i) in gallery"><span class="size-1.5 rounded-full" :class="active === i ? 'bg-ink' : 'bg-ink/30'"></span></template></div>
                </div>
            </div>

            {{-- Lightbox --}}
            <div x-show="lightbox" x-cloak class="fixed inset-0 z-[85] grid place-items-center bg-ink/95 p-4" role="dialog" aria-modal="true" aria-label="Image viewer" x-trap.noscroll="lightbox" x-transition.opacity>
                <button type="button" @click="lightbox = false" class="btn btn-icon absolute right-4 top-4 bg-bg text-ink" aria-label="Close viewer"><x-icon name="x" /></button>
                <button type="button" @click="active = (active - 1 + gallery.length) % gallery.length" class="btn btn-icon absolute left-4 top-1/2 -translate-y-1/2 bg-bg text-ink" aria-label="Previous image"><x-icon name="chevron-left" /></button>
                <img :src="gallery[active].src" :alt="gallery[active].alt" class="max-h-[88vh] max-w-full rounded-xl object-contain" @@error="$el.src = fallback">
                <button type="button" @click="active = (active + 1) % gallery.length" class="btn btn-icon absolute right-4 top-1/2 -translate-y-1/2 bg-bg text-ink" aria-label="Next image"><x-icon name="chevron-right" /></button>
            </div>
        </div>

        {{-- ============ Buy box ============ --}}
        <div class="lg:sticky lg:top-24 lg:self-start">
            <a href="{{ route('categories.show', $product->category->slug) }}" class="eyebrow hover:text-ink">{{ $product->brand?->name }} · {{ $product->category->name }}</a>
            <h1 class="display-lg mt-2 text-balance">{{ $product->name }}</h1>
            <a href="#reviews" class="mt-3 inline-block"><x-rating :value="$product->rating_avg" :count="$product->rating_count" size="size-4" /></a>

            <p class="mt-5 flex flex-wrap items-baseline gap-3 tabular" aria-live="polite">
                <span class="font-display text-4xl font-bold" x-text="current ? current.price : '{{ \App\Support\Money::format(\App\Support\Money::toCents($product->effective_price)) }}'"></span>
                <s class="text-lg text-muted" x-show="current && current.list" x-text="current && current.list"></s>
                @if($product->discount_percent > 0)<span class="badge badge-accent">Save {{ $product->discount_percent }}%</span>@endif
            </p>
            @if($product->activePromotion())<p class="mt-2 text-sm text-muted">Includes the “{{ $product->activePromotion()->name }}” promotion — ends {{ $product->activePromotion()->ends_at->format('M j, g:i A') }}.</p>@endif
            <p class="mt-4 text-muted">{{ $product->short_description }}</p>

            @if($errors->has('variant_id'))<p class="field-error mt-3" role="alert">{{ $errors->first('variant_id') }}</p>@endif

            <template x-if="colors().length">
                <fieldset class="mt-7"><legend class="label">Colour: <span class="font-normal text-muted" x-text="color"></span></legend>
                    <div class="flex flex-wrap gap-2.5">
                        <template x-for="c in colors()" :key="c.name">
                            <button type="button" @click="pick('color', c.name)" :disabled="!avail(c.name)" :aria-pressed="color === c.name" :aria-label="c.name + (avail(c.name) ? '' : ' (sold out)')"
                                    class="relative size-10 rounded-full border border-line ring-offset-2 ring-offset-[var(--bg)] transition disabled:opacity-40" :class="color === c.name ? 'ring-2 ring-[var(--ink)]' : 'hover:scale-105'" :style="{ background: c.hex || '#999' }">
                                <span x-show="!avail(c.name)" class="absolute inset-x-1 top-1/2 h-px -rotate-45 bg-ink"></span></button>
                        </template>
                    </div></fieldset>
            </template>
            <template x-if="sizes().length">
                <fieldset class="mt-6"><legend class="label">Size</legend>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="s in sizes()" :key="s">
                            <button type="button" @click="pick('size', s)" :disabled="!avail(color, s)" :aria-pressed="size === s" class="chip !h-11 !min-w-[3rem] justify-center disabled:line-through disabled:opacity-40" :class="size === s && 'is-on'" x-text="s"></button>
                        </template>
                    </div></fieldset>
            </template>

            <p class="mt-5 text-sm" aria-live="polite">
                <template x-if="current && current.stock <= 0"><span class="font-medium text-danger">Out of stock in this option</span></template>
                <template x-if="current && current.stock > 0 && current.stock <= {{ (int) config('vyora.low_stock_threshold') }}"><span class="font-medium text-warn" x-text="'Only ' + current.stock + ' left in stock'"></span></template>
                <template x-if="current && current.stock > {{ (int) config('vyora.low_stock_threshold') }}"><span class="inline-flex items-center gap-1.5 text-success"><x-icon name="check" class="size-4" /> In stock</span></template>
                <template x-if="!current"><span class="text-muted">This combination isn't available.</span></template>
            </p>

            <div class="mt-5 flex items-center gap-3">
                <div class="inline-flex items-center rounded-full border border-line" role="group" aria-label="Quantity">
                    <button type="button" @click="qty = Math.max(1, qty - 1)" class="grid size-12 place-items-center rounded-full hover:bg-surface-2 active:scale-90" aria-label="Decrease quantity"><x-icon name="minus" class="size-4" /></button>
                    <output class="w-9 text-center font-semibold tabular" x-text="qty" aria-live="polite"></output>
                    <button type="button" @click="qty = Math.min(limit || 1, qty + 1)" class="grid size-12 place-items-center rounded-full hover:bg-surface-2 active:scale-90" aria-label="Increase quantity"><x-icon name="plus" class="size-4" /></button>
                </div>
                <button type="button" class="btn btn-primary btn-lg flex-1" :disabled="!current || current.stock <= 0" @click="vyora.addToCart(current.id, qty)">Add to cart</button>
            </div>

            <form method="POST" action="{{ route('cart.buy-now') }}" class="mt-3">@csrf
                <input type="hidden" name="variant_id" :value="current ? current.id : ''"><input type="hidden" name="quantity" :value="qty">
                <button class="btn btn-accent btn-lg w-full" :disabled="!current || current.stock <= 0">Buy now</button>
            </form>
            <div class="mt-3 grid grid-cols-2 gap-3">
                <button type="button" @click="vyora.toggleWishlist('{{ $product->slug }}', {{ $product->id }}, $el)" :aria-pressed="$store.wl.ids.includes({{ $product->id }})" class="btn btn-ghost"><x-icon name="heart" class="size-[18px]" x-bind:class="$store.wl.ids.includes({{ $product->id }}) ? 'fill-current text-danger' : ''" /> <span x-text="$store.wl.ids.includes({{ $product->id }}) ? 'Saved' : 'Wishlist'"></span></button>
                <button type="button" @click="share()" class="btn btn-ghost"><x-icon name="share" class="size-[18px]" /> Share</button>
            </div>

            <ul class="mt-8 divide-y divide-line rounded-2xl border border-line text-sm">
                <li class="flex gap-3 p-4"><x-icon name="truck" class="mt-0.5 size-5 shrink-0" /><div><p class="font-semibold">Delivery estimate</p><p class="text-muted">Standard: {{ \Carbon\Carbon::parse($delivery[0])->format('M j') }}–{{ \Carbon\Carbon::parse($delivery[1])->format('M j') }}. Express available at checkout. {{ $settings['shipping_regions'] }}</p></div></li>
                <li class="flex gap-3 p-4"><x-icon name="package" class="mt-0.5 size-5 shrink-0" /><div><p class="font-semibold">Shipping</p><p class="text-muted">Free standard shipping over ${{ number_format((float) config('vyora.free_shipping_threshold'), 0) }}. Orders ship within {{ $settings['processing_days'] }} business days. <a href="{{ route('shipping') }}" class="underline">Details</a></p></div></li>
                <li class="flex gap-3 p-4"><x-icon name="rotate" class="mt-0.5 size-5 shrink-0" /><div><p class="font-semibold">{{ $settings['returns_days'] }}-day returns</p><p class="text-muted">{{ $settings['return_shipping'] }} <a href="{{ route('returns') }}" class="underline">Returns policy</a></p></div></li>
            </ul>
        </div>
    </div>

    {{-- ============ Details ============ --}}
    <div class="mt-20 grid gap-12 lg:grid-cols-[1.15fr_1fr] lg:gap-14">
        <section aria-labelledby="about-title">
            <h2 id="about-title" class="display-lg">About this product</h2>
            <div class="prose-vyora mt-5">{!! nl2br(e($product->description)) !!}</div>
            @if($product->materials)<p class="mt-6 text-sm"><strong>Materials:</strong> <span class="text-muted">{{ $product->materials }}</span></p>@endif
            @if($product->tags->isNotEmpty())<p class="mt-5 flex flex-wrap gap-2">@foreach($product->tags as $t)<a href="{{ route('search', ['q' => $t->name]) }}" class="chip">#{{ $t->name }}</a>@endforeach</p>@endif
        </section>

        <section aria-labelledby="specs-title">
            <h2 id="specs-title" class="display-lg">Specifications</h2>
            @php $specs = collect($product->specifications ?? [])->merge($product->attributes()->get()->pluck('value', 'name')); @endphp
            @if($specs->isEmpty())<p class="mt-5 text-sm text-muted">No specifications listed.</p>
            @else<dl class="mt-5 divide-y divide-line rounded-2xl border border-line text-sm">@foreach($specs as $k => $v)<div class="grid grid-cols-[9rem_1fr] gap-4 px-4 py-3"><dt class="text-muted">{{ $k }}</dt><dd class="font-medium">{{ $v }}</dd></div>@endforeach</dl>@endif
        </section>
    </div>

    @if($benefits->isNotEmpty())
        <section class="mt-20" aria-labelledby="love-title">
            <x-section-heading eyebrow="Why you'll love it" title="What it does for you" id="love-title" />
            <ul class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">@foreach($benefits as $i => $b)<li class="card p-6"><span class="font-display text-3xl font-extrabold text-accent-text tabular">0{{ $i + 1 }}</span><p class="mt-3 font-medium leading-snug">{{ $b }}</p></li>@endforeach</ul>
        </section>
    @endif

    {{-- ============ Reviews ============ --}}
    <section class="mt-24" id="reviews" aria-labelledby="reviews-title">
        <x-section-heading eyebrow="Customer reviews" title="What buyers say" id="reviews-title" />
        <div class="grid gap-10 lg:grid-cols-[22rem_1fr]">
            <div class="card self-start p-6">
                @if($product->rating_count > 0)
                    <p class="font-display text-6xl font-extrabold tabular">{{ number_format($product->rating_avg, 1) }}</p>
                    <x-rating :value="$product->rating_avg" :count="$product->rating_count" size="size-5" :showCount="false" class="mt-1" />
                    <p class="mt-1 text-sm text-muted">{{ $product->rating_count }} {{ \Illuminate\Support\Str::plural('review', $product->rating_count) }}</p>
                    <ul class="mt-5 space-y-2">@foreach([5, 4, 3, 2, 1] as $star)@php $n = (int) ($breakdown[$star] ?? 0); @endphp
                        <li class="flex items-center gap-3 text-sm"><span class="w-8 tabular">{{ $star }} ★</span><span class="h-2 flex-1 overflow-hidden rounded-full bg-surface-2"><span class="block h-full rounded-full bg-ink" style="width: {{ $n / $total * 100 }}%"></span></span><span class="w-6 text-right text-muted tabular">{{ $n }}</span></li>
                    @endforeach</ul>
                @else
                    <p class="font-display text-2xl font-bold">No reviews yet</p><p class="mt-1 text-sm text-muted">Reviews appear here once verified buyers share their experience.</p>
                @endif

                <div class="mt-6 border-t border-line pt-5">
                    @auth
                        @if($canReview)
                            <form method="POST" action="{{ route('product.reviews.store', $product) }}" class="space-y-3" x-data="{ r: {{ (int) old('rating', 0) }} }">@csrf
                                <p class="label">Write a verified review</p>
                                <fieldset><legend class="sr-only">Rating</legend>
                                    <div class="flex gap-1">@for($i = 1; $i <= 5; $i++)<label class="cursor-pointer"><input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" x-model.number="r" @checked(old('rating') == $i)><x-icon name="star" class="size-7 transition" x-bind:class="r >= {{ $i }} ? 'fill-current text-ink' : 'text-line'" /><span class="sr-only">{{ $i }} stars</span></label>@endfor</div>
                                    @error('rating')<p class="field-error" role="alert">{{ $message }}</p>@enderror</fieldset>
                                <x-field name="title" label="Headline (optional)" />
                                <x-field name="body" type="textarea" label="Your review" required rows="4" />
                                <button class="btn btn-primary w-full">Submit for moderation</button>
                            </form>
                        @elseif($hasReviewed)<p class="text-sm text-muted">You've already reviewed this product. Thank you!</p>
                        @else<p class="text-sm text-muted">Reviews are open to customers whose order of this product has shipped. That keeps every review verified.</p>@endif
                    @else
                        <p class="text-sm text-muted"><a href="{{ route('login') }}" class="font-semibold text-ink underline underline-offset-4">Sign in</a> to review products you've bought.</p>
                    @endauth
                </div>
            </div>

            <div>
                @if($reviews->total() > 0)
                    <div class="mb-5 flex items-center justify-between"><p class="text-sm text-muted tabular">{{ $reviews->total() }} {{ \Illuminate\Support\Str::plural('review', $reviews->total()) }}</p>
                        <form method="GET" action="{{ route('product.show', $product) }}#reviews" class="flex items-center gap-2"><label for="rs" class="text-sm text-muted">Sort</label>
                            <select id="rs" name="review_sort" class="input !h-9 !w-auto !rounded-full !py-0 !text-sm" onchange="this.form.submit()">@foreach(['newest' => 'Newest', 'highest' => 'Highest rated', 'lowest' => 'Lowest rated'] as $k => $l)<option value="{{ $k }}" @selected($reviewSort === $k)>{{ $l }}</option>@endforeach</select><noscript><button class="btn btn-ghost btn-sm">Apply</button></noscript></form></div>
                    <ul class="divide-y divide-line">@foreach($reviews as $r)
                        <li class="py-6 first:pt-0"><div class="flex flex-wrap items-center gap-x-3 gap-y-1"><x-rating :value="$r->rating" :count="1" :showCount="false" />
                            @if($r->is_verified)<span class="inline-flex items-center gap-1 text-xs font-semibold text-success"><x-icon name="check" class="size-3.5" :stroke="3" /> Verified purchase</span>@endif
                            @if($r->is_demo)<span class="badge badge-soft" title="Seeded demonstration review">Demo review</span>@endif</div>
                            @if($r->title)<h3 class="mt-2 font-display text-lg font-bold">{{ $r->title }}</h3>@endif
                            <p class="mt-1.5 text-muted">{{ $r->body }}</p>
                            <p class="mt-2 text-xs text-muted">{{ $r->user->name ? \Illuminate\Support\Str::of($r->user->name)->before(' ') : 'Customer' }} · {{ $r->created_at->format('M j, Y') }}</p></li>
                    @endforeach</ul>
                    <div class="mt-8">{{ $reviews->links() }}</div>
                @else
                    <x-empty-state icon="message" title="Be the first to review" text="Verified buyers can review this product once their order ships." />
                @endif
            </div>
        </div>
    </section>

    @if($look->isNotEmpty())
        <section class="mt-24" aria-labelledby="look-title"><x-section-heading eyebrow="Complete the look" title="Pairs well with" id="look-title" />
            <div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-4">@foreach($look as $p)<x-product-card :product="$p" />@endforeach</div></section>
    @endif
    @if($related->isNotEmpty())
        <section class="mt-24" aria-labelledby="like-title"><x-section-heading eyebrow="You may also like" title="Similar picks" id="like-title" />
            <div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-4">@foreach($related as $p)<x-product-card :product="$p" />@endforeach</div></section>
    @endif
    @if($recent->isNotEmpty())
        <section class="mt-24" aria-labelledby="rv-title"><x-section-heading eyebrow="Pick up where you left off" title="Recently viewed" id="rv-title" />
            <div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-6">@foreach($recent as $p)<x-product-card :product="$p" />@endforeach</div></section>
    @endif
</div>
</x-layouts.app>
