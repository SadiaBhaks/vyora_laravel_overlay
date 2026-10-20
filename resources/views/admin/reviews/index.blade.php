<x-layouts.admin title="Reviews">
    <h1 class="display-lg">Reviews</h1>
    <nav class="mt-6 flex gap-2" aria-label="Review status">@foreach(['pending','approved','rejected'] as $s)<a href="{{ route('admin.reviews.index', ['status' => $s]) }}" class="chip {{ $status === $s ? 'is-on' : '' }}">{{ ucfirst($s) }} ({{ $counts[$s] ?? 0 }})</a>@endforeach</nav>
    <div class="mt-6 space-y-3">@forelse($reviews as $r)
        <article class="card p-5 text-sm"><div class="flex flex-wrap items-center justify-between gap-2"><p class="font-semibold">{{ $r->product?->name }} · {{ $r->rating }}/5 @if($r->is_verified)<span class="badge badge-soft">Verified purchase</span>@endif @if($r->is_demo)<span class="badge badge-soft">Demo</span>@endif</p><p class="text-muted">{{ $r->user?->name }} · {{ $r->created_at->format('M j, Y') }}</p></div>
            @if($r->title)<p class="mt-2 font-semibold">{{ $r->title }}</p>@endif<p class="mt-1">{{ $r->body }}</p>
            <div class="mt-4 flex gap-2">@foreach(['approved' => 'Approve', 'rejected' => 'Reject'] as $to => $label)@if($r->status !== $to)<form method="POST" action="{{ route('admin.reviews.update', $r) }}">@csrf @method('PUT')<input type="hidden" name="status" value="{{ $to }}"><button class="btn btn-ghost btn-sm">{{ $label }}</button></form>@endif @endforeach
                <form method="POST" action="{{ route('admin.reviews.destroy', $r) }}" onsubmit="return confirm('Delete review?')">@csrf @method('DELETE')<button class="btn btn-ghost btn-sm text-danger">Delete</button></form></div></article>
    @empty<p class="py-10 text-center text-muted">No {{ $status }} reviews.</p>@endforelse</div>
    <div class="mt-6">{{ $reviews->links() }}</div>
</x-layouts.admin>
