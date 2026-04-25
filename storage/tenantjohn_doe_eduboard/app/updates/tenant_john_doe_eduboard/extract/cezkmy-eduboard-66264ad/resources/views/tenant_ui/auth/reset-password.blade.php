@extends('tenant_ui.layouts.auth-layout')

@section('title', (tenant('school_name') ?? 'School') . ' - Reset Password')

@section('content')
<div class="auth-wrapper" style="display: block !important; padding: 4rem 1rem !important; height: auto !important; min-height: 100vh;">
    <div class="auth-container" style="margin: 0 auto !important; max-width: 480px;">
        <div class="auth-card">
            <div class="auth-header" style="text-align: center; margin-bottom: 2.5rem;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 20px; background: rgba(var(--accent-rgb), 0.10); color: var(--accent); margin-bottom: 1.25rem;">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 36px; height: 36px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h1 style="font-family: 'Sora', sans-serif; font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin: 0; letter-spacing: -0.5px;">Set New Password</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.5rem;">Verification successful. Please choose a strong new password to protect your account.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger" style="background: #fef2f2; color: #dc2626; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 20px; border: 1px solid #fee2e2;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('tenant.password.update-post') }}" class="auth-form" x-data="{ password: '', showPass: false, showConfirm: false }">
                @csrf
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; text-transform: uppercase;">New Password</label>
                    <div style="position: relative;">
                        <input :type="showPass ? 'text' : 'password'" name="password" x-model="password"
                               placeholder="••••••••"
                               required autofocus
                               style="width: 100%; padding: 14px 16px; padding-right: 44px; border-radius: 14px; border: 2px solid #f3f4f6; background: #f9fafb; outline: none; transition: all 0.2s;"
                               onfocus="this.style.borderColor='var(--accent)'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(var(--accent-rgb), 0.1)'"
                               onblur="this.style.borderColor='#f3f4f6'; this.style.background='#f9fafb'; this.style.boxShadow='none'">
                        <button type="button" @click="showPass = !showPass" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--muted); padding: 4px; display: flex; align-items: center; justify-content: center;">
                            <svg x-show="!showPass" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.391 9.851 7.318 6 12 6s8.609 3.85 9.964 5.678a1.012 1.012 0 010 .644C20.609 14.149 16.682 18 12 18s-8.609-3.851-9.964-5.678z" /><circle cx="12" cy="12" r="3" /></svg>
                            <svg x-show="showPass" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 18px; height: 18px; display: none;" :style="{ display: showPass ? 'block' : 'none' }"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.355 1.829 5.281 5.678 9.964 5.678a10.477 10.477 0 005.074-1.3l2.84 2.84m-9.914-9.914L3.98 3.98m6.02 6.02L9.168 9.168m6.02 6.02l5.031 5.031M16.898 16.898a10.477 10.477 0 002.512-4.898c-1.355-1.829-5.281-5.678-9.964-5.678a10.477 10.477 0 00-2.512.428m0 0l2.512 2.512m0 0a3 3 0 013.951 3.951" /></svg>
                        </button>
                    </div>
                    
                    {{-- Password Strength Indicator --}}
                    <div style="display: flex; gap: 6px; margin-top: 10px;">
                        @for($i = 0; $i < 4; $i++)
                            <div style="flex: 1; height: 6px; border-radius: 3px; transition: all 0.3s;"
                                 :style="password.length >= ({{ $i+1 }} * 2) ? 'background-color: var(--accent)' : 'background-color: #f3f4f6'"></div>
                        @endfor
                    </div>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-2" x-show="password.length > 0">
                        Strength: <span x-text="password.length < 4 ? 'Weak' : (password.length < 8 ? 'Fair' : 'Strong')"></span>
                    </p>
                </div>

                <div class="form-group" style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; text-transform: uppercase;">Confirm Password</label>
                    <div style="position: relative;">
                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" 
                               placeholder="••••••••"
                               required
                               style="width: 100%; padding: 14px 16px; padding-right: 44px; border-radius: 14px; border: 2px solid #f3f4f6; background: #f9fafb; outline: none; transition: all 0.2s;"
                               onfocus="this.style.borderColor='var(--accent)'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(var(--accent-rgb), 0.1)'"
                               onblur="this.style.borderColor='#f3f4f6'; this.style.background='#f9fafb'; this.style.boxShadow='none'">
                        <button type="button" @click="showConfirm = !showConfirm" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--muted); padding: 4px; display: flex; align-items: center; justify-content: center;">
                            <svg x-show="!showConfirm" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 18px; height: 18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.391 9.851 7.318 6 12 6s8.609 3.85 9.964 5.678a1.012 1.012 0 010 .644C20.609 14.149 16.682 18 12 18s-8.609-3.851-9.964-5.678z" /><circle cx="12" cy="12" r="3" /></svg>
                            <svg x-show="showConfirm" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 18px; height: 18px; display: none;" :style="{ display: showConfirm ? 'block' : 'none' }"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.355 1.829 5.281 5.678 9.964 5.678a10.477 10.477 0 005.074-1.3l2.84 2.84m-9.914-9.914L3.98 3.98m6.02 6.02L9.168 9.168m6.02 6.02l5.031 5.031M16.898 16.898a10.477 10.477 0 002.512-4.898c-1.355-1.829-5.281-5.678-9.964-5.678a10.477 10.477 0 00-2.512.428m0 0l2.512 2.512m0 0a3 3 0 013.951 3.951" /></svg>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-auth" style="width: 100%; padding: 16px; background: var(--accent); color: white; border: none; border-radius: 16px; font-weight: 800; font-size: 14px; cursor: pointer; transition: all 0.2s; box-shadow: 0 10px 15px -3px rgba(var(--accent-rgb), 0.3);">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
