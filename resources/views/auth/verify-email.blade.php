<x-layouts.app title="Verify your email" :noindex="true">
    <x-auth-shell title="Check your inbox" subtitle="We sent a verification link to your email address. Click it to confirm your account." art="verify">
        @if(session('status'))<p class="mb-5 rounded-xl bg-surface-2 p-3 text-sm" role="status">{{ session('status') }}</p>@endif
        <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-primary btn-lg w-full">Resend verification email</button></form>
        <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">@csrf<button class="text-sm text-muted underline underline-offset-4">Sign out</button></form>
    </x-auth-shell>
</x-layouts.app>
