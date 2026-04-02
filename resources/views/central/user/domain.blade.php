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
                                <div class="border border-secondary border-opacity-10 rounded-3 p-4 h-100 position-relative bg-body">
                                    <p class="text-secondary small mb-2 text-uppercase fw-bold" style="letter-spacing: 0.05em;">
                                        <i class="bi bi-globe2 me-1 text-success"></i>Your School Domain
                                    </p>
                                    <div class="d-flex flex-column align-items-start gap-3 mt-3">
                                        <h4 class="mb-0 fw-bold text-success text-break w-100" style="word-break: break-all;">
                                            {{ auth()->user()->school_domain }}
                                        </h4>
                                        <a href="{{ route('central.user.impersonate') }}" class="btn btn-success px-4 mt-auto">
                                            <i class="bi bi-box-arrow-up-right me-2"></i>Visit
                                        </a>
                                    </div>
                                    <div class="mt-4 p-2 bg-secondary bg-opacity-10 rounded-2 small text-secondary">
                                        <i class="bi bi-info-circle me-1"></i>
                                        This is your public school address on Eduboard.
                                    </div>
                                </div>
                            </div>

                            {{-- Info/Credentials Section --}}
                            <div class="col-md-6">
                                <div class="border border-secondary border-opacity-10 rounded-3 p-4 h-100 d-flex flex-column justify-content-center bg-body">
                                    <div class="text-center mb-3">
                                        <div class="bg-success bg-opacity-10 d-inline-flex p-3 rounded-circle mb-3">
                                            <i class="bi bi-shield-check fs-1 text-success"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2">Secure Access Enabled</h5>
                                        <p class="small text-secondary px-3">
                                            Use your central account credentials to log in to your school domain, or simply click the <strong>Visit</strong> button to access your dashboard automatically.
                                        </p>
                                    </div>
                                    
                                    <div class="mt-auto pt-3 border-top border-secondary border-opacity-25">
                                        <div class="d-flex justify-content-between align-items-center small mb-2">
                                            <span class="text-secondary">Subscription Status:</span>
                                            <span class="text-success fw-bold">Active</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center small">
                                            <span class="text-secondary">Next Renewal:</span>
                                            <span class="fw-bold">
                                                @if(auth()->user()->trial_ends_at)
                                                    {{ \Carbon\Carbon::parse(auth()->user()->trial_ends_at)->format('M d, Y') }}
                                                @else
                                                    {{ now()->addMonth()->format('M d, Y') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
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
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-secondary bg-opacity-10 py-3 border-bottom-0">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-link-45deg me-2 text-success"></i>Custom Domain
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info bg-info bg-opacity-10 border-0 d-flex align-items-center gap-3 mb-4">
                        <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 text-info"></i>
                        <div class="text-info">
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
