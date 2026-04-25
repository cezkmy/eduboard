<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('eduboard:seed-tenant-users {tenantId? : Optional tenant id. If omitted, seeds all active tenants.}')]
#[Description('Seed tenant databases with mock admin, teacher, student, and pending users.')]
class SeedTenantMockUsers extends Command
{
    public function handle(): int
    {
        $tenantId = $this->argument('tenantId');
        $tenants = $tenantId
            ? Tenant::where('id', $tenantId)->get()
            : Tenant::where('status', 'Active')->get();

        if ($tenants->isEmpty()) {
            $this->warn('No matching tenants found.');
            return self::FAILURE;
        }

        tenancy()->runForMultiple($tenants, function ($tenant): void {
            $prefix = strtolower((string) $tenant->id);
            $school = $tenant->school_name ?? 'Demo School';

            $users = [
                ['name' => 'Second Admin', 'email' => "admin2@{$prefix}.local", 'role' => 'admin', 'status' => 'active'],
                ['name' => 'Prof. Reyes', 'email' => "reyes@{$prefix}.local", 'role' => 'teacher', 'status' => 'active'],
                ['name' => 'Prof. Garcia', 'email' => "garcia@{$prefix}.local", 'role' => 'teacher', 'status' => 'active'],
                ['name' => 'Prof. Santos', 'email' => "santos@{$prefix}.local", 'role' => 'teacher', 'status' => 'active'],
                ['name' => 'Juan Dela Cruz', 'email' => "juan@{$prefix}.local", 'role' => 'student', 'status' => 'active'],
                ['name' => 'Maria Clara', 'email' => "maria@{$prefix}.local", 'role' => 'student', 'status' => 'active'],
                ['name' => 'Kevin Park', 'email' => "kevin@{$prefix}.local", 'role' => 'student', 'status' => 'pending'],
                ['name' => 'Ashley Tan', 'email' => "ashley@{$prefix}.local", 'role' => 'student', 'status' => 'pending'],
            ];

            foreach ($users as $user) {
                User::updateOrCreate(
                    ['email' => $user['email']],
                    [
                        'name' => $user['name'],
                        'password' => Hash::make('password123'),
                        'role' => $user['role'],
                        'status' => $user['status'],
                        'school_name' => $school,
                        'email_verified_at' => now(),
                    ]
                );
            }

            $this->info("Seeded users for tenant [{$tenant->id}]");
        });

        $this->info('Tenant mock user seeding completed.');
        return self::SUCCESS;
    }
}
