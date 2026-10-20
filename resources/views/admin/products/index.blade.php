<x-layouts.admin title="Products">
    <div class="flex flex-wrap items-center justify-between gap-3"><h1 class="display-lg">Products</h1><a href="{{ route('admin.products.create') }}" class="btn btn-primary">New product</a></div>
    <form method="GET" class="mt-6 flex flex-wrap gap-2" role="search"><input name="q" value="{{ $q }}" class="input max-w-xs" placeholder="Name or SKU" aria-label="Search products">
        <select name="status" class="input w-auto" aria-label="Status"><option value="">All</option>@foreach(['active','draft','archived','deleted'] as $s)<option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>@endforeach</select><button class="btn btn-ghost">Filter</button></form>
    <div class="card mt-6 overflow-x-auto"><table class="w-full text-left text-sm">
        <thead class="border-b border-line text-xs uppercase tracking-wider text-muted"><tr><th class="px-4 py-3">Product</th><th class="px-4 py-3">SKU</th><th class="px-4 py-3">Price</th><th class="px-4 py-3">Stock</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr></thead>
        <tbody class="divide-y divide-line">@forelse($products as $p)
            <tr><td class="px-4 py-3"><span class="font-semibold">{{ $p->name }}</span><br><span class="text-xs text-muted">{{ $p->brand?->name }} · {{ $p->category?->name }}</span></td><td class="px-4 py-3 font-mono text-xs">{{ $p->sku }}</td>
                <td class="px-4 py-3 tabular">${{ number_format($p->price, 2) }}</td><td class="px-4 py-3 tabular">{{ $p->allVariants->where('is_active', true)->sum('stock') }}</td>
                <td class="px-4 py-3"><span class="badge badge-soft">{{ $p->trashed() ? 'Deleted' : ucfirst($p->status) }}</span></td>
                <td class="whitespace-nowrap px-4 py-3 text-right">@unless($p->trashed())<a href="{{ route('admin.products.edit', $p) }}" class="font-semibold underline underline-offset-4">Edit</a><form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="ml-3 inline" onsubmit="return confirm('Delete or archive this product?')">@csrf @method('DELETE')<button class="text-danger underline underline-offset-4">Delete</button></form>@endunless</td></tr>
        @empty<tr><td colspan="6" class="px-4 py-10 text-center text-muted">No products found.</td></tr>@endforelse</tbody></table></div>
    <div class="mt-6">{{ $products->links() }}</div>
</x-layouts.admin>
