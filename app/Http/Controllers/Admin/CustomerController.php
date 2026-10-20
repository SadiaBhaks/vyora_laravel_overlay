<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->withCount('orders')->latest('id');
        if ($q = trim((string) $request->query('q'))) {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($q)).'%';
            $query->where(fn ($w) => $w->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$like])->orWhereRaw("LOWER(email) LIKE ? ESCAPE '!'", [$like]));
        }

        return view('admin.customers.index', ['customers' => $query->paginate(20)->withQueryString(), 'q' => $q]);
    }

    /** Admin-only. Role is guarded from mass assignment, so it is set explicitly here and nowhere else. */
    public function role(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', 'in:customer,staff,admin']]);
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }
        $user->forceFill(['role' => $data['role']])->save();

        return back()->with('toast', "{$user->name} is now {$data['role']}.");
    }
}
