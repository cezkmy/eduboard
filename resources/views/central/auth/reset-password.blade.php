@extends('central.layouts.auth-layout')

@section('title', 'EduBoard - Set New Password')

@section('content')
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card" style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div class="auth-header text-center">
                <div style="width: 64px; height: 64px; background: #e6f2f0; color: #2c7a6e; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="bi bi-shield-lock" style="font-size: 2rem;"></i>
                </div>
                <h2 style="font-weight: 800; color: #1e1b4b;">New Password</h2>
                <p style="color: #64748b;">Verification confirmed. Please enter your new secure password below to finish the process.</p>
            </div>
            
            <div class="auth-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update-post') }}" x-data="{ password: '' }">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label for="password" style="font-weight: 700; color: #334155; font-size: 13px; text-transform: uppercase;">New Password</label>
                        <div class="position-relative">
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter new password"
                                   style="padding: 12px 16px; border-radius: 12px; border: 1px solid #e2e8f0; padding-right: 40px;"
                                   required autofocus x-model="password">
                            <button type="button" class="toggle-pass position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent pe-3" style="z-index: 10;" data-target="password">
                                <i class="bi bi-eye text-muted"></i>
                            </button>
                        </div>
                        
                        <div style="display: flex; gap: 4px; margin-top: 8px;">
                            @for($i = 0; $i < 4; $i++)
                                <div style="flex: 1; height: 4px; border-radius: 2px; transition: all 0.3s;"
                                     :style="password.length >= ({{ $i+1 }} * 2) ? 'background-color: #2c7a6e' : 'background-color: #f1f5f9'"></div>
                            @endfor
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="password_confirmation" style="font-weight: 700; color: #334155; font-size: 13px; text-transform: uppercase;">Confirm Password</label>
                        <div class="position-relative">
                            <input type="password" 
                                class="form-control" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                placeholder="Confirm new password"
                                style="padding: 12px 16px; border-radius: 12px; border: 1px solid #e2e8f0; padding-right: 40px;"
                                required>
                            <button type="button" class="toggle-pass position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent pe-3" style="z-index: 10;" data-target="password_confirmation">
                                <i class="bi bi-eye text-muted"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-auth" style="background: #2c7a6e; color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 700; width: 100%; transition: all 0.2s;">
                        Reset Password
                    </button>
                    
                    <div class="auth-links mt-4 text-center">
                        <p style="font-size: 13px; color: #94a3b8;">Make sure your password is at least 8 characters long.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-pass').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });
</script>
@endsection
