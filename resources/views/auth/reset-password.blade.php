<x-layouts.app title="Choose a new password" :noindex="true">
    <x-auth-shell title="Choose a new password" art="reset">
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4" novalidate>@csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-field name="email" type="email" label="Email address" autocomplete="email" required :value="$email" />
            <x-field name="password" type="password" label="New password" autocomplete="new-password" required help="8+ characters with letters and numbers." />
            <x-field name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" required />
            <button class="btn btn-primary btn-lg w-full">Update password</button>
        </form>
    </x-auth-shell>
</x-layouts.app>
