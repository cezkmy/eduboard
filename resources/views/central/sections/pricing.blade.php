<section id="pricing" class="pricing-section">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Simple, transparent pricing</h2>
            <p class="lead text-secondary">Choose the plan that fits your school. Upgrade anytime.</p>
        </div>
        
        <div class="row g-4">
            @foreach($plans as $index => $plan)
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="pricing-card {{ $plan->is_popular ? 'highlight' : '' }}">
                        @if($plan->is_popular)
                            <div class="popular-badge">Most Popular</div>
                        @elseif(strtolower($plan->name ?? '') === 'basic')
                            <div class="popular-badge popular-badge--free-trial">Free Trial for New Users</div>
                        @endif
                        
                        <h3 class="h4 fw-bold mb-3">{{ $plan->name }}</h3>
                        
                        <div class="price mb-4">
                            {{ $plan->price }}<small>{{ $plan->period }}</small>
                        </div>
                        
                        <ul class="feature-list">
                            @foreach($plan->features as $feature)
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        
                        <a href="{{ route('register') }}" class="btn {{ $plan->is_popular ? 'btn-primary' : 'btn-outline-primary' }} w-100">
                            Get Started
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>



