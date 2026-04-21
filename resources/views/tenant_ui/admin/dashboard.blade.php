<x-app-layout>
    <div class="content-header">
        <h1 class="content-title">Dashboard</h1>
        <p class="content-subtitle">Welcome back, {{ auth()->user()->name }}</p>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card hover:shadow-lg dark:hover:bg-gray-700/80 transition-all duration-300">
            <div class="stat-info">
                <div class="stat-label">Total Announcements</div>
                <div class="stat-value">{{ $totalAnnouncements }}</div>
            </div>
            <div class="stat-icon teal">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
            </div>
        </div>

        <div class="stat-card hover:shadow-lg dark:hover:bg-gray-700/80 transition-all duration-300">
            <div class="stat-info">
                <div class="stat-label">Total Teachers</div>
                <div class="stat-value">{{ $totalTeachers }}</div>
            </div>
            <div class="stat-icon blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0118 18c-2.305 0-4.408.867-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
        </div>

        <div class="stat-card hover:shadow-lg dark:hover:bg-gray-700/80 transition-all duration-300">
            <div class="stat-info">
                <div class="stat-label">Total Students</div>
                <div class="stat-value">{{ $totalStudents }}</div>
            </div>
            <div class="stat-icon amber">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147L12 14.63l7.74-4.483m-15.48 0L12 5.667l7.74 4.48m-15.48 0v6.331c0 .603.346 1.154.894 1.442L12 21.35l7.106-3.73a1.5 1.5 0 00.894-1.442V10.147m-15.48 0L12 14.63l7.74-4.483" />
                </svg>
            </div>
        </div>

        @if(auth()->user()->hasPermission('page_admin_users'))
        <a href="{{ route('tenant.admin.users') }}?tab=pending" class="stat-card hover:shadow-lg dark:hover:bg-gray-700/80 transition-all duration-300 group">
            <div class="stat-info">
                <div class="stat-label">Pending Approvals</div>
                <div class="stat-value {{ $pendingApprovalsCount > 0 ? 'text-red-500' : '' }}">{{ $pendingApprovalsCount }}</div>
            </div>
            <div class="stat-icon red group-hover:scale-110 transition-transform">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
            </div>
        </a>
        @endif

        @if(auth()->user()->hasPermission('page_admin_dashboard'))
        <div class="stat-card hover:shadow-lg dark:hover:bg-gray-700/80 transition-all duration-300">
            <div class="stat-info">
                <div class="stat-label">Total Engagement</div>
                <div class="stat-value">{{ $totalReactions + $totalComments }}</div>
            </div>
            <div class="stat-icon purple">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </div>
        </div>
        @endif
    </div>

    <div class="admin-grid-layout">
        {{-- Left: Recent Announcements --}}
        @if(auth()->user()->hasPermission('page_admin_dashboard'))
        <div class="grid-main">
            <div class="section-header">
                <h2>Recent Announcements</h2>
                <a href="{{ route('tenant.admin.announcements') }}" class="view-all">View All</a>
            </div>

            <div class="space-y-3">
                @forelse($recentAnnouncements as $announcement)
                    @php
                        $authorInitial = strtoupper(substr($announcement->postedBy?->name ?? 'S', 0, 1));
                    @endphp
                    <a href="{{ route('tenant.admin.announcements') }}" class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/50 hover:border-[var(--accent)] hover:bg-gray-50 dark:hover:bg-gray-700/80 transition-all group shadow-sm cursor-pointer no-underline">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-gray-50 dark:bg-gray-900/50 flex items-center justify-center text-gray-500 dark:text-gray-400 font-black text-lg border border-gray-100 dark:border-gray-700 group-hover:border-[var(--accent)] group-hover:text-[var(--accent)] transition-all">
                                {{ $authorInitial }}
                            </div>
                            <div>
                                <h4 class="font-black text-gray-900 dark:text-white leading-tight group-hover:text-[var(--accent)] transition-colors">{{ $announcement->title }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-gray-500 dark:group-hover:text-gray-300 transition-colors">{{ $announcement->postedBy?->name ?? 'System' }}</span>
                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <span class="text-[10px] font-black text-[var(--accent)] uppercase tracking-widest">{{ $announcement->category }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter block group-hover:text-gray-500 dark:group-hover:text-gray-300 transition-colors">{{ $announcement->created_at->format('M d') }}</span>
                            <span class="text-[9px] font-bold text-gray-400/60 uppercase block">{{ $announcement->created_at->diffForHumans() }}</span>
                        </div>
                    </a>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700 p-12 text-center">
                        <p class="text-gray-500 font-bold uppercase tracking-widest text-xs">No recent announcements</p>
                    </div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- Right: Recent Pending Approvals --}}
        @if(auth()->user()->hasPermission('page_admin_users'))
        <div class="lg:col-span-1">
            <div class="section-header">
                <h2>Pending Approvals</h2>
                <a href="{{ route('tenant.admin.users') }}?tab=pending" class="view-all">Manage</a>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentPendingUsers as $user)
                        <div class="p-4 flex items-center justify-between hover:bg-[var(--accent)] transition-all cursor-pointer group rounded-lg mx-2 my-1">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 font-bold text-xs group-hover:scale-110 group-hover:bg-white group-hover:text-[var(--accent)] transition-all">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-white transition-colors">{{ $user->name }}</p>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 group-hover:text-white/80 transition-colors">{{ $user->course ?? 'N/A' }} · {{ $user->year_level ?? '' }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 text-[10px] font-bold uppercase tracking-wider rounded-lg border border-amber-100/50 dark:border-amber-900/30 group-hover:bg-white group-hover:text-[var(--accent)] group-hover:border-white transition-all">Pending</span>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <p class="text-xs text-gray-500">No pending requests.</p>
                        </div>
                    @endforelse
                </div>
                
                <a href="{{ route('tenant.admin.users') }}?tab=pending" class="block p-3 text-center text-xs font-medium text-[var(--accent)] bg-gray-50 dark:bg-gray-700/50 hover:bg-[var(--accent)] hover:text-white transition-all">
                    View All Pending Requests
                </a>
            </div>

            {{-- Quick Tip --}}
            <div class="mt-4 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900/30">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-white-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-[12px] text-slate-900 dark:text-white-200 leading-relaxed">
                        New student registrations require admin approval before they can access their portal.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>










