<x-account-shell title="Profile" active="profile">
    <h1 class="display-lg">Profile</h1>
    <form method="POST" action="{{ route('account.profile.update') }}" class="card mt-8 max-w-xl space-y-5 p-6 sm:p-8" novalidate>@csrf @method('PUT')
        <x-field name="name" label="Full name" :value="auth()->user()->name" required autocomplete="name" />
        <x-field name="email" type="email" label="Email address" :value="auth()->user()->email" required autocomplete="email" help="Changing your email may require re-verification." />
        <x-field name="phone" type="tel" label="Phone (optional)" :value="auth()->user()->phone" autocomplete="tel" />
        <button class="btn btn-primary">Save changes</button>
    </form>
</x-account-shell>
