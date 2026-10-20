@php $ld = ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $collection->name, 'description' => $collection->description ?: $collection->tagline, 'url' => route('collections.show', $collection)]; @endphp
<x-layouts.app :title="$collection->name" :description="$collection->description ?: $collection->tagline" :jsonld="$ld">
    <section class="relative isolate overflow-hidden bg-ink text-bg">
        <img src="{{ $collection->artUrl(1) }}" alt="" class="absolute inset-0 -z-10 size-full object-cover opacity-50" width="1600" height="900">
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink via-ink/40 to-transparent"></div>
        <div class="mx-auto flex min-h-[26rem] max-w-[88rem] flex-col justify-end px-4 pb-12 pt-10 md:min-h-[34rem] md:px-8">
            <nav aria-label="Breadcrumb" class="mb-auto text-sm text-bg/70"><a class="hover:underline" href="{{ route('home') }}">Home</a> / <a class="hover:underline" href="{{ route('collections') }}">Collections</a> / <span class="text-bg">{{ $collection->name }}</span></nav>
            <p class="eyebrow !text-bg/60">Collection · {{ $products->count() }} {{ \Illuminate\Support\Str::plural('piece', $products->count()) }}</p>
            <h1 class="hero-title mt-2 !text-[clamp(2.6rem,9vw,7.5rem)]">{{ $collection->name }}</h1>
            @if($collection->tagline)<p class="mt-4 max-w-lg text-xl font-medium text-bg/85">{{ $collection->tagline }}</p>@endif
        </div>
    </section>

    <div class="mx-auto max-w-[88rem] px-4 md:px-8">
        @if($collection->description)<div class="mx-auto mt-14 max-w-3xl text-center"><p class="font-display text-2xl font-medium leading-snug tracking-tight text-balance md:text-3xl">{{ $collection->description }}</p></div>@endif

        <section class="mt-16" aria-labelledby="sel-title">
            <x-section-heading eyebrow="The selection" title="Pieces in this collection" id="sel-title" />
            @if($products->isEmpty())<x-empty-state icon="package" title="This collection is being curated" text="Products are coming soon." :action="route('shop')" actionLabel="Browse the shop" />
            @else
                <div class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4 md:gap-x-5">
                    @foreach($products as $i => $p)
                        <div class="{{ $i % 5 === 0 ? 'md:row-span-1 md:mt-0' : ($i % 2 ? 'md:mt-12' : '') }}"><x-product-card :product="$p" /></div>
                    @endforeach
                </div>
            @endif
        </section>

        @if($complementary->isNotEmpty())
            <section class="mt-24" aria-labelledby="comp-title"><x-section-heading eyebrow="Complete the set" title="Goes well with this" id="comp-title" />
                <div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-4">@foreach($complementary as $p)<x-product-card :product="$p" />@endforeach</div></section>
        @endif

        @if($others->isNotEmpty())
            <section class="mt-24" aria-labelledby="more-col"><x-section-heading eyebrow="Keep exploring" title="More collections" :link="route('collections')" id="more-col" />
                <div class="grid gap-4 md:grid-cols-3">@foreach($others as $i => $c)
                    <a href="{{ route('collections.show', $c) }}" class="group relative isolate flex min-h-[14rem] flex-col justify-end overflow-hidden rounded-2xl p-5 text-white"><img src="{{ $c->artUrl($i + 2) }}" alt="" loading="lazy" class="absolute inset-0 -z-10 size-full object-cover transition duration-700 group-hover:scale-105"><div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/70 to-transparent"></div><h3 class="font-display text-2xl font-bold">{{ $c->name }}</h3><p class="text-sm text-white/80">{{ $c->tagline }}</p></a>
                @endforeach</div></section>
        @endif
    </div>
</x-layouts.app>
