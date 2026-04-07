<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;

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
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('eduboard:check-github')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TenantCouldNotBeIdentifiedOnDomainException $e, \Illuminate\Http\Request $request) {
            // Avoid 500s when visiting a non-existent tenant domain.
            // This commonly happens in local dev when a tenant wasn't created yet.
            return response()->view('errors.tenant-not-found', [
                'domain' => $e->domain ?? $request->getHost(),
            ], 404);
        });
    })->create();
