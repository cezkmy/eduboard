<x-app-layout>
    <div class="content-header">
        <h1 class="content-title">Interactions</h1>
        <p class="content-subtitle">Track engagement on your announcements</p>
    </div>

    {{-- Stats Grid --}}
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
                <div class="stat-label">Total Reactions</div>
                <div class="stat-value">{{ number_format($totalReactions) }}</div>
            </div>
            <div class="stat-icon amber">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Comments</div>
                <div class="stat-value">{{ number_format($totalComments) }}</div>
            </div>
            <div class="stat-icon teal">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Recent Reactions Feed --}}
        <div>
            <div class="section-header">
                <h2>Latest Reactions</h2>
            </div>
            <div class="space-y-3 mt-4">
                @forelse($recentReactions as $reaction)
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-500 text-lg">
                            {{ $reaction->type === 'heart' ? '❤️' : ($reaction->type === 'like' ? '👍' : ($reaction->type === 'fire' ? '🔥' : '😢')) }}
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-900 dark:text-white">
                                <span class="font-black">{{ $reaction->user->name }}</span>
                                <span class="text-gray-500">reacted to</span>
                                <span class="font-bold">"{{ Str::limit($reaction->announcement->title, 30) }}"</span>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1 uppercase font-bold">{{ $reaction->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">No reactions yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Comments Feed --}}
        <div>
            <div class="section-header">
                <h2>Latest Comments</h2>
            </div>
            <div class="space-y-3 mt-4">
                @forelse($recentComments as $comment)
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3 mb-2">
                            <img src="{{ url('/api/placeholder/32/32') }}" class="w-8 h-8 rounded-full border-2 border-teal-500/20" />
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-black text-gray-900 dark:text-white truncate">{{ $comment->user->name }}</p>
                                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-tighter">On: {{ Str::limit($comment->announcement->title, 25) }}</p>
                            </div>
                            <span class="text-[10px] text-gray-400 uppercase font-bold">{{ $comment->created_at->diffForHumans(null, true) }}</span>
                        </div>
                        <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">{{ Str::limit($comment->content, 80) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">No comments yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>










