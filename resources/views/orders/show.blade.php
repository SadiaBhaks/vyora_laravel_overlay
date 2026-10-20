@php $addr = $order->shipping_address; $cancelled = $order->status === 'cancelled'; @endphp
<x-layouts.app :title="($confirmation ? 'Order confirmed ' : 'Order ').$order->number" :noindex="true">
    <div class="mx-auto max-w-5xl px-4 py-10 md:px-8 md:py-14">
        @if($confirmation)
            <header class="mb-10">
                @if($order->payment_status === 'paid')
                    <p class="eyebrow">{{ $order->is_demo ? 'Demo order recorded' : 'Order confirmed' }}</p>
                    <h1 class="display-lg mt-2">Thank you, {{ \Illuminate\Support\Str::of($addr['full_name'] ?? 'there')->before(' ') }}.</h1>
                    <p class="mt-3 max-w-xl text-muted">{{ $order->is_demo ? 'This was placed in demo mode. No card was charged and nothing will be shipped.' : 'Your payment was received. We\'ve emailed a receipt to '.$order->email.'.' }}</p>
                @elseif($order->payment_status === 'pending')
                    <p class="eyebrow">Awaiting payment confirmation</p>
                    <h1 class="display-lg mt-2">We're confirming your payment.</h1>
                    <p class="mt-3 max-w-xl text-muted">This usually takes a few seconds. This page does not mark the order as paid until the payment provider confirms it. <a href="" class="font-semibold underline">Refresh</a></p>
                @else
                    <p class="eyebrow">Payment not completed</p>
                    <h1 class="display-lg mt-2">This order wasn't paid.</h1>
                    <p class="mt-3 max-w-xl text-muted">No payment was taken. Your items were released — you can <a href="{{ route('shop') }}" class="font-semibold underline">keep shopping</a>.</p>
                @endif
            </header>
        @else
            <a href="{{ route('account.orders') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm text-muted hover:text-ink"><x-icon name="arrow-left" class="size-4" />All orders</a>
            <h1 class="display-lg mb-8">Order {{ $order->number }}</h1>
        @endif

        @if($errors->any())<p class="mb-6 rounded-xl border border-danger/50 bg-danger/10 p-4 text-sm" role="alert">{{ $errors->first() }}</p>@endif

        <section class="card p-6 md:p-8" aria-labelledby="trk">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div><h2 id="trk" class="font-display text-xl font-bold">Order tracking</h2><p class="text-sm text-muted">{{ $order->number }} · placed {{ $order->placed_at->format('M j, Y') }}</p></div>
                <div class="flex gap-2">@if($order->is_demo)<span class="badge badge-soft">Demo</span>@endif<span class="badge badge-soft">{{ $order->paymentLabel() }}</span><span class="badge badge-ink">{{ $order->statusLabel() }}</span></div>
            </div>
            @if($cancelled)
                <p class="mt-6 text-sm">This order was cancelled.</p>
            @else
                <ol class="mt-8 grid gap-6 md:grid-cols-6 md:gap-3">
                    @foreach($timeline as $step)
                        @php $done = (bool) $step['reached_at']; @endphp
                        <li class="flex gap-3 md:block" @if($step['current']) aria-current="step" @endif>
                            <span class="grid size-7 shrink-0 place-items-center rounded-full border-2 text-xs {{ $done ? 'border-ink bg-ink text-bg' : 'border-line text-muted' }}">@if($done)<x-icon name="check" class="size-3.5" />@endif</span>
                            <div class="md:mt-2"><p class="text-sm font-semibold {{ $done ? '' : 'text-muted' }}">{{ $step['label'] }}</p><p class="text-xs text-muted">{{ $done ? $step['reached_at']->format('M j, g:i A') : 'Not yet' }}</p></div>
                        </li>
                    @endforeach
                </ol>
                @if($order->tracking_number)<p class="mt-6 text-sm"><span class="text-muted">Carrier:</span> {{ $order->carrier ?: '—' }} · <span class="text-muted">Tracking:</span> <span class="font-mono">{{ $order->tracking_number }}</span></p>@endif
                @if($order->estimated_delivery_from && $order->payment_status === 'paid' && ! $order->is_demo)<p class="mt-2 text-sm text-muted">Estimated delivery {{ $order->estimated_delivery_from->format('M j') }}–{{ $order->estimated_delivery_to->format('M j') }}.</p>@endif
            @endif
        </section>

        <div class="mt-6 grid gap-6 md:grid-cols-[1fr_20rem]">
            <section class="card p-6" aria-labelledby="its"><h2 id="its" class="font-display text-xl font-bold">Items</h2>
                <ul class="mt-4 divide-y divide-line">
                    @foreach($order->items as $i)
                        <li class="flex gap-4 py-4 first:pt-0 last:pb-0"><div class="size-16 shrink-0 overflow-hidden rounded-lg bg-surface-2"><img src="{{ $i->image_url ?: route('art', ['seed' => $i->sku, 'c' => 'lifestyle']) }}" alt="" class="size-full object-cover" width="64" height="64" loading="lazy"></div>
                            <div class="min-w-0 flex-1 text-sm"><p class="font-semibold">{{ $i->name }}</p><p class="text-muted">{{ collect([$i->color, $i->size])->filter()->implode(' · ') }} · Qty {{ $i->quantity }}</p></div>
                            <p class="text-sm font-semibold tabular">{{ $i->lineTotal() }}</p></li>
                    @endforeach
                </ul></section>
            <aside class="space-y-6">
                <section class="card p-6 text-sm" aria-labelledby="sm"><h2 id="sm" class="font-display text-xl font-bold">Summary</h2>
                    <dl class="mt-4 space-y-2">
                        <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd class="tabular">{{ $order->money('subtotal') }}</dd></div>
                        @if($order->discount_cents)<div class="flex justify-between"><dt class="text-muted">Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</dt><dd class="tabular">−{{ $order->money('discount') }}</dd></div>@endif
                        <div class="flex justify-between"><dt class="text-muted">Shipping</dt><dd class="tabular">{{ $order->shipping_cents ? $order->money('shipping') : 'Free' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-muted">Estimated tax</dt><dd class="tabular">{{ $order->money('tax') }}</dd></div>
                        <div class="flex justify-between border-t border-line pt-3 text-base font-bold"><dt>Total</dt><dd class="tabular">{{ $order->money('total') }}</dd></div>
                    </dl></section>
                <section class="card p-6 text-sm" aria-labelledby="sa"><h2 id="sa" class="font-display text-xl font-bold">Shipping to</h2>
                    <p class="mt-3 text-muted">{{ $addr['full_name'] ?? '' }}<br>{{ $addr['line1'] ?? '' }}@if(! empty($addr['line2'])), {{ $addr['line2'] }}@endif<br>{{ $addr['city'] ?? '' }}, {{ $addr['state'] ?? '' }} {{ $addr['postal_code'] ?? '' }}<br>{{ $addr['country'] ?? '' }}</p></section>
                @can('cancel', $order)
                    <form method="POST" action="{{ route('account.orders.cancel', $order) }}" onsubmit="return confirm('Cancel this order?')">@csrf<button class="btn btn-ghost w-full">Cancel order</button></form>
                @endcan
            </aside>
        </div>
        <p class="mt-8 text-sm text-muted">Need help? <a href="{{ route('contact') }}" class="font-semibold underline">Contact us</a> with order number {{ $order->number }}.</p>
    </div>
</x-layouts.app>
