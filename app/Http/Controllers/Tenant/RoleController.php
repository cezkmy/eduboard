<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantRole;
use App\Models\TenantPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display the roles and permissions management page.
     */
    public function index()
    {
        // Strictly mapped to Admin page structure as requested
        $permissionsSchema = [
            'Dashboard' => [
                'view_admin_dashboard' => 'View Admin Dashboard (Stats & Recent Activity)',
                'view_recent_announcements' => 'View Recent Announcements Feed',
                'view_pending_approvals_stats' => 'View Pending Approvals Count',
                'view_engagement_overview' => 'View Total Engagement Stats'
            ],
            'Category' => [
                'manage_categories' => 'Manage Organization Categories (Colleges/Levels/Sections)',
                'use_category_presets' => 'Use Category Presets (SHS/JHS/College)',
                'create_categories' => 'Create New Category Entries',
                'delete_categories' => 'Delete Category Entries'
            ],
            'Users' => [
                'view_users_list' => 'View All Users List (Teachers/Students/Admins)',
                'approve_users' => 'Approve/Reject Pending User Registrations',
                'edit_users' => 'Edit User Information & Roles',
                'delete_users' => 'Delete/Archive User Accounts',
                'lock_users' => 'Lock/Unlock User Accounts',
                'restore_users' => 'Restore Archived Users'
            ],
            'Reports' => [
                'view_admin_reports' => 'Access System Reports & Analytics',
                'generate_pdf_reports' => 'Generate & Export PDF Reports',
                'filter_reports_by_date' => 'Filter Reports by Date/Period'
            ],
            'Settings' => [
                'manage_general_settings' => 'Manage General System Settings',
                'manage_branding' => 'Branding Settings (Logo/School Names)',
                'manage_appearance' => 'Appearance Settings (Theme Colors)',
                'manage_templates' => 'Manage Announcement Templates',
                'manage_danger_zone' => 'Access Critical/Danger Zone Actions'
            ],
            'Subscription' => [
                'view_subscription_plan' => 'View Current Subscription Plan',
                'manage_billing' => 'Manage Billing & Payments',
                'upgrade_plan' => 'Upgrade/Downgrade Subscription'
            ],
            'Profile' => [
                'view_profile' => 'View Own Profile',
                'update_profile' => 'Update Personal Info (Name/Email/Language)',
                'update_security' => 'Update Account Security (Password)',
                'update_profile_photo' => 'Change Profile Photo'
            ],
            'Teacher Portal' => [
                'access_teacher_dashboard' => 'View Teacher Dashboard & Stats',
                'manage_announcements' => 'Create & Manage Announcements',
                'view_my_announcements' => 'View My Announcements List',
                'edit_announcements' => 'Edit/Update Announcements'
            ],
            'Student Portal' => [
                'access_student_page' => 'View Student Announcement Feed',
                'filter_by_category' => 'Filter by Category',
                'filter_by_date' => 'Filter by Date Range',
                'interact_with_posts' => 'React & Comment on Posts'
            ]
        ];

        // Fetch custom permissions from database
        $customDbPermissions = TenantPermission::all();
        $customPermissionsList = [];
        if ($customDbPermissions->count() > 0) {
            foreach ($customDbPermissions as $cp) {
                $group = $cp->group ?: 'Custom Capabilities';
                if (!isset($permissionsSchema[$group])) {
                    $permissionsSchema[$group] = [];
                }
                $permissionsSchema[$group][$cp->code] = $cp->label;
                $customPermissionsList[$cp->code] = ['label' => $cp->label, 'group' => $group];
            }
        }

        // Base roles configuration
        $baseRoles = [
            'admin' => 'Administrator',
            'teacher' => 'Teacher',
            'student' => 'Student',
        ];

        // Ensure roles exist
        $existingRoleNames = TenantRole::whereIn('name', array_keys($baseRoles))->pluck('name')->toArray();
        foreach ($baseRoles as $name => $displayName) {
            if (!in_array($name, $existingRoleNames)) {
                $permissions = [];
                if ($name === 'admin') {
                    $permissions = array_keys(array_merge(...array_values($permissionsSchema)));
                } elseif ($name === 'teacher') {
                    $permissions = ['view_profile', 'update_profile', 'update_security', 'update_profile_photo', 'access_teacher_dashboard', 'manage_announcements', 'view_my_announcements', 'edit_announcements'];
                } elseif ($name === 'student') {
                    $permissions = ['view_profile', 'update_profile', 'update_security', 'update_profile_photo', 'access_student_page', 'filter_by_category', 'filter_by_date', 'interact_with_posts'];
                }

                TenantRole::create([
                    'name' => $name,
                    'display_name' => $displayName,
                    'permissions' => $permissions
                ]);
            }
        }

        // Strict mapping of groups to roles
        $roleGroupMapping = [
            'admin' => ['Dashboard', 'Category', 'Users', 'Reports', 'Settings', 'Subscription', 'Profile', 'Custom Capabilities'],
            'teacher' => ['Teacher Portal', 'Profile', 'Custom Capabilities'],
            'student' => ['Student Portal', 'Profile', 'Custom Capabilities']
        ];

        // Get existing tenant roles and sort them (Admin first, then Teacher, Student, then others)
        $roles = TenantRole::all()->sortBy(function($role) {
            $order = ['admin' => 1, 'teacher' => 2, 'student' => 3];
            return $order[strtolower($role->name)] ?? 99;
        });
        
        // Remove unnecessary user fetching from here to prevent timeout

        $tenantRoles = [];
        foreach ($roles as $role) {
            $roleName = strtolower($role->name);
            $tenantRoles[$roleName] = [
                'display_name' => $role->display_name ?? ucfirst($role->name),
                'permissions' => $role->permissions ?? [],
            ];
        }

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

        return view('tenant_ui.admin.roles', compact('permissionsSchema', 'tenantRoles', 'roleGroupMapping', 'customPermissionsList'));
    }

    /**
     * Update permissions for a specific user.
     */
    public function updateUserPermissions(Request $request, $userId)
    {
        $request->validate([
            'permissions' => 'nullable|array'
        ]);

        $user = \App\Models\User::findOrFail($userId);
        
        $user->update([
            'custom_permissions' => $request->permissions ?? []
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permissions for ' . $user->name . ' updated successfully!'
        ]);
    }

    /**
     * Store a new custom role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:tenant_roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array'
        ]);

        $role = TenantRole::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'permissions' => $request->permissions ?? []
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Custom role "' . $request->display_name . '" created successfully!'
        ]);
    }

    /**
     * Delete a custom role.
     */
    public function destroy($roleName)
    {
        // Prevent deletion of base roles
        if (in_array($roleName, ['admin', 'teacher', 'student'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete base system roles'
            ]);
        }

        $role = TenantRole::where('name', $roleName)->first();
        
        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ]);
        }

        // Check if any users have this role
        $usersWithRole = \App\Models\User::where('role', $roleName)->count();
        if ($usersWithRole > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role. ' . $usersWithRole . ' user(s) are assigned to this role.'
            ]);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role "' . ($role->display_name ?? ucfirst($role->name)) . '" deleted successfully!'
        ]);
    }

    /**
     * Update permissions for a specific role (base or custom).
     */
    public function updatePermissions(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string',
            'permissions' => 'nullable|array' // Array of active permission strings
        ]);

        $role = TenantRole::where('name', $request->role_name)->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ]);
        }

        $role->update([
            'permissions' => $request->permissions ?? []
        ]);

        return response()->json([
            'success' => true,
            'message' => ($role->display_name ?? ucfirst($role->name)) . ' permissions updated successfully!'
        ]);
    }

    /**
     * Store a new custom permission.
     */
    public function storeCustomPermission(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'group' => 'nullable|string|max:255'
        ]);

        $code = Str::slug($request->label, '_');
        $code = preg_replace('/[^a-zA-Z0-9_]/', '', $code);

        // Ensure unique code
        if (TenantPermission::where('code', $code)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'A permission with a similar name already exists.'
            ]);
        }

        $group = $request->group ?: 'Custom Capabilities';

        $permission = TenantPermission::create([
            'code' => $code,
            'label' => $request->label,
            'group' => $group
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Custom permission added successfully!',
            'permission' => $permission
        ]);
    }

    /**
     * Delete a custom permission.
     */
    public function deleteCustomPermission($code)
    {
        $permission = TenantPermission::where('code', $code)->first();

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found.'
            ]);
        }

        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Custom permission deleted successfully!'
        ]);
    }
}
