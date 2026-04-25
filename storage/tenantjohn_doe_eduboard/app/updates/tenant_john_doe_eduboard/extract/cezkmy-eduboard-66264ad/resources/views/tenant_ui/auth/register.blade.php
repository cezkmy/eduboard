@extends('tenant_ui.layouts.auth-layout')

@section('title', (tenant('school_name') ?? 'School') . ' - Register')

@section('content')
    <div class="auth-wrapper"
        style="display: block !important; padding: 3rem 1rem 4rem !important; height: auto !important; min-height: 100vh;">
        <div class="auth-container" style="margin: 0 auto !important; max-width: 480px;">
            <div class="auth-card">
                <div class="auth-header" style="text-align: center; margin-bottom: 2.5rem;">
                    <a href="/"
                        style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 20px; background: rgba(var(--accent-rgb), 0.10); color: var(--accent); margin-bottom: 1.25rem; text-decoration: none; transition: transform 0.2s ease, background 0.2s ease;" onmouseover="this.style.transform='scale(1.05)'; this.style.background='rgba(var(--accent-rgb), 0.15)';" onmouseout="this.style.transform='scale(1)'; this.style.background='rgba(var(--accent-rgb), 0.10)';">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"
                            style="width: 36px; height: 36px;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                    </a>
                    <h1
                        style="font-family: 'Sora', sans-serif; font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin: 0; letter-spacing: -0.5px;">
                        {{ tenant('school_name') ?? 'EduPlatform' }}
                    </h1>
                    <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.5rem;">Join the official digital
                        Eduboard.
                    </p>
                </div>

                <form method="POST" action="/register" class="auth-form">
                    @csrf

                    <div class="form-group">
                        <label>Registration Type</label>
                        <select name="role" class="form-select" required>
                            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                            <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Faculty Member / Teacher
                            </option>
                        </select>
                        @error('role')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Juan Dela Cruz" required
                            autofocus>
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="juan@school.edu" required>
                        @error('email')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Min. 8 chars" required>
                            @error('password')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Confirm</label>
                            <input type="password" name="password_confirmation" placeholder="Repeat password" required>
                        </div>
                    </div>

                    <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">
                        By registering, you agree to our <a href="#"
                            style="color: var(--teal); text-decoration: none; font-weight: 600;">Terms</a>.
                    </div>

                    <button type="submit" class="btn-auth" style="margin-top: 12px; width: 100%; justify-content: center;">
                        Register Now
                    </button>
                </form>

                <div style="margin-top: 28px; text-align: center; font-size: 13px;">
                    <span style="color: var(--muted);">Already have an account?</span>
                    <a href="{{ route('tenant.login') }}"
                        style="color: var(--teal); text-decoration: none; font-weight: 700; margin-left: 4px;">Sign In</a>
                </div>
            </div>
        </div>
    </div>
@endsection