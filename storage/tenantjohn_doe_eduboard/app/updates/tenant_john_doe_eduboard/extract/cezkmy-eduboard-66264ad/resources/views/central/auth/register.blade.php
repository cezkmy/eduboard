@extends('central.layouts.auth-layout')

@section('title', 'EduBoard - Register School')

@section('content')
<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <i class="bi bi-mortarboard"></i>
                <h2>Register School</h2>
                <p>Create your EduBoard school account.</p>
            </div>
            
            <div class="auth-body">
                <!-- Show validation errors -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Enter your full name"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Enter your email"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- School Name -->
                    <div class="form-group">
                        <label for="school_name">School Name</label>
                        <input type="text" 
                               class="form-control @error('school_name') is-invalid @enderror" 
                               id="school_name" 
                               name="school_name" 
                               value="{{ old('school_name') }}" 
                               placeholder="Enter school name"
                               required>
                        @error('school_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="Create password (min. 8 characters)"
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Minimum 8 characters</small>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               placeholder="Confirm password"
                               required>
                    </div>
                    
                    <!-- Register Button -->
                    <button type="submit" class="btn-auth">
                        Register School
                    </button>
                    
                    <!-- Login link -->
                    <div class="auth-links">
                        <span>Already have an account?</span>
                        <a href="{{ route('login') }}">Sign in</a>
                    </div>
                    
                    <!-- Back link -->
                    <div class="back-link">
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
@endsection



