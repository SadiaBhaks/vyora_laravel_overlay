@php
    $total = \App\Livewire\MatchWizard::TOTAL;
    $pct = min(100, (int) round(($step - 1) / $total * 100));
    $opt = fn ($on) => 'group relative flex min-h-[4.5rem] items-center justify-between gap-3 rounded-2xl border p-4 text-left font-display text-xl font-bold tracking-tight transition '.($on ? 'border-ink bg-ink text-bg' : 'border-line bg-surface hover:border-ink');
@endphp
<div class="mx-auto max-w-3xl">
    @if($step <= $total)
        <div class="mb-8" aria-live="polite">
            <div class="mb-2 flex items-center justify-between text-xs font-semibold uppercase tracking-widest text-muted"><span>Step {{ $step }} of {{ $total }}</span><span class="tabular">{{ $pct }}%</span></div>
            <div class="h-1.5 overflow-hidden rounded-full bg-surface-2" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}"><div class="h-full rounded-full bg-accent transition-all duration-500" style="width: {{ max(6, $pct) }}%"></div></div>
        </div>

        <div wire:key="step-{{ $step }}" class="animate-rise">
            @if($step === 1)
                <h2 class="display-lg text-balance">What are you shopping for?</h2>
                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                    @foreach($categories as $c)
                        <button type="button" wire:click="choose('category', '{{ $c->slug }}')" class="{{ $opt($category === $c->slug) }}" aria-pressed="{{ $category === $c->slug ? 'true' : 'false' }}">{{ $c->name }}<x-icon name="arrow-right" class="size-5 opacity-50 transition group-hover:translate-x-1" /></button>
                    @endforeach
                    <button type="button" wire:click="choose('category', 'any')" class="{{ $opt($category === 'any') }} sm:col-span-2" aria-pressed="{{ $category === 'any' ? 'true' : 'false' }}">Surprise me — anything<x-icon name="sparkles" class="size-5" /></button>
                </div>
                @error('category')<p class="field-error mt-3" role="alert">{{ $message }}</p>@enderror

            @elseif($step === 2)
                <h2 class="display-lg text-balance">What's your budget?</h2>
                <div class="mt-8 grid gap-3">@foreach($budgets as $key => [$label])
                    <button type="button" wire:click="choose('budget', '{{ $key }}')" class="{{ $opt($budget === $key) }}" aria-pressed="{{ $budget === $key ? 'true' : 'false' }}">{{ $label }}<x-icon name="arrow-right" class="size-5 opacity-50" /></button>
                @endforeach</div>

            @elseif($step === 3)
                <h2 class="display-lg text-balance">Which style feels like you?</h2>
                <div class="mt-8 grid gap-3 sm:grid-cols-2">@foreach($styles as $key => $label)
                    <button type="button" wire:click="choose('style', '{{ $key }}')" class="{{ $opt($style === $key) }}" aria-pressed="{{ $style === $key ? 'true' : 'false' }}">{{ $label }}<x-icon name="arrow-right" class="size-5 opacity-50" /></button>
                @endforeach</div>
                <button type="button" wire:click="choose('style', '')" class="mt-4 text-sm text-muted underline underline-offset-4">No preference</button>

            @elseif($step === 4)
                <h2 class="display-lg text-balance">Which features matter most?</h2>
                <p class="mt-2 text-sm text-muted">Pick as many as you like.</p>
                <div class="mt-8 flex flex-wrap gap-2.5">@foreach($featureOptions as $key => $label)
                    <button type="button" wire:click="toggleFeature('{{ $key }}')" class="chip !h-12 !px-5 !text-base {{ in_array($key, $features) ? 'is-on' : '' }}" aria-pressed="{{ in_array($key, $features) ? 'true' : 'false' }}">@if(in_array($key, $features))<x-icon name="check" class="size-4" />@endif{{ $label }}</button>
                @endforeach</div>

            @else
                <h2 class="display-lg text-balance">Is it for you, or a gift?</h2>
                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                    <button type="button" wire:click="choose('recipient', 'me')" class="{{ $opt($recipient === 'me') }}" aria-pressed="{{ $recipient === 'me' ? 'true' : 'false' }}">For me<x-icon name="user" class="size-5 opacity-60" /></button>
                    <button type="button" wire:click="choose('recipient', 'gift')" class="{{ $opt($recipient === 'gift') }}" aria-pressed="{{ $recipient === 'gift' ? 'true' : 'false' }}">A gift<x-icon name="gift" class="size-5 opacity-60" /></button>
                </div>
            @endif
        </div>

        <div class="mt-10 flex items-center justify-between">
            <button type="button" wire:click="back" class="btn btn-ghost" @disabled($step === 1)><x-icon name="arrow-left" class="size-4" /> Back</button>
            <button type="button" wire:click="next" class="btn btn-primary btn-lg">{{ $step === $total ? 'See my matches' : 'Continue' }} <x-icon name="arrow-right" class="size-4" /></button>
        </div>
    @else
        <div class="animate-rise">
            <p class="eyebrow">Your matches</p>
            <h2 class="display-lg mt-2 text-balance">{{ $results->isEmpty() ? 'No exact match — let\'s widen it' : 'Here\'s what fits your world' }}</h2>
            <p class="mt-3 flex items-start gap-2 text-sm text-muted"><x-icon name="info" class="mt-0.5 size-4 shrink-0" /> Matches come from transparent scoring rules applied to our live catalogue (category, budget, style and feature tags, ratings, stock). VYORA Match is rule-based, not an AI model.</p>
        </div>

        @if($results->isEmpty())
            <x-empty-state icon="sparkles" title="Nothing fits all of that" text="Try a higher budget, fewer features, or a different category." class="mt-8">
                <button type="button" wire:click="refine" class="btn btn-primary mt-6">Refine preferences</button>
            </x-empty-state>
        @else
            <ol class="mt-8 space-y-5">
                @foreach($results as $i => $r)
                    @php $p = $r['product']; $avail = $p->variants->where('stock', '>', 0); @endphp
                    <li class="card grid gap-5 p-4 sm:grid-cols-[11rem_1fr] sm:p-5" wire:key="m-{{ $p->id }}">
                        <a href="{{ route('product.show', $p) }}" class="relative block aspect-square overflow-hidden rounded-xl bg-surface-2">
                            <x-img :image="$p->images->first()" :alt="$p->name" :seed="$p->slug.'-1'" :cat="$p->category?->slug ?? 'lifestyle'" />
                            <span class="badge badge-accent absolute left-2 top-2">#{{ $i + 1 }} match</span>
                        </a>
                        <div class="flex flex-col">
                            <p class="eyebrow !text-[.66rem]">{{ $p->brand?->name }}</p>
                            <h3 class="font-display text-xl font-bold"><a href="{{ route('product.show', $p) }}" class="hover:underline">{{ $p->name }}</a></h3>
                            <div class="mt-1 flex items-center gap-3"><x-price :product="$p" /><x-rating :value="$p->rating_avg" :count="$p->rating_count" /></div>
                            <div class="mt-3"><p class="text-xs font-semibold uppercase tracking-widest text-muted">Why it matches</p>
                                <ul class="mt-1.5 space-y-1 text-sm">@foreach($r['reasons'] as $reason)<li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 shrink-0 text-success" />{{ $reason }}</li>@endforeach</ul></div>
                            <div class="mt-auto flex flex-wrap gap-2 pt-4">
                                <a href="{{ route('product.show', $p) }}" class="btn btn-ghost btn-sm">View product</a>
                                @if($avail->count() === 1)
                                    <button type="button" x-data @click="vyora.addToCart({{ $avail->first()->id }})" class="btn btn-primary btn-sm"><x-icon name="bag" class="size-4" /> Add to cart</button>
                                @else
                                    <button type="button" x-data @click="vyora.quickView({{ $p->id }})" class="btn btn-primary btn-sm"><x-icon name="bag" class="size-4" /> Choose options</button>
                                @endif
                                <button type="button" x-data @click="vyora.toggleWishlist('{{ $p->slug }}', {{ $p->id }}, $el)" :aria-pressed="$store.wl.ids.includes({{ $p->id }})" class="btn btn-ghost btn-sm"><x-icon name="heart" class="size-4" x-bind:class="$store.wl.ids.includes({{ $p->id }}) ? 'fill-current text-danger' : ''" /> Save</button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
            <div class="mt-8 flex flex-wrap gap-3"><button type="button" wire:click="refine" class="btn btn-ghost">Refine preferences</button><button type="button" wire:click="restart" class="btn btn-ghost">Start over</button></div>
        @endif
    @endif
</div>
