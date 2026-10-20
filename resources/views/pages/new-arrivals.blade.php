<x-layouts.app title="New arrivals" description="The newest products at VYORA, updated as soon as they land.">
    <div class="mx-auto max-w-[88rem] px-4 pb-4 pt-8 md:px-8">
        <x-breadcrumbs :items="[['label' => 'New arrivals']]" />
        <p class="eyebrow mb-2">Just landed</p><h1 class="display-xl">New arrivals</h1>
    </div>
    <div class="mx-auto max-w-[88rem] px-4 md:px-8"><livewire:product-listing mode="new" /></div>
</x-layouts.app>
