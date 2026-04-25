<x-app-layout>
    <div x-data="{ 
        confirmingDeletion: false, 
        showingSuccess: false,
        showingCreateModal: false,
        showingExitConfirmation: false,
        showingDraftSuccess: false,
        successMessage: '',
        deleteUrl: '',
        activeTab: 'published',
        modalTitle: 'New Announcement',
        openCreateModal() {
            this.modalTitle = 'New Announcement';
            window.dispatchEvent(new CustomEvent('reset-announcement-form'));
            this.showingCreateModal = true;
        },
        openEditModal(announcement) {
            this.modalTitle = 'Edit Announcement';
            window.dispatchEvent(new CustomEvent('edit-announcement', { detail: announcement }));
            this.showingCreateModal = true;
        },
        confirmDeletion(id) {
            this.deleteUrl = `{{ url('announcements') }}/${id}`;
            this.confirmingDeletion = true;
        },
        async performDeletion() {
            if (!this.deleteUrl) return;
            try {
                const response = await fetch(this.deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.confirmingDeletion = false;
                    this.showSuccess('Announcement deleted successfully');
                    setTimeout(() => window.location.reload(), 1000);
                }
            } catch (error) {
                console.error('Delete error:', error);
            }
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
        <div class="max-w-5xl mx-auto">
            {{-- Page Header --}}
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="content-title">My Announcements</h1>
                    <p class="content-subtitle">Manage and track the announcements you've posted</p>
                </div>
                <div class="flex items-center gap-4">
                    <form action="{{ url()->current() }}" method="GET" class="relative flex-1 max-w-md">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search my posts..." class="w-full pl-12 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl focus:ring-4 focus:ring-[var(--accent-rgb)]/10 focus:border-[var(--accent)] transition-all text-sm">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </form>
                    <button class="px-5 py-2.5 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all flex items-center gap-2 shadow-lg active:scale-95" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);" @click="openCreateModal()">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        New Announcement
                    </button>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="flex gap-4 mb-8 border-b border-gray-100 dark:border-gray-800">
                <button @click="activeTab = 'published'" class="px-6 py-3 text-sm font-bold transition-all border-b-2" :class="activeTab === 'published' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                    Published ({{ $announcements->total() - $announcements->where('status', 'draft')->count() }})
                </button>
                <button @click="activeTab = 'drafts'" class="px-6 py-3 text-sm font-bold transition-all border-b-2" :class="activeTab === 'drafts' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                    Drafts ({{ $announcements->where('status', 'draft')->count() }})
                </button>
            </div>

            {{-- Announcements List --}}
            <div id="announcements-list" class="announcements space-y-10">
                {{-- Published Section --}}
                <div x-show="activeTab === 'published'" class="space-y-10">
                    @forelse($announcements->where('status', '!=', 'draft') as $announcement)
                        <div class="relative group">
                            <x-announcement-card :announcement="$announcement" :show-reactions="true" />
                            
                            {{-- Edit/Delete Actions --}}
                            <div class="absolute top-16 right-8 flex gap-3 opacity-0 group-hover:opacity-100 transition-all scale-95 group-hover:scale-100 z-50">
                                <button type="button" @click="openEditModal(JSON.parse($el.dataset.announcement))" data-announcement="{{ $announcement->toJson() }}" class="p-3.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xl hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                </button>
                                <button 
                                    type="button" 
                                    @click="confirmDeletion('{{ $announcement->id }}')"
                                    class="p-3.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 transition-all"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="card p-12 text-center bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
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

                    <div class="mt-8">
                        {{ $announcements->links() }}
                    </div>
                </div>

                {{-- Drafts Section --}}
                <div x-show="activeTab === 'drafts'" class="space-y-10">
                    @forelse($announcements->where('status', 'draft') as $announcement)
                        <div class="relative group">
                            <x-announcement-card :announcement="$announcement" :show-reactions="true" />
                            <div class="absolute top-4 right-20">
                                <span class="px-2 py-1 bg-amber-100 text-amber-600 text-[10px] font-bold uppercase rounded-md shadow-sm border border-amber-200">Draft</span>
                            </div>
                            {{-- Edit/Delete Actions --}}
                            <div class="absolute top-16 right-8 flex gap-3 opacity-0 group-hover:opacity-100 transition-all scale-95 group-hover:scale-100 z-50">
                                <button type="button" @click="openEditModal(JSON.parse($el.dataset.announcement))" data-announcement="{{ $announcement->toJson() }}" class="p-3.5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-xl hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="card p-12 text-center bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
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
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100" x-text="modalTitle"></h2>
                            <p class="text-xs text-gray-500 mt-1">Manage your announcement content and targeting</p>
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
                        <button @click="showingExitConfirmation = false" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 transition-colors">
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
                        <button @click="showingExitConfirmation = false" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
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
                        <button @click="showingDraftSuccess = false" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 transition-colors">
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
                        <button @click="showingSuccess = false" class="px-4 py-2 bg-[var(--accent)] text-white rounded-lg text-sm font-bold hover:bg-[var(--accent-dark)] transition-all">
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
                        <button @click="confirmingDeletion = false" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 transition-colors">
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
                        <button @click="performDeletion()" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-bold hover:bg-red-700 transition-all">
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









