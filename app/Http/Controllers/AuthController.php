<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            if (function_exists('tenant') && tenant()) {
                return redirect('/dashboard');
            }
            return redirect()->route('dashboard');
        }

        if (function_exists('tenant') && tenant()) {
            return view('tenant_ui.auth.login');
        }
        return view('central.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Handle tenant redirect
            if (function_exists('tenant') && tenant()) {
                $role = Auth::user()->role;
                
                // Use relative paths to stay on the same tenant domain
                if ($role === 'admin') {
                    return redirect('/admin/dashboard');
                } elseif ($role === 'teacher') {
                    return redirect('/teacher/dashboard');
                } elseif ($role === 'student') {
                    return redirect('/student/dashboard');
                }
                
                return redirect('/dashboard');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('central.auth.register');
    }

    public function showTenantRegister()
    {
        return view('tenant_ui.auth.register');
    }

    public function tenantRegister(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'string', 'in:admin,teacher,student'],
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'student',
                'school_name' => tenant('school_name'),
                'status' => 'active',
            ]);

            Auth::login($user);

            // Role-based redirection for tenant registration
            $role = $user->role;
            if ($role === 'admin') {
                return redirect('/admin/dashboard');
            } elseif ($role === 'teacher') {
                return redirect('/teacher/dashboard');
            } elseif ($role === 'student') {
                return redirect('/student/dashboard');
            }

            return redirect('/dashboard');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Error creating account: ' . $e->getMessage()])->withInput();
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'school_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'school_name' => $request->school_name,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(30),
                'plan' => 'Basic',
            ]);

            Auth::login($user);

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Error creating account: ' . $e->getMessage()])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return function_exists('tenant') && tenant()
            ? redirect()->route('tenant.landing')
            : redirect()->route('home');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];

        // Handle student fields for tenants
        if ($user->role === 'student') {
            $rules['course'] = ['nullable', 'string', 'max:255'];
            $rules['year_level'] = ['nullable', 'string', 'max:255'];
            $rules['section'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $data = [];
        if ($request->has('name')) $data['name'] = $validated['name'];
        if ($request->has('email')) $data['email'] = $validated['email'];

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo && \Storage::disk('public')->exists($user->profile_photo)) {
                \Storage::disk('public')->delete($user->profile_photo);
            }
            
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $data['profile_photo'] = $path;
        }

        if ($user->role === 'student') {
            if ($request->has('course')) $data['course'] = $validated['course'] ?? null;
            if ($request->has('year_level')) $data['year_level'] = $validated['year_level'] ?? null;
            if ($request->has('section')) $data['section'] = $validated['section'] ?? null;
        }

        if ($request->filled('new_password')) {
            $data['password'] = Hash::make($request->new_password);
        }

        $user->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => $user,
                'profile_photo_url' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null
            ]);
        }

        return back()->with('success', 'Profile updated successfully!')
                     ->with('status', 'profile-updated');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }

    public function destroyUser(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/');
    }
}
