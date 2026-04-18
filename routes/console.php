<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('eduboard:check-github')->everyThirtyMinutes();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tenancy:repair-localhost-domains {tenantId?}', function () {
    $tenantId = $this->argument('tenantId');

    $tenants = $tenantId
        ? \App\Models\Tenant::where('id', $tenantId)->get()
        : \App\Models\Tenant::all();

    if ($tenants->isEmpty()) {
        $this->error('No tenants found.');
        return 1;
    }

    $fixed = 0;

    foreach ($tenants as $tenant) {
        $rawSub = (string) $tenant->id;
        $safeSub = str_replace('_', '-', $rawSub);

        $tenant->domains()->firstOrCreate(['domain' => $rawSub]);
        $tenant->domains()->firstOrCreate(['domain' => $rawSub . '.localhost']);
        $tenant->domains()->firstOrCreate(['domain' => $safeSub]);
        $tenant->domains()->firstOrCreate(['domain' => $safeSub . '.localhost']);

        $fixed++;
        $this->info("Repaired: {$tenant->id}");
    }

    $this->comment("Done. Repaired {$fixed} tenant(s).");
    return 0;
})->purpose('Backfill *.localhost domain records for tenants');

Artisan::command('central:normalize-localhost-school-domains', function () {
    $users = \App\Models\User::whereNotNull('school_domain')
        ->where('school_domain', 'like', '%.localhost%')
        ->get();

    $updated = 0;

    foreach ($users as $user) {
        $old = (string) $user->school_domain;
        $new = str_replace('_', '-', $old);
        if ($new !== $old) {
            $user->school_domain = $new;
            $user->save();
            $updated++;
        }
    }

    $this->info("Updated {$updated} central user school_domain value(s).");
    $this->comment('Tip: run `php artisan tenancy:repair-localhost-domains` after this if needed.');
    return 0;
})->purpose('Normalize central user *.localhost school_domain underscores to dashes');
