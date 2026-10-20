@php
    $row = function (string $eyebrow, string $title, $items, bool $rank = false, ?string $note = null) {
        return compact('eyebrow', 'title', 'items', 'rank', 'note');
    };
    $sections = [
        $row('Popular products', 'Trending right now', $popular->take(8), true, 'Score = views in the last 14 days + 3× sales + review quality.'),
        $row('Best sellers', 'Most ordered', $bestSellers, true, 'Ranked by recorded units sold.'),
        $row('Highly rated', 'Loved by reviewers', $topRated, false, 'Products with at least 3 approved reviews.'),
        $row('Recently popular', 'Most viewed this week', $recentlyPopular->filter(fn ($p) => $p->week_views > 0), true, 'Ranked by product page views in the last 7 days.'),
        $row('Rising', 'Gaining momentum', $rising, false, 'More views this week than last week.'),
        $row('Editor picks', 'Chosen by hand', $editorPicks, false),
    ];
@endphp
<x-layouts.app title="Trending" description="What's popular at VYORA: trending, best-selling, highly rated, rising and editor-picked products.">
    <div class="mx-auto max-w-[88rem] px-4 pt-8 md:px-8">
        <x-breadcrumbs :items="[['label' => 'Trending']]" />
        <p class="eyebrow mb-2">Right now</p><h1 class="display-xl">Trending</h1>
        <p class="mt-3 max-w-xl text-muted">Rankings are computed from real recorded activity on this store — never invented.</p>
        @foreach($sections as $i => $s)
            @if($s['items']->isNotEmpty())
                <section class="mt-16" aria-labelledby="t-{{ $i }}">
                    <x-section-heading :eyebrow="$s['eyebrow']" :title="$s['title']" id="t-{{ $i }}" />
                    <div class="grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-4 md:gap-x-5 {{ $i === 0 ? 'lg:grid-cols-4' : 'lg:grid-cols-6' }}">
                        @foreach($s['items'] as $k => $p)<x-product-card :product="$p" :rank="$s['rank'] ? $k + 1 : null" :trending="$i === 0" />@endforeach
                    </div>
                    @if($s['note'])<p class="mt-5 text-xs text-muted">{{ $s['note'] }}</p>@endif
                </section>
            @endif
        @endforeach
    </div>
</x-layouts.app>
