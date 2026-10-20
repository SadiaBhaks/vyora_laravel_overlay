<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Match\ProductMatcher;
use App\Services\Match\RuleBasedMatcher;
use App\Services\SettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        // Replace this binding to plug in a real AI-backed matcher later.
        $this->app->bind(ProductMatcher::class, RuleBasedMatcher::class);
    }

    public function boot(): void
    {
        // Surface N+1 queries during development without breaking pages for visitors.
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::handleLazyLoadingViolationUsing(fn ($model, $relation) => logger()->warning('Lazy loading violation: '.$model::class.'::'.$relation));
        Paginator::defaultView('pagination.vyora');

        Gate::define('admin', fn (User $u) => $u->isAdmin());
        Gate::define('staff', fn (User $u) => $u->isStaff());

        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(5)->by(mb_strtolower((string) $r->input('email')).'|'.$r->ip()));
        RateLimiter::for('forms', fn (Request $r) => Limit::perMinute(6)->by($r->ip()));
        RateLimiter::for('checkout', fn (Request $r) => Limit::perMinute(10)->by(($r->user()?->id ?? 'g').'|'.$r->ip()));
    }
}
