@php
    $categories = ['General', 'Academic', 'Events', 'Urgent'];
    $programs = ['BSIT', 'BSEMC'];
    $yearLevels = [1, 2, 3, 4];
    $sections = ['A', 'B', 'C', 'D', 'E', 'F'];
@endphp

<div class="space-y-6" x-data="{ targetAll: true, targetProgram: '', targetYear: '', targetSection: '' }">
    <form id="teacherAnnouncementForm" class="space-y-6">
        @csrf

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
                    required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
                >
                    <option value="">Select category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            @else
                <input type="hidden" id="annCategory" name="category" value="General">
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
                placeholder="Write your announcement..."
                rows="5"
                required
                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all resize-none"
            ></textarea>
        </div>

        <div>
            <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4 text-emerald-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Media Upload
            </label>
            <div class="relative group border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-8 text-center hover:border-emerald-500 hover:bg-emerald-50/10 transition-all cursor-pointer">
                <input
                    type="file"
                    id="annMedia"
                    name="media[]"
                    multiple
                    accept="{{ tenant() && tenant()->hasFeature('video_upload') ? 'image/*,video/*' : 'image/jpeg,image/png' }}"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                />
                <div class="space-y-2">
                    <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center mx-auto text-emerald-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300">Click or drag to upload media</p>
                    <p class="text-xs text-gray-500">
                        {{ tenant() && tenant()->hasFeature('video_upload') ? 'Supports images and videos (max 100MB)' : 'Supports images only (JPEG, PNG)' }}
                    </p>
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
                <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-white dark:hover:bg-gray-800 transition-colors cursor-pointer">
                    <input
                        type="checkbox"
                        x-model="targetAll"
                        class="w-5 h-5 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500"
                    />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">All Students (School-wide)</span>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl hover:bg-white dark:hover:bg-gray-800 transition-colors cursor-pointer">
                    <input
                        type="checkbox"
                        id="annPinned"
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
                 class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 text-center">Program</label>
                    <select
                        id="annProgram"
                        name="target_program"
                        x-model="targetProgram"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm"
                    >
                        <option value="">All Programs</option>
                        @foreach($programs as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 text-center">Year Level</label>
                    <select
                        id="annYear"
                        name="target_year"
                        x-model="targetYear"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm"
                    >
                        <option value="">All Years</option>
                        @foreach($yearLevels as $y)
                            <option value="{{ $y }}">Year {{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 text-center">Section</label>
                    <select
                        id="annSection"
                        name="target_section"
                        x-model="targetSection"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm"
                    >
                        <option value="">All Sections</option>
                        @foreach($sections as $s)
                            <option value="{{ $s }}">Section {{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
            <button type="button" onclick="saveAsDraft()" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Save as Draft
            </button>
            <button type="submit" class="px-10 py-3 bg-emerald-500 text-white rounded-xl text-sm font-bold hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                </svg>
                Publish Announcement
            </button>
        </div>
    </form>
</div>

<script>
    const DRAFT_KEY = 'announcement_draft';

    // Function to check if there are unsaved changes
    window.hasUnsavedChanges = function() {
        const title = document.getElementById('annTitle').value;
        const category = document.getElementById('annCategory').value;
        const content = document.getElementById('annContent').value;
        return title.trim() !== '' || category.trim() !== '' || content.trim() !== '';
    };

    // Function to clear the draft
    window.clearAnnouncementDraft = function() {
        localStorage.removeItem(DRAFT_KEY);
        document.getElementById('teacherAnnouncementForm').reset();
    };

    // Save as Draft function
    window.saveAsDraft = function() {
        const draft = {
            title: document.getElementById('annTitle').value,
            category: document.getElementById('annCategory').value,
            content: document.getElementById('annContent').value,
            targetAll: document.querySelector('[x-data]')?.__x?.$data?.targetAll ?? true,
            timestamp: new Date().getTime()
        };
        localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
        
        // Dispatch event for custom success modal
        window.dispatchEvent(new CustomEvent('save-draft-success'));
        
        // Close modal
        if (window.Alpine) {
            // Find the parent Alpine data that has showingCreateModal
            let currentEl = document.getElementById('teacherAnnouncementForm');
            while (currentEl) {
                if (currentEl.__x && currentEl.__x.$data.hasOwnProperty('showingCreateModal')) {
                    currentEl.__x.$data.showingCreateModal = false;
                    break;
                }
                currentEl = currentEl.parentElement;
            }
        }
    };

    // Load Draft on initialization
    const loadDraft = () => {
        const savedDraft = localStorage.getItem(DRAFT_KEY);
        if (savedDraft) {
            const draft = JSON.parse(savedDraft);
            const titleEl = document.getElementById('annTitle');
            const catEl = document.getElementById('annCategory');
            const contEl = document.getElementById('annContent');
            
            if (titleEl) titleEl.value = draft.title || '';
            if (catEl) catEl.value = draft.category || '';
            if (contEl) contEl.value = draft.content || '';
            
            // Wait for Alpine to be ready to set targetAll
            setTimeout(() => {
                const alpineEl = document.querySelector('[x-data]');
                if (alpineEl && alpineEl.__x && draft.targetAll !== undefined) {
                    alpineEl.__x.$data.targetAll = draft.targetAll;
                }
            }, 100);
        }
    };

    // Initialize draft loading
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadDraft);
    } else {
        loadDraft();
    }

    // Auto-save progress locally as user types
    const inputs = ['annTitle', 'annCategory', 'annContent'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', () => {
                const draft = {
                    title: document.getElementById('annTitle').value,
                    category: document.getElementById('annCategory').value,
                    content: document.getElementById('annContent').value,
                    timestamp: new Date().getTime()
                };
                localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
            });
        }
    });
</script>

    <script>
        document.getElementById('teacherAnnouncementForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const title = document.getElementById('annTitle').value;
            const category = document.getElementById('annCategory').value;
            const content = document.getElementById('annContent').value;
            const isPinned = document.getElementById('annPinned').checked;
            const mediaFiles = document.getElementById('annMedia').files;
            
            // Handle Media
            let mediaHtml = '';
            if (mediaFiles.length > 0) {
                const mediaUrls = Array.from(mediaFiles).map(file => ({
                    url: URL.createObjectURL(file),
                    type: file.type.startsWith('video') ? 'video' : 'image'
                }));

                if (mediaUrls.length === 1) {
                    const item = mediaUrls[0];
                    if (item.type === 'video') {
                        mediaHtml = `
                            <div class="mt-4 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 aspect-video bg-black media-thumb cursor-pointer" data-src="${item.url}" data-type="video">
                                <video class="w-full h-full" controls src="${item.url}"></video>
                            </div>`;
                    } else {
                        mediaHtml = `
                            <div class="mt-4 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 media-thumb cursor-pointer" data-src="${item.url}" data-type="image">
                                <img src="${item.url}" class="w-full h-auto max-h-[400px] object-cover hover:scale-105 transition-transform duration-200">
                            </div>`;
                    }
                } else if (mediaUrls.length >= 2) {
                    mediaHtml = `<div class="mt-4 grid grid-cols-2 gap-3">`;
                    mediaUrls.slice(0, 2).forEach(item => {
                        if (item.type === 'video') {
                            mediaHtml += `
                                <div class="rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 aspect-video bg-black media-thumb cursor-pointer" data-src="${item.url}" data-type="video">
                                    <video class="w-full h-full" controls src="${item.url}"></video>
                                </div>`;
                        } else {
                            mediaHtml += `
                                <div class="rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 aspect-video media-thumb cursor-pointer" data-src="${item.url}" data-type="image">
                                    <img src="${item.url}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                                </div>`;
                        }
                    });
                    mediaHtml += `</div>`;
                }
            }
            
            // Create a static announcement card
            const announcementList = document.getElementById('announcements-list');
            if (announcementList) {
                const newCard = document.createElement('div');
                newCard.className = 'relative group';
                newCard.innerHTML = `
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 overflow-hidden flex items-center justify-center">
                                    <img src="/images/download.jpg" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">${document.body.dataset.userName || 'User'}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 ann-meta">${new Date().toISOString().split('T')[0]} · ${category}</p>
                                </div>
                            </div>
                            ${isPinned ? '<span class="ann-pinned-badge px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-[10px] font-bold uppercase rounded-md tracking-wider">Pinned</span>' : ''}
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-2 ann-title">${title}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed ann-content">${content}</p>
                        ${mediaHtml}
                    </div>
                    <div class="absolute top-4 right-4 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="openEditModal(this)" class="p-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            </svg>
                        </button>
                        <button 
                            type="button" 
                            @click="confirmingDeletion = true"
                            class="p-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </div>
                `;
                // Re-attach lightbox listener to new media thumbs
                newCard.querySelectorAll('.media-thumb').forEach(el => {
                    el.addEventListener('click', function() {
                        const src  = this.getAttribute('data-src');
                        const type = this.getAttribute('data-type');
                        const lb   = document.getElementById('mediaLightbox');
                        const img  = document.getElementById('lightboxImg');
                        const vid  = document.getElementById('lightboxVideo');
                        img.style.display = 'none'; vid.style.display = 'none';
                        if (type === 'video') { vid.src = src; vid.style.display = 'block'; }
                        else                  { img.src = src; img.style.display = 'block'; }
                        lb.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                    });
                });
                announcementList.prepend(newCard);
            }
            
            // Hide form and reset
            window.clearAnnouncementDraft();
            
            // Show custom success modal
            const alpineData = document.querySelector('[x-data]').__x.$data;
            alpineData.showSuccess('Announcement published successfully');
            alpineData.showingCreateModal = false;
        });
    </script>
</div>










