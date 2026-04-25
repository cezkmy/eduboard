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
            <a href="{{ route('tenant.admin.reports.export', request()->all()) }}" class="px-5 py-2.5 bg-[var(--accent)] text-white rounded-xl text-sm font-bold hover:bg-[var(--accent-dark)] transition-all shadow-lg active:scale-95 flex items-center gap-2" style="box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.20);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <span>Export PDF</span>
            </a>
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

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Posts</p>
                    <p class="text-xl font-black text-gray-900 dark:text-white">{{ $announcements->count() }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">New Users</p>
                    <p class="text-xl font-black text-gray-900 dark:text-white">{{ $users->count() }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Reactions</p>
                    <p class="text-xl font-black text-gray-900 dark:text-white">{{ number_format($periodStats['reactions']) }}</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Comments</p>
                    <p class="text-xl font-black text-gray-900 dark:text-white">{{ number_format($periodStats['comments']) }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Announcements Section --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">Announcements</h2>
                    <span class="px-2 py-0.5 bg-[rgba(var(--accent-rgb),0.10)] text-[var(--accent)] text-[10px] font-black rounded-lg border" style="border-color: rgba(var(--accent-rgb), 0.22);">{{ $announcements->count() }} TOTAL</span>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden" style="background: var(--bg-card); border-color: var(--border-color);">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-900/20" style="background: var(--bg-card);">
                                <th class="px-6 py-4 text-[11px] font-black text-gray-500 dark:text-white uppercase tracking-wider">Details</th>
                                <th class="px-6 py-4 text-[11px] font-black text-gray-500 dark:text-white uppercase tracking-wider">Category</th>
                                <th class="px-6 py-4 text-[11px] font-black text-gray-500 dark:text-white uppercase tracking-wider">Engagement</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            @forelse($announcements as $announcement)
                                <tr class="hover:bg-[rgba(var(--accent-rgb),0.06)] dark:hover:bg-[rgba(var(--accent-rgb),0.14)] transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="space-y-0.5">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1">{{ $announcement->title }}</p>
                                            <p class="text-[10px] text-gray-500 font-medium">By {{ $announcement->postedBy->name ?? 'Deleted User' }} • {{ $announcement->created_at->format('M d') }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $categoryObj = \App\Models\Category::where('name', $announcement->category)->where('type', 'announcement_category')->first();
                                            $customColor = $categoryObj->color ?? null;
                                            
                                            if ($customColor) {
                                                $catStyle = "background-color: {$customColor}22; color: {$customColor}; border-color: {$customColor}44;";
                                            } else {
                                                $catColorClass = match(strtolower($announcement->category)) {
                                                    'emergency' => 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-300 border-red-100 dark:border-red-900/40',
                                                    'events' => 'bg-[rgba(var(--accent-rgb),0.10)] dark:bg-[rgba(var(--accent-rgb),0.18)] text-[var(--accent)] border-[rgba(var(--accent-rgb),0.22)] dark:border-[rgba(var(--accent-rgb),0.35)]',
                                                    'academic' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-300 border-blue-100 dark:border-blue-900/40',
                                                    default => 'bg-gray-50 dark:bg-gray-700/40 text-gray-600 dark:text-gray-300 border-gray-100 dark:border-gray-600'
                                                };
                                                $catStyle = "";
                                            }
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase border {{ $customColor ? '' : $catColorClass }}" style="{{ $catStyle }}">
                                            {{ $announcement->category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3 text-[10px] font-black text-gray-400">
                                            <span class="flex items-center gap-1" title="Reactions in this period"><span class="text-xs">❤️</span> {{ $announcement->reactions_count }}</span>
                                            <span class="flex items-center gap-1" title="Comments in this period"><span class="text-xs">💬</span> {{ $announcement->comments_count }}</span>
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
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden" style="background: var(--bg-card); border-color: var(--border-color);">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-900/20" style="background: var(--bg-card);">
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 dark:text-white uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 dark:text-white uppercase tracking-wider">Role</th>
                                <th class="px-6 py-4 text-[11px] font-black text-gray-400 dark:text-white uppercase tracking-wider">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            @forelse($users as $user)
                                <tr class="hover:bg-[rgba(var(--accent-rgb),0.06)] dark:hover:bg-[rgba(var(--accent-rgb),0.14)] transition-colors">
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
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-white" style="background: var(--accent);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <span class="text-sm font-bold">Report exported successfully!</span>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>