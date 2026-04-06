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
        $allUsers = User::withTrashed()
            ->latest()
            ->get();

        $admins = $allUsers->whereNull('deleted_at')->where('role', 'admin')->where('status', '!=', 'pending')->values();
        $teachers = $allUsers->whereNull('deleted_at')->where('role', 'teacher')->where('status', '!=', 'pending')->values();
        $students = $allUsers->whereNull('deleted_at')->where('role', 'student')->where('status', '!=', 'pending')->values();
        $pendingUsers = $allUsers->whereNull('deleted_at')->where('status', 'pending')->values();
        $archivedUsers = $allUsers->whereNotNull('deleted_at')->values();

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
            'archivedUsers',
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

    public function rejectUser(User $user)
    {
        $user->forceDelete();
        return back()->with('success', "Pending user {$user->name} has been rejected and permanently removed.");
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
        return response()->json(['success' => true, 'message' => 'User deleted (archived) successfully!']);
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        return response()->json(['success' => true, 'message' => 'User restored successfully!']);
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();
        return response()->json(['success' => true, 'message' => 'User permanently deleted!']);
    }

    public function lockAccount(Request $request, User $user)
    {
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
