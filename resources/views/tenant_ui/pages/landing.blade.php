@extends('tenant_ui.layouts.auth-layout')

@section('title', 'Welcome to ' . (tenant('school_name') ?? 'School') . ' EduBoard')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 800px; width: 100%; display: grid; grid-template-columns: 1fr 1fr; padding: 0; overflow: hidden; border-radius: 32px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); border: none; margin: auto;">
        <!-- Left Side: Branding -->
        <div style="background: var(--accent); padding: 60px 48px; color: white; display: flex; flex-direction: column; justify-content: center; align-items: flex-start; position: relative; overflow: hidden;">
            {{-- Decorative pattern --}}
            <div style="position: absolute; top: -10%; right: -10%; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
            
            <div style="background: rgba(255,255,255,0.2); width: 80px; height: 80px; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin-bottom: 32px; backdrop-filter: blur(4px); overflow: hidden; border: 1px solid rgba(255,255,255,0.3);">
                @if(tenant('logo'))
                    <img src="{{ tenant_asset(tenant('logo')) }}" style="width: 100%; height: 100%; object-fit: contain; padding: 6px;">
                @else
                    <svg fill="currentColor" viewBox="0 0 24 24" style="width: 40px; height: 40px; color: white;">
                        <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/>
                    </svg>
                @endif
            </div>
            
            <h1 style="font-size: 36px; color: white; font-weight: 800; margin-bottom: 16px; font-family: 'Sora', sans-serif; letter-spacing: -1px;">Welcome to {{ tenant('school_name') ?? 'Buksu' }}</h1>
            
            <p style="font-size: 16px; color: white; opacity: 0.85; line-height: 1.6; margin-bottom: 40px; font-family: 'DM Sans', sans-serif;">
                {{ tenant('site_description') ?? 'Official EduBoard platform for announcements, events, and academic updates for ' . (tenant('school_name') ?? 'Buksu') . '.' }}
            </p>
            
            <div style="margin-top: auto; color: white; font-size: 13px; opacity: 0.6; font-weight: 500;">
                © {{ date('Y') }} {{ tenant('school_name') ?? 'Buksu' }} EduBoard
            </div>
        </div>

        <!-- Right Side: Actions -->
        <div style="padding: 60px 48px; background: white; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">

            @auth
                {{-- Already logged in: show indicator --}}
                <div style="margin-bottom: 32px;">
                    <div style="width: 72px; height: 72px; background: rgba(var(--accent-rgb), 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <svg fill="none" stroke="var(--accent)" viewBox="0 0 24 24" stroke-width="2" style="width: 32px; height: 32px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h2 style="font-size: 22px; color: #1a1a1a; font-weight: 800; margin-bottom: 8px;">You're logged in</h2>
                    <p style="color: #64748b; font-size: 14px; line-height: 1.6;">
                        Welcome back, <strong style="color: #1a1a1a;">{{ auth()->user()->name }}</strong>!<br>
                        You already have an active session.
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px; width: 100%; max-width: 280px;">
                    @php
                        $dashRoute = (auth()->user()->role === 'admin' || auth()->user()->role === 'teacher')
                            ? route('tenant.admin.dashboard')
                            : route('tenant.announcements');
                    @endphp
                    <a href="{{ $dashRoute }}" style="background: var(--accent); color: white; padding: 14px 28px; border-radius: 100px; text-decoration: none; font-weight: 700; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 4px 12px rgba(var(--accent-rgb), 0.25);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Go Back to Dashboard
                    </a>
                </div>
            @else
                {{-- Guest: show login/register --}}
                <div class="auth-header" style="margin-bottom: 40px; width: 100%;">
                    <h1 style="font-size: 24px; color: #1a1a1a; font-weight: 800; margin-bottom: 8px;">Get Started</h1>
                    <p style="color: #64748b; font-size: 14px;">Please sign in to access your portal at {{ tenant('school_name') ?? 'Buksu' }}.</p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px; width: 100%; max-width: 280px;">
                    <a href="{{ route('tenant.login') }}" class="btn-auth" style="background: var(--accent); color: white; padding: 14px 28px; border-radius: 100px; text-decoration: none; font-weight: 600; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(var(--accent-rgb), 0.2);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        School Login
                    </a>

                    <a href="{{ route('tenant.register') }}" style="background: white; color: #64748b; padding: 14px 28px; border-radius: 100px; text-decoration: none; font-weight: 500; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.2s ease; border: 1.5px solid #e2e8f0;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Register New Account
                    </a>
                </div>

                <div style="margin-top: 60px;">
                    <p style="font-size: 13px; color: #94a3b8; font-weight: 500;">
                        Need help? Contact us at <a href="mailto:{{ tenant('primary_email') ?? 'admin@school.edu' }}" style="color: var(--accent); text-decoration: underline; font-weight: 700;">{{ tenant('primary_email') ?? 'admin@school.edu' }}</a>
                    </p>
                </div>
            @endauth
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .auth-card {
            grid-template-columns: 1fr !important;
        }
        .auth-card > div:first-child {
            display: none !important;
        }
    }
</style>
@endsection
