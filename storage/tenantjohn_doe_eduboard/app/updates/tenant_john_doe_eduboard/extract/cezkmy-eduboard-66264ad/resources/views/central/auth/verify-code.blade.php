@extends('central.layouts.auth-layout')

@section('title', 'EduBoard - Verify Code')

@section('content')
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card" style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div class="auth-header text-center">
                <div style="width: 64px; height: 64px; background: #e6f2f0; color: #2c7a6e; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="bi bi-envelope-check" style="font-size: 2rem;"></i>
                </div>
                <h2 style="font-weight: 800; color: #1e1b4b;">Verify Email</h2>
                <p style="color: #64748b;">A 6-digit code has been sent to your email. Please enter it below to verify your account.</p>
            </div>
            
            <div class="auth-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.post-verify') }}" id="verify-form">
                    @csrf
                    
                    <div style="display: flex; gap: 8px; justify-content: center; margin-bottom: 2rem;">
                        @for($i = 0; $i < 6; $i++)
                            <input type="text" name="code[]" maxlength="1" 
                                   class="code-input"
                                   data-index="{{ $i }}"
                                   required
                                   style="width: 48px; height: 60px; text-align: center; font-size: 24px; font-weight: 800; border: 1px solid #cbd5e1; border-radius: 10px; outline: none; transition: border-color 0.2s;"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value.length === 1 && {{ $i }} < 5) document.querySelector('[data-index=\'{{ $i+1 }}\']').focus();"
                                   onkeydown="if(event.key === 'Backspace' && !this.value && {{ $i }} > 0) document.querySelector('[data-index=\'{{ $i-1 }}\']').focus();">
                        @endfor
                    </div>
                    
                    <button type="submit" class="btn-auth" style="background: #2c7a6e; color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 700; width: 100%; transition: all 0.2s;">
                        Verify Code
                    </button>
                    
                    <div class="auth-links mt-4" style="text-align: center;">
                        <p style="font-size: 14px; color: #64748b;">Didn't receive the code?</p>
                        <form action="{{ route('password.email') }}" method="POST">
                            @csrf
                            <input type="hidden" name="email" value="{{ session('reset_email') }}">
                            <button type="submit" style="background: none; border: none; color: #2c7a6e; font-weight: 700; cursor: pointer; padding: 0;">Resend Code</button>
                        </form>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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

    document.querySelectorAll('.code-input').forEach(input => {
        input.addEventListener('focus', () => { input.style.borderColor = '#4f46e5'; });
        input.addEventListener('blur', () => { input.style.borderColor = '#cbd5e1'; });
    });
</script>
@endsection
