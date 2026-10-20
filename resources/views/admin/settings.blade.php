<x-layouts.admin title="Settings">
    <h1 class="display-lg">Store settings</h1>
    <p class="mt-2 text-sm text-muted">These values feed the Shipping and Returns pages. Edit them to match your real policies.</p>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="card mt-8 max-w-2xl space-y-4 p-6 sm:p-8" novalidate>@csrf @method('PUT')
        <x-field name="store_name" label="Store name" :value="$settings['store_name'] ?? ''" required />
        <div class="grid gap-4 sm:grid-cols-2"><x-field name="returns_days" type="number" label="Return window (days)" :value="$settings['returns_days'] ?? 30" required /><x-field name="processing_days" type="number" label="Processing time (days)" :value="$settings['processing_days'] ?? 2" required /></div>
        <x-field name="return_shipping" type="textarea" rows="3" label="Return shipping policy" :value="$settings['return_shipping'] ?? ''" required />
        <x-field name="shipping_regions" label="Shipping regions" :value="$settings['shipping_regions'] ?? ''" required />
        <x-field name="support_hours" label="Support hours" :value="$settings['support_hours'] ?? ''" required />
        <button class="btn btn-primary">Save settings</button>
    </form>
</x-layouts.admin>
