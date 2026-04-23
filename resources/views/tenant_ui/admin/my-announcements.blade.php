<x-app-layout>
    <x-slot name="title">My Announcements - EduBoard Admin</x-slot>

    <div x-data="{ 
        announcementModal: false,
        confirmingDeletion: false, 
        deletingAnnouncementId: null,
        isDeleting: false,
        showingSuccess: false,
        showingExitConfirmation: false,
        showingDraftSuccess: false,
        modalTitle: 'New Announcement',
        successMessage: '',
        successIcon: null,
        activeTab: 'published',
        showSuccess(msg, icon = null) {
            this.successMessage = msg;
            this.successIcon = icon;
            this.showingSuccess = true;
        },
        openCreateModal() {
            this.modalTitle = 'New Announcement';
            window.dispatchEvent(new CustomEvent('reset-announcement-form'));
            this.announcementModal = true;
        },
        openEditModal(announcement) {
            this.modalTitle = 'Edit Announcement';
            window.dispatchEvent(new CustomEvent('edit-announcement', { detail: announcement }));
            this.announcementModal = true;
        },
        closeModal() {
            this.announcementModal = false;
        },
        confirmDelete(id) {
            this.deletingAnnouncementId = id;
            this.confirmingDeletion = true;
        },
        async deleteAnnouncement() {
            if (this.isDeleting) return;
            this.isDeleting = true;
            
            try {
                const response = await fetch(`{{ url('announcements') }}/${this.deletingAnnouncementId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                if (result.success) {
                    this.confirmingDeletion = false;
                    this.showSuccess('Announcement deleted successfully');
                    setTimeout(() => window.location.reload(), 1000);
                }
            } catch (error) {
                console.error('Delete error:', error);
                showAlert('Error', 'Failed to delete announcement', 'error');
            } finally {
                this.isDeleting = false;
            }
        }
    }" @save-draft-success.window="showingDraftSuccess = true; announcementModal = false">

        <div class="max-w-5xl mx-auto">
            {{-- Page Header --}}
            <div class="content-header flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">My Announcements</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Manage and track the announcements you've posted</p>
                </div>
                <button class="px-5 py-2.5 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all flex items-center gap-2 shadow-lg active:scale-95" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);" @click="openCreateModal()">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Announcement
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex gap-4 mb-8 border-b border-gray-100 dark:border-gray-800">
                <button @click="activeTab = 'published'" class="px-6 py-3 text-sm font-bold transition-all border-b-2" :class="activeTab === 'published' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-gray-500 hover:text-gray-700'">
                    Published ({{ $announcements->where('status', '!=', 'draft')->count() }})
                </button>
                <button @click="activeTab = 'drafts'" class="px-6 py-3 text-sm font-bold transition-all border-b-2" :class="activeTab === 'drafts' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-gray-500 hover:text-gray-700'">
                    Drafts ({{ $announcements->where('status', 'draft')->count() }})
                </button>
            </div>

            {{-- Announcements List --}}
            <div id="announcements-list" class="space-y-6">
                {{-- Published Section --}}
                <div x-show="activeTab === 'published'" class="space-y-6">
                    @forelse($announcements->where('status', '!=', 'draft') as $announcement)
                        <div class="relative group">
                            <x-announcement-card :announcement="$announcement" />
                            <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                <button @click="openEditModal({{ $announcement->toJson() }})" class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl text-gray-600 dark:text-gray-400 hover:text-[var(--accent)] hover:border-[var(--accent)] transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="confirmDelete({{ $announcement->id }})" class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm mb-8">
                            <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">No published announcements</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">You haven't published any announcements yet.</p>
                            <button class="btn-primary px-6 py-2" @click="openCreateModal()">Post Now</button>
                        </div>
                    @endforelse
                </div>

                {{-- Drafts Section --}}
                <div x-show="activeTab === 'drafts'" class="space-y-6">
                    @forelse($announcements->where('status', 'draft') as $announcement)
                        <div class="relative group">
                            <x-announcement-card :announcement="$announcement" />
                            <div class="absolute top-4 right-16">
                                <span class="px-2 py-1 bg-amber-100 text-amber-600 text-[10px] font-bold uppercase rounded-md shadow-sm border border-amber-200">Draft</span>
                            </div>
                            <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                <button @click="openEditModal({{ $announcement->toJson() }})" class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl text-gray-600 dark:text-gray-400 hover:text-[var(--accent)] hover:border-[var(--accent)] transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="confirmDelete({{ $announcement->id }})" class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm mb-8">
                            <div class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">No drafts found</h3>
                            <p class="text-gray-500 dark:text-gray-400">Save your work as a draft to finish it later.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Announcement Modal --}}
        <template x-teleport="body">
            <div x-show="announcementModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeModal()"></div>
                <div class="relative w-full max-w-5xl bg-white dark:bg-gray-900 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-800 animate-modal-enter">
                    <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-white dark:bg-gray-900 sticky top-0 z-10">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight" x-text="modalTitle"></h3>
                            <p class="text-xs text-gray-500 font-medium">Manage your announcement content and targeting</p>
                        </div>
                        <button @click="closeModal()" class="w-10 h-10 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-all flex items-center justify-center">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-8 max-h-[90vh] overflow-y-auto">
                        <x-announcement-form />
                    </div>
                </div>
            </div>
        </template>

        {{-- Delete Confirmation Modal --}}
        <template x-teleport="body">
            <div x-show="confirmingDeletion" class="fixed inset-0 z-[110] flex items-center justify-center p-4" x-cloak>
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="confirmingDeletion = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 bg-red-50 dark:bg-red-900/20 rounded-full flex items-center justify-center text-red-500 mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-8 w-8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Delete Announcement?</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Are you sure you want to delete this announcement? This action cannot be undone.</p>
                        
                        <div class="flex gap-3">
                            <button @click="confirmingDeletion = false" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-200 transition-all">Cancel</button>
                            <button @click="deleteAnnouncement()" :disabled="isDeleting" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition-all disabled:opacity-50">
                                <span x-show="!isDeleting">Delete</span>
                                <span x-show="isDeleting" class="flex items-center justify-center">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Success Modal --}}
        <template x-teleport="body">
            <div x-show="showingSuccess" class="fixed inset-0 z-[120] flex items-center justify-center p-4" x-cloak>
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showingSuccess = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-green-50 dark:bg-green-900/20 rounded-full flex items-center justify-center text-green-500 mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Success!</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm" x-text="successMessage"></p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
