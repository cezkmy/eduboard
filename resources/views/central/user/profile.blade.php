@extends('central.layouts.user-layout')

@section('page-title', 'Profile')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-1">My Profile</h2>
            <p class="text-secondary">Manage your account settings and preferences</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="position-relative d-inline-block">
                        <div class="bg-success bg-opacity-10 rounded-circle mb-3 overflow-hidden d-flex align-items-center justify-content-center" style="width: 120px; height: 120px; margin: 0 auto;">
                            @if(auth()->user()->profile_photo)
                                <img id="profile_preview_image" src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile" class="w-100 h-100 object-fit-cover">
                            @else
                                <div id="profile_placeholder" class="d-flex align-items-center justify-content-center w-100 h-100">
                                    <i class="bi bi-person-fill text-success" style="font-size: 3.5rem;"></i>
                                </div>
                                <img id="profile_preview_image" src="" alt="Profile" class="w-100 h-100 object-fit-cover d-none">
                            @endif
                        </div>
                        <label for="profile_photo_input" class="btn btn-sm btn-light position-absolute bottom-0 end-0 rounded-circle shadow-sm border" 
                                style="cursor: pointer;" data-bs-toggle="tooltip" title="Change Photo">
                            <i class="bi bi-camera-fill text-primary"></i>
                        </label>
                    </div>
                    <h4 class="fw-bold mb-1">{{ auth()->user()->name }}</h4>
                    <p class="text-secondary mb-3">{{ auth()->user()->is_admin ? 'Admin' : 'School Admin' }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                            <i class="bi bi-shield-check me-1"></i> Active
                        </span>
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                            <i class="bi bi-building me-1"></i> {{ auth()->user()->school_name }}
                        </span>
                    </div>

                    <div class="text-start">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-secondary bg-opacity-10 p-2 rounded me-3">
                                <i class="bi bi-envelope text-secondary"></i>
                            </div>
                            <div>
                                <small class="text-secondary d-block">Email Address</small>
                                <span class="fw-medium">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-secondary bg-opacity-10 p-2 rounded me-3">
                                <i class="bi bi-calendar text-secondary"></i>
                            </div>
                            <div>
                                <small class="text-secondary d-block">Member Since</small>
                                <span class="fw-medium">{{ \Carbon\Carbon::parse(auth()->user()->created_at)->format('F d, Y') }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary bg-opacity-10 p-2 rounded me-3">
                                <i class="bi bi-clock text-secondary"></i>
                            </div>
                            <div>
                                <small class="text-secondary d-block">Last Login</small>
                                <span class="fw-medium">{{ \Carbon\Carbon::parse(auth()->user()->updated_at)->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h5 class="fw-semibold mb-0">Edit Profile Information</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('central.user.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Hidden File Input for Profile Photo (No longer auto-submitting) -->
                        <input type="file" id="profile_photo_input" name="profile_photo" class="d-none" accept="image/*">
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Full Name</label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       name="name" 
                                       value="{{ old('name', auth()->user()->name) }}"
                                       placeholder="Enter your full name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Email Address</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" 
                                       value="{{ old('email', auth()->user()->email) }}"
                                       placeholder="Enter your email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-medium">School Name</label>
                                <input type="text" 
                                       class="form-control @error('school_name') is-invalid @enderror" 
                                       name="school_name" 
                                       value="{{ old('school_name', auth()->user()->school_name) }}"
                                       placeholder="Enter school name">
                                @error('school_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Phone Number</label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       name="phone" 
                                       value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                       placeholder="Enter phone number">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Address</label>
                                <input type="text" 
                                       class="form-control @error('address') is-invalid @enderror" 
                                       name="address" 
                                       value="{{ old('address', auth()->user()->address ?? '') }}"
                                       placeholder="Enter address">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success px-5">
                                    <i class="bi bi-check-circle me-2"></i>Save Profile
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr class="my-5">

                    <!-- Change Password Form -->
                    <h5 class="fw-semibold mb-4">Security & Password</h5>
                    <form method="POST" action="{{ route('central.user.password.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Current Password</label>
                                <input type="password" 
                                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                                       name="current_password" 
                                       placeholder="••••••••">
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-medium">New Password</label>
                                <input type="password" 
                                       class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                                       name="password" 
                                       placeholder="••••••••">
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-medium">Confirm Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       name="password_confirmation" 
                                       placeholder="••••••••">
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-dark px-5">
                                    <i class="bi bi-shield-lock me-2"></i>Update Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoInput = document.getElementById('profile_photo_input');
        const previewImage = document.getElementById('profile_preview_image');
        const placeholder = document.getElementById('profile_placeholder');

        if (photoInput) {
            photoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewImage.classList.remove('d-none');
                        if (placeholder) {
                            placeholder.classList.add('d-none');
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endpush



