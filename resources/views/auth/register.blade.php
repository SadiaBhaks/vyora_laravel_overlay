<x-layouts.app title="Create your account" :noindex="true" description="Create your VYORA account.">
    <x-auth-shell title="Your world. Your picks." subtitle="Create your VYORA account to discover products tailored to your style, save your favorites, and keep every order within reach." art="register">
        <form method="POST" action="{{ route('register') }}" class="space-y-4" novalidate>
            @csrf
            <x-field name="name" label="Full name" autocomplete="name" required autofocus />
            <x-field name="email" type="email" label="Email address" autocomplete="email" required />
            <x-field name="password" type="password" label="Password" autocomplete="new-password" required help="8+ characters with letters and numbers." />
            <x-field name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" required />
            <fieldset><legend class="label">Shopping interests <span class="font-normal text-muted">(optional)</span></legend>
                <div class="flex flex-wrap gap-2">@foreach($categories as $c)<label class="cursor-pointer"><input type="checkbox" name="interests[]" value="{{ $c->slug }}" class="peer sr-only" @checked(in_array($c->slug, old('interests', [])))><span class="chip peer-checked:bg-ink peer-checked:text-bg peer-focus-visible:outline peer-focus-visible:outline-2">{{ $c->name }}</span></label>@endforeach</div></fieldset>
            <button class="btn btn-primary btn-lg w-full">Create Account</button>
            @if(\App\Http\Controllers\Auth\SocialAuthController::enabled())<a href="{{ route('auth.google') }}" class="btn btn-ghost btn-lg w-full">Continue with Google</a>@endif
        </form>
        <p class="mt-6 text-center text-sm text-muted">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-ink underline underline-offset-4">Sign in</a></p>
    </x-auth-shell>
</x-layouts.app>
