<x-layouts.admin title="Dashboard">
    <h1 class="display-lg">Dashboard</h1>
    <p class="mt-2 text-sm text-muted">"Revenue" counts paid, non-cancelled, non-demo orders only. Demo-mode orders are shown separately and never mixed in.</p>
    <dl class="mt-8 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach([['Revenue', \App\Support\Money::format($stats['revenue_cents'])], ['Paid orders', $stats['paid_orders']], ['Average order', \App\Support\Money::format($stats['aov_cents'])], ['Customers', $stats['customers']], ['Demo revenue', \App\Support\Money::format($stats['demo_revenue_cents'])], ['All orders', $stats['orders']], ['Products', $stats['products']], ['Reviews to moderate', $stats['pending_reviews']]] as [$l, $v])
            <div class="card p-5"><dt class="eyebrow">{{ $l }}</dt><dd class="mt-1 font-display text-2xl font-bold tabular">{{ $v }}</dd></div>
        @endforeach
    </dl>
    <section class="card mt-8 p-5" aria-labelledby="ch"><h2 id="ch" class="font-display text-lg font-bold">Last 30 days</h2>
        <div class="mt-4 h-72"><canvas x-data x-init="$nextTick(() => vyoraRevenueChart($el, @js($chart)))" role="img" aria-label="Daily revenue chart for the last 30 days"></canvas></div></section>
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="card p-5"><h2 class="font-display text-lg font-bold">Recent orders</h2>
            <table class="mt-3 w-full text-sm"><tbody class="divide-y divide-line">@forelse($recentOrders as $o)<tr><td class="py-2"><a class="font-semibold underline-offset-4 hover:underline" href="{{ route('admin.orders.show', $o) }}">{{ $o->number }}</a>@if($o->is_demo) <span class="badge badge-soft">Demo</span>@endif</td><td class="text-muted">{{ $o->statusLabel() }}</td><td class="text-right tabular">{{ $o->money('total') }}</td></tr>@empty<tr><td class="py-4 text-muted">No orders yet.</td></tr>@endforelse</tbody></table></section>
        <section class="card p-5"><h2 class="font-display text-lg font-bold">Low stock</h2>
            <table class="mt-3 w-full text-sm"><tbody class="divide-y divide-line">@forelse($lowStock as $v)<tr><td class="py-2">{{ $v->product->name }} <span class="text-muted">{{ collect([$v->color, $v->size])->filter()->implode(' · ') }}</span></td><td class="text-right font-semibold tabular {{ $v->stock === 0 ? 'text-danger' : '' }}">{{ $v->stock }}</td></tr>@empty<tr><td class="py-4 text-muted">All stocked.</td></tr>@endforelse</tbody></table></section>
        <section class="card p-5"><h2 class="font-display text-lg font-bold">Top products (units sold)</h2>
            <table class="mt-3 w-full text-sm"><tbody class="divide-y divide-line">@forelse($topProducts as $p)<tr><td class="py-2">{{ $p->name }}</td><td class="text-right tabular">{{ $p->units }}</td></tr>@empty<tr><td class="py-4 text-muted">No paid orders yet.</td></tr>@endforelse</tbody></table></section>
    </div>
</x-layouts.admin>
