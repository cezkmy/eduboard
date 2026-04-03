@extends('tenant_ui.layouts.auth-layout')

@section('title', (tenant('school_name') ?? 'School') . ' - Verify Code')

@section('content')
<div class="auth-wrapper" style="display: block !important; padding: 4rem 1rem !important; height: auto !important; min-height: 100vh;">
    <div class="auth-container" style="margin: 0 auto !important; max-width: 480px;">
        <div class="auth-card">
            <div class="auth-header" style="text-align: center; margin-bottom: 2.5rem;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 20px; background: rgba(var(--accent-rgb), 0.10); color: var(--accent); margin-bottom: 1.25rem;">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 36px; height: 36px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </div>
                <h1 style="font-family: 'Sora', sans-serif; font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin: 0; letter-spacing: -0.5px;">Check Your Email</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.5rem;">We've sent a 6-digit code to <span style="color: var(--accent); font-weight: 800;">{{ session('reset_email') }}</span>.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger" style="background: #fef2f2; color: #dc2626; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 20px; border: 1px solid #fee2e2;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.post-verify') }}" class="auth-form" id="verify-form">
                @csrf
                
                <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 2.5rem;">
                    @for($i = 0; $i < 6; $i++)
                        <input type="text" name="code[]" maxlength="1" 
                               class="code-input"
                               data-index="{{ $i }}"
                               required
                               style="width: 52px; height: 68px; text-align: center; font-size: 28px; font-weight: 800; border: 2px solid #f3f4f6; border-radius: 16px; outline: none; transition: all 0.2s; background: #f9fafb;"
                               oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value.length === 1 && {{ $i }} < 5) document.querySelector('[data-index=\'{{ $i+1 }}\']').focus();"
                               onkeydown="if(event.key === 'Backspace' && !this.value && {{ $i }} > 0) document.querySelector('[data-index=\'{{ $i-1 }}\']').focus();">
                    @endfor
                </div>
                
                <button type="submit" class="btn-auth" style="width: 100%; padding: 16px; background: var(--accent); color: white; border: none; border-radius: 16px; font-weight: 800; font-size: 14px; cursor: pointer; transition: all 0.2s; box-shadow: 0 10px 15px -3px rgba(var(--accent-rgb), 0.3);">
                    Verify & Continue
                </button>
            </form>

            <div style="margin-top: 32px; text-align: center; font-size: 13px;">
                <p style="color: var(--text-muted); margin-bottom: 8px;">Didn't receive the code?</p>
                <form action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="{{ session('reset_email') }}">
                    <button type="submit" style="background: none; border: none; color: var(--accent); font-weight: 800; cursor: pointer; padding: 4px 8px; text-decoration: underline;">Resend Code</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .code-input:focus {
        border-color: var(--accent) !important;
        background: white !important;
        box-shadow: 0 0 0 5px rgba(var(--accent-rgb), 0.1) !important;
        transform: translateY(-2px);
    }
</style>

<script>
    document.addEventListener('paste', function(e) {
        if (e.target.classList.contains('code-input')) {
            const data = e.clipboardData.getData('text').trim();
            if (data.length === 6 && /^\d+$/.test(data)) {
                const inputs = document.querySelectorAll('.code-input');
                data.split('').forEach((char, i) => {
                    inputs[i].value = char;
                });
                document.getElementById('verify-form').submit();
            }
        }
    });

    document.querySelector('[data-index="5"]').addEventListener('input', function() {
        if (this.value.length === 1) {
            setTimeout(() => { document.getElementById('verify-form').submit(); }, 150);
        }
    });
</script>
@endsection
