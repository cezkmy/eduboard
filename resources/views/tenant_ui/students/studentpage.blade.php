<x-app-layout>
    <x-slot name="title">Announcements</x-slot>

    {{-- Content --}}
    <div class="content">

        {{-- Page Header --}}
        <div class="page-header mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">Announcements</h1>
            <p class="text-gray-500 dark:text-gray-400">Stay updated with the latest from {{ tenant('school_name') ?? 'Westfield Academy' }}</p>
        </div>

        {{-- Tabs --}}
        <div class="tabs flex gap-4 mb-6 border-b border-gray-200 dark:border-gray-700" x-data="{ activeTab: 'general' }">
            <button class="tab px-4 py-2 font-bold transition-all" 
                    :class="activeTab === 'general' ? 'text-[var(--accent)] border-b-2 border-[var(--accent)]' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    @click="activeTab = 'general'; $dispatch('filter-tab', 'general')">General</button>
            <button class="tab px-4 py-2 font-bold transition-all" 
                    :class="activeTab === 'foryou' ? 'text-[var(--accent)] border-b-2 border-[var(--accent)]' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    @click="activeTab = 'foryou'; $dispatch('filter-tab', 'foryou')">For You</button>
        </div>

        {{-- Category Pills --}}
        @if(tenant() && tenant()->hasFeature('categories'))
        <div class="categories flex flex-wrap gap-2 mb-8">
            <button class="ann-filter-pill all active px-4 py-1.5 rounded-full bg-[var(--accent)] text-white text-sm font-semibold" data-category="all">All</button>
            <button class="ann-filter-pill academic px-4 py-1.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-sm font-semibold" data-category="academic">Academic</button>
            <button class="ann-filter-pill events px-4 py-1.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-semibold" data-category="events">Events</button>
            <button class="ann-filter-pill administrative px-4 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-sm font-semibold" data-category="administrative">Administrative</button>
            <button class="ann-filter-pill student-affairs px-4 py-1.5 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-sm font-semibold" data-category="student-affairs">Student Affairs</button>
            <button class="ann-filter-pill emergency px-4 py-1.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-semibold" data-category="emergency">Emergency</button>
        </div>
        @endif

        {{-- Date Filter --}}
        <div class="date-filter mb-8 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="date-filter-inner flex flex-wrap items-center gap-4">
                <div class="date-input-group flex items-center gap-2">
                    <label for="dateFrom" class="text-sm font-medium text-gray-600 dark:text-gray-400">From</label>
                    <input type="date" id="dateFrom" class="date-input bg-gray-50 dark:bg-gray-700 border-none rounded-lg text-sm">
                </div>
                <div class="date-separator text-gray-400">—</div>
                <div class="date-input-group flex items-center gap-2">
                    <label for="dateTo" class="text-sm font-medium text-gray-600 dark:text-gray-400">To</label>
                    <input type="date" id="dateTo" class="date-input bg-gray-50 dark:bg-gray-700 border-none rounded-lg text-sm">
                </div>
                <button class="date-filter-btn px-4 py-2 bg-[var(--accent)] text-white rounded-lg text-sm font-bold hover:bg-[var(--accent-dark)] transition-colors" id="applyDateFilter">Apply</button>
                <button class="date-filter-clear px-4 py-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 text-sm font-medium" id="clearDateFilter">Clear</button>
            </div>
        </div>

        {{-- Announcement Cards --}}
        <div class="space-y-6 ann-list">
            
            @forelse($announcements as $announcement)
                @php
                    $mediaPaths = is_array($announcement->media_paths) ? $announcement->media_paths : json_decode($announcement->media_paths ?? '[]', true) ?? [];
                    $mediaCount = count($mediaPaths);
                    $authorInitial = strtoupper(substr($announcement->postedBy?->name ?? 'S', 0, 1));
                    $avatarClass = match(strtolower($announcement->category)) {
                        'emergency' => 'bg-red-100 text-red-600',
                        'events' => 'bg-green-100 text-green-600',
                        'academic' => 'bg-blue-100 text-blue-600',
                        'administrative' => 'bg-amber-100 text-amber-600',
                        default => 'bg-gray-100 text-gray-600'
                    };
                    $userReactions = $announcement->reactions->where('user_id', auth()->id())->pluck('type')->toArray();
                @endphp
                <div class="ann-card bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm hover:shadow-md transition-all" id="card-{{ $announcement->id }}" data-category="{{ strtolower($announcement->category) }}">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl {{ $avatarClass }} overflow-hidden flex items-center justify-center font-bold text-lg">
                                {{ $authorInitial }}
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-gray-900 dark:text-gray-100">{{ $announcement->postedBy?->name ?? 'System' }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $announcement->created_at->format('M d, Y') }} · <span class="uppercase tracking-wider font-semibold">{{ $announcement->category }}</span></p>
                            </div>
                        </div>
                        @if($announcement->is_pinned)
                            <span class="px-2.5 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-bold uppercase rounded-lg tracking-widest">Pinned</span>
                        @endif
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-3">{{ $announcement->title }}</h3>
                    
                    <div class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed {{ $mediaCount > 0 ? 'mb-4' : '' }}">
                        {{ $announcement->content }}
                    </div>

                    @if($mediaCount > 0)
                        {{-- Photo Display --}}
                        <div class="grid {{ $mediaCount > 1 ? 'grid-cols-2' : 'grid-cols-1' }} gap-3 mt-4">
                            @foreach($mediaPaths as $path)
                                @php
                                    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                                    $isVideo = in_array($extension, ['mp4', 'mov', 'avi']);
                                @endphp
                                <div class="rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 aspect-video">
                                    @if($isImage)
                                        <img src="{{ asset('storage/'.$path) }}" alt="{{ $announcement->title }}" class="w-full h-full object-cover">
                                    @elseif($isVideo)
                                        <video class="w-full h-full object-cover" controls preload="metadata">
                                            <source src="{{ asset('storage/'.$path) }}" type="video/mp4">
                                        </video>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex items-center gap-3 mt-6 border-t border-gray-50 dark:border-gray-700 pt-4">
                        <button class="reaction-btn flex items-center gap-1.5 px-3 py-1.5 rounded-xl {{ in_array('heart', $userReactions) ? 'bg-[rgba(var(--accent-rgb),0.10)] text-[var(--accent)]' : 'bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300' }} text-xs font-bold hover:bg-red-50 dark:hover:bg-red-900/20 transition-all" data-type="heart" data-id="{{ $announcement->id }}">
                            <span>❤️</span> <span class="count">{{ $announcement->heart_count ?? 0 }}</span>
                        </button>
                        <button class="reaction-btn flex items-center gap-1.5 px-3 py-1.5 rounded-xl {{ in_array('like', $userReactions) ? 'bg-[rgba(var(--accent-rgb),0.10)] text-[var(--accent)]' : 'bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300' }} text-xs font-bold hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all" data-type="like" data-id="{{ $announcement->id }}">
                            <span>👍</span> <span class="count">{{ $announcement->like_count ?? 0 }}</span>
                        </button>
                        <button class="reaction-btn flex items-center gap-1.5 px-3 py-1.5 rounded-xl {{ in_array('fire', $userReactions) ? 'bg-[rgba(var(--accent-rgb),0.10)] text-[var(--accent)]' : 'bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300' }} text-xs font-bold hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-all" data-type="fire" data-id="{{ $announcement->id }}">
                            <span>🔥</span> <span class="count">{{ $announcement->fire_count ?? 0 }}</span>
                        </button>
                        <button class="reaction-btn flex items-center gap-1.5 px-3 py-1.5 rounded-xl {{ in_array('sad', $userReactions) ? 'bg-[rgba(var(--accent-rgb),0.10)] text-[var(--accent)]' : 'bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300' }} text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all" data-type="sad" data-id="{{ $announcement->id }}">
                            <span>😮</span> <span class="count">{{ $announcement->sad_count ?? 0 }}</span>
                        </button>
                    </div>

                    {{-- Comment Section --}}
                    <div class="mt-6 border-t border-gray-50 dark:border-gray-700 pt-6">
                        <h5 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            Comments
                        </h5>
                        
                        {{-- Comment List --}}
                        <div class="comment-list space-y-4 mb-4" id="comments-{{ $announcement->id }}">
                            @foreach($announcement->comments->where('parent_id', null) as $comment)
                                <div class="comment-item flex gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[rgba(var(--accent-rgb),0.15)] text-[var(--accent)] flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="bg-gray-50 dark:bg-gray-700/30 p-3 rounded-2xl rounded-tl-none">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-xs font-bold text-gray-900 dark:text-gray-100">{{ $comment->user->name }}</span>
                                                <span class="text-[10px] text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $comment->content }}</p>
                                        </div>
                                        <div class="flex items-center gap-4 mt-2 ml-2">
                                            <button class="text-[10px] font-bold text-gray-400 hover:text-[var(--accent)] transition-colors reply-btn" data-comment-id="{{ $comment->id }}" data-user-name="{{ $comment->user->name }}">Reply</button>
                                        </div>

                                        {{-- Replies --}}
                                        @if($comment->replies->count() > 0)
                                            <div class="mt-3 space-y-3 ml-4 border-l-2 border-gray-100 dark:border-gray-700 pl-4">
                                                @foreach($comment->replies as $reply)
                                                    <div class="comment-item flex gap-3">
                                                        <div class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-[10px] flex-shrink-0">
                                                            {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                                        </div>
                                                        <div class="flex-1">
                                                            <div class="bg-gray-50 dark:bg-gray-700/30 p-2.5 rounded-2xl rounded-tl-none">
                                                                <div class="flex items-center justify-between mb-1">
                                                                    <span class="text-[10px] font-bold text-gray-900 dark:text-gray-100">{{ $reply->user->name }}</span>
                                                                    <span class="text-[10px] text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                                                </div>
                                                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $reply->content }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Comment Input --}}
                        <div class="mt-4 flex gap-3 items-start">
                            <div class="w-8 h-8 rounded-lg bg-[var(--accent)] text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 relative">
                                <div class="reply-indicator hidden bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-t-xl text-[10px] flex items-center justify-between">
                                    <span>Replying to <span class="replying-to-name font-bold"></span></span>
                                    <button class="cancel-reply text-red-500">✕</button>
                                </div>
                                <textarea class="comment-textarea w-full bg-gray-50 dark:bg-gray-700 border-none rounded-xl text-sm p-3 focus:ring-1 focus:ring-[var(--accent)] custom-scrollbar" placeholder="Write a comment..." rows="1" data-announcement-id="{{ $announcement->id }}"></textarea>
                                <button class="submit-comment absolute right-2 bottom-2 p-1.5 text-[var(--accent)] hover:bg-[rgba(var(--accent-rgb),0.10)] rounded-lg transition-all" data-announcement-id="{{ $announcement->id }}">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state bg-white dark:bg-gray-800 rounded-3xl p-12 text-center border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="w-20 h-20 bg-[rgba(var(--accent-rgb),0.12)] rounded-full flex items-center justify-center text-[var(--accent)] mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">No announcements found</h3>
                    <p class="text-gray-500 dark:text-gray-400">Stay tuned! Announcements will appear here once posted by the administration.</p>
                </div>
            @endforelse
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/tenant/studentpage.js'])
    @endpush

</x-app-layout>
