<x-layouts.admin title="Customers">
    <h1 class="display-lg">Customers</h1>
    <form method="GET" class="mt-6 flex max-w-md gap-2" role="search"><input name="q" value="{{ $q }}" class="input" placeholder="Name or email" aria-label="Search"><button class="btn btn-ghost">Search</button></form>
    <div class="card mt-6 overflow-x-auto"><table class="w-full text-left text-sm">
        <thead class="border-b border-line text-xs uppercase tracking-wider text-muted"><tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Joined</th><th class="px-4 py-3">Orders</th><th class="px-4 py-3">Role</th></tr></thead>
        <tbody class="divide-y divide-line">@foreach($customers as $c)<tr><td class="px-4 py-3 font-semibold">{{ $c->name }}</td><td class="px-4 py-3">{{ $c->email }}</td><td class="px-4 py-3 text-muted">{{ $c->created_at->format('M j, Y') }}</td><td class="px-4 py-3 tabular">{{ $c->orders_count }}</td>
            <td class="px-4 py-3">@if(auth()->user()->isAdmin() && $c->id !== auth()->id())<form method="POST" action="{{ route('admin.customers.role', $c) }}" class="flex gap-2">@csrf @method('PUT')<select name="role" class="input h-9 w-auto py-0" aria-label="Role for {{ $c->name }}">@foreach(['customer','staff','admin'] as $r)<option @selected($c->role === $r)>{{ $r }}</option>@endforeach</select><button class="btn btn-ghost btn-sm">Set</button></form>@else<span class="badge badge-soft">{{ $c->role }}</span>@endif</td></tr>@endforeach</tbody></table></div>
    <div class="mt-6">{{ $customers->links() }}</div>
</x-layouts.admin>
