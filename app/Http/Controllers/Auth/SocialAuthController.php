<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\GuestMerger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/** Optional Google sign-in. Needs `composer require laravel/socialite` and GOOGLE_* env values. */
class SocialAuthController extends Controller
{
    public static function enabled(): bool
    {
        return class_exists(\Laravel\Socialite\Facades\Socialite::class)
            && filled(config('vyora.google.client_id')) && filled(config('vyora.google.client_secret'));
    }

    public function redirect()
    {
        abort_unless(self::enabled(), 404);
        config(['services.google' => config('vyora.google')]);

        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    public function callback(GuestMerger $merger)
    {
        abort_unless(self::enabled(), 404);
        config(['services.google' => config('vyora.google')]);
        $guestCart = session('vyora.cart', []);
        $guestWishlist = session('vyora.wishlist', []);

        $google = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
        if (! ($google->user['email_verified'] ?? false)) {
            return redirect()->route('login')->withErrors(['email' => 'Your Google email address is not verified.']);
        }

        $user = User::where('email', $google->getEmail())->first();
        if (! $user) {
            $user = User::create(['name' => $google->getName() ?: 'VYORA customer', 'email' => $google->getEmail(), 'password' => Str::random(40)]);
            $user->forceFill(['email_verified_at' => now()])->save();
            UserPreference::create(['user_id' => $user->id, 'interests' => []]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();
        $merger->merge($user, $guestCart, $guestWishlist);

        return redirect()->intended(route('account.index'));
    }
}
