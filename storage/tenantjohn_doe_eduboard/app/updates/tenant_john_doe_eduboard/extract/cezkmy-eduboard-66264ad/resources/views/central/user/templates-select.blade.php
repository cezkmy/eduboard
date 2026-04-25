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
            [
                'id' => 1,
                'name' => 'Blue Landing Layout',
                'category' => 'Blue',
                'description' => 'Default modern theme with blue accents.',
                'circle_color' => '#0d6efd',
                'circle_bg' => 'rgba(13,110,253,0.10)',
                'icon' => 'bi bi-globe2'
            ],
            [
                'id' => 2,
                'name' => 'Green Landing Layout',
                'category' => 'Green',
                'description' => 'Eco-friendly theme with green accents.',
                'circle_color' => '#198754',
                'circle_bg' => 'rgba(25,135,84,0.10)',
                'icon' => 'bi bi-tree-fill'
            ],
            [
                'id' => 3,
                'name' => 'Pink Landing Layout',
                'category' => 'Pink',
                'description' => 'Cute pink themed landing layout.',
                'circle_color' => '#ec4899',
                'circle_bg' => 'rgba(236,72,153,0.10)',
                'icon' => 'bi bi-heart-fill'
            ],
            [
                'id' => 5,
                'name' => 'Yellow Landing Layout',
                'category' => 'Yellow',
                'description' => 'Bright yellow landing layout.',
                'circle_color' => '#facc15',
                'circle_bg' => 'rgba(250,204,21,0.14)',
                'icon' => 'bi bi-sun-fill'
            ],
            [
                'id' => 6,
                'name' => 'Orange Landing Layout',
                'category' => 'Orange',
                'description' => 'Warm orange landing layout.',
                'circle_color' => '#f97316',
                'circle_bg' => 'rgba(249,115,22,0.12)',
                'icon' => 'bi bi-fire'
            ],
        ];

        // Domain layout templates only (theme colors for tenant UI)
    @endphp

    @php
        $isProPlus = in_array(auth()->user()->plan ?? 'Basic', ['Pro', 'Ultimate'], true);
        $alreadySelected = (bool) (auth()->user()->has_selected_template ?? false);
        $tenant = \App\Models\Tenant::where('owner_id', auth()->id())->first();
        $selectedLayoutId = $tenant->template_id ?? null;
    @endphp

    @if($hasSchool)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center p-4">
                <i class="bi bi-info-circle-fill fs-1 me-4"></i>
                <div>
                    <h5 class="fw-bold mb-1">School Already Created!</h5>
                    <p class="mb-0">
                        Your school domain is: <strong>{{ auth()->user()->school_domain }}</strong>.
                        Manage it in <a href="{{ route('central.user.domain') }}" class="fw-bold text-decoration-none">Domain Management</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8 text-center mb-5">
            <h2 class="fw-bold mb-3">Domain Layout Templates</h2>
            <p class="lead text-secondary">
                Choose your tenant system UI theme.
                @if(!$alreadySelected)
                    You can select <strong>ONE</strong> layout on Basic.
                @else
                    @if($isProPlus)
                        You can change your layout anytime (Pro / Ultimate).
                    @else
                        You have already selected your layout.
                    @endif
                @endif
            </p>
            
            @if(!$hasSchool)
            <div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-25 text-info">
                <i class="bi bi-info-circle me-2"></i>
                After selecting a template, we will:
                <ul class="text-start mt-2 mb-0">
                    <li>Create your school's database</li>
                    @php
                        $host = parse_url(config('app.url'), PHP_URL_HOST) ?? request()->getHost();
                        // If it's localhost or 127.0.0.1, use a "customized" domain like eduboard.com
                        if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
                            $baseHost = 'localhost';
                        } elseif (str_starts_with($host, 'eduboard.')) {
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
                                $host = parse_url(config('app.url'), PHP_URL_HOST) ?? request()->getHost();
                                // If it's localhost or 127.0.0.1, use a "customized" domain like eduboard.com
                                if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
                                    $baseHost = 'localhost';
                                } elseif (str_starts_with($host, 'eduboard.')) {
                                    $baseHost = substr($host, 9);
                                } else {
                                    $baseHost = $host;
                                }
                                $port = parse_url(config('app.url'), PHP_URL_PORT);
                                $suffix = '_eduboard.' . $baseHost . ($port ? ':' . $port : '');
                            @endphp
                            <span class="input-group-text fw-bold text-primary bg-primary bg-opacity-10">{{ $suffix }}</span>
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
        @foreach($freeTemplates as $template)
        @php
            $isSelected = $selectedLayoutId && (int) $selectedLayoutId === (int) $template['id'];
            $isDisabled = $alreadySelected && !$isProPlus;
        @endphp
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 {{ $isSelected ? 'border border-2 border-success' : '' }} {{ $isDisabled ? 'opacity-75' : '' }}">
                <div class="card-body p-4 text-center position-relative">
                    <div
                        class="p-4 rounded-circle d-inline-block mb-3"
                        style="background-color: {{ $template['circle_bg'] ?? 'rgba(16,185,129,0.10)' }};
                               color: {{ $template['circle_color'] ?? '#10b981' }};
                               line-height: 1;">
                        <i class="bi {{ $template['icon'] ?? 'bi bi-file-text' }} fs-1"
                           style="color: {{ $template['circle_color'] ?? '#10b981' }};"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">{{ $template['name'] }}</h5>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary mb-3">{{ $template['category'] }}</span>
                    <p class="small text-secondary mb-4">{{ $template['description'] }}</p>

                    @if($isSelected)
                        <span class="badge bg-success bg-opacity-10 text-success mb-2">SELECTED</span>
                    @endif

                    @if(!$hasSchool)
                        <button type="button"
                                class="btn btn-success w-100 select-template-btn {{ $isDisabled ? 'disabled' : '' }}"
                                {{ $isDisabled ? 'disabled' : '' }}
                                data-bs-toggle="modal"
                                data-bs-target="#confirmModal"
                                data-template-id="{{ $template['id'] }}"
                                data-template-name="{{ $template['name'] }}">
                            <i class="bi bi-check-circle me-2"></i>{{ $isDisabled ? 'Already Selected' : 'Select This Layout' }}
                        </button>
                    @else
                        @if($isProPlus)
                            <form method="POST" action="{{ route('central.user.templates.layout.update') }}">
                                @csrf
                                <input type="hidden" name="template_id" value="{{ $template['id'] }}">
                                <input type="hidden" name="template_name" value="{{ $template['name'] }}">
                                <button type="submit" class="btn btn-success w-100" {{ $isSelected ? 'disabled' : '' }}>
                                    <i class="bi bi-palette me-2"></i>{{ $isSelected ? 'Current Layout' : 'Choose Template' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('central.user.subscription') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-arrow-up-circle me-2"></i>Upgrade Plan
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
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

                <div class="bg-secondary bg-opacity-10 p-3 rounded">
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
    let appDomain = '{{ parse_url(config('app.url'), PHP_URL_HOST) ?? request()->getHost() }}';
    if (['localhost', '127.0.0.1', '::1'].includes(appDomain)) {
        appDomain = 'eduboard.com';
    }
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

    @if(!$hasSchool)
    // If user came from templates-purchase, preselect the template
    const params = new URLSearchParams(window.location.search);
    const prefillTemplateId = params.get('template_id');
    if (prefillTemplateId) {
        const btn = Array.from(selectButtons).find(b => String(b.dataset.templateId) === String(prefillTemplateId));
        if (btn) btn.click();
    }
    @endif
});
</script>
@endsection



