@extends('tenant_ui.layouts.auth-layout')

@section('title', (tenant('school_name') ?? 'School') . ' - Register')

@section('content')
<div class="auth-wrapper">
    <div class="auth-container">
        <a href="/" class="auth-brand">
            <div class="auth-brand-icon">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                </svg>
            </div>
            <span class="auth-brand-name">EduBoard</span>
        </a>

        <div class="auth-card">
            <div class="auth-header">
                <h1>Create Account</h1>
                <p>Join {{ tenant('school_name') ?? 'your school' }}'s digital platform.</p>
            </div>

            <form method="POST" action="/register" class="auth-form">
                @csrf
                
                <div class="form-group">
                    <label>Registration Type</label>
                    <select name="role" class="form-select" required>
                        <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Faculty Member / Teacher</option>
                    </select>
                    @error('role')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" 
                           placeholder="Juan Dela Cruz"
                           required autofocus>
                    @error('name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           placeholder="juan@school.edu"
                           required>
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" 
                               placeholder="Min. 8 chars"
                               required>
                        @error('password')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Confirm</label>
                        <input type="password" name="password_confirmation" 
                               placeholder="Repeat password"
                               required>
                    </div>
                </div>
                
                <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">
                    By registering, you agree to our <a href="#" style="color: var(--teal); text-decoration: none; font-weight: 600;">Terms</a>.
                </div>
                
                <button type="submit" class="btn-auth" style="margin-top: 12px; width: 100%; justify-content: center;">
                    Register Now
                </button>
            </form>

            <div style="margin-top: 28px; text-align: center; font-size: 13px;">
                <span style="color: var(--muted);">Already have an account?</span>
                <a href="{{ route('tenant.login') }}" style="color: var(--teal); text-decoration: none; font-weight: 700; margin-left: 4px;">Sign In</a>
            </div>
        </div>
    </div>
</div>
@endsection
