@php
    $navCategories = \Illuminate\Support\Facades\Cache::remember('nav.categories', 60, fn () => \App\Models\Category::active()->get(['id', 'name', 'slug', 'tagline']));
    $navCollections = \Illuminate\Support\Facades\Cache::remember('nav.collections', 60, fn () => \App\Models\Collection::active()->get(['id', 'name', 'slug', 'tagline']));
    $threshold = (float) config('vyora.free_shipping_threshold');
    $link = fn ($active) => 'relative px-3 py-2 text-sm font-medium transition hover:text-ink '.($active ? 'text-ink' : 'text-muted');
@endphp

@if($threshold > 0)
    <div class="bg-ink text-bg">
        <p class="mx-auto flex max-w-[88rem] items-center justify-center gap-2 px-4 py-2 text-center text-xs font-medium tracking-wide">
            <x-icon name="truck" class="size-3.5 text-accent" /> Free standard shipping on orders over ${{ number_format($threshold, 0) }}
        </p>
    </div>
@endif

<header class="sticky top-0 z-50 transition-all duration-300" :class="scrolled ? 'glass border-b border-line shadow-[0_8px_30px_-20px_rgb(0_0_0/.35)]' : 'bg-bg border-b border-transparent'">
    <div class="mx-auto flex max-w-[88rem] items-center gap-3 px-4 transition-all duration-300 md:px-8" :class="scrolled ? 'h-14' : 'h-[4.5rem]'">
        <a href="{{ route('home') }}" class="shrink-0" aria-label="VYORA home"><x-logo /></a>

        {{-- Desktop navigation --}}
        <nav class="ml-6 hidden items-center lg:flex" aria-label="Primary">
            <a href="{{ route('shop') }}" class="{{ $link(request()->routeIs('shop')) }}" @if(request()->routeIs('shop')) aria-current="page" @endif>Shop</a>

            <div class="relative" x-data="{ open: false }" @mouseleave="open = false" @keydown.escape="open = false">
                <button type="button" class="{{ $link(request()->routeIs('categories*')) }} inline-flex items-center gap-1" @mouseenter="open = true" @click="open = !open" :aria-expanded="open" aria-haspopup="true">
                    Categories <x-icon name="chevron-down" class="size-3.5 transition" ::class="open && 'rotate-180'" />
                </button>
                <div x-show="open" x-cloak x-transition.origin.top.duration.150ms class="absolute left-0 top-full z-50 w-[30rem] pt-2">
                    <div class="card grid grid-cols-2 gap-1 p-3 shadow-soft">
                        @foreach($navCategories as $c)
                            <a href="{{ route('categories.show', $c->slug) }}" class="group flex items-center gap-3 rounded-lg p-2 hover:bg-surface-2">
                                <span class="size-11 shrink-0 overflow-hidden rounded-lg bg-surface-2"><img src="{{ route('art', ['seed' => $c->slug.'-nav', 'c' => $c->slug]) }}" alt="" width="44" height="44" loading="lazy" class="size-full object-cover"></span>
                                <span><span class="block text-sm font-semibold">{{ $c->name }}</span><span class="line-clamp-1 text-xs text-muted">{{ $c->tagline }}</span></span>
                            </a>
                        @endforeach
                        <a href="{{ route('categories') }}" class="col-span-2 mt-1 flex items-center justify-between rounded-lg bg-surface-2 px-3 py-2 text-sm font-semibold">All categories <x-icon name="arrow-right" class="size-4" /></a>
                    </div>
                </div>
            </div>

            <a href="{{ route('new-arrivals') }}" class="{{ $link(request()->routeIs('new-arrivals')) }}" @if(request()->routeIs('new-arrivals')) aria-current="page" @endif>New Arrivals</a>
            <a href="{{ route('trending') }}" class="{{ $link(request()->routeIs('trending')) }}" @if(request()->routeIs('trending')) aria-current="page" @endif>Trending</a>

            <div class="relative" x-data="{ open: false }" @mouseleave="open = false" @keydown.escape="open = false">
                <button type="button" class="{{ $link(request()->routeIs('collections*')) }} inline-flex items-center gap-1" @mouseenter="open = true" @click="open = !open" :aria-expanded="open" aria-haspopup="true">
                    Collections <x-icon name="chevron-down" class="size-3.5 transition" ::class="open && 'rotate-180'" />
                </button>
                <div x-show="open" x-cloak x-transition.origin.top.duration.150ms class="absolute left-0 top-full z-50 w-72 pt-2">
                    <div class="card p-2 shadow-soft">
                        @foreach($navCollections as $c)
                            <a href="{{ route('collections.show', $c) }}" class="block rounded-lg px-3 py-2 hover:bg-surface-2"><span class="block text-sm font-semibold">{{ $c->name }}</span><span class="line-clamp-1 text-xs text-muted">{{ $c->tagline }}</span></a>
                        @endforeach
                        <a href="{{ route('collections') }}" class="mt-1 flex items-center justify-between rounded-lg bg-surface-2 px-3 py-2 text-sm font-semibold">All collections <x-icon name="arrow-right" class="size-4" /></a>
                    </div>
                </div>
            </div>

            <a href="{{ route('deals') }}" class="{{ $link(request()->routeIs('deals')) }}" @if(request()->routeIs('deals')) aria-current="page" @endif>Deals</a>
            <a href="{{ route('match') }}" class="ml-2 inline-flex items-center gap-1.5 rounded-full bg-accent px-3.5 py-1.5 text-sm font-semibold text-accent-ink transition hover:shadow-[0_0_0_4px_color-mix(in_srgb,var(--accent)_35%,transparent)]">
                <x-icon name="sparkles" class="size-4" /> Match
            </a>
        </nav>

        <div class="ml-auto flex items-center gap-1">
            <button type="button" @click="search = true" class="btn btn-ghost btn-icon border-transparent" aria-label="Search (press / or Ctrl K)"><x-icon name="search" /></button>

            <button type="button" @click="$store.theme.toggle()" class="btn btn-ghost btn-icon hidden border-transparent sm:inline-flex" aria-label="Toggle light and dark theme">
                <x-icon name="moon" class="dark:hidden" /><x-icon name="sun" class="hidden dark:block" />
            </button>

            <a href="{{ auth()->check() ? route('account.wishlist') : route('login') }}" class="btn btn-ghost btn-icon relative hidden border-transparent sm:inline-flex" aria-label="Wishlist">
                <x-icon name="heart" />
                <span x-data x-show="$store.wl.ids.length > 0" x-cloak x-text="$store.wl.ids.length" class="absolute -right-0.5 -top-0.5 grid min-w-[1.1rem] place-items-center rounded-full bg-ink px-1 text-[.65rem] font-bold leading-[1.1rem] text-bg tabular"></span>
            </a>

            {{-- Account menu --}}
            <div class="relative hidden lg:block" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
                <button type="button" @click="open = !open" class="btn btn-ghost btn-icon border-transparent" :aria-expanded="open" aria-haspopup="menu" aria-label="Account menu"><x-icon name="user" /></button>
                <div x-show="open" x-cloak x-transition.origin.top.right.duration.150ms class="absolute right-0 top-full z-50 mt-2 w-60">
                    <div class="card p-2 shadow-soft" role="menu">
                        @auth
                            <div class="px-3 pb-2 pt-1.5"><p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p><p class="truncate text-xs text-muted">{{ auth()->user()->email }}</p></div>
                            <a role="menuitem" href="{{ route('account.index') }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-surface-2">My account</a>
                            <a role="menuitem" href="{{ route('account.orders') }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-surface-2">Orders</a>
                            <a role="menuitem" href="{{ route('account.wishlist') }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-surface-2">Wishlist</a>
                            @if(auth()->user()->isStaff())<a role="menuitem" href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-surface-2">Admin dashboard</a>@endif
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-line pt-1">@csrf<button role="menuitem" class="block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-surface-2">Sign out</button></form>
                        @else
                            <a role="menuitem" href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold hover:bg-surface-2">Sign in</a>
                            <a role="menuitem" href="{{ route('register') }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-surface-2">Create an account</a>
                        @endauth
                    </div>
                </div>
            </div>

            <button type="button" @click="Livewire.dispatch('cart-open')" class="btn btn-primary relative ml-1 !h-10 !px-4" aria-label="Open cart">
                <x-icon name="bag" class="size-[18px]" />
                <span class="hidden sm:inline">Cart</span>
                <span x-data x-text="$store.cart.count" class="grid min-w-[1.25rem] place-items-center rounded-full bg-accent px-1 text-[.7rem] font-bold leading-5 text-accent-ink tabular"></span>
            </button>

            <button type="button" @click="menu = true" class="btn btn-ghost btn-icon border-transparent lg:hidden" aria-label="Open menu"><x-icon name="menu" /></button>
        </div>
    </div>

    {{-- Mobile menu sheet --}}
    <div x-show="menu" x-cloak class="fixed inset-0 z-[70] lg:hidden" role="dialog" aria-modal="true" aria-label="Menu" @keydown.escape.window="menu = false">
        <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" @click="menu = false" x-transition.opacity></div>
        <div class="absolute inset-y-0 right-0 flex w-[88%] max-w-sm flex-col bg-bg shadow-2xl" x-show="menu" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" x-trap.noscroll="menu">
            <div class="flex h-16 items-center justify-between px-5"><x-logo /><button type="button" @click="menu = false" class="btn btn-ghost btn-icon border-transparent" aria-label="Close menu"><x-icon name="x" /></button></div>
            <nav class="flex-1 overflow-y-auto px-5 pb-8" aria-label="Mobile">
                <ul class="divide-y divide-line font-display text-3xl font-bold tracking-tight">
                    @foreach([['Shop', route('shop')], ['New Arrivals', route('new-arrivals')], ['Trending', route('trending')], ['Collections', route('collections')], ['Deals', route('deals')], ['Categories', route('categories')]] as [$label, $url])
                        <li><a href="{{ $url }}" class="flex items-center justify-between py-3.5">{{ $label }} <x-icon name="arrow-up-right" class="size-5 text-muted" /></a></li>
                    @endforeach
                </ul>
                <a href="{{ route('match') }}" class="btn btn-accent btn-lg mt-6 w-full"><x-icon name="sparkles" class="size-5" /> Discover your match</a>
                <div class="mt-8 grid gap-1 text-sm">
                    @auth
                        <a class="rounded-lg px-3 py-2.5 hover:bg-surface-2" href="{{ route('account.index') }}">My account</a>
                        <a class="rounded-lg px-3 py-2.5 hover:bg-surface-2" href="{{ route('account.orders') }}">Orders</a>
                        @if(auth()->user()->isStaff())<a class="rounded-lg px-3 py-2.5 font-semibold hover:bg-surface-2" href="{{ route('admin.dashboard') }}">Admin dashboard</a>@endif
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-lg px-3 py-2.5 text-left hover:bg-surface-2">Sign out</button></form>
                    @else
                        <a class="rounded-lg px-3 py-2.5 font-semibold hover:bg-surface-2" href="{{ route('login') }}">Sign in</a>
                        <a class="rounded-lg px-3 py-2.5 hover:bg-surface-2" href="{{ route('register') }}">Create an account</a>
                    @endauth
                    <button type="button" @click="$store.theme.toggle()" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-left hover:bg-surface-2"><x-icon name="moon" class="size-4 dark:hidden" /><x-icon name="sun" class="hidden size-4 dark:block" /> Toggle theme</button>
                </div>
            </nav>
        </div>
    </div>
</header>
