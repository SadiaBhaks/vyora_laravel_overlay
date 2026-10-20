<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureStaff;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'staff' => EnsureStaff::class,
        ]);
        // Stripe signs its webhooks; the signature (not a CSRF token) authenticates them.
        $middleware->validateCsrfTokens(except: ['stripe/webhook']);
        if (env('TRUST_PROXIES')) {
            $middleware->trustProxies(at: env('TRUST_PROXIES') === '*' ? '*' : explode(',', env('TRUST_PROXIES')));
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
