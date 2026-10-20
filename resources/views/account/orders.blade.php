<x-account-shell title="Orders" active="orders">
    <h1 class="display-lg">Orders</h1>
    <div class="mt-8 space-y-3">
        @forelse($orders as $o)
            <a href="{{ route('account.orders.show', $o) }}" class="card block p-5 transition hover:border-ink">
                <div class="flex flex-wrap items-center justify-between gap-3"><div><p class="font-semibold">{{ $o->number }}</p><p class="text-sm text-muted">{{ $o->placed_at->format('M j, Y') }}</p></div>
                    <div class="flex items-center gap-3">@if($o->is_demo)<span class="badge badge-soft">Demo</span>@endif<span class="badge badge-soft">{{ $o->statusLabel() }}</span><span class="font-semibold tabular">{{ $o->money('total') }}</span></div></div>
                <p class="mt-3 line-clamp-1 text-sm text-muted">{{ $o->items->pluck('name')->implode(' · ') }}</p>
            </a>
        @empty<x-empty-state icon="package" title="No orders yet" text="When you place an order it will show up here." :action="route('shop')" actionLabel="Start shopping" />@endforelse
    </div>
    <div class="mt-8">{{ $orders->links() }}</div>
</x-account-shell>
