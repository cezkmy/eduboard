@extends('central.layouts.user-layout')

@section('page-title', 'Templates')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-1">Template Marketplace</h2>
            <p class="text-secondary">Browse and purchase templates</p>
        </div>
    </div>

    @if(!auth()->user()->has_selected_template)
        <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25 text-warning mb-4">
            <i class="bi bi-info-circle me-2"></i>
            You haven't selected your free trial template yet. 
            <a href="{{ route('central.user.templates.select') }}" class="alert-link text-warning fw-bold">Click here to select</a>
        </div>
    @endif

    <div class="row g-4">
        @php
            $templates = [
                ['id' => 1, 'name' => 'School Event Template', 'price' => '₱49', 'popular' => true],
                ['id' => 2, 'name' => 'Graduation Ceremony Pack', 'price' => '₱79', 'popular' => true],
                ['id' => 3, 'name' => 'Exam Schedule Template', 'price' => '₱39', 'popular' => false],
                ['id' => 4, 'name' => 'Holiday Notice Collection', 'price' => '₱59', 'popular' => true],
            ];
        @endphp

        @foreach($templates as $template)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                @if($template['popular'])
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge bg-success">Popular</span>
                </div>
                @endif
                <div class="card-body p-4">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-file-text fs-2 text-success"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">{{ $template['name'] }}</h5>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="h5 fw-bold text-success mb-0">{{ $template['price'] }}</span>
                        <button class="btn btn-sm btn-success">
                            <i class="bi bi-bag me-1"></i>Buy
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection



