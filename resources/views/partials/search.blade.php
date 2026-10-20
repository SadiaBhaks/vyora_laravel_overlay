<div x-show="search" x-cloak class="fixed inset-0 z-[80]" role="dialog" aria-modal="true" aria-label="Search" @keydown.escape.window="search = false">
    <div class="absolute inset-0 bg-bg/90 backdrop-blur-xl" x-show="search" x-transition.opacity.duration.200ms @click="search = false"></div>
    <div class="relative mx-auto h-full max-w-4xl overflow-y-auto px-4 pb-16 pt-6 md:px-8 md:pt-12"
         x-show="search" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="-translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
         x-trap.noscroll="search">
        <livewire:search-overlay />
    </div>
</div>
