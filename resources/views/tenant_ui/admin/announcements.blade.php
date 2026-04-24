<x-app-layout>
    <x-slot name="title">All Announcements - EduBoard Admin</x-slot>

    <div x-data="{ 
        confirmingDeletion: false, 
        showingSuccess: false,
        showingCreateModal: false,
        showingExitConfirmation: false,
        showingDraftSuccess: false,
        successMessage: '',
        successIcon: null,
        showSuccess(msg, icon = null) {
            this.successMessage = msg;
            this.successIcon = icon;
            this.showingSuccess = true;
        },
        closeModal() {
            if (window.hasUnsavedChanges()) {
                this.showingExitConfirmation = true;
            } else {
                this.showingCreateModal = false;
            }
        },
        confirmExit() {
             window.clearAnnouncementDraft();
             this.showingExitConfirmation = false;
             this.showingCreateModal = false;
         }
    }" @save-draft-success.window="showingDraftSuccess = true; showingCreateModal = false">

        <div class="max-w-5xl mx-auto">
            {{-- Page Header --}}
            <div class="content-header flex items-center justify-between mb-8">
                <div>
                    <h1 class="content-title">Announcements</h1>
                    <p class="content-subtitle">Manage and monitor all school announcements</p>
                </div>
                <div class="flex items-center gap-4">
                    <form action="{{ url()->current() }}" method="GET" class="relative flex-1 max-w-md">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search announcements..." class="w-full pl-12 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl focus:ring-4 focus:ring-[var(--accent-rgb)]/10 focus:border-[var(--accent)] transition-all text-sm">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </form>
                    <button @click="showingCreateModal = true" class="px-4 py-2 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all flex items-center gap-2 shadow-md" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        New Announcement
                    </button>
                </div>
            </div>

            <div id="announcements-list" class="space-y-6">
                @forelse($announcements as $announcement)
                    <x-announcement-card :announcement="$announcement" />
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="w-20 h-20 bg-emerald-50 dark:bg-emerald-900/20 rounded-full flex items-center justify-center text-emerald-500 mx-auto mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">No announcements found</h2>
                        <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">There are no announcements to display at the moment.</p>
                    </div>
                @endforelse

                <div class="mt-8">
                    {{ $announcements->links() }}
                </div>
            </div>
        </div>


        {{-- Create Announcement Modal --}}
        <template x-teleport="body">
            <div x-show="showingCreateModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" x-cloak>
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md" @click="closeModal()"></div>
                <div class="relative w-full max-w-5xl bg-white dark:bg-gray-900 rounded-3xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-800 max-h-[95vh] flex flex-col"
                     style="resize: both; min-width: 800px; min-height: 600px;"
                     x-show="showingCreateModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                    
                    <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-white dark:bg-gray-900">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Post New Announcement</h2>
                            <p class="text-xs text-gray-500 mt-1">Create a new announcement for students and staff</p>
                        </div>
                        <button @click="closeModal()" class="p-2 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-8 overflow-y-auto custom-scrollbar">
                        <x-announcement-form />
                    </div>
                </div>
            </div>
        </template>

        {{-- Custom Exit Confirmation Modal --}}
        <template x-teleport="body">
            <div x-show="showingExitConfirmation" class="fixed inset-0 z-[110] flex items-center justify-center p-4" x-cloak>
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showingExitConfirmation = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Exit Confirmation</h3>
                        <button @click="showingExitConfirmation = false" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/20 rounded-full flex items-center justify-center text-amber-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                                You have unsaved changes. Are you sure you want to exit? Your progress will be cleared.
                            </p>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex items-center justify-end gap-3">
                        <button @click="showingExitConfirmation = false" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700">
                            No, Keep Editing
                        </button>
                        <button @click="confirmExit()" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition-all">
                            Yes, Exit
                        </button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Custom Draft Success Modal --}}
        <template x-teleport="body">
            <div x-show="showingDraftSuccess" class="fixed inset-0 z-[110] flex items-center justify-center p-4" x-cloak>
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showingDraftSuccess = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Draft Saved</h3>
                        <button @click="showingDraftSuccess = false" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-[rgba(var(--accent-rgb),0.12)] rounded-full flex items-center justify-center text-[var(--accent)] mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-8 w-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                            Your announcement has been saved as a draft. You can continue editing it later.
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex justify-center">
                        <button @click="showingDraftSuccess = false" class="px-8 py-2 bg-[var(--accent)] text-white rounded-lg text-sm font-bold hover:bg-[var(--accent-dark)] transition-all">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </template>


        {{-- Custom Success Modal --}}
        <template x-teleport="body">
            <div x-show="showingSuccess" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showingSuccess = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Success</h3>
                        <button @click="showingSuccess = false" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 transition-colors">
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