<x-layouts.app title="Returns" description="VYORA's returns and refunds policy.">
    <div class="mx-auto max-w-3xl px-4 pt-8 md:px-8"><x-breadcrumbs :items="[['label' => 'Returns']]" />
        <h1 class="display-xl">Returns &amp; refunds</h1>
        <div class="prose-vyora mt-8">
            <p>Not right? Return it within <strong>{{ $settings['returns_days'] }} days of delivery</strong>.</p>
            <h2>How it works</h2>
            <ol><li>Contact us with your order number and the item(s) you want to return.</li><li>We'll send return instructions.</li><li>Send the item back unused and in its original packaging.</li><li>We inspect it and refund your original payment method.</li></ol>
            <h2>Return shipping</h2><p>{{ $settings['return_shipping'] }}</p>
            <h2>Exceptions</h2><p>Items that are used, damaged by the customer, or missing their original packaging may be refused or partially refunded. Gift cards and personalised items are not returnable.</p>
            <p><a href="{{ route('contact') }}">Start a return</a></p>
        </div>
    </div>
</x-layouts.app>
