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
        if (!auth()->user()->hasPermission('page_admin_users')) {
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
        $lockedCount   = User::whereNull('deleted_at')->whereNotNull('locked_until')->where('locked_until', '>', now())->count();
        $archivedCount = User::onlyTrashed()->count();

        // Fetch users for the active tab with pagination
        if ($activeTab === 'pending') {
            $users = User::whereNull('deleted_at')->where('status', 'pending')->latest()->paginate(20)->withQueryString();
        } elseif ($activeTab === 'locked') {
            $users = User::whereNull('deleted_at')->whereNotNull('locked_until')->where('locked_until', '>', now())->latest()->paginate(20)->withQueryString();
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

        // Strictly Page-Based Permissions Schema
        $permissionsSchema = [
            'Admin Pages' => [
                'page_admin_dashboard'          => 'Dashboard & Analytics Page',
                'page_admin_announcements'      => 'All Announcements Feed',
                'page_admin_categories'         => 'Organization Categories Page',
                'page_admin_my_announcements'   => 'My Announcements Manager',
                'page_admin_edit_announcement'  => 'Edit Announcement Page',
                'page_admin_users'              => 'User Management Directory',
                'page_admin_roles'              => 'Roles & Permissions Page',
                'page_admin_reports'            => 'System Reports Page',
                'page_admin_reports_pdf'        => 'PDF Export Reports',
                'page_admin_settings'           => 'System Settings & Branding',
                'page_admin_subscription'       => 'Subscription & Billing Page',
                'page_admin_templates'          => 'Announcement Templates',
            ],
            'Teacher Pages' => [
                'page_teacher_dashboard'     => 'Teacher Dashboard',
                'page_teacher_announcements' => 'Teacher Feed Page',
                'page_teacher_my_announcements' => 'My Announcements Manager',
                'page_teacher_edit_announcement' => 'Edit Announcement Page',
            ],
            'Student Pages' => [
                'page_student_studentpage'   => 'Student Portal Feed',
            ],
            'General Access' => [
                'page_profile'               => 'User Profile & Security Settings',
            ]
        ];

        // Strict mapping of groups to roles for UI organization
        $roleGroupMapping = [
            'admin'   => ['Admin Pages', 'Teacher Pages', 'Student Pages', 'General Access'],
            'teacher' => ['Teacher Pages', 'General Access'],
            'student' => ['Student Pages', 'General Access']
        ];
        
        // Only dynamically append CUSTOM groups (not base system ones) to roles that don't have them
        $baseGroups = ['Admin Pages', 'Teacher Pages', 'Student Pages', 'General Access'];
        foreach ($permissionsSchema as $groupName => $perms) {
            if (!in_array($groupName, $baseGroups)) {
                foreach ($roleGroupMapping as $role => &$mapping) {
                    if (!in_array($groupName, $mapping)) {
                        $mapping[] = $groupName;
                    }
                }
            }
        }

        // Ensure base roles exist and have default permissions
        $baseRoles = [
            'admin' => 'Administrator',
            'teacher' => 'Teacher',
            'student' => 'Student',
        ];

        $roles = \App\Models\TenantRole::all();
        $existingRoleNames = $roles->pluck('name')->toArray();

        foreach ($baseRoles as $name => $displayName) {
            if (!in_array($name, $existingRoleNames)) {
                $permissions = [];
                if ($name === 'admin') {
                    $permissions = array_keys(array_merge(...array_values($permissionsSchema)));
                } elseif ($name === 'teacher') {
                    $permissions = ['page_teacher_dashboard', 'page_teacher_announcements', 'page_teacher_my_announcements', 'page_teacher_edit_announcement', 'page_profile'];
                } elseif ($name === 'student') {
                    $permissions = ['page_student_studentpage', 'page_profile'];
                }

                \App\Models\TenantRole::create([
                    'name' => $name,
                    'display_name' => $displayName,
                    'permissions' => $permissions
                ]);
            } else {
                // Proactively fix empty or incomplete permissions for existing base roles
                $role = $roles->where('name', $name)->first();
                $currentPerms = $role->permissions ?? [];
                
                $needsUpdate = false;
                if (empty($currentPerms)) {
                    $needsUpdate = true;
                }
                
                if ($needsUpdate) {
                    $permissions = [];
                    if ($name === 'admin') {
                        $permissions = array_keys(array_merge(...array_values($permissionsSchema)));
                    } elseif ($name === 'teacher') {
                        $permissions = ['page_teacher_dashboard', 'page_teacher_announcements', 'page_teacher_my_announcements', 'page_teacher_edit_announcement', 'page_profile'];
                    } elseif ($name === 'student') {
                        $permissions = ['page_student_studentpage', 'page_profile'];
                    }
                    
                    if (!empty($permissions)) {
                        $role->update(['permissions' => $permissions]);
                    }
                }
            }
        }

        // Re-fetch roles to get the updated data
        $roles = \App\Models\TenantRole::all();

        $tenantRoles = $roles->keyBy('name')->map(fn($r) => [
            'permissions' => $r->permissions ?? [],
        ])->toArray();

        $schoolType = tenant('school_type') ?? 'college';
        
        // Fetch all categories for flexible modal
        $yearLevels = Category::where('type', 'level')->get();
        $gradeLevels = Category::where('type', 'grade_level')->get();
        $programs = Category::where('type', 'program')->get();
        $strands = Category::where('type', 'strand')->get();
        $colleges = Category::where('type', 'college')->get();
        $sections = Category::where('type', 'section')->get();

        return view('tenant_ui.admin.users', compact(
            'users',
            'roleCounts',
            'pendingCount',
            'lockedCount',
            'archivedCount',
            'activeTab',
            'yearLevels',
            'gradeLevels',
            'programs',
            'strands',
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
        if (!auth()->user()->hasPermission('page_admin_users')) {
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
        if (!auth()->user()->hasPermission('page_admin_users')) {
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
        if (!auth()->user()->hasPermission('page_admin_users')) {
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
        if (!auth()->user()->hasPermission('page_admin_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to create users.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|exists:tenant_roles,name',
            'status' => 'required|string|in:active,inactive'
        ]);

        if ($request->role === 'admin') {
            $limit = tenant()->getLimit('admins');
            $currentCount = User::where('role', 'admin')->whereNull('deleted_at')->count();
            if ($limit !== -1 && $currentCount >= $limit) {
                return response()->json(['error' => "Plan limit reached: You can only have up to {$limit} admins. Please upgrade your plan."], 403);
            }
        }

        if ($request->role === 'teacher') {
            $limit = tenant()->getLimit('teachers');
            $currentCount = User::where('role', 'teacher')->whereNull('deleted_at')->count();
            if ($limit !== -1 && $currentCount >= $limit) {
                return response()->json(['error' => "Plan limit reached: You can only have up to {$limit} teachers. Please upgrade your plan."], 403);
            }
        }

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
        if (!auth()->user()->hasPermission('page_admin_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to edit users.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string|exists:tenant_roles,name',
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
        if (!auth()->user()->hasPermission('page_admin_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to delete users.'], 403);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted (archived) successfully!']);
    }

    public function restore($id)
    {
        if (!auth()->user()->hasPermission('page_admin_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to restore users.'], 403);
        }

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        
        // When restoring, also clear any temporary locks to ensure they can log in
        $user->update(['locked_until' => null]);
        
        return response()->json(['success' => true, 'message' => 'User restored and account unlocked successfully!']);
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->hasPermission('page_admin_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to delete users.'], 403);
        }

        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();
        return response()->json(['success' => true, 'message' => 'User permanently deleted!']);
    }

    public function lockAccount(Request $request, User $user)
    {
        if (!auth()->user()->hasPermission('page_admin_users')) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to lock accounts.'], 403);
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
