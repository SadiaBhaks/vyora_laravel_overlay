<x-layouts.app title="Sign in" :noindex="true" description="Sign in to your VYORA account.">
    <x-auth-shell title="Welcome back" subtitle="Sign in to see your picks, wishlist and orders.">
        @if(session('status'))<p class="mb-5 rounded-xl bg-surface-2 p-3 text-sm" role="status">{{ session('status') }}</p>@endif
        <form method="POST" action="{{ route('login') }}" class="space-y-4" novalidate>
            @csrf
            <x-field name="email" type="email" label="Email address" autocomplete="email" required autofocus />
            <x-field name="password" type="password" label="Password" autocomplete="current-password" required />
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2"><input type="checkbox" name="remember" class="size-4 rounded accent-[var(--ink)]"> Remember me</label>
                <a href="{{ route('password.request') }}" class="font-medium underline underline-offset-4">Forgot password?</a>
            </div>
            <button class="btn btn-primary btn-lg w-full">Sign in</button>
            @if(\App\Http\Controllers\Auth\SocialAuthController::enabled())<a href="{{ route('auth.google') }}" class="btn btn-ghost btn-lg w-full">Continue with Google</a>@endif
        </form>
        <p class="mt-6 text-center text-sm text-muted">New to VYORA? <a href="{{ route('register') }}" class="font-semibold text-ink underline underline-offset-4">Create an account</a></p>
    </x-auth-shell>
</x-layouts.app>
