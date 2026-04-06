<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Mail\PasswordResetCodeMailable;
use Carbon\Carbon;

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
            'g-recaptcha-response' => ['required'],
        ], [
            'g-recaptcha-response.required' => 'Please complete the ReCaptcha verification.',
        ]);

        // Verify ReCaptcha
        $response = Http::asForm()->withoutVerifying()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => '6Lf02KQsAAAAAEOsDhCTHw5KWGA1r2C_Dm1TiuDd',
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!$response->json('success')) {
            return back()->withErrors(['g-recaptcha-response' => 'ReCaptcha verification failed. Please try again.'])->withInput();
        }

        // Remove ReCaptcha from credentials before login attempt to avoid DB column error
        unset($credentials['g-recaptcha-response']);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Check if locked
            if ($user->locked_until && now()->lessThan($user->locked_until)) {
                $lockedUntil = \Carbon\Carbon::parse($user->locked_until)->format('M d, Y h:i A');
                Auth::logout();
                return back()->withErrors(['email' => "Your account is temporarily locked. Try again after {$lockedUntil}."])->withInput();
            }

            // Check for pending status in tenant context
            if (function_exists('tenant') && tenant() && $user->status === 'pending') {
                Auth::logout();
                return redirect()->route('tenant.auth.waiting-approval');
            }

            $request->session()->regenerate();

            // Handle tenant redirect
            if (function_exists('tenant') && tenant()) {
                $role = $user->role;
                
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

        // If local attempt fails and we are inside a tenant
        if (function_exists('tenant') && tenant()) {
            // Check central database for credentials match
            $centralMatched = tenancy()->central(function () use ($credentials) {
                $centralUser = \App\Models\User::where('email', $credentials['email'])->first();
                if ($centralUser && Hash::check($credentials['password'], $centralUser->password)) {
                    return $centralUser->password; // Return the central hash to sync locally
                }
                return false;
            });

            if ($centralMatched) {
                // Password matches Central! Update local tenant user and log in
                $localUser = \App\Models\User::where('email', $credentials['email'])->first();
                if ($localUser) {
                    // Check if locked
                    if ($localUser->locked_until && now()->lessThan($localUser->locked_until)) {
                        $lockedUntil = \Carbon\Carbon::parse($localUser->locked_until)->format('M d, Y h:i A');
                        return back()->withErrors(['email' => "Your account is temporarily locked. Try again after {$lockedUntil}."])->withInput();
                    }

                    $localUser->update(['password' => $centralMatched]);
                    Auth::login($localUser, $request->boolean('remember'));
                    
                    $request->session()->regenerate();
                    $role = Auth::user()->role;
                    if ($role === 'admin') return redirect('/admin/dashboard');
                    elseif ($role === 'teacher') return redirect('/teacher/dashboard');
                    elseif ($role === 'student') return redirect('/student/dashboard');
                    return redirect('/dashboard');
                }
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
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
                'status' => 'pending',
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

            return redirect()->route('tenant.auth.waiting-approval');
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

    public function showRegister()
    {
        // Registration Toggle Check
        $registrationEnabled = \App\Models\CentralSetting::get('registration_enabled', '1');
        if ($registrationEnabled !== '1') {
            return redirect()->route('home')->with('error', 'New tenant registration is currently disabled by the system administrator.');
        }

        if (function_exists('tenant') && tenant()) {
            return view('tenant_ui.auth.register');
        }
        return view('central.auth.register');
    }

    public function updateProfile(Request $request)
    {
        $userId = $request->input('id', auth()->id());
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'profile_photo' => 'nullable|image|max:2048',
            'employee_id' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'course' => 'nullable|string|max:100',
            'year_level' => 'nullable|string|max:100',
            'section' => 'nullable|string|max:100',
            'strand' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:10',
        ]);

        $oldEmail = $user->email;
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Handle Profile Photo Upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo = $path;
        }
        
        // Role-specific fields
        if ($user->role === 'student') {
            $user->course = $validated['course'] ?? $user->course;
            $user->year_level = $validated['year_level'] ?? $user->year_level;
            $user->section = $validated['section'] ?? $user->section;
            $user->strand = $validated['strand'] ?? $user->strand;
        } else {
            $user->employee_id = $validated['employee_id'] ?? $user->employee_id;
            $user->department = $validated['department'] ?? $user->department;
        }

        // Settings (Language)
        if (!empty($validated['language'])) {
            $settings = $user->settings ?? [];
            $settings['language'] = $validated['language'];
            $user->settings = $settings;
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'profile_photo_url' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null,
                'user' => $user
            ]);
        }

        // Sync back to central if tenant, or sync to tenant if central
        if (function_exists('tenant') && tenant()) {
            // We are in Tenant. Sync back to Central DB ONLY for Admins (School Owners)
            if ($user->role === 'admin') {
                // Ensure we are not already in central context to prevent potential recursion if called via tenancy()->run()
                if (config('database.default') !== 'central') {
                    tenancy()->central(function () use ($user, $oldEmail, $validated) {
                        $centralUser = \App\Models\User::where('email', $oldEmail)->first();
                        if ($centralUser) {
                            $syncData = [
                                'email' => $user->email,
                                'name' => $user->name,
                                'profile_photo' => $user->profile_photo,
                            ];
                            
                            // Only sync password if it was actually changed in the request
                            if (!empty($validated['password'])) {
                                $syncData['password'] = $user->password;
                            }

                            $centralUser->update($syncData);
                        }
                    });
                }
            }
        } else {
            // We are in Central. Sync to Tenant DB if user is owner.
            $tenant = \App\Models\Tenant::where('owner_id', $user->id)->first();
            if ($tenant) {
                $tenant->run(function () use ($user, $oldEmail, $validated) {
                    $tenantUser = \App\Models\User::where('email', $oldEmail)->first();
                    if ($tenantUser) {
                        $syncData = [
                            'email' => $user->email,
                            'name' => $user->name,
                            'profile_photo' => $user->profile_photo,
                        ];

                        // Only sync password if it was actually changed in the request
                        if (!empty($validated['password'])) {
                            $syncData['password'] = $user->password;
                        }

                        $tenantUser->update($syncData);
                    }
                });
            }
        }

        return back()->with('success', 'User updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $newPasswordHash = Hash::make($validated['password']);
        $request->user()->update([
            'password' => $newPasswordHash,
        ]);

        // Sync password
        if (function_exists('tenant') && tenant()) {
            // We are in Tenant. Sync back to Central DB ONLY for Admins (School Owners)
            if ($request->user()->role === 'admin') {
                if (config('database.default') !== 'central') {
                    $userEmail = $request->user()->email;
                    tenancy()->central(function () use ($userEmail, $newPasswordHash) {
                        $centralUser = \App\Models\User::where('email', $userEmail)->first();
                        if ($centralUser) {
                            $centralUser->update(['password' => $newPasswordHash]);
                        }
                    });
                }
            }
        } else {
            // We are in Central. Sync to Tenant DB if user is owner.
            $user = $request->user();
            $tenant = \App\Models\Tenant::where('owner_id', $user->id)->first();
            if ($tenant) {
                $tenant->run(function () use ($user, $newPasswordHash) {
                    $tenantUser = \App\Models\User::where('email', $user->email)->first();
                    if ($tenantUser) {
                        $tenantUser->update(['password' => $newPasswordHash]);
                    }
                });
            }
        }

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

    // --- Password Reset Methods ---

    public function showForgotPassword()
    {
        $viewPath = (function_exists('tenant') && tenant()) ? 'tenant_ui.auth.forgot-password' : 'central.auth.forgot-password';
        return view($viewPath);
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'g-recaptcha-response' => 'required'
        ], [
            'g-recaptcha-response.required' => 'Please complete the ReCaptcha verification.'
        ]);

        // Verify ReCaptcha
        $response = Http::asForm()->withoutVerifying()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => '6Lf02KQsAAAAAEOsDhCTHw5KWGA1r2C_Dm1TiuDd',
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!$response->json('success')) {
            return back()->withErrors(['g-recaptcha-response' => 'ReCaptcha verification failed. Please try again.'])->withInput();
        }

        $email = $request->email;
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Save to DB
        DB::table('password_reset_codes')->updateOrInsert(
            ['email' => $email],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(15),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // Send Email
        try {
            Mail::to($email)->send(new PasswordResetCodeMailable($code));
        } catch (\Exception $e) {
            \Log::error('Password reset email failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to send reset email. Please check your mail settings.');
        }

        session(['reset_email' => $email]);

        $nextRoute = (function_exists('tenant') && tenant()) ? 'tenant.password.verify' : 'password.verify';
        return redirect()->route($nextRoute);
    }

    public function showVerifyCode()
    {
        if (!session('reset_email')) {
            $reqRoute = (function_exists('tenant') && tenant()) ? 'tenant.password.request' : 'password.request';
            return redirect()->route($reqRoute);
        }
        $viewPath = (function_exists('tenant') && tenant()) ? 'tenant_ui.auth.verify-code' : 'central.auth.verify-code';
        return view($viewPath);
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required|array|size:6']);
        $code = implode('', $request->code);
        $email = session('reset_email');

        $record = DB::table('password_reset_codes')
            ->where('email', $email)
            ->where('code', $code)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$record) {
            return back()->withErrors(['code' => 'Invalid or expired verification code.']);
        }

        session(['code_verified' => true]);
        $nextRoute = (function_exists('tenant') && tenant()) ? 'tenant.password.reset' : 'password.reset';
        return redirect()->route($nextRoute);
    }

    public function showResetPassword()
    {
        if (!session('code_verified')) {
            $reqRoute = (function_exists('tenant') && tenant()) ? 'tenant.password.request' : 'password.request';
            return redirect()->route($reqRoute);
        }
        $viewPath = (function_exists('tenant') && tenant()) ? 'tenant_ui.auth.reset-password' : 'central.auth.reset-password';
        return view($viewPath);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = session('reset_email');
        if (!$email) {
            $reqRoute = (function_exists('tenant') && tenant()) ? 'tenant.password.request' : 'password.request';
            return redirect()->route($reqRoute);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $newPasswordHash = Hash::make($request->password);
            $user->update(['password' => $newPasswordHash]);

            // Sync password to central if tenant school owner
            if (function_exists('tenant') && tenant() && $user->role === 'admin') {
                tenancy()->central(function () use ($email, $newPasswordHash) {
                    $centralUser = User::where('email', $email)->first();
                    if ($centralUser) $centralUser->update(['password' => $newPasswordHash]);
                });
            } elseif (!function_exists('tenant') || !tenant()) {
                // If central, sync to tenant if owner
                $tenant = \App\Models\Tenant::where('owner_id', $user->id)->first();
                if ($tenant) {
                    $tenant->run(function () use ($email, $newPasswordHash) {
                        $tenantUser = User::where('email', $email)->first();
                        if ($tenantUser) $tenantUser->update(['password' => $newPasswordHash]);
                    });
                }
            }

            // Clear codes and session
            DB::table('password_reset_codes')->where('email', $email)->delete();
            session()->forget(['reset_email', 'code_verified']);

            $loginRoute = (function_exists('tenant') && tenant()) ? 'tenant.login' : 'login';
            return redirect()->route($loginRoute)->with('success', 'Password reset successfully. Please log in.');
        }

        $reqRoute = (function_exists('tenant') && tenant()) ? 'tenant.password.request' : 'password.request';
        return redirect()->route($reqRoute)->withErrors(['email' => 'User not found.']);
    }
}
