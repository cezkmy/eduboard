<x-app-layout>
    <x-slot name="title">All Announcements - EduBoard Admin</x-slot>

    <div class="admin-content" x-data="{ 
        confirmingDeletion: false, 
        showingSuccess: false,
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
                <h1 class="content-title">Announcements</h1>
                <p class="content-subtitle">Manage and monitor all school announcements</p>
            </div>
            <a href="#" class="px-4 py-2 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all flex items-center gap-2 shadow-md" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                New Announcement
            </a>
        </div>

        <div id="announcements-list" class="space-y-6">
            {{-- Static Announcement 1 --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all group relative">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center font-bold">
                            AS
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Admin System</h4>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400">2026-03-10 · <span class="uppercase tracking-wider font-semibold">Emergency</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-bold uppercase rounded-lg tracking-widest">Pinned</span>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Edit">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                </svg>
                            </button>
                            <button @click="confirmingDeletion = true" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Classes Suspended on March 10</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Due to inclement weather, all classes are suspended on March 10, 2026. Please stay safe and monitor official channels for updates.</p>
            </div>

            {{-- Static Announcement 2 --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all group relative">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center font-bold">
                            EC
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Events Committee</h4>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400">2026-03-07 · <span class="uppercase tracking-wider font-semibold">Events</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Edit">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                            </svg>
                        </button>
                        <button @click="confirmingDeletion = true" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Upcoming Seminar: Career Opportunities in IT</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-4">Join us for an insightful seminar featuring industry experts discussing the latest trends and career paths in the Information Technology sector. Open to all students.</p>
                
                {{-- Photo Display --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 aspect-video cursor-pointer media-thumb group/media" data-src="{{ asset('images/download.jpg') }}" data-type="image">
                        <img src="{{ asset('images/download.jpg') }}" alt="IT Seminar 1" class="w-full h-full object-cover group-hover/media:scale-105 transition-transform duration-300">
                    </div>
                    <div class="rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 aspect-video cursor-pointer media-thumb group/media" data-src="{{ asset('images/download.jpg') }}" data-type="image">
                        <img src="{{ asset('images/download.jpg') }}" alt="IT Seminar 2" class="w-full h-full object-cover group-hover/media:scale-105 transition-transform duration-300">
                    </div>
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

    {{-- Lightbox Modal --}}
    <div id="mediaLightbox" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.88); backdrop-filter:blur(4px); align-items:center; justify-content:center;" onclick="closeLightbox(event)">
        <button onclick="closeLightboxBtn()" style="position:absolute; top:20px; right:24px; background:rgba(255,255,255,0.12); border:none; color:white; border-radius:50%; width:40px; height:40px; font-size:22px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">&times;</button>
        <div style="max-width:90vw; max-height:90vh; display:flex; align-items:center; justify-content:center;">
            <img id="lightboxImg" src="" alt="" style="display:none; max-width:90vw; max-height:86vh; object-fit:contain; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.6);">
            <video id="lightboxVideo" controls style="display:none; max-width:90vw; max-height:86vh; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.6);"></video>
        </div>
    </div>

    <script>
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
            if (e.key === 'Escape') closeLightboxBtn();
        });
    </script>
</x-app-layout>