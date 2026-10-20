@php
    $hero = $trending->take(3)->values();
    $h1 = $hero->get(0); $h2 = $hero->get(1); $h3 = $hero->get(2);
    $wrap = 'mx-auto max-w-[88rem] px-4 md:px-8';
    $bento = [
        0 => 'col-span-2 row-span-2 md:col-span-2 md:row-span-2',
        1 => 'col-span-1 row-span-1',
        2 => 'col-span-1 row-span-2',
        3 => 'col-span-1 row-span-1 md:col-span-2',
        4 => 'col-span-1 row-span-1',
        5 => 'col-span-1 row-span-2 md:col-span-1',
        6 => 'col-span-2 row-span-1 md:col-span-2',
        7 => 'col-span-1 row-span-1',
    ];
    $ld = ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => 'VYORA', 'url' => url('/'),
           'potentialAction' => ['@type' => 'SearchAction', 'target' => route('search').'?q={search_term_string}', 'query-input' => 'required name=search_term_string']];
@endphp
<x-layouts.app :main="false" description="Discover products that match your world. Explore better, shop smarter, and find your next favorite — VYORA, Intelligent Commerce." :jsonld="$ld">
<main id="main" tabindex="-1">

    {{-- ============================ HERO ============================ --}}
    <section class="relative overflow-hidden" aria-labelledby="hero-title">
        <div class="pointer-events-none absolute -right-40 -top-40 size-[44rem] rounded-full bg-accent/25 blur-3xl dark:bg-accent/10" aria-hidden="true"></div>
        <div class="{{ $wrap }} relative grid items-center gap-10 pb-16 pt-8 md:pt-14 lg:grid-cols-[1.15fr_1fr] lg:pb-24">
            <div class="animate-rise">
                <p class="eyebrow mb-6 inline-flex items-center gap-2"><span class="size-2 rounded-full bg-accent ring-2 ring-ink/10"></span> VYORA — Intelligent Commerce</p>
                <h1 id="hero-title" class="hero-title">Shop<br>what's<br><span class="relative inline-block"><span class="relative z-10 text-accent-ink">next.</span><span class="absolute inset-x-[-.04em] bottom-[.06em] top-[.2em] -rotate-1 rounded-md bg-accent" aria-hidden="true"></span></span></h1>
                <p class="mt-8 max-w-md text-lg text-muted text-balance">Discover products that match your world. Explore better, shop smarter, and find your next favorite.</p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('shop') }}" class="btn btn-primary btn-lg">Explore Collection <x-icon name="arrow-right" class="size-5" /></a>
                    <a href="{{ route('match') }}" class="btn btn-ghost btn-lg"><x-icon name="sparkles" class="size-5" /> Discover Your Match</a>
                </div>
                <dl class="mt-12 flex flex-wrap gap-x-10 gap-y-4 text-sm">
                    <div><dt class="eyebrow">In the catalogue</dt><dd class="font-display text-2xl font-bold tabular">{{ $categories->sum('products_count') }} products</dd></div>
                    <div><dt class="eyebrow">Across</dt><dd class="font-display text-2xl font-bold tabular">{{ $categories->count() }} categories</dd></div>
                </dl>
            </div>

            {{-- Layered composition (desktop) --}}
            <div class="relative mx-auto hidden aspect-[4/5] w-full max-w-xl lg:block" aria-hidden="true">
                <div class="absolute right-0 top-0 h-[78%] w-[66%] rotate-[3deg] overflow-hidden rounded-[1.75rem] bg-surface-2 shadow-soft"><x-img :image="$h1?->images->first()" alt="" seed="hero-1" :cat="$h1?->category?->slug ?? 'fashion'" :eager="true" /></div>
                <div class="absolute bottom-[4%] left-0 h-[46%] w-[48%] -rotate-[4deg] overflow-hidden rounded-[1.75rem] border-4 border-bg bg-surface-2 shadow-soft"><x-img :image="$h2?->images->first()" alt="" seed="hero-2" :cat="$h2?->category?->slug ?? 'electronics'" :eager="true" /></div>
                <div class="absolute bottom-0 right-[6%] size-[34%] overflow-hidden rounded-full border-4 border-bg bg-surface-2 shadow-soft"><x-img :image="$h3?->images->first()" alt="" seed="hero-3" :cat="$h3?->category?->slug ?? 'home'" /></div>

                @if($h1)
                    <a href="{{ route('product.show', $h1) }}" tabindex="-1" class="animate-floaty card absolute left-[2%] top-[10%] flex items-center gap-3 p-3 pr-5 shadow-soft backdrop-blur">
                        <span class="grid size-10 place-items-center rounded-lg bg-accent text-accent-ink"><x-icon name="flame" class="size-5" /></span>
                        <span><span class="eyebrow block !text-[.6rem]">Trending now</span><span class="block max-w-[10rem] truncate text-sm font-semibold">{{ $h1->name }}</span><span class="text-xs text-muted tabular">${{ number_format((float) $h1->effective_price, 2) }}</span></span>
                    </a>
                @endif
                @if($h2)
                    <div class="animate-floaty-slow card absolute bottom-[36%] right-[-3%] flex items-center gap-2 px-4 py-3 shadow-soft backdrop-blur">
                        <x-rating :value="$h2->rating_avg" :count="$h2->rating_count" :showCount="false" />
                        <span class="text-xs font-semibold tabular">{{ $h2->rating_count ? number_format($h2->rating_avg, 1) : 'New' }}</span>
                    </div>
                @endif
            </div>

            {{-- Mobile composition: a compact bento, not the desktop collage shrunk --}}
            <div class="grid grid-cols-5 gap-2.5 lg:hidden" aria-hidden="true">
                <div class="col-span-3 row-span-2 aspect-[4/5] overflow-hidden rounded-3xl bg-surface-2"><x-img :image="$h1?->images->first()" alt="" seed="hero-1" :cat="$h1?->category?->slug ?? 'fashion'" :eager="true" /></div>
                <div class="col-span-2 aspect-square overflow-hidden rounded-3xl bg-surface-2"><x-img :image="$h2?->images->first()" alt="" seed="hero-2" :cat="$h2?->category?->slug ?? 'electronics'" /></div>
                <div class="col-span-2 aspect-square overflow-hidden rounded-full bg-surface-2"><x-img :image="$h3?->images->first()" alt="" seed="hero-3" :cat="$h3?->category?->slug ?? 'home'" /></div>
            </div>
        </div>

        {{-- Ticker --}}
        <div class="border-y border-line bg-ink py-3 text-bg" aria-hidden="true">
            <div class="overflow-hidden"><div class="marquee-track font-display text-sm font-semibold uppercase tracking-[.2em]">
                @for($i = 0; $i < 2; $i++)@foreach($categories as $c)<span class="mx-6 inline-flex items-center gap-6">{{ $c->name }} <span class="size-1.5 rounded-full bg-accent"></span></span>@endforeach @endfor
            </div></div>
        </div>
    </section>

    {{-- ============================ CATEGORIES (bento) ============================ --}}
    <section class="{{ $wrap }} mt-20 md:mt-28" aria-labelledby="cats-title">
        <x-section-heading eyebrow="Trending categories" title="Find your corner of the world" :link="route('categories')" linkLabel="All categories" id="cats-title" />
        <div class="grid auto-rows-[9.5rem] grid-cols-2 gap-3 md:auto-rows-[11rem] md:grid-cols-4 md:gap-4">
            @foreach($categories as $i => $c)
                <a href="{{ route('categories.show', $c->slug) }}" class="group relative isolate overflow-hidden rounded-[1.25rem] bg-surface-2 {{ $bento[$i % 8] }}">
                    <img src="{{ $c->artUrl($i + 1) }}" alt="" loading="lazy" width="800" height="800" class="absolute inset-0 -z-10 size-full object-cover transition duration-700 [transition-timing-function:var(--ease-vyora)] group-hover:scale-105">
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/55 via-black/5 to-transparent"></div>
                    <div class="flex h-full flex-col justify-end p-4 text-white md:p-5">
                        <span class="font-display text-xl font-bold leading-tight md:text-2xl">{{ $c->name }}</span>
                        <span class="mt-0.5 flex items-center justify-between text-xs text-white/80"><span>{{ $c->products_count }} {{ \Illuminate\Support\Str::plural('product', $c->products_count) }}</span><x-icon name="arrow-up-right" class="size-4 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" /></span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============================ NEW ARRIVALS ============================ --}}
    <section class="{{ $wrap }} mt-20 md:mt-28" aria-labelledby="new-title">
        <x-section-heading eyebrow="Just landed" title="New arrivals" :link="route('new-arrivals')" id="new-title" />
        <div class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 scrollbar-none md:mx-0 md:grid md:grid-cols-4 md:gap-x-5 md:gap-y-10 md:overflow-visible md:px-0">
            @foreach($newArrivals as $p)
                <x-product-card :product="$p" class="w-[68%] shrink-0 snap-start sm:w-[44%] md:w-auto" />
            @endforeach
        </div>
    </section>

    {{-- ============================ VYORA MATCH ============================ --}}
    <section class="{{ $wrap }} mt-20 md:mt-28" aria-labelledby="match-title">
        <div class="relative isolate overflow-hidden rounded-[2rem] bg-ink p-8 text-bg md:p-14">
            <div class="absolute -right-24 -top-24 -z-10 size-96 rounded-full bg-accent/20 blur-3xl" aria-hidden="true"></div>
            <div class="grid items-center gap-10 lg:grid-cols-[1.2fr_1fr]">
                <div>
                    <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-bg/20 px-3 py-1 text-xs font-semibold uppercase tracking-widest"><x-icon name="sparkles" class="size-3.5 text-accent" /> Signature feature</p>
                    <h2 id="match-title" class="display-xl text-balance uppercase">Vyora <span class="text-accent">Match</span></h2>
                    <p class="mt-5 max-w-lg text-lg text-bg/70">Five quick questions. We score the live catalogue against your category, budget, style and must-have features — and tell you exactly why each pick fits.</p>
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ route('match') }}" class="btn btn-accent btn-lg">Discover your match <x-icon name="arrow-right" class="size-5" /></a>
                        <span class="text-xs text-bg/60">Takes about 30 seconds · rule-based, not AI</span>
                    </div>
                </div>
                <ol class="grid gap-3 text-sm" aria-label="How Match works">
                    @foreach([['What are you shopping for?', 'Category'], ['What\'s your budget?', 'Price range'], ['Which style feels like you?', 'Minimal · Premium · Sporty · Futuristic · Practical · Sustainable'], ['Which features matter most?', 'Wireless, portable, long battery…'], ['For you, or a gift?', 'Tunes the ranking']] as $n => [$q, $hint])
                        <li class="flex items-start gap-4 rounded-2xl border border-bg/15 bg-bg/5 p-4"><span class="font-display text-2xl font-extrabold text-accent tabular">0{{ $n + 1 }}</span><span><strong class="block text-base">{{ $q }}</strong><span class="text-bg/60">{{ $hint }}</span></span></li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- ============================ TRENDING NOW ============================ --}}
    <section class="{{ $wrap }} mt-20 md:mt-28" aria-labelledby="trend-title">
        <x-section-heading eyebrow="Trending now" title="What everyone's looking at" :link="route('trending')" id="trend-title" />
        <div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-4 md:gap-x-5">
            @foreach($trending->take(8) as $i => $p)<x-product-card :product="$p" :trending="true" :rank="$i + 1" />@endforeach
        </div>
        <p class="mt-6 text-xs text-muted">Ranked from recorded product views in the last 14 days, sales and review quality.</p>
    </section>

    {{-- ============================ COLLECTIONS ============================ --}}
    @if($collections->isNotEmpty())
    <section class="{{ $wrap }} mt-20 md:mt-28" aria-labelledby="col-title">
        <x-section-heading eyebrow="Curated collections" title="Edited like a magazine" :link="route('collections')" linkLabel="All collections" id="col-title" />
        <div class="grid gap-4 md:grid-cols-6">
            @foreach($collections as $i => $c)
                @php $span = match ($i % 7) { 0 => 'md:col-span-4 md:row-span-2 min-h-[22rem]', 1 => 'md:col-span-2', 2 => 'md:col-span-2', 3 => 'md:col-span-3', 4 => 'md:col-span-3', default => 'md:col-span-2' }; @endphp
                <a href="{{ route('collections.show', $c) }}" class="group relative isolate flex min-h-[15rem] flex-col justify-end overflow-hidden rounded-[1.5rem] bg-surface-2 p-6 text-white {{ $span }}">
                    <img src="{{ $c->artUrl($i + 1, 'lifestyle') }}" alt="" loading="lazy" width="1600" height="900" class="absolute inset-0 -z-10 size-full object-cover transition duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
                    <div class="absolute right-5 top-5 flex -space-x-3">@foreach($c->products->take(3) as $cp)<span class="size-11 overflow-hidden rounded-full border-2 border-white/80 bg-surface-2"><x-img :image="$cp->images->first()" :alt="$cp->name" :seed="$cp->slug.'-1'" /></span>@endforeach</div>
                    <h3 class="font-display text-3xl font-extrabold leading-none tracking-tight md:text-4xl">{{ $c->name }}</h3>
                    <p class="mt-2 max-w-sm text-sm text-white/80">{{ $c->tagline }}</p>
                    <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold">Explore <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" /></span>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ============================ DEALS ============================ --}}
    @if($deals->isNotEmpty())
    <section class="mt-20 bg-surface-2/70 py-16 md:mt-28 md:py-20" aria-labelledby="deals-title">
        <div class="{{ $wrap }}">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-6">
                <div><p class="eyebrow mb-2">Exclusive deals</p><h2 id="deals-title" class="display-lg">{{ $promotion?->name ?? 'Prices worth a second look' }}</h2>
                    @if($promotion)<p class="mt-2 text-sm text-muted">{{ $promotion->discount_percent }}% off selected products.</p>@endif</div>
                @if($promotion)<x-countdown :end="$promotion->ends_at" label="Ends in" />@endif
            </div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-4 md:gap-x-5">@foreach($deals as $p)<x-product-card :product="$p" />@endforeach</div>
            <div class="mt-10 text-center"><a href="{{ route('deals') }}" class="btn btn-primary">See all deals</a></div>
        </div>
    </section>
    @endif

    {{-- ============================ PERSONALISED ============================ --}}
    <section class="{{ $wrap }} mt-20 md:mt-28" aria-labelledby="for-you-title">
        <x-section-heading :eyebrow="$personalised ? 'Based on your interests and browsing' : 'Popular right now'" :title="$personalised ? (auth()->check() ? 'Picked for you, '.explode(' ', auth()->user()->name)[0] : 'Picked for you') : 'A good place to start'" id="for-you-title" />
        <div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-4 md:gap-x-5">@foreach($personal->take(8) as $p)<x-product-card :product="$p" />@endforeach</div>
        @unless($personalised)<p class="mt-6 text-sm text-muted">Browse a few products{{ auth()->guest() ? ' or create an account' : '' }} and this row will tailor itself to you.</p>@endunless
    </section>

    @if($recent->isNotEmpty())
    <section class="{{ $wrap }} mt-20 md:mt-28" aria-labelledby="recent-title">
        <x-section-heading eyebrow="Pick up where you left off" title="Recently viewed" id="recent-title" />
        <div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-6 md:gap-x-4">@foreach($recent as $p)<x-product-card :product="$p" />@endforeach</div>
    </section>
    @endif

    {{-- ============================ REGISTRATION ============================ --}}
    <section class="{{ $wrap }} mt-20 md:mt-28" aria-labelledby="join-title" id="join">
        <div class="grid overflow-hidden rounded-[2rem] border border-line bg-surface lg:grid-cols-[1fr_1.05fr]">
            <div class="relative isolate flex flex-col justify-between overflow-hidden bg-accent p-8 text-accent-ink md:p-12">
                <img src="{{ route('art', ['seed' => 'join-panel', 'c' => 'lifestyle', 'w' => 'tall']) }}" alt="" class="absolute inset-0 -z-10 size-full object-cover opacity-25 mix-blend-multiply" loading="lazy" width="800" height="1000">
                <div>
                    <p class="eyebrow !text-accent-ink/70">Join VYORA</p>
                    <h2 id="join-title" class="display-xl mt-3 uppercase text-balance">Your world.<br>Your picks.<br>Your VYORA.</h2>
                </div>
                <div>
                    <p class="mt-8 max-w-sm text-lg font-medium leading-snug">Create your VYORA account to discover products tailored to your style, save your favorites, and keep every order within reach.</p>
                    <ul class="mt-6 space-y-2 text-sm font-semibold">@foreach(['Personalised recommendations', 'Wishlist that follows you everywhere', 'Order tracking and saved addresses'] as $b)<li class="flex items-center gap-2"><x-icon name="check" class="size-4" :stroke="3" /> {{ $b }}</li>@endforeach</ul>
                </div>
            </div>

            <div class="p-8 md:p-12">
                @auth
                    <div class="flex h-full flex-col justify-center">
                        <p class="eyebrow">You're signed in</p>
                        <h3 class="display-lg mt-2">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}.</h3>
                        <p class="mt-3 text-muted">Your picks, wishlist and orders are waiting.</p>
                        <div class="mt-6 flex flex-wrap gap-3"><a href="{{ route('account.index') }}" class="btn btn-primary">My account</a><a href="{{ route('account.settings') }}" class="btn btn-ghost">Edit interests</a></div>
                    </div>
                @else
                    <form method="POST" action="{{ route('register') }}" class="space-y-4" novalidate>
                        @csrf
                        <x-field name="name" label="Full name" autocomplete="name" required />
                        <x-field name="email" type="email" label="Email address" autocomplete="email" required />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-field name="password" type="password" label="Password" autocomplete="new-password" required help="8+ characters with letters and numbers." />
                            <x-field name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" required />
                        </div>
                        <fieldset>
                            <legend class="label">Shopping interests <span class="font-normal text-muted">(optional)</span></legend>
                            <div class="flex flex-wrap gap-2">
                                @foreach($interestCategories as $c)
                                    <label class="cursor-pointer"><input type="checkbox" name="interests[]" value="{{ $c->slug }}" class="peer sr-only" @checked(in_array($c->slug, old('interests', [])))><span class="chip peer-checked:bg-ink peer-checked:text-bg peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2">{{ $c->name }}</span></label>
                                @endforeach
                            </div>
                        </fieldset>
                        <button class="btn btn-primary btn-lg w-full">Create Account</button>
                        @if(\App\Http\Controllers\Auth\SocialAuthController::enabled())
                            <a href="{{ route('auth.google') }}" class="btn btn-ghost btn-lg w-full">Continue with Google</a>
                        @endif
                        <p class="text-center text-sm text-muted">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-ink underline underline-offset-4">Sign in</a></p>
                    </form>
                @endauth
            </div>
        </div>
    </section>

    {{-- ============================ NEWSLETTER ============================ --}}
    <section class="{{ $wrap }} mt-20 md:mt-28" aria-labelledby="news-title">
        <div class="grid items-center gap-8 rounded-[2rem] bg-surface-2 p-8 md:grid-cols-2 md:p-12">
            <div><p class="eyebrow mb-2">The VYORA edit</p><h2 id="news-title" class="display-lg text-balance">New drops and quiet favourites, in your inbox.</h2><p class="mt-3 text-sm text-muted">No spam. Unsubscribe whenever you like.</p></div>
            <form method="POST" action="{{ route('newsletter') }}" novalidate>
                @csrf <input type="hidden" name="_form" value="newsletter">
                <label for="home-news" class="sr-only">Email address</label>
                <div class="flex flex-col gap-2 sm:flex-row"><input id="home-news" type="email" name="email" required autocomplete="email" placeholder="you@email.com" value="{{ old('_form') === 'newsletter' ? old('email') : '' }}" class="input !h-14"><button class="btn btn-primary btn-lg">Subscribe</button></div>
                @if(old('_form') === 'newsletter' && $errors->has('email'))<p class="field-error" role="alert">{{ $errors->first('email') }}</p>@endif
            </form>
        </div>
    </section>
</main>
</x-layouts.app>
