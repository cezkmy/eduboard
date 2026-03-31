@extends('central.layouts.admin-layout')

@section('page-title', 'Template Management')

@section('content')
<div class="container-fluid py-4">
    <!-- Top Action actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <p class="text-secondary mb-0">Manage and preview templates available for schools.</p>
        <button class="btn btn-success d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> Add New Template
        </button>
    </div>

    <!-- Template Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="h3 fw-bold mb-1">7</div>
                    <div class="text-secondary small">Total Templates</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="h3 fw-bold mb-1 text-success">4</div>
                    <div class="text-secondary small">Free Templates</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="h3 fw-bold mb-1 text-warning">3</div>
                    <div class="text-secondary small">Premium Templates</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates List -->
    <div class="row g-4">
        @php
            $templates = [
                ['id' => 1, 'name' => 'School Event Template', 'category' => 'Events', 'type' => 'Free', 'status' => 'Active', 'usage' => 45],
                ['id' => 2, 'name' => 'Academic Announcement', 'category' => 'Academic', 'type' => 'Free', 'status' => 'Active', 'usage' => 32],
                ['id' => 3, 'name' => 'Holiday Notice', 'category' => 'Holidays', 'type' => 'Free', 'status' => 'Active', 'usage' => 18],
                ['id' => 4, 'name' => 'Modern Admin Portal', 'category' => 'Management', 'type' => 'Free', 'status' => 'Active', 'usage' => 12],
                ['id' => 5, 'name' => 'Academic Calendar', 'category' => 'Premium', 'type' => 'Premium', 'status' => 'Active', 'usage' => 8],
                ['id' => 6, 'name' => 'Student Portal', 'category' => 'Premium', 'type' => 'Premium', 'status' => 'Active', 'usage' => 5],
                ['id' => 7, 'name' => 'Staff Directory', 'category' => 'Premium', 'type' => 'Premium', 'status' => 'Active', 'usage' => 3],
            ];
        @endphp

        @foreach($templates as $template)
        <div class="col-md-4 col-lg-3">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-file-text fs-3 text-primary"></i>
                        </div>
                        <span class="badge {{ $template['type'] == 'Free' ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning' }}">
                            {{ $template['type'] }}
                        </span>
                    </div>
                    
                    <h5 class="fw-bold mb-1">{{ $template['name'] }}</h5>
                    <p class="text-secondary small mb-3">{{ $template['category'] }}</p>
                    
                    <div class="d-flex justify-content-between align-items-center small mb-4">
                        <span class="text-secondary">
                            <i class="bi bi-person-check me-1"></i> {{ $template['usage'] }} uses
                        </span>
                        <span class="text-success">
                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> {{ $template['status'] }}
                        </span>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm flex-grow-1">
                            <i class="bi bi-eye me-1"></i> Preview
                        </button>
                        <button class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection




