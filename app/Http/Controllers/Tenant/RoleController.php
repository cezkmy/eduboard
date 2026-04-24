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
        if (!auth()->user()->hasPermission('page_admin_roles')) {
            abort(403, 'Unauthorized access to roles management.');
        }
        // Strictly mapped to Admin page structure as requested
        // Strictly Page-Based Permissions Schema
        $permissionsSchema = [
            'Admin Pages' => [
                'page_admin_dashboard'       => 'Dashboard & Analytics Page',
                'page_admin_announcements'   => 'All Announcements Feed',
                'page_admin_categories'      => 'Organization Categories Page',
                'page_admin_my_announcements'=> 'My Announcements Manager',
                'page_admin_users'           => 'User Management Directory',
                'page_admin_roles'           => 'Roles & Permissions Page',
                'page_admin_reports'         => 'System Reports Page',
                'page_admin_reports_pdf'     => 'PDF Export Reports',
                'page_admin_settings'        => 'System Settings & Branding',
                'page_admin_subscription'    => 'Subscription & Billing Page',
                'page_admin_templates'       => 'Announcement Templates',
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

        // Merge any custom permissions saved in the database
        $customPermissionsList = [];
        foreach (TenantPermission::all() as $cp) {
            $group = $cp->group ?: 'Custom Capabilities';
            if (!isset($permissionsSchema[$group])) $permissionsSchema[$group] = [];
            $permissionsSchema[$group][$cp->code] = $cp->label;
            $customPermissionsList[$cp->code] = ['label' => $cp->label, 'group' => $group];
        }

        // Base roles configuration
        $baseRoles = [
            'admin' => 'Administrator',
            'teacher' => 'Teacher',
            'student' => 'Student',
        ];

        // Ensure roles exist and have defaults if empty
        $roles = TenantRole::all();
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

                TenantRole::create([
                    'name' => $name,
                    'display_name' => $displayName,
                    'permissions' => $permissions
                ]);
            } else {
                // Proactively fix empty permissions for base roles
                $role = $roles->where('name', $name)->first();
                if (empty($role->permissions)) {
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

        // Strict mapping of groups to roles for UI organization
        $roleGroupMapping = [
            'admin'   => ['Admin Pages', 'Teacher Pages', 'Student Pages', 'General Access'],
            'teacher' => ['Teacher Pages', 'General Access'],
            'student' => ['Student Pages', 'General Access']
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
