@php
    $s = $settings;
    $groups = [
        'Orders & payment' => [
            ['How do I place an order?', 'Add items to your cart, then check out as a guest or with an account. You\'ll enter contact details, a shipping address and a delivery method, review everything, and pay.'],
            ['Which payment methods do you accept?', 'Payments are processed by Stripe on their secure hosted page, so you can use any method Stripe offers for your region. VYORA never sees or stores your card number.'],
            ['Can I use a coupon code?', 'Yes. Enter it in the cart or at checkout. Codes have their own start and end dates and may require a minimum spend; the Deals page lists public codes.'],
            ['Can I change or cancel my order?', 'Unpaid orders can be cancelled from your account. Once an order is paid, contact us and we\'ll cancel it if it hasn\'t shipped.'],
        ],
        'Shipping & delivery' => [
            ['How long does delivery take?', 'Orders are processed within '.$s['processing_days'].' business days, then standard delivery takes 4–7 days and express 1–3 days. You\'ll see an exact estimate before you pay.'],
            ['How much is shipping?', 'Standard shipping is free over $'.number_format((float) config('vyora.free_shipping_threshold'), 0).'. Otherwise the fee is shown at checkout before you confirm.'],
            ['Where do you ship?', $s['shipping_regions']],
            ['How do I track my order?', 'Sign in and open the order in your account. The timeline shows each stage we have actually recorded, plus the carrier and tracking number once the parcel ships.'],
        ],
        'Returns' => [
            ['What is your return window?', 'You have '.$s['returns_days'].' days from delivery to return unused items in their original packaging.'],
            ['Who pays for return shipping?', $s['return_shipping']],
            ['When will I get my refund?', 'Refunds go back to your original payment method after we receive and inspect the return.'],
        ],
        'Your account & VYORA Match' => [
            ['Do I need an account to buy?', 'No — guest checkout is available. An account adds saved addresses, a wishlist that follows you, order history and personalised picks.'],
            ['How does VYORA Match work?', 'It\'s a rule-based picker: your answers are scored against real product data such as category, price, style and feature tags, ratings and stock, and each result explains why it matched. It is not an AI model.'],
            ['How are reviews handled?', 'Only customers whose order has shipped can review a product, and every review is moderated before it appears.'],
        ],
    ];
    $ld = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => collect($groups)->flatten(1)->map(fn ($q) => ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]])->values()->all()];
@endphp
<x-layouts.app title="FAQ" description="Answers about ordering, payment, shipping, returns, accounts and VYORA Match." :jsonld="$ld">
    <div class="mx-auto max-w-3xl px-4 pt-8 md:px-8">
        <x-breadcrumbs :items="[['label' => 'FAQ']]" />
        <h1 class="display-xl">Frequently asked questions</h1>
        @foreach($groups as $title => $qs)
            <section class="mt-12" aria-labelledby="g-{{ $loop->index }}"><h2 id="g-{{ $loop->index }}" class="eyebrow mb-3 !font-sans">{{ $title }}</h2>
                <div class="divide-y divide-line rounded-2xl border border-line bg-surface">
                    @foreach($qs as [$q, $a])
                        <div x-data="{ open: false }">
                            <h3><button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-base font-semibold tracking-normal">{{ $q }}<x-icon name="plus" class="size-5 shrink-0 transition duration-300" ::class="open && 'rotate-45'" /></button></h3>
                            <div x-show="open" x-collapse x-cloak><p class="px-5 pb-5 text-muted">{{ $a }}</p></div>
                        </div>
                    @endforeach
                </div></section>
        @endforeach
        <p class="mt-12 text-center text-muted">Still stuck? <a href="{{ route('contact') }}" class="font-semibold text-ink underline underline-offset-4">Contact us</a>.</p>
    </div>
</x-layouts.app>
