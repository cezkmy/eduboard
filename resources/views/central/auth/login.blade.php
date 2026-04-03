@extends('central.layouts.auth-layout')

@section('title', (function_exists('tenant') && tenant('school_name')) ? tenant('school_name') . ' - Login' : 'EduBoard - Admin Login')

@section('content')
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <i class="bi bi-mortarboard"></i>
                <h2>{{ (function_exists('tenant') && tenant('school_name')) ? tenant('school_name') . ' Login' : 'Admin Login' }}</h2>
                <p>{{ (function_exists('tenant') && tenant('school_name')) ? 'Sign in to your school panel.' : 'Sign in to the EduBoard admin panel.' }}</p>
            </div>
            
            <div class="auth-body">
                <!-- Show error message if login fails -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Enter your email"
                               required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="position-relative">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   style="padding-right: 40px;"
                                   required>
                            <button type="button" id="togglePassword" class="position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent pe-3" style="z-index: 10;">
                                <i class="bi bi-eye text-muted" id="eyeIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="text-end mt-1">
                            <a href="{{ route('password.request') }}" class="text-decoration-none small" style="color: #2c7a6e; font-weight: 700;">Forgot Password?</a>
                        </div>
                    </div>
                    
                    <!-- ReCaptcha Integration -->
                    <div class="form-group d-flex justify-content-center mt-4">
                        <div class="g-recaptcha" data-sitekey="6Lf02KQsAAAAAKnEOnptwcS0Bdu_ThNf-u4wAntd"></div>
                    </div>

                    <!-- Remember Me -->
                    <div class="form-check mb-3 mt-3">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <!-- Sign In Button -->
                    <button type="submit" class="btn-auth">
                        Sign In
                    </button>
                    
                    <!-- Register link -->
                    <div class="auth-links mt-4">
                        <span>Don't have an account?</span>
                        <a href="{{ route('register') }}" style="color: #2c7a6e; font-weight: 700;">Register your school</a>
                    </div>
                    
                    <!-- Back link -->
                    <div class="back-link mt-3">
                        <a href="{{ route('home') }}">
                            <i class="bi bi-arrow-left"></i>
                            Back to website
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        }
    });
</script>
@endsection




