@php
    $filterPanel = true;
    $groupTitle = 'eyebrow mb-3 !font-sans';
@endphp
<div>
    {{-- Toolbar --}}
    <div class="sticky top-[3.5rem] z-30 -mx-4 mb-6 border-b border-line bg-bg/90 px-4 py-3 backdrop-blur md:top-[3.75rem] md:-mx-8 md:px-8">
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" wire:click="$toggle('filtersOpen')" class="btn btn-ghost btn-sm lg:hidden"><x-icon name="sliders" class="size-4" /> Filters @if(count($chips))<span class="badge badge-ink">{{ count($chips) }}</span>@endif</button>
            <p class="text-sm text-muted" aria-live="polite"><strong class="text-ink tabular">{{ $products->total() }}</strong> {{ \Illuminate\Support\Str::plural('product', $products->total()) }}</p>

            <div class="ml-auto flex items-center gap-2">
                <label for="sort" class="sr-only">Sort by</label>
                <select id="sort" wire:model.live="sort" class="input !h-9 !w-auto !rounded-full !py-0 !text-sm">
                    @foreach($sorts as $key => $label)<option value="{{ $key }}" @selected($currentSort === $key)>{{ $label }}</option>@endforeach
                </select>
                <div class="hidden rounded-full border border-line p-0.5 sm:flex" role="group" aria-label="Layout">
                    <button type="button" wire:click="$set('layout', 'grid')" class="grid size-8 place-items-center rounded-full {{ $layout === 'grid' ? 'bg-ink text-bg' : 'text-muted' }}" aria-label="Grid view" aria-pressed="{{ $layout === 'grid' ? 'true' : 'false' }}"><x-icon name="grid" class="size-4" /></button>
                    <button type="button" wire:click="$set('layout', 'list')" class="grid size-8 place-items-center rounded-full {{ $layout === 'list' ? 'bg-ink text-bg' : 'text-muted' }}" aria-label="List view" aria-pressed="{{ $layout === 'list' ? 'true' : 'false' }}"><x-icon name="list" class="size-4" /></button>
                </div>
            </div>
        </div>

        @if(count($chips))
            <div class="mt-3 flex flex-wrap items-center gap-2" aria-label="Active filters">
                @foreach($chips as $chip)
                    <button type="button" wire:click="removeFilter('{{ $chip['key'] }}', @js($chip['value']))" class="chip is-on" aria-label="Remove filter {{ $chip['label'] }}">{{ $chip['label'] }} <x-icon name="x" class="size-3" /></button>
                @endforeach
                <button type="button" wire:click="clearAll" class="text-sm font-medium underline underline-offset-4">Clear all</button>
            </div>
        @endif
    </div>

    <div class="grid gap-10 lg:grid-cols-[15.5rem_1fr]">
        {{-- Filters: sidebar on desktop, bottom sheet on mobile --}}
        <aside class="{{ $filtersOpen ? 'fixed inset-0 z-[65] flex items-end bg-ink/50 lg:static lg:block lg:bg-transparent' : 'hidden lg:block' }}" aria-label="Filters">
            <div class="max-h-[85vh] w-full overflow-y-auto rounded-t-3xl bg-bg p-6 lg:sticky lg:top-40 lg:max-h-[calc(100vh-11rem)] lg:rounded-none lg:bg-transparent lg:p-0 lg:pr-2">
                <div class="mb-4 flex items-center justify-between lg:hidden"><h2 class="font-display text-xl font-bold">Filters</h2><button type="button" wire:click="$set('filtersOpen', false)" class="btn btn-ghost btn-icon" aria-label="Close filters"><x-icon name="x" /></button></div>

                <div class="space-y-8">
                    @if($mode !== 'category' && $facets['categories']->count() > 1)
                        <fieldset><legend class="{{ $groupTitle }}">Category</legend>
                            <div class="space-y-2">@foreach($facets['categories'] as $c)
                                <label class="flex cursor-pointer items-center gap-2.5 text-sm"><input type="checkbox" wire:model.live="categories" value="{{ $c->slug }}" class="size-4 rounded accent-[var(--ink)]"> {{ $c->name }}</label>
                            @endforeach</div></fieldset>
                    @endif

                    @if($facets['brands']->count() > 1)
                        <fieldset><legend class="{{ $groupTitle }}">Brand</legend>
                            <div class="max-h-48 space-y-2 overflow-y-auto pr-1">@foreach($facets['brands'] as $b)
                                <label class="flex cursor-pointer items-center gap-2.5 text-sm"><input type="checkbox" wire:model.live="brands" value="{{ $b->slug }}" class="size-4 rounded accent-[var(--ink)]"> {{ $b->name }}</label>
                            @endforeach</div></fieldset>
                    @endif

                    <fieldset><legend class="{{ $groupTitle }}">Price (USD)</legend>
                        <div class="flex items-center gap-2">
                            <label class="sr-only" for="min">Minimum price</label><input id="min" type="number" inputmode="decimal" min="0" wire:model.live.debounce.500ms="min" placeholder="Min" class="input !h-10 !text-sm">
                            <span class="text-muted">–</span>
                            <label class="sr-only" for="max">Maximum price</label><input id="max" type="number" inputmode="decimal" min="0" wire:model.live.debounce.500ms="max" placeholder="Max" class="input !h-10 !text-sm">
                        </div></fieldset>

                    <fieldset><legend class="{{ $groupTitle }}">Rating</legend>
                        <div class="flex flex-wrap gap-2">@foreach([4, 3, 2] as $r)
                            <button type="button" wire:click="$set('rating', {{ $rating === $r ? 0 : $r }})" class="chip {{ $rating === $r ? 'is-on' : '' }}" aria-pressed="{{ $rating === $r ? 'true' : 'false' }}">{{ $r }}★ &amp; up</button>
                        @endforeach</div></fieldset>

                    <fieldset><legend class="{{ $groupTitle }}">Availability &amp; offers</legend>
                        <div class="space-y-2">
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm"><input type="checkbox" wire:model.live="inStock" class="size-4 rounded accent-[var(--ink)]"> In stock only</label>
                            @foreach([10, 25, 40] as $d)
                                <label class="flex cursor-pointer items-center gap-2.5 text-sm"><input type="radio" name="discount" wire:model.live="discount" value="{{ $d }}" class="size-4 accent-[var(--ink)]"> {{ $d }}% off or more</label>
                            @endforeach
                            @if($discount)<button type="button" wire:click="$set('discount', 0)" class="text-xs text-muted underline">Any discount</button>@endif
                        </div></fieldset>

                    @if($facets['colors']->count())
                        <fieldset><legend class="{{ $groupTitle }}">Colour</legend>
                            <div class="flex flex-wrap gap-2">@foreach($facets['colors'] as $c)
                                <label class="cursor-pointer" title="{{ $c->color }}">
                                    <input type="checkbox" wire:model.live="colors" value="{{ $c->color }}" class="peer sr-only">
                                    <span class="block size-7 rounded-full border border-line ring-offset-2 ring-offset-[var(--bg)] transition peer-checked:ring-2 peer-checked:ring-[var(--ink)] peer-focus-visible:ring-2" style="background: {{ $c->color_hex ?: '#999' }}"></span>
                                    <span class="sr-only">{{ $c->color }}</span>
                                </label>
                            @endforeach</div></fieldset>
                    @endif

                    @if($facets['sizes']->count())
                        <fieldset><legend class="{{ $groupTitle }}">Size</legend>
                            <div class="flex flex-wrap gap-2">@foreach($facets['sizes'] as $s)
                                <label class="cursor-pointer"><input type="checkbox" wire:model.live="sizes" value="{{ $s }}" class="peer sr-only"><span class="chip peer-checked:bg-ink peer-checked:text-bg peer-focus-visible:outline peer-focus-visible:outline-2">{{ $s }}</span></label>
                            @endforeach</div></fieldset>
                    @endif

                    @foreach($facets['attributes'] as $name => $values)
                        <fieldset><legend class="{{ $groupTitle }}">{{ $name }}</legend>
                            <div class="space-y-2">@foreach($values as $a)
                                <label class="flex cursor-pointer items-center gap-2.5 text-sm"><input type="checkbox" wire:model.live="attrs" value="{{ $a->name }}::{{ $a->value }}" class="size-4 rounded accent-[var(--ink)]"> {{ $a->value }}</label>
                            @endforeach</div></fieldset>
                    @endforeach
                </div>

                <div class="mt-8 flex gap-2 lg:hidden"><button type="button" wire:click="clearAll" class="btn btn-ghost flex-1">Clear all</button><button type="button" wire:click="$set('filtersOpen', false)" class="btn btn-primary flex-1">Show {{ $products->total() }}</button></div>
            </div>
        </aside>

        <section aria-label="Products" class="relative">
            {{-- Skeleton while filtering --}}
            <div wire:loading.delay.shortest class="absolute inset-0 z-20 bg-bg/70 backdrop-blur-[2px]">
                <div class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-3">
                    @for($i = 0; $i < 6; $i++)<div><div class="skeleton aspect-[4/5]"></div><div class="skeleton mt-3 h-3 w-1/3"></div><div class="skeleton mt-2 h-4 w-2/3"></div></div>@endfor
                </div>
            </div>

            @if($products->isEmpty())
                <x-empty-state icon="search" title="Nothing matches those filters" text="Try removing a filter or widening your price range.">
                    <button type="button" wire:click="clearAll" class="btn btn-primary mt-6">Clear all filters</button>
                </x-empty-state>
            @else
                <div class="{{ $layout === 'list' ? 'grid gap-4' : 'grid grid-cols-2 gap-x-4 gap-y-9 md:grid-cols-3' }}">
                    @foreach($products as $product)
                        <x-product-card :product="$product" :layout="$layout" wire:key="p-{{ $product->id }}" />
                    @endforeach
                </div>
                <div class="mt-12">{{ $products->links() }}</div>
            @endif
        </section>
    </div>
</div>
