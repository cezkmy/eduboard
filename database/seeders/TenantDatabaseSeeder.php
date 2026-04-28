<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Tenant\CategorySeeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            BookModelSeeder::class,
            AuthorModelSeeder::class,
            AnnouncementSeeder::class,
        ]);
    }
}
