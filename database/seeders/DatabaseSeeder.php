<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Safety check: If we are in a tenant context, only run tenant-specific seeds
        if (function_exists('tenant') && tenant()) {
            $this->call([
                TenantDatabaseSeeder::class,
            ]);
            return;
        }

        $this->call([
            AdminUserSeeder::class,
            PlanSeeder::class,
            TemplateSeeder::class,
            MockUserSeeder::class, // Run users before tenants
            MockTenantAndBillingSeeder::class,
        ]);
    }
}
