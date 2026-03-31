@extends('central.layouts.admin-layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Plans</h2>
                <p class="text-secondary">Manage subscription plans for schools.</p>
            </div>
            <button class="btn btn-success d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> New Plan
            </button>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="row g-4">
        @foreach($plans as $plan)
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header pt-4 px-4 d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="fw-bold mb-2">{{ $plan->name }}</h4>
                        <div class="d-flex align-items-baseline gap-1 mb-3">
                            <span class="h2 fw-bold mb-0">{{ $plan->price }}</span>
                            @if($plan->period)
                                <span class="text-secondary small">{{ $plan->period }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-link text-secondary p-1" data-bs-toggle="modal" data-bs-target="#editPlanModal{{ $plan->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body px-4">
                    <div class="bg-secondary bg-opacity-10 rounded-3 px-3 py-2 mb-4">
                        <span class="text-secondary small">{{ \App\Models\Tenant::where('plan', $plan->name)->count() }} schools subscribed</span>
                    </div>
                    
                    <ul class="list-unstyled">
                        @foreach($plan->features as $feature)
                        <li class="d-flex align-items-center gap-2 mb-2 text-secondary small">
                            <i class="bi bi-check-lg text-success"></i>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Modals -->
    @foreach($plans as $plan)
    <div class="modal fade" id="editPlanModal{{ $plan->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('central.admin.plans.update', $plan->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit {{ $plan->name }} Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Price</label>
                            <input type="text" class="form-control" name="price" value="{{ $plan->price }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Period / Suffix</label>
                            <input type="text" class="form-control" name="period" value="{{ $plan->period }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Features (One per line)</label>
                            <textarea class="form-control" name="features" rows="6" required>{{ implode("\n", $plan->features) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endsection




