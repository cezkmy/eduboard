<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use App\Models\BillingHistory;
use App\Models\Domain;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MockTenantAndBillingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mockUserMapping = [
            'ashley@example.com' => 'buksu',
            'chris@example.com' => 'cmu',
            'emily@example.com' => 'ustp',
            'michael@example.com' => 'bnhs',
            'john@example.com' => 'sti',
        ];
        
        $mockEmails = array_keys($mockUserMapping);

        // 1. DEEP CLEANUP: Find and delete all previous mock data
        $mockUsers = User::whereIn('email', $mockEmails)->get();
        $userIds = $mockUsers->pluck('id')->toArray();

        // Find all tenants owned by these users or matching the naming patterns
        $tenantsToDelete = Tenant::whereIn('owner_id', $userIds)
            ->orWhere('id', 'like', '%_school')
            ->orWhere('id', 'like', '%_eduboard')
            ->get();

        foreach ($tenantsToDelete as $tenant) {
            // Manually drop database to be 100% sure it's gone
            $dbName = $tenant->id . config('tenancy.database.suffix', '_db');
            try {
                // Using raw SQL to avoid Eloquent events/Tenancy interceptions
                DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `$dbName` ");
            } catch (\Exception $e) {
                // Ignore if DB doesn't exist or permission denied
            }
            
            // Delete billing histories first due to foreign key (if any)
            BillingHistory::where('tenant_id', $tenant->id)->delete();
            
            // Delete domains
            Domain::where('tenant_id', $tenant->id)->delete();
            
            // Delete tenant record using DB table to bypass Tenancy events
            DB::table('tenants')->where('id', $tenant->id)->delete();
        }

        // 2. SEED FRESH DATA
        $plans = [
            'Pro' => 199,
            'Ultimate' => 299,
            'Basic' => 0,
        ];

        // Determine base host for domains
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'eduboard.com';
        if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
            $baseHost = 'localhost';
        } elseif (str_starts_with($host, 'eduboard.')) {
            $baseHost = substr($host, 9);
        } else {
            $baseHost = $host;
        }
        $port = parse_url(config('app.url'), PHP_URL_PORT);

        foreach ($mockUsers as $index => $user) {
            // Distribute plans explicitly
            $planMapping = [
                'ashley@example.com' => 'Pro',
                'chris@example.com' => 'Ultimate',
                'emily@example.com' => 'Pro',
                'michael@example.com' => 'Basic',
                'john@example.com' => 'Basic',
            ];
            $selectedPlan = $planMapping[$user->email] ?? 'Pro';
            
            // Create EXACTLY ONE tenant for each mock user with the new pattern
            $prefix = $mockUserMapping[$user->email] ?? Str::slug($user->name, '_');
            $subdomain = $prefix . '_eduboard';
            $safeSubdomain = $baseHost === 'localhost' ? str_replace('_', '-', $subdomain) : $subdomain;
            $domainName = $safeSubdomain . '.' . $baseHost;

            // Use Tenant::create so it handles DB creation
            $tenant = Tenant::create([
                'id' => $subdomain,
                'owner_id' => $user->id,
                'school_name' => strtoupper($prefix) . ' EduBoard',
                'status' => 'Active',
                'plan' => $selectedPlan,
                'expires_at' => now()->addMonth(),
            ]);

            // Create domain record (FQDN)
            Domain::create([
                'domain' => $domainName,
                'tenant_id' => $tenant->id
            ]);
            
            // Create domain record (Subdomain only, required for InitializeTenancyByDomainOrSubdomain)
            Domain::create([
                'domain' => $safeSubdomain,
                'tenant_id' => $tenant->id
            ]);

            // SYNC CENTRAL USER: Update the user's plan and domain
            $user->update([
                'plan' => $selectedPlan,
                'school_domain' => $domainName . ($port ? ':' . $port : ''),
                'has_selected_template' => true,
                'status' => 'active',
                'trial_ends_at' => null,
            ]);

            // Create admin user inside the tenant database
            $tenant->run(function () use ($user, $selectedPlan, $prefix) {
                // Set storage limit
                $storageLimit = match($selectedPlan) {
                    'Pro' => 15.00,
                    'Ultimate' => 30.00,
                    default => 5.00,
                };
                \App\Models\Tenant::find(tenant('id'))->update([
                    'storage_limit_gb' => $storageLimit
                ]);

                \App\Models\User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                    'role' => 'admin',
                    'school_name' => strtoupper($prefix) . ' EduBoard',
                    'status' => 'active',
                ]);
            });

            // Create mock billing history records ONLY for paid plans
            if ($selectedPlan !== 'Basic') {
                // Current month payment
                BillingHistory::create([
                    'tenant_id' => $tenant->id,
                    'plan' => $selectedPlan,
                    'amount' => $plans[$selectedPlan],
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                ]);

                // Past payments
                for ($i = 1; $i < rand(2, 4); $i++) {
                    $pastPlan = (rand(0, 1) == 0) ? 'Pro' : 'Ultimate';
                    BillingHistory::create([
                        'tenant_id' => $tenant->id,
                        'plan' => $pastPlan,
                        'amount' => $plans[$pastPlan],
                        'payment_status' => 'paid',
                        'paid_at' => now()->subMonths($i),
                        'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                    ]);
                }
            }
        }
    }
}
