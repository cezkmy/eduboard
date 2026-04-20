@extends('central.layouts.user-layout')

@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="bi bi-building fs-1 text-success"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-1">{{ auth()->user()->school_name }}</h2>
                            <p class="text-secondary mb-0">Welcome back, {{ auth()->user()->name }}!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $currentPlan = auth()->user()->tenant?->plan ?? auth()->user()->plan ?? 'Basic';
        $subscriptionStatus = strtolower(auth()->user()->tenant?->status ?? auth()->user()->status ?? '');
        $isTrial = $subscriptionStatus === 'trial';
    @endphp

    <!-- Trial Banner (with null check) -->
    @if($isTrial)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-star-fill text-warning fs-4 me-3"></i>
                    <div>
                        <strong>Trial Period:</strong> 
                        @if(auth()->user()->trial_ends_at)
                            Your trial ends on {{ \Carbon\Carbon::parse(auth()->user()->trial_ends_at)->format('F d, Y') }}. 
                            <span class="fw-bold">{{ \Carbon\Carbon::parse(auth()->user()->trial_ends_at)->diffForHumans() }} remaining.</span>
                        @else
                            You are on a free trial.
                        @endif
                    </div>
                    <a href="{{ route('central.user.subscription') }}" class="btn btn-sm btn-outline-light ms-auto">
                        Upgrade Now
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Plan Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="fw-bold mb-2">Current Plan: <span class="badge bg-white text-success">{{ $currentPlan }} @if($isTrial)(Trial)@endif</span></h4>
                            <p class="mb-0 opacity-75">
                                @if($isTrial)
                                    Free for 1 month • Select 1 free template • Domain Management after template selection
                                @else
                                    Active Subscription
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('central.user.subscription') }}" class="btn btn-light">
                                <i class="bi bi-arrow-up-circle me-2"></i>
                                @if(auth()->user()->status === 'trial')
                                    Upgrade Plan
                                @else
                                    Manage Plan
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-secondary small fw-medium">Template Status</span>
                        <i class="bi bi-files text-secondary"></i>
                    </div>
                    @if(auth()->user()->has_selected_template)
                        <div class="h3 fw-bold mb-1 text-success">Selected</div>
                        <div class="small text-success">
                            <i class="bi bi-check-circle-fill me-1"></i> 1 template selected
                        </div>
                    @else
                        <div class="h3 fw-bold mb-1 text-warning">Pending</div>
                        <div class="small text-warning">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> No template selected
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-secondary small fw-medium">Domain Access</span>
                        <i class="bi bi-globe2 text-secondary"></i>
                    </div>
                    @if(auth()->user()->has_selected_template)
                        <div class="h3 fw-bold mb-1 text-success">Available</div>
                        <div class="small text-success">
                            <i class="bi bi-check-circle-fill me-1"></i> You can manage domain
                        </div>
                    @else
                        <div class="h3 fw-bold mb-1 text-warning">Locked</div>
                        <div class="small text-warning">
                            <i class="bi bi-lock-fill me-1"></i> Select template first
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-secondary small fw-medium">Trial Days</span>
                        <i class="bi bi-calendar text-secondary"></i>
                    </div>
                    @if(auth()->user()->trial_ends_at)
                        <div class="h3 fw-bold mb-1">{{ max(0, \Carbon\Carbon::parse(auth()->user()->trial_ends_at)->diffInDays(now())) }} days</div>
                        <div class="small text-secondary">
                            <i class="bi bi-clock me-1"></i> Remaining
                        </div>
                    @else
                        <div class="h3 fw-bold mb-1">30 days</div>
                        <div class="small text-secondary">
                            <i class="bi bi-clock me-1"></i> Trial period
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-files fs-2 text-success"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Template</h5>
                    <p class="text-secondary small mb-3">
                        @if(auth()->user()->has_selected_template)
                            You have selected your trial template
                        @else
                            Choose your free template to get started
                        @endif
                    </p>
                    @if(auth()->user()->has_selected_template)
                        <a href="{{ route('central.user.templates') }}" class="btn btn-outline-success w-100">Browse More</a>
                    @else
                        <a href="{{ route('central.user.templates.select') }}" class="btn btn-success w-100">Select Template</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-globe2 fs-2 text-success"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Domain</h5>
                    <p class="text-secondary small mb-3">Configure your school domain</p>
                    @if(auth()->user()->has_selected_template)
                        <a href="{{ route('central.user.domain') }}" class="btn btn-outline-success w-100">Manage Domain</a>
                    @else
                        <button class="btn btn-outline-secondary w-100" disabled>Select Template First</button>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-credit-card fs-2 text-success"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Subscription</h5>
                    <p class="text-secondary small mb-3">View and upgrade your plan</p>
                    <a href="{{ route('central.user.subscription') }}" class="btn btn-outline-success w-100">View Plan</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



