<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $host = request()->getHost();
        $centralDomains = config('tenancy.central_domains', []);
        
        // Register components with specific namespaces to avoid collisions
        if ($this->app->runningInConsole()) {
            \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/central/components'), 'central');
            \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/central/layouts'), 'central');
            \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/tenant_ui/components'), 'tenant');
            \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/tenant_ui/layouts'), 'tenant');
        }

        // If the current host is not in central domains, it's a tenant request
        if (!in_array($host, $centralDomains)) {
            // Register tenant layouts and components as default for tenant domains
            \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/tenant_ui/components'));
            \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/tenant_ui/layouts'));
        } else {
            // Register central layouts and components as default for central domains
            \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/central/components'));
            \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/central/layouts'));
        }
    }
}
