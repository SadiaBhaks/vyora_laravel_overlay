@props(['title' => 'Admin'])
@php
    $nav = [['admin.dashboard', 'Dashboard', 'home', false], ['admin.orders.index', 'Orders', 'package', false], ['admin.products.index', 'Products', 'tag', false], ['admin.inventory.index', 'Inventory', 'package', false], ['admin.categories.index', 'Categories', 'grid', false], ['admin.brands.index', 'Brands', 'tag', false], ['admin.collections.index', 'Collections', 'package', false], ['admin.coupons.index', 'Coupons', 'tag', false], ['admin.promotions.index', 'Promotions', 'zap', false], ['admin.customers.index', 'Customers', 'user', false], ['admin.reviews.index', 'Reviews', 'star', false], ['admin.newsletter.index', 'Newsletter', 'mail', false], ['admin.settings.edit', 'Settings', 'settings', true]];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"><meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} — VYORA Admin</title>
    <script>(function(){var m='system';try{m=localStorage.getItem('vyora-theme')||'system'}catch(e){}if(m==='dark'||(m==='system'&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark')})();window.vyoraBoot={!! json_encode(['cartCount' => 0, 'wishlist' => [], 'csrf' => csrf_token()]) !!};</script>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-bg" x-data="{ nav: false }">
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-ink focus:px-4 focus:py-2 focus:text-bg">Skip to content</a>
<div class="lg:grid lg:grid-cols-[15rem_1fr]">
    <aside class="border-b border-line lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto lg:border-b-0 lg:border-r" aria-label="Admin">
        <div class="flex items-center justify-between p-4 lg:p-5"><a href="{{ route('admin.dashboard') }}"><x-logo /></a>
            <button type="button" class="btn btn-ghost btn-sm lg:hidden" @click="nav = !nav" :aria-expanded="nav">Menu</button></div>
        <nav class="px-3 pb-6" :class="nav ? '' : 'hidden lg:block'">
            @foreach($nav as [$r, $label, $icon, $adminOnly])
                @continue($adminOnly && ! auth()->user()->isAdmin())
                <a href="{{ route($r) }}" @if(request()->routeIs(\Illuminate\Support\Str::beforeLast($r, '.').'.*')) aria-current="page" @endif
                   class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium {{ request()->routeIs(\Illuminate\Support\Str::beforeLast($r, '.').'.*') ? 'bg-ink text-bg' : 'text-muted hover:bg-surface-2 hover:text-ink' }}"><x-icon :name="$icon" class="size-4" />{{ $label }}</a>
            @endforeach
            <div class="mt-4 border-t border-line pt-4"><a href="{{ route('home') }}" class="block rounded-xl px-3 py-2 text-sm text-muted hover:bg-surface-2">View store</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-xl px-3 py-2 text-left text-sm text-muted hover:bg-surface-2">Sign out</button></form></div>
        </nav>
    </aside>
    <main id="main" class="min-w-0 p-4 md:p-8" tabindex="-1">
        @if(session('toast'))<p class="mb-5 rounded-xl border border-line bg-surface-2 p-3 text-sm" role="status">{{ session('toast') }}</p>@endif
        @if($errors->any())<div class="mb-5 rounded-xl border border-danger/50 bg-danger/10 p-3 text-sm" role="alert"><p class="font-semibold">Please fix the following:</p><ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        {{ $slot }}
    </main>
</div>
@livewireScripts
</body></html>
