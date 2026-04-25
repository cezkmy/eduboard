<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenancyServiceProvider.
|
*/

Route::middleware([\App\Http\Middleware\CheckTenantStatus::class])->group(function () {
    Route::get('/', function () {
        return view('tenant_ui.pages.landing');
    })->name('tenant.landing');

    Route::get('/dashboard', function () {
        $role = auth()->user()->role;
        
        if ($role === 'admin') {
            return redirect()->route('tenant.admin.dashboard');
        } elseif ($role === 'teacher') {
            return redirect()->route('tenant.teacher.dashboard');
        } elseif ($role === 'student') {
            return redirect()->route('tenant.student.page');
        }
        
        return view('tenant_ui.pages.dashboard');
    })->middleware(['auth'])->name('tenant.dashboard');

    Route::get('/ping', function () {
        return response()->json(['status' => 'ok']);
    })->middleware(['auth'])->name('tenant.ping');

    // ── Admin Register ──
    Route::get('/admin/register', function () {
        return view('tenant_ui.auth.register-admin');
    })->name('tenant.admin.register');

    // ── Teacher Register ──
    Route::get('/teacher/register', function () {
        return view('tenant_ui.auth.register-teacher');
    })->name('tenant.teacher.register');

    // Tenant specific admin routes
    Route::prefix('admin')->middleware(['auth'])->name('tenant.')->group(function () {
        Route::get('/dashboard', function () {
            if (!auth()->user()->hasPermission('page_admin_dashboard')) {
                abort(403, 'Unauthorized. Standard users cannot access the admin dashboard.');
            }

            $totalAnnouncements = \App\Models\Announcement::where('status', '!=', 'draft')->count();
            $totalTeachers = \App\Models\User::where('role', 'teacher')->where('status', 'active')->count();
            $totalStudents = \App\Models\User::where('role', 'student')->where('status', 'active')->count();
            $pendingApprovalsCount = \App\Models\User::where('status', 'pending')->count();
            
            // Additional Analytics
            $totalReactions = \App\Models\Reaction::count();
            $totalComments = \App\Models\Comment::count();

            $recentAnnouncements = \App\Models\Announcement::where('status', '!=', 'draft')
                ->with(['postedBy', 'comments', 'reactions'])
                ->latest()
                ->take(5)
                ->get();
            
            $recentPendingUsers = \App\Models\User::where('status', 'pending')
                ->latest()
                ->take(5)
                ->get();

            return view('tenant_ui.admin.dashboard', [
                'totalAnnouncements' => $totalAnnouncements,
                'totalTeachers' => $totalTeachers,
                'totalStudents' => $totalStudents,
                'pendingApprovalsCount' => $pendingApprovalsCount,
                'totalReactions' => $totalReactions,
                'totalComments' => $totalComments,
                'recentAnnouncements' => $recentAnnouncements,
                'recentPendingUsers' => $recentPendingUsers,
                'appearance' => ['navPos' => 'left']
            ]);
        })->name('admin.dashboard');

        Route::get('/users', [\App\Http\Controllers\Tenant\UserController::class, 'index'])->name('admin.users');
        Route::post('/users', [\App\Http\Controllers\Tenant\UserController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{user}', [\App\Http\Controllers\Tenant\UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\Tenant\UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/users/bulk-update', [\App\Http\Controllers\Tenant\UserController::class, 'bulkUpdate'])->name('admin.users.bulk_update');
        Route::post('/users/bulk-permissions', [\App\Http\Controllers\Tenant\UserController::class, 'bulkUpdatePermissions'])->name('admin.users.bulk_permissions');
        Route::post('/users/{user}/approve', [\App\Http\Controllers\Tenant\UserController::class, 'approveUser'])->name('admin.users.approve');
        Route::post('/users/{user}/reject', [\App\Http\Controllers\Tenant\UserController::class, 'rejectUser'])->name('admin.users.reject');
        Route::post('/users/{id}/restore', [\App\Http\Controllers\Tenant\UserController::class, 'restore'])->name('admin.users.restore');
        Route::delete('/users/{id}/force', [\App\Http\Controllers\Tenant\UserController::class, 'forceDelete'])->name('admin.users.force_delete');
        Route::post('/users/{user}/lock-account', [\App\Http\Controllers\Tenant\UserController::class, 'lockAccount'])->name('admin.users.lock_account');
        Route::post('/users/{user}/edit-lock', [\App\Http\Controllers\Tenant\UserController::class, 'editLock'])->name('admin.users.edit_lock');
        Route::post('/users/{user}/edit-unlock', [\App\Http\Controllers\Tenant\UserController::class, 'editUnlock'])->name('admin.users.edit_unlock');
        
        // Notifications
        Route::get('/notifications/read', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return back();
        })->name('notifications.read');
        
        // Roles & Permissions
        Route::get('/roles', [\App\Http\Controllers\Tenant\RoleController::class, 'index'])->name('admin.roles');
        Route::post('/roles', [\App\Http\Controllers\Tenant\RoleController::class, 'store'])->name('admin.roles.store');
        Route::delete('/roles/{roleName}', [\App\Http\Controllers\Tenant\RoleController::class, 'destroy'])->name('admin.roles.destroy');
        Route::post('/roles/permissions', [\App\Http\Controllers\Tenant\RoleController::class, 'updatePermissions'])->name('admin.roles.permissions.update');
        Route::post('/roles/user-permissions/{userId}', [\App\Http\Controllers\Tenant\RoleController::class, 'updateUserPermissions'])->name('admin.roles.user_permissions.update');
        
        // Custom System Permissions
        Route::post('/custom-permissions', [\App\Http\Controllers\Tenant\RoleController::class, 'storeCustomPermission'])->name('admin.roles.permissions.custom.store');
        Route::delete('/custom-permissions/{code}', [\App\Http\Controllers\Tenant\RoleController::class, 'deleteCustomPermission'])->name('admin.roles.permissions.custom.destroy');

        Route::get('/announcements', function () { 
            if (!auth()->user()->hasPermission('page_admin_announcements')) {
                abort(403, 'Unauthorized.');
            }
            $query = \App\Models\Announcement::where('status', '!=', 'draft');
            
            if (request('search')) {
                $searchTerm = request('search');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('content', 'like', "%{$searchTerm}%")
                      ->orWhere('category', 'like', "%{$searchTerm}%");
                });
            }

            $announcements = $query->with(['postedBy', 'comments.user', 'comments.replies.user', 'reactions'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->paginate(10);
            return view('tenant_ui.admin.announcements', compact('announcements')); 
        })->name('admin.announcements');

        Route::get('/my-announcements', function () { 
            if (!auth()->user()->hasPermission('page_admin_my_announcements')) {
                abort(403, 'Unauthorized.');
            }
            $query = \App\Models\Announcement::where('posted_by', auth()->id());

            if (request('search')) {
                $searchTerm = request('search');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('content', 'like', "%{$searchTerm}%")
                      ->orWhere('category', 'like', "%{$searchTerm}%");
                });
            }

            $announcements = $query->with(['postedBy', 'comments.user', 'comments.replies.user', 'reactions'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->paginate(10);
            return view('tenant_ui.admin.my-announcements', compact('announcements')); 
        })->name('admin.my-announcements');
        Route::get('/categories', [\App\Http\Controllers\Tenant\OrganizationController::class, 'index'])->name('admin.categories');
        Route::post('/categories', [\App\Http\Controllers\Tenant\OrganizationController::class, 'store'])->name('admin.categories.store');
        Route::post('/categories/presets', [\App\Http\Controllers\Tenant\OrganizationController::class, 'generatePresets'])->name('admin.categories.presets');
        Route::delete('/categories/{category}', [\App\Http\Controllers\Tenant\OrganizationController::class, 'destroy'])->name('admin.categories.destroy');
        Route::post('/settings/school-type', [\App\Http\Controllers\Tenant\OrganizationController::class, 'updateType'])->name('admin.settings.school_type');
        Route::get('/reports', function (\Illuminate\Http\Request $request) {
            if (!auth()->user()->hasPermission('page_admin_reports')) {
                abort(403, 'Unauthorized.');
            }
            if (!tenant()->hasFeature('reports')) abort(403, 'Upgrade your plan to access Reports.');

            $year = $request->get('year', date('Y'));
            $month = $request->get('month');
            $day = $request->get('day');

            $query = \App\Models\Announcement::with('postedBy')
                ->withCount(['comments' => function ($q) use ($year, $month, $day) {
                    if ($year) $q->whereYear('created_at', $year);
                    if ($month) $q->whereMonth('created_at', $month);
                    if ($day) $q->whereDay('created_at', $day);
                }])
                ->withCount(['reactions' => function ($q) use ($year, $month, $day) {
                    if ($year) $q->whereYear('created_at', $year);
                    if ($month) $q->whereMonth('created_at', $month);
                    if ($day) $q->whereDay('created_at', $day);
                }]);

            if ($year) {
                $query->whereYear('created_at', $year);
            }
            if ($month) {
                $query->whereMonth('created_at', $month);
            }
            if ($day) {
                $query->whereDay('created_at', $day);
            }

            $announcements = $query->latest()->get();

            // Calculate totals for the filtered period
            $totalReactionsCount = \App\Models\Reaction::query();
            $totalCommentsCount = \App\Models\Comment::query();
            if ($year) {
                $totalReactionsCount->whereYear('created_at', $year);
                $totalCommentsCount->whereYear('created_at', $year);
            }
            if ($month) {
                $totalReactionsCount->whereMonth('created_at', $month);
                $totalCommentsCount->whereMonth('created_at', $month);
            }
            if ($day) {
                $totalReactionsCount->whereDay('created_at', $day);
                $totalCommentsCount->whereDay('created_at', $day);
            }

            $periodStats = [
                'reactions' => $totalReactionsCount->count(),
                'comments' => $totalCommentsCount->count()
            ];

            $userQuery = \App\Models\User::query();
            if ($year) {
                $userQuery->whereYear('created_at', $year);
            }
            if ($month) {
                $userQuery->whereMonth('created_at', $month);
            }
            if ($day) {
                $userQuery->whereDay('created_at', $day);
            }
            $users = $userQuery->latest()->get();

            $availableYears = \App\Models\Announcement::selectRaw('YEAR(created_at) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();

            if (empty($availableYears)) {
                $availableYears = [date('Y')];
            }

            return view('tenant_ui.admin.reports', [
                'announcements' => $announcements,
                'users' => $users,
                'availableYears' => $availableYears,
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'periodStats' => $periodStats,
            ]);
        })->name('admin.reports');
        
        Route::get('/reports/export', function (\Illuminate\Http\Request $request) {
            if (!auth()->user()->hasPermission('page_admin_reports_pdf')) {
                abort(403, 'Unauthorized.');
            }
            if (!tenant()->hasFeature('reports')) abort(403, 'Upgrade your plan to access Reports.');

            $year = $request->get('year');
            $month = $request->get('month');
            $day = $request->get('day');

            $query = \App\Models\Announcement::with('postedBy')
                ->withCount(['comments' => function ($q) use ($year, $month, $day) {
                    if ($year) $q->whereYear('created_at', $year);
                    if ($month) $q->whereMonth('created_at', $month);
                    if ($day) $q->whereDay('created_at', $day);
                }])
                ->withCount(['reactions' => function ($q) use ($year, $month, $day) {
                    if ($year) $q->whereYear('created_at', $year);
                    if ($month) $q->whereMonth('created_at', $month);
                    if ($day) $q->whereDay('created_at', $day);
                }]);
            if ($year) $query->whereYear('created_at', $year);
            if ($month) $query->whereMonth('created_at', $month);
            if ($day) $query->whereDay('created_at', $day);
            $announcements = $query->latest()->get();

            $userQuery = \App\Models\User::query();
            if ($year) $userQuery->whereYear('created_at', $year);
            if ($month) $userQuery->whereMonth('created_at', $month);
            if ($day) $userQuery->whereDay('created_at', $day);
            $users = $userQuery->latest()->get();

            // Calculate totals for the filtered period
            $totalReactionsCount = \App\Models\Reaction::query();
            $totalCommentsCount = \App\Models\Comment::query();
            if ($year) {
                $totalReactionsCount->whereYear('created_at', $year);
                $totalCommentsCount->whereYear('created_at', $year);
            }
            if ($month) {
                $totalReactionsCount->whereMonth('created_at', $month);
                $totalCommentsCount->whereMonth('created_at', $month);
            }
            if ($day) {
                $totalReactionsCount->whereDay('created_at', $day);
                $totalCommentsCount->whereDay('created_at', $day);
            }

            $periodStats = [
                'reactions' => $totalReactionsCount->count(),
                'comments' => $totalCommentsCount->count()
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tenant_ui.admin.reports-pdf', [
                'announcements' => $announcements,
                'users' => $users,
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'periodStats' => $periodStats,
            ]);

            $pdfContent = $pdf->output();
            $fileSize = strlen($pdfContent);
            
            // Track Download Bandwidth
            if ($fileSize > 0) {
                $gb = $fileSize / 1073741824;
                $tenant = tenant();
                \Illuminate\Support\Facades\DB::connection('mysql')
                    ->table('tenants')
                    ->where('id', $tenant->id)
                    ->increment('bandwidth_used_gb', $gb);
            }

            return response()->streamDownload(
                fn () => print($pdfContent),
                'system-report-' . date('Y-m-d') . '.pdf'
            );
        })->name('admin.reports.export');
        Route::get('/settings', function () { 
            if (!auth()->user()->hasPermission('page_admin_settings')) {
                abort(403, 'Unauthorized.');
            }
            $latestRelease = \App\Services\GitHubService::getLatestRelease();
            return view('tenant_ui.admin.settings', [
                'latestRelease' => $latestRelease
            ]);
        })->name('admin.settings');
        
        Route::post('/settings', [\App\Http\Controllers\Tenant\SettingsController::class, 'update'])->name('admin.settings.update');
        Route::post('/settings/system-version', [\App\Http\Controllers\Tenant\SettingsController::class, 'updateSystemVersion'])->name('admin.settings.system_version');

        Route::get('/subscription', function () { 
            if (!auth()->user()->hasPermission('page_admin_subscription')) {
                abort(403, 'Unauthorized.');
            }

            // Proactively ensure prices are updated in the database
            \App\Models\Plan::where('name', 'Pro')->where('price', '!=', '₱199')->update(['price' => '₱199']);
            \App\Models\Plan::where('name', 'Ultimate')->where('price', '!=', '₱299')->update(['price' => '₱299']);

            $plans = \App\Models\Plan::all();
            return view('tenant_ui.admin.subscription', compact('plans')); 
        })->name('admin.subscription');
        Route::post('/subscription/upgrade', function (\Illuminate\Http\Request $request) { 
            if (!auth()->user()->hasPermission('page_admin_subscription')) {
                return response()->json(['error' => 'Unauthorized. You do not have permission to manage billing.'], 403);
            }
            $request->validate(['plan' => 'required|string']);
            
            // Set the new plan in the tenant database
            $tenant = tenant();
            $oldPlan = $tenant->plan ?? 'Basic';
            $newPlan = $request->plan;

            // Define base storage for each plan
            $storageLimits = [
                'Basic' => 5.0,
                'Pro' => 15.0,
                'Ultimate' => 30.0,
            ];

            $oldBase = $storageLimits[$oldPlan] ?? 5.0;
            $newBase = $storageLimits[$newPlan] ?? 5.0;
            $storageDiff = max(0, $newBase - $oldBase);

            $tenant->update([
                'plan' => $newPlan,
            ]);

            // Update storage limit in central DB if there's a difference
            if ($storageDiff > 0) {
                $currentLimit = (float) ($tenant->storage_limit_gb ?? 5.0);
                \Illuminate\Support\Facades\DB::connection('mysql')
                    ->table('tenants')
                    ->where('id', $tenant->id)
                    ->update(['storage_limit_gb' => $currentLimit + $storageDiff]);
            }
            
            // Notify Central Admin via Email & Database
            tenancy()->central(function () use ($tenant, $request) {
                $centralAdmin = \App\Models\User::where('role', 'admin')->orWhere('is_admin', true)->first();
                if ($centralAdmin) {
                    $centralAdmin->notify(new \App\Notifications\CentralPlanUpgradedNotification($tenant->school_name, $request->plan));
                }
            });
                
            // Send Thank You Email to the upgrading Tenant Admin
            auth()->user()->notify(new \App\Notifications\TenantPlanUpgradedNotification($request->plan));
                
            return response()->json(['success' => true]);
        })->name('admin.subscription.upgrade');

        Route::post('/storage/purchase', function (\Illuminate\Http\Request $request) {
            if (!auth()->user()->hasPermission('page_admin_subscription')) {
                return response()->json(['error' => 'Unauthorized. You do not have permission to manage billing.'], 403);
            }
            $request->validate(['gb' => 'required|numeric', 'price' => 'required|numeric']);
            
            $tenant = tenant();
            $currentLimit = (float)($tenant->storage_limit_gb ?? 5.0);
            $newLimit = $currentLimit + $request->gb;
            
            // Use central DB connection — inside tenant context, must force 'mysql'
            \Illuminate\Support\Facades\DB::connection('mysql')
                ->table('tenants')
                ->where('id', $tenant->id)
                ->update(['storage_limit_gb' => $newLimit]);

            // Update memory object
            $tenant->storage_limit_gb = $newLimit;

            // Add payment to billing history in central DB
            tenancy()->central(function () use ($tenant, $request) {
                \App\Models\BillingHistory::create([
                    'tenant_id' => $tenant->id,
                    'invoice_number' => 'INV-' . strtoupper(\Illuminate\Support\Str::random(8)),
                    'plan' => 'Add-on: +' . $request->gb . 'GB Storage',
                    'amount' => $request->price,
                ]);
            });

            return response()->json(['success' => true]);
        })->name('admin.storage.purchase');

        Route::post('/bandwidth/purchase', function (\Illuminate\Http\Request $request) {
            if (!auth()->user()->hasPermission('page_admin_subscription')) {
                return response()->json(['error' => 'Unauthorized. You do not have permission to manage billing.'], 403);
            }
            $request->validate(['gb' => 'required|numeric', 'price' => 'required|numeric']);
            
            $tenant = tenant();
            $currentLimit = (float)($tenant->bandwidth_limit_gb ?? 50.0);
            $newLimit = $currentLimit + $request->gb;

            // Use central DB connection — inside tenant context, must force 'mysql'
            \Illuminate\Support\Facades\DB::connection('mysql')
                ->table('tenants')
                ->where('id', $tenant->id)
                ->update(['bandwidth_limit_gb' => $newLimit]);

            // Update memory object
            $tenant->bandwidth_limit_gb = $newLimit;

            // Add payment to billing history in central DB
            tenancy()->central(function () use ($tenant, $request) {
                \App\Models\BillingHistory::create([
                    'tenant_id' => $tenant->id,
                    'invoice_number' => 'INV-' . strtoupper(\Illuminate\Support\Str::random(8)),
                    'plan' => 'Add-on: +' . $request->gb . 'GB Bandwidth',
                    'amount' => $request->price,
                ]);
            });

            return response()->json(['success' => true]);
        })->name('admin.bandwidth.purchase');

        // Tenant System Updates (GitHub release integration)
        Route::get('/system-update', [\App\Http\Controllers\Tenant\SystemUpdateController::class, 'index'])
            ->name('admin.system.update');
        Route::post('/system-update/auto-toggle', [\App\Http\Controllers\Tenant\SystemUpdateController::class, 'toggleAutoUpdate'])
            ->name('admin.system.update.auto_toggle');

        Route::get('/templates', function () { 
            if (!auth()->user()->hasPermission('page_admin_templates')) {
                abort(403, 'Unauthorized.');
            }
            $templates = tenancy()->central(function () {
                return \App\Models\Template::all();
            });
            return view('tenant_ui.admin.templates', compact('templates'));
        })->name('admin.templates');
        
        // Version Management
        Route::post('/version/apply', [\App\Http\Controllers\Tenant\VersionController::class, 'applyUpdate'])->name('admin.version.apply');
        Route::post('/version/rollback', [\App\Http\Controllers\Tenant\VersionController::class, 'rollback'])->name('admin.version.rollback');
        Route::get('/version/logs/{updateId}', [\App\Http\Controllers\Tenant\VersionController::class, 'logs'])->name('admin.version.logs');
        
        Route::get('/notifications/read', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return back();
        })->name('notifications.read');
    });

    // Teacher Routes
    Route::prefix('teacher')->middleware(['auth'])->name('tenant.teacher.')->group(function () {
        Route::get('/dashboard', function () {
            if (!auth()->user()->hasPermission('page_teacher_dashboard') && auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized.');
            }
            $user = auth()->user();
            $myAnnouncementIds = \App\Models\Announcement::where('posted_by', $user->id)->pluck('id');
            
            $myAnnouncementsCount = $myAnnouncementIds->count();
            
            // Fetch recent reactions on teacher's posts
            $recentReactions = \App\Models\Reaction::whereIn('announcement_id', $myAnnouncementIds)
                ->with(['user', 'announcement'])
                ->latest()
                ->take(10)
                ->get();
                
            // Fetch recent comments on teacher's posts
            $recentComments = \App\Models\Comment::whereIn('announcement_id', $myAnnouncementIds)
                ->with(['user', 'announcement'])
                ->latest()
                ->take(10)
                ->get();

            $totalReactions = \App\Models\Reaction::whereIn('announcement_id', $myAnnouncementIds)->count();
            $totalComments = \App\Models\Comment::whereIn('announcement_id', $myAnnouncementIds)->count();

            return view('tenant_ui.teacher.dashboard', [
                'myAnnouncementsCount' => $myAnnouncementsCount,
                'recentReactions' => $recentReactions,
                'recentComments' => $recentComments,
                'totalViews' => 0, // Placeholder if views aren't tracked yet
                'totalReactions' => $totalReactions,
                'totalComments' => $totalComments,
            ]);
        })->name('dashboard');
        Route::get('/announcements', function () { 
            if (!auth()->user()->hasPermission('page_teacher_announcements')) {
                abort(403, 'Unauthorized.');
            }
            $user = auth()->user();
            $query = \App\Models\Announcement::where('status', '!=', 'draft')->forUser($user);

            if (request('search')) {
                $searchTerm = request('search');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('content', 'like', "%{$searchTerm}%")
                      ->orWhere('category', 'like', "%{$searchTerm}%");
                });
            }

            $announcements = $query->with(['postedBy', 'comments.user', 'comments.replies.user', 'reactions'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->paginate(10);
            return view('tenant_ui.teacher.announcements', compact('announcements')); 
        })->name('announcements');
        Route::get('/my-announcements', function () { 
            if (!auth()->user()->hasPermission('page_teacher_my_announcements')) {
                abort(403, 'Unauthorized.');
            }
            $query = \App\Models\Announcement::where('posted_by', auth()->id());

            if (request('search')) {
                $searchTerm = request('search');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('content', 'like', "%{$searchTerm}%")
                      ->orWhere('category', 'like', "%{$searchTerm}%");
                });
            }

            $announcements = $query->with(['postedBy', 'comments.user', 'comments.replies.user', 'reactions'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->paginate(10);
            return view('tenant_ui.teacher.my-announcements', compact('announcements')); 
        })->name('my-announcements');
    });

    // Student Routes
    Route::prefix('student')->middleware(['auth', 'student'])->name('tenant.student.')->group(function () {
        Route::get('/dashboard', function () {
            if (!auth()->user()->hasPermission('page_student_studentpage')) {
                abort(403, 'Unauthorized.');
            }
            $user = auth()->user();
            $query = \App\Models\Announcement::where('status', '!=', 'draft')->forUser($user);

            if (request('search')) {
                $searchTerm = request('search');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('content', 'like', "%{$searchTerm}%")
                      ->orWhere('category', 'like', "%{$searchTerm}%");
                });
            }

            $announcements = $query->with(['postedBy', 'comments.user', 'reactions'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->paginate(10);
            return view('tenant_ui.students.studentpage', [
                'announcements' => $announcements
            ]);
        })->name('page');

        // Student Profile Route
        Route::get('/profile', function () { 
            if (!auth()->user()->hasPermission('page_profile')) abort(403, 'Unauthorized access to profile.');
            
            // Calculate DB size for the tenant
            $dbSize = \Illuminate\Support\Facades\DB::select("SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = DATABASE()")[0]->size ?? 0;
            
            return view('tenant_ui.profile.edit', ['user' => auth()->user(), 'dbSize' => $dbSize]); 
        })->name('profile');
    });

    // ── Shared Announcement CRUD & Interactions ──
    Route::middleware(['auth'])->name('tenant.')->group(function () {
        // Shared Interaction routes (Admin, Teacher, Student)
        Route::post('/announcements/{announcement}/react', [App\Http\Controllers\AnnouncementInteractionController::class, 'toggleReaction'])->name('announcements.react.toggle');
        Route::get('/announcements/{announcement}/comments', [App\Http\Controllers\AnnouncementInteractionController::class, 'comments'])->name('announcements.comments');
        Route::post('/announcements/{announcement}/comments', [App\Http\Controllers\AnnouncementInteractionController::class, 'storeComment'])->name('announcements.comment.store');

        Route::post('/announcements', [\App\Http\Controllers\Tenant\AnnouncementController::class, 'store'])->name('announcements.store');

        Route::get('/announcements/{announcement}/edit', function (\App\Models\Announcement $announcement) {
            return view('tenant_ui.teacher.edit-announcement', compact('announcement'));
        })->name('announcements.edit');

        Route::put('/announcements/{announcement}', [\App\Http\Controllers\Tenant\AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [\App\Http\Controllers\Tenant\AnnouncementController::class, 'destroy'])->name('announcements.destroy');

        // ── Legacy / Shared Routes ──
        Route::get('/all-announcements', function () {
            return view('tenant_ui.announcements');
        })->name('announcements.all');

        // ── Support Chat ──
        Route::get('/support/messages', [\App\Http\Controllers\Tenant\SupportController::class, 'messages'])->name('support.messages');
        Route::post('/support/send', [\App\Http\Controllers\Tenant\SupportController::class, 'send'])->name('support.send');
        Route::post('/support/ticket', [\App\Http\Controllers\Tenant\SupportController::class, 'createTicket'])->name('support.ticket');
        Route::get('/support/inbox', [\App\Http\Controllers\Tenant\SupportController::class, 'inbox'])->name('support.inbox');
        Route::get('/support/unread', [\App\Http\Controllers\Tenant\SupportController::class, 'unreadCount'])->name('support.unread');
        
        // Tenant Admin -> Central routes
        Route::get('/support/central/inbox', [\App\Http\Controllers\Tenant\SupportController::class, 'centralInbox'])->name('support.central.inbox');
        Route::get('/support/central/messages', [\App\Http\Controllers\Tenant\SupportController::class, 'centralMessages'])->name('support.central.messages');
        Route::post('/support/central/send', [\App\Http\Controllers\Tenant\SupportController::class, 'centralSend'])->name('support.central.send');
        Route::post('/support/central/ticket', [\App\Http\Controllers\Tenant\SupportController::class, 'centralCreateTicket'])->name('support.central.ticket');
    });

    // ── Subscription Plans (public) ──
    Route::get('/plans', function () {
        return view('tenant_ui.subscriptions');
    })->name('tenant.plans');

    // ── Profile ──
    Route::middleware(['auth'])->name('tenant.')->group(function () {
        Route::get('/profile', function () { 
            if (!auth()->user()->hasPermission('page_profile')) abort(403, 'Unauthorized access to profile.');
            
            // Calculate DB size for the tenant
            $dbSize = \Illuminate\Support\Facades\DB::select("SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = DATABASE()")[0]->size ?? 0;
            
            return view('tenant_ui.profile.edit', ['user' => auth()->user(), 'dbSize' => $dbSize]); 
        })->name('profile.edit');
        
        Route::patch('/profile', function (\Illuminate\Http\Request $request) {
            if (!auth()->user()->hasPermission('page_profile')) abort(403, 'Unauthorized.');
            return app(App\Http\Controllers\AuthController::class)->updateProfile($request);
        })->name('profile.update');
        Route::delete('/profile', [App\Http\Controllers\AuthController::class, 'destroyUser'])->name('profile.destroy');

        Route::post('/profile/security', function (\Illuminate\Http\Request $request) {
            if (!auth()->user()->hasPermission('page_profile')) abort(403, 'Unauthorized.');
            return app(App\Http\Controllers\AuthController::class)->updatePassword($request);
        })->name('profile.security');

        // Backward-compatible password update route used by profile partials.
        Route::put('/password', function (\Illuminate\Http\Request $request) {
            if (!auth()->user()->hasPermission('page_profile')) abort(403, 'Unauthorized.');
            return app(App\Http\Controllers\AuthController::class)->updatePassword($request);
        })->name('password.update');
    });

    // Tenant Login/Logout
    Route::get('/autologin', [App\Http\Controllers\AuthController::class, 'autologin'])->name('tenant.autologin');
    Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('tenant.login');
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('tenant.login.post');
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('tenant.logout');

    // Password Reset (Tenant)
    Route::get('/forgot-password', [App\Http\Controllers\AuthController::class, 'showForgotPassword'])->name('tenant.password.request');
    Route::post('/forgot-password', [App\Http\Controllers\AuthController::class, 'sendResetCode'])->name('tenant.password.email');
    Route::get('/verify-code', [App\Http\Controllers\AuthController::class, 'showVerifyCode'])->name('tenant.password.verify');
    Route::post('/verify-code', [App\Http\Controllers\AuthController::class, 'verifyCode'])->name('tenant.password.post-verify');
    Route::get('/reset-password', [App\Http\Controllers\AuthController::class, 'showResetPassword'])->name('tenant.password.reset');
    Route::post('/reset-password', [App\Http\Controllers\AuthController::class, 'resetPassword'])->name('tenant.password.update-post');

    // Tenant Registration
    Route::get('/register', [App\Http\Controllers\AuthController::class, 'showTenantRegister'])->name('tenant.register');
    Route::post('/register', [App\Http\Controllers\AuthController::class, 'tenantRegister']);
    Route::get('/waiting-approval', function () {
        return view('tenant_ui.auth.waiting-approval');
    })->name('tenant.auth.waiting-approval');
});
