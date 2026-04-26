<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Safety check: Skip if running in tenant context
        if (function_exists('tenancy') && tenancy()->initialized) {
            return;
        }

        $admins = [
            ['name' => 'Jeru', 'email' => 'jeru@gmail.com'],
            ['name' => 'Julius', 'email' => 'julius@gmail.com'],
            ['name' => 'Stephanie', 'email' => 'stephanie@gmail.com'],
            ['name' => 'Charme', 'email' => 'charme@gmail.com'],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'admin',
                    'is_admin' => true,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
