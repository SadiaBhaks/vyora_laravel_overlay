<x-layouts.admin title="Inventory">
    <div class="flex flex-wrap items-center justify-between gap-3"><h1 class="display-lg">Inventory</h1><a href="{{ route('admin.inventory.index', $low ? [] : ['low' => 1]) }}" class="btn btn-ghost btn-sm">{{ $low ? 'Show all' : 'Low stock only (≤'.$threshold.')' }}</a></div>
    <div class="card mt-6 overflow-x-auto"><table class="w-full text-left text-sm">
        <thead class="border-b border-line text-xs uppercase tracking-wider text-muted"><tr><th class="px-4 py-3">Product</th><th class="px-4 py-3">SKU</th><th class="px-4 py-3">Stock</th><th class="px-4 py-3">Adjust</th></tr></thead>
        <tbody class="divide-y divide-line">@foreach($variants as $v)<tr><td class="px-4 py-3">{{ $v->product->name }} <span class="text-muted">{{ collect([$v->color, $v->size])->filter()->implode(' · ') }}</span></td><td class="px-4 py-3 font-mono text-xs">{{ $v->sku }}</td><td class="px-4 py-3 font-semibold tabular {{ $v->stock <= $threshold ? 'text-danger' : '' }}">{{ $v->stock }}</td>
            <td class="px-4 py-3"><form method="POST" action="{{ route('admin.inventory.adjust', $v) }}" class="flex gap-2">@csrf<input type="number" name="change" class="input h-9 w-24 py-0" placeholder="+/−" aria-label="Change" required><input name="note" class="input h-9 w-40 py-0" placeholder="Note" aria-label="Note"><button class="btn btn-ghost btn-sm">Apply</button></form></td></tr>@endforeach</tbody></table></div>
    <div class="mt-6">{{ $variants->links() }}</div>
    <section class="mt-10"><h2 class="font-display text-lg font-bold">Recent movements</h2>
        <ul class="mt-3 space-y-1 text-sm text-muted">@forelse($movements as $m)<li>{{ $m->created_at->format('M j H:i') }} · {{ $m->variant?->product?->name }} · <strong class="text-ink">{{ $m->change > 0 ? '+' : '' }}{{ $m->change }}</strong> ({{ $m->reason }}) @if($m->user)· {{ $m->user->name }}@endif</li>@empty<li>None yet.</li>@endforelse</ul></section>
</x-layouts.admin>
