@php
    $fCategories = \Illuminate\Support\Facades\Cache::remember('nav.categories', 60, fn () => \App\Models\Category::active()->get(['id', 'name', 'slug', 'tagline']));
    $fCollections = \Illuminate\Support\Facades\Cache::remember('nav.collections', 60, fn () => \App\Models\Collection::active()->get(['id', 'name', 'slug', 'tagline']));
    $demoData = (string) app(\App\Services\SettingsService::class)->get('demo_data', '0') === '1';
    $col = 'space-y-2.5 text-sm text-muted [&_a:hover]:text-ink [&_a:hover]:underline [&_a]:underline-offset-4';
@endphp
<footer class="mt-24 border-t border-line bg-surface-2/60">
    <div class="mx-auto max-w-[88rem] px-4 py-14 md:px-8">
        <div class="grid gap-12 lg:grid-cols-[1.3fr_2.7fr]">
            <div>
                <x-logo />
                <p class="mt-4 max-w-xs font-display text-2xl font-bold leading-tight tracking-tight">Discover differently.<br><span class="text-muted">A new era of shopping.</span></p>
                <form method="POST" action="{{ route('newsletter') }}" class="mt-6 max-w-sm" novalidate>
                    @csrf <input type="hidden" name="_form" value="newsletter">
                    <label for="footer-news" class="label">Get the VYORA edit</label>
                    <div class="flex gap-2">
                        <input id="footer-news" type="email" name="email" required autocomplete="email" placeholder="you@email.com" value="{{ old('_form') === 'newsletter' ? old('email') : '' }}" class="input !h-11" @if(old('_form') === 'newsletter' && $errors->has('email')) aria-invalid="true" @endif>
                        <button class="btn btn-primary !h-11 shrink-0">Subscribe</button>
                    </div>
                    @if(old('_form') === 'newsletter' && $errors->has('email'))<p class="field-error" role="alert">{{ $errors->first('email') }}</p>@endif
                </form>
                <div class="mt-6 flex gap-2">
                    @foreach(array_filter(config('vyora.social')) as $name => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="btn btn-ghost btn-icon" aria-label="VYORA on {{ $name === 'x' ? 'X' : ucfirst($name) }}"><x-icon :name="$name === 'x' ? 'twitter' : $name" class="size-[18px]" /></a>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:grid-cols-5">
                <div><h3 class="eyebrow mb-4 !font-sans">Shop</h3>
                    <ul class="{{ $col }}"><li><a href="{{ route('shop') }}">All products</a></li><li><a href="{{ route('new-arrivals') }}">New arrivals</a></li><li><a href="{{ route('trending') }}">Trending</a></li><li><a href="{{ route('deals') }}">Deals</a></li><li><a href="{{ route('match') }}">VYORA Match</a></li></ul></div>
                <div><h3 class="eyebrow mb-4 !font-sans">Categories</h3>
                    <ul class="{{ $col }}">@foreach($fCategories as $c)<li><a href="{{ route('categories.show', $c->slug) }}">{{ $c->name }}</a></li>@endforeach</ul></div>
                <div><h3 class="eyebrow mb-4 !font-sans">Collections</h3>
                    <ul class="{{ $col }}">@foreach($fCollections as $c)<li><a href="{{ route('collections.show', $c) }}">{{ $c->name }}</a></li>@endforeach</ul></div>
                <div><h3 class="eyebrow mb-4 !font-sans">About VYORA</h3>
                    <ul class="{{ $col }}"><li><a href="{{ route('about') }}">Our story</a></li><li><a href="{{ route('contact') }}">Contact</a></li><li><a href="{{ route('faq') }}">FAQ</a></li><li><a href="{{ route('shipping') }}">Shipping</a></li><li><a href="{{ route('returns') }}">Returns</a></li></ul></div>
                <div><h3 class="eyebrow mb-4 !font-sans">My account</h3>
                    <ul class="{{ $col }}">
                        @auth<li><a href="{{ route('account.index') }}">Dashboard</a></li><li><a href="{{ route('account.orders') }}">Orders</a></li><li><a href="{{ route('account.wishlist') }}">Wishlist</a></li>
                        @else<li><a href="{{ route('login') }}">Sign in</a></li><li><a href="{{ route('register') }}">Create account</a></li>@endauth
                    </ul></div>
            </div>
        </div>

        @if($demoData)
            <p class="mt-12 rounded-xl border border-dashed border-line px-4 py-3 text-xs text-muted">
                <strong class="font-semibold text-ink">Demonstration store.</strong> Products, prices, reviews, order counts and activity figures are seeded demo data, not real customer activity. Product artwork is generated illustration, not photography.
            </p>
        @endif

        <div class="mt-10 flex flex-col-reverse items-start justify-between gap-4 border-t border-line pt-6 text-xs text-muted sm:flex-row sm:items-center">
            <p>© {{ date('Y') }} VYORA — Intelligent Commerce. All rights reserved.</p>
            <ul class="flex gap-5"><li><a class="hover:text-ink hover:underline" href="{{ route('privacy') }}">Privacy Policy</a></li><li><a class="hover:text-ink hover:underline" href="{{ route('terms') }}">Terms of Service</a></li></ul>
        </div>
    </div>
</footer>
