@extends('tenant_ui.layouts.auth-layout')

@section('title', (tenant('school_name') ?? 'School') . ' - Login')

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
                <h1>Welcome Back</h1>
                <p>Enter your credentials to access your portal.</p>
            </div>

            @if(session('status'))
                <div class="form-error" style="background: var(--teal-bg); color: var(--teal); padding: 10px 14px; border-radius: 10px; margin-bottom: 20px;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="/login" class="auth-form">
                @csrf
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           placeholder="name@school.edu"
                           required autofocus>
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" 
                           placeholder="••••••••"
                           required>
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="auth-actions">
                    <label class="form-remember">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    
                    <button type="submit" class="btn-auth">
                        Sign In
                    </button>
                </div>
            </form>

            <div style="margin-top: 28px; text-align: center; font-size: 13px;">
                <span style="color: var(--muted);">Don't have an account?</span>
                <a href="{{ route('tenant.register') }}" style="color: var(--teal); text-decoration: none; font-weight: 700; margin-left: 4px;">Register Now</a>
            </div>
        </div>
    </div>
</div>
@endsection
