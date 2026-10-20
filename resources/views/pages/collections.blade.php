<x-layouts.app title="Collections" description="Editorial collections from VYORA — Future Tech, Quiet Luxury, Everyday Essentials and more.">
    <div class="mx-auto max-w-[88rem] px-4 pt-8 md:px-8">
        <x-breadcrumbs :items="[['label' => 'Collections']]" />
        <p class="eyebrow mb-2">Curated</p><h1 class="display-xl">Collections</h1>
        <div class="mt-10 grid gap-5 md:grid-cols-2">
            @foreach($collections as $i => $c)
                <a href="{{ route('collections.show', $c) }}" class="group relative isolate flex min-h-[22rem] flex-col justify-end overflow-hidden rounded-[1.75rem] bg-surface-2 p-7 text-white {{ $i % 3 === 0 ? 'md:col-span-2 md:min-h-[28rem]' : '' }}">
                    <img src="{{ $c->artUrl($i + 1) }}" alt="" loading="lazy" width="1600" height="900" class="absolute inset-0 -z-10 size-full object-cover transition duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/75 via-black/15 to-transparent"></div>
                    <p class="eyebrow !text-white/70 tabular">{{ $c->products_count }} {{ \Illuminate\Support\Str::plural('piece', $c->products_count) }}</p>
                    <h2 class="display-lg mt-1 uppercase">{{ $c->name }}</h2><p class="mt-2 max-w-md text-white/80">{{ $c->tagline }}</p>
                    <div class="mt-5 flex -space-x-3">@foreach($c->products->take(4) as $p)<span class="size-12 overflow-hidden rounded-full border-2 border-white/80 bg-surface-2"><x-img :image="$p->images->first()" :alt="$p->name" :seed="$p->slug.'-1'" /></span>@endforeach</div>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.app>
