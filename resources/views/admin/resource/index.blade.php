<x-layouts.admin :title="$title">
    <div class="flex flex-wrap items-center justify-between gap-3"><h1 class="display-lg">{{ $title }}</h1><a href="{{ route($route.'.create') }}" class="btn btn-primary">New {{ strtolower($singular) }}</a></div>
    <form method="GET" class="mt-6 flex max-w-md gap-2" role="search"><input name="q" value="{{ $q }}" class="input" placeholder="Search {{ strtolower($title) }}" aria-label="Search"><button class="btn btn-ghost">Search</button></form>
    <div class="card mt-6 overflow-x-auto"><table class="w-full text-left text-sm">
        <thead class="border-b border-line text-xs uppercase tracking-wider text-muted"><tr>@foreach($columns as $label => $_)<th class="px-4 py-3 font-semibold">{{ $label }}</th>@endforeach<th class="px-4 py-3"><span class="sr-only">Actions</span></th></tr></thead>
        <tbody class="divide-y divide-line">
            @forelse($rows as $row)
                <tr>@foreach($columns as $label => $col)<td class="px-4 py-3">{{ is_callable($col) ? $col($row) : data_get($row, $col) }}</td>@endforeach
                    <td class="whitespace-nowrap px-4 py-3 text-right"><a href="{{ route($route.'.edit', $row) }}" class="font-semibold underline underline-offset-4">Edit</a>
                        <form method="POST" action="{{ route($route.'.destroy', $row) }}" class="ml-3 inline" onsubmit="return confirm('Delete this {{ strtolower($singular) }}?')">@csrf @method('DELETE')<button class="text-danger underline underline-offset-4">Delete</button></form></td></tr>
            @empty<tr><td colspan="{{ count($columns) + 1 }}" class="px-4 py-10 text-center text-muted">Nothing here yet.</td></tr>@endforelse
        </tbody></table></div>
    <div class="mt-6">{{ $rows->links() }}</div>
</x-layouts.admin>
