<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
        ]);
        
        // Daftarkan middleware alias untuk sistem langganan
        $middleware->alias([
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'feature' => \App\Http\Middleware\CheckFeatureAccess::class,
            'track' => \App\Http\Middleware\TrackUsage::class,
            'admin' => \App\Http\Middleware\AdminAuth::class,
            'has_subscription' => \App\Http\Middleware\EnsureHasActiveSubscription::class,
            'suspended' => \App\Http\Middleware\CheckSuspendedAccount::class,
            'global_feature' => \App\Http\Middleware\CheckFeatureFlag::class,
            'onboarding' => \App\Http\Middleware\CheckOnboardingComplete::class,
            'quota' => \App\Http\Middleware\CheckPlanLimits::class,
        ]);

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
