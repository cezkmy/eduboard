<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Notifications\UserApprovedNotification;
use App\Notifications\UserCredentialsNotification;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('view_users_list')) {
            abort(403, 'Unauthorized.');
        }

        $activeTab = $request->query('tab', 'teachers');
        $searchQuery = $request->query('search');
        $deptFilter = $request->query('dept');

        $query = User::withTrashed()->latest();

        // Apply filters if present
        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('email', 'like', "%{$searchQuery}%");
            });
        }

        if ($deptFilter && $deptFilter !== 'all') {
            $query->where(function($q) use ($deptFilter) {
                $q->where('department', $deptFilter)
                  ->orWhere('course', $deptFilter);
            });
        }

        // Get counts for each role efficiently
        // Get counts for each role efficiently, excluding deleted and pending for accurate tabs
        $roleCounts = User::whereNull('deleted_at')
            ->where('status', '!=', 'pending')
            ->selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        $pendingCount  = User::whereNull('deleted_at')->where('status', 'pending')->count();
        $archivedCount = User::onlyTrashed()->count();

        // Fetch users for the active tab with pagination
        if ($activeTab === 'pending') {
            $users = User::whereNull('deleted_at')->where('status', 'pending')->latest()->paginate(20)->withQueryString();
        } elseif ($activeTab === 'archived') {
            $users = User::onlyTrashed()->latest()->paginate(20)->withQueryString();
        } else {
            $role  = rtrim($activeTab, 's');
            $users = User::withTrashed()
                ->where('role', $role)
                ->whereNull('deleted_at')
                ->where('status', '!=', 'pending')
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        // Build permissionsSchema identical to RoleController so both pages are in sync
        $permissionsSchema = [
            'Dashboard'      => [
                'view_admin_dashboard'        => 'View Admin Dashboard',
                'view_recent_announcements'   => 'View Recent Announcements Feed',
                'view_pending_approvals_stats'=> 'View Pending Approvals Count',
                'view_engagement_overview'    => 'View Total Engagement Stats',
            ],
            'Category'       => [
                'manage_categories'    => 'Manage Organization Categories',
                'use_category_presets' => 'Use Category Presets',
                'create_categories'    => 'Create New Categories',
                'delete_categories'    => 'Delete Categories',
            ],
            'Users'          => [
                'view_users_list' => 'View All Users List',
                'approve_users'   => 'Approve/Reject Pending Users',
                'edit_users'      => 'Edit User Information',
                'delete_users'    => 'Delete/Archive Users',
                'lock_users'      => 'Lock/Unlock User Accounts',
                'restore_users'   => 'Restore Archived Users',
            ],
            'Reports'        => [
                'view_admin_reports'     => 'Access System Reports',
                'generate_pdf_reports'   => 'Generate & Export PDF Reports',
                'filter_reports_by_date' => 'Filter Reports by Date',
            ],
            'Settings'       => [
                'manage_general_settings' => 'Manage General System Settings',
                'manage_branding'         => 'Branding Settings',
                'manage_appearance'       => 'Appearance Settings',
                'manage_templates'        => 'Manage Templates',
                'manage_danger_zone'      => 'Access Critical/Danger Zone Actions',
            ],
            'Subscription'   => [
                'view_subscription_plan' => 'View Subscription Plan',
                'manage_billing'         => 'Manage Billing & Payments',
                'upgrade_plan'           => 'Upgrade/Downgrade Subscription',
            ],
            'Profile'        => [
                'view_profile'        => 'View Own Profile',
                'update_profile'      => 'Update Personal Info',
                'update_security'     => 'Update Account Security',
                'update_profile_photo'=> 'Change Profile Photo',
            ],
            'Teacher Portal' => [
                'access_teacher_dashboard' => 'View Teacher Dashboard',
                'manage_announcements'     => 'Create & Manage Announcements',
                'view_my_announcements'    => 'View My Announcements',
                'edit_announcements'       => 'Edit Announcements',
            ],
            'Student Portal' => [
                'access_student_page'  => 'View Student Feed',
                'filter_by_category'   => 'Filter by Category',
                'filter_by_date'       => 'Filter by Date',
                'interact_with_posts'  => 'React & Comment on Posts',
            ],
        ];

        // Merge any custom permissions saved in the database
        foreach (\App\Models\TenantPermission::all() as $cp) {
            $group = $cp->group ?: 'Custom Capabilities';
            if (!isset($permissionsSchema[$group])) $permissionsSchema[$group] = [];
            $permissionsSchema[$group][$cp->code] = $cp->label;
        }

        // Strict mapping of groups to roles
        $roleGroupMapping = [
            'admin' => ['Dashboard', 'Category', 'Users', 'Reports', 'Settings', 'Subscription', 'Profile', 'Custom Capabilities'],
            'teacher' => ['Teacher Portal', 'Profile', 'Custom Capabilities'],
            'student' => ['Student Portal', 'Profile', 'Custom Capabilities']
        ];
        
        // Only dynamically append CUSTOM groups (not base system ones) to roles that don't have them
        $baseGroups = ['Dashboard', 'Category', 'Users', 'Reports', 'Settings', 'Subscription', 'Profile', 'Teacher Portal', 'Student Portal'];
        foreach ($permissionsSchema as $groupName => $perms) {
            if (!in_array($groupName, $baseGroups)) {
                foreach ($roleGroupMapping as $role => &$mapping) {
                    if (!in_array($groupName, $mapping)) {
                        $mapping[] = $groupName;
                    }
                }
            }
        }

        // tenantRoles as plain array: ['admin' => ['permissions' => [...]], ...]
        $tenantRoles = \App\Models\TenantRole::all()->keyBy('name')->map(fn($r) => [
            'permissions' => $r->permissions ?? [],
        ])->toArray();

        $schoolType = tenant('school_type') ?? 'college';
        $levels = Category::where('type', $schoolType === 'college' ? 'level' : 'grade_level')->get();
        $programs = Category::where('type', $schoolType === 'college' ? 'program' : 'strand')->get();
        $colleges = Category::where('type', 'college')->get();
        $sections = Category::where('type', 'section')->get();

        return view('tenant_ui.admin.users', compact(
            'users',
            'roleCounts',
            'pendingCount',
            'archivedCount',
            'activeTab',
            'levels',
            'programs',
            'colleges',
            'sections',
            'schoolType',
            'permissionsSchema',
            'tenantRoles',
            'roleGroupMapping'
        ));
    }

    public function bulkUpdate(Request $request)
    {
        if (!auth()->user()->hasPermission('edit_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to edit users.'], 403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'field' => 'required|string|in:course,department,year_level,section',
            'value' => 'required|string'
        ]);

        User::whereIn('id', $request->user_ids)->update([
            $request->field => $request->value
        ]);

        return response()->json(['success' => true, 'message' => 'Bulk update successful!']);
    }

    public function bulkUpdatePermissions(Request $request)
    {
        if (!auth()->user()->hasPermission('edit_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to modify user permissions.'], 403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'permissions' => 'required|array'
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();

        foreach ($users as $user) {
            $custom = $user->custom_permissions ?? ['granted' => [], 'denied' => []];
            
            // Ensure granted and denied are arrays
            $granted = $custom['granted'] ?? [];
            $denied = $custom['denied'] ?? [];

            foreach ($request->permissions as $perm) {
                // If not already explicitly granted, add it
                if (!in_array($perm, $granted)) {
                    $granted[] = $perm;
                }
                
                // If it was previously explicitly denied, remove it from denied
                if (in_array($perm, $denied)) {
                    $denied = array_values(array_diff($denied, [$perm]));
                }
            }

            $custom['granted'] = $granted;
            $custom['denied'] = $denied;
            
            $user->custom_permissions = $custom;
            $user->save();
        }

        return response()->json(['success' => true, 'message' => 'Permissions granted successfully to selected users!']);
    }

    public function approveUser(User $user)
    {
        if (!auth()->user()->hasPermission('approve_users')) {
            abort(403, 'Unauthorized.');
        }

        $user->update(['status' => 'active']);
        
        // Notify the user via Email & Database
        try {
            $user->notify(new UserApprovedNotification(tenant('school_name')));
        } catch (\Exception $e) {
            \Log::error('Failed to send User Approved Notification: ' . $e->getMessage());
        }

        return back()->with('success', "User {$user->name} approved successfully. A notification has been sent.");
    }

    public function rejectUser(User $user)
    {
        $user->forceDelete();
        return back()->with('success', "Pending user {$user->name} has been rejected and permanently removed.");
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('edit_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to create users.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|in:admin,teacher,student',
            'status' => 'required|string|in:active,inactive'
        ]);

        // Automatically generate a secure password if not provided
        $plainPassword = $request->password ?: \Illuminate\Support\Str::random(10);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt($plainPassword),
            'status' => $request->status,
            'department' => $request->department,
            'employee_id' => $request->employee_id,
            'course' => $request->course,
            'year_level' => $request->year_level,
            'section' => $request->section,
            'custom_permissions' => $request->custom_permissions ?? null,
        ]);

        // Notify the user with their credentials
        try {
            $user->notify(new UserCredentialsNotification(
                $request->name, 
                $request->email, 
                $plainPassword, 
                tenant('school_name')
            ));
        } catch (\Exception $e) {
            \Log::error('Failed to send User Credentials Notification: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'User created successfully! Login details sent to Gmail.', 'password' => $plainPassword ?? null]);
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->user()->hasPermission('edit_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to edit users.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string|in:admin,teacher,student',
            'status' => 'required|string|in:active,inactive'
        ]);

        $data = $request->only(['name', 'email', 'role', 'status', 'department', 'employee_id', 'course', 'year_level', 'section']);
        
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->has('custom_permissions')) {
            $data['custom_permissions'] = $request->custom_permissions;
        }

        $user->update($data);

        return response()->json(['success' => true, 'message' => 'User updated successfully!']);
    }

    public function destroy(User $user)
    {
        if (!auth()->user()->hasPermission('delete_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to delete users.'], 403);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted (archived) successfully!']);
    }

    public function restore($id)
    {
        if (!auth()->user()->hasPermission('restore_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to restore users.'], 403);
        }

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        return response()->json(['success' => true, 'message' => 'User restored successfully!']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->hasPermission('delete_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to delete users.'], 403);
        }

        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();
        return response()->json(['success' => true, 'message' => 'User permanently deleted!']);
    }

    public function lockAccount(Request $request, User $user)
    {
        if (!auth()->user()->hasPermission('lock_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to lock/unlock user accounts.'], 403);
        }

        $request->validate([
            'days' => 'required|integer|min:0'
        ]);

        if ($request->days == 0) {
            $user->update(['locked_until' => null]);
            return response()->json(['success' => true, 'message' => 'User account unlocked successfully!']);
        } elseif ($request->days == 9999) {
            // Treat 9999 as permanent
            $user->update(['locked_until' => now()->addYears(100)]);
            return response()->json(['success' => true, 'message' => 'User account permanently locked!']);
        }

        $user->update(['locked_until' => now()->addDays($request->days)]);
        return response()->json(['success' => true, 'message' => "User account locked for {$request->days} days!"]);
    }

    public function editLock(User $user)
    {
        $lockKey = "edit_lock_user_{$user->id}";
        $currentLock = \Illuminate\Support\Facades\Cache::get($lockKey);
        
        if ($currentLock && $currentLock['admin_id'] !== auth()->id()) {
            return response()->json([
                'locked' => true, 
                'success' => false,
                'message' => "This user is currently being edited by {$currentLock['admin_name']}.",
                'by' => $currentLock['admin_name']
            ], 423);
        }

        // Lock for 15 minutes, enough time for an edit session
        \Illuminate\Support\Facades\Cache::put($lockKey, [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name
        ], now()->addMinutes(15));

        return response()->json(['success' => true, 'locked' => false]);
    }

    public function editUnlock(User $user)
    {
        $lockKey = "edit_lock_user_{$user->id}";
        $currentLock = \Illuminate\Support\Facades\Cache::get($lockKey);
        
        if ($currentLock && $currentLock['admin_id'] === auth()->id()) {
            \Illuminate\Support\Facades\Cache::forget($lockKey);
        }

        return response()->json(['success' => true]);
    }
}
