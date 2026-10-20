<div x-data x-show="$wire.open" x-cloak class="fixed inset-0 z-[75]" role="dialog" aria-modal="true" aria-label="Shopping cart" @keydown.escape.window="$wire.open = false">
    <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" x-show="$wire.open" x-transition.opacity @click="$wire.open = false"></div>

    <aside class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-bg shadow-2xl"
           x-show="$wire.open" x-trap.noscroll="$wire.open"
           x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
        <div class="flex h-16 items-center justify-between border-b border-line px-5">
            <h2 class="font-display text-xl font-bold">Your cart <span class="text-muted tabular">({{ $summary['lines']->sum('quantity') }})</span></h2>
            <button type="button" class="btn btn-ghost btn-icon border-transparent" @click="$wire.open = false" aria-label="Close cart"><x-icon name="x" /></button>
        </div>

        @if($summary['lines']->isEmpty())
            <div class="grid flex-1 place-items-center px-8 text-center">
                <div>
                    <span class="mx-auto mb-5 grid size-14 place-items-center rounded-full bg-surface-2"><x-icon name="bag" class="size-6 text-muted" /></span>
                    <p class="font-display text-2xl font-bold">Nothing here yet</p>
                    <p class="mt-2 text-sm text-muted">Find something that matches your world.</p>
                    <div class="mt-6 flex justify-center gap-2">
                        <a href="{{ route('shop') }}" class="btn btn-primary">Shop now</a>
                        <a href="{{ route('match') }}" class="btn btn-ghost">Try Match</a>
                    </div>
                </div>
            </div>
        @else
            @if($summary['free_shipping_threshold_cents'] > 0)
                <div class="border-b border-line px-5 py-3">
                    @if($summary['free_shipping_remaining_cents'] > 0)
                        <p class="text-xs"><strong class="tabular">{{ \App\Support\Money::format($summary['free_shipping_remaining_cents']) }}</strong> away from free standard shipping</p>
                    @else
                        <p class="flex items-center gap-1.5 text-xs font-semibold text-success"><x-icon name="check" class="size-3.5" /> You've unlocked free standard shipping</p>
                    @endif
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-surface-2" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                         aria-valuenow="{{ min(100, (int) round(($summary['subtotal_cents'] - $summary['discount_cents']) / max(1, $summary['free_shipping_threshold_cents']) * 100)) }}">
                        <div class="h-full rounded-full bg-accent transition-all duration-500" style="width: {{ min(100, ($summary['subtotal_cents'] - $summary['discount_cents']) / max(1, $summary['free_shipping_threshold_cents']) * 100) }}%"></div>
                    </div>
                </div>
            @endif

            <ul class="flex-1 divide-y divide-line overflow-y-auto px-5">
                @foreach($summary['lines'] as $line)
                    @php $v = $line['variant']; $p = $v->product; @endphp
                    <li class="flex gap-4 py-4" wire:key="drawer-{{ $v->id }}">
                        <a href="{{ route('product.show', $p) }}" class="size-24 shrink-0 overflow-hidden rounded-lg bg-surface-2"><x-img :image="$p->images->first()" :alt="$p->name" :seed="$p->slug.'-1'" /></a>
                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex justify-between gap-3">
                                <div class="min-w-0"><a href="{{ route('product.show', $p) }}" class="line-clamp-2 text-sm font-semibold hover:underline">{{ $p->name }}</a>
                                    <p class="mt-0.5 text-xs text-muted">{{ $v->label() }}</p></div>
                                <span class="text-sm font-semibold tabular">{{ \App\Support\Money::format($line['line_cents']) }}</span>
                            </div>
                            @if($line['warning'])<p class="mt-1 text-xs font-medium text-danger" role="alert">{{ $line['warning'] }}</p>@endif
                            <div class="mt-auto flex items-center justify-between pt-3">
                                <div class="inline-flex items-center rounded-full border border-line" role="group" aria-label="Quantity for {{ $p->name }}">
                                    <button type="button" wire:click="decrement({{ $v->id }})" class="grid size-8 place-items-center rounded-full hover:bg-surface-2 active:scale-90" aria-label="Decrease quantity"><x-icon name="minus" class="size-3.5" /></button>
                                    <span class="w-7 text-center text-sm font-semibold tabular" aria-live="polite">{{ $line['quantity'] }}</span>
                                    <button type="button" wire:click="increment({{ $v->id }})" class="grid size-8 place-items-center rounded-full hover:bg-surface-2 active:scale-90 disabled:opacity-40" aria-label="Increase quantity" @disabled($line['quantity'] >= $v->stock || $line['quantity'] >= config('vyora.max_cart_quantity'))><x-icon name="plus" class="size-3.5" /></button>
                                </div>
                                <button type="button" wire:click="remove({{ $v->id }})" class="text-xs text-muted underline-offset-4 hover:text-danger hover:underline">Remove</button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="space-y-3 border-t border-line bg-surface px-5 py-5">
                <dl class="space-y-1.5 text-sm tabular">
                    <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd>{{ \App\Support\Money::format($summary['subtotal_cents']) }}</dd></div>
                    @if($summary['discount_cents'] > 0)<div class="flex justify-between text-success"><dt>Discount</dt><dd>−{{ \App\Support\Money::format($summary['discount_cents']) }}</dd></div>@endif
                    <div class="flex justify-between text-muted"><dt>Shipping &amp; tax</dt><dd>Calculated at checkout</dd></div>
                </dl>
                <a href="{{ route('checkout') }}" class="btn btn-accent btn-lg w-full">Checkout · {{ \App\Support\Money::format($summary['subtotal_cents'] - $summary['discount_cents']) }}</a>
                <a href="{{ route('cart') }}" class="btn btn-ghost w-full">View full cart</a>
            </div>
        @endif
    </aside>
</div>
