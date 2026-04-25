@extends('tenant_ui.layouts.auth-layout')

@section('title', (tenant('school_name') ?? 'School') . ' - Forgot Password')

@section('content')
<div class="auth-wrapper" style="display: block !important; padding: 4rem 1rem !important; height: auto !important; min-height: 100vh;">
    <div class="auth-container" style="margin: 0 auto !important; max-width: 480px;">
        <div class="auth-card">
            <div class="auth-header" style="text-align: center; margin-bottom: 2.5rem;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 20px; background: rgba(var(--accent-rgb), 0.10); color: var(--accent); margin-bottom: 1.25rem;">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 36px; height: 36px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                    </svg>
                </div>
                <h1 style="font-family: 'Sora', sans-serif; font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin: 0; letter-spacing: -0.5px;">Reset Password</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.5rem;">Enter your email to receive a 6-digit verification code.</p>
            </div>

            @if(session('status'))
                <div class="alert alert-success" style="background: rgba(var(--accent-rgb), 0.10); color: var(--accent); padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 20px; border: 1px solid rgba(var(--accent-rgb), 0.10);">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" style="background: #fef2f2; color: #dc2626; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 20px; border: 1px solid #fee2e2;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route((function_exists('tenant') && tenant()) ? 'tenant.password.email' : 'password.email') }}" class="auth-form">
                @csrf
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; text-transform: uppercase;">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           placeholder="your@email.com"
                           required autofocus
                           style="width: 100%; padding: 14px 16px; border-radius: 14px; border: 2px solid #f3f4f6; background: #f9fafb; outline: none; transition: all 0.2s;"
                           onfocus="this.style.borderColor='var(--accent)'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(var(--accent-rgb), 0.1)'"
                           onblur="this.style.borderColor='#f3f4f6'; this.style.background='#f9fafb'; this.style.boxShadow='none'">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <div class="g-recaptcha" data-sitekey="6Lf02KQsAAAAAKnEOnptwcS0Bdu_ThNf-u4wAntd"></div>
                </div>

                <button type="submit" class="btn-auth" style="width: 100%; padding: 16px; background: var(--accent); color: white; border: none; border-radius: 16px; font-weight: 800; font-size: 14px; cursor: pointer; transition: all 0.2s; box-shadow: 0 10px 15px -3px rgba(var(--accent-rgb), 0.3);">
                    Send Reset Code
                </button>
            </form>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>

            <div style="margin-top: 28px; text-align: center; font-size: 13px;">
                <a href="{{ route('tenant.login') }}" style="color: var(--accent); text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 14px; height: 14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
