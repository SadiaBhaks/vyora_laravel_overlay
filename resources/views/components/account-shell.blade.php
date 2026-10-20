@props(['title', 'active' => 'index'])
@php
    $nav = [['index', 'Overview', 'home', route('account.index')], ['orders', 'Orders', 'package', route('account.orders')], ['wishlist', 'Wishlist', 'heart', route('account.wishlist')], ['addresses', 'Addresses', 'map-pin', route('account.addresses')], ['profile', 'Profile', 'user', route('account.profile')], ['settings', 'Settings', 'settings', route('account.settings')]];
@endphp
<x-layouts.app :title="$title" :noindex="true">
    <div class="mx-auto max-w-[88rem] px-4 pt-8 md:px-8">
        <div class="grid gap-8 lg:grid-cols-[15rem_1fr] lg:gap-12">
            <aside>
                <p class="eyebrow mb-1">My account</p>
                <p class="mb-5 font-display text-2xl font-bold">{{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}</p>
                <nav aria-label="Account" class="-mx-4 flex gap-1 overflow-x-auto px-4 scrollbar-none lg:mx-0 lg:flex-col lg:px-0">
                    @foreach($nav as [$key, $label, $icon, $url])
                        <a href="{{ $url }}" @if($active === $key) aria-current="page" @endif class="flex shrink-0 items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ $active === $key ? 'bg-ink text-bg' : 'text-muted hover:bg-surface-2 hover:text-ink' }}"><x-icon :name="$icon" class="size-4" />{{ $label }}</a>
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}" class="lg:mt-3">@csrf<button class="flex w-full shrink-0 items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-sm font-medium text-muted hover:bg-surface-2 hover:text-ink"><x-icon name="log-out" class="size-4" />Sign out</button></form>
                </nav>
            </aside>
            <div class="min-w-0">{{ $slot }}</div>
        </div>
    </div>
</x-layouts.app>
