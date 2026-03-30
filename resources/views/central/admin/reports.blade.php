@extends('central.layouts.admin-layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-1">Reports</h2>
            <p class="text-secondary">Revenue and tenant analytics.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-semibold mb-1">Revenue Report</h5>
                    <p class="text-secondary small mb-3">Monthly and annual revenue breakdown.</p>
                    <div class="h2 fw-bold text-success mb-1">₱38,450</div>
                    <p class="text-secondary small">This month</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-semibold mb-1">Tenant Report</h5>
                    <p class="text-secondary small mb-3">Tenant growth and activity metrics.</p>
                    <div class="h2 fw-bold text-success mb-1">127</div>
                    <p class="text-secondary small">Active schools</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



