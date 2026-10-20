<x-layouts.app title="Privacy Policy" description="How VYORA collects, uses and protects your personal data.">
    <div class="mx-auto max-w-3xl px-4 pt-8 md:px-8"><x-breadcrumbs :items="[['label' => 'Privacy Policy']]" />
        <h1 class="display-xl">Privacy Policy</h1>
        <p class="mt-4 rounded-xl border border-dashed border-warn p-4 text-sm"><strong class="text-warn">Draft for review.</strong> This policy is a starting template for the demonstration store. Have it reviewed by qualified legal counsel and adapted to your business and jurisdictions before launching.</p>
        <div class="prose-vyora mt-6">
            <h2>What we collect</h2><ul><li><strong>Account data:</strong> name, email, hashed password, saved addresses and shopping preferences.</li><li><strong>Order data:</strong> items, amounts, delivery details and payment status.</li><li><strong>Activity data:</strong> recently viewed products, wishlist, cart and search terms, used to personalise the store.</li><li><strong>Messages:</strong> anything you send via the contact form or newsletter signup.</li></ul>
            <h2>Payments</h2><p>Card payments are handled by Stripe. We never receive or store full card numbers; we store only the payment status and Stripe's reference for each order.</p>
            <h2>How we use data</h2><p>To run your account and fulfil orders, to provide recommendations, to answer your messages, to prevent fraud and abuse, and to meet legal obligations. Marketing emails are sent only if you opt in, and every one can be unsubscribed from.</p>
            <h2>Cookies and storage</h2><p>We use a session cookie to keep you signed in and to hold your cart, plus a CSRF cookie for security. Your theme choice and recent searches are stored in your browser's local storage and never sent to us.</p>
            <h2>Sharing</h2><p>We share data only with service providers needed to operate the store (for example Stripe and our email and hosting providers) and when required by law. We don't sell personal data.</p>
            <h2>Your rights</h2><p>Depending on where you live, you may be able to access, correct, export or delete your data. Contact us at <a href="mailto:{{ config('vyora.contact_email') }}">{{ config('vyora.contact_email') }}</a>.</p>
            <h2>Retention</h2><p>We keep order records as long as the law requires and account data until you ask us to delete it.</p>
        </div>
    </div>
</x-layouts.app>
