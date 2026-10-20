<x-layouts.app title="Shop everything" description="Browse the full VYORA catalogue. Filter by category, brand, price, rating, colour and size.">
    <div class="mx-auto max-w-[88rem] px-4 pb-4 pt-8 md:px-8">
        <x-breadcrumbs :items="[['label' => 'Shop']]" />
        <h1 class="display-xl">Shop everything</h1>
        <p class="mt-3 max-w-xl text-muted">Every product, filterable by what matters to you. Share the page — your filters are in the link.</p>
    </div>
    <div class="mx-auto max-w-[88rem] px-4 md:px-8"><livewire:product-listing mode="shop" /></div>
</x-layouts.app>
