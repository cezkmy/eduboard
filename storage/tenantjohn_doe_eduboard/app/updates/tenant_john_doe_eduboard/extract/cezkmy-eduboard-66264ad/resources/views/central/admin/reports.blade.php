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
                <button type="button" class="btn btn-outline-success d-flex align-items-center gap-2" onclick="downloadReport('revenue')">
                    <i class="bi bi-download"></i> Download Revenue Report
                </button>
                <button type="button" class="btn btn-success d-flex align-items-center gap-2" onclick="downloadReport('tenant')">
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

    <!-- Analytics Charts -->
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0">Revenue Trend (Last 6 months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0">New Tenants (Last 6 months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="tenantTrendChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function downloadReport(category) {
        const url = "{{ route('central.admin.reports.download', ['category' => '__category__']) }}".replace('__category__', category);
        window.location.href = url;
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const months = @json($months ?? []);
        const revenueTrend = @json($revenueTrend ?? []);
        const tenantTrend = @json($tenantTrend ?? []);

        if (document.getElementById('revenueTrendChart')) {
            const revenueCtx = document.getElementById('revenueTrendChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Revenue (Paid)',
                        data: revenueTrend,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        if (document.getElementById('tenantTrendChart')) {
            const tenantCtx = document.getElementById('tenantTrendChart').getContext('2d');
            new Chart(tenantCtx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'New Tenants',
                        data: tenantTrend,
                        backgroundColor: 'rgba(16, 185, 129, 0.35)',
                        borderColor: '#10b981',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    });
</script>
@endpush
@endsection
