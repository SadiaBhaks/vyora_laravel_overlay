<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\GuestMerger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function __construct(private GuestMerger $merger) {}

    public function showRegister()
    {
        return view('auth.register', ['categories' => \App\Models\Category::active()->get()]);
    }

    public function register(RegisterRequest $request)
    {
        $guestCart = session('vyora.cart', []);
        $guestWishlist = session('vyora.wishlist', []);

        $user = DB::transaction(function () use ($request) {
            // Role is never taken from input: new accounts are always customers.
            $user = User::create($request->only(['name', 'email', 'password']));
            UserPreference::create(['user_id' => $user->id, 'interests' => $request->input('interests', [])]);

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        $this->merger->merge($user, $guestCart, $guestWishlist);

        return redirect()->route('account.index')->with('toast', 'Welcome to VYORA, '.explode(' ', $user->name)[0].'. Your picks are ready.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $guestCart = session('vyora.cart', []);
        $guestWishlist = session('vyora.wishlist', []);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $this->merger->merge($request->user(), $guestCart, $guestWishlist);

        return redirect()->intended(route('account.index'))->with('toast', 'Welcome back.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('toast', 'You have been signed out.');
    }
}
