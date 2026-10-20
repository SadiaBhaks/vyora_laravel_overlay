<x-account-shell title="Settings" active="settings">
    <h1 class="display-lg">Settings</h1>
    <form method="POST" action="{{ route('account.settings.preferences') }}" class="card mt-8 max-w-2xl space-y-6 p-6 sm:p-8">@csrf @method('PUT')
        <h2 class="font-display text-xl font-bold">Shopping preferences</h2>
        <fieldset><legend class="label">Interests — these shape your recommendations</legend>
            <div class="flex flex-wrap gap-2">@foreach($categories as $c)<label class="cursor-pointer"><input type="checkbox" name="interests[]" value="{{ $c->slug }}" class="peer sr-only" @checked(in_array($c->slug, old('interests', $prefs?->interests ?? [])))><span class="chip peer-checked:bg-ink peer-checked:text-bg peer-focus-visible:outline peer-focus-visible:outline-2">{{ $c->name }}</span></label>@endforeach</div></fieldset>
        <x-field name="style" type="select" label="Preferred style"><option value="">No preference</option>@foreach(\App\Services\Match\RuleBasedMatcher::STYLES as $k => $l)<option value="{{ $k }}" @selected(old('style', $prefs?->style) === $k)>{{ $l }}</option>@endforeach</x-field>
        <label class="flex items-center gap-2.5 text-sm"><input type="checkbox" name="marketing_opt_in" value="1" class="size-4 rounded accent-[var(--ink)]" @checked(old('marketing_opt_in', auth()->user()->marketing_opt_in))> Email me about new arrivals and offers</label>
        <button class="btn btn-primary">Save preferences</button>
    </form>
    <form method="POST" action="{{ route('account.settings.password') }}" class="card mt-6 max-w-2xl space-y-4 p-6 sm:p-8" novalidate>@csrf @method('PUT')
        <h2 class="font-display text-xl font-bold">Change password</h2>
        <x-field name="current_password" type="password" label="Current password" required autocomplete="current-password" />
        <x-field name="password" type="password" label="New password" required autocomplete="new-password" help="8+ characters with letters and numbers." />
        <x-field name="password_confirmation" type="password" label="Confirm new password" required autocomplete="new-password" />
        <button class="btn btn-primary">Update password</button>
    </form>
</x-account-shell>
