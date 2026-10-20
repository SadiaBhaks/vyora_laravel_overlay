@php if (! isset($scrollTo)) { $scrollTo = 'body'; } $scrollIntoViewJsSnippet = ($scrollTo !== false) ? "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView({ behavior: 'smooth', block: 'start' })" : ''; @endphp
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-4">
        <p class="hidden text-sm text-muted sm:block">Showing <span class="tabular">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span> of <span class="tabular">{{ $paginator->total() }}</span></p>
        <ul class="mx-auto flex items-center gap-1 sm:mx-0">
            <li>@if($paginator->onFirstPage())<span class="btn btn-ghost btn-sm" aria-disabled="true">Prev</span>@else<button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="btn btn-ghost btn-sm">Prev</button>@endif</li>
            @foreach ($elements as $element)
                @if (is_string($element))<li class="px-1 text-muted">…</li>@endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="hidden sm:block" wire:key="pg-{{ $paginator->getPageName() }}-{{ $page }}">@if ($page == $paginator->currentPage())<span class="btn btn-primary btn-sm btn-icon tabular" aria-current="page">{{ $page }}</span>@else<button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="btn btn-ghost btn-sm btn-icon tabular" aria-label="Page {{ $page }}">{{ $page }}</button>@endif</li>
                    @endforeach
                @endif
            @endforeach
            <li class="px-2 text-sm text-muted sm:hidden tabular">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</li>
            <li>@if($paginator->hasMorePages())<button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="btn btn-ghost btn-sm">Next</button>@else<span class="btn btn-ghost btn-sm" aria-disabled="true">Next</span>@endif</li>
        </ul>
    </nav>
@endif
