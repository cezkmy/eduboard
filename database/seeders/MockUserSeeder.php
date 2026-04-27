<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MockUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Safety check: Skip if running in tenant context
        if (function_exists('tenant') && tenant()) {
            return;
        }

        $users = [
            ['name' => 'Ashley Thomas', 'email' => 'ashley@example.com'],
            ['name' => 'Chris Wilson', 'email' => 'chris@example.com'],
            ['name' => 'Emily Davis', 'email' => 'emily@example.com'],
            ['name' => 'Michael Brown', 'email' => 'michael@example.com'],
            ['name' => 'John Doe', 'email' => 'john@example.com'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                    'plan' => 'Basic',
                    'has_selected_template' => false,
                ]
            );
        }
    }
}
