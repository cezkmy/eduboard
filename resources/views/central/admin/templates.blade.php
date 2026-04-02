@extends('central.layouts.admin-layout')

@section('page-title', 'Template Management')

@push('styles')
<style>
    .card-template {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #edf2f7 !important;
    }
    
    .card-template:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
    }

    .card-template-preview {
        width: 100%;
        height: 140px;
        background: #f8f9fa;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 0 1rem 0;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }



    .x-small {
        font-size: 0.65rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-weight: 700;
        padding: 0.25rem 0.75rem !important;
    }

    .action-bar {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 4px;
        margin-top: auto;
    }

    .btn-action {
        background: #fff;
        border: 1px solid #edf2f7;
        color: #718096;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .btn-action:hover {
        background: #f1f5f9;
        color: #2d3748;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterLinks = document.querySelectorAll('[aria-labelledby="categoryFilter"] .dropdown-item');
        const templateCards = document.querySelectorAll('[data-category]');

        filterLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const selectedCategory = this.textContent.trim();
                if(!selectedCategory) return;

                templateCards.forEach(card => {
                    if (selectedCategory === 'All' || card.dataset.category === selectedCategory) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        const typeChangeLinks = document.querySelectorAll('.type-change-btn');
        typeChangeLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const newType = this.dataset.type;
                const templateId = this.dataset.id;
                
                // Submit a form to update the type silently or perform fetch. Let's just create a hidden form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/templates/${templateId}`;
                form.innerHTML = `
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="type" value="${newType}">
                    <input type="hidden" name="name" value="${this.closest('.card').querySelector('h6').innerText}">
                    <input type="hidden" name="category" value="${this.closest('.card').dataset.category}">
                `;
                document.body.appendChild(form);
                form.submit();
            });
        });

        const editModal = document.getElementById('editTemplateModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const card = button.closest('.card');
            const id = card.dataset.id;
            const name = card.querySelector('h6').innerText;
            const category = card.dataset.category;
            const type = card.dataset.type;

            const form = editModal.querySelector('form');
            form.action = `/admin/templates/${id}`;
            
            editModal.querySelector('#editTemplateName').value = name;
            editModal.querySelector('#editTemplateCategory').value = category;
            editModal.querySelector('#editTemplateType').value = type;
        });
    });
</script>
@endpush

@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Top Action actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Announcement Templates</h2>
            <p class="text-secondary mb-0">Manage and preview custom borders for school announcements.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-folder-plus"></i> Add Category
            </button>
            <button class="btn btn-outline-secondary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addTypeModal">
                <i class="bi bi-tag-fill"></i> Add Type
            </button>
            <button class="btn btn-success d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
                <i class="bi bi-plus-lg"></i> Add New Template
            </button>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form action="{{ route('central.admin.templates.category.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. Modern">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Type Modal -->
    <div class="modal fade" id="addTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form action="{{ route('central.admin.templates.type.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Type Name</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g. Premium">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Template Modal -->
    <div class="modal fade" id="addTemplateModal" tabindex="-1" aria-labelledby="addTemplateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('central.admin.templates.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTemplateModalLabel">Add New Template</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="templateName" class="form-label">Template Name</label>
                            <input type="text" class="form-control" name="name" id="templateName" required>
                        </div>
                        <div class="mb-3">
                            <label for="templateCategory" class="form-label">Category</label>
                            <select class="form-select" name="category" id="templateCategory" required>
                                <option value="" selected disabled>Choose...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="templateType" class="form-label">Type</label>
                            <select class="form-select" name="type" id="templateType" required>
                                <option value="" selected disabled>Choose...</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->name }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="templateImage" class="form-label">Upload Image</label>
                            <input class="form-control" type="file" name="image" id="templateImage">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Template Modal -->
    <div class="modal fade" id="editTemplateModal" tabindex="-1" aria-labelledby="editTemplateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTemplateModalLabel">Edit Template</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editTemplateName" class="form-label">Template Name</label>
                            <input type="text" class="form-control" name="name" id="editTemplateName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTemplateCategory" class="form-label">Category</label>
                            <select class="form-select" name="category" id="editTemplateCategory" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editTemplateType" class="form-label">Type</label>
                            <select class="form-select" name="type" id="editTemplateType" required>
                                @foreach($types as $type)
                                    <option value="{{ $type->name }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Template Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h3 fw-bold mb-1">{{ $templates->count() }}</div>
                    <div class="text-secondary small">Total Templates</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h3 fw-bold mb-1 text-success">{{ $templates->where('type', 'Basic')->count() }}</div>
                    <div class="text-secondary small">Basic (Free)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h3 fw-bold mb-1 text-primary">{{ $templates->where('type', 'Pro')->count() }}</div>
                    <div class="text-secondary small">Pro Templates</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="h3 fw-bold mb-1 text-warning">{{ $templates->where('type', 'Ultimate')->count() }}</div>
                    <div class="text-secondary small">Ultimate Templates</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="d-flex justify-content-end mb-3">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" id="categoryFilter" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-funnel-fill"></i> Filter by Category
            </button>
            <ul class="dropdown-menu shadow" aria-labelledby="categoryFilter">
                <li><a class="dropdown-item d-flex align-items-center gap-2" href="#"><i class="bi bi-grid-fill text-secondary"></i> All</a></li>
                @if($categories->count() > 0)
                    <li><hr class="dropdown-divider"></li>
                    @foreach($categories as $cat)
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="#"><i class="bi bi-folder text-primary"></i> {{ $cat->name }}</a></li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>

    <!-- Domain Layout Templates -->
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="fw-bold mb-2">Domain Layout Templates</h3>
            <p class="text-secondary mb-0">Colors / theme presets for the tenant system UI.</p>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="mb-3" style="width: 44px; height: 44px; border-radius: 999px; background: rgba(13,110,253,0.12); color: #0d6efd; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-globe2"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Blue Landing Layout</h6>
                    <div class="text-secondary small">Default modern theme</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="mb-3" style="width: 44px; height: 44px; border-radius: 999px; background: rgba(25,135,84,0.12); color: #198754; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-leaf-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Green Landing Layout</h6>
                    <div class="text-secondary small">Eco-friendly theme</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="mb-3" style="width: 44px; height: 44px; border-radius: 999px; background: rgba(107,33,168,0.12); color: #6b21a8; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-heart-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Pink Landing Layout</h6>
                    <div class="text-secondary small">Royal/pink theme</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="mb-3" style="width: 44px; height: 44px; border-radius: 999px; background: rgba(250,204,21,0.20); color: #facc15; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-sun-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Yellow Landing Layout</h6>
                    <div class="text-secondary small">Bright theme</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="mb-3" style="width: 44px; height: 44px; border-radius: 999px; background: rgba(249,115,22,0.18); color: #f97316; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-fire"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Orange Landing Layout</h6>
                    <div class="text-secondary small">Warm theme</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Border Templates -->
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="fw-bold mb-1">Border Templates</h3>
            <p class="text-secondary mb-0">Announcement border designs (with preview images).</p>
        </div>
    </div>

    <!-- Templates List -->
    <div class="row g-4">
        @foreach($templates as $template)
        @php
            $badgeColorClass = '';
            $badgeStyle = '';
            
            if (strtolower($template->type) === 'ultimate') {
                $badgeColorClass = 'bg-warning bg-opacity-10 text-warning';
            } elseif (strtolower($template->type) === 'pro') {
                $badgeColorClass = 'bg-primary bg-opacity-10 text-primary';
            } elseif (strtolower($template->type) === 'basic') {
                $badgeColorClass = 'bg-success bg-opacity-10 text-success';
            } else {
                $hash = 0;
                for ($i = 0; $i < strlen($template->type); $i++) {
                    $hash = ord($template->type[$i]) + (($hash << 5) - $hash);
                }
                $hue = abs($hash) % 360;
                $color = "hsl({$hue}, 70%, 50%)";
                $bg = "hsla({$hue}, 70%, 50%, 0.1)";
                
                $badgeStyle = "background-color: {$bg}; color: {$color};";
            }
        @endphp
        <div class="col-6 col-md-4 col-lg-3 col-xl-2" data-category="{{ $template->category }}">
            <div class="card h-100 border-0 shadow-sm overflow-hidden" data-id="{{ $template->id }}" data-category="{{ $template->category }}" data-type="{{ $template->type }}">
                <div class="card-body p-3 d-flex flex-column text-start">
                    <!-- Fixed Image Preview Container -->
                    <div class="card-template-preview mx-0 mb-3">
                        @if($template->image)
                            <img src="{{ asset('template/' . $template->image) }}" style="max-width: 100%; max-height: 100%; object-fit: contain;" alt="{{ $template->name }}">
                        @else
                            <i class="bi bi-image text-secondary fs-1"></i>
                        @endif
                    </div>
                    
                    <!-- Template Name -->
                    <h6 class="fw-bold mb-2 text-truncate" style="font-size: 1rem;" title="{{ $template->name }}">
                        {{ $template->name }}
                    </h6>
                    
                    <!-- Badge -->
                    <div class="mb-2">
                        <span class="badge {{ $badgeColorClass }} rounded-pill x-small" style="{{ $badgeStyle }}">
                            {{ $template->type }}
                        </span>
                    </div>
                    
                    <!-- Category -->
                    <p class="text-secondary mb-3 text-truncate" style="font-size: 0.8rem;">{{ $template->category }}</p>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-1 mt-auto">
                        <div class="dropdown w-100">
                                <button
                                    class="btn btn-light btn-sm w-100 border-0 bg-secondary bg-opacity-10"
                                    type="button"
                                    id="dropdownMenuButton-{{ $template->id }}"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    title="Edit {{ $template->name }}"
                                >
                                    <i class="bi bi-pencil"></i>
                                    <span class="ms-1 text-truncate d-inline-block" style="max-width: 145px; vertical-align: bottom;">
                                       Edit
                                    </span>
                            </button>
                            <ul class="dropdown-menu shadow" aria-labelledby="dropdownMenuButton-{{ $template->id }}">
                                <li><a class="dropdown-item d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#editTemplateModal"><i class="bi bi-pencil"></i> Edit Details</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li class="px-3 py-1 text-muted small">Change Type</li>
                                @foreach($types as $type)
                                    <li><a class="dropdown-item type-change-btn" href="#" data-id="{{ $template->id }}" data-type="{{ $type->name }}">{{ $type->name }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection