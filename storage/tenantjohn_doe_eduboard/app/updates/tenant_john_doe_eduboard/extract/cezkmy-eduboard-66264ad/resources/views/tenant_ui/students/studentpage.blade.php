<x-app-layout>
    <x-slot name="title">Announcements</x-slot>

    {{-- Content --}}
    <div class="content max-w-5xl mx-auto px-4 lg:px-8 py-8">

        {{-- Page Header --}}
        <div class="page-header mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">Announcements</h1>
            <p class="text-gray-500 dark:text-gray-400">Stay updated with the latest from
                {{ tenant('school_name') ?? 'Westfield Academy' }}
            </p>
        </div>

        {{-- Tabs --}}
        <div class="tabs flex gap-4 mb-6 border-b border-gray-200 dark:border-gray-700"
            x-data="{ activeTab: 'general' }">
            <button class="tab px-4 py-2 font-bold transition-all"
                :class="activeTab === 'general' ? 'text-[var(--accent)] border-b-2 border-[var(--accent)]' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                @click="activeTab = 'general'; $dispatch('filter-tab', 'general')">General</button>
            <button class="tab px-4 py-2 font-bold transition-all"
                :class="activeTab === 'foryou' ? 'text-[var(--accent)] border-b-2 border-[var(--accent)]' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                @click="activeTab = 'foryou'; $dispatch('filter-tab', 'foryou')">For You</button>
        </div>

        {{-- Category & Interaction Filters --}}
        <div class="filters-container space-y-6 mb-8">
            @if(tenant() && tenant()->hasFeature('categories'))
                <div class="category-filters">
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-wider mb-2 ml-1">Categories</p>
                    <div class="flex flex-wrap gap-2">
                        <button class="ann-filter-pill all active px-4 py-1.5 rounded-xl bg-[var(--accent)] text-white text-xs font-bold transition-all shadow-sm" data-category="all">All Categories</button>
                        @php
                            $dynamicCategories = \App\Models\Category::where('type', 'announcement_category')->get();
                        @endphp
                        @foreach($dynamicCategories as $cat)
                            <button class="ann-filter-pill {{ strtolower($cat->name) }} px-4 py-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-bold border border-gray-100 dark:border-gray-700 hover:border-[var(--accent)] transition-all shadow-sm" data-category="{{ strtolower($cat->name) }}">{{ $cat->name }}</button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="interaction-filters">
                <p class="text-[11px] font-black text-gray-400 uppercase tracking-wider mb-2 ml-1">Interaction Filter</p>
                <div class="flex flex-wrap gap-2">
                    <button class="int-filter-pill all active px-4 py-1.5 rounded-xl bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-xs font-bold transition-all shadow-sm" data-filter="all">Any Interaction</button>
                    <button class="int-filter-pill reacted px-4 py-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-bold border border-gray-100 dark:border-gray-700 hover:border-rose-500 transition-all shadow-sm" data-filter="reacted">❤️ Reacted Only</button>
                    <button class="int-filter-pill commented px-4 py-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-bold border border-gray-100 dark:border-gray-700 hover:border-amber-500 transition-all shadow-sm" data-filter="commented">💬 Commented Only</button>
                    <button class="int-filter-pill popular px-4 py-1.5 rounded-xl bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-bold border border-gray-100 dark:border-gray-700 hover:border-blue-500 transition-all shadow-sm" data-filter="popular">🔥 Highly Engaged</button>
                </div>
            </div>
        </div>

        {{-- Date Filter --}}
        <div
            class="date-filter mb-8 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="date-filter-inner flex flex-wrap items-center gap-4">
                <div class="date-input-group flex items-center gap-2">
                    <label for="dateFrom" class="text-sm font-medium text-gray-600 dark:text-gray-400">From</label>
                    <input type="date" id="dateFrom"
                        class="date-input bg-gray-50 dark:bg-gray-700 border-none rounded-lg text-sm">
                </div>
                <div class="date-separator text-gray-400">—</div>
                <div class="date-input-group flex items-center gap-2">
                    <label for="dateTo" class="text-sm font-medium text-gray-600 dark:text-gray-400">To</label>
                    <input type="date" id="dateTo"
                        class="date-input bg-gray-50 dark:bg-gray-700 border-none rounded-lg text-sm">
                </div>
                <button
                    class="date-filter-btn px-4 py-2 bg-[var(--accent)] text-white rounded-lg text-sm font-bold hover:bg-[var(--accent-dark)] transition-colors"
                    id="applyDateFilter">Apply</button>
                <button
                    class="date-filter-clear px-4 py-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-sm font-medium"
                    id="clearDateFilter">Clear</button>
            </div>
        </div>

        {{-- Search Section --}}
        <div class="flex items-center justify-between gap-6 mb-8">
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Dashboard</h1>
            <form action="{{ url()->current() }}" method="GET" class="relative flex-1 max-w-md">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search announcements..." class="w-full pl-12 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl focus:ring-4 focus:ring-[var(--accent-rgb)]/10 focus:border-[var(--accent)] transition-all text-sm">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </form>
        </div>

        {{-- Announcement Cards --}}
        <div class="space-y-6 ann-list">
            @if($announcements->count() > 0)
                @foreach($announcements as $announcement)
                    <x-announcement-card :announcement="$announcement" :show-reactions="true" />
                @endforeach

                <div class="mt-8">
                    {{ $announcements->links() }}
                </div>
            @else
                <div
                    class="empty-state bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div
                        class="w-20 h-20 bg-[rgba(var(--accent-rgb),0.12)] rounded-full flex items-center justify-center text-[var(--accent)] mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">No announcements found</h3>
                    <p class="text-gray-500 dark:text-gray-400">Stay tuned! Announcements will appear here once posted by
                        the administration.</p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/tenant/studentpage.js'])
    @endpush

</x-app-layout>