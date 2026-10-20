<x-layouts.app title="Categories" description="Shop VYORA by category: fashion, electronics, accessories, beauty, home, fitness, lifestyle and smart living.">
    <div class="mx-auto max-w-[88rem] px-4 pt-8 md:px-8">
        <x-breadcrumbs :items="[['label' => 'Categories']]" />
        <h1 class="display-xl">Categories</h1>
        <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach($categories as $i => $c)
                <a href="{{ route('categories.show', $c->slug) }}" class="group card overflow-hidden transition hover:border-ink">
                    <div class="aspect-[4/3] overflow-hidden bg-surface-2"><img src="{{ $c->artUrl($i + 1) }}" alt="" loading="lazy" width="800" height="800" class="size-full object-cover transition duration-700 group-hover:scale-105"></div>
                    <div class="p-5"><div class="flex items-center justify-between"><h2 class="font-display text-2xl font-bold">{{ $c->name }}</h2><x-icon name="arrow-up-right" class="size-5 text-muted transition group-hover:text-ink" /></div>
                        <p class="mt-1 text-sm text-muted">{{ $c->tagline }}</p><p class="mt-3 text-xs font-semibold uppercase tracking-widest text-muted tabular">{{ $c->products_count }} {{ \Illuminate\Support\Str::plural('product', $c->products_count) }}</p></div>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.app>
