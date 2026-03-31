@extends('central.layouts.admin-layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Tenants</h2>
                <p class="text-secondary">Manage all registered schools.</p>
            </div>
            <button class="btn btn-success d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Add School
            </button>
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
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text border-end-0">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" placeholder="Search schools..." id="searchInput">
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
                            <th class="fw-medium">School</th>
                            <th class="fw-medium">Domain</th>
                            <th class="fw-medium">Plan</th>
                            <th class="fw-medium">Duration</th>
                            <th class="fw-medium">Status</th>
                            <th class="fw-medium text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                        @php
                            $statusInfo = $tenant->status ?? 'Active';
                        @endphp
                        <tr class="tenant-row" data-status="{{ $statusInfo }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-success bg-opacity-10 p-2 rounded">
                                        <i class="bi bi-building text-success"></i>
                                    </div>
                                    <span class="fw-medium">{{ $tenant->school_name ?? 'Unnamed School' }}</span>
                                </div>
                            </td>
                            <td class="text-secondary">{{ $tenant->domains->first() ? $tenant->domains->first()->domain : 'N/A' }}</td>
                            <td>
                                @php
                                    $plan = $tenant->plan ?? 'Basic';
                                    $planClass = match($plan) {
                                        'Ultimate' => 'bg-warning bg-opacity-10 text-warning',
                                        'Pro' => 'bg-success bg-opacity-10 text-success',
                                        default => 'bg-secondary bg-opacity-10 text-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $planClass }} px-3 py-2 rounded-pill">{{ $plan }}</span>
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
                                        <div class="text-success"><i class="bi bi-infinity me-1"></i> Unlimited</div>
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
                            Are you sure you want to manually extend the subscription for <strong>{{ $tenant->school_name ?? 'Unnamed School' }}</strong> by <strong>30 Days</strong>? <br><br>
                            This will bypass the standard billing mechanism and reactivate their system access immediately if they are currently locked out.
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Yes, Extend 30 Days</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
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
        let tableRows = document.querySelectorAll('#tenantsTable tbody tr.tenant-row');
        
        tableRows.forEach(row => {
            let text = row.textContent.toLowerCase();
            let rowStatus = row.getAttribute('data-status').toLowerCase();
            
            let matchesSearch = text.includes(searchText);
            let matchesTab = currentTabStatus === 'all' || rowStatus === currentTabStatus;
            
            row.style.display = matchesSearch && matchesTab ? '' : 'none';
        });
    }
</script>
@endpush
@endsection



