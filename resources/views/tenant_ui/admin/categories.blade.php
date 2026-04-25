<x-app-layout>
    <x-slot name="title">Categories - EduBoard Admin</x-slot>

    @php $schoolType = tenant('school_type') ?? 'college'; @endphp
    <div class="admin-content" x-data="categoryManager()">

        {{-- Page Header --}}
        <div class="content-header flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h1 class="content-title">Organization & Structure</h1>
                <p class="content-subtitle text-gray-500 dark:text-gray-400 font-medium">Define your school's audience and category hierarchy</p>
            </div>
            <div class="flex items-center gap-3">

                <button class="px-5 py-2.5 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all flex items-center gap-2 shadow-lg active:scale-95" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);"
                        @click="selectedType = activeTab; modalTitle = 'Add ' + activeTab.replace('_', ' '); categoryName = ''; categoryModal = true">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Entry
                </button>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex items-center gap-6 mb-8 border-b border-gray-100 dark:border-gray-700 overflow-x-auto custom-scrollbar whitespace-nowrap">
            <button @click="activeTab = 'announcement_category'" class="px-4 py-3 text-sm font-black uppercase tracking-widest transition-all border-b-2" 
                    :class="activeTab === 'announcement_category' ? 'text-[var(--accent)] border-[var(--accent)]' : 'text-gray-400 border-transparent hover:text-gray-600'">Categories</button>
            
            <button @click="activeTab = 'college'" class="px-4 py-3 text-sm font-black uppercase tracking-widest transition-all border-b-2" 
                    :class="activeTab === 'college' ? 'text-[var(--accent)] border-[var(--accent)]' : 'text-gray-400 border-transparent hover:text-gray-600'">Colleges</button>
            <button @click="activeTab = 'level'" class="px-4 py-3 text-sm font-black uppercase tracking-widest transition-all border-b-2" 
                    :class="activeTab === 'level' ? 'text-[var(--accent)] border-[var(--accent)]' : 'text-gray-400 border-transparent hover:text-gray-600'">Year Levels</button>
            <button @click="activeTab = 'grade_level'" class="px-4 py-3 text-sm font-black uppercase tracking-widest transition-all border-b-2" 
                    :class="activeTab === 'grade_level' ? 'text-[var(--accent)] border-[var(--accent)]' : 'text-gray-400 border-transparent hover:text-gray-600'">Grade Levels</button>
            <button @click="activeTab = 'program'" class="px-4 py-3 text-sm font-black uppercase tracking-widest transition-all border-b-2" 
                    :class="activeTab === 'program' ? 'text-[var(--accent)] border-[var(--accent)]' : 'text-gray-400 border-transparent hover:text-gray-600'">Programs</button>
            <button @click="activeTab = 'strand'" class="px-4 py-3 text-sm font-black uppercase tracking-widest transition-all border-b-2" 
                    :class="activeTab === 'strand' ? 'text-[var(--accent)] border-[var(--accent)]' : 'text-gray-400 border-transparent hover:text-gray-600'">Strands</button>
            <button @click="activeTab = 'section'" class="px-4 py-3 text-sm font-black uppercase tracking-widest transition-all border-b-2" 
                    :class="activeTab === 'section' ? 'text-[var(--accent)] border-[var(--accent)]' : 'text-gray-400 border-transparent hover:text-gray-600'">Sections</button>
        </div>

        {{-- Content Panels --}}
        @foreach(['announcement_category', 'college', 'level', 'grade_level', 'program', 'strand', 'section'] as $type)
            <div x-show="activeTab === '{{ $type }}'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @php 
                        $items = match($type) {
                            'announcement_category' => $categories,
                            'college' => $colleges,
                            'level' => $yearLevels,
                            'grade_level' => $gradeLevels,
                            'program' => $programs,
                            'strand' => $strands,
                            'section' => $sections,
                            default => collect()
                        };
                    @endphp

                    @forelse($items as $item)
                        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-[var(--accent)] opacity-[0.03] rounded-full -mr-12 -mt-12"></div>
                            
                            <div class="flex items-center justify-between mb-4 relative z-10">
                                <span class="px-3 py-1 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-[10px] font-black uppercase tracking-widest text-gray-500">
                                    #{{ $item->id }}
                                </span>
                                
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all" 
                                            @click="deleteTargetName = '{{ $item->name }}'; deleteTargetId = {{ $item->id }}; deleteModal = true" title="Delete">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 mb-1">{{ $item->name }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest">
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </p>
                                @if($item->educational_level)
                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    <p class="text-[11px] text-[var(--accent)] font-black uppercase tracking-widest">
                                        {{ $item->educational_level }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-20 text-center bg-gray-50/50 dark:bg-gray-800/30 rounded-[3rem] border-2 border-dashed border-gray-100 dark:border-gray-700/50">
                            <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-3xl flex items-center justify-center text-gray-300 mx-auto mb-4 shadow-sm">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" class="w-10 h-10">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest">No {{ str_replace('_', ' ', $type) }} items found</h4>
                            <p class="text-xs text-gray-400 mt-1">Start by adding a new one above.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach

        {{-- Add Entry Modal --}}
        <template x-teleport="body">
            <div x-show="categoryModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="categoryModal = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl overflow-hidden border border-white dark:border-gray-700 animate-modal-enter">
                    <div class="px-8 py-8 flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Add New Entry</h3>
                            <p class="text-xs text-[var(--accent)] font-black uppercase tracking-widest mt-1" x-text="activeTab.replace('_', ' ')"></p>
                        </div>
                        <button @click="categoryModal = false" class="w-12 h-12 rounded-2xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 transition-all flex items-center justify-center">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form class="px-8 pb-10 space-y-6" action="{{ route('tenant.admin.categories.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" :value="activeTab">

                        <template x-if="activeTab !== 'announcement_category'">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Educational Level</label>
                                <select name="educational_level" x-model="selectedEducationalLevel"
                                        class="w-full bg-gray-50 dark:bg-gray-900 px-6 py-4 border-none rounded-2xl text-sm font-bold focus:ring-4 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.10);">
                                    <option value="elementary">Elementary</option>
                                    <option value="junior_high">Junior High</option>
                                    <option value="senior_high">Senior High</option>
                                    <option value="college">College</option>
                                </select>
                            </div>
                        </template>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Name / Label</label>
                            <input type="text" name="name" x-model="categoryName" :placeholder="'e.g. ' + (activeTab === 'program' ? 'BS Computer Science' : 'Academic')" 
                                   autofocus required
                                   class="w-full bg-gray-50 dark:bg-gray-900 px-6 py-4 border-none rounded-2xl text-sm font-bold focus:ring-4 transition-all" style="--tw-ring-color: rgba(var(--accent-rgb), 0.10);">
                        </div>

                        <template x-if="activeTab === 'announcement_category'">
                            <div class="space-y-4 pt-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Color Theme</label>
                                @php
                                    $categoryColors = [
                                        'blue' => '#3B82F6',
                                        'green' => '#22C55E',
                                        'amber' => '#F59E0B',
                                        'red' => '#EF4444',
                                        'gray' => '#6B7280',
                                        'purple' => '#8B5CF6',
                                        'emerald' => '#10B981',
                                        'indigo' => '#6366F1',
                                    ];
                                @endphp
                                <div class="flex flex-wrap gap-2">
                                    <input type="hidden" name="color" :value="selectedColor">
                                    @foreach($categoryColors as $color => $hex)
                                        <button type="button"
                                                @click="selectedColor = '{{ $color }}'"
                                                class="w-10 h-10 rounded-xl border-2 transition-all flex items-center justify-center overflow-hidden focus:outline-none focus:ring-2 focus:ring-[var(--accent)]/30"
                                                :style="{ backgroundColor: '{{ $hex }}' }"
                                                :class="selectedColor === '{{ $color }}' ? 'border-[var(--accent)] ring-4 ring-[var(--accent)]/10 scale-110' : 'border-white/10 hover:scale-105'">
                                            <template x-if="selectedColor === '{{ $color }}'">
                                                <svg fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="3" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </template>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </template>

                        <div class="pt-4">
                            <button type="submit" class="w-full py-4 bg-[var(--accent)] text-white rounded-2xl text-sm font-black hover:bg-[var(--accent-dark)] transition-all shadow-xl active:scale-95 uppercase tracking-widest" style="box-shadow: 0 12px 30px -5px rgba(var(--accent-rgb), 0.40);">Create {{ $schoolType === 'college' ? 'Entry' : 'Component' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- Delete Confirmation Modal --}}
        <template x-teleport="body">
            <div x-show="deleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto" x-cloak>
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="deleteModal = false"></div>
                <div class="relative w-full max-w-sm bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl overflow-hidden animate-modal-enter">
                    <form :action="`{{ url('admin/categories') }}/${deleteTargetId}`" method="POST" class="p-8 text-center">
                        @csrf
                        @method('DELETE')
                        <div class="w-20 h-20 bg-red-50 dark:bg-red-900/20 rounded-[2rem] flex items-center justify-center text-red-500 mx-auto mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Delete Permanently?</h3>
                        <p class="text-sm text-gray-500 font-medium mb-8">
                            Are you sure you want to delete <span class="text-gray-900 dark:text-white font-black" x-text="deleteTargetName"></span>?
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <button type="button" @click="deleteModal = false" class="py-4 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-200 transition-all">Cancel</button>
                            <button type="submit" class="py-4 bg-red-500 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-red-600 transition-all shadow-xl shadow-red-500/20">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>



    </div>

    <script>
        function categoryManager() {
            return {
                categoryModal: false,
                deleteModal: false,
                successModal: false,
                activeTab: 'announcement_category',
                modalTitle: 'Add Category',
                categoryName: '',
                selectedEducationalLevel: 'tertiary',
                selectedType: 'announcement_category',
                selectedColor: 'blue',
                deleteTargetName: '',
                deleteTargetId: null,
            };
        }
    </script>
</x-app-layout>