<x-layouts.admin title="Orders">
    <h1 class="display-lg">Orders</h1>
    <form method="GET" class="mt-6 flex flex-wrap gap-2" role="search">
        <input name="q" value="{{ $filters['q'] ?? '' }}" class="input max-w-xs" placeholder="Order # or email" aria-label="Search orders">
        <select name="status" class="input w-auto" aria-label="Status"><option value="">Any status</option>@foreach(array_merge(\App\Services\OrderService::FLOW, ['cancelled']) as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach</select>
        <select name="payment" class="input w-auto" aria-label="Payment"><option value="">Any payment</option>@foreach(['pending','paid','failed','cancelled'] as $s)<option value="{{ $s }}" @selected(($filters['payment'] ?? '') === $s)>{{ ucfirst($s) }}</option>@endforeach</select>
        <button class="btn btn-ghost">Filter</button></form>
    <div class="card mt-6 overflow-x-auto"><table class="w-full text-left text-sm">
        <thead class="border-b border-line text-xs uppercase tracking-wider text-muted"><tr><th class="px-4 py-3">Order</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Placed</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Payment</th><th class="px-4 py-3 text-right">Total</th></tr></thead>
        <tbody class="divide-y divide-line">@forelse($orders as $o)
            <tr><td class="px-4 py-3"><a href="{{ route('admin.orders.show', $o) }}" class="font-semibold underline-offset-4 hover:underline">{{ $o->number }}</a> @if($o->is_demo)<span class="badge badge-soft">Demo</span>@endif</td><td class="px-4 py-3">{{ $o->email }}</td><td class="px-4 py-3 text-muted">{{ $o->placed_at->format('M j, Y H:i') }}</td><td class="px-4 py-3">{{ $o->statusLabel() }}</td><td class="px-4 py-3">{{ $o->paymentLabel() }}</td><td class="px-4 py-3 text-right tabular">{{ $o->money('total') }}</td></tr>
        @empty<tr><td colspan="6" class="px-4 py-10 text-center text-muted">No orders match.</td></tr>@endforelse</tbody></table></div>
    <div class="mt-6">{{ $orders->links() }}</div>
</x-layouts.admin>
