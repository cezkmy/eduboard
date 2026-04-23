@extends('central.layouts.admin-layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Tenants</h2>
                <p class="text-secondary">Manage all registered schools.</p>
            </div>

        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="h3 fw-bold mb-1">{{ $tenants->count() }}</div>
                    <div class="text-secondary small">Total Schools</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="h3 fw-bold mb-1 text-success">{{ $activeCount }}</div>
                    <div class="text-secondary small">Active</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="h3 fw-bold mb-1 text-danger">{{ $deactivatedCount }}</div>
                    <div class="text-secondary small">Deactivated</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="card">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0">All Schools</h5>
                <div class="d-flex gap-2 col-md-6 justify-content-end">
                    <div class="input-group w-auto">
                        <span class="input-group-text bg-secondary bg-opacity-10 border-0">
                            <i class="bi bi-filter text-secondary"></i>
                        </span>
                        <select class="form-select border-0 bg-secondary bg-opacity-10 text-secondary fw-medium custom-select-dark" id="planFilter" onchange="applyFilters()" style="min-width: 140px; cursor: pointer;">
                            <option value="all">All Plans</option>
                            <option value="Basic">Basic</option>
                            <option value="Pro">Pro</option>
                            <option value="Ultimate">Ultimate</option>
                        </select>
                    </div>
                    <div class="input-group w-50">
                        <span class="input-group-text bg-secondary bg-opacity-10 border-0">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" class="form-control border-0 bg-secondary bg-opacity-10" placeholder="Search schools..." id="searchInput">
                    </div>
                </div>
            </div>
            <ul class="nav nav-tabs card-header-tabs" id="tenantFilters">
                <li class="nav-item">
                    <a class="nav-link active cursor-pointer" style="cursor: pointer;" onclick="filterTenants('all', this)">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link cursor-pointer" style="cursor: pointer;" onclick="filterTenants('active', this)">Active</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger cursor-pointer" style="cursor: pointer;" onclick="filterTenants('deactivated', this)">Deactivated</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tenantsTable">
                    <thead class="text-secondary small">
                        <tr>
                            <th class="fw-medium">School / Admin</th>
                            <th class="fw-medium">Domain</th>
                            <th class="fw-medium">Plan</th>
                            <th class="fw-medium">Storage</th>
                            <th class="fw-medium">Bandwidth</th>
                            <th class="fw-medium">Duration</th>
                            <th class="fw-medium">Status</th>
                            <th class="fw-medium text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                        @php
                            $statusInfo = $tenant->status ?? 'Active';
                            $planInfo = $tenant->plan ?? 'Basic';
                        @endphp
                        <tr class="tenant-row" data-status="{{ strtolower($statusInfo) }}" data-plan="{{ $planInfo }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded text-primary">
                                        <i class="bi bi-building fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold d-block">{{ $tenant->school_name ?? 'Unnamed School' }}</div>
                                        <div class="small text-secondary">
                                            <i class="bi bi-person me-1"></i> {{ $tenant->owner->name ?? 'Unknown Admin' }}
                                        </div>
                                        <a href="#" class="small text-primary text-decoration-none" data-bs-toggle="modal" data-bs-target="#billingModal{{ $tenant->id }}">
                                            <i class="bi bi-receipt me-1"></i> Billing History
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td class="text-secondary small">
                                @php
                                    $domainRecord = $tenant->domains->first();
                                    $domainName = $domainRecord ? $domainRecord->domain : 'N/A';
                                    if ($domainName !== 'N/A') {
                                        // If it's just a subdomain (no dots), append the local suffix
                                        if (strpos($domainName, '.') === false) {
                                            $domainName .= '.localhost:8000';
                                        }
                                    }
                                @endphp
                                @if($domainName !== 'N/A')
                                    <a href="http://{{ $domainName }}" target="_blank" class="text-secondary text-decoration-none hover-primary">
                                        {{ $domainName }} <i class="bi bi-box-arrow-up-right x-small ms-1"></i>
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @php
                                    $plan = $tenant->plan ?? 'Basic';
                                    $planClass = match($plan) {
                                        'Ultimate' => 'bg-warning bg-opacity-10 text-warning',
                                        'Pro' => 'bg-primary bg-opacity-10 text-primary',
                                        default => 'bg-success bg-opacity-10 text-success',
                                    };
                                @endphp
                                <span class="badge {{ $planClass }} px-3 py-2 rounded-pill">{{ $plan }}</span>
                            </td>
                            <td style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#storageModal{{ $tenant->id }}">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1" style="min-width: 80px;">
                                        @php
                                            $used = $tenant->storage_used_gb ?? 0;
                                            $limit = $tenant->storage_limit_gb ?? 5;
                                            $percent = $limit > 0 ? ($used / $limit) * 100 : 0;
                                            $progressClass = $percent > 90 ? 'bg-danger' : ($percent > 70 ? 'bg-warning' : 'bg-success');
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <span class="small text-secondary mt-1 d-block">{{ number_format($used, 1) }} / {{ number_format($limit, 1) }} GB</span>
                                    </div>
                                    <i class="bi bi-pencil-square text-secondary small"></i>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1" style="min-width: 80px;">
                                        @php
                                            $usedBandwidth = $tenant->bandwidth_used_gb ?? ($used * 1.5);
                                            $limitBandwidth = $tenant->bandwidth_limit_gb ?? ($limit * 10);
                                            $percentBandwidth = $limitBandwidth > 0 ? ($usedBandwidth / $limitBandwidth) * 100 : 0;
                                            $progressClassBandwidth = $percentBandwidth > 90 ? 'bg-danger' : ($percentBandwidth > 70 ? 'bg-warning' : 'bg-info');
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar {{ $progressClassBandwidth }}" role="progressbar" style="width: {{ $percentBandwidth }}%"></div>
                                        </div>
                                        <span class="small text-secondary mt-1 d-block">{{ number_format($usedBandwidth, 1) }} / {{ number_format($limitBandwidth, 1) }} GB</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $expires = $tenant->expires_at ? \Carbon\Carbon::parse($tenant->expires_at) : null;
                                    $created = $tenant->created_at ? \Carbon\Carbon::parse($tenant->created_at) : null;
                                @endphp
                                <div class="small">
                                    <div class="text-secondary mb-1">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        {{ $created ? $created->format('M d, Y') : 'Unknown' }}
                                    </div>
                                    @if($expires)
                                        <div class="{{ $expires->isPast() ? 'text-danger fw-bold' : 'text-secondary' }}">
                                            <i class="bi bi-calendar-x me-1"></i>
                                            {{ $expires->format('M d, Y') }}
                                            @if($expires->isPast()) (Expired) @endif
                                        </div>
                                    @else
                                        <div class="text-secondary"><i class="bi bi-calendar-x me-1"></i> No expiry set</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $status = $tenant->status ?? 'Active';
                                    $statusClass = match($status) {
                                        'Active' => 'bg-success bg-opacity-10 text-success',
                                        'Deactivated' => 'bg-danger bg-opacity-10 text-danger',
                                        default => 'bg-warning bg-opacity-10 text-warning',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">{{ $status }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    @if($status !== 'Deactivated')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deactivateModal{{ $tenant->id }}" title="Deactivate">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#activateModal{{ $tenant->id }}" title="Activate">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    @endif
                                    
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#extendModal{{ $tenant->id }}" title="Extend 30 Days">
                                        <i class="bi bi-calendar-plus"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="viewTenantDetails('{{ $tenant->id }}')" title="View Detailed Stats">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-secondary">No schools found in the database.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @foreach($tenants as $tenant)
        <!-- Deactivate Modal -->
        <div class="modal fade" id="deactivateModal{{ $tenant->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('central.admin.tenants.deactivate', $tenant->id) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold text-danger">Confirm Deactivation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to <strong>deactivate</strong> the school <strong>{{ $tenant->school_name ?? 'Unnamed School' }}</strong>? <br><br>
                            This will immediately lock all teachers and students out of their accounts, and the admin will be forced to the subscription page.
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Yes, Deactivate</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Activate Modal -->
        <div class="modal fade" id="activateModal{{ $tenant->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('central.admin.tenants.activate', $tenant->id) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold text-success">Confirm Activation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to <strong>reactivate</strong> the school <strong>{{ $tenant->school_name ?? 'Unnamed School' }}</strong>? <br><br>
                            Note: If their subscription is still expired, reactivating them will not restore system access unless their plan is also extended.
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Yes, Activate</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Extend Modal -->
        <div class="modal fade" id="extendModal{{ $tenant->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('central.admin.tenants.extend', $tenant->id) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold text-primary">Extend Subscription</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Manually extend the subscription for <strong>{{ $tenant->school_name ?? 'Unnamed School' }}</strong>.</p>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Number of Days</label>
                                <div class="input-group">
                                    <input type="number" name="days" class="form-control" value="30" min="1">
                                    <span class="input-group-text">Days</span>
                                </div>
                                <div class="form-text small">This will add days to their current expiry or starting from today if expired.</div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Apply Extension</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Storage Modal -->
        <div class="modal fade" id="storageModal{{ $tenant->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('central.admin.tenants.storage', $tenant->id) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold">Update Storage Limit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Adjust the storage capacity for <strong>{{ $tenant->school_name ?? 'Unnamed School' }}</strong>.</p>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Storage Limit (GB)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="storage_limit_gb" class="form-control" value="{{ $tenant->storage_limit_gb ?? 5.0 }}">
                                    <span class="input-group-text">GB</span>
                                </div>
                                <div class="form-text small">Currently used: {{ number_format($tenant->storage_used_gb ?? 0, 2) }} GB</div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Billing Modal -->
        <div class="modal fade" id="billingModal{{ $tenant->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Billing History - {{ $tenant->school_name ?? 'Unnamed School' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice #</th>
                                        <th>Plan</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $histories = \App\Models\BillingHistory::where('tenant_id', $tenant->id)->latest()->get();
                                    @endphp
                                    @forelse($histories as $history)
                                        <tr>
                                            <td>{{ $history->created_at->format('M d, Y') }}</td>
                                            <td class="fw-medium">{{ $history->invoice_number }}</td>
                                            <td>{{ $history->plan }}</td>
                                            <td>₱{{ number_format($history->amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1">Paid</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-secondary small">No billing records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Detailed Stats Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="detailsModalTitle">School Statistics</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailsModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function viewTenantDetails(id) {
        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        const body = document.getElementById('detailsModalBody');
        const title = document.getElementById('detailsModalTitle');
        
        title.textContent = 'Loading Stats...';
        body.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        modal.show();

        fetch(`/admin/tenants/${id}/details`)
            .then(response => response.json())
            .then(data => {
                title.textContent = `Stats for ${data.school_name}`;
                body.innerHTML = `
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-3 rounded bg-primary bg-opacity-10">
                                <h3 class="fw-bold text-primary mb-0">${data.stats.admins}</h3>
                                <small class="text-secondary text-uppercase fw-bold">Admins</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded bg-success bg-opacity-10">
                                <h3 class="fw-bold text-success mb-0">${data.stats.teachers}</h3>
                                <small class="text-secondary text-uppercase fw-bold">Teachers</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded bg-info bg-opacity-10">
                                <h3 class="fw-bold text-info mb-0">${data.stats.students}</h3>
                                <small class="text-secondary text-uppercase fw-bold">Students</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded bg-secondary bg-opacity-10">
                                <h3 class="fw-bold text-secondary mb-0">${data.stats.total}</h3>
                                <small class="text-secondary text-uppercase fw-bold">Total Users</small>
                            </div>
                        </div>
                    </div>
                `;
            })
            .catch(error => {
                title.textContent = 'Error';
                body.innerHTML = '<div class="alert alert-danger">Failed to load statistics. Please try again.</div>';
            });
    }

    let currentTabStatus = 'all';

    function filterTenants(status, element) {
        currentTabStatus = status;
        
        // Update active tab styling
        document.querySelectorAll('#tenantFilters .nav-link').forEach(el => el.classList.remove('active', 'fw-bold'));
        if(element) element.classList.add('active', 'fw-bold');
        
        applyFilters();
    }

    // Enhance Search
    document.getElementById('searchInput').addEventListener('keyup', applyFilters);

    function applyFilters() {
        let searchText = document.getElementById('searchInput').value.toLowerCase();
        let planFilter = document.getElementById('planFilter').value;
        let tableRows = document.querySelectorAll('#tenantsTable tbody tr.tenant-row');
        
        tableRows.forEach(row => {
            let text = row.textContent.toLowerCase();
            let rowStatus = row.getAttribute('data-status').toLowerCase();
            let rowPlan = row.getAttribute('data-plan');
            
            let matchesSearch = text.includes(searchText);
            let matchesTab = currentTabStatus === 'all' || rowStatus === currentTabStatus;
            let matchesPlan = planFilter === 'all' || rowPlan === planFilter;
            
            row.style.display = matchesSearch && matchesTab && matchesPlan ? '' : 'none';
        });
    }
</script>
@endpush
@endsection



