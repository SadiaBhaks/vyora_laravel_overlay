<x-account-shell title="Addresses" active="addresses">
    <h1 class="display-lg">Addresses</h1>
    <div class="mt-8 grid gap-4 md:grid-cols-2">
        @foreach($addresses as $a)
            <div class="card p-5 text-sm"><p class="font-semibold">{{ $a->label }} @if($a->is_default)<span class="badge badge-soft ml-1">Default</span>@endif</p>
                <p class="mt-2 text-muted">{{ $a->full_name }}<br>{{ $a->line1 }}@if($a->line2), {{ $a->line2 }}@endif<br>{{ $a->city }}, {{ $a->state }} {{ $a->postal_code }}<br>{{ $a->country }}@if($a->phone)<br>{{ $a->phone }}@endif</p>
                <div class="mt-4 flex gap-4"><a href="{{ route('account.addresses.edit', $a) }}" class="font-semibold underline underline-offset-4">Edit</a>
                    <form method="POST" action="{{ route('account.addresses.destroy', $a) }}" onsubmit="return confirm('Remove this address?')">@csrf @method('DELETE')<button class="text-danger underline underline-offset-4">Remove</button></form></div></div>
        @endforeach
    </div>
    @php $e = $editing; @endphp
    <form method="POST" action="{{ $e ? route('account.addresses.update', $e) : route('account.addresses.store') }}" class="card mt-8 max-w-2xl space-y-4 p-6 sm:p-8" novalidate>@csrf @if($e) @method('PUT') @endif
        <h2 class="font-display text-xl font-bold">{{ $e ? 'Edit address' : 'Add an address' }}</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="label" label="Label" :value="$e?->label ?? 'Home'" /><x-field name="full_name" label="Full name" :value="$e?->full_name ?? auth()->user()->name" required autocomplete="name" />
            <x-field name="line1" label="Address" :value="$e?->line1" required class="sm:col-span-2" autocomplete="address-line1" /><x-field name="line2" label="Apartment, suite (optional)" :value="$e?->line2" class="sm:col-span-2" autocomplete="address-line2" />
            <x-field name="city" label="City" :value="$e?->city" required autocomplete="address-level2" /><x-field name="state" label="State / region" :value="$e?->state" autocomplete="address-level1" />
            <x-field name="postal_code" label="Postal code" :value="$e?->postal_code" required autocomplete="postal-code" /><x-field name="country" label="Country (2-letter code)" :value="$e?->country ?? 'US'" required maxlength="2" autocomplete="country" />
            <x-field name="phone" type="tel" label="Phone (optional)" :value="$e?->phone" autocomplete="tel" />
        </div>
        <label class="flex items-center gap-2.5 text-sm"><input type="checkbox" name="is_default" value="1" class="size-4 rounded accent-[var(--ink)]" @checked(old('is_default', $e?->is_default))> Make this my default address</label>
        <div class="flex gap-3"><button class="btn btn-primary">{{ $e ? 'Save address' : 'Add address' }}</button>@if($e)<a href="{{ route('account.addresses') }}" class="btn btn-ghost">Cancel</a>@endif</div>
    </form>
</x-account-shell>
