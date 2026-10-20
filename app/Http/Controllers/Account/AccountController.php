<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Models\Category;
use App\Models\UserPreference;
use App\Services\RecentlyViewedService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index(Request $request, RecommendationService $recs, RecentlyViewedService $recent)
    {
        $user = $request->user();
        $orders = $user->orders()->with('items')->limit(3)->get();
        $stats = [
            'orders' => $user->orders()->count(),
            'spent_cents' => (int) $user->orders()->where('payment_status', 'paid')->where('status', '!=', 'cancelled')->sum('total_cents'),
            'wishlist' => $user->wishlistItems()->count(),
            'open' => $user->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count(),
        ];

        return view('account.index', [
            'orders' => $orders,
            'stats' => $stats,
            'wishlist' => $user->wishlistProducts()->active()->with(['images', 'brand', 'variants', 'promotions'])->limit(4)->get(),
            'recent' => $recent->products(4),
            'recommended' => $recs->forVisitor($user, 4)['products'],
            'addresses' => $user->addresses()->limit(2)->get(),
        ]);
    }

    public function orders(Request $request)
    {
        return view('account.orders', ['orders' => $request->user()->orders()->with('items')->paginate(8)]);
    }

    public function profile() { return view('account.profile'); }

    public function updateProfile(ProfileRequest $request)
    {
        $user = $request->user();
        $user->fill($request->validated());
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();
        if (! $user->hasVerifiedEmail() && config('vyora.require_email_verification')) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('toast', 'Profile updated.');
    }

    public function settings(Request $request)
    {
        return view('account.settings', ['categories' => Category::active()->get(), 'prefs' => $request->user()->preferences]);
    }

    public function updatePreferences(Request $request)
    {
        $data = $request->validate([
            'interests' => ['nullable', 'array', 'max:8'],
            'interests.*' => ['string', 'exists:categories,slug'],
            'style' => ['nullable', 'in:'.implode(',', array_keys(\App\Services\Match\RuleBasedMatcher::STYLES))],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ]);
        $user = $request->user();
        UserPreference::updateOrCreate(['user_id' => $user->id], ['interests' => $data['interests'] ?? [], 'style' => $data['style'] ?? null]);
        $user->update(['marketing_opt_in' => $request->boolean('marketing_opt_in')]);

        return back()->with('toast', 'Preferences saved.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        $request->user()->update(['password' => $request->password]);

        return back()->with('toast', 'Password changed.');
    }

    public function wishlist(Request $request)
    {
        return view('account.wishlist', [
            'items' => $request->user()->wishlistItems()->with(['product' => fn ($q) => $q->with(['images', 'brand', 'variants', 'promotions'])])->latest()->get()->filter(fn ($i) => $i->product),
        ]);
    }
}
