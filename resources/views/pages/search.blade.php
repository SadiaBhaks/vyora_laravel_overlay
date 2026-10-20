<x-layouts.app :title="$q !== '' ? 'Search: '.$q : 'Search'" description="Search the VYORA catalogue by product, brand, category or tag." :noindex="true">
    <div class="mx-auto max-w-[88rem] px-4 pb-4 pt-8 md:px-8">
        <x-breadcrumbs :items="[['label' => 'Search']]" />
        <h1 class="display-xl text-balance">{{ $q !== '' ? 'Results for “'.$q.'”' : 'Search VYORA' }}</h1>
        <form action="{{ route('search') }}" method="GET" role="search" class="mt-6 flex max-w-xl gap-2">
            <label for="page-search" class="sr-only">Search</label>
            <input id="page-search" name="q" type="search" value="{{ $q }}" maxlength="80" placeholder="Search products, brands, tags…" class="input !h-12">
            <button class="btn btn-primary !h-12">Search</button>
        </form>
    </div>
    <div class="mx-auto max-w-[88rem] px-4 md:px-8">
        @if($q === '')
            <x-empty-state icon="search" title="Type something to search" text="Try a product, a brand, a category or a tag like “wireless”." :action="route('shop')" actionLabel="Browse all products" class="mt-8" />
        @else
            <livewire:product-listing mode="search" :q="$q" />
        @endif
    </div>
</x-layouts.app>
