@php $a = $order->shipping_address; @endphp
<x-layouts.admin :title="'Order '.$order->number">
    <a href="{{ route('admin.orders.index') }}" class="text-sm text-muted hover:text-ink">← Orders</a>
    <div class="mt-2 flex flex-wrap items-center gap-3"><h1 class="display-lg">{{ $order->number }}</h1>@if($order->is_demo)<span class="badge badge-accent">Demo order — no real payment</span>@endif<span class="badge badge-soft">{{ $order->paymentLabel() }}</span><span class="badge badge-ink">{{ $order->statusLabel() }}</span></div>
    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_22rem]">
        <div class="space-y-6">
            <section class="card p-6"><h2 class="font-display text-lg font-bold">Items</h2>
                <table class="mt-3 w-full text-sm"><tbody class="divide-y divide-line">@foreach($order->items as $i)<tr><td class="py-2"><span class="font-semibold">{{ $i->name }}</span><br><span class="text-xs text-muted">{{ $i->sku }} · {{ collect([$i->color, $i->size])->filter()->implode(' · ') }}</span></td><td class="tabular">{{ $i->unitPrice() }} × {{ $i->quantity }}</td><td class="text-right tabular">{{ $i->lineTotal() }}</td></tr>@endforeach</tbody></table>
                <dl class="mt-4 space-y-1 border-t border-line pt-4 text-sm"><div class="flex justify-between"><dt>Subtotal</dt><dd class="tabular">{{ $order->money('subtotal') }}</dd></div>@if($order->discount_cents)<div class="flex justify-between"><dt>Discount {{ $order->coupon_code }}</dt><dd class="tabular">−{{ $order->money('discount') }}</dd></div>@endif<div class="flex justify-between"><dt>Shipping</dt><dd class="tabular">{{ $order->money('shipping') }}</dd></div><div class="flex justify-between"><dt>Tax</dt><dd class="tabular">{{ $order->money('tax') }}</dd></div><div class="flex justify-between text-base font-bold"><dt>Total</dt><dd class="tabular">{{ $order->money('total') }}</dd></div></dl></section>
            <section class="card p-6"><h2 class="font-display text-lg font-bold">History</h2>
                <ul class="mt-3 space-y-2 text-sm">@forelse($order->histories as $h)<li><span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $h->status)) }}</span> <span class="text-muted">— {{ $h->created_at->format('M j, H:i') }}@if($h->note) · {{ $h->note }}@endif</span></li>@empty<li class="text-muted">No history.</li>@endforelse</ul></section>
            <section class="card p-6"><h2 class="font-display text-lg font-bold">Payments</h2>
                <ul class="mt-3 space-y-2 text-sm">@forelse($order->payments as $p)<li>{{ ucfirst($p->provider) }} · {{ $p->status }} · {{ \App\Support\Money::format($p->amount_cents) }} @if($p->is_demo)<span class="badge badge-soft">Demo</span>@endif @if($p->failure_reason)<span class="text-muted">({{ $p->failure_reason }})</span>@endif</li>@empty<li class="text-muted">None.</li>@endforelse</ul></section>
        </div>
        <aside class="space-y-6">
            <section class="card p-6 text-sm"><h2 class="font-display text-lg font-bold">Customer</h2><p class="mt-2">{{ $order->user?->name ?? 'Guest' }}<br>{{ $order->email }}</p>
                <p class="mt-3 text-muted">{{ $a['full_name'] ?? '' }}<br>{{ $a['line1'] ?? '' }}@if(! empty($a['line2'])), {{ $a['line2'] }}@endif<br>{{ $a['city'] ?? '' }}, {{ $a['state'] ?? '' }} {{ $a['postal_code'] ?? '' }}<br>{{ $a['country'] ?? '' }}</p></section>
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="card space-y-3 p-6">@csrf @method('PUT')
                <h2 class="font-display text-lg font-bold">Fulfilment</h2>
                @if($order->payment_status !== 'paid')<p class="text-xs text-muted">Only paid orders can move through fulfilment.</p>@endif
                <x-field name="status" type="select" label="Status">@foreach($flow as $s)<option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>@endforeach</x-field>
                <x-field name="carrier" label="Carrier" :value="$order->carrier" /><x-field name="tracking_number" label="Tracking number" :value="$order->tracking_number" /><x-field name="note" label="Internal note" />
                <button class="btn btn-primary w-full" @disabled($order->status === 'cancelled')>Update order</button></form>
            @if($order->status !== 'cancelled')<form method="POST" action="{{ route('admin.orders.cancel', $order) }}" onsubmit="return confirm('Cancel this order and return stock? Any refund must be issued in Stripe.')">@csrf<button class="btn btn-ghost w-full">Cancel order & restock</button></form>@endif
        </aside>
    </div>
</x-layouts.admin>
