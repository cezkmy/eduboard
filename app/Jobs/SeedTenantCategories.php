<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SeedTenantCategories implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $tenant;

    public function __construct($tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle()
    {
        $this->tenant->run(function () {
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'Database\Seeders\Tenant\CategorySeeder',
                '--force' => true,
            ]);
        });
    }
}