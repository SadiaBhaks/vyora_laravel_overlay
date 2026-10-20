<x-layouts.app title="Demo payment" :noindex="true">
    <div class="mx-auto max-w-xl px-4 py-14 md:py-20">
        <p class="badge badge-accent">Demo mode</p>
        <h1 class="display-lg mt-4">Demo payment</h1>
        <p class="mt-3 text-muted">Stripe isn't configured on this store, so payment is simulated. <strong class="text-ink">No card is charged and no real order will be fulfilled.</strong></p>
        <div class="card mt-8 p-6 text-sm">
            <p class="font-semibold">Order {{ $order->number }}</p>
            <ul class="mt-3 space-y-1 text-muted">@foreach($order->items as $i)<li>{{ $i->quantity }} × {{ $i->name }}</li>@endforeach</ul>
            <p class="mt-4 flex justify-between border-t border-line pt-4 text-base font-bold"><span>Total</span><span class="tabular">{{ $order->money('total') }}</span></p>
        </div>
        <form method="POST" action="{{ route('checkout.demo.confirm', $order) }}" class="mt-6">@csrf<button class="btn btn-primary btn-lg w-full">Record demo payment</button></form>
        <p class="mt-4 text-center text-xs text-muted">This will mark the order as a <em>demo</em> paid order.</p>
    </div>
</x-layouts.app>
