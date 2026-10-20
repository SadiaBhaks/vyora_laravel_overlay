@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-4">
        <p class="hidden text-sm text-muted sm:block">Showing <span class="tabular">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span> of <span class="tabular">{{ $paginator->total() }}</span></p>
        <ul class="mx-auto flex items-center gap-1 sm:mx-0">
            @if($paginator->onFirstPage())<li><span class="btn btn-ghost btn-sm" aria-disabled="true">Prev</span></li>
            @else<li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-ghost btn-sm">Prev</a></li>@endif
            @foreach ($elements as $element)
                @if (is_string($element))<li class="px-1 text-muted">…</li>@endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="hidden sm:block">@if ($page == $paginator->currentPage())<span class="btn btn-primary btn-sm btn-icon tabular" aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}" class="btn btn-ghost btn-sm btn-icon tabular" aria-label="Page {{ $page }}">{{ $page }}</a>@endif</li>
                    @endforeach
                @endif
            @endforeach
            <li class="px-2 text-sm text-muted sm:hidden tabular">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</li>
            @if($paginator->hasMorePages())<li><a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-ghost btn-sm">Next</a></li>
            @else<li><span class="btn btn-ghost btn-sm" aria-disabled="true">Next</span></li>@endif
        </ul>
    </nav>
@endif
