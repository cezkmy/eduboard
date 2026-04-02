<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\UserController;
use App\Http\Controllers\AuthController;

use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

// Central Routes
foreach (config('tenancy.central_domains') as $domain) {
    Route::middleware(['web'])->domain($domain)->group(function () {
        // Central Landing Page
        Route::get('/', function () {
            $plans = \App\Models\Plan::all();
            return view('central.pages.home', compact('plans'));
        })->name('home');

        // Authentication Routes
        Route::middleware('guest')->group(function () {
            Route::get('login', [AuthController::class, 'showLogin'])->name('login');
            Route::post('login', [AuthController::class, 'login']);
            Route::get('register', [AuthController::class, 'showRegister'])->name('register');
            Route::post('register', [AuthController::class, 'register']);
        });

        Route::post('logout', [AuthController::class, 'logout'])->name('logout')->withoutMiddleware('guest');

        // Dashboard and Central User Routes
        Route::middleware(['auth'])->group(function () {
            // Redirect to central user dashboard or admin dashboard based on role
            Route::get('/dashboard', function () {
                if (auth()->user()->is_admin) {
                    return redirect()->route('central.admin.dashboard');
                }
                return redirect()->route('central.user.dashboard');
            })->name('dashboard');
            
            // Mark all notifications as read
            Route::get('/notifications/read', function () {
                auth()->user()->unreadNotifications->markAsRead();
                return back();
            })->name('central.notifications.read');

            // Central Admin Routes
            Route::prefix('admin')->name('central.admin.')->middleware(['auth'])->group(function () {
                Route::get('dashboard', function () {
                    if (!auth()->user()->is_admin) {
                        return redirect()->route('central.user.dashboard');
                    }
                    $totalSchools = \App\Models\Tenant::count();
                    $activeUsers = \App\Models\User::count();
                    $recentTenants = \App\Models\Tenant::latest()->take(5)->get();
                    return view('central.admin.dashboard', compact('totalSchools', 'activeUsers', 'recentTenants'));
                })->name('dashboard');

                Route::get('users', function () {
                    if (!auth()->user()->is_admin) {
                        return redirect()->route('central.user.dashboard');
                    }
                    $users = \App\Models\User::latest()->get();
                    return view('central.admin.users', compact('users'));
                })->name('users');
                
                Route::get('tenants', function () { 
                    if (!auth()->user()->is_admin) {
                        return redirect()->route('central.user.dashboard');
                    }
                    $tenants = \App\Models\Tenant::with('domains')->get();
                    $activeCount = \App\Models\Tenant::where('status', 'Active')->count();
                    $deactivatedCount = \App\Models\Tenant::where('status', 'Deactivated')->count();
                    return view('central.admin.tenants', compact('tenants', 'activeCount', 'deactivatedCount')); 
                })->name('tenants');

                Route::post('tenants/{id}/deactivate', [\App\Http\Controllers\Central\TenantController::class, 'deactivate'])->name('tenants.deactivate');
                Route::post('tenants/{id}/activate', [\App\Http\Controllers\Central\TenantController::class, 'activate'])->name('tenants.activate');
                Route::post('tenants/{id}/extend', [\App\Http\Controllers\Central\TenantController::class, 'extend'])->name('tenants.extend');
                Route::post('tenants/{id}/storage', [\App\Http\Controllers\Central\TenantController::class, 'updateStorage'])->name('tenants.storage');
                Route::get('tenants/{id}/details', [\App\Http\Controllers\Central\TenantController::class, 'getDetails'])->name('tenants.details');

                Route::get('plans', [\App\Http\Controllers\Central\PlanController::class, 'index'])->name('plans');
                Route::put('plans/{id}', [\App\Http\Controllers\Central\PlanController::class, 'update'])->name('plans.update');

                Route::get('payments', function () { 
                    if (!auth()->user()->is_admin) return redirect()->route('central.user.dashboard');
                    $payments = \App\Models\BillingHistory::with('tenant')->latest()->get();
                    return view('central.admin.payments', compact('payments')); 
                })->name('payments');

                Route::get('reports', function () { 
                    if (!auth()->user()->is_admin) return redirect()->route('central.user.dashboard');
                    
                    $totalRevenue = \App\Models\BillingHistory::where('payment_status', 'paid')->sum('amount');
                    $monthlyRevenue = \App\Models\BillingHistory::where('payment_status', 'paid')
                        ->whereMonth('paid_at', now()->month)
                        ->whereYear('paid_at', now()->year)
                        ->sum('amount');
                    
                    $totalTenants = \App\Models\Tenant::count();
                    $activeTenants = \App\Models\Tenant::where('status', 'Active')->count();
                    
                    return view('central.admin.reports', compact('totalRevenue', 'monthlyRevenue', 'totalTenants', 'activeTenants')); 
                })->name('reports');

                Route::get('templates', [\App\Http\Controllers\Central\TemplateController::class, 'index'])->name('templates');
                Route::post('templates', [\App\Http\Controllers\Central\TemplateController::class, 'store'])->name('templates.store');
                Route::put('templates/{id}', [\App\Http\Controllers\Central\TemplateController::class, 'update'])->name('templates.update');
                Route::post('templates/category', [\App\Http\Controllers\Central\TemplateController::class, 'storeCategory'])->name('templates.category.store');
                Route::post('templates/type', [\App\Http\Controllers\Central\TemplateController::class, 'storeType'])->name('templates.type.store');

                Route::get('profile', function () { 
                    if (!auth()->user()->is_admin) return redirect()->route('central.user.dashboard');
                    return view('central.admin.profile'); 
                })->name('profile');

                Route::get('users/{id}/billing', function($id) {
                    $user = \App\Models\User::findOrFail($id);
                    $tenant = \App\Models\Tenant::where('owner_id', $user->id)->first();
                    if (!$tenant) return response()->json([]);
                    return response()->json($tenant->billingHistories()->latest()->get());
                })->name('users.billing');

                Route::put('profile', [AuthController::class, 'updateProfile'])->name('profile.update');
                Route::delete('profile', [AuthController::class, 'destroyUser'])->name('profile.delete');

                Route::get('settings', function () { 
                    if (!auth()->user()->is_admin) return redirect()->route('central.user.dashboard');
                    return view('central.admin.settings'); 
                })->name('settings');

                Route::post('settings/general', [UserController::class, 'updateSettings'])->name('settings.general');
                Route::post('settings/release', function(\Illuminate\Http\Request $request) {
                    $request->validate(['version' => 'required|string']);
                    \Illuminate\Support\Facades\Artisan::call('eduboard:release', [
                        'version' => $request->version
                    ]);
                    return back()->with('success', 'Successfully broadcasted System Update ' . $request->version . ' to all active Tenants!');
                })->name('settings.release');
            });

            // Central User Routes
            Route::prefix('user')->name('central.user.')->group(function () {
                Route::get('dashboard', function () {
                    if (auth()->user()->is_admin) {
                        return redirect()->route('central.admin.dashboard');
                    }
                    return view('central.user.dashboard');
                })->name('dashboard');
                
                Route::get('profile', function () { return view('central.user.profile'); })->name('profile');
                Route::put('profile', [UserController::class, 'updateProfile'])->name('profile.update');
                Route::put('password', [UserController::class, 'updatePassword'])->name('password.update');
                
                Route::get('settings', function () { return view('central.user.settings'); })->name('settings');
                Route::post('settings', [UserController::class, 'updateSettings'])->name('settings.update');
                
                Route::get('subscription', function () {
                    $plans = \App\Models\Plan::all();
                    return view('central.user.subscription', compact('plans'));
                })->name('subscription');
                Route::post('subscription/upgrade', function (\Illuminate\Http\Request $request) {
                    $request->validate(['plan' => 'required|string']);
                    $user = auth()->user();
                    
                    // Update user's central billing plan
                    $user->update([
                        'plan' => $request->plan,
                        'status' => 'active',
                        'trial_ends_at' => null
                    ]);
                    
                    // Also upgrade their assigned tenant
                    if ($user->school_domain) {
                        $host = explode(':', $user->school_domain)[0];
                        $domain = \App\Models\Domain::where('domain', $host)->first();
                        if ($domain && $domain->tenant) {
                            $domain->tenant->update(['plan' => $request->plan]);
                        }
                    }
                    
                    // Notify Central Admin via Email & Database
                    $centralAdmin = \App\Models\User::where('role', 'admin')->orWhere('is_admin', true)->first();
                    if ($centralAdmin) {
                        $centralAdmin->notify(new \App\Notifications\CentralPlanUpgradedNotification($user->school_name, $request->plan));
                    }
                        
                    // Send Thank You Email to the upgrading School
                    $user->notify(new \App\Notifications\TenantPlanUpgradedNotification($request->plan));
                    
                    // Create billing history record
                    if ($user->school_domain) {
                        $host = explode(':', $user->school_domain)[0];
                        $domain = \App\Models\Domain::where('domain', $host)->first();
                        if ($domain && $domain->tenant) {
                            $planPrice = match($request->plan) {
                                'Pro' => 2499,
                                'Ultimate' => 4999,
                                'Basic' => 999,
                                default => 0
                            };

                            \App\Models\BillingHistory::create([
                                'tenant_id' => $domain->tenant->id,
                                'plan' => $request->plan,
                                'amount' => $planPrice,
                                'payment_status' => 'paid',
                                'paid_at' => now(),
                                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                            ]);
                        }
                    }

                    return response()->json(['success' => true]);
                })->name('subscription.upgrade');
                Route::get('templates', function () { 
                    if (auth()->user()->is_admin) {
                        return redirect()->route('central.admin.templates');
                    }
                    return view('central.user.templates-select'); 
                })->name('templates');
                
                Route::get('templates/select', [TenantController::class, 'showTemplateSelect'])->name('templates.select');
                Route::post('templates/select', [TenantController::class, 'store'])->name('templates.select.store');
                
                Route::get('domain', function () { 
                    $tenant = \App\Models\Tenant::where('owner_id', auth()->id())->first();
                    return view('central.user.domain', compact('tenant')); 
                })->name('domain');
                
                Route::get('impersonate', [\App\Http\Controllers\Central\TenantController::class, 'impersonate'])->name('impersonate');
                
                Route::post('session/clear', function () { 
                    session()->forget(['tenant_credentials', 'tenant_domain']);
                    return back();
                })->name('session.clear');
            });

            // Tenant Creation Routes
            Route::get('/central/templates', [TenantController::class, 'showTemplateSelect'])->name('central.templates');
            Route::post('/central/tenants', [TenantController::class, 'store'])->name('central.tenants.store');
        });
    });
}
