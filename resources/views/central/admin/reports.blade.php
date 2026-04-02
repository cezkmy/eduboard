@extends('central.layouts.admin-layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Reports & Analytics</h2>
                <p class="text-secondary">Comprehensive overview of revenue and tenant growth.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-success d-flex align-items-center gap-2" onclick="downloadReport('revenue')">
                    <i class="bi bi-download"></i> Download Revenue Report
                </button>
                <button class="btn btn-success d-flex align-items-center gap-2" onclick="downloadReport('tenant')">
                    <i class="bi bi-download"></i> Download Tenant Report
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Revenue Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Revenue Overview</h5>
                            <p class="text-secondary small">Earnings from all subscriptions</p>
                        </div>
                        <div class="bg-success bg-opacity-10 p-2 rounded text-success">
                            <i class="bi bi-currency-dollar fs-4"></i>
                        </div>
                    </div>
                    <div class="row align-items-end">
                        <div class="col">
                            <div class="h1 fw-bold text-success mb-1">₱{{ number_format($totalRevenue, 2) }}</div>
                            <p class="text-secondary small mb-0">Total Lifetime Revenue</p>
                        </div>
                        <div class="col-auto text-end">
                            <div class="h3 fw-bold text-dark mb-1">₱{{ number_format($monthlyRevenue, 2) }}</div>
                            <p class="text-secondary small mb-0">This Month</p>
                        </div>
                    </div>
                    <hr class="my-4 opacity-50">
                    <div class="d-flex justify-content-between small">
                        <span class="text-secondary">Growth from last month</span>
                        <span class="text-success fw-bold">+12.5% <i class="bi bi-arrow-up"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tenant Card -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">Tenant Growth</h5>
                            <p class="text-secondary small">Onboarded schools and status</p>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-2 rounded text-primary">
                            <i class="bi bi-building fs-4"></i>
                        </div>
                    </div>
                    <div class="row align-items-end">
                        <div class="col">
                            <div class="h1 fw-bold text-primary mb-1">{{ $totalTenants }}</div>
                            <p class="text-secondary small mb-0">Total Schools Registered</p>
                        </div>
                        <div class="col-auto text-end">
                            <div class="h3 fw-bold text-success mb-1">{{ $activeTenants }}</div>
                            <p class="text-secondary small mb-0">Active Subscriptions</p>
                        </div>
                    </div>
                    <hr class="my-4 opacity-50">
                    <div class="d-flex justify-content-between small">
                        <span class="text-secondary">New schools this week</span>
                        <span class="text-primary fw-bold">+4 schools</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Placeholder -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0">Monthly Performance</h5>
                </div>
                <div class="card-body py-5 text-center">
                    <div class="opacity-25 mb-3">
                        <i class="bi bi-graph-up-arrow display-1"></i>
                    </div>
                    <h5 class="text-secondary">Performance charts are being generated...</h5>
                    <p class="text-muted small">Charts will display revenue and growth trends once more data is collected.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function downloadReport(category) {
        alert('Preparing ' + category + ' report for download... This will generate a comprehensive CSV/PDF including detailed metrics for ' + (category === 'revenue' ? 'all payments and plan distribution' : 'school growth and usage statistics') + '.');
    }
</script>
@endpush
@endsection
