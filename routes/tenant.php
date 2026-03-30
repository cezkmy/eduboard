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
            $totalAnnouncements = \App\Models\Announcement::count();
            $totalTeachers = \App\Models\User::where('role', 'teacher')->count();
            $totalStudents = \App\Models\User::where('role', 'student')->count();
            $pendingApprovalsCount = \App\Models\User::where('status', 'pending')->count();
            $recentAnnouncements = \App\Models\Announcement::with('postedBy')->latest()->take(5)->get();
            $recentUsers = \App\Models\User::latest()->take(5)->get();
            $recentPendingUsers = \App\Models\User::where('status', 'pending')->latest()->take(5)->get();

            return view('tenant_ui.admin.dashboard', [
                'totalAnnouncements' => $totalAnnouncements,
                'totalTeachers' => $totalTeachers,
                'totalStudents' => $totalStudents,
                'pendingApprovalsCount' => $pendingApprovalsCount,
                'recentAnnouncements' => $recentAnnouncements,
                'recentUsers' => $recentUsers,
                'recentPendingUsers' => $recentPendingUsers,
                'appearance' => ['navPos' => 'left']
            ]);
        })->name('admin.dashboard');

        Route::get('/users', function () { 
            $adminCount = \App\Models\User::where('role', 'admin')->count();
            $teacherCount = \App\Models\User::where('role', 'teacher')->count();
            return view('tenant_ui.admin.users', compact('adminCount', 'teacherCount')); 
        })->name('admin.users');
        Route::get('/announcements', function () { return view('tenant_ui.admin.announcements'); })->name('admin.announcements');
        Route::get('/my-announcements', function () { return view('tenant_ui.admin.my-announcements'); })->name('admin.my-announcements');
        Route::get('/categories', function () { 
            if (!tenant()->hasFeature('categories')) abort(403, 'Upgrade your plan to access Categories.');
            return view('tenant_ui.admin.categories'); 
        })->name('admin.categories');
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
        Route::get('/settings', function () { return view('tenant_ui.admin.settings'); })->name('admin.settings');
        
        Route::post('/settings', function (\Illuminate\Http\Request $request) {
            $tenant = tenant();
            
            if ($tenant->plan === 'Basic' && $tenant->has_updated_settings) {
                return back()->with('error', 'Basic plan allows only one-time settings update. Please upgrade plan to unlock unlimited settings changes.');
            }

            if ($request->has('site_description')) {
                $tenant->update(['site_description' => $request->site_description]);
            }
            if ($request->has('primary_email')) {
                $tenant->update(['primary_email' => $request->primary_email]);
            }
            if ($request->has('school_name')) {
                $tenant->update(['school_name' => $request->school_name]);
            }
            
            $tenant->update(['has_updated_settings' => true]);

            return back()->with('success', 'Settings updated successfully!');
        })->name('admin.settings.update');

        Route::post('/settings/system-version', function (\Illuminate\Http\Request $request) {
            $action = $request->input('action');
            if ($action === 'upgrade') {
                tenant()->update(['system_version' => 'v2.0']);
                return back()->with('success', 'System updated to Version 2.0 successfully!');
            } else if ($action === 'rollback') {
                tenant()->update(['system_version' => 'v1.0']);
                return back()->with('success', 'System safely rolled back to Version 1.0.');
            }
            return back();
        })->name('admin.settings.system_version');

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
            if (!tenant()->hasFeature('pre_built_templates')) abort(403, 'Upgrade your plan to access Templates.');
            return view('tenant_ui.admin.templates'); 
        })->name('admin.templates');
        
        Route::get('/notifications/read', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return back();
        })->name('notifications.read');
    });

    // Teacher Routes
    Route::prefix('teacher')->middleware(['auth'])->name('tenant.teacher.')->group(function () {
        Route::get('/dashboard', function () {
            $myAnnouncementsCount = \App\Models\Announcement::where('posted_by', auth()->id())->count();
            $recentAnnouncements = \App\Models\Announcement::with('postedBy')
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->take(3)
                ->get();

            return view('tenant_ui.teacher.dashboard', [
                'myAnnouncementsCount' => $myAnnouncementsCount,
                'recentAnnouncements' => $recentAnnouncements,
                'totalViews' => 0,
                'totalReactions' => 0,
            ]);
        })->name('dashboard');
        Route::get('/announcements', function () { return view('tenant_ui.teacher.announcements'); })->name('announcements');
        Route::get('/my-announcements', function () { return view('tenant_ui.teacher.my-announcements'); })->name('my-announcements');
    });

    // Student Routes
    Route::prefix('student')->middleware(['auth', 'student'])->name('tenant.student.')->group(function () {
        Route::get('/dashboard', function () {
            $announcements = \App\Models\Announcement::with(['postedBy', 'comments.user', 'reactions'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('pinned_at', 'desc')
                ->latest()
                ->get();
            return view('tenant_ui.students.studentpage', [
                'announcements' => $announcements
            ]);
        })->name('page');

        // Interaction routes
        Route::post('/announcements/{announcement}/comment', [App\Http\Controllers\AnnouncementInteractionController::class, 'storeComment'])->name('comment.store');
        Route::post('/announcements/{announcement}/react', [App\Http\Controllers\AnnouncementInteractionController::class, 'toggleReaction'])->name('react.toggle');
        
        // Student Profile Route
        Route::get('/profile', function () { 
            return view('tenant_ui.profile.edit', ['user' => auth()->user()]); 
        })->name('profile');
    });

    // ── Shared Announcement CRUD (Admin & Teacher) ──
    Route::middleware(['auth'])->name('tenant.')->group(function () {
        Route::post('/announcements', function () {
            return back()->with('success', 'Announcement posted!');
        })->name('announcements.store');

        Route::get('/announcements/{id}/edit', function ($id) {
            return view('tenant_ui.teacher.edit-announcement');
        })->name('announcements.edit');

        Route::put('/announcements/{id}', function ($id) {
            $role = auth()->user()->role;
            $redirect = $role === 'admin' ? 'tenant.admin.my-announcements' : 'tenant.teacher.my-announcements';
            return redirect()->route($redirect)->with('success', 'Announcement updated!');
        })->name('announcements.update');

        Route::delete('/announcements/{id}', function ($id) {
            return back()->with('success', 'Announcement deleted!');
        })->name('announcements.destroy');

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
    Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('tenant.login');
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('tenant.login.post');
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('tenant.logout');

    // Tenant Registration
    Route::get('/register', [App\Http\Controllers\AuthController::class, 'showTenantRegister'])->name('tenant.register');
    Route::post('/register', [App\Http\Controllers\AuthController::class, 'tenantRegister']);
});
