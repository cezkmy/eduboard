<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function autologin(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('tenant.login')->with('error', 'Invalid login token.');
        }

        $user = User::where('autologin_token', $token)
                    ->where('autologin_token_expires_at', '>', now())
                    ->first();

        if (!$user) {
            return redirect()->route('tenant.login')->with('error', 'Login token expired or invalid.');
        }

        // Clear token
        $user->update([
            'autologin_token' => null,
            'autologin_token_expires_at' => null,
        ]);

        // Login user
        Auth::login($user);
        $request->session()->regenerate();

        // Redirect based on role
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        } elseif ($user->role === 'teacher') {
            return redirect('/teacher/dashboard');
        } elseif ($user->role === 'student') {
            return redirect('/student/dashboard');
        }

        return redirect('/dashboard');
    }

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

            // Notify Tenant Admin via Email
            try {
                $tenantAdmin = User::where('role', 'admin')->orWhere('is_admin', true)->first();
                if ($tenantAdmin) {
                    $tenantAdmin->notify(new \App\Notifications\TenantNewUserNotification($user->name, $user->role, $user->email));
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send Tenant New User Notification: ' . $e->getMessage());
            }

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
                'role' => 'user',
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
        $userId = $request->input('id', auth()->id());
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        $oldEmail = $user->email;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();

        // Sync to tenant if email or other details changed
        $tenant = \App\Models\Tenant::where('owner_id', $user->id)->first();
        if ($tenant) {
            $tenant->run(function () use ($user, $oldEmail) {
                $tenantUser = \App\Models\User::where('email', $oldEmail)->first();
                if ($tenantUser) {
                    $tenantUser->update([
                        'email' => $user->email,
                        'name' => $user->name,
                        'password' => $user->password,
                    ]);
                }
            });
        }

        return back()->with('success', 'User updated successfully!');
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
