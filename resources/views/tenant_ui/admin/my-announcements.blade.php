<x-app-layout>
    <x-slot name="title">My Announcements - EduBoard Admin</x-slot>

    <div class="admin-content" x-data="{ 
        announcementModal: false,
        confirmingDeletion: false, 
        showingSuccess: false,
        modalTitle: 'New Announcement',
        successMessage: '',
        successIcon: null,
        showSuccess(msg, icon = null) {
            this.successMessage = msg;
            this.successIcon = icon;
            this.showingSuccess = true;
        }
    }">

        {{-- Page Header --}}
        <div class="content-header flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">My Announcements</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Manage and track the announcements you've posted</p>
            </div>
            <button class="px-5 py-2.5 bg-teal-500 text-white rounded-xl text-sm font-bold hover:bg-teal-600 transition-all flex items-center gap-2 shadow-lg shadow-teal-500/20 active:scale-95" @click="modalTitle = 'New Announcement'; announcementModal = true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Announcement
            </button>
        </div>

        {{-- Empty State --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm mb-8">
            <div class="w-20 h-20 bg-teal-50 dark:bg-teal-900/20 rounded-full flex items-center justify-center text-teal-500 mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">You haven't posted any announcements yet</h2>
            <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-8">Share news, updates, or events with the school community to keep everyone informed and engaged.</p>
            <button class="px-8 py-3 bg-teal-500 text-white rounded-2xl text-sm font-bold hover:bg-teal-600 transition-all shadow-md shadow-teal-500/20" @click="modalTitle = 'New Announcement'; announcementModal = true">
                Post Your First Announcement
            </button>
        </div>

        {{-- Announcement Modal --}}
        <template x-teleport="body">
            <div x-show="announcementModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="announcementModal = false"></div>
                <div class="relative w-full max-w-3xl bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter">
                    <div class="px-8 py-6 border-b border-gray-50 dark:border-gray-700/50 flex items-center justify-between bg-white dark:bg-gray-800 sticky top-0 z-10">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight" x-text="modalTitle"></h3>
                            <p class="text-xs text-gray-500 font-medium">Create a new announcement for the school community</p>
                        </div>
                        <button @click="announcementModal = false" class="w-10 h-10 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-all flex items-center justify-center">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-8 max-h-[80vh] overflow-y-auto">
                        <x-announcement-form />
                    </div>
                </div>
            </div>
        </template>
                    {{-- Photo Display --}}
                    <div class="rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 cursor-pointer media-thumb" data-src="{{ asset('images/download.jpg') }}" data-type="image">
                        <img src="{{ asset('images/download.jpg') }}" alt="Sports Festival Poster" class="w-full h-auto max-h-[400px] object-cover hover:scale-105 transition-transform duration-200">
                    </div>
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Custom Success Modal --}}
        <template x-teleport="body">
            <div x-show="showingSuccess" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showingSuccess = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Success</h3>
                        <button @click="showingSuccess = false" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 text-center">
                        <template x-if="successIcon">
                            <div class="mb-4">
                                <img :src="successIcon" class="w-20 h-20 rounded-xl object-cover mx-auto border-4 border-green-50 dark:border-green-900/30 shadow-sm">
                            </div>
                        </template>
                        <template x-if="!successIcon">
                            <svg class="animated-check" viewBox="0 0 52 52">
                                <circle class="animated-check-circle" cx="26" cy="26" r="25" fill="none" />
                                <path class="animated-check-path" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                            </svg>
                        </template>
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed" x-text="successMessage"></p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex justify-end">
                        <button @click="showingSuccess = false" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-all">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Custom Delete Confirmation Modal --}}
        <template x-teleport="body">
            <div x-show="confirmingDeletion" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="confirmingDeletion = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Delete Announcement</h3>
                        <button @click="confirmingDeletion = false" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                            Are you sure you want to delete this announcement? This action cannot be undone.
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex items-center justify-end gap-3">
                        <button @click="confirmingDeletion = false" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button @click="confirmingDeletion = false; showSuccess('Announcement deleted successfully')" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition-all">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </template>

    </div>

    {{-- Edit Announcement Modal --}}
    <style>
        .edit-media-grid { display:flex; flex-wrap:wrap; gap:10px; margin-top:6px; }
        .edit-media-item { position:relative; width:100px; height:80px; flex-shrink:0; }
        .edit-media-item img, .edit-media-item video {
            width:100px; height:80px; object-fit:cover;
            border-radius:10px; border:2px solid #e5e7eb; display:block;
        }
        .edit-media-del {
            position:absolute; top:-8px; right:-8px;
            width:22px; height:22px; border-radius:50%;
            background:#ef4444; border:2px solid #fff;
            color:#fff; font-size:14px; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 2px 6px rgba(0,0,0,0.2);
            transition:background 0.15s, transform 0.1s; z-index:2;
        }
        .edit-media-del:hover { background:#b91c1c; transform:scale(1.12); }
        .edit-no-media { font-size:0.8rem; color:#9ca3af; font-style:italic; }
        .edit-upload-zone {
            border:2px dashed #d1d5db; border-radius:10px;
            padding:14px; text-align:center; cursor:pointer;
            background:#f9fafb; transition:border-color 0.2s, background 0.2s;
            margin-top:6px;
        }
        .edit-upload-zone:hover { border-color:#3b82f6; background:#eff6ff; }
        .edit-upload-zone input { display:none; }
        .edit-upload-zone span { font-size:0.8rem; color:#6b7280; }
        .edit-new-grid { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
        .edit-new-item { position:relative; width:80px; height:64px; }
        .edit-new-item img, .edit-new-item video {
            width:80px; height:64px; object-fit:cover;
            border-radius:8px; border:2px solid #3b82f6;
        }
        .edit-new-del {
            position:absolute; top:-7px; right:-7px;
            width:20px; height:20px; border-radius:50%;
            background:#ef4444; border:2px solid #fff;
            color:#fff; font-size:12px; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
        }
    </style>

    <div id="editModal" style="display:none; position:fixed; inset:0; z-index:9998; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); align-items:center; justify-content:center;" onclick="_editBdClick(event)">
        <div style="background:white; width:100%; max-width:580px; max-height:90vh; display:flex; flex-direction:column; overflow:hidden; border-radius:16px; box-shadow:0 24px 60px rgba(0,0,0,0.3); margin:16px;" class="dark:bg-gray-800">
            <!-- Header -->
            <div style="padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; background:white; flex-shrink:0;" class="dark:bg-gray-800 dark:border-gray-700">
                <h3 style="font-size:16px; font-weight:700; color:#111827;" class="dark:text-gray-100">Edit Announcement</h3>
                <button onclick="closeEditModal()" style="background:none; border:none; cursor:pointer; padding:4px; border-radius:8px; color:#6b7280;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <!-- Body -->
            <div style="padding:24px; display:flex; flex-direction:column; gap:16px; flex:1; overflow-y:auto;">
                <!-- Title -->
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; margin-bottom:6px;">Title</label>
                    <input id="editTitle" type="text" style="width:100%; padding:10px 12px; border:1.5px solid #d1d5db; border-radius:10px; font-size:14px; color:#111827; outline:none; box-sizing:border-box; font-family:inherit;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                </div>
                <!-- Content -->
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; margin-bottom:6px;">Content</label>
                    <textarea id="editContent" rows="4" style="width:100%; padding:10px 12px; border:1.5px solid #d1d5db; border-radius:10px; font-size:14px; color:#111827; outline:none; resize:vertical; box-sizing:border-box; font-family:inherit;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'"></textarea>
                </div>
                <!-- Category -->
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; margin-bottom:6px;">Category</label>
                    <select id="editCategory" style="width:100%; padding:10px 12px; border:1.5px solid #d1d5db; border-radius:10px; font-size:14px; color:#111827; background:#f9fafb; outline:none; box-sizing:border-box; cursor:pointer;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                        <option value="General">General</option>
                        <option value="Academic">Academic</option>
                        <option value="Events">Events</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>
                <!-- Pinned -->
                <div style="display:flex; align-items:center; gap:10px; padding:12px 14px; background:#fafafa; border:1.5px solid #e5e7eb; border-radius:10px;">
                    <input type="checkbox" id="editPinned" style="width:16px; height:16px; accent-color:#ef4444; cursor:pointer; flex-shrink:0;">
                    <label for="editPinned" style="font-size:13px; font-weight:600; color:#dc2626; cursor:pointer; user-select:none;">📌 Pin this announcement</label>
                </div>
                <!-- Existing media -->
                <div id="editMediaSection">
                    <label style="display:block; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; margin-bottom:6px;">Attached Media</label>
                    <div id="editMediaGrid" class="edit-media-grid"></div>
                </div>
                <!-- Add new media -->
                <div>
                    <label style="display:block; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#6b7280; margin-bottom:4px;">Add Images / Videos</label>
                    <div class="edit-upload-zone" onclick="document.getElementById('editFileInput').click()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:26px;height:26px;color:#9ca3af;margin:0 auto 4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                        <span>Click to upload images or videos</span>
                        <input type="file" id="editFileInput" accept="image/*,video/*" multiple onchange="_handleNewFiles(this)">
                    </div>
                    <div id="editNewGrid" class="edit-new-grid"></div>
                </div>
            </div>
            <!-- Footer -->
            <div style="padding:16px 24px; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:10px; background:white; flex-shrink:0;" class="dark:bg-gray-800 dark:border-gray-700">
                <button onclick="closeEditModal()" style="padding:9px 20px; background:#f3f4f6; border:none; border-radius:10px; font-size:14px; font-weight:600; color:#374151; cursor:pointer;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">Cancel</button>
                <button onclick="saveEdit()" style="padding:9px 22px; background:#2563eb; border:none; border-radius:10px; font-size:14px; font-weight:700; color:white; cursor:pointer;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Save Changes</button>
            </div>
        </div>
    </div>

    {{-- Lightbox Modal --}}
    <div id="mediaLightbox" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.88); backdrop-filter:blur(4px); align-items:center; justify-content:center;" onclick="closeLightbox(event)">
        <button onclick="closeLightboxBtn()" style="position:absolute; top:20px; right:24px; background:rgba(255,255,255,0.12); border:none; color:white; border-radius:50%; width:40px; height:40px; font-size:22px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">&times;</button>
        <div style="max-width:90vw; max-height:90vh; display:flex; align-items:center; justify-content:center;">
            <img id="lightboxImg" src="" alt="" style="display:none; max-width:90vw; max-height:86vh; object-fit:contain; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.6);">
            <video id="lightboxVideo" controls style="display:none; max-width:90vw; max-height:86vh; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.6);"></video>
        </div>
    </div>

    <script>
        document.body.dataset.userName = "{{ Auth::user()->name }}";

        window.addEventListener('announcement-published', (e) => {
            const alpineData = document.querySelector('[x-data]').__x.$data;
            alpineData.showSuccess(e.detail.message || 'Announcement published successfully');
            document.getElementById('new-announcement-form').classList.add('hidden');
        });

        document.querySelectorAll('.media-thumb').forEach(function(el) {
            el.addEventListener('click', function() {
                const src = this.getAttribute('data-src');
                const type = this.getAttribute('data-type');
                const lb = document.getElementById('mediaLightbox');
                const img = document.getElementById('lightboxImg');
                const vid = document.getElementById('lightboxVideo');
                img.style.display = 'none';
                vid.style.display = 'none';
                if (type === 'video') {
                    vid.src = src;
                    vid.style.display = 'block';
                } else {
                    img.src = src;
                    img.style.display = 'block';
                }
                lb.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });

        function closeLightbox(e) {
            if (e.target === document.getElementById('mediaLightbox')) closeLightboxBtn();
        }

        function closeLightboxBtn() {
            const lb = document.getElementById('mediaLightbox');
            lb.style.display = 'none';
            document.getElementById('lightboxVideo').pause && document.getElementById('lightboxVideo').pause();
            document.getElementById('lightboxVideo').src = '';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightboxBtn();
                closeEditModal();
            }
        });

        // ── Edit Modal Logic ──
        let _editTargetCard = null;
        let _editExistingMedia = []; // { el, src, type, deleted }
        let _editNewFiles = [];      // File objects

        function openEditModal(btn) {
            const card = btn.closest('.relative.group');
            _editTargetCard = card;
            _editNewFiles = [];

            // Populate text fields
            const titleEl   = card.querySelector('.ann-title');
            const contentEl = card.querySelector('.ann-content');
            const metaEl    = card.querySelector('.ann-meta');
            const pinnedEl  = card.querySelector('.ann-pinned-badge');
            document.getElementById('editTitle').value   = titleEl   ? titleEl.textContent.trim()   : '';
            document.getElementById('editContent').value = contentEl ? contentEl.textContent.trim() : '';

            // Parse category from meta text e.g. "2026-03-18 · Academic"
            const metaText = metaEl ? metaEl.textContent : '';
            const catMatch = metaText.match(/·\s*(.+)$/);
            const currentCat = catMatch ? catMatch[1].trim() : 'General';
            const catSelect = document.getElementById('editCategory');
            catSelect.value = currentCat;
            if (!catSelect.value) catSelect.value = 'General'; // fallback

            // Pinned state
            document.getElementById('editPinned').checked = !!pinnedEl;

            // Collect existing media elements
            _editExistingMedia = [];
            card.querySelectorAll('.media-thumb img, .media-thumb video').forEach(el => {
                let src  = el.tagName === 'IMG' ? el.src : (el.querySelector('source') ? el.querySelector('source').src : el.src);
                let type = el.tagName === 'IMG' ? 'image' : 'video';
                _editExistingMedia.push({ el, src, type, deleted: false });
            });

            _renderExistingMedia();
            _renderNewMedia();

            document.getElementById('editModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            setTimeout(() => document.getElementById('editTitle').focus(), 100);
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.body.style.overflow = '';
            _editTargetCard    = null;
            _editExistingMedia = [];
            _editNewFiles      = [];
        }

        function _editBdClick(e) {
            if (e.target === document.getElementById('editModal')) closeEditModal();
        }

        // Render existing (attached) media thumbnails with × delete button
        function _renderExistingMedia() {
            const grid    = document.getElementById('editMediaGrid');
            const section = document.getElementById('editMediaSection');
            const active  = _editExistingMedia.filter(m => !m.deleted);

            if (_editExistingMedia.length === 0) { section.style.display = 'none'; return; }
            section.style.display = 'block';

            if (active.length === 0) {
                grid.innerHTML = '<span class="edit-no-media">All media removed — save to apply.</span>';
                return;
            }

            grid.innerHTML = '';
            _editExistingMedia.forEach((m, i) => {
                if (m.deleted) return;
                const wrap = document.createElement('div');
                wrap.className = 'edit-media-item';
                const tag = m.type === 'video'
                    ? `<video src="${m.src}" muted preload="metadata" style="width:100px;height:80px;object-fit:cover;border-radius:10px;border:2px solid #e5e7eb;"></video>`
                    : `<img src="${m.src}" style="width:100px;height:80px;object-fit:cover;border-radius:10px;border:2px solid #e5e7eb;">`;
                wrap.innerHTML = tag + `<button type="button" class="edit-media-del" onclick="_delExisting(${i})" title="Remove">&times;</button>`;
                grid.appendChild(wrap);
            });
        }

        function _delExisting(i) { _editExistingMedia[i].deleted = true; _renderExistingMedia(); }

        // Handle new file selection
        function _handleNewFiles(input) {
            _editNewFiles = _editNewFiles.concat(Array.from(input.files));
            _renderNewMedia();
            input.value = '';
        }

        function _renderNewMedia() {
            const grid = document.getElementById('editNewGrid');
            if (!_editNewFiles.length) { grid.innerHTML = ''; return; }
            grid.innerHTML = '';
            _editNewFiles.forEach((file, i) => {
                const url  = URL.createObjectURL(file);
                const wrap = document.createElement('div');
                wrap.className = 'edit-new-item';
                const tag = file.type.startsWith('video/')
                    ? `<video src="${url}" muted preload="metadata"></video>`
                    : `<img src="${url}" alt="">`;
                wrap.innerHTML = tag + `<button type="button" class="edit-new-del" onclick="_delNew(${i})">&times;</button>`;
                grid.appendChild(wrap);
            });
        }

        function _delNew(i) { _editNewFiles.splice(i, 1); _renderNewMedia(); }

        function saveEdit() {
            if (!_editTargetCard) return;
            const newTitle   = document.getElementById('editTitle').value.trim();
            const newContent = document.getElementById('editContent').value.trim();
            if (!newTitle || !newContent) return;

            // Update text in the card
            const titleEl   = _editTargetCard.querySelector('.ann-title');
            const contentEl = _editTargetCard.querySelector('.ann-content');
            if (titleEl)   titleEl.textContent   = newTitle;
            if (contentEl) contentEl.textContent = newContent;

            // Hide deleted media thumbs in the card
            _editExistingMedia.forEach(m => {
                if (m.deleted) {
                    const thumb = m.el.closest('.media-thumb');
                    if (thumb) thumb.style.display = 'none';
                }
            });

            // ── Adjust grid layout if only 1 media thumb remains visible ──
            _editTargetCard.querySelectorAll('.grid').forEach(grid => {
                const visible = Array.from(grid.querySelectorAll('.media-thumb'))
                                     .filter(t => t.style.display !== 'none');
                if (visible.length === 1) {
                    // Unwrap: move the lone thumb out of the grid, replace grid with it
                    const lone = visible[0];
                    // Remove aspect-video so it uses its natural height (like a full poster)
                    lone.classList.remove('aspect-video');
                    lone.classList.add('w-full');
                    const img = lone.querySelector('img');
                    const vid = lone.querySelector('video');
                    if (img) { img.className = 'w-full h-auto max-h-[400px] object-cover hover:scale-105 transition-transform duration-200'; }
                    if (vid) { vid.className = 'w-full h-auto max-h-[400px]'; }
                    grid.parentNode.insertBefore(lone, grid);
                    grid.remove();
                } else if (visible.length === 0) {
                    grid.remove();
                }
            });

            // Append new media previews to the card's media area
            const mediaArea = _editTargetCard.querySelector('.grid, .rounded-xl.overflow-hidden');
            _editNewFiles.forEach(file => {
                const url  = URL.createObjectURL(file);
                const isVid = file.type.startsWith('video/');
                const wrap  = document.createElement('div');
                wrap.className = 'rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 aspect-video cursor-pointer media-thumb';
                wrap.setAttribute('data-src', url);
                wrap.setAttribute('data-type', isVid ? 'video' : 'image');
                wrap.innerHTML = isVid
                    ? `<video src="${url}" class="w-full h-full object-cover" muted></video>`
                    : `<img src="${url}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">`;
                // Re-attach lightbox click
                wrap.addEventListener('click', function() {
                    const lb  = document.getElementById('mediaLightbox');
                    const img = document.getElementById('lightboxImg');
                    const vid = document.getElementById('lightboxVideo');
                    img.style.display = 'none'; vid.style.display = 'none';
                    if (isVid) { vid.src = url; vid.style.display = 'block'; }
                    else       { img.src = url; img.style.display = 'block'; }
                    lb.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                });
                if (mediaArea) mediaArea.appendChild(wrap);
            });

            // ── Update category in ann-meta ──
            const newCat = document.getElementById('editCategory').value;
            const metaEl = _editTargetCard.querySelector('.ann-meta');
            if (metaEl) {
                const datePart = metaEl.textContent.split('·')[0].trim();
                metaEl.textContent = `${datePart} · ${newCat}`;
            }

            // ── Update pinned badge ──
            const isPinned  = document.getElementById('editPinned').checked;
            const headerRow = _editTargetCard.querySelector('.flex.items-start.justify-between');
            let pinnedBadge = _editTargetCard.querySelector('.ann-pinned-badge');

            if (isPinned && !pinnedBadge && headerRow) {
                const badge = document.createElement('span');
                badge.className = 'ann-pinned-badge px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-[10px] font-bold uppercase rounded-md tracking-wider';
                badge.textContent = 'Pinned';
                headerRow.appendChild(badge);
            } else if (!isPinned && pinnedBadge) {
                pinnedBadge.remove();
            }

            closeEditModal();

            // Show success toast
            const root = document.querySelector('[x-data]');
            if (root && root._x_dataStack) root._x_dataStack[0].showSuccess('Announcement updated successfully!');
            else if (root && root.__x) root.__x.$data.showSuccess('Announcement updated successfully!');
        }
    </script>
</x-app-layout>