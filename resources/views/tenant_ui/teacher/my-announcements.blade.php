<x-app-layout>
    @php
        $myAnnouncements = \App\Models\Announcement::with('postedBy')->where('posted_by', Auth::id())->latest()->get();
    @endphp

    <div x-data="{ 
        confirmingDeletion: false, 
        showingSuccess: false,
        showingCreateModal: false,
        showingExitConfirmation: false,
        showingDraftSuccess: false,
        successMessage: '',
        deleteUrl: '',
        confirmDeletion(url) {
            this.confirmingDeletion = true;
        },
        showSuccess(msg) {
            this.successMessage = msg;
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
        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="content-title">My Announcements</h1>
                <p class="content-subtitle">Manage and track the announcements you've posted</p>
            </div>
        </div>

        {{-- Announcements List --}}
        <div id="announcements-list" class="announcements space-y-10">
            @forelse($myAnnouncements as $announcement)
                <div class="relative group">
                    <x-announcement-card :announcement="$announcement" :show-reactions="true" />
                    
                    {{-- Edit/Delete Actions --}}
                    <div class="absolute top-8 right-8 flex gap-3 opacity-0 group-hover:opacity-100 transition-all scale-95 group-hover:scale-100">
                        <a href="{{ route('tenant.teacher.announcements.edit', $announcement) }}" class="p-3.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xl hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            </svg>
                        </a>
                        <button 
                            type="button" 
                            @click="confirmDeletion()"
                            class="p-3.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 transition-all"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="empty-state-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 32px; height: 32px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h2 class="empty-state-title">You haven't posted any announcements yet</h2>
                    <p class="empty-state-desc">Share news, updates, or events with the school community to keep everyone informed and engaged.</p>
                    <button @click="showingCreateModal = true" class="btn-primary">
                        Post Your First Announcement
                    </button>
                </div>
            @endforelse
        </div>

        {{-- Create Announcement Modal --}}
        <template x-teleport="body">
            <div x-show="showingCreateModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" x-cloak>
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md" @click="closeModal()"></div>
                <div class="relative w-full max-w-3xl bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700 max-h-[90vh] flex flex-col"
                     x-show="showingCreateModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                    
                    <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
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
                        <div class="w-16 h-16 bg-teal-50 dark:bg-teal-900/20 rounded-full flex items-center justify-center text-teal-500 mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-8 w-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                            Your announcement has been saved as a draft. You can continue editing it later.
                        </p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex justify-center">
                        <button @click="showingDraftSuccess = false" class="px-8 py-2 bg-[var(--teal)] text-white rounded-lg text-sm font-bold hover:opacity-90 transition-all">
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
                        <button @click="showingSuccess = false" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed" x-text="successMessage"></p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex justify-end">
                        <button @click="showingSuccess = false" class="px-4 py-2 bg-[var(--teal)] text-white rounded-lg text-sm font-bold hover:opacity-90 transition-all">
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
                        <button @click="confirmingDeletion = false; $dispatch('announcement-deleted')" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition-all">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        document.body.dataset.userName = "{{ Auth::user()->name }}";
        
        // Listen for internal events to show custom success modal
        window.addEventListener('announcement-deleted', () => {
            const alpineData = document.querySelector('[x-data]').__x.$data;
            alpineData.showSuccess('Announcement deleted successfully');
        });
    </script>
</x-app-layout>









