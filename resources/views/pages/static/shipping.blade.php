<x-layouts.app title="Shipping" description="VYORA shipping options, costs and delivery times.">
    <div class="mx-auto max-w-3xl px-4 pt-8 md:px-8"><x-breadcrumbs :items="[['label' => 'Shipping']]" />
        <h1 class="display-xl">Shipping</h1>
        <div class="prose-vyora mt-8">
            <p>{{ $settings['shipping_regions'] }} Orders are processed within <strong>{{ $settings['processing_days'] }} business days</strong> of payment being confirmed.</p>
            <h2>Delivery options</h2>
        </div>
        <div class="mt-4 overflow-hidden rounded-2xl border border-line">
            <table class="w-full text-left text-sm"><caption class="sr-only">Shipping options</caption>
                <thead class="bg-surface-2 text-xs uppercase tracking-widest text-muted"><tr><th scope="col" class="px-5 py-3">Method</th><th scope="col" class="px-5 py-3">Transit time</th><th scope="col" class="px-5 py-3 text-right">Cost</th></tr></thead>
                <tbody class="divide-y divide-line">@foreach(config('vyora.shipping_methods') as $m)
                    <tr><th scope="row" class="px-5 py-4 font-semibold">{{ $m['label'] }}</th><td class="px-5 py-4 text-muted">{{ $m['days_min'] }}–{{ $m['days_max'] }} days</td>
                        <td class="px-5 py-4 text-right tabular">${{ number_format($m['price'], 2) }}@if($m['free_over_threshold'] && config('vyora.free_shipping_threshold') > 0)<span class="block text-xs text-success">Free over ${{ number_format((float) config('vyora.free_shipping_threshold'), 0) }}</span>@endif</td></tr>
                @endforeach</tbody></table>
        </div>
        <div class="prose-vyora mt-8"><h2>Tracking</h2><p>Once your parcel ships, the carrier and tracking number appear on your order page. We only show delivery information we've actually recorded.</p>
            <h2>Duties and taxes</h2><p>Sales tax is calculated at checkout where applicable. Orders outside our shipping regions can't currently be placed.</p></div>
    </div>
</x-layouts.app>
