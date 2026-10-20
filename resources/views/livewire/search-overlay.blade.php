<div x-data="recentSearches" @keydown.window.escape="$wire.set('q', '')">
    <div class="flex items-start justify-between gap-4">
        <h2 class="display-xl text-balance uppercase"><span class="block text-muted">What are you</span>looking for?</h2>
        <button type="button" @click="search = false" class="btn btn-ghost btn-icon shrink-0" aria-label="Close search"><x-icon name="x" /></button>
    </div>

    <form action="{{ route('search') }}" method="GET" role="search" class="relative mt-8" @submit="save($wire.q)">
        <label for="search-input" class="sr-only">Search products, brands, categories and tags</label>
        <x-icon name="search" class="pointer-events-none absolute left-5 top-1/2 size-6 -translate-y-1/2 text-muted" />
        <input id="search-input" name="q" type="search" autocomplete="off" maxlength="80" wire:model.live.debounce.250ms="q" x-init="$watch('search', v => v && $nextTick(() => $el.focus()))"
               placeholder="Search products, brands, tags…" class="input !h-16 !rounded-2xl !pl-14 !pr-28 !text-lg">
        <div class="absolute right-3 top-1/2 flex -translate-y-1/2 items-center gap-2">
            @if($q !== '')<button type="button" wire:click="$set('q', '')" class="text-sm font-medium text-muted underline underline-offset-4 hover:text-ink">Clear</button>@endif
            <span wire:loading wire:target="q" class="size-4 animate-spin rounded-full border-2 border-line border-t-ink" aria-hidden="true"></span>
        </div>
    </form>

    <div class="mt-8" aria-live="polite">
        @if($result === null)
            <div class="grid gap-8 md:grid-cols-2">
                <div>
                    <h3 class="eyebrow mb-3 !font-sans">Popular searches</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($popular as $term)
                            <button type="button" wire:click="$set('q', '{{ addslashes($term) }}')" class="chip">{{ $term }}</button>
                        @empty
                            @foreach(['headphones', 'minimal', 'travel', 'wireless'] as $term)<button type="button" wire:click="$set('q', '{{ $term }}')" class="chip">{{ $term }}</button>@endforeach
                        @endforelse
                    </div>
                </div>
                <div x-show="items.length" x-cloak>
                    <div class="mb-3 flex items-center justify-between"><h3 class="eyebrow !font-sans">Recent searches</h3>
                        <button type="button" @click="clear()" class="text-xs text-muted underline underline-offset-4 hover:text-ink">Clear history</button></div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="t in items" :key="t"><button type="button" class="chip" @click="$wire.set('q', t)"><x-icon name="search" class="size-3.5 text-muted" /><span x-text="t"></span></button></template>
                    </div>
                </div>
            </div>
        @elseif($result['count'] === 0)
            <x-empty-state icon="search" :title="'No matches for “'.$term.'”'" text="Check the spelling, try a broader word, or browse by category." :action="route('categories')" actionLabel="Browse categories" class="!py-12" />
        @else
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-muted"><strong class="text-ink tabular">{{ $result['count'] }}</strong> {{ \Illuminate\Support\Str::plural('result', $result['count']) }} for “{{ $term }}”</p>
                <a href="{{ route('search', ['q' => $term]) }}" @click="save('{{ addslashes($term) }}')" class="inline-flex items-center gap-1 text-sm font-semibold underline-offset-4 hover:underline">See all <x-icon name="arrow-right" class="size-4" /></a>
            </div>
            <ul class="grid gap-2 sm:grid-cols-2" wire:loading.class="opacity-50" wire:target="q">
                @foreach($result['products'] as $p)
                    <li wire:key="s-{{ $p->id }}">
                        <a href="{{ route('product.show', $p) }}" @click="save('{{ addslashes($term) }}')" class="card flex items-center gap-4 p-3 transition hover:border-ink">
                            <span class="size-16 shrink-0 overflow-hidden rounded-lg bg-surface-2"><x-img :image="$p->images->first()" :alt="$p->name" :seed="$p->slug.'-1'" /></span>
                            <span class="min-w-0"><span class="eyebrow block !text-[.62rem]">{{ $p->brand?->name }}</span><span class="line-clamp-1 text-sm font-semibold">{{ $p->name }}</span><x-price :product="$p" size="text-sm" /></span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
