{{-- Thumb-reach tab bar for phones; the desktop header is not simply squeezed down. --}}
@php $on = fn ($pattern) => request()->routeIs($pattern) ? 'text-ink' : 'text-muted'; @endphp
<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-line glass pb-[env(safe-area-inset-bottom)] md:hidden" aria-label="Quick navigation">
    <ul class="mx-auto grid max-w-md grid-cols-5 items-end px-2 pt-1.5">
        <li><a href="{{ route('home') }}" class="flex flex-col items-center gap-0.5 py-1.5 text-[.65rem] font-medium {{ $on('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif><x-icon name="home" class="size-5" />Home</a></li>
        <li><a href="{{ route('shop') }}" class="flex flex-col items-center gap-0.5 py-1.5 text-[.65rem] font-medium {{ $on('shop') }}" @if(request()->routeIs('shop')) aria-current="page" @endif><x-icon name="grid" class="size-5" />Shop</a></li>
        <li class="-mt-5"><a href="{{ route('match') }}" class="mx-auto flex size-14 flex-col items-center justify-center rounded-full bg-accent text-accent-ink shadow-soft ring-4 ring-[var(--bg)]" aria-label="VYORA Match"><x-icon name="sparkles" class="size-6" /></a></li>
        <li><a href="{{ auth()->check() ? route('account.wishlist') : route('login') }}" class="relative flex flex-col items-center gap-0.5 py-1.5 text-[.65rem] font-medium {{ $on('account.wishlist') }}"><x-icon name="heart" class="size-5" />Saved
            <span x-data x-show="$store.wl.ids.length > 0" x-cloak x-text="$store.wl.ids.length" class="absolute right-3 top-0.5 grid min-w-4 place-items-center rounded-full bg-ink px-1 text-[.6rem] font-bold leading-4 text-bg"></span></a></li>
        <li><a href="{{ auth()->check() ? route('account.index') : route('login') }}" class="flex flex-col items-center gap-0.5 py-1.5 text-[.65rem] font-medium {{ $on('account.*') }}"><x-icon name="user" class="size-5" />Account</a></li>
    </ul>
</nav>
