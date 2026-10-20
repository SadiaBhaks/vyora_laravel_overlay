<x-layouts.app title="Terms of Service" description="The terms that apply when you use VYORA and place orders.">
    <div class="mx-auto max-w-3xl px-4 pt-8 md:px-8"><x-breadcrumbs :items="[['label' => 'Terms of Service']]" />
        <h1 class="display-xl">Terms of Service</h1>
        <p class="mt-4 rounded-xl border border-dashed border-warn p-4 text-sm"><strong class="text-warn">Draft for review.</strong> These terms are a starting template for the demonstration store. Have them reviewed by qualified legal counsel before a real launch.</p>
        <div class="prose-vyora mt-6">
            <h2>1. Using VYORA</h2><p>By browsing or buying you agree to these terms. You must provide accurate information and keep your account credentials secure.</p>
            <h2>2. Products and pricing</h2><p>We aim to describe products accurately. Prices are shown in {{ config('vyora.currency') }} and are confirmed at checkout; if we find an error before shipping we may cancel the order and refund you in full.</p>
            <h2>3. Orders and payment</h2><p>An order is accepted when payment is confirmed. Payments are processed by Stripe. Orders may be cancelled if stock turns out to be unavailable.</p>
            <h2>4. Shipping and returns</h2><p>See our <a href="{{ route('shipping') }}">Shipping</a> and <a href="{{ route('returns') }}">Returns</a> pages, which form part of these terms.</p>
            <h2>5. Reviews</h2><p>Reviews must reflect your genuine experience of a product you bought. We moderate reviews and may remove content that is abusive, unlawful or misleading.</p>
            <h2>6. Liability</h2><p>To the extent permitted by law, VYORA isn't liable for indirect or consequential losses. Nothing here limits rights you have under consumer law.</p>
            <h2>7. Changes</h2><p>We may update these terms; continued use means you accept the updated version.</p>
            <h2>8. Contact</h2><p>Questions? <a href="{{ route('contact') }}">Contact us</a>.</p>
        </div>
    </div>
</x-layouts.app>
