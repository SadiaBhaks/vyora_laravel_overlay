<x-account-shell title="My account" active="index">
    <h1 class="display-lg">Welcome back, {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}.</h1>
    @if(config('vyora.require_email_verification') && ! auth()->user()->hasVerifiedEmail())
        <p class="mt-4 rounded-xl border border-warn/50 bg-warn/10 p-4 text-sm">Please verify your email address. <form method="POST" action="{{ route('verification.send') }}" class="inline">@csrf<button class="font-semibold underline">Resend link</button></form></p>
    @endif

    <dl class="mt-8 grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach([['Orders', $stats['orders']], ['In progress', $stats['open']], ['Wishlist', $stats['wishlist']], ['Total spent', \App\Support\Money::format($stats['spent_cents'])]] as [$l, $v])
            <div class="card p-5"><dt class="eyebrow">{{ $l }}</dt><dd class="mt-1 font-display text-3xl font-bold tabular">{{ $v }}</dd></div>
        @endforeach
    </dl>

    <section class="mt-12" aria-labelledby="ro"><x-section-heading title="Recent orders" :link="route('account.orders')" linkLabel="All orders" id="ro" class="!mb-4" />
        @forelse($orders as $o)
            <a href="{{ route('account.orders.show', $o) }}" class="card mb-3 flex flex-wrap items-center justify-between gap-4 p-5 transition hover:border-ink">
                <div><p class="font-semibold">{{ $o->number }}</p><p class="text-sm text-muted">{{ $o->placed_at->format('M j, Y') }} · {{ $o->items->sum('quantity') }} {{ \Illuminate\Support\Str::plural('item', $o->items->sum('quantity')) }}</p></div>
                <span class="badge badge-soft">{{ $o->statusLabel() }}</span><p class="font-semibold tabular">{{ $o->money('total') }}</p>
            </a>
        @empty<x-empty-state icon="package" title="No orders yet" text="When you place an order it will show up here." :action="route('shop')" actionLabel="Start shopping" class="!py-10" />@endforelse
    </section>

    @if($wishlist->isNotEmpty())<section class="mt-12" aria-labelledby="wl"><x-section-heading title="From your wishlist" :link="route('account.wishlist')" linkLabel="View wishlist" id="wl" class="!mb-4" /><div class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-4">@foreach($wishlist as $p)<x-product-card :product="$p" />@endforeach</div></section>@endif
    @if($recommended->isNotEmpty())<section class="mt-12" aria-labelledby="rc"><x-section-heading title="Recommended for you" id="rc" class="!mb-4" /><div class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-4">@foreach($recommended as $p)<x-product-card :product="$p" />@endforeach</div></section>@endif
    @if($recent->isNotEmpty())<section class="mt-12" aria-labelledby="rv"><x-section-heading title="Recently viewed" id="rv" class="!mb-4" /><div class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-4">@foreach($recent as $p)<x-product-card :product="$p" />@endforeach</div></section>@endif

    <section class="mt-12" aria-labelledby="ad"><x-section-heading title="Saved addresses" :link="route('account.addresses')" linkLabel="Manage" id="ad" class="!mb-4" />
        <div class="grid gap-3 md:grid-cols-2">@forelse($addresses as $a)<div class="card p-5 text-sm"><p class="font-semibold">{{ $a->label }} @if($a->is_default)<span class="badge badge-soft ml-1">Default</span>@endif</p><p class="mt-1 text-muted">{{ $a->full_name }}<br>{{ $a->line1 }}, {{ $a->city }} {{ $a->postal_code }}</p></div>@empty<p class="text-sm text-muted">No saved addresses yet.</p>@endforelse</div></section>
</x-account-shell>
