@extends('central.layouts.user-layout')

@section('page-title', 'Subscription')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-1">Manage Subscription</h2>
            <p class="text-secondary">Upgrade your plan</p>
        </div>
    </div>

    <!-- Current Plan -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="fw-bold mb-2">Current Plan: 
                                <span class="badge bg-white text-success">
                                    {{ auth()->user()->plan ?? 'Basic' }} 
                                    @if(auth()->user()->status === 'trial')(Trial)@endif
                                </span>
                            </h4>
                            <p class="mb-0 opacity-75">
                                @if(auth()->user()->status === 'trial' && auth()->user()->trial_ends_at)
                                    Free Trial • Ends {{ \Carbon\Carbon::parse(auth()->user()->trial_ends_at)->format('F d, Y') }}
                                @elseif(auth()->user()->status === 'trial')
                                    Free Trial • No end date set
                                @else
                                    Active Subscription
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-white text-success py-2 px-3">
                                {{ auth()->user()->status === 'trial' ? 'Trial' : 'Active' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Plans -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-semibold mb-3">Upgrade Options</h5>
        </div>
    </div>

    <div class="row g-4">
        @foreach($plans as $plan)
        @php
            // Temporarily mark the Pro plan as popular for styling
            $isPopular = $plan->name === 'Pro';
            // Current user plan logic
            $isCurrent = (auth()->user()->plan ?? 'Basic') === $plan->name;
        @endphp
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 {{ $isPopular ? 'border-success border-2' : '' }}">
                @if($isPopular)
                <div class="position-absolute top-0 start-50 translate-middle-x mt-2">
                    <span class="badge bg-success">Most Popular</span>
                </div>
                @endif
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <h4 class="fw-bold mb-2 text-uppercase text-secondary" style="font-size: 0.9rem; letter-spacing: 1px;">{{ $plan->name }}</h4>
                        @if($isCurrent)
                            <span class="badge bg-success bg-opacity-10 text-success">ACTIVE</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-baseline gap-1 mb-4 mt-2">
                        <span class="h2 fw-bold text-dark mb-0">{{ $plan->price }}</span>
                        <span class="text-secondary fw-medium">{{ $plan->period ?? '/month' }}</span>
                    </div>
                    
                    <ul class="list-unstyled mb-4 text-secondary">
                        @foreach($plan->features as $feature)
                        <li class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="fw-medium">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    
                    <div class="mt-auto pt-3">
                        @if($isCurrent)
                            <button class="btn border-success text-success bg-white w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#upgradeModal" data-bs-plan="{{ $plan->name }}">MANAGE PLAN</button>
                        @elseif($plan->price === 'Free')
                            <button class="btn btn-light w-100 fw-bold text-muted disabled">CURRENT PLAN</button>
                        @else
                            <button class="btn btn-dark w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#upgradeModal" data-bs-plan="{{ $plan->name }}">UPGRADE NOW</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Upgrade Modal -->
<div class="modal fade" id="upgradeModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            
            <!-- Step 1: Confirm -->
            <div id="modalStepConfirm">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Confirm Upgrade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="fs-5">Are you sure you want to upgrade to the <strong id="selectedPlanName" class="text-success"></strong> Plan?</p>
                    <div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-25 mt-3">
                        <i class="bi bi-info-circle-fill text-info me-2"></i>
                        You will be billed immediately and new features will unlock.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success px-4" id="btnConfirmPay">Pay & Upgrade</button>
                </div>
            </div>

            <!-- Step 2: Processing -->
            <div id="modalStepProcessing" class="d-none text-center py-5">
                <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h4 class="fw-bold mt-4">Processing Payment...</h4>
                <p class="text-secondary mb-0">Please securely wait and do not close this window.</p>
            </div>

            <!-- Step 3: Success -->
            <div id="modalStepSuccess" class="d-none text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-4" style="width: 80px; height: 80px;">
                    <i class="bi bi-check-lg" style="font-size: 2.5rem;"></i>
                </div>
                <h3 class="fw-bold text-dark">Payment Successful!</h3>
                <p class="text-secondary px-4">Thank you for subscribing! Your new features are now unlocked.</p>
                <button type="button" class="btn btn-success mt-3 px-5" onclick="window.location.reload();">Return to Dashboard</button>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const upgradeModal = document.getElementById('upgradeModal');
        let selectedPlan = 'Pro';

        if (upgradeModal) {
            upgradeModal.addEventListener('show.bs.modal', function (event) {
                // Button that triggered the modal
                const button = event.relatedTarget;
                if (button) {
                    selectedPlan = button.getAttribute('data-bs-plan');
                    document.getElementById('selectedPlanName').textContent = selectedPlan;
                }
                
                // Reset states
                document.getElementById('modalStepConfirm').classList.remove('d-none');
                document.getElementById('modalStepProcessing').classList.add('d-none');
                document.getElementById('modalStepSuccess').classList.add('d-none');
            });

            document.getElementById('btnConfirmPay').addEventListener('click', function() {
                // Show processing
                document.getElementById('modalStepConfirm').classList.add('d-none');
                document.getElementById('modalStepProcessing').classList.remove('d-none');
                
                // Fake delay simulate payment gateway
                setTimeout(() => {
                    fetch('{{ route('central.user.subscription.upgrade') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ plan: selectedPlan })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Show success
                        document.getElementById('modalStepProcessing').classList.add('d-none');
                        document.getElementById('modalStepSuccess').classList.remove('d-none');
                    })
                    .catch(error => {
                        alert('Something went wrong. Please try again.');
                        window.location.reload();
                    });
                }, 2500); // 2.5 seconds fake processing
            });
        }
    });
</script>
@endsection



