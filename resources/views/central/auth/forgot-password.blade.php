@extends('central.layouts.auth-layout')

@section('title', 'EduBoard - Forgot Password')

@section('content')
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card" style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div class="auth-header">
                <div style="width: 64px; height: 64px; background: #e6f2f0; color: #2c7a6e; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="bi bi-key" style="font-size: 2rem;"></i>
                </div>
                <h2 style="font-weight: 800; color: #1e1b4b;">Forgot Password?</h2>
                <p style="color: #64748b;">Enter your email address and we'll send you a 6-digit verification code to reset your password.</p>
            </div>
            
            <div class="auth-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    
                    <div class="form-group mb-4">
                        <label for="email" style="font-weight: 700; color: #334155; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Email Address</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Enter your email"
                               style="padding: 12px 16px; border-radius: 12px; border: 1px solid #e2e8f0;"
                               required autofocus>
                    </div>
                    
                    <button type="submit" class="btn-auth" style="background: #2c7a6e; color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 700; width: 100%; transition: all 0.2s;">
                        Send Verification Code
                    </button>
                    
                    <div class="auth-links mt-4" style="text-align: center;">
                        <a href="{{ route('login') }}" style="color: #2c7a6e; font-weight: 700; text-decoration: none; font-size: 14px;">
                            <i class="bi bi-arrow-left"></i>
                            Back to Sign In
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
