@php
    $ld = ['@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => $category->name, 'description' => $category->description ?: $category->tagline, 'url' => route('categories.show', $category->slug)];
@endphp
<x-layouts.app :title="$category->name" :description="$category->description ?: ('Shop '.$category->name.' at VYORA.')" :jsonld="$ld" :canonical="route('categories.show', $category->slug)">
    <section class="relative isolate overflow-hidden">
        <img src="{{ $category->artUrl(1) }}" alt="" class="absolute inset-0 -z-10 size-full object-cover opacity-30 dark:opacity-20" width="800" height="800">
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-transparent to-[var(--bg)]"></div>
        <div class="mx-auto max-w-[88rem] px-4 pb-10 pt-8 md:px-8 md:pb-14">
            <x-breadcrumbs :items="[['label' => 'Categories', 'url' => route('categories')], ['label' => $category->name]]" />
            <h1 class="display-xl">{{ $category->name }}</h1>
            @if($category->tagline)<p class="mt-3 text-lg font-medium">{{ $category->tagline }}</p>@endif
            @if($category->description)<p class="mt-2 max-w-xl text-muted">{{ $category->description }}</p>@endif
        </div>
    </section>
    <div class="mx-auto max-w-[88rem] px-4 md:px-8"><livewire:product-listing mode="category" :scope="$category->slug" /></div>
</x-layouts.app>
