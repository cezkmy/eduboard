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
                ['id' => 1, 'name' => 'Blue Landing Layout', 'price' => 'Free', 'popular' => true, 'circle_color' => '#0d6efd', 'circle_bg' => 'rgba(13,110,253,0.10)', 'icon' => 'bi bi-globe2'],
                ['id' => 2, 'name' => 'Green Landing Layout', 'price' => 'Free', 'popular' => true, 'circle_color' => '#198754', 'circle_bg' => 'rgba(25,135,84,0.10)', 'icon' => 'bi bi-leaf-fill'],
                ['id' => 3, 'name' => 'Pink Landing Layout', 'price' => 'Free', 'popular' => true, 'circle_color' => '#6b21a8', 'circle_bg' => 'rgba(107,33,168,0.10)', 'icon' => 'bi bi-heart-fill'],
                ['id' => 5, 'name' => 'Yellow Landing Layout', 'price' => 'Free', 'popular' => false, 'circle_color' => '#facc15', 'circle_bg' => 'rgba(250,204,21,0.14)', 'icon' => 'bi bi-sun-fill'],
                ['id' => 6, 'name' => 'Orange Landing Layout', 'price' => 'Free', 'popular' => false, 'circle_color' => '#f97316', 'circle_bg' => 'rgba(249,115,22,0.12)', 'icon' => 'bi bi-fire'],
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
                    <div
                        class="p-3 rounded-circle d-inline-block mb-3"
                        style="background-color: {{ $template['circle_bg'] }}; color: {{ $template['circle_color'] }};">
                        <i class="bi {{ $template['icon'] }} fs-2" style="color: {{ $template['circle_color'] }};"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">{{ $template['name'] }}</h5>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="h5 fw-bold text-success mb-0">{{ $template['price'] }}</span>
                        <a href="{{ route('central.user.templates.select') }}?template_id={{ $template['id'] }}" class="btn btn-sm btn-success">
                            <i class="bi bi-check-circle me-1"></i>Select
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection



