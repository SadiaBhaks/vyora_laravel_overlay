<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\ArtController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MiscController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// ---- Storefront --------------------------------------------------------------------------------
Route::get('/', HomeController::class)->name('home');
Route::get('/art', ArtController::class)->name('art');

Route::get('/shop', [CatalogController::class, 'shop'])->name('shop');
Route::get('/categories', [CatalogController::class, 'categories'])->name('categories');
Route::get('/categories/{slug}', [CatalogController::class, 'category'])->name('categories.show');
Route::get('/new-arrivals', [CatalogController::class, 'newArrivals'])->name('new-arrivals');
Route::get('/search', [CatalogController::class, 'search'])->name('search');
Route::get('/deals', [CatalogController::class, 'deals'])->name('deals');
Route::get('/trending', [CatalogController::class, 'trending'])->name('trending');

Route::get('/collections', [CollectionController::class, 'index'])->name('collections');
Route::get('/collections/{collection:slug}', [CollectionController::class, 'show'])->name('collections.show');

Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.show');
Route::post('/product/{product:slug}/reviews', [ProductController::class, 'storeReview'])->middleware(['auth', 'throttle:forms'])->name('product.reviews.store');

Route::view('/match', 'pages.match')->name('match');
Route::view('/cart', 'pages.cart')->name('cart');
Route::post('/cart/buy-now', [CartController::class, 'buyNow'])->middleware('throttle:forms')->name('cart.buy-now');
Route::post('/wishlist/{product:slug}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// ---- Checkout & orders -------------------------------------------------------------------------
Route::middleware('throttle:checkout')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
});
Route::get('/checkout/return/{order:number}', [CheckoutController::class, 'returned'])->name('checkout.return');
Route::get('/checkout/cancelled/{order:number}', [CheckoutController::class, 'cancelled'])->name('checkout.cancelled');
Route::get('/checkout/demo/{order:number}', [CheckoutController::class, 'demo'])->name('checkout.demo');
Route::post('/checkout/demo/{order:number}', [CheckoutController::class, 'confirmDemo'])->name('checkout.demo.confirm');
Route::get('/order-confirmation/{order:number}', [OrderController::class, 'show'])->name('orders.confirmation');

Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

// ---- Auth --------------------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:forms');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/forgot-password', [PasswordController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendLink'])->middleware('throttle:forms')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'reset'])->middleware('throttle:forms')->name('password.update');
    Route::get('/auth/google', [SocialAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'callback'])->name('auth.google.callback');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
});

// ---- Customer account --------------------------------------------------------------------------
Route::middleware(['auth', config('vyora.require_email_verification') ? 'verified' : 'auth'])->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{order:number}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order:number}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [AddressController::class, 'edit'])->name('addresses.edit');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::get('/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
    Route::get('/settings', [AccountController::class, 'settings'])->name('settings');
    Route::put('/settings/preferences', [AccountController::class, 'updatePreferences'])->name('settings.preferences');
    Route::put('/settings/password', [AccountController::class, 'updatePassword'])->name('settings.password');
});

// ---- Public pages ------------------------------------------------------------------------------
Route::post('/newsletter', [MiscController::class, 'newsletter'])->middleware('throttle:forms')->name('newsletter');
Route::get('/contact', [MiscController::class, 'contact'])->name('contact');
Route::post('/contact', [MiscController::class, 'sendContact'])->middleware('throttle:forms')->name('contact.send');
foreach (['about', 'faq', 'shipping', 'returns', 'privacy', 'terms'] as $page) {
    Route::get('/'.$page, [MiscController::class, 'page'])->defaults('page', $page)->name($page);
}
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// ---- Admin -------------------------------------------------------------------------------------
Route::middleware(['auth', 'staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::resource('products', Admin\ProductController::class)->except('show');
    Route::delete('products/{product}/images/{image}', [Admin\ProductController::class, 'destroyImage'])->name('products.images.destroy');
    Route::resource('categories', Admin\CategoryController::class)->except('show');
    Route::resource('brands', Admin\BrandController::class)->except('show');
    Route::resource('collections', Admin\CollectionController::class)->except('show');

    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order:number}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order:number}', [Admin\OrderController::class, 'update'])->name('orders.update');
    Route::post('orders/{order:number}/cancel', [Admin\OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('customers', [Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('inventory', [Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/{variant}', [Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');

    Route::resource('coupons', Admin\CouponController::class)->except('show');
    Route::resource('promotions', Admin\PromotionController::class)->except('show');

    Route::get('reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::put('reviews/{review}', [Admin\ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('newsletter', [Admin\NewsletterController::class, 'index'])->name('newsletter.index');
    Route::delete('newsletter/{subscriber}', [Admin\NewsletterController::class, 'destroy'])->name('newsletter.destroy');

    // Sensitive store configuration is admin-only (staff can't change policies or promote anyone).
    Route::middleware('admin')->group(function () {
        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::put('customers/{user}/role', [Admin\CustomerController::class, 'role'])->name('customers.role');
    });
});
