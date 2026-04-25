@extends('central.layouts.admin-layout')

@section('content')
<style>
    #customConfirmModal {
        backdrop-filter: blur(5px);
        background: rgba(0, 0, 0, 0.2);
    }
    
    #customConfirmModal .modal-content {
        border: none;
        border-radius: 1rem;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .input-group-custom {
        background: rgba(108, 117, 125, 0.1);
        border-radius: 0.75rem;
        transition: all 0.2s ease;
        overflow: hidden;
    }

    .input-group-custom:focus-within {
        background: rgba(108, 117, 125, 0.15);
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    .input-group-custom .input-group-text, 
    .input-group-custom .form-control, 
    .input-group-custom .form-select {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding-top: 0.6rem;
        padding-bottom: 0.6rem;
    }

    .input-group-custom .form-select {
        cursor: pointer;
    }
</style>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Users</h2>
                <p class="text-secondary">Manage all central users.</p>
            </div>
            <div class="col-md-7 d-flex gap-2">
                <div class="input-group-custom flex-grow-1 d-flex align-items-center">
                    <span class="input-group-text ps-3">
                        <i class="bi bi-search text-secondary"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Search users by name or email..." id="userSearchInput" onkeyup="filterUsers()">
                </div>
                
                <div class="input-group-custom d-flex align-items-center" style="width: 180px;">
                    <span class="input-group-text ps-3">
                        <i class="bi bi-funnel text-secondary"></i>
                    </span>
                    <select class="form-select text-secondary small" id="roleFilter" onchange="filterUsers()">
                        <option value="all">All Roles</option>
                        <option value="Admin">Admin</option>
                        <option value="User">User</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header py-3">
            <h5 class="fw-semibold mb-0">All Users</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="usersTable">
                    <thead class="text-secondary small">
                        <tr>
                            <th class="fw-medium cursor-pointer" onclick="sortTable(0)">User <i class="bi bi-arrow-down-up x-small ms-1"></i></th>
                            <th class="fw-medium cursor-pointer" onclick="sortTable(1)">Role <i class="bi bi-arrow-down-up x-small ms-1"></i></th>
                            <th class="fw-medium cursor-pointer" onclick="sortTable(2)">Handled Domain <i class="bi bi-arrow-down-up x-small ms-1"></i></th>
                            <th class="fw-medium cursor-pointer" onclick="sortTable(3)">Joined <i class="bi bi-arrow-down-up x-small ms-1"></i></th>
                            <th class="fw-medium text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="user-row" data-role="{{ $user->is_admin ? 'Admin' : 'User' }}">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar overflow-hidden" style="width: 38px; height: 38px; background: var(--primary); color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                                        @if($user->profile_photo)
                                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Profile" class="w-full h-full object-cover" onerror="this.style.display='none'; this.parentElement.innerText='{{ substr($user->name, 0, 1) }}'">
                                        @else
                                            {{ substr($user->name, 0, 1) }}
                                        @endif
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block text-dark">{{ $user->name }}</span>
                                        <small class="text-secondary">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $roleLabel = $user->is_admin ? 'Admin' : 'User';
                                    $roleClass = $user->is_admin ? 'bg-primary bg-opacity-10 text-primary' : 'bg-info bg-opacity-10 text-info';
                                @endphp
                                <span class="badge {{ $roleClass }} px-3 py-2 rounded-pill">{{ $roleLabel }}</span>
                            </td>
                            <td>
                                @if($user->school_domain)
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium text-success small">{{ $user->school_domain }}</span>
                                        <a href="#" class="x-small text-primary text-decoration-none mt-1" onclick="viewUserBilling('{{ $user->id }}', '{{ $user->name }}')">
                                            <i class="bi bi-receipt me-1"></i> View Billing
                                        </a>
                                    </div>
                                @else
                                    <span class="text-muted small italic">No domain handled</span>
                                @endif
                            </td>
                            <td class="text-secondary small">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modals for User Management -->
    @foreach($users as $user)
    <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('central.admin.profile.update', ['id' => $user->id]) }}" method="POST" id="editUserForm{{ $user->id }}">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Edit User: {{ $user->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        <div class="alert alert-warning small d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill fs-5"></i>
                            <span>Security Note: Changing user credentials will affect their login access. Proceed with caution.</span>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold d-flex justify-content-between align-items-center">
                                <span>New Password (Leave blank to keep current)</span>
                                <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none p-0" onclick="generateRandomPass('passInput{{ $user->id }}')">
                                    <i class="bi bi-magic me-1"></i>Generate Password
                                </button>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="passInput{{ $user->id }}">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePass('passInput{{ $user->id }}')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="confirmUpdate('{{ $user->id }}')">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    <!-- Custom Confirmation Modal -->
    <div class="modal fade" id="customConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Confirm Action</h5>
                    <p class="text-secondary mb-4">This is an important action. Are you sure you want to save the changes to the user profile? This action cannot be undone.</p>
                    
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary px-4" id="confirmSubmitBtn">Yes, Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing History Modal -->
    <div class="modal fade" id="userBillingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="userBillingTitle">Billing History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="userBillingBody">
                    <!-- Loaded via JS -->
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
    let currentFormId = null;
    const customConfirmModal = new bootstrap.Modal(document.getElementById('customConfirmModal'));

    function confirmUpdate(userId) {
        currentFormId = `editUserForm${userId}`;
        customConfirmModal.show();
    }

    document.getElementById('confirmSubmitBtn').addEventListener('click', function() {
        if (currentFormId) {
            document.getElementById(currentFormId).submit();
        }
    });

    function filterUsers() {
        let input = document.getElementById('userSearchInput');
        let filter = input.value.toLowerCase();
        let roleFilter = document.getElementById('roleFilter').value;
        let rows = document.querySelectorAll('#usersTable tbody tr.user-row');

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            let role = row.getAttribute('data-role');
            
            let matchesSearch = text.includes(filter);
            let matchesRole = (roleFilter === 'all' || role === roleFilter);
            
            row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
        });
    }

    function sortTable(n) {
        let table = document.getElementById("usersTable");
        let rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
        switching = true;
        dir = "asc"; 
        
        while (switching) {
            switching = false;
            rows = table.rows;
            for (i = 1; i < (rows.length - 1); i++) {
                shouldSwitch = false;
                x = rows[i].getElementsByTagName("TD")[n];
                y = rows[i + 1].getElementsByTagName("TD")[n];
                
                let xContent = x.innerText.toLowerCase();
                let yContent = y.innerText.toLowerCase();
                
                if (dir == "asc") {
                    if (xContent > yContent) {
                        shouldSwitch = true;
                        break;
                    }
                } else if (dir == "desc") {
                    if (xContent < yContent) {
                        shouldSwitch = true;
                        break;
                    }
                }
            }
            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
                switchcount ++;      
            } else {
                if (switchcount == 0 && dir == "asc") {
                    dir = "desc";
                    switching = true;
                }
            }
        }
    }

    function togglePass(id) {
        let input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    function generateRandomPass(inputId) {
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
        let pass = "";
        for (let i = 0; i < 12; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        const input = document.getElementById(inputId);
        input.value = pass;
        input.type = 'text'; // Show the password so they can see it
        
        // Show a brief notification or alert if you want, but filling the input is usually enough
    }

    function viewUserBilling(userId, userName) {
        const modal = new bootstrap.Modal(document.getElementById('userBillingModal'));
        const body = document.getElementById('userBillingBody');
        const title = document.getElementById('userBillingTitle');
        
        title.textContent = `Billing History - ${userName}`;
        body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
        modal.show();

        // We'll fetch the billing history via AJAX
        fetch(`/admin/users/${userId}/billing`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    body.innerHTML = '<div class="text-center py-4 text-secondary">No billing records found for this user.</div>';
                    return;
                }
                
                let tableHtml = `
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
                `;
                
                data.forEach(item => {
                    tableHtml += `
                        <tr>
                            <td>${new Date(item.created_at).toLocaleDateString()}</td>
                            <td class="fw-medium">${item.invoice_number}</td>
                            <td>${item.plan}</td>
                            <td>₱${parseFloat(item.amount).toLocaleString()}</td>
                            <td><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1">Paid</span></td>
                        </tr>
                    `;
                });
                
                tableHtml += '</tbody></table></div>';
                body.innerHTML = tableHtml;
            })
            .catch(() => {
                body.innerHTML = '<div class="alert alert-danger">Failed to load billing history.</div>';
            });
    }
</script>
@endpush
@endsection
