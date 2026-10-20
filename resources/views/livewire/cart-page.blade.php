<div>
    @if($summary['lines']->isEmpty())
        <x-empty-state icon="bag" title="Your cart is empty" text="Add something you love — or let VYORA Match point the way." :action="route('shop')" actionLabel="Start shopping">
            <a href="{{ route('match') }}" class="mt-3 text-sm font-semibold underline underline-offset-4">Discover your match</a>
        </x-empty-state>
    @else
        <div class="grid gap-10 lg:grid-cols-[1fr_24rem]">
            <section aria-labelledby="cart-heading">
                <h1 id="cart-heading" class="sr-only">Cart items</h1>

                @if($summary['free_shipping_threshold_cents'] > 0)
                    <div class="card mb-5 p-4">
                        @if($summary['free_shipping_remaining_cents'] > 0)
                            <p class="text-sm">Add <strong class="tabular">{{ \App\Support\Money::format($summary['free_shipping_remaining_cents']) }}</strong> more for free standard shipping.</p>
                        @else
                            <p class="flex items-center gap-2 text-sm font-semibold text-success"><x-icon name="check" class="size-4" /> Free standard shipping unlocked.</p>
                        @endif
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-surface-2"><div class="h-full rounded-full bg-accent transition-all duration-500" style="width: {{ min(100, ($summary['subtotal_cents'] - $summary['discount_cents']) / max(1, $summary['free_shipping_threshold_cents']) * 100) }}%"></div></div>
                    </div>
                @endif

                <ul class="divide-y divide-line card px-4 sm:px-6">
                    @foreach($summary['lines'] as $line)
                        @php $v = $line['variant']; $p = $v->product; @endphp
                        <li class="grid grid-cols-[6rem_1fr] gap-4 py-5 sm:grid-cols-[8rem_1fr] sm:gap-6" wire:key="cart-{{ $v->id }}">
                            <a href="{{ route('product.show', $p) }}" class="aspect-square overflow-hidden rounded-xl bg-surface-2"><x-img :image="$p->images->first()" :alt="$p->name" :seed="$p->slug.'-1'" /></a>
                            <div class="flex flex-col">
                                <div class="flex justify-between gap-4">
                                    <div><p class="eyebrow !text-[.66rem]">{{ $p->brand?->name }}</p>
                                        <a href="{{ route('product.show', $p) }}" class="font-semibold hover:underline">{{ $p->name }}</a>
                                        <p class="mt-0.5 text-sm text-muted">{{ $v->label() }}</p></div>
                                    <p class="font-semibold tabular">{{ \App\Support\Money::format($line['line_cents']) }}</p>
                                </div>
                                <p class="mt-1 text-xs text-muted tabular">{{ \App\Support\Money::format($line['unit_cents']) }} each</p>
                                @if($line['warning'])<p class="mt-1 text-xs font-medium text-danger" role="alert">{{ $line['warning'] }}</p>@endif
                                <div class="mt-auto flex items-center justify-between pt-4">
                                    <div class="inline-flex items-center rounded-full border border-line" role="group" aria-label="Quantity for {{ $p->name }}">
                                        <button type="button" wire:click="decrement({{ $v->id }})" class="grid size-9 place-items-center rounded-full hover:bg-surface-2 active:scale-90" aria-label="Decrease quantity"><x-icon name="minus" class="size-4" /></button>
                                        <span class="w-8 text-center text-sm font-semibold tabular" aria-live="polite">{{ $line['quantity'] }}</span>
                                        <button type="button" wire:click="increment({{ $v->id }})" class="grid size-9 place-items-center rounded-full hover:bg-surface-2 active:scale-90 disabled:opacity-40" aria-label="Increase quantity" @disabled($line['quantity'] >= $v->stock || $line['quantity'] >= config('vyora.max_cart_quantity'))><x-icon name="plus" class="size-4" /></button>
                                    </div>
                                    <button type="button" wire:click="remove({{ $v->id }})" class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-danger"><x-icon name="trash" class="size-4" /> Remove</button>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <aside class="lg:sticky lg:top-24 lg:self-start" aria-labelledby="summary-heading">
                <div class="card p-6">
                    <h2 id="summary-heading" class="font-display text-xl font-bold">Order summary</h2>

                    <form wire:submit="applyCoupon" class="mt-5">
                        <label for="coupon" class="label">Coupon code</label>
                        <div class="flex gap-2">
                            <input id="coupon" wire:model="coupon" class="input !h-11 uppercase" placeholder="WELCOME10" autocomplete="off">
                            <button class="btn btn-ghost !h-11">Apply</button>
                        </div>
                        @if($couponMessage)<p class="mt-2 text-xs {{ $summary['coupon'] ? 'text-success' : 'text-danger' }}" role="status">{{ $couponMessage }}</p>@endif
                        @if($summary['coupon'])<button type="button" wire:click="removeCoupon" class="mt-1 text-xs text-muted underline">Remove coupon</button>@endif
                    </form>

                    <dl class="mt-6 space-y-2.5 text-sm tabular">
                        <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd>{{ \App\Support\Money::format($summary['subtotal_cents']) }}</dd></div>
                        @if($summary['discount_cents'] > 0)<div class="flex justify-between text-success"><dt>Discount ({{ $summary['coupon']->code }})</dt><dd>−{{ \App\Support\Money::format($summary['discount_cents']) }}</dd></div>@endif
                        <div class="flex justify-between"><dt class="text-muted">Shipping (standard)</dt><dd>{{ $summary['shipping_cents'] === 0 ? 'Free' : \App\Support\Money::format($summary['shipping_cents']) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-muted">Estimated tax</dt><dd>{{ \App\Support\Money::format($summary['tax_cents']) }}</dd></div>
                        <div class="flex justify-between border-t border-line pt-3 text-base font-bold"><dt>Total</dt><dd>{{ \App\Support\Money::format($summary['total_cents']) }}</dd></div>
                    </dl>
                    @php [$from, $to] = app(\App\Services\PricingService::class)->deliveryWindow('standard'); @endphp
                    <p class="mt-4 flex items-center gap-2 text-xs text-muted"><x-icon name="truck" class="size-4" /> Estimated delivery {{ \Carbon\Carbon::parse($from)->format('M j') }}–{{ \Carbon\Carbon::parse($to)->format('M j') }}</p>

                    <a href="{{ route('checkout') }}" class="btn btn-accent btn-lg mt-6 w-full">Checkout securely</a>
                    <a href="{{ route('shop') }}" class="mt-3 block text-center text-sm text-muted underline-offset-4 hover:underline">Continue shopping</a>
                </div>
            </aside>
        </div>

        @if($suggested->isNotEmpty())
            <section class="mt-16" aria-labelledby="add-heading">
                <x-section-heading eyebrow="Recommended additions" title="Pairs well with your cart" />
                <div class="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-4">
                    @foreach($suggested as $p)<x-product-card :product="$p" wire:key="sug-{{ $p->id }}" />@endforeach
                </div>
            </section>
        @endif
    @endif
</div>
