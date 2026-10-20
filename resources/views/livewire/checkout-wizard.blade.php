@php
    $steps = [1 => 'Contact', 2 => 'Shipping', 3 => 'Delivery', 4 => 'Payment', 5 => 'Review'];
    $addr = fn ($k) => $address[$k] ?? '';
@endphp
<div class="grid gap-10 lg:grid-cols-[1fr_26rem]">
    <div>
        {{-- Stepper --}}
        <ol class="mb-8 flex items-center gap-2 overflow-x-auto scrollbar-none text-sm" aria-label="Checkout progress">
            @foreach($steps as $n => $label)
                <li class="flex shrink-0 items-center gap-2">
                    <button type="button" wire:click="goTo({{ $n }})" @disabled($n >= $step) class="flex items-center gap-2 {{ $n === $step ? 'font-bold' : ($n < $step ? 'text-ink' : 'text-muted') }}" @if($n === $step) aria-current="step" @endif>
                        <span class="grid size-7 place-items-center rounded-full text-xs font-bold {{ $n < $step ? 'bg-accent text-accent-ink' : ($n === $step ? 'bg-ink text-bg' : 'border border-line') }}">@if($n < $step)<x-icon name="check" class="size-3.5" :stroke="3" />@else{{ $n }}@endif</span>
                        <span class="hidden sm:inline">{{ $label }}</span>
                    </button>
                    @if(! $loop->last)<span class="h-px w-4 bg-line sm:w-8"></span>@endif
                </li>
            @endforeach
        </ol>

        @if($error)<div class="mb-6 flex items-start gap-3 rounded-xl border border-danger/40 bg-danger/10 p-4 text-sm" role="alert"><x-icon name="alert" class="mt-0.5 size-5 shrink-0 text-danger" /><p>{{ $error }}</p></div>@endif

        <div class="card p-6 sm:p-8 animate-rise" wire:key="checkout-step-{{ $step }}">
            @if($step === 1)
                <h2 class="font-display text-2xl font-bold">Contact information</h2>
                @guest<p class="mt-1 text-sm text-muted">Checking out as a guest. <a href="{{ route('login') }}" class="font-semibold underline underline-offset-4">Sign in</a> to use saved addresses.</p>@endguest
                <div class="mt-6">
                    <label for="email" class="label">Email address <span class="text-danger" aria-hidden="true">*</span></label>
                    <input id="email" type="email" wire:model="email" autocomplete="email" class="input @error('email') is-invalid @enderror" required @error('email') aria-invalid="true" @enderror>
                    @error('email')<p class="field-error" role="alert">{{ $message }}</p>@else<p class="field-help">Your order confirmation is sent here.</p>@enderror
                </div>

            @elseif($step === 2)
                <h2 class="font-display text-2xl font-bold">Shipping address</h2>
                @if($saved->isNotEmpty())
                    <fieldset class="mt-6 space-y-3"><legend class="sr-only">Saved addresses</legend>
                        @foreach($saved as $a)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition {{ $addressId === $a->id ? 'border-ink bg-surface-2' : 'border-line hover:border-ink' }}">
                                <input type="radio" wire:model.live="addressId" value="{{ $a->id }}" class="mt-1 accent-[var(--ink)]">
                                <span class="text-sm"><strong class="block">{{ $a->label }} · {{ $a->full_name }}</strong>{{ $a->line1 }}@if($a->line2), {{ $a->line2 }}@endif, {{ $a->city }} {{ $a->state }} {{ $a->postal_code }}, {{ $a->country }}</span>
                            </label>
                        @endforeach
                        <button type="button" wire:click="useNewAddress" class="text-sm font-semibold underline underline-offset-4">+ Use a new address</button>
                        @error('addressId')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    </fieldset>
                @endif
                @if(! $addressId || $saved->isEmpty())
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach([['full_name', 'Full name', 'name', 'sm:col-span-2'], ['phone', 'Phone (optional)', 'tel', 'sm:col-span-2'], ['line1', 'Address', 'address-line1', 'sm:col-span-2'], ['line2', 'Apartment, suite (optional)', 'address-line2', 'sm:col-span-2'], ['city', 'City', 'address-level2', ''], ['state', 'State / region', 'address-level1', ''], ['postal_code', 'Postal code', 'postal-code', ''], ['country', 'Country (2-letter code)', 'country', '']] as [$k, $label, $ac, $span])
                            <div class="{{ $span }}">
                                <label for="a-{{ $k }}" class="label">{{ $label }}</label>
                                <input id="a-{{ $k }}" wire:model="address.{{ $k }}" autocomplete="{{ $ac }}" @if($k === 'country') maxlength="2" @endif class="input @error('address.'.$k) is-invalid @enderror" @error('address.'.$k) aria-invalid="true" @enderror>
                                @error('address.'.$k)<p class="field-error" role="alert">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                    @auth<label class="mt-5 flex items-center gap-2.5 text-sm"><input type="checkbox" wire:model="saveAddress" class="size-4 rounded accent-[var(--ink)]"> Save this address to my account</label>@endauth
                @endif

            @elseif($step === 3)
                <h2 class="font-display text-2xl font-bold">Delivery method</h2>
                <fieldset class="mt-6 space-y-3"><legend class="sr-only">Delivery method</legend>
                    @foreach($methods as $key => $m)
                        @php
                            $cost = app(\App\Services\PricingService::class)->shippingCents($key, $summary['subtotal_cents'] - $summary['discount_cents']);
                            [$from, $to] = app(\App\Services\PricingService::class)->deliveryWindow($key);
                        @endphp
                        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border p-4 transition {{ $shippingMethod === $key ? 'border-ink bg-surface-2' : 'border-line hover:border-ink' }}">
                            <span class="flex items-start gap-3"><input type="radio" wire:model.live="shippingMethod" value="{{ $key }}" class="mt-1 accent-[var(--ink)]">
                                <span><strong class="block">{{ $m['label'] }}</strong><span class="text-sm text-muted">Estimated {{ \Carbon\Carbon::parse($from)->format('M j') }}–{{ \Carbon\Carbon::parse($to)->format('M j') }}</span></span></span>
                            <strong class="tabular">{{ $cost === 0 ? 'Free' : \App\Support\Money::format($cost) }}</strong>
                        </label>
                    @endforeach
                </fieldset>
                @error('shippingMethod')<p class="field-error" role="alert">{{ $message }}</p>@enderror

            @elseif($step === 4)
                <h2 class="font-display text-2xl font-bold">Payment</h2>
                @if($provider === 'stripe')
                    <div class="mt-6 flex items-start gap-3 rounded-xl bg-surface-2 p-4 text-sm"><x-icon name="lock" class="mt-0.5 size-5 shrink-0" />
                        <p>You'll be redirected to Stripe's secure payment page to enter your card details. VYORA never sees or stores your card number. Your order is confirmed only after Stripe confirms the payment.</p></div>
                @elseif($provider === 'demo')
                    <div class="mt-6 rounded-xl border border-dashed border-warn p-4 text-sm"><p class="flex items-center gap-2 font-semibold text-warn"><x-icon name="alert" class="size-4" /> Demo checkout mode</p>
                        <p class="mt-1.5 text-muted">Stripe isn't configured on this site, so payment is simulated. <strong class="text-ink">No card will be charged and no real payment is taken.</strong> Orders placed this way are labelled “demo” everywhere.</p></div>
                @else
                    <div class="mt-6 rounded-xl border border-danger/40 bg-danger/10 p-4 text-sm" role="alert">Online payment isn't available right now. Please contact us to place your order.</div>
                @endif

            @else
                <h2 class="font-display text-2xl font-bold">Review your order</h2>
                <dl class="mt-6 divide-y divide-line text-sm">
                    <div class="flex justify-between gap-4 py-3"><dt class="text-muted">Contact</dt><dd class="text-right">{{ $email }}</dd><button type="button" wire:click="goTo(1)" class="font-semibold underline underline-offset-4">Edit</button></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-muted">Ship to</dt>
                        <dd class="text-right">@php $shown = $addressId ? $saved->firstWhere('id', $addressId) : null; @endphp
                            @if($shown){{ $shown->full_name }}, {{ $shown->line1 }}, {{ $shown->city }} {{ $shown->postal_code }}@else{{ $address['full_name'] }}, {{ $address['line1'] }}, {{ $address['city'] }} {{ $address['postal_code'] }}@endif</dd>
                        <button type="button" wire:click="goTo(2)" class="font-semibold underline underline-offset-4">Edit</button></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-muted">Delivery</dt><dd class="text-right">{{ $methods[$shippingMethod]['label'] }}</dd><button type="button" wire:click="goTo(3)" class="font-semibold underline underline-offset-4">Edit</button></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-muted">Payment</dt><dd class="text-right">{{ $provider === 'stripe' ? 'Card via Stripe (secure redirect)' : ($provider === 'demo' ? 'Demo mode — no real charge' : 'Unavailable') }}</dd><span></span></div>
                </dl>
                <p class="mt-4 text-xs text-muted">By placing your order you agree to our <a class="underline" href="{{ route('terms') }}">Terms</a> and <a class="underline" href="{{ route('returns') }}">Returns policy</a>.</p>
            @endif

            <div class="mt-8 flex items-center justify-between gap-3">
                @if($step > 1)<button type="button" wire:click="goTo({{ $step - 1 }})" class="btn btn-ghost"><x-icon name="arrow-left" class="size-4" /> Back</button>@else<a href="{{ route('cart') }}" class="btn btn-ghost"><x-icon name="arrow-left" class="size-4" /> Cart</a>@endif
                @if($step < 5)
                    <button type="button" wire:click="next" class="btn btn-primary btn-lg" wire:loading.attr="disabled">Continue <x-icon name="arrow-right" class="size-4" /></button>
                @else
                    <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder" class="btn btn-accent btn-lg" @disabled($provider === null)>
                        <span wire:loading.remove wire:target="placeOrder">{{ $provider === 'stripe' ? 'Pay '.\App\Support\Money::format($summary['total_cents']) : 'Place order · '.\App\Support\Money::format($summary['total_cents']) }}</span>
                        <span wire:loading wire:target="placeOrder" class="inline-flex items-center gap-2"><span class="size-4 animate-spin rounded-full border-2 border-accent-ink/30 border-t-accent-ink"></span> Processing…</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Order summary --}}
    <aside class="lg:sticky lg:top-24 lg:self-start" aria-label="Order summary">
        <div class="card p-6">
            <h2 class="font-display text-xl font-bold">Order summary</h2>
            <ul class="mt-5 max-h-72 divide-y divide-line overflow-y-auto">
                @foreach($summary['lines'] as $line)
                    @php $p = $line['variant']->product; @endphp
                    <li class="flex gap-3 py-3 first:pt-0" wire:key="co-{{ $line['variant']->id }}">
                        <span class="relative size-16 shrink-0 overflow-hidden rounded-lg bg-surface-2"><x-img :image="$p->images->first()" :alt="$p->name" :seed="$p->slug.'-1'" /><span class="absolute -right-0 -top-0 grid min-w-5 place-items-center rounded-bl-lg bg-ink px-1 text-[.7rem] font-bold leading-5 text-bg">{{ $line['quantity'] }}</span></span>
                        <span class="min-w-0 flex-1 text-sm"><span class="line-clamp-1 font-semibold">{{ $p->name }}</span><span class="text-xs text-muted">{{ $line['variant']->label() }}</span>@if($line['warning'])<span class="block text-xs text-danger">{{ $line['warning'] }}</span>@endif</span>
                        <span class="text-sm font-semibold tabular">{{ \App\Support\Money::format($line['line_cents']) }}</span>
                    </li>
                @endforeach
            </ul>

            <form wire:submit="applyCoupon" class="mt-5 flex gap-2">
                <label for="co-coupon" class="sr-only">Coupon code</label>
                <input id="co-coupon" wire:model="coupon" placeholder="Coupon code" class="input !h-10 uppercase" autocomplete="off">
                <button class="btn btn-ghost btn-sm !h-10">Apply</button>
            </form>
            @if($couponMessage)<p class="mt-2 text-xs {{ $summary['coupon'] ? 'text-success' : 'text-danger' }}" role="status">{{ $couponMessage }}</p>@endif

            <dl class="mt-5 space-y-2 border-t border-line pt-5 text-sm tabular">
                <div class="flex justify-between"><dt class="text-muted">Subtotal</dt><dd>{{ \App\Support\Money::format($summary['subtotal_cents']) }}</dd></div>
                @if($summary['discount_cents'] > 0)<div class="flex justify-between text-success"><dt>Discount</dt><dd>−{{ \App\Support\Money::format($summary['discount_cents']) }}</dd></div>@endif
                <div class="flex justify-between"><dt class="text-muted">Shipping</dt><dd>{{ $summary['shipping_cents'] === 0 ? 'Free' : \App\Support\Money::format($summary['shipping_cents']) }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted">Tax</dt><dd>{{ \App\Support\Money::format($summary['tax_cents']) }}</dd></div>
                <div class="flex justify-between border-t border-line pt-3 text-lg font-bold"><dt>Total</dt><dd>{{ \App\Support\Money::format($summary['total_cents']) }}</dd></div>
            </dl>
        </div>
    </aside>
</div>
