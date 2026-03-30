<section id="features" class="features-section py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold mb-3">Everything your school needs</h2>
            <p class="lead text-secondary">Powerful features built for multi-tenant school management.</p>
        </div>
        
        <div class="row g-4">
            @php
                $features = [
                    ['icon' => 'bi-bell', 'title' => 'Announcements', 'desc' => 'Post and manage school-wide announcements with media attachments.'],
                    ['icon' => 'bi-shield-check', 'title' => 'Data Isolation', 'desc' => 'Each school\'s data is fully isolated — secure multi-tenancy.'],
                    ['icon' => 'bi-people', 'title' => 'Role-Based Access', 'desc' => 'Admins, Teachers, and Students each get tailored permissions.'],
                    ['icon' => 'bi-cloud-upload', 'title' => 'Media Uploads', 'desc' => 'Upload images and videos based on your subscription plan.'],
                    ['icon' => 'bi-pin', 'title' => 'Pin Announcements', 'desc' => 'Keep important announcements at the top for everyone to see.'],
                    ['icon' => 'bi-palette', 'title' => 'Custom Branding', 'desc' => 'Add your school logo and customize themes on higher plans.'],
                    ['icon' => 'bi-grid-3x3', 'title' => 'Categories', 'desc' => 'Organize announcements by category for easy navigation.'],
                    ['icon' => 'bi-phone', 'title' => 'Responsive', 'desc' => 'Works beautifully on desktops, tablets, and phones.'],
                ];
            @endphp
            
            @foreach($features as $index => $feature)
                <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="{{ $index * 50 }}">
                    <div class="feature-card">
                        <div class="feature-icon mb-3">
                            <i class="bi {{ $feature['icon'] }} fs-4"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-2">{{ $feature['title'] }}</h3>
                        <p class="text-secondary small mb-0">{{ $feature['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>



