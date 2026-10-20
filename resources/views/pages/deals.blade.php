<x-layouts.app title="Deals" description="Current VYORA offers: genuinely discounted products, limited-time promotions and coupon codes.">
    <div class="mx-auto max-w-[88rem] px-4 pt-8 md:px-8">
        <x-breadcrumbs :items="[['label' => 'Deals']]" />
        <p class="eyebrow mb-2">Current offers</p><h1 class="display-xl">Deals</h1>
        <p class="mt-3 max-w-xl text-muted">Every price here is calculated from live promotion records. When a countdown reaches zero, the discount stops applying.</p>

        @forelse($promotions as $promo)
            <section class="mt-12" aria-labelledby="promo-{{ $promo->id }}">
                <div class="mb-6 flex flex-wrap items-end justify-between gap-5 rounded-2xl bg-ink p-6 text-bg md:p-8">
                    <div><p class="eyebrow !text-bg/60">Limited-time promotion</p><h2 id="promo-{{ $promo->id }}" class="display-lg mt-1">{{ $promo->name }} · {{ $promo->discount_percent }}% off</h2>
                        @if($promo->description)<p class="mt-2 max-w-lg text-sm text-bg/70">{{ $promo->description }}</p>@endif</div>
                    <x-countdown :end="$promo->ends_at" />
                </div>
                <div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-4 md:gap-x-5">@foreach($promo->products as $p)<x-product-card :product="$p" />@endforeach</div>
            </section>
        @empty
            <p class="mt-10 rounded-xl border border-dashed border-line p-6 text-sm text-muted">There's no timed promotion running right now. Reduced-price products are listed below.</p>
        @endforelse

        @if($coupons->isNotEmpty())
            <section class="mt-16" aria-labelledby="coupons-title">
                <x-section-heading eyebrow="Coupon codes" title="Enter at checkout" id="coupons-title" />
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach($coupons as $c)
                        <div class="card relative overflow-hidden p-6">
                            <x-icon name="ticket" class="absolute -right-3 -top-3 size-20 text-surface-2" />
                            <p class="font-display text-3xl font-extrabold tracking-tight">{{ $c->type === 'percent' ? rtrim(rtrim($c->value, '0'), '.').'% off' : '$'.number_format($c->value, 2).' off' }}</p>
                            <p class="mt-1 text-sm text-muted">{{ $c->description }}</p>
                            <p class="mt-4 inline-block rounded-lg border border-dashed border-ink px-3 py-1.5 font-mono text-sm font-bold tracking-widest">{{ $c->code }}</p>
                            <p class="mt-3 text-xs text-muted">@if($c->min_subtotal > 0)Min. spend ${{ number_format($c->min_subtotal, 2) }}. @endif @if($c->ends_at)Valid until {{ $c->ends_at->format('M j, Y') }}.@endif</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($popular->isNotEmpty())
            <section class="mt-16" aria-labelledby="popular-deals"><x-section-heading eyebrow="Popular deals" title="Most-viewed on sale" id="popular-deals" />
                <div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-4">@foreach($popular as $p)<x-product-card :product="$p" />@endforeach</div></section>
        @endif

        <section class="mt-16" aria-labelledby="all-sale">
            <x-section-heading eyebrow="All reduced prices" title="On sale now" id="all-sale" />
            @if($dealCategories->isNotEmpty())<p class="-mt-3 mb-6 flex flex-wrap items-center gap-2 text-sm text-muted">Deals in: @foreach($dealCategories as $c)<a class="chip" href="{{ route('categories.show', $c->slug) }}">{{ $c->name }}</a>@endforeach</p>@endif
            @if($onSale->isEmpty())<x-empty-state icon="tag" title="No reduced prices right now" text="Check back soon, or browse what's trending." :action="route('trending')" actionLabel="See trending" />
            @else<div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-4 md:gap-x-5">@foreach($onSale as $p)<x-product-card :product="$p" />@endforeach</div>@endif
        </section>
    </div>
</x-layouts.app>
