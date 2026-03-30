<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function showTemplateSelect()
    {
        return view('central.user.templates-select');
    }

    public function store(Request $request)
    {
        $request->validate([
            'custom_domain' => 'required|string|max:255',
            'template_id' => 'required',
        ]);

        $user = auth()->user();
        
        // Use underscores for DB names and subdomains as requested
        $subdomain = Str::slug($request->custom_domain, '_');
        
        // Ensure the subdomain ends with _eduboard if not already present
        if (!str_ends_with($subdomain, '_eduboard')) {
            $subdomain .= '_eduboard';
        }
        
        // Standardize on localhost for local development
        // We want the pattern [subdomain]_eduboard.localhost
        $domainName = $subdomain . '.localhost';
        
        // If we're on a specific port, add it to the domain name if needed for identification
        // However, Stancl/Tenancy usually handles the port separately or expects the domain without port
        // Let's stick to the domain name without port for the DB record.

        // Check if domain already exists
        if (\App\Models\Domain::where('domain', $domainName)->exists()) {
            return back()->withErrors(['custom_domain' => 'This domain name is already taken.'])->withInput();
        }

        try {
            \Log::info('--- Tenant Creation Start ---');
            \Log::info('Subdomain: ' . $subdomain);
            \Log::info('Domain: ' . $domainName);
            
            // Clean up any old ghost tenant with same ID if it exists
            $oldTenant = Tenant::find($subdomain);
            if ($oldTenant) {
                $oldTenant->delete(); // This should trigger DeleteDatabase job if configured
            } else {
                // If the tenant record is missing but the database exists, we need to handle it
                // Stancl/Tenancy's CreateDatabase job will fail if the DB already exists.
                // We'll manually drop it if it exists to ensure a clean start.
                $dbName = $subdomain . config('tenancy.database.suffix');
                \Illuminate\Support\Facades\DB::statement("DROP DATABASE IF EXISTS `$dbName` ");
                
                \Illuminate\Support\Facades\DB::connection('mysql')->table('tenants')->where('id', $subdomain)->delete();
                \Illuminate\Support\Facades\DB::connection('mysql')->table('domains')->where('tenant_id', $subdomain)->delete();
            }
            \Log::info('Ghost records and database cleared (if any).');

            \Log::info('Step 1: Calling Tenant::create');
            $adminPassword = Str::random(10);
            $tenant = Tenant::create([
                'id' => $subdomain,
                'school_name' => $user->school_name,
                'template_id' => $request->template_id,
                'template_name' => $request->template_name ?? "Template " . $request->template_id,
                'owner_id' => $user->id,
                'plan' => $user->plan,
                'status' => 'Active',
                'admin_password' => $adminPassword,
            ]);
            \Log::info('Step 1 Success. Tenant ID: ' . ($tenant->id ?? 'EMPTY'));
            
            // Re-fetch tenant to ensure all attributes are correctly loaded from DB
            $tenant = $tenant->fresh();
            \Log::info('Fresh Tenant ID: ' . ($tenant->id ?? 'EMPTY'));
            \Log::info('Actual DB Name used: ' . $tenant->getInternal('db_name'));
            
            // Notify Central Admin via Email & Database
            try {
                $centralAdmin = \App\Models\User::where('role', 'admin')->orWhere('is_admin', true)->first();
                if ($centralAdmin) {
                    $centralAdmin->notify(new \App\Notifications\CentralNewTenantNotification($tenant->school_name, $domainName));
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send New Tenant Notification Email: ' . $e->getMessage());
            }

            \Log::info('Step 2: Calling domains()->create');
            $tenant->domains()->create(['domain' => $domainName]); // Full domain for Domain identification
            $tenant->domains()->create(['domain' => $subdomain]);  // Subdomain for Subdomain identification
            \Log::info('Step 2 Success.');

            \Log::info('Step 3: Creating Admin User for Tenant');
            
            // We use the tenant context to create the admin user
            $tenant->run(function () use ($user, $adminPassword) {
                \App\Models\User::create([
                    'name' => 'School Admin',
                    'email' => $user->email,
                    'password' => \Illuminate\Support\Facades\Hash::make($adminPassword),
                    'role' => 'admin',
                    'is_admin' => true,
                    'school_name' => $user->school_name,
                    'status' => 'active',
                ]);
            });
            \Log::info('Step 3 Success.');

            \Log::info('Step 4: Updating central user status');
            // Update the user model directly and ensure it's saved to central DB
            $user->has_selected_template = true;
            
            // For local development, we want to include the port in the school_domain link
            $port = parse_url(config('app.url'), PHP_URL_PORT);
            $user->school_domain = $domainName . ($port ? ':' . $port : '');
            
            $user->save();
            
            \Log::info('Step 4 Success. User status updated in central DB.');
            \Log::info('--- Tenant Creation Complete ---');

            // Redirect directly to Domain Management instead of Dashboard
            return redirect()->route('central.user.domain')->with('success', 'School successfully created! You can manage your domain and database here.');
        } catch (\Exception $e) {
            \Log::error('CRITICAL: Tenant creation failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // Cleanup tenant if it was created but domain/admin failed
            if (isset($tenant)) {
                try {
                    $tenant->delete();
                } catch (\Exception $cleanupError) {
                    \Log::error('Cleanup failed: ' . $cleanupError->getMessage());
                }
            }
            
            return back()->withErrors(['custom_domain' => 'Fatal Error: ' . $e->getMessage() . '. Please check logs for details.'])->withInput();
        }
    }

    public function deactivate($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->status = 'Deactivated';
        $tenant->save();

        return back()->with('success', 'School successfully deactivated.');
    }

    public function activate($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->status = 'Active';
        $tenant->save();

        return back()->with('success', 'School successfully activated.');
    }

    public function extend($id)
    {
        $tenant = Tenant::findOrFail($id);
        
        $currentExpiry = $tenant->expires_at ? \Carbon\Carbon::parse($tenant->expires_at) : now();
        // If it's already past, start from today
        if ($currentExpiry->isPast()) {
            $currentExpiry = now();
        }
        
        $tenant->expires_at = $currentExpiry->addDays(30);
        $tenant->status = 'Active'; // Re-activate by default if extended
        $tenant->save();

        return back()->with('success', 'School subscription extended by 30 days.');
    }
}
