@extends('tenant_ui.layouts.auth-layout')

@section('title', (tenant('school_name') ?? 'School') . ' - Login')

@section('content')
<div class="auth-wrapper" style="display: block !important; padding: 4rem 1rem !important; height: auto !important; min-height: 100vh;">
    <div class="auth-container" style="margin: 0 auto !important; max-width: 480px;">
        <div class="auth-card">
            <div class="auth-header" style="text-align: center; margin-bottom: 2.5rem;">
                <a href="/" style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 20px; background: rgba(var(--accent-rgb), 0.10); color: var(--accent); margin-bottom: 1.25rem; text-decoration: none; transition: transform 0.2s ease, background 0.2s ease;" onmouseover="this.style.transform='scale(1.05)'; this.style.background='rgba(var(--accent-rgb), 0.15)';" onmouseout="this.style.transform='scale(1)'; this.style.background='rgba(var(--accent-rgb), 0.10)';">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 36px; height: 36px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                    </svg>
                </a>
                <h1 style="font-family: 'Sora', sans-serif; font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin: 0; letter-spacing: -0.5px;">{{ tenant('school_name') ?? 'EduPlatform' }}</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.5rem;">Welcome back. Please sign in to your portal.</p>
            </div>

            @if(session('status'))
                <div class="form-error" style="background: rgba(var(--accent-rgb), 0.10); color: var(--accent); padding: 10px 14px; border-radius: 10px; margin-bottom: 20px;">
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
                    <div style="position: relative;">
                        <input type="password" name="password" id="password"
                               placeholder="••••••••"
                               required style="padding-right: 44px;">
                        <button type="button" id="togglePassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--muted); display: flex; align-items: center; justify-content: center; padding: 4px;">
                            <svg id="eyeOpen" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.391 9.851 7.318 6 12 6s8.609 3.85 9.964 5.678a1.012 1.012 0 010 .644C20.609 14.149 16.682 18 12 18s-8.609-3.851-9.964-5.678z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg id="eyeClosed" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 20px; height: 20px; display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.355 1.829 5.281 5.678 9.964 5.678a10.477 10.477 0 005.074-1.3l2.84 2.84m-9.914-9.914L3.98 3.98m6.02 6.02L9.168 9.168m6.02 6.02l5.031 5.031M16.898 16.898a10.477 10.477 0 002.512-4.898c-1.355-1.829-5.281-5.678-9.964-5.678a10.477 10.477 0 00-2.512.428m0 0l2.512 2.512m0 0a3 3 0 013.951 3.951" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                    <div style="text-align: right; margin-top: 8px;">
                        <a href="{{ route('tenant.password.request') }}" style="color: var(--accent); text-decoration: none; font-size: 13px; font-weight: 700;">Forgot Password?</a>
                    </div>
                </div>
                
                <div class="auth-actions" style="margin-top: 2rem; display: flex; flex-direction: column; gap: 1.5rem; align-items: center;">
                    <div style="display: flex; justify-content: center;">
                        <div class="g-recaptcha" data-sitekey="6Lf02KQsAAAAAKnEOnptwcS0Bdu_ThNf-u4wAntd"></div>
                    </div>
                    
                    <button type="submit" class="btn-auth" style="width: 100%; justify-content: center; padding: 14px 28px !important;">
                        Sign In
                    </button>
                </div>
            </form>

            <div style="margin-top: 32px; text-align: center; font-size: 14px;">
                <span style="color: var(--muted);">Don't have an account?</span>
                <a href="{{ route('tenant.register') }}" style="color: var(--accent); text-decoration: none; font-weight: 800; margin-left: 6px;">Register Now</a>
            </div>
        </div>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeOpen.style.display = 'none';
            eyeClosed.style.display = 'block';
        } else {
            passwordInput.type = 'password';
            eyeOpen.style.display = 'block';
            eyeClosed.style.display = 'none';
        }
    });
</script>
@endsection
