@php
    $schoolType = tenant('school_type') ?? 'college';
    $announcementCategories = \App\Models\Category::where('type', 'announcement_category')->get();
    $colleges = \App\Models\Category::where('type', 'college')->get();
    $yearLevels = \App\Models\Category::where('type', 'level')->get();
    $gradeLevels = \App\Models\Category::where('type', 'grade_level')->get();
    $programs = \App\Models\Category::where('type', 'program')->get();
    $strands = \App\Models\Category::where('type', 'strand')->get();
    $sections = \App\Models\Category::where('type', 'section')->get();

    // Fetch templates from central database
    $templates = [];
    if (function_exists('tenancy')) {
        $templates = tenancy()->central(function () {
            return \App\Models\Template::all();
        });
    }
@endphp

<div class="space-y-6" x-data="{ 
    isEdit: false,
    announcementId: null,
    title: '',
    category: '',
    content: '',
    targetAll: true, 
    targetColleges: [],
    targetPrograms: [], 
    targetYearLevels: [], 
    targetGradeLevels: [],
    targetStrands: [],
    targetSections: [],
    targetRoles: [],
    showTargetingModal: false,
    targetingTab: 'college',
    targetingSearch: '',
    selectedTemplate: '', 
    bgColor: '#ffffff', 
    layoutType: 'landscape',
    mediaPreview: [],
    borderRadius: 24,
    mediaLayout: 'landscape',
    fontStyle: 'font-sans',
    selectedTemplateImage: '',
    titleColor: '#111827',
    contentColor: '#4b5563',
    categoryColor: '#4b5563',
    borderColor: 'transparent',
    isPublishing: false,
    presetColors: [
        { name: 'Emerald', color: '#10b981' },
        { name: 'Blue', color: '#3b82f6' },
        { name: 'Purple', color: '#8b5cf6' },
        { name: 'Pink', color: '#ec4899' },
        { name: 'Orange', color: '#f59e0b' },
        { name: 'Red', color: '#ef4444' },
        { name: 'Indigo', color: '#6366f1' },
        { name: 'Slate', color: '#64748b' }
    ],
    init() {
        this.$watch('title', value => this.saveToLocalStorage());
        this.$watch('category', value => this.saveToLocalStorage());
        this.$watch('content', value => this.saveToLocalStorage());
        this.loadDraft();

        window.addEventListener('edit-announcement', (e) => {
            if (e && e.detail) this.editAnnouncement(e.detail);
        });
        
        window.addEventListener('reset-announcement-form', () => {
            this.resetForm();
        });
    },
    resetForm() {
        this.isEdit = false;
        this.announcementId = null;
        this.title = '';
        this.category = '';
        this.content = '';
        this.targetAll = true;
        this.targetColleges = [];
        this.targetPrograms = [];
        this.targetYearLevels = [];
        this.targetGradeLevels = [];
        this.targetStrands = [];
        this.targetSections = [];
        this.targetRoles = [];
        this.selectedTemplate = '';
        this.selectedTemplateImage = '';
        this.bgColor = '#ffffff';
        this.mediaPreview = [];
        this.isPublishing = false;
    },
    selectTemplate(id, image) {
        this.selectedTemplate = id;
        this.selectedTemplateImage = image;
    },
    editAnnouncement(announcement) {
        if (!announcement) return;
        this.isEdit = true;
        this.announcementId = announcement.id;
        this.title = announcement.title || '';
        this.category = announcement.category || '';
        this.content = announcement.content || '';
        
        // Handle targeting
        this.targetColleges = announcement.target_college || [];
        this.targetPrograms = announcement.target_program || [];
        this.targetYearLevels = announcement.target_year || [];
        this.targetGradeLevels = announcement.target_grade_level || [];
        this.targetStrands = announcement.target_strand || [];
        this.targetSections = announcement.target_section || [];
        this.targetRoles = announcement.target_roles || [];
        
        this.targetAll = ! (
            this.targetColleges.length || 
            this.targetPrograms.length || 
            this.targetYearLevels.length || 
            this.targetGradeLevels.length || 
            this.targetStrands.length || 
            this.targetSections.length ||
            this.targetRoles.length
        );

        this.selectedTemplate = announcement.template_id || '';
        this.selectedTemplateImage = announcement.template_image || '';
        this.bgColor = announcement.bg_color || '#ffffff';
        this.layoutType = announcement.layout_type || 'landscape';
        this.borderRadius = announcement.border_radius || 24;
        this.mediaLayout = announcement.media_layout || 'landscape';
        this.fontStyle = announcement.font_style || 'font-sans';
        this.titleColor = announcement.title_color || '#111827';
        this.contentColor = announcement.content_color || '#4b5563';
        this.categoryColor = announcement.category_color || '#4b5563';
        this.borderColor = announcement.border_color || 'transparent';
    },
    saveToLocalStorage() {
        if (this.isEdit) return;
        try {
            const draft = {
                title: this.title,
                category: this.category,
                content: this.content,
                timestamp: new Date().getTime()
            };
            localStorage.setItem('announcement_draft_local', JSON.stringify(draft));
        } catch(e) {}
    },
    loadDraft() {
        if (this.isEdit) return;
        try {
            const savedDraft = localStorage.getItem('announcement_draft_local');
            if (savedDraft) {
                const draft = JSON.parse(savedDraft);
                if (draft) {
                    this.title = draft.title || '';
                    this.category = draft.category || '';
                    this.content = draft.content || '';
                }
            }
        } catch (e) {
            console.warn('Draft recovery failed:', e);
            localStorage.removeItem('announcement_draft_local');
        }
    },
    async submitForm(status = 'published') {
        if (this.isPublishing) return;

        const form = this.$refs.annForm || this.$el.querySelector('form');
        if (!form) {
            showAlert('Error', 'Form not found. Please refresh the page.', 'error');
            return;
        }

        this.isPublishing = true;
        try {
            const formData = new FormData(form);
            formData.append('status', status);
            formData.append('template_id', this.selectedTemplate);
            
            if (!this.targetAll) {
                this.targetColleges.forEach(val => formData.append('target_college[]', val));
                this.targetPrograms.forEach(val => formData.append('target_program[]', val));
                this.targetYearLevels.forEach(val => formData.append('target_year[]', val));
                this.targetGradeLevels.forEach(val => formData.append('target_grade_level[]', val));
                this.targetStrands.forEach(val => formData.append('target_strand[]', val));
                this.targetSections.forEach(val => formData.append('target_section[]', val));
                this.targetRoles.forEach(val => formData.append('target_roles[]', val));
            }

            formData.append('bg_color', this.bgColor);
            formData.append('layout_type', this.layoutType);
            formData.append('border_radius', this.borderRadius);
            formData.append('media_layout', this.mediaLayout);
            formData.append('font_style', this.fontStyle);
            formData.append('title_color', this.titleColor);
            formData.append('content_color', this.contentColor);
            formData.append('category_color', this.categoryColor);
            formData.append('border_color', this.borderColor);

            const url = this.isEdit 
                ? `{{ url('announcements') }}/${this.announcementId}`
                : '{{ route("tenant.announcements.store") }}';
                
            if (this.isEdit) formData.append('_method', 'PUT');

            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const result = await response.json();
            if (result.success) {
                window.dispatchEvent(new CustomEvent('announcement-published', {
                    detail: { message: this.isEdit ? 'Updated' : 'Published' }
                }));
                if (!this.isEdit) localStorage.removeItem('announcement_draft_local');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert('Error: ' + (result.message || 'Check inputs'));
            }
        } catch (error) {
            alert('A connection error occurred.');
        } finally {
            this.isPublishing = false;
        }
    }
}">
    <form x-ref="annForm" @submit.prevent="submitForm('published')" class="space-y-6">
        @csrf

        {{-- Loading Overlay --}}
        <template x-teleport="body">
            <div x-show="isPublishing" 
                 class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-cloak>
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-2xl flex flex-col items-center gap-4 max-w-xs w-full mx-4 border border-gray-100 dark:border-gray-700">
                    <div class="relative w-16 h-16">
                        <div class="absolute inset-0 border-4 border-emerald-100 dark:border-emerald-900/30 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Uploading Media</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Please wait while we process your announcement...</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Title
                </label>
                <input
                    id="annTitle"
                    name="title"
                    x-model="title"
                    placeholder="Announcement title..."
                    required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
                />
            </div>

            @if(tenant() && tenant()->hasFeature('categories'))
                <div>
                    <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Category
                    </label>
                    <select
                        id="annCategory"
                        name="category"
                        x-model="category"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
                    >
                        <option value="">Select category</option>
                        @foreach($announcementCategories as $cat)
                            <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" id="annCategory" name="category" x-model="category" value="General">
            @endif
        </div>

        <div>
            <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                </svg>
                Content
            </label>
            <textarea
                id="annContent"
                name="content"
                x-model="content"
                placeholder="Write your announcement..."
                rows="5"
                required
                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all resize-none"
            ></textarea>
        </div>

        <div x-show="!isEdit">
            <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Media Upload
            </label>
            <div class="relative group border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-4 text-center hover:border-emerald-500 hover:bg-emerald-50/10 transition-all cursor-pointer">
                <input
                    type="file"
                    id="annMedia"
                    name="media[]"
                    multiple
                    @change="
                        let hasOversized = false;
                        const validFiles = Array.from($event.target.files).filter(file => {
                            if (file.type.startsWith('video') && file.size > 104857600) { // 100MB
                                hasOversized = true;
                                return false;
                            }
                            return true;
                        });
                        
                        if (hasOversized) {
                            alert('Video uploads are limited to 100MB per file.');
                            $event.target.value = '';
                            mediaPreview = [];
                            return;
                        }

                        mediaPreview = validFiles.map(file => ({
                            url: URL.createObjectURL(file),
                            type: file.type.startsWith('video') ? 'video' : 'image'
                        }))
                    "
                    accept="{{ tenant() && tenant()->hasFeature('video_upload') ? 'image/*,video/*' : 'image/jpeg,image/png' }}"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                />
                <div class="flex items-center justify-center gap-4">
                    <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center text-emerald-500 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <div class="text-left">
                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Click or drag to upload media</p>
                        <p class="text-[10px] text-gray-500">
                            {{ tenant() && tenant()->hasFeature('video_upload') ? 'Images and videos (max 100MB)' : 'Images only (JPEG, PNG)' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if(count($templates) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        Announcement Border Template
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[350px] overflow-y-auto p-3 custom-scrollbar border border-gray-200 dark:border-gray-700 rounded-2xl bg-gray-50 dark:bg-gray-900/80">
                        <div 
                            @click="selectedTemplate = ''; selectedTemplateImage = ''" 
                            class="relative aspect-[4/3] rounded-xl border-2 transition-all cursor-pointer flex flex-col items-center justify-center gap-2 group"
                            :class="selectedTemplate === '' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 shadow-md' : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-800 hover:border-emerald-300 dark:hover:border-emerald-800'"
                        >
                            <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 group-hover:text-emerald-500 transition-colors">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">No Border</span>
                            <input type="radio" name="template_id" value="" x-model="selectedTemplate" class="hidden">
                        </div>

                        @foreach($templates as $template)
                            @php
                                $userPlan = strtolower(tenant('plan') ?? 'basic');
                                $templateType = strtolower($template->type);
                                $isLocked = true;
                                if ($userPlan === 'ultimate')
                                    $isLocked = false;
                                elseif ($userPlan === 'pro' && in_array($templateType, ['basic', 'pro', 'free']))
                                    $isLocked = false;
                                elseif (in_array($templateType, ['basic', 'free']))
                                    $isLocked = false;
                            @endphp
                            <div 
                                @click="{{ $isLocked ? '' : 'selectTemplate(\'' . $template->id . '\', \'' . $template->image . '\')' }}" 
                                data-template-id="{{ $template->id }}"
                                class="relative aspect-[4/3] rounded-xl border-2 transition-all cursor-pointer group overflow-hidden"
                                :class="selectedTemplate === '{{ $template->id }}' ? 'border-emerald-500 shadow-md scale-[1.02]' : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-800 {{ $isLocked ? 'opacity-40 grayscale cursor-not-allowed' : 'hover:border-emerald-300 dark:hover:border-emerald-800' }}'"
                            >
                                @if($template->image)
                                    <img src="{{ global_asset('template/' . $template->image) }}" class="w-full h-full object-contain p-2 group-hover:scale-110 transition-transform duration-300" alt="{{ $template->name }}">
                                @endif

                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-2 translate-y-2 group-hover:translate-y-0 transition-transform duration-200">
                                    <p class="text-[9px] font-black text-white text-center truncate tracking-tight">{{ $template->name }}</p>
                                </div>

                                @if($isLocked)
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 backdrop-blur-[1px]">
                                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                                            <svg fill="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-white">
                                                <path d="M12 2C9.243 2 7 4.243 7 7V9H6C4.897 9 4 9.897 4 11V21C4 22.103 4.897 23 6 23H18C19.103 23 20 22.103 20 21V11C20 9.897 19.103 9 18 9H17V7C17 4.243 14.757 2 12 2ZM9 7C9 5.346 10.346 4 12 4C13.654 4 15 5.346 15 7V9H9V7ZM6 11H18V21H6V11Z"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endif

                                <input type="radio" name="template_id" value="{{ $template->id }}" x-model="selectedTemplate" class="hidden" {{ $isLocked ? 'disabled' : '' }}>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-6">
                    {{-- Styling Options --}}
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 h-full">
                        <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-6">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.828 2.828a2 2 0 010 2.828l-8.486 8.486L5 21l1.657-1.657" />
                            </svg>
                            Card Styling & Orientation
                        </label>

                        <div class="space-y-6">
                            <div>
                                <span class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Background Color & Glow</span>
                                <div class="flex items-center gap-4">
                                    <input type="color" x-model="bgColor" class="w-14 h-14 rounded-xl border-4 border-white dark:border-gray-800 shadow-lg cursor-pointer">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: bgColor, boxShadow: '0 0 10px ' + bgColor }"></div>
                                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300" x-text="bgColor.toUpperCase()"></span>
                                        </div>
                                        <p class="text-[10px] text-gray-500 font-medium">This color will be applied to the container with a soft glow effect.</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <span class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Preset Border Colors</span>
                                <div class="grid grid-cols-4 sm:grid-cols-8 gap-2">
                                    <template x-for="p in presetColors" :key="p.color">
                                        <button type="button" 
                                            @click="borderColor = p.color" 
                                            class="w-full aspect-square rounded-lg border-2 transition-all"
                                            :style="{ backgroundColor: p.color }"
                                            :class="borderColor === p.color ? 'border-white ring-2 ring-emerald-500 scale-110 shadow-lg' : 'border-transparent hover:scale-105'"
                                            :title="p.name">
                                        </button>
                                    </template>
                                    <button type="button" 
                                        @click="borderColor = 'transparent'" 
                                        class="w-full aspect-square rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-center text-gray-400 transition-all"
                                        :class="borderColor === 'transparent' ? 'ring-2 ring-emerald-500 scale-110 shadow-lg' : 'hover:scale-105'"
                                        title="None">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <span class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Title Color</span>
                                    <input type="color" x-model="titleColor" class="w-full h-10 rounded-lg border-2 border-gray-100 dark:border-gray-700 cursor-pointer">
                                </div>
                                <div>
                                    <span class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Content Color</span>
                                    <input type="color" x-model="contentColor" class="w-full h-10 rounded-lg border-2 border-gray-100 dark:border-gray-700 cursor-pointer">
                                </div>
                                <div>
                                    <span class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Category Color</span>
                                    <input type="color" x-model="categoryColor" class="w-full h-10 rounded-lg border-2 border-gray-100 dark:border-gray-700 cursor-pointer">
                                </div>
                            </div>

                            <div>
                                <span class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Card Orientation</span>
                                <div class="grid grid-cols-3 gap-3">
                                    <button type="button" @click="layoutType = 'landscape'" 
                                        class="p-3 rounded-xl border-2 transition-all flex flex-col items-center gap-2"
                                        :class="layoutType === 'landscape' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600' : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-800 text-gray-400 hover:border-emerald-300 dark:hover:border-emerald-700'">
                                        <div class="w-8 h-5 border-2 border-current rounded-sm"></div>
                                        <span class="text-[10px] font-black uppercase tracking-wider">Landscape</span>
                                    </button>
                                    <button type="button" @click="layoutType = 'portrait'" 
                                        class="p-3 rounded-xl border-2 transition-all flex flex-col items-center gap-2"
                                        :class="layoutType === 'portrait' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600' : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-800 text-gray-400 hover:border-emerald-300 dark:hover:border-emerald-700'">
                                        <div class="w-5 h-8 border-2 border-current rounded-sm"></div>
                                        <span class="text-[10px] font-black uppercase tracking-wider">Portrait</span>
                                    </button>
                                    <button type="button" @click="layoutType = 'square'" 
                                        class="p-3 rounded-xl border-2 transition-all flex flex-col items-center gap-2"
                                        :class="layoutType === 'square' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600' : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-800 text-gray-400 hover:border-emerald-300 dark:hover:border-emerald-700'">
                                        <div class="w-6 h-6 border-2 border-current rounded-sm"></div>
                                        <span class="text-[10px] font-black uppercase tracking-wider">Square</span>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <span class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Inner Media Layout</span>
                                <div class="grid grid-cols-3 gap-3">
                                    <button type="button" @click="mediaLayout = 'landscape'" 
                                        class="p-3 rounded-xl border-2 transition-all flex flex-col items-center gap-2"
                                        :class="mediaLayout === 'landscape' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-600' : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-800 text-gray-400 hover:border-blue-300'">
                                        <div class="w-6 h-4 border-2 border-current rounded-sm"></div>
                                        <span class="text-[10px] font-black uppercase tracking-wider">Landscape</span>
                                    </button>
                                    <button type="button" @click="mediaLayout = 'portrait'" 
                                        class="p-3 rounded-xl border-2 transition-all flex flex-col items-center gap-2"
                                        :class="mediaLayout === 'portrait' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-600' : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-800 text-gray-400 hover:border-blue-300'">
                                        <div class="w-4 h-6 border-2 border-current rounded-sm"></div>
                                        <span class="text-[10px] font-black uppercase tracking-wider">Portrait</span>
                                    </button>
                                    <button type="button" @click="mediaLayout = 'square'" 
                                        class="p-3 rounded-xl border-2 transition-all flex flex-col items-center gap-2"
                                        :class="mediaLayout === 'square' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-600' : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-800 text-gray-400 hover:border-blue-300'">
                                        <div class="w-5 h-5 border-2 border-current rounded-sm"></div>
                                        <span class="text-[10px] font-black uppercase tracking-wider">Square</span>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="block text-xs font-black text-gray-400 uppercase tracking-widest">
                                        <div class="flex items-center gap-2">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.125 1.125 0 011.924.907l1.45 2.901a1.125 1.125 0 00.907 1.924l2.901 1.45c1.756.426 1.756 2.924 0 3.35a1.125 1.125 0 00-.907 1.924l-1.45 2.901a1.125 1.125 0 01-1.924.907c-1.756-.426-2.924-.426-3.35 0a1.125 1.125 0 01-1.924-.907l-1.45-2.901a1.125 1.125 0 00-.907-1.924l-2.901-1.45c-1.756-.426-1.756-2.924 0-3.35a1.125 1.125 0 00.907-1.924l1.45-2.901a1.125 1.125 0 011.924-.907z" />
                                            </svg>
                                            Border Radius
                                        </div>
                                    </span>
                                    <span class="text-[10px] font-black text-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md" x-text="borderRadius + 'px'"></span>
                                </div>
                                <input type="range" x-model="borderRadius" min="0" max="60" step="4" class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                                <div class="flex justify-between mt-1 px-1">
                                    <span class="text-[8px] font-bold text-gray-400 uppercase">Sharp</span>
                                    <span class="text-[8px] font-bold text-gray-400 uppercase">Round</span>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-2.414-2.414A1 1 0 0015.586 6H7a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                    </svg>
                                    <span>Font Style</span>
                                </div>
                                <select x-model="fontStyle" class="form-select w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="font-sans">Sans Serif (Default)</option>
                                    <option value="font-serif">Serif</option>
                                    <option value="font-mono">Monospace</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Live Preview Section --}}
        <div class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Live Preview
                </label>
                <div class="flex gap-2">
                    <button type="button" @click="layoutType = 'landscape'" class="p-2 rounded-lg transition-colors" :class="layoutType === 'landscape' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v16.5h16.5V3.75H3.75Zm16.5 4.5h-16.5" />
                        </svg>
                    </button>
                    <button type="button" @click="layoutType = 'portrait'" class="p-2 rounded-lg transition-colors" :class="layoutType === 'portrait' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v16.5h16.5V3.75H3.75Zm4.5 16.5V3.75" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex justify-center p-8 bg-gray-100 dark:bg-gray-950 rounded-[2rem] border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div id="announcementPreview" 
                     class="transition-all duration-500 shadow-2xl relative flex flex-col p-12 gap-8"
                     :class="{
                         'w-full': layoutType === 'landscape',
                         'w-full max-w-4xl mx-auto': layoutType === 'portrait',
                         'w-full max-w-full mx-auto': layoutType === 'square'
                     }"
                    :style="{ 
                        backgroundColor: bgColor,
                        boxShadow: '0 30px 60px -12px ' + bgColor + '66',
                        borderRadius: '4rem',
                        border: selectedTemplate ? '50px solid transparent' : (borderColor !== 'transparent' ? '8px solid ' + borderColor : '1px solid rgba(0,0,0,0.05)'),
                        borderImage: selectedTemplate ? 'url({{ global_asset('template') }}/' + selectedTemplateImage + ') 120 round' : 'none',
                        borderImageOutset: selectedTemplate ? '15px' : '0'
                    }"
                >
            <div class="flex-1 flex flex-col gap-6 relative z-10 overflow-hidden" :class="fontStyle">
                <div class="space-y-4 shrink-0">
                    <div class="flex-1 space-y-1">
                        <h3 class="text-4xl font-black leading-[1.1] tracking-tight break-words" 
                            :style="{ color: titleColor }"
                            x-text="title || 'Announcement Title'"></h3>
                        <div class="flex items-center gap-3 pt-2">
                            <span class="px-4 py-1.5 bg-black/5 rounded-full text-[12px] font-black uppercase tracking-[0.1em] shadow-sm" 
                                :style="{ color: categoryColor }"
                                x-text="category || 'General'"></span>
                            <div class="flex flex-col">
                                <span class="text-[12px] font-black text-gray-800 dark:text-gray-200">{{ auth()->user()->name }}</span>
                                <span class="text-[11px] font-bold text-gray-400">Just now</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="absolute -left-6 top-0 bottom-0 w-1 bg-gradient-to-b from-black/10 via-black/5 to-transparent rounded-full opacity-50"></div>
                    <p class="text-xl leading-relaxed break-words font-medium opacity-90" 
                        :style="{ color: contentColor }"
                        x-text="content || 'Write your announcement content here...'"></p>
                </div>
                
                {{-- Inner Image Container --}}
                <div x-show="mediaPreview.length > 0" 
                    class="w-full aspect-video bg-gray-50 dark:bg-gray-900 border-4 border-white dark:border-gray-800 overflow-hidden shadow-2xl relative group/media shrink-0 transition-all duration-300"
                    :class="{
                        'aspect-video': mediaLayout === 'landscape',
                        'aspect-[3/4]': mediaLayout === 'portrait',
                        'aspect-square': mediaLayout === 'square'
                    }"
                    :style="{ borderRadius: borderRadius + 'px' }">
                    <template x-if="mediaPreview.length > 0">
                        <div class="w-full h-full grid gap-2" :class="{
                            'grid-cols-1': mediaPreview.length === 1,
                            'grid-cols-2': mediaPreview.length === 2,
                            'grid-cols-2 grid-rows-2': mediaPreview.length >= 3
                        }">
                            <template x-for="(media, index) in mediaPreview.slice(0, 4)" :key="index">
                                <div class="relative w-full h-full overflow-hidden bg-gray-100 dark:bg-gray-800" :class="{
                                    'col-span-2 row-span-1': mediaPreview.length === 3 && index === 0,
                                    'col-span-1 row-span-2': mediaPreview.length === 2,
                                    'col-span-1 row-span-2': mediaPreview.length === 1
                                }">
                                    <template x-if="media.type === 'image'">
                                        <img :src="media.url" class="w-full h-full object-cover hover:scale-110 transition-transform duration-700">
                                    </template>
                                    <template x-if="media.type === 'video'">
                                        <div class="w-full h-full flex items-center justify-center relative">
                                            <video :src="media.url" class="w-full h-full object-cover" muted></video>
                                            <div class="absolute inset-0 flex items-center justify-center bg-black/20">
                                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    {{-- Overlay for 5th image --}}
                                    <template x-if="index === 3 && mediaPreview.length > 4">
                                        <div class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center">
                                            <span class="text-white font-black text-3xl">+<span x-text="mediaPreview.length - 4"></span></span>
                                            <span class="text-white/70 text-xs font-bold uppercase tracking-widest mt-1">View All</span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                
                {{-- Decorative placeholder when no media is selected --}}
                <div x-show="mediaPreview.length === 0" class="mt-auto w-full h-24 bg-gradient-to-r from-black/5 to-transparent rounded-2xl border border-black/5 flex items-center justify-center opacity-40 shrink-0">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-6 h-6 text-black/20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                {{-- Mockup Footer --}}
                <div class="mt-auto flex items-center justify-between border-t border-black/5 pt-6 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 px-4 py-2 bg-black/5 rounded-2xl text-[14px] font-bold text-gray-700">
                            <span>❤️</span> <span class="text-xs">0</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 bg-black/5 rounded-2xl text-[14px] font-bold text-gray-700">
                            <span>👍</span> <span class="text-xs">0</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 bg-black/5 rounded-2xl text-[14px] font-bold text-gray-700">
                            <span>🔥</span> <span class="text-xs">0</span>
                        </div>
                    </div>
                    <div class="p-3 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
            <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-4">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Target Audience & Options
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                    <input
                        type="checkbox"
                        x-model="targetAll"
                        class="w-5 h-5 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500"
                    />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">All Students (School-wide)</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer group">
                    <input
                        type="checkbox"
                        x-ref="annPinned"
                        class="w-5 h-5 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500"
                    />
                    <div class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        Pin this announcement
                    </div>
                </label>
            </div>
            
            <div x-show="!targetAll" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                
                <div class="flex flex-wrap gap-2 mb-4">
                    <template x-for="c in targetColleges" :key="'c'+c">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 rounded-full text-[10px] font-black uppercase tracking-wider border border-blue-100 dark:border-blue-900/40">
                            <span x-text="c"></span>
                            <button type="button" @click="targetColleges = targetColleges.filter(i => i !== c)" class="hover:text-blue-800">&times;</button>
                        </span>
                    </template>
                    <template x-for="p in targetPrograms" :key="'p'+p">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300 rounded-full text-[10px] font-black uppercase tracking-wider border border-purple-100 dark:border-purple-900/40">
                            <span x-text="p"></span>
                            <button type="button" @click="targetPrograms = targetPrograms.filter(i => i !== p)" class="hover:text-purple-800">&times;</button>
                        </span>
                    </template>
                    <template x-for="s in targetSections" :key="'s'+s">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-300 rounded-full text-[10px] font-black uppercase tracking-wider border border-amber-100 dark:border-amber-900/40">
                            <span x-text="s"></span>
                            <button type="button" @click="targetSections = targetSections.filter(i => i !== s)" class="hover:text-amber-800">&times;</button>
                        </span>
                    </template>
                    <template x-for="r in targetRoles" :key="'r'+r">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-300 rounded-full text-[10px] font-black uppercase tracking-wider border border-rose-100 dark:border-rose-900/40">
                            <span x-text="r"></span>
                            <button type="button" @click="targetRoles = targetRoles.filter(i => i !== r)" class="hover:text-rose-800">&times;</button>
                        </span>
                    </template>
                    <template x-if="targetColleges.length + targetPrograms.length + targetGradeLevels.length + targetYearLevels.length + targetStrands.length + targetSections.length + targetRoles.length === 0">
                        <p class="text-[11px] text-gray-400 font-bold italic">No specific audience selected yet.</p>
                    </template>
                </div>

                <button type="button" @click="showTargetingModal = true" class="w-full py-3 bg-gray-100 dark:bg-gray-800 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl text-xs font-black uppercase tracking-widest text-gray-500 hover:bg-[var(--accent)] hover:text-white hover:border-[var(--accent)] transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Select Target Audience(s)
                </button>
            </div>
        </div>

        {{-- NEW Targeting Modal --}}
        <template x-teleport="body">
            <div x-show="showTargetingModal" class="fixed inset-0 z-[120] flex items-center justify-center p-4 sm:p-6" x-cloak>
                <div class="absolute inset-0 bg-gray-900/80 backdrop-blur-md" @click="showTargetingModal = false"></div>
                <div class="relative w-full max-w-2xl bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-800 flex flex-col max-h-[80vh]">
                    <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white">Target Audience</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Select multiple groups for this announcement</p>
                        </div>
                        <button @click="showTargetingModal = false" class="w-10 h-10 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 flex items-center justify-center transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex border-b border-gray-100 dark:border-gray-800 overflow-x-auto whitespace-nowrap custom-scrollbar px-4">
                        @foreach(['role' => 'Roles', 'college' => 'Colleges', 'program' => 'Courses', 'level' => 'Years', 'grade_level' => 'Grades', 'strand' => 'Strands', 'section' => 'Sections'] as $key => $label)
                            <button @click="targetingTab = '{{ $key }}'" 
                                    class="px-4 py-4 text-[10px] font-black uppercase tracking-widest transition-all border-b-2"
                                    :class="targetingTab === '{{ $key }}' ? 'text-[var(--accent)] border-[var(--accent)]' : 'text-gray-400 border-transparent hover:text-gray-600'">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="p-6 flex-1 overflow-y-auto custom-scrollbar">
                        {{-- Targeting Lists --}}
                        <div class="mb-4">
                            <input type="text" x-model="targetingSearch" placeholder="Search groups..." class="w-full bg-gray-50 dark:bg-gray-800/50 border-none rounded-xl text-xs font-bold px-4 py-3 focus:ring-2 focus:ring-[var(--accent)]/20 transition-all">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div x-show="targetingTab === 'role'" class="contents">
                                @foreach(['student' => 'Students', 'teacher' => 'Teachers'] as $roleKey => $roleLabel)
                                    <label class="flex items-center gap-3 p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-[var(--accent)] hover:bg-gray-50 dark:hover:bg-gray-700/80 transition-all cursor-pointer group shadow-sm">
                                        <input type="checkbox" value="{{ $roleKey }}" x-model="targetRoles" class="w-6 h-6 rounded-lg border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-gray-900 dark:text-white group-hover:text-[var(--accent)] transition-colors uppercase tracking-widest">{{ $roleLabel }}</span>
                                            <span class="text-[9px] text-gray-500 dark:text-gray-400 font-bold">Target all users with the {{ $roleKey }} role</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            @foreach(['college' => 'targetColleges', 'program' => 'targetPrograms', 'level' => 'targetYearLevels', 'grade_level' => 'targetGradeLevels', 'strand' => 'targetStrands', 'section' => 'targetSections'] as $type => $stateKey)
                                <div x-show="targetingTab === '{{ $type }}'" class="contents">
                                    @php 
                                                                            $items = match ($type) {
                                            'college' => $colleges,
                                            'program' => $programs,
                                            'level' => $yearLevels,
                                            'grade_level' => $gradeLevels,
                                            'strand' => $strands,
                                            'section' => $sections,
                                            default => collect()
                                        };
                                    @endphp
                                    @foreach($items as $item)
                                        <label x-show="!targetingSearch || '{{ strtolower($item->name) }}'.includes(targetingSearch.toLowerCase())" 
                                               class="flex items-center gap-3 p-3 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-[var(--accent)] hover:bg-gray-50 dark:hover:bg-gray-700/80 transition-all cursor-pointer group shadow-sm">
                                            <input type="checkbox" value="{{ $item->name }}" x-model="{{ $stateKey }}" class="w-5 h-5 rounded border-gray-300 text-[var(--accent)] focus:ring-[var(--accent)]">
                                            <span class="text-xs font-black text-gray-900 dark:text-white group-hover:text-[var(--accent)] transition-colors">{{ $item->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50/50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <p class="text-[10px] font-black text-gray-400 px-2 uppercase tracking-widest"><span x-text="targetColleges.length + targetPrograms.length + targetGradeLevels.length + targetYearLevels.length + targetStrands.length + targetSections.length + targetRoles.length"></span> Selected</p>
                        <button type="button" @click="showTargetingModal = false" class="px-8 py-3 bg-[var(--accent)] text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-[var(--accent-dark)] transition-all shadow-lg active:scale-95 border-none">Save Selection</button>
                    </div>
                </div>
            </div>
        </template>

        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100 dark:border-gray-800 relative z-[50]">
            <button type="button" 
                    @click="submitForm('draft')" 
                    :disabled="isPublishing" 
                    class="px-6 py-3.5 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl text-sm font-bold shadow-xl shadow-amber-500/20 transition-all flex items-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 pointer-events-auto">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5" x-show="!isPublishing">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span x-show="isPublishing" class="w-4 h-4 border-3 border-white border-t-transparent rounded-full animate-spin"></span>
                <span x-text="isPublishing ? 'Saving...' : 'Save Draft'"></span>
            </button>

            <button type="submit" 
                    :disabled="isPublishing" 
                    class="min-w-[220px] px-10 py-3.5 bg-emerald-500 text-white rounded-2xl text-sm font-black hover:bg-emerald-600 shadow-xl shadow-emerald-500/20 transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 pointer-events-auto">
                <div x-show="!isPublishing" class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                    <span x-text="isEdit ? 'Update Announcement' : 'Publish Announcement'"></span>
                </div>
                <div x-show="isPublishing" class="flex items-center gap-2" x-cloak>
                    <span class="w-4 h-4 border-3 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="isEdit ? 'Updating...' : 'Publishing...'"></span>
                </div>
            </button>
        </div>
    </form>
</div>

<script>
    // Universal helper for external modules to check unsaved changes
    window.hasUnsavedChanges = function() {
        const div = document.querySelector('[x-data]');
        if (!div) return false;
        try {
            const data = div._x_dataStack ? div._x_dataStack[0] : (div.__x ? div.__x.$data : null);
            if (!data) return false;
            return (data.title && data.title.trim() !== '') || 
                   (data.category && data.category.trim() !== '') || 
                   (data.content && data.content.trim() !== '');
        } catch(e) { return false; }
    };

    window.clearAnnouncementDraft = function() {
        localStorage.removeItem('announcement_draft_local');
        const div = document.querySelector('[x-data]');
        if (div && (div._x_dataStack || div.__x)) {
            // Force a reload to clear state cleanly if requested externally
            window.location.reload();
        }
    };
</script>











