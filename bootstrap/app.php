<?php

use App\Http\Middleware\EnsureAirlineAdmin;
use App\Http\Middleware\EnsureCustomer;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [\App\Http\Middleware\SetLandingLocale::class]);
        $middleware->alias([
            'role.customer' => EnsureCustomer::class,
            'role.airline-admin' => EnsureAirlineAdmin::class,
            'role.super-admin' => EnsureSuperAdmin::class,
        ]);
        $middleware->validateCsrfTokens(except: ['stripe/webhook']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
