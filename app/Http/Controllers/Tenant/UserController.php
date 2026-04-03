<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Notifications\UserApprovedNotification;

class UserController extends Controller
{
    public function index()
    {
        $allUsers = User::query()
            ->latest()
            ->get();

        $admins = $allUsers->where('role', 'admin')->where('status', '!=', 'pending')->values();
        $teachers = $allUsers->where('role', 'teacher')->where('status', '!=', 'pending')->values();
        $students = $allUsers->where('role', 'student')->where('status', '!=', 'pending')->values();
        $pendingUsers = $allUsers->where('status', 'pending')->values();

        $adminCount = $admins->count();
        $teacherCount = $teachers->count();

        $schoolType = tenant('school_type') ?? 'college';
        
        // Fetch organizational structures for bulk actions
        $levels = Category::where('type', $schoolType === 'college' ? 'level' : 'grade_level')->get();
        $programs = Category::where('type', $schoolType === 'college' ? 'program' : 'strand')->get();
        $colleges = Category::where('type', 'college')->get();
        $sections = Category::where('type', 'section')->get();

        return view('tenant_ui.admin.users', compact(
            'adminCount',
            'teacherCount',
            'admins',
            'teachers',
            'students',
            'pendingUsers',
            'levels',
            'programs',
            'colleges',
            'sections',
            'schoolType'
        ));
    }

    public function bulkUpdate(Request $request)
    {
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

    public function approveUser(User $user)
    {
        $user->update(['status' => 'active']);
        
        // Notify the user via Email & Database
        try {
            $user->notify(new UserApprovedNotification(tenant('school_name')));
        } catch (\Exception $e) {
            \Log::error('Failed to send User Approved Notification: ' . $e->getMessage());
        }

        return back()->with('success', "User {$user->name} approved successfully. A notification has been sent.");
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|in:admin,teacher,student',
            'password' => 'required|string|min:8',
            'status' => 'required|string|in:active,inactive'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt($request->password),
            'status' => $request->status,
            'department' => $request->department,
            'employee_id' => $request->employee_id,
            'course' => $request->course,
            'year_level' => $request->year_level,
            'section' => $request->section,
        ]);

        return response()->json(['success' => true, 'message' => 'User created successfully!']);
    }

    public function update(Request $request, User $user)
    {
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

        $user->update($data);

        return response()->json(['success' => true, 'message' => 'User updated successfully!']);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted successfully!']);
    }
}
