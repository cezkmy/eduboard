@extends('central.layouts.admin-layout')

@section('content')
<style>
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
    .input-group-custom .form-control {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding-top: 0.6rem;
        padding-bottom: 0.6rem;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    #reportInfoModal {
        backdrop-filter: blur(5px);
        background: rgba(0, 0, 0, 0.2);
    }
    
    #reportInfoModal .modal-content {
        border: none;
        border-radius: 1rem;
    }
</style>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Payments & Billing History</h2>
                <p class="text-secondary">Track all subscription payments and generate reports.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-success d-flex align-items-center gap-2" onclick="generateReport('monthly')">
                    <i class="bi bi-file-earmark-pdf"></i> Monthly Report
                </button>
                <button class="btn btn-success d-flex align-items-center gap-2" onclick="generateReport('yearly')">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Yearly Report
                </button>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="fw-semibold mb-0">Transaction History</h5>
                </div>
                <div class="col-auto d-flex gap-2">
                    <div class="input-group-custom d-flex align-items-center" style="width: 180px;">
                        <span class="input-group-text ps-3">
                            <i class="bi bi-funnel text-secondary"></i>
                        </span>
                        <select class="form-select text-secondary small" id="planFilter" onchange="filterPayments()">
                            <option value="all">All Plans</option>
                            <option value="Basic">Basic</option>
                            <option value="Pro">Pro</option>
                            <option value="Ultimate">Ultimate</option>
                        </select>
                    </div>
                    <div class="input-group-custom d-flex align-items-center" style="width: 300px;">
                        <span class="input-group-text ps-3">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search invoices or schools..." id="paymentSearch" onkeyup="filterPayments()">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="paymentsTable">
                    <thead class="bg-secondary bg-opacity-10 text-secondary small">
                        <tr>
                            <th class="ps-4 fw-medium cursor-pointer" onclick="sortTable(0)">Invoice # <i class="bi bi-arrow-down-up x-small ms-1"></i></th>
                            <th class="fw-medium cursor-pointer" onclick="sortTable(1)">School / Tenant <i class="bi bi-arrow-down-up x-small ms-1"></i></th>
                            <th class="fw-medium cursor-pointer" onclick="sortTable(2)">Plan <i class="bi bi-arrow-down-up x-small ms-1"></i></th>
                            <th class="fw-medium cursor-pointer" onclick="sortTable(3)">Amount <i class="bi bi-arrow-down-up x-small ms-1"></i></th>
                            <th class="fw-medium cursor-pointer" onclick="sortTable(4)">Date Paid <i class="bi bi-arrow-down-up x-small ms-1"></i></th>
                            <th class="fw-medium">Status</th>
                            <th class="pe-4 text-end fw-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr class="payment-row" data-plan="{{ $payment->plan }}">
                            <td class="ps-4 fw-bold text-dark">{{ $payment->invoice_number }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded me-2 text-primary">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $payment->tenant->school_name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $payment->tenant_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $planClass = match($payment->plan) {
                                        'Ultimate' => 'bg-warning text-warning',
                                        'Pro' => 'bg-primary text-primary',
                                        default => 'bg-success text-success',
                                    };
                                @endphp
                                <span class="badge {{ $planClass }} bg-opacity-10 px-3 py-2 rounded-pill">
                                    {{ $payment->plan }}
                                </span>
                            </td>
                            <td class="fw-bold">₱{{ number_format($payment->amount, 2) }}</td>
                            <td class="text-secondary small">{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('M d, Y h:i A') : 'N/A' }}</td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                    <i class="bi bi-check-circle-fill me-1"></i> {{ ucfirst($payment->payment_status) }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-light border-0 bg-secondary bg-opacity-10" title="Download Invoice">
                                    <i class="bi bi-download"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="bi bi-cash-stack fs-1 d-block mb-3 opacity-25"></i>
                                No payment records found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Custom Info Modal for Reports -->
<div class="modal fade" id="reportInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-body p-4 text-center">
                <div class="mb-3 text-success">
                    <i class="bi bi-file-earmark-check-fill fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2">Generating Report</h5>
                <p class="text-secondary mb-4" id="reportModalMessage">Preparing your report for download...</p>
                
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-success px-5 rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const reportInfoModal = new bootstrap.Modal(document.getElementById('reportInfoModal'));

    function generateReport(type) {
        const message = 'Generating ' + type + ' payment report... This will export a detailed ' + (type === 'monthly' ? 'PDF' : 'CSV') + ' file of all successful transactions.';
        document.getElementById('reportModalMessage').textContent = message;
        reportInfoModal.show();
        
        // In a real app, this would redirect to a route that streams a PDF/CSV
        // setTimeout(() => { window.location.href = `/admin/payments/report/${type}`; }, 1500);
    }

    function sortTable(n) {
        let table = document.getElementById("paymentsTable");
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
                
                // Special handling for Amount (strip ₱ and comma)
                if (n === 3) {
                    xContent = parseFloat(x.innerText.replace(/[₱,]/g, ''));
                    yContent = parseFloat(y.innerText.replace(/[₱,]/g, ''));
                }

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

    function filterPayments() {
        let searchValue = document.getElementById('paymentSearch').value.toLowerCase();
        let planFilter = document.getElementById('planFilter').value;
        let rows = document.querySelectorAll('#paymentsTable tbody tr.payment-row');
        
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            let plan = row.getAttribute('data-plan');
            
            let matchesSearch = text.includes(searchValue);
            let matchesPlan = (planFilter === 'all' || plan === planFilter);
            
            row.style.display = (matchesSearch && matchesPlan) ? '' : 'none';
        });
    }
</script>
@endpush
@endsection
