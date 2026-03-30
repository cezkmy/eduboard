<?php

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
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if (function_exists('tenant') && tenant()) {
                return route('tenant.login');
            }
            return route('login');
        });

        $middleware->alias([
            'student' => \App\Http\Middleware\StudentMiddleware::class,
        ]);
        $middleware->priority([
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain::class,
            \Stancl\Tenancy\Middleware\InitializeTenancyByPath::class,
            \Stancl\Tenancy\Middleware\InitializeTenancyByRequestData::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
