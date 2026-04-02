<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    public function showTemplateSelect()
    {
        return view('central.user.templates-select');
    }

    public function updateLayout(Request $request)
    {
        $request->validate([
            'template_id' => 'required|integer',
            'template_name' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        if (!in_array($user->plan ?? 'Basic', ['Pro', 'Ultimate'], true)) {
            return back()->withErrors(['template_id' => 'Upgrade to Pro to change your domain layout template.']);
        }

        $tenant = Tenant::where('owner_id', $user->id)->first();
        if (!$tenant) {
            return back()->withErrors(['template_id' => 'No school found for your account.']);
        }

        $tenant->update([
            'template_id' => (int) $request->template_id,
            'template_name' => $request->template_name ?? ('Template ' . (int) $request->template_id),
        ]);

        return back()->with('success', 'Domain layout updated successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'custom_domain' => 'required|string|max:255',
            'template_id' => 'required',
        ]);

        $user = auth()->user();

        if ($user->has_selected_template) {
            return back()->withErrors(['template_id' => 'You have already selected a layout template. Please upgrade your plan if you want to unlock more templates.']);
        }
        
        // Use underscores for DB names and subdomains as requested
        $subdomain = Str::slug($request->custom_domain, '_');
        
        // Ensure the subdomain ends with _eduboard if not already present
        if (!str_ends_with($subdomain, '_eduboard')) {
            $subdomain .= '_eduboard';
        }
        
        // Use the current host to determine the domain suffix
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?? request()->getHost();
        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            // Local dev should use *.localhost so tenancy can resolve fast.
            $baseHost = 'localhost';
        } elseif (str_starts_with($host, 'eduboard.')) {
            $baseHost = substr($host, 9);
        } else {
            $baseHost = $host;
        }
        
        // Canonical tenant hostname for local dev should not contain underscores.
        $safeSubdomain = $baseHost === 'localhost'
            ? str_replace('_', '-', $subdomain)
            : $subdomain;

        $domainName = $safeSubdomain . '.' . $baseHost;
        
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
                // Fix: Ensure we ONLY notify the actual Central SaaS admin (is_admin = true)
                $centralAdmin = \App\Models\User::where('role', 'admin')->first();
                if ($centralAdmin) {
                    $centralAdmin->notify(new \App\Notifications\CentralNewTenantNotification($tenant->school_name, $domainName));
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send New Tenant Notification Email: ' . $e->getMessage());
            }

            \Log::info('Step 2: Calling domains()->create');
            $tenant->domains()->create(['domain' => $domainName]); // Full domain for Domain identification
            $tenant->domains()->create(['domain' => $subdomain]);  // Subdomain for Subdomain identification
            $tenant->domains()->firstOrCreate(['domain' => $safeSubdomain]); // Safe subdomain for local dev

            // If local dev, also register a safe *.localhost host (underscores are problematic in hostnames).
            if ($baseHost === 'localhost') {
                $tenant->domains()->firstOrCreate(['domain' => $subdomain . '.localhost']);
                $tenant->domains()->firstOrCreate(['domain' => $safeSubdomain . '.localhost']);
            }
            \Log::info('Step 2 Success.');

            \Log::info('Step 3: Creating Admin User for Tenant');
            
            // Get the central user's password hash to sync it
            $centralPasswordHash = $user->password;

            // We use the tenant context to create the admin user
            $tenant->run(function () use ($user, $centralPasswordHash) {
                // Determine storage limit based on plan
                $storageLimit = match($user->plan) {
                    'Pro' => 15.00,
                    'Ultimate' => 30.00,
                    default => 5.00,
                };

                // Update tenant storage limit directly
                \App\Models\Tenant::find(tenant('id'))->update([
                    'storage_limit_gb' => $storageLimit
                ]);

                \App\Models\User::create([
                    'name' => 'School Admin',
                    'email' => $user->email,
                    'password' => $centralPasswordHash, // Sync hashed password directly
                    'role' => 'admin',
                    'school_name' => $user->school_name,
                    'status' => 'active',
                ]);
            });
            \Log::info('Step 3 Success.');

            \Log::info('Step 4: Updating central user status');
            // Update the user model directly and ensure it's saved to central DB
            $user->has_selected_template = true;
            
            // For local development, we want to include the port in the school_domain link.
            // Use the canonical safe hostname so the user can copy/paste and it always works.
            $port = parse_url(config('app.url'), PHP_URL_PORT);
            $user->school_domain = $domainName . ($port ? ':' . $port : '');
            
            $user->save();
            
            \Log::info('Step 4 Success. User status updated in central DB.');
            \Log::info('--- Tenant Creation Complete ---');

            // Redirect directly to Domain Management instead of Dashboard
            return redirect()->route('central.user.domain')->with('success', 'Your school has been successfully created! You can now manage your domain and database here.');
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

    public function impersonate()
    {
        $user = Auth::user();
        $tenant = Tenant::where('owner_id', $user->id)->first();

        if (!$tenant) {
            return back()->with('error', 'No school found for your account.');
        }

        $token = Str::random(60);
        $expiresAt = now()->addMinutes(5);

        // Switch to tenant context and save token
        $tenant->run(function () use ($user, $token, $expiresAt) {
            $tenantUser = \App\Models\User::where('email', $user->email)->first();
            if ($tenantUser) {
                $tenantUser->update([
                    'autologin_token' => $token,
                    'autologin_token_expires_at' => $expiresAt,
                ]);
            }
        });

        // Logout from Central
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Redirect to tenant's autologin route
        $scheme = request()->getScheme();
        $currentHost = request()->getHost();
        $port = request()->getPort();

        // On localhost, always use a fast + valid hostname and ensure it's registered in tenancy domains.
        if (in_array($currentHost, ['localhost', '127.0.0.1', '::1'], true)) {
            $rawSub = (string) $tenant->id;
            $safeSub = str_replace('_', '-', $rawSub); // underscores are slow/problematic in hostnames

            // IMPORTANT: tenancy subdomain resolver looks up domains.domain = {subdomain}
            // for hosts like {subdomain}.localhost, so we must register BOTH:
            // - {subdomain} (safeSub)
            // - {subdomain}.localhost (fqdn)
            //
            // Also register the raw underscore variant so direct visits like buksu_eduboard.localhost work.
            $tenant->domains()->firstOrCreate(['domain' => $rawSub]);
            $tenant->domains()->firstOrCreate(['domain' => $rawSub . '.localhost']);
            $tenant->domains()->firstOrCreate(['domain' => $safeSub]);
            $tenant->domains()->firstOrCreate(['domain' => $safeSub . '.localhost']);

            $domain = $safeSub . '.localhost';
        } else {
            // Determine the base host for tenant domains.
            if (str_starts_with($currentHost, 'eduboard.')) {
                $baseHost = substr($currentHost, 9);
            } else {
                $baseHost = $currentHost;
            }

            $domains = $tenant->domains()->pluck('domain')->all();
            $domain = collect($domains)->first(fn ($d) => str_ends_with($d, '.' . $baseHost))
                ?: collect($domains)->first(fn ($d) => str_contains($d, '.'))
                ?: ($tenant->id . '.' . $baseHost);

            if (!str_contains($domain, '.')) {
                $domain = $domain . '.' . $baseHost;
            }
        }

        $portPart = in_array((int) $port, [80, 443], true) ? '' : (':' . $port);
        $redirectUrl = $scheme . "://" . $domain . $portPart . "/autologin?token=" . $token;

        return redirect($redirectUrl);
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

    public function extend(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $days = $request->input('days', 30);
        
        $currentExpiry = $tenant->expires_at ? \Carbon\Carbon::parse($tenant->expires_at) : now();
        if ($currentExpiry->isPast()) {
            $currentExpiry = now();
        }
        
        $tenant->expires_at = $currentExpiry->addDays($days);
        $tenant->status = 'Active';
        $tenant->save();

        return back()->with('success', "School subscription successfully extended by $days days.");
    }

    public function getDetails($id)
    {
        $tenant = Tenant::findOrFail($id);
        
        // Count users within the tenant database
        $stats = $tenant->run(function() {
            return [
                'admins' => \App\Models\User::where('role', 'admin')->count(),
                'teachers' => \App\Models\User::where('role', 'teacher')->count(),
                'students' => \App\Models\User::where('role', 'student')->count(),
                'total' => \App\Models\User::count(),
            ];
        });

        return response()->json([
            'school_name' => $tenant->school_name,
            'stats' => $stats
        ]);
    }

    public function updateStorage(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $request->validate([
            'storage_limit_gb' => 'required|numeric|min:0',
        ]);

        $tenant->storage_limit_gb = $request->storage_limit_gb;
        $tenant->save();

        return back()->with('success', 'Storage limit updated successfully.');
    }
}
