@extends('central.layouts.admin-layout')

@section('page-title', 'Profile')

@section('content')
<div class="container-fluid py-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('central.admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center p-4">
                    <div class="position-relative d-inline-block">
                        <div class="bg-success bg-opacity-10 p-4 rounded-circle mb-3" style="width: 120px; height: 120px; margin: 0 auto;">
                            <i class="bi bi-person-fill text-success" style="font-size: 3.5rem;"></i>
                        </div>
                        <button class="btn btn-sm btn-light position-absolute bottom-0 end-0 rounded-circle" 
                                data-bs-toggle="tooltip" title="Change Photo">
                            <i class="bi bi-camera"></i>
                        </button>
                    </div>
                    <h4 class="fw-bold mb-1">{{ auth()->user()->name }}</h4>
                    <p class="text-secondary mb-3">{{ ucfirst(auth()->user()->role) }} Administrator</p>
                    
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                            <i class="bi bi-shield-check me-1"></i> Verified
                        </span>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                            <i class="bi bi-clock-history me-1"></i> Active
                        </span>
                    </div>

                    <hr class="my-4">

                    <div class="text-start">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-light p-2 rounded me-3">
                                <i class="bi bi-envelope text-secondary"></i>
                            </div>
                            <div>
                                <small class="text-secondary d-block">Email Address</small>
                                <span class="fw-medium">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-light p-2 rounded me-3">
                                <i class="bi bi-calendar text-secondary"></i>
                            </div>
                            <div>
                                <small class="text-secondary d-block">Member Since</small>
                                <span class="fw-medium">{{ auth()->user()->created_at->format('F d, Y') }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-light p-2 rounded me-3">
                                <i class="bi bi-shield text-secondary"></i>
                            </div>
                            <div>
                                <small class="text-secondary d-block">Last Login</small>
                                <span class="fw-medium">{{ auth()->user()->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-semibold mb-0">Edit Profile Information</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('central.admin.profile.update') }}">
                        @csrf
                        @method('PUT')
                        
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
                                <hr class="my-2">
                                <h6 class="fw-semibold mt-3 mb-3">Change Password (Optional)</h6>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-medium">Current Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           name="current_password" 
                                           placeholder="••••••••">
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-medium">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control @error('new_password') is-invalid @enderror" 
                                           name="new_password" 
                                           placeholder="••••••••">
                                </div>
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-medium">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-check-circle"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control" 
                                           name="new_password_confirmation" 
                                           placeholder="••••••••">
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success px-5">
                                    <i class="bi bi-check-circle me-2"></i>Save Changes
                                </button>
                                <button type="reset" class="btn btn-outline-secondary px-4 ms-2">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card mt-4 border-danger">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-semibold mb-0 text-danger">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="fw-semibold mb-1">Delete Account</h6>
                            <p class="text-secondary small mb-0">Once you delete your account, there is no going back.</p>
                        </div>
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-2"></i>Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                
                <form method="POST" action="{{ route('central.admin.profile.delete') }}" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary small">Confirm Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                    </div>

                    <div class="bg-light p-3 rounded mb-3">
                        <small class="text-secondary d-block mb-2">Please type <strong class="text-danger">DELETE</strong> to confirm:</small>
                        <input type="text" class="form-control form-control-sm" id="confirmDelete" placeholder="DELETE">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="deleteForm" class="btn btn-danger" id="deleteBtn" disabled>Delete Account</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Enable delete button only when DELETE is typed
    document.getElementById('confirmDelete').addEventListener('input', function(e) {
        document.getElementById('deleteBtn').disabled = e.target.value !== 'DELETE';
    });
</script>
@endsection



