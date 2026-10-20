@props(['title' => null, 'description' => 'VYORA — Intelligent Commerce. Discover products that match your world.', 'canonical' => null, 'image' => null, 'type' => 'website', 'noindex' => false, 'jsonld' => null, 'main' => true])
@php
    $fullTitle = $title ? $title.' — VYORA' : 'VYORA — Intelligent Commerce. Discover differently.';
    $canonicalUrl = $canonical ?: url()->current();
    $ogImage = $image ?: route('art', ['seed' => 'vyora-og', 'c' => 'lifestyle', 'w' => 'wide']);
    $wishlistIds = array_map('intval', app(\App\Services\WishlistService::class)->ids());
    $cartCount = app(\App\Services\CartService::class)->count();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($description), 160) }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @if($noindex)<meta name="robots" content="noindex, nofollow">@endif
    <meta property="og:site_name" content="VYORA">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($description), 200) }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#f5f3ee" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0a0b09" media="(prefers-color-scheme: dark)">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='9' fill='%230f100d'/%3E%3Cpath d='M8 9.5 16 24l8-14.5' fill='none' stroke='%23f5f3ee' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'/%3E%3Ccircle cx='23.6' cy='9' r='2.7' fill='%23d7ff3f'/%3E%3C/svg%3E">
    @if($jsonld)<script type="application/ld+json">{!! json_encode($jsonld, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>@endif

    {{-- Apply the saved/system theme before first paint to avoid a flash. --}}
    <script>
        (function () {
            var m = 'system';
            try { m = localStorage.getItem('vyora-theme') || 'system'; } catch (e) {}
            var dark = m === 'dark' || (m === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) document.documentElement.classList.add('dark');
        })();
        window.vyoraBoot = {!! json_encode(['cartCount' => $cartCount, 'wishlist' => $wishlistIds, 'csrf' => csrf_token()], JSON_HEX_TAG | JSON_HEX_AMP) !!};
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{ $head ?? '' }}
</head>
<body x-data="{ search: false, menu: false, scrolled: false }"
      @scroll.window.passive="scrolled = window.scrollY > 24"
      @keydown.window="if ((($event.metaKey || $event.ctrlKey) && $event.key.toLowerCase() === 'k')) { $event.preventDefault(); search = true } else if ($event.key === '/' && !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); search = true }"
      class="min-h-screen pb-20 md:pb-0">
    <a href="#main" class="sr-only z-[100] rounded-lg bg-ink px-4 py-2 text-bg focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>

    @include('partials.header')

    @if($main)
        <main id="main" tabindex="-1">{{ $slot }}</main>
    @else
        {{ $slot }}
    @endif

    @include('partials.footer')
    @include('partials.mobile-nav')

    <livewire:cart-drawer />
    <livewire:quick-view />
    @include('partials.search')

    {{-- Toasts: visual + screen-reader announcements --}}
    <div class="pointer-events-none fixed inset-x-0 bottom-24 z-[90] flex flex-col items-center gap-2 px-4 md:bottom-6 md:items-end md:px-6" x-data
         x-init="@if(session('toast')) $store.toasts.push(@js(session('toast'))) @endif">
        <div class="sr-only" role="status" aria-live="polite" aria-atomic="true" x-text="$store.toasts.items.length ? $store.toasts.items[$store.toasts.items.length - 1].message : ''"></div>
        <template x-for="t in $store.toasts.items" :key="t.id">
            <div x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-3 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition duration-200" x-transition:leave-end="opacity-0"
                 class="pointer-events-auto flex max-w-sm items-center gap-3 rounded-xl border border-line bg-ink px-4 py-3 text-sm font-medium text-bg shadow-soft">
                <span class="grid size-5 shrink-0 place-items-center rounded-full" :class="t.type === 'error' ? 'bg-danger text-ink' : 'bg-accent text-accent-ink'">
                    <svg viewBox="0 0 24 24" class="size-3" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path x-show="t.type !== 'error'" d="M20 6 9 17l-5-5"/><path x-show="t.type === 'error'" d="M12 8v5M12 17h.01"/></svg>
                </span>
                <span x-text="t.message"></span>
                <button type="button" class="ml-1 opacity-60 hover:opacity-100" @click="$store.toasts.remove(t.id)" aria-label="Dismiss">✕</button>
            </div>
        </template>
    </div>

    @livewireScripts
    {{ $scripts ?? '' }}
</body>
</html>
