@props(['items' => []])
@php
    $crumbs = collect([['label' => 'Home', 'url' => route('home')]])->merge($items)->values();
    $ld = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $crumbs->map(fn ($c, $i) => array_filter(['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c['label'], 'item' => $c['url'] ?? null]))->all()];
@endphp
<nav aria-label="Breadcrumb" class="mb-6 text-sm text-muted">
    <ol class="flex flex-wrap items-center gap-1.5">
        @foreach($crumbs as $c)
            <li class="flex items-center gap-1.5">
                @if(! $loop->last && ! empty($c['url']))<a href="{{ $c['url'] }}" class="hover:text-ink hover:underline underline-offset-4">{{ $c['label'] }}</a><x-icon name="chevron-right" class="size-3.5" />
                @else<span class="text-ink" @if($loop->last) aria-current="page" @endif>{{ $c['label'] }}</span>@endif
            </li>
        @endforeach
    </ol>
</nav>
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
