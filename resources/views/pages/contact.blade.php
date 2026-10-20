<x-layouts.app title="Contact" description="Get in touch with the VYORA team about orders, products or anything else.">
    <div class="mx-auto max-w-[88rem] px-4 pt-8 md:px-8">
        <x-breadcrumbs :items="[['label' => 'Contact']]" />
        <div class="grid gap-12 lg:grid-cols-[1fr_1.1fr]">
            <div>
                <p class="eyebrow mb-2">Contact</p><h1 class="display-xl text-balance">Talk to a human.</h1>
                <p class="mt-5 max-w-md text-lg text-muted">Questions about an order, a product or a return? Send us a note and we'll reply within two working days.</p>
                <dl class="mt-10 space-y-6 text-sm">
                    <div class="flex gap-4"><x-icon name="mail" class="mt-0.5 size-5" /><div><dt class="font-semibold">Email</dt><dd class="text-muted"><a class="underline underline-offset-4" href="mailto:{{ config('vyora.contact_email') }}">{{ config('vyora.contact_email') }}</a></dd></div></div>
                    <div class="flex gap-4"><x-icon name="message" class="mt-0.5 size-5" /><div><dt class="font-semibold">Support hours</dt><dd class="text-muted">{{ app(\App\Services\SettingsService::class)->get('support_hours') }}</dd></div></div>
                    <div class="flex gap-4"><x-icon name="info" class="mt-0.5 size-5" /><div><dt class="font-semibold">Quick answers</dt><dd class="text-muted">Many questions are covered in the <a class="underline underline-offset-4" href="{{ route('faq') }}">FAQ</a>, <a class="underline underline-offset-4" href="{{ route('shipping') }}">shipping</a> and <a class="underline underline-offset-4" href="{{ route('returns') }}">returns</a> pages.</dd></div></div>
                </dl>
            </div>
            <form method="POST" action="{{ route('contact.send') }}" class="card space-y-5 p-6 sm:p-8" novalidate>
                @csrf
                <div class="hidden" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                <div class="grid gap-5 sm:grid-cols-2"><x-field name="name" label="Your name" autocomplete="name" required :value="auth()->user()?->name" /><x-field name="email" type="email" label="Email" autocomplete="email" required :value="auth()->user()?->email" /></div>
                <x-field name="subject" label="Subject" required />
                <x-field name="message" type="textarea" label="Message" rows="6" required help="Include your order number if it's about an order." />
                <button class="btn btn-primary btn-lg w-full">Send message</button>
            </form>
        </div>
    </div>
</x-layouts.app>
