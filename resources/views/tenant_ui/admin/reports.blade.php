<x-app-layout>
    <x-slot name="title">Reports - Buksu Eduboard</x-slot>

    <div class="admin-content" x-data="{ 
        exporting: false,
        showExportSuccess: false,
        exportData() {
            this.exporting = true;
            setTimeout(() => {
                this.exporting = false;
                this.showExportSuccess = true;
                setTimeout(() => { this.showExportSuccess = false }, 3000);
            }, 1500);
        }
    }">
        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">System Reports</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Generate and analyze activity reports for {{ tenant('school_name') ?? 'Buksu' }}</p>
            </div>
            <button @click="exportData()" :disabled="exporting" class="px-5 py-2.5 bg-teal-500 text-white rounded-xl text-sm font-bold hover:bg-teal-600 transition-all shadow-lg shadow-teal-500/20 active:scale-95 disabled:opacity-50 flex items-center gap-2">
                <template x-if="!exporting">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </template>
                <template x-if="exporting">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </template>
                <span x-text="exporting ? 'Exporting...' : 'Export PDF'"></span>
            </button>
        </div>

        {{-- Filters Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 mb-8">
            <form action="{{ route('tenant.admin.reports') }}" method="GET" class="flex flex-col md:flex-row items-end gap-4">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4 w-full">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-wider ml-1">Year</label>
                        <select name="year" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-bold appearance-none">
                            @foreach($availableYears as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-wider ml-1">Month</label>
                        <select name="month" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-bold appearance-none">
                            <option value="">All Months</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-wider ml-1">Day</label>
                        <input type="number" name="day" min="1" max="31" value="{{ $day }}" placeholder="e.g. 15" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border-none rounded-xl text-sm font-bold">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-6 py-2.5 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-xl text-sm font-bold hover:bg-black transition-all">
                        Filter
                    </button>
                    <a href="{{ route('tenant.admin.reports') }}" class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Announcements Section --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">Announcements</h2>
                    <span class="px-2 py-0.5 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 text-[10px] font-black rounded-lg border border-teal-100 dark:border-teal-900/30">{{ $announcements->count() }} TOTAL</span>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-900/20">
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-wider">Details</th>
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-wider">Engagement</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            @forelse($announcements as $announcement)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-700/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="space-y-0.5">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1">{{ $announcement->title }}</p>
                                            <p class="text-[10px] text-gray-500 font-medium">By {{ $announcement->postedBy->name ?? 'Deleted User' }} • {{ $announcement->created_at->format('M d') }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $catColor = match(strtolower($announcement->category)) {
                                                'emergency' => 'bg-red-50 text-red-600 border-red-100',
                                                'events' => 'bg-teal-50 text-teal-600 border-teal-100',
                                                'academic' => 'bg-blue-50 text-blue-600 border-blue-100',
                                                default => 'bg-gray-50 text-gray-600 border-gray-100'
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase border {{ $catColor }}">
                                            {{ $announcement->category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3 text-[10px] font-black text-gray-400">
                                            <span class="flex items-center gap-1"><span class="text-xs">❤️</span> {{ ($announcement->heart_count ?? 0) + ($announcement->like_count ?? 0) }}</span>
                                            <span class="flex items-center gap-1"><span class="text-xs">💬</span> {{ $announcement->comments->count() }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center gap-2 opacity-20">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                            <p class="text-xs font-bold uppercase tracking-widest">No data available</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Registrations Section --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">New Registrations</h2>
                    <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-black rounded-lg border border-blue-100 dark:border-blue-900/30">{{ $users->count() }} TOTAL</span>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-900/20">
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-wider">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-700/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-900 flex items-center justify-center text-[10px] font-black text-gray-500">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div class="space-y-0.5">
                                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                                <p class="text-[10px] text-gray-500 font-medium">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 text-[10px] font-black rounded-lg uppercase">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-bold text-gray-500">{{ $user->created_at->format('M d, Y') }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-20 text-center">
                                        <div class="flex flex-col items-center gap-2 opacity-20">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                            <p class="text-xs font-bold uppercase tracking-widest">No users found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Export Success Toast --}}
        <template x-teleport="body">
            <div x-show="showExportSuccess" x-transition:enter="translate-y-10 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="translate-y-10 opacity-0" class="fixed bottom-8 right-8 z-[200]" x-cloak>
                <div class="bg-gray-900 text-white px-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3">
                    <div class="w-6 h-6 bg-teal-500 rounded-full flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <span class="text-sm font-bold">Report exported successfully!</span>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>