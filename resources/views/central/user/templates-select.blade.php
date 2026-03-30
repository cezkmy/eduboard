@extends('central.layouts.user-layout')

@section('page-title', 'Select Template')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            @if(session('error'))
                {{ session('error') }}
            @endif
            @if($errors->any())
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $hasSchool = auth()->user()->school_domain;
        
        $freeTemplates = [
            ['id' => 1, 'name' => 'School Event Template', 'category' => 'Events', 'description' => 'Perfect for announcing school events, programs, and activities'],
            ['id' => 2, 'name' => 'Academic Announcement', 'category' => 'Academic', 'description' => 'For exam schedules, enrollment updates, and academic notices'],
            ['id' => 3, 'name' => 'Holiday Notice', 'category' => 'Holidays', 'description' => 'Beautiful templates for holiday greetings and announcements'],
            ['id' => 4, 'name' => 'Modern Admin Portal', 'category' => 'Management', 'description' => 'A clean and professional administrative portal for your school'],
        ];

        $premiumTemplates = [
            ['id' => 5, 'name' => 'Academic Calendar', 'category' => 'Premium', 'description' => 'Detailed interactive academic calendars for students'],
            ['id' => 6, 'name' => 'Student Portal', 'category' => 'Premium', 'description' => 'Integrated student management and resource portal'],
            ['id' => 7, 'name' => 'Staff Directory', 'category' => 'Premium', 'description' => 'Professional staff directory with contact integration'],
        ];
    @endphp

    @if($hasSchool)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center p-4">
                <i class="bi bi-info-circle-fill fs-1 me-4"></i>
                <div>
                    <h5 class="fw-bold mb-1">School Already Created!</h5>
                    <p class="mb-0">You have already set up your school domain: <strong>{{ auth()->user()->school_domain }}</strong>. You can view your current school details in <a href="{{ route('central.user.domain') }}" class="fw-bold text-decoration-none">Domain Management</a>. Below you can see our Premium Templates available for upgrade.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8 text-center mb-5">
            <h2 class="fw-bold mb-3">{{ $hasSchool ? 'Premium Templates' : 'Welcome to Your Trial! 🎉' }}</h2>
            <p class="lead text-secondary">{{ $hasSchool ? 'Upgrade your plan to access these advanced school management templates.' : 'Choose ONE free template and set up your school domain' }}</p>
            
            @if(!$hasSchool)
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                After selecting a template, we will:
                <ul class="text-start mt-2 mb-0">
                    <li>Create your school's database</li>
                    @php
                        $host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
                        if (str_starts_with($host, 'eduboard.')) {
                            $baseHost = substr($host, 9);
                        } else {
                            $baseHost = $host;
                        }
                        $port = parse_url(config('app.url'), PHP_URL_PORT);
                        $exampleDomain = 'yourschool_eduboard.' . $baseHost . ($port ? ':' . $port : '');
                    @endphp
                    <li>Setup your custom domain ({{ $exampleDomain }})</li>
                    <li>Generate admin credentials for you</li>
                </ul>
            </div>
            @endif
        </div>
    </div>

    @if(!$hasSchool)
    <!-- Domain Input Section -->
    <div class="row justify-content-center mb-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Choose Your School Domain</h5>
                    <div class="mb-3">
                        <label for="domainInput" class="form-label fw-medium">Domain Name</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="domainInput" 
                                   placeholder="your-school-name"
                                   value="{{ auth()->user()->school_domain ? explode('_eduboard.', auth()->user()->school_domain)[0] : Str::slug(auth()->user()->school_name) }}"
                                   {{ auth()->user()->school_domain ? 'readonly' : '' }}>
                            @php
                                $host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
                                // If host is eduboard.localhost, we just want .localhost for the suffix
                                // to avoid sasa_eduboard.eduboard.localhost
                                if (str_starts_with($host, 'eduboard.')) {
                                    $baseHost = substr($host, 9); // remove 'eduboard.'
                                } else {
                                    $baseHost = $host;
                                }
                                $port = parse_url(config('app.url'), PHP_URL_PORT);
                                $suffix = '_eduboard.' . $baseHost . ($port ? ':' . $port : '');
                            @endphp
                            <span class="input-group-text bg-light">{{ $suffix }}</span>
                        </div>
                        <div class="form-text text-secondary">
                            <i class="bi bi-info-circle me-1"></i>
                            Only lowercase letters, numbers, and hyphens allowed. No spaces.
                        </div>
                    </div>
                    <div id="domainPreview" class="alert alert-success d-none">
                        Your domain will be: <strong id="previewDomain"></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-4">
        @foreach($hasSchool ? $premiumTemplates : $freeTemplates as $template)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 {{ $hasSchool ? 'premium-locked' : '' }}">
                <div class="card-body p-4 text-center">
                    <div class="{{ $hasSchool ? 'bg-primary' : 'bg-success' }} bg-opacity-10 p-4 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-file-text fs-1 {{ $hasSchool ? 'text-primary' : 'text-success' }}"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">{{ $template['name'] }}</h5>
                    <span class="badge bg-light text-secondary mb-3">{{ $template['category'] }}</span>
                    <p class="small text-secondary mb-4">{{ $template['description'] }}</p>
                    
                    @if($hasSchool)
                        <a href="{{ route('central.user.subscription') }}" class="btn btn-primary w-100">
                            <i class="bi bi-unlock me-2"></i>Upgrade to Unlock
                        </a>
                    @else
                        <button type="button" 
                                class="btn btn-success w-100 select-template-btn"
                                data-bs-toggle="modal" 
                                data-bs-target="#confirmModal"
                                data-template-id="{{ $template['id'] }}"
                                data-template-name="{{ $template['name'] }}">
                            <i class="bi bi-check-circle me-2"></i>Select This Template
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($hasSchool)
    <style>
        .premium-locked {
            opacity: 0.85;
            transition: all 0.3s ease;
        }
        .premium-locked:hover {
            opacity: 1;
            transform: translateY(-5px);
        }
    </style>
    @endif
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Template Selection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bi bi-file-text text-success fs-1"></i>
                </div>
                
                <!-- Domain Preview in Modal -->
                <div class="alert alert-info mb-4">
                    <strong>Your domain will be:</strong><br>
                    <span id="modalDomain" class="fw-bold"></span>
                </div>
                
                <p class="fw-medium text-center">Are you sure you want to select <strong class="text-success" id="modalTemplateName"></strong>?</p>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>This action cannot be undone.</strong>
                </div>

                <div class="bg-light p-3 rounded">
                    <p class="fw-medium mb-2">After confirmation:</p>
                    <ul class="small mb-0">
                        <li>✓ Your school database will be created with name: <strong id="modalDbName"></strong></li>
                        <li>✓ Your domain will be set up</li>
                        <li>✓ Admin credentials will be generated</li>
                        <li>✓ You can start managing your school</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('central.user.templates.select.store') }}" id="selectTemplateForm">
                    @csrf
                    <input type="hidden" name="template_id" id="modalTemplateId">
                    <input type="hidden" name="template_name" id="modalTemplateNameInput">
                    <input type="hidden" name="custom_domain" id="modalCustomDomain">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>Yes, Proceed
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const domainInput = document.getElementById('domainInput');
    const domainPreview = document.getElementById('domainPreview');
    const previewDomain = document.getElementById('previewDomain');
    const modalDomain = document.getElementById('modalDomain');
    const modalDbName = document.getElementById('modalDbName');
    const modalCustomDomain = document.getElementById('modalCustomDomain');
    
    const selectButtons = document.querySelectorAll('.select-template-btn');
    const modalTemplateName = document.getElementById('modalTemplateName');
    const modalTemplateId = document.getElementById('modalTemplateId');
    const modalTemplateNameInput = document.getElementById('modalTemplateNameInput');

    // Function to validate and format domain
    function formatDomain(input) {
        return input.toLowerCase()
            .replace(/[^a-z0-9]/g, '_') // Replace invalid chars with underscore
            .replace(/__+/g, '_') // Replace multiple underscores with single
            .replace(/^_|_$/g, ''); // Remove leading/trailing underscores
    }

    // Use the central domain from PHP
    const appDomain = '{{ parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost' }}';
    const appPort = '{{ parse_url(config('app.url'), PHP_URL_PORT) ?? '' }}';
    
    // Logic to determine the base host for suffix
    let baseHost = appDomain;
    if (appDomain.startsWith('eduboard.')) {
        baseHost = appDomain.substring(9);
    }
    const domainSuffix = '_eduboard.' + baseHost + (appPort ? ':' + appPort : '');

    // Update preview as user types
    domainInput.addEventListener('input', function() {
        const formatted = formatDomain(this.value);
        if (formatted) {
            // (name)_eduboard.(base_host)(:port)
            const fullDomain = formatted + domainSuffix;
            previewDomain.textContent = fullDomain;
            domainPreview.classList.remove('d-none');
        } else {
            domainPreview.classList.add('d-none');
        }
    });

    // Trigger initial preview
    if (domainInput.value) {
        domainInput.dispatchEvent(new Event('input'));
    }

    selectButtons.forEach(button => {
        button.addEventListener('click', function() {
            const templateId = this.dataset.templateId;
            const templateName = this.dataset.templateName;
            
            let domainValue = domainInput.value.trim();
            if (!domainValue) {
                domainValue = formatDomain('{{ auth()->user()->school_name }}');
            }
            
            const formattedDomain = formatDomain(domainValue);
            const fullDomain = formattedDomain + domainSuffix;
            const dbName = formattedDomain + '_eduboard_db';
            
            modalTemplateName.textContent = templateName;
            modalTemplateId.value = templateId;
            modalTemplateNameInput.value = templateName;
            modalCustomDomain.value = formattedDomain;
            modalDomain.textContent = fullDomain;
            modalDbName.textContent = dbName;
        });
    });
});
</script>
@endsection



