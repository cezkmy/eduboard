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
        Route::post('/users/{user}/approve', [\App\Http\Controllers\Tenant\UserController::class, 'approveUser'])->name('admin.users.approve');
        Route::post('/users/{user}/reject', [\App\Http\Controllers\Tenant\UserController::class, 'rejectUser'])->name('admin.users.reject');
        Route::post('/users/{id}/restore', [\App\Http\Controllers\Tenant\UserController::class, 'restore'])->name('admin.users.restore');
        Route::delete('/users/{id}/force', [\App\Http\Controllers\Tenant\UserController::class, 'forceDelete'])->name('admin.users.force_delete');
        Route::post('/users/{user}/lock-account', [\App\Http\Controllers\Tenant\UserController::class, 'lockAccount'])->name('admin.users.lock_account');
        Route::post('/users/{user}/edit-lock', [\App\Http\Controllers\Tenant\UserController::class, 'editLock'])->name('admin.users.edit_lock');
        Route::post('/users/{user}/edit-unlock', [\App\Http\Controllers\Tenant\UserController::class, 'editUnlock'])->name('admin.users.edit_unlock');
        Route::get('/announcements', function () { 
            $announcements = \App\Models\Announcement::where('status', '!=', 'draft')
                ->with(['postedBy', 'comments.user', 'comments.replies.user', 'reactions'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->get();
            return view('tenant_ui.admin.announcements', compact('announcements')); 
        })->name('admin.announcements');

        Route::get('/my-announcements', function () { 
            $announcements = \App\Models\Announcement::where('posted_by', auth()->id())
                ->with(['postedBy', 'comments.user', 'comments.replies.user', 'reactions'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->get();
            return view('tenant_ui.admin.my-announcements', compact('announcements')); 
        })->name('admin.my-announcements');
        Route::get('/categories', [\App\Http\Controllers\Tenant\OrganizationController::class, 'index'])->name('admin.categories');
        Route::post('/categories', [\App\Http\Controllers\Tenant\OrganizationController::class, 'store'])->name('admin.categories.store');
        Route::post('/categories/presets', [\App\Http\Controllers\Tenant\OrganizationController::class, 'generatePresets'])->name('admin.categories.presets');
        Route::delete('/categories/{category}', [\App\Http\Controllers\Tenant\OrganizationController::class, 'destroy'])->name('admin.categories.destroy');
        Route::post('/settings/school-type', [\App\Http\Controllers\Tenant\OrganizationController::class, 'updateType'])->name('admin.settings.school_type');
        Route::get('/reports', function (\Illuminate\Http\Request $request) {
            if (!tenant()->hasFeature('reports')) abort(403, 'Upgrade your plan to access Reports.');

            $year = $request->get('year', date('Y'));
            $month = $request->get('month');
            $day = $request->get('day');

            $query = \App\Models\Announcement::with('postedBy');

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
            ]);
        })->name('admin.reports');
        
        Route::get('/reports/export', function (\Illuminate\Http\Request $request) {
            if (!tenant()->hasFeature('reports')) abort(403, 'Upgrade your plan to access Reports.');

            $year = $request->get('year');
            $month = $request->get('month');
            $day = $request->get('day');

            $query = \App\Models\Announcement::with('postedBy');
            if ($year) $query->whereYear('created_at', $year);
            if ($month) $query->whereMonth('created_at', $month);
            if ($day) $query->whereDay('created_at', $day);
            $announcements = $query->latest()->get();

            $userQuery = \App\Models\User::query();
            if ($year) $userQuery->whereYear('created_at', $year);
            if ($month) $userQuery->whereMonth('created_at', $month);
            if ($day) $userQuery->whereDay('created_at', $day);
            $users = $userQuery->latest()->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tenant_ui.admin.reports-pdf', [
                'announcements' => $announcements,
                'users' => $users,
                'year' => $year,
                'month' => $month,
                'day' => $day
            ]);

            return $pdf->download('system-report-' . date('Y-m-d') . '.pdf');
        })->name('admin.reports.export');
        Route::get('/settings', function () { 
            $latestRelease = \App\Services\GitHubService::getLatestRelease();
            return view('tenant_ui.admin.settings', [
                'latestRelease' => $latestRelease
            ]);
        })->name('admin.settings');
        
        Route::post('/settings', [\App\Http\Controllers\Tenant\SettingsController::class, 'update'])->name('admin.settings.update');
        Route::post('/settings/system-version', [\App\Http\Controllers\Tenant\SettingsController::class, 'updateSystemVersion'])->name('admin.settings.system_version');

        Route::get('/subscription', function () { 
            $plans = \App\Models\Plan::all();
            return view('tenant_ui.admin.subscription', compact('plans')); 
        })->name('admin.subscription');
        Route::post('/subscription/upgrade', function (\Illuminate\Http\Request $request) { 
            $request->validate(['plan' => 'required|string']);
            
            // Set the new plan in the tenant database
            $tenant = tenant();
            $tenant->update([
                'plan' => $request->plan,
            ]);
            
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
        Route::get('/templates', function () { 
            $templates = tenancy()->central(function () {
                return \App\Models\Template::all();
            });
            return view('tenant_ui.admin.templates', compact('templates'));
        })->name('admin.templates');
        
        // Version Management
        Route::post('/version/apply', [\App\Http\Controllers\Tenant\VersionController::class, 'applyUpdate'])->name('admin.version.apply');
        Route::post('/version/rollback', [\App\Http\Controllers\Tenant\VersionController::class, 'rollback'])->name('admin.version.rollback');
        
        Route::get('/notifications/read', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return back();
        })->name('notifications.read');
    });

    // Teacher Routes
    Route::prefix('teacher')->middleware(['auth'])->name('tenant.teacher.')->group(function () {
        Route::get('/dashboard', function () {
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
            $user = auth()->user();
            $announcements = \App\Models\Announcement::where('status', '!=', 'draft')
                ->forUser($user)
                ->with(['postedBy', 'comments.user', 'comments.replies.user', 'reactions'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->get();
            return view('tenant_ui.teacher.announcements', compact('announcements')); 
        })->name('announcements');
        Route::get('/my-announcements', function () { 
            $announcements = \App\Models\Announcement::where('posted_by', auth()->id())
                ->with(['postedBy', 'comments.user', 'comments.replies.user', 'reactions'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->get();
            return view('tenant_ui.teacher.my-announcements', compact('announcements')); 
        })->name('my-announcements');
    });

    // Student Routes
    Route::prefix('student')->middleware(['auth', 'student'])->name('tenant.student.')->group(function () {
        Route::get('/dashboard', function () {
            $user = auth()->user();
            $announcements = \App\Models\Announcement::where('status', '!=', 'draft')
                ->forUser($user)
                ->with(['postedBy', 'comments.user', 'reactions'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->get();
            return view('tenant_ui.students.studentpage', [
                'announcements' => $announcements
            ]);
        })->name('page');

        // Student Profile Route
        Route::get('/profile', function () { 
            return view('tenant_ui.profile.edit', ['user' => auth()->user()]); 
        })->name('profile');
    });

    // ── Shared Announcement CRUD & Interactions ──
    Route::middleware(['auth'])->name('tenant.')->group(function () {
        // Shared Interaction routes (Admin, Teacher, Student)
        Route::post('/announcements/{announcement}/comment', [App\Http\Controllers\AnnouncementInteractionController::class, 'storeComment'])->name('announcements.comment.store');
        Route::post('/announcements/{announcement}/react', [App\Http\Controllers\AnnouncementInteractionController::class, 'toggleReaction'])->name('announcements.react.toggle');

        Route::post('/announcements', [\App\Http\Controllers\Tenant\AnnouncementController::class, 'store'])->name('announcements.store');

        Route::get('/announcements/{id}/edit', function ($id) {
            return view('tenant_ui.teacher.edit-announcement');
        })->name('announcements.edit');

        Route::put('/announcements/{announcement}', [\App\Http\Controllers\Tenant\AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [\App\Http\Controllers\Tenant\AnnouncementController::class, 'destroy'])->name('announcements.destroy');

        // ── Legacy / Shared Routes ──
        Route::get('/all-announcements', function () {
            return view('tenant_ui.announcements');
        })->name('announcements.all');
    });

    // ── Subscription Plans (public) ──
    Route::get('/plans', function () {
        return view('tenant_ui.subscriptions');
    })->name('tenant.plans');

    // ── Profile ──
    Route::middleware(['auth'])->name('tenant.')->group(function () {
        Route::get('/profile', function () { 
            return view('tenant_ui.profile.edit', ['user' => auth()->user()]); 
        })->name('profile.edit');
        
        Route::patch('/profile', [App\Http\Controllers\AuthController::class, 'updateProfile'])->name('profile.update');
        Route::delete('/profile', [App\Http\Controllers\AuthController::class, 'destroyUser'])->name('profile.destroy');
        Route::put('/password', [App\Http\Controllers\AuthController::class, 'updatePassword'])->name('password.update');
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
