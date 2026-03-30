<x-app-layout>
    <x-slot name="title">Categories - EduBoard Admin</x-slot>

    <div class="admin-content" x-data="{ 
        categoryModal: false, 
        deleteModal: false, 
        successModal: false,
        modalTitle: 'Add Category',
        categoryName: '',
        selectedColor: 'blue',
        deleteTargetName: '',
        showSuccess(msg) {
            this.successMessage = msg;
            this.successModal = true;
            setTimeout(() => { if(this.successModal) this.successModal = false; }, 3000);
        },
        successMessage: 'Action completed successfully.'
    }">

        {{-- Page Header --}}
        <div class="content-header flex items-center justify-between mb-8">
            <div>
                <h1 class="content-title">Categories</h1>
                <p class="content-subtitle text-gray-500 dark:text-gray-400 font-medium">Manage announcement categories for Buksu</p>
            </div>
            <button class="px-5 py-2.5 bg-teal-500 text-white rounded-xl text-sm font-bold hover:bg-teal-600 transition-all flex items-center gap-2 shadow-lg shadow-teal-500/20 active:scale-95" 
                    @click="modalTitle = 'Add Category'; categoryName = ''; categoryModal = true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Category
            </button>
        </div>

        {{-- Categories Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="categoriesGrid">

            @php
                $categories = [
                    ['name' => 'Academic', 'class' => 'academic', 'color' => 'blue'],
                    ['name' => 'Events', 'class' => 'events', 'color' => 'green'],
                    ['name' => 'Administrative', 'class' => 'administrative', 'color' => 'amber'],
                    ['name' => 'Emergency', 'class' => 'emergency', 'color' => 'red'],
                    ['name' => 'General', 'class' => 'general', 'color' => 'gray'],
                ];
            @endphp

            @foreach($categories as $cat)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition-all group" data-name="{{ $cat['name'] }}">
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider 
                            {{ $cat['class'] === 'academic' ? 'bg-blue-50 text-blue-600' : '' }}
                            {{ $cat['class'] === 'events' ? 'bg-green-50 text-green-600' : '' }}
                            {{ $cat['class'] === 'administrative' ? 'bg-amber-50 text-amber-600' : '' }}
                            {{ $cat['class'] === 'emergency' ? 'bg-red-50 text-red-600' : '' }}
                            {{ $cat['class'] === 'general' ? 'bg-gray-50 text-gray-600' : '' }}
                        ">{{ $cat['name'] }}</span>
                        
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" 
                                    @click="modalTitle = 'Edit Category'; categoryName = '{{ $cat['name'] }}'; categoryModal = true" title="Edit">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                </svg>
                            </button>
                            <button class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" 
                                    @click="deleteTargetName = '{{ $cat['name'] }}'; deleteModal = true" title="Delete">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $cat['name'] }} Announcements</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Click edit to change name or color style.</p>
                </div>
            @endforeach

        </div>

        {{-- Add/Edit Category Modal --}}
        <template x-teleport="body">
            <div x-show="categoryModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="categoryModal = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter">
                    <div class="px-8 py-6 border-b border-gray-50 dark:border-gray-700/50 flex items-center justify-between bg-white dark:bg-gray-800 sticky top-0 z-10">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight" x-text="modalTitle"></h3>
                            <p class="text-xs text-gray-500 font-medium">Categorize your school announcements</p>
                        </div>
                        <button @click="categoryModal = false" class="w-10 h-10 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-all flex items-center justify-center">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form class="p-8 space-y-6" @submit.prevent="categoryModal = false; showSuccess('Category saved successfully!')">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Category Name</label>
                            <input type="text" x-model="categoryName" placeholder="e.g. Academic, Events..." 
                                   class="w-full bg-gray-50 dark:bg-gray-900/50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Color Style</label>
                            <div class="grid grid-cols-5 gap-3">
                                @foreach(['blue', 'green', 'amber', 'red', 'gray'] as $color)
                                    <button type="button" 
                                            class="w-full aspect-square rounded-xl border-2 transition-all flex items-center justify-center
                                                   bg-{{ $color }}-500 shadow-lg shadow-{{ $color }}-500/20"
                                            :class="selectedColor === '{{ $color }}' ? 'border-teal-500 scale-110 ring-4 ring-teal-500/10' : 'border-transparent opacity-70 hover:opacity-100'"
                                            @click="selectedColor = '{{ $color }}'">
                                        <template x-if="selectedColor === '{{ $color }}'">
                                            <svg fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="3" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </template>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="pt-4">
                            <button type="submit" class="w-full py-4 bg-teal-500 text-white rounded-[1.25rem] text-sm font-black hover:bg-teal-600 transition-all shadow-xl shadow-teal-500/20 active:scale-95">SAVE CATEGORY</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- Delete Confirmation Modal --}}
        <template x-teleport="body">
            <div x-show="deleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="deleteModal = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter">
                    <div class="p-8 text-center">
                        <div class="w-20 h-20 bg-red-50 dark:bg-red-900/20 rounded-full flex items-center justify-center text-red-500 mx-auto mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Delete Category?</h3>
                        <p class="text-sm text-gray-500 font-medium mb-8">
                            Are you sure you want to delete <span class="text-gray-900 dark:text-white font-black" x-text="deleteTargetName"></span>? This action cannot be undone.
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="deleteModal = false" class="py-4 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-[1.25rem] text-sm font-black hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">CANCEL</button>
                            <button @click="deleteModal = false; showSuccess('Category deleted successfully!')" class="py-4 bg-red-500 text-white rounded-[1.25rem] text-sm font-black hover:bg-red-600 transition-all shadow-xl shadow-red-500/20">DELETE</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Success Modal --}}
        <template x-teleport="body">
            <div x-show="successModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 overflow-y-auto" x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="successModal = false"></div>
                <div class="relative w-full max-w-sm bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700 animate-modal-enter">
                    <div class="p-8 text-center">
                        <div class="mb-6">
                            <svg class="animated-check mx-auto" viewBox="0 0 52 52" style="width: 80px; height: 80px;">
                                <circle class="animated-check-circle" cx="26" cy="26" r="25" fill="none" stroke="#10b981" stroke-width="2"/>
                                <path class="animated-check-path" fill="none" stroke="#10b981" stroke-width="4" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Success!</h3>
                        <p class="text-sm text-gray-500 font-medium mb-8" x-text="successMessage"></p>
                        <button @click="successModal = false" class="w-full py-4 bg-gray-900 dark:bg-white dark:text-gray-900 text-white rounded-[1.25rem] text-sm font-black hover:opacity-90 transition-all">CONTINUE</button>
                    </div>
                </div>
            </div>
        </template>

    </div>
</x-app-layout>