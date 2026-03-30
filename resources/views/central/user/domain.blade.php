@extends('central.layouts.user-layout')

@section('page-title', 'Domain Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-1">Domain Management</h2>
            <p class="text-secondary">Configure your school's domain</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Check Template Selection -->
    @if(!auth()->user()->has_selected_template)
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-1 mb-3"></i>
                        <h4 class="fw-bold mb-3">Template Required</h4>
                        <p class="text-secondary mb-4">You need to select a template first before accessing Domain Management.</p>
                        <a href="{{ route('central.user.templates.select') }}" class="btn btn-success btn-lg">
                            <i class="bi bi-files me-2"></i>Select Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
    @if(auth()->user()->school_domain)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-header bg-primary border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-semibold mb-0 text-white">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        School Active & Connected
                    </h5>
                    <span class="badge bg-white text-primary px-3 rounded-pill fw-bold">Active</span>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Domain Section -->
                        <div class="col-md-6">
                            <div class="bg-white text-dark p-4 rounded-3 h-100 shadow-sm">
                                <p class="text-secondary small mb-2 text-uppercase fw-bold tracking-wider">Your School Domain</p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h4 class="mb-0 fw-bold text-primary">{{ auth()->user()->school_domain }}</h4>
                                    <a href="http://{{ auth()->user()->school_domain }}" target="_blank" class="btn btn-primary px-4">
                                        <i class="bi bi-box-arrow-up-right me-2"></i> Visit
                                    </a>
                                </div>
                                <div class="mt-3 p-2 bg-light rounded-2 small text-secondary">
                                    <i class="bi bi-info-circle me-1"></i>
                                    This is your public school address on Eduboard.
                                </div>
                            </div>
                        </div>

                        <!-- Credentials Section -->
                        <div class="col-md-6">
                            @if(isset($tenant) && isset($tenant->admin_password))
                            <div class="bg-dark text-light p-4 rounded-3 h-100 shadow-sm border border-warning border-opacity-50">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <p class="text-warning small mb-0 text-uppercase fw-bold tracking-wider">
                                        <i class="bi bi-shield-lock me-1"></i> Admin Credentials
                                    </p>
                                    <div class="badge bg-warning text-dark small">Database Access</div>
                                </div>
                                
                                <div class="mb-3 d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25 pb-2">
                                    <span class="text-secondary">Email:</span>
                                    <span class="fw-bold">{{ auth()->user()->email }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-secondary">Password:</span>
                                    <div class="d-flex align-items-center gap-3">
                                        <code id="adminPassword" class="text-warning fw-bold fs-5 tracking-widest">••••••••</code>
                                        <button type="button" class="btn btn-sm btn-outline-warning border-0 p-1" id="togglePassword">
                                            <i class="bi bi-eye fs-5" id="passwordIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-4 p-2 bg-secondary bg-opacity-10 rounded-2 small text-secondary border border-secondary border-opacity-10 text-center">
                                    <i class="bi bi-lock me-1"></i>
                                    Use these to login to your school dashboard.
                                </div>
                            </div>
                            @else
                            <div class="bg-dark text-light p-4 rounded-3 h-100 shadow-sm border border-secondary d-flex flex-column justify-content-center text-center">
                                <i class="bi bi-shield-slash fs-1 text-secondary mb-3"></i>
                                <h6 class="text-secondary">Credentials Unavailable</h6>
                                <p class="small text-muted mb-0">Contact support if you cannot access your school dashboard.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordCode = document.getElementById('adminPassword');
            const toggleBtn = document.getElementById('togglePassword');
            const icon = document.getElementById('passwordIcon');
            
            @if(isset($tenant) && isset($tenant->admin_password))
            const realPassword = "{{ $tenant->admin_password }}";
            let isVisible = false;

            toggleBtn.addEventListener('click', function() {
                isVisible = !isVisible;
                if (isVisible) {
                    passwordCode.textContent = realPassword;
                    passwordCode.classList.remove('tracking-widest');
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    passwordCode.textContent = '••••••••';
                    passwordCode.classList.add('tracking-widest');
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
            @endif
        });
    </script>
    @else
    <!-- No Domain Created State -->
    <div class="row mb-4">
        <div class="col-12 text-center py-5">
            <div class="bg-white p-5 rounded shadow-sm">
                <i class="bi bi-globe-americas display-1 text-light mb-4"></i>
                <h3 class="fw-bold">No School Domain Yet</h3>
                <p class="text-secondary mb-4">You need to select a template first to create your school domain and database.</p>
                <a href="{{ route('central.user.templates') }}" class="btn btn-primary px-5 py-3 rounded-pill fw-bold">
                    Select a Template Now <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
    @endif

        <!-- Custom Domain (Upgrade Prompt) -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-semibold mb-0">Custom Domain</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Custom domains are available on paid plans. Upgrade to connect your own domain.
                        </div>
                        <a href="{{ route('central.user.subscription') }}" class="btn btn-success">
                            <i class="bi bi-arrow-up-circle me-2"></i>Upgrade Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection



