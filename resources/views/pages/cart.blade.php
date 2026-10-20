<x-layouts.app title="Your cart" description="Review the items in your VYORA cart." :noindex="true">
    <div class="mx-auto max-w-[88rem] px-4 pt-8 md:px-8">
        <x-breadcrumbs :items="[['label' => 'Cart']]" />
        <h1 class="display-xl mb-10">Your cart</h1>
        <livewire:cart-page />
    </div>
</x-layouts.app>
