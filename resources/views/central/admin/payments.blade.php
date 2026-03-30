@extends('central.layouts.admin-layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-1">Payments</h2>
            <p class="text-secondary">Track all subscription payments.</p>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="text-secondary small">
                        <tr>
                            <th class="fw-medium">ID</th>
                            <th class="fw-medium">School</th>
                            <th class="fw-medium">Plan</th>
                            <th class="fw-medium">Amount</th>
                            <th class="fw-medium">Date</th>
                            <th class="fw-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $payments = [
                                ['id' => 'PAY-001', 'school' => 'Manila Science HS', 'amount' => '₱199', 'plan' => 'Pro', 'date' => '2026-03-15', 'status' => 'Paid'],
                                ['id' => 'PAY-002', 'school' => 'Cebu International Academy', 'amount' => '₱299', 'plan' => 'Ultimate', 'date' => '2026-03-14', 'status' => 'Paid'],
                                ['id' => 'PAY-003', 'school' => 'Baguio City High', 'amount' => '₱199', 'plan' => 'Pro', 'date' => '2026-03-12', 'status' => 'Pending'],
                                ['id' => 'PAY-004', 'school' => 'Zamboanga Academy', 'amount' => '₱299', 'plan' => 'Ultimate', 'date' => '2026-03-10', 'status' => 'Paid'],
                            ];
                        @endphp

                        @foreach($payments as $payment)
                        <tr>
                            <td class="fw-medium">{{ $payment['id'] }}</td>
                            <td>{{ $payment['school'] }}</td>
                            <td>
                                @php
                                    $planClass = $payment['plan'] === 'Ultimate' ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success';
                                @endphp
                                <span class="badge {{ $planClass }} px-3 py-2 rounded-pill">{{ $payment['plan'] }}</span>
                            </td>
                            <td class="fw-medium">{{ $payment['amount'] }}</td>
                            <td class="text-secondary">{{ $payment['date'] }}</td>
                            <td>
                                @php
                                    $statusClass = $payment['status'] === 'Paid' ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning';
                                @endphp
                                <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">{{ $payment['status'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection



