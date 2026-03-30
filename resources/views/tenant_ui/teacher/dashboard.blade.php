<x-app-layout>
    <div class="content-header">
        <h1 class="content-title">Dashboard</h1>
        <p class="content-subtitle">Welcome back, {{ Auth::user()->name }}</p>
    </div>

    {{-- Stats Grid matching Admin style --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">My Announcements</div>
                <div class="stat-value">{{ $myAnnouncementsCount }}</div>
            </div>
            <div class="stat-icon blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9Z" />
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Views</div>
                <div class="stat-value">{{ number_format($totalViews) }}</div>
            </div>
            <div class="stat-icon teal">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Reactions</div>
                <div class="stat-value">{{ $totalReactions }}</div>
            </div>
            <div class="stat-icon amber">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <div class="section-header">
            <h2>Recent Announcements</h2>
            <a href="{{ route('tenant.teacher.announcements') }}" class="section-link">View All</a>
        </div>
        
        <div class="announcements">
            @forelse($recentAnnouncements as $announcement)
                <x-announcement-card :announcement="$announcement" :show-reactions="false" />
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No announcements yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>










