<x-layouts.admin title="Newsletter">
    <h1 class="display-lg">Newsletter subscribers</h1>
    <div class="card mt-6 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="border-b border-line text-xs uppercase tracking-wider text-muted"><tr><th class="px-4 py-3">Email</th><th class="px-4 py-3">Subscribed</th><th class="px-4 py-3"></th></tr></thead>
        <tbody class="divide-y divide-line">@forelse($subscribers as $s)<tr><td class="px-4 py-3">{{ $s->email }}</td><td class="px-4 py-3 text-muted">{{ $s->subscribed_at?->format('M j, Y') }}</td><td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.newsletter.destroy', $s) }}" onsubmit="return confirm('Remove subscriber?')">@csrf @method('DELETE')<button class="text-danger underline underline-offset-4">Remove</button></form></td></tr>@empty<tr><td colspan="3" class="px-4 py-10 text-center text-muted">No subscribers yet.</td></tr>@endforelse</tbody></table></div>
    <div class="mt-6">{{ $subscribers->links() }}</div>
</x-layouts.admin>
