@extends('tenant_ui.layouts.auth-layout')

@section('title', (tenant('school_name') ?? 'School') . ' - Register Admin')

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
            <div class="auth-role-badge admin">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.744c0 5.052 3.13 9.373 7.544 11.121a11.959 11.959 0 007.544-11.121c0-1.312-.21-2.574-.598-3.744A11.959 11.959 0 0112 2.714z" />
                </svg>
                Administrator
            </div>

            <div class="auth-header">
                <h1>Admin Portal</h1>
                <p>Register a new administrator for {{ tenant('school_name') ?? 'your school' }}.</p>
            </div>

            <form method="POST" action="/register" class="auth-form">
                @csrf
                <input type="hidden" name="role" value="admin">
                
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
                    <label>School Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           placeholder="admin@school.edu"
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
                
                <button type="submit" class="btn-auth" style="margin-top: 12px; width: 100%; justify-content: center;">
                    Register Admin
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 18px; height: 18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
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

    </div>
</div>

<style>
    input:focus {
        border-color: #2D7A6E !important;
        box-shadow: 0 0 0 4px rgba(45, 122, 110, 0.05);
    }
    @media (max-width: 768px) {
        .auth-card {
            grid-template-columns: 1fr !important;
            max-width: 450px !important;
        }
        .auth-card > div:first-child {
            display: none !important;
        }
    }
</style>
@endsection
