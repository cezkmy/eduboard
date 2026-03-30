<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Central Admin
        User::factory()->create([
            'name' => 'SaaS Admin',
            'email' => 'admin@eduboard.com',
            'is_admin' => true,
        ]);

        // Regular User (Tenant Owner)
        User::factory()->create([
            'name' => 'School Owner',
            'email' => 'owner@example.com',
            'school_name' => 'Test School',
            'is_admin' => false,
        ]);
    }
}
