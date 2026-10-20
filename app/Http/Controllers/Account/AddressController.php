<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return view('account.addresses', ['addresses' => $request->user()->addresses, 'editing' => null]);
    }

    public function edit(Request $request, Address $address)
    {
        $this->authorize('manage', $address);

        return view('account.addresses', ['addresses' => $request->user()->addresses, 'editing' => $address]);
    }

    public function store(AddressRequest $request)
    {
        $user = $request->user();
        $makeDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;
        if ($makeDefault) {
            $user->addresses()->update(['is_default' => false]);
        }
        $user->addresses()->create($request->validated() + ['is_default' => $makeDefault]);

        return redirect()->route('account.addresses')->with('toast', 'Address saved.');
    }

    public function update(AddressRequest $request, Address $address)
    {
        $this->authorize('manage', $address);
        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
        }
        $address->update($request->validated() + ['is_default' => $request->boolean('is_default')]);

        return redirect()->route('account.addresses')->with('toast', 'Address updated.');
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorize('manage', $address);
        $wasDefault = $address->is_default;
        $address->delete();
        if ($wasDefault) {
            $request->user()->addresses()->first()?->update(['is_default' => true]);
        }

        return redirect()->route('account.addresses')->with('toast', 'Address removed.');
    }
}
