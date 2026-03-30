<section class="hero">
    <!-- Background decoration - FIXED: dapat green ni -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="z-index: -1; pointer-events: none;">
        <div class="position-absolute top-0 start-0 w-25 h-25 rounded-circle bg-primary opacity-10" style="filter: blur(80px); transform: translate(-30%, -20%);"></div>
        <div class="position-absolute top-50 end-0 w-25 h-25 rounded-circle bg-accent opacity-10" style="filter: blur(80px);"></div>
    </div>
    
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6" data-aos="fade-up">
                <div class="hero-badge">
                    <i class="bi bi-megaphone"></i>
                    Multi-Tenant School Platform
                </div>
                
                <h1 class="fw-bold mb-3">
                    One platform, 
                    <span class="text-gradient">every school</span> 
                    connected
                </h1>
                
                <p class="text-secondary mb-4" style="font-size: 1.1rem; max-width: 90%;">
                    EduBoard lets schools manage bulletins, announcements, and media
                    from a single dashboard — with isolated data, role-based access,
                    and flexible pricing plans.
                </p>
                
                <div class="d-flex gap-3 mb-4">
                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn-primary px-4 py-2">
                        Start Free <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                    <a href="#features" class="btn btn-outline-primary px-4 py-2">
                        See Features
                    </a>
                </div>
                
                <div class="hero-stats">
                    <div class="hero-stat">
                        <i class="bi bi-building"></i>
                        <span>100+ Schools</span>
                    </div>
                    <div class="hero-stat">
                        <i class="bi bi-people"></i>
                        <span>50k+ Users</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                <img src="{{ asset('board.png') }}" 
                     alt="EduBoard dashboard" 
                     class="img-fluid rounded-4 shadow-lg"
                     style="border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            </div>
        </div>
    </div>
</section>



