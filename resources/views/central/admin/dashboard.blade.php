@extends('central.layouts.admin-layout')

@section('page-title', 'Dashboard')

@section('content')
<!-- Success Message -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-medium">Total Schools</span>
                    <i class="bi bi-building text-secondary"></i>
                </div>
                <div class="h3 fw-bold mb-1">{{ $totalSchools }}</div>
                <div class="d-flex align-items-center gap-1 small text-success">
                    <i class="bi bi-arrow-up-short"></i>
                    <span>+12% from last month</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <a href="{{ route('central.admin.users') }}" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-secondary small fw-medium text-uppercase">Active Users</span>
                        <i class="bi bi-people text-secondary"></i>
                    </div>
                    <div class="h3 fw-bold mb-1">{{ $activeUsers }}</div>
                    <div class="d-flex align-items-center gap-1 small text-success">
                        <i class="bi bi-arrow-up-short"></i>
                        <span>+8.2% from last month</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-medium">Revenue (Monthly)</span>
                    <i class="bi bi-credit-card text-secondary"></i>
                </div>
                <div class="h3 fw-bold mb-1">₱38,450</div>
                <div class="d-flex align-items-center gap-1 small text-success">
                    <i class="bi bi-arrow-up-short"></i>
                    <span>+15% from last month</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-medium">Growth Rate</span>
                    <i class="bi bi-graph-up text-secondary"></i>
                </div>
                <div class="h3 fw-bold mb-1">23%</div>
                <div class="d-flex align-items-center gap-1 small text-danger">
                    <i class="bi bi-arrow-down-short"></i>
                    <span>-2% from last month</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tenants Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h5 class="fw-semibold mb-0">Recent Tenants</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-secondary small">
                            <tr>
                                <th class="fw-medium">School</th>
                                <th class="fw-medium">Plan</th>
                                <th class="fw-medium">Users</th>
                                <th class="fw-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTenants as $tenant)
                            <tr>
                                <td class="fw-medium">{{ $tenant->data['school_name'] ?? $tenant->id }}</td>
                                <td>
                                    @php
                                        $plan = $tenant->data['plan'] ?? 'Basic';
                                        $planClass = match($plan) {
                                            'Ultimate' => 'bg-warning bg-opacity-10 text-warning',
                                            'Pro' => 'bg-success bg-opacity-10 text-success',
                                            default => 'bg-secondary bg-opacity-10 text-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $planClass }} px-3 py-2 rounded-pill">{{ $plan }}</span>
                                </td>
                                <td class="text-secondary">{{ $tenant->data['user_count'] ?? 0 }}</td>
                                <td>
                                    @php
                                        $status = $tenant->data['status'] ?? 'Active';
                                        $statusClass = $status === 'Active' ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning';
                                    @endphp
                                    <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">{{ $status }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



