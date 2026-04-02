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

    {{-- Check Template Selection --}}
    @if(!auth()->user()->has_selected_template)
        <div class="row mb-4">
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

    @elseif(auth()->user()->school_domain)
        {{-- ======================== Domain Active State ======================== --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden">
                    {{-- Card Header --}}
                    <div class="card-header d-flex justify-content-between align-items-center py-3"
                         style="background: linear-gradient(135deg, #2c7a6e, #1e5a50);">
                        <h5 class="fw-semibold mb-0 text-white">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            School Active &amp; Connected
                        </h5>
                        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">
                            <i class="bi bi-wifi me-1"></i> Active
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4">
                            {{-- Domain Section --}}
                            <div class="col-md-6">
                                <div class="border rounded-3 p-4 h-100" style="background: #f8fffe;">
                                    <p class="text-secondary small mb-2 text-uppercase fw-bold" style="letter-spacing: 0.05em;">
                                        <i class="bi bi-globe2 me-1 text-success"></i>Your School Domain
                                    </p>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-3">
                                        <h4 class="mb-0 fw-bold text-success">{{ auth()->user()->school_domain }}</h4>
                                        <a href="{{ route('central.user.impersonate') }}" class="btn btn-success px-4">
                                            <i class="bi bi-box-arrow-up-right me-2"></i>Visit
                                        </a>
                                    </div>
                                    <div class="mt-3 p-2 bg-light rounded-2 small text-secondary">
                                        <i class="bi bi-info-circle me-1"></i>
                                        This is your public school address on Eduboard.
                                    </div>
                                </div>
                            </div>

                            {{-- Credentials Section --}}
                            <div class="col-md-6">
                                @if(isset($tenant) && isset($tenant->admin_password))
                                <div class="rounded-3 p-4 h-100" style="background: #1a2634; color: #e2e8f0; border: 1px solid rgba(251,191,36,0.3);">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <p class="small mb-0 fw-bold text-uppercase" style="color: #fbbf24; letter-spacing: 0.05em;">
                                            <i class="bi bi-shield-lock me-1"></i>Admin Credentials
                                        </p>
                                        <span class="badge" style="background: rgba(251,191,36,0.2); color: #fbbf24; font-size: 0.7rem;">Database Access</span>
                                    </div>

                                    <div class="mb-3 pb-2 d-flex justify-content-between align-items-center"
                                         style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                                        <span style="color: #94a3b8;">Email:</span>
                                        <span class="fw-bold">{{ auth()->user()->email }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span style="color: #94a3b8;">Password:</span>
                                        <div class="d-flex align-items-center gap-3">
                                            <code id="adminPassword" class="fw-bold fs-5" style="color: #fbbf24; letter-spacing: 0.2em;">••••••••</code>
                                            <button type="button" class="btn btn-sm border-0 p-1" id="togglePassword"
                                                    style="color: #fbbf24; background: transparent;">
                                                <i class="bi bi-eye fs-5" id="passwordIcon"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-4 p-2 rounded-2 small text-center"
                                         style="background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.08);">
                                        <i class="bi bi-lock me-1"></i>
                                        Use these to login to your school dashboard.
                                    </div>
                                </div>
                                @else
                                <div class="rounded-3 p-4 h-100 d-flex flex-column justify-content-center text-center"
                                     style="background: #1a2634; border: 1px solid rgba(255,255,255,0.1);">
                                    <i class="bi bi-shield-slash fs-1 mb-3" style="color: #4a5568;"></i>
                                    <h6 style="color: #718096;">Credentials Unavailable</h6>
                                    <p class="small mb-0" style="color: #4a5568;">Contact support if you cannot access your school dashboard.</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if(isset($tenant) && isset($tenant->admin_password))
                const passwordCode = document.getElementById('adminPassword');
                const toggleBtn   = document.getElementById('togglePassword');
                const icon        = document.getElementById('passwordIcon');
                const realPassword = @json($tenant->admin_password);
                let isVisible = false;

                toggleBtn.addEventListener('click', function() {
                    isVisible = !isVisible;
                    if (isVisible) {
                        passwordCode.textContent = realPassword;
                        passwordCode.style.letterSpacing = 'normal';
                        icon.classList.replace('bi-eye', 'bi-eye-slash');
                    } else {
                        passwordCode.textContent = '••••••••';
                        passwordCode.style.letterSpacing = '0.2em';
                        icon.classList.replace('bi-eye-slash', 'bi-eye');
                    }
                });
                @endif
            });
        </script>
        @endpush

    @else
        {{-- ======================== No Domain State ======================== --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5 px-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4"
                             style="width: 96px; height: 96px; background: rgba(44,122,110,0.1);">
                            <i class="bi bi-globe-americas" style="font-size: 3rem; color: #2c7a6e;"></i>
                        </div>
                        <h3 class="fw-bold mb-2">No School Domain Yet</h3>
                        <p class="text-secondary mb-4 mx-auto" style="max-width: 420px;">
                            You need to select a template first to create your school domain and database.
                        </p>
                        <a href="{{ route('central.user.templates') }}" class="btn btn-success px-5 py-2 rounded-pill fw-bold">
                            Select a Template Now <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Custom Domain (Upgrade Prompt) — always visible once template is selected --}}
    @if(auth()->user()->has_selected_template)
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-link-45deg me-2 text-success"></i>Custom Domain
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info d-flex align-items-center gap-3 mb-4">
                        <i class="bi bi-info-circle-fill fs-5 flex-shrink-0"></i>
                        <div>
                            Custom domains are available on paid plans. Upgrade to connect your own domain (e.g. <strong>school.com</strong>).
                        </div>
                    </div>
                    <a href="{{ route('central.user.subscription') }}" class="btn btn-success px-4">
                        <i class="bi bi-arrow-up-circle me-2"></i>Upgrade Now
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
