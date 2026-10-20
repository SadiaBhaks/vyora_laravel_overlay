<x-layouts.app title="Reset password" :noindex="true">
    <x-auth-shell title="Forgot your password?" subtitle="Enter your email and we'll send a reset link." art="forgot">
        @if(session('status'))<p class="mb-5 rounded-xl bg-surface-2 p-3 text-sm" role="status">{{ session('status') }}</p>@endif
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4" novalidate>@csrf
            <x-field name="email" type="email" label="Email address" autocomplete="email" required autofocus />
            <button class="btn btn-primary btn-lg w-full">Email me a reset link</button>
        </form>
        <p class="mt-6 text-center text-sm"><a href="{{ route('login') }}" class="font-semibold underline underline-offset-4">Back to sign in</a></p>
    </x-auth-shell>
</x-layouts.app>
