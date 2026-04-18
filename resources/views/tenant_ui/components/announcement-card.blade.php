@php
    $categoryColors = [
        'General' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'Academic' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'Events' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'Administrative' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
        'Emergency' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    ];
    $reactionEmojis = ['heart' => '❤️', 'like' => '👍', 'fire' => '🔥', 'sad' => '😢'];
    $mediaPaths = is_array($announcement->media_paths) ? $announcement->media_paths : (json_decode($announcement->media_paths ?? '[]', true) ?? []);
    $mediaCount = count($mediaPaths);

    // Fetch template if template_id is set
    $template = null;
    if (!empty($announcement->template_id)) {
        $template = tenancy()->central(function () use ($announcement) {
            return \App\Models\Template::find($announcement->template_id);
        });
    }
    
    $templateStyle = '';
    if ($template && $template->image) {
        $imgUrl = global_asset('template/' . $template->image);
        $templateStyle = "border: 50px solid transparent; border-image: url({$imgUrl}) 120 round; border-image-outset: 15px;";
    }

    $borderColor = $announcement->border_color ?? 'transparent';
    $bgColor = $announcement->bg_color ?? '#ffffff';
    $titleColor = $announcement->title_color ?? '#111827';
    $contentColor = $announcement->content_color ?? '#374151';
    $categoryColor = $announcement->category_color ?? '#374151';
    $fontStyle = $announcement->font_style ?? '';
    $mediaLayout = $announcement->media_layout ?? 'default';
    $layoutType = $announcement->layout_type ?? 'default';
    $borderRadius = $announcement->border_radius ?? 32;
    
    // Determine if custom BG is white/light and override in dark mode
    $isLightBg = ($bgColor === '#ffffff' || strtolower($bgColor) === '#fff');
    $announcementBg = $isLightBg ? 'var(--card-bg-default)' : $bgColor;
@endphp

<style>
    :root {
        --card-bg-default: {{ $bgColor }};
        --title-color: {{ $titleColor }};
        --content-color: {{ $contentColor }};
        --category-color: {{ $categoryColor }};
    }
    .dark {
        --card-bg-default: #1f2937; /* gray-800 */
        
        /* Auto-flip default dark colors to light in dark mode */
        @if($isLightBg)
            @if($titleColor === '#111827') --title-color: #f9fafb; @endif
            @if($contentColor === '#374151' || $contentColor === '#4b5563') --content-color: #e5e7eb; @endif
            @if($categoryColor === '#374151' || $categoryColor === '#4b5563') --category-color: #9ca3af; @endif
        @endif
    }
    .announcement-card-{{ $announcement->id }} {
        background-color: var(--card-bg-default);
    }
</style>
@php
    $wrapperClasses = 'ann-wrapper relative my-12 flex flex-col gap-4';
    if ($layoutType === 'landscape') $wrapperClasses .= ' w-full mx-auto';
    elseif ($layoutType === 'portrait') $wrapperClasses .= ' w-full max-w-4xl mx-auto';
    else $wrapperClasses .= ' w-full max-w-full mx-auto';
    
    $layoutClasses = 'ann-card relative flex flex-col p-6 transition-all duration-500 shadow-lg group/card';

    $mediaAspect = 'aspect-video';
    if ($mediaLayout === 'portrait') $mediaAspect = 'aspect-[3/4]';
    elseif ($mediaLayout === 'square') $mediaAspect = 'aspect-square';

    $borderStyle = '';
    if ($templateStyle) {
        $borderStyle = $templateStyle;
    } elseif ($borderColor && $borderColor !== 'transparent') {
        $borderStyle = "border: 8px solid {$borderColor};";
    } else {
        $borderStyle = "border: 1px solid rgba(0,0,0,0.05);";
    }

    // Comment count including replies
    $totalComments = $announcement->comments->count();
@endphp

<div 
    x-data="{ 
        galleryOpen: false, 
        viewerOpen: false, 
        commentsOpen: false,
        currentIndex: 0, 
        media: {{ json_encode(array_map(fn($path) => [
            'url' => (function_exists('tenant_asset') && tenant()) ? tenant_asset($path) : asset('storage/'.$path),
            'type' => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['mp4', 'mov', 'avi', 'webm']) ? 'video' : 'image'
        ], $mediaPaths)) }},
        reactions: {
            heart: { count: {{ $announcement->heart_count ?? 0 }}, active: {{ in_array('heart', $userReactions ?? []) ? 'true' : 'false' }} },
            like: { count: {{ $announcement->like_count ?? 0 }}, active: {{ in_array('like', $userReactions ?? []) ? 'true' : 'false' }} },
            fire: { count: {{ $announcement->fire_count ?? 0 }}, active: {{ in_array('fire', $userReactions ?? []) ? 'true' : 'false' }} },
            sad: { count: {{ $announcement->sad_count ?? 0 }}, active: {{ in_array('sad', $userReactions ?? []) ? 'true' : 'false' }} }
        },
        async toggleReaction(type) {
            try {
                const response = await fetch('{{ route("tenant.announcements.react.toggle", $announcement) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ type })
                });
                const data = await response.json();
                if (data.success) {
                    this.reactions[type].count = data.count;
                    this.reactions[type].active = data.active;
                }
            } catch (error) {
                console.error('Error toggling reaction:', error);
            }
        },
        openGallery() { 
            this.galleryOpen = true; 
            document.body.style.overflow = 'hidden';
        },
        closeGallery() {
            this.galleryOpen = false;
            document.body.style.overflow = 'auto';
        },
        openViewer(index) { 
            this.currentIndex = index; 
            this.viewerOpen = true; 
            document.body.style.overflow = 'hidden';
        },
        closeViewer() {
            this.viewerOpen = false;
            if (!this.galleryOpen) document.body.style.overflow = 'auto';
        },
        next() { this.currentIndex = (this.currentIndex + 1) % this.media.length; },
        prev() { this.currentIndex = (this.currentIndex - 1 + this.media.length) % this.media.length; }
    }"
    class="{{ $wrapperClasses }}"
    id="announcement-wrapper-{{ $announcement->id }}"
>
    {{-- INNER BORDERED ANNOUNCEMENT CARD --}}
    <div 
        class="{{ $layoutClasses }} announcement-card-{{ $announcement->id }}"
        data-category="{{ strtolower($announcement->category ?? 'general') }}"
        style="border-radius: 1.5rem; box-shadow: 0 15px 30px -10px {{ $isLightBg ? 'rgba(0,0,0,0.08)' : $bgColor.'44' }}; {{ $borderStyle }}"
    >
        {{-- Background Decoration --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32 blur-3xl pointer-events-none group-hover/card:bg-white/10 transition-all duration-700"></div>
        
        <div class="flex-1 flex flex-col gap-6 relative z-10 {{ $fontStyle }}">
        {{-- Header Section --}}
        <div class="space-y-4 shrink-0">
            <div class="flex items-start justify-between gap-6">
                <div class="flex-1 space-y-1">
                    <h3 class="text-xl font-black leading-tight tracking-tight break-words" style="color: var(--title-color)">
                        {{ $announcement->title }}
                    </h3>
                    <div class="flex items-center gap-3 pt-2">
                        <span class="px-4 py-1.5 bg-black/5 rounded-full text-[12px] font-black uppercase tracking-[0.1em] shadow-sm" style="color: var(--category-color)">
                            {{ $announcement->category ?? 'General' }}
                        </span>
                        <div class="flex flex-col">
                            <span class="text-[12px] font-black text-gray-900 dark:text-white">
                                {{ $announcement->postedBy?->name ?? 'School Administrator' }}
                            </span>
                            <span class="text-[11px] font-bold text-gray-500 uppercase tracking-tighter">
                                {{ $announcement->created_at->format('M d, Y') }} • {{ $announcement->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>
                @if($announcement->is_pinned ?? false)
                    <div class="flex flex-col items-center gap-1 shrink-0">
                        <span class="p-2.5 bg-red-500 text-white rounded-2xl shadow-xl shadow-red-500/40">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                <path d="M11 2a1 1 0 1 1 2 0v1.007c3.414.218 6.13 2.924 6.364 6.335L20 12.358V14a1 1 0 0 1-1 1h-6v5h1a1 1 0 1 1 0 2h-4a1 1 0 1 1 0-2h1v-5H5a1 1 0 0 1-1-1v-1.642l.636-2.96c.234-3.41 2.95-6.117 6.364-6.335V2ZM7.14 13h9.72l-.544-2.528A4.4 4.4 0 0 0 12 7a4.4 4.4 0 0 0-4.316 3.472L7.14 13Z"/>
                            </svg>
                        </span>
                        <span class="text-[9px] font-black text-red-500 uppercase tracking-widest">Pinned</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Content Section --}}
        <div class="relative">
            <div class="absolute -left-6 top-0 bottom-0 w-1 bg-gradient-to-b from-black/10 via-black/5 to-transparent rounded-full opacity-50"></div>
            <p class="text-base leading-relaxed break-words font-medium opacity-90" style="color: var(--content-color)">
                {{ $announcement->content }}
            </p>
        </div>

        {{-- Inner Image Container --}}
        @if($mediaPaths && $mediaCount > 0)
            <div class="w-full {{ $mediaAspect }} bg-gray-50 dark:bg-gray-900 border-4 border-white dark:border-gray-800 overflow-hidden shadow-2xl relative group/media shrink-0" style="border-radius: {{ $borderRadius }}px">
                <div class="w-full h-full grid gap-2 {{ $mediaCount === 1 ? 'grid-cols-1' : 'grid-cols-2' }} {{ $mediaCount >= 3 ? 'grid-rows-2' : '' }}">
                    @foreach(array_slice($mediaPaths, 0, 4) as $index => $path)
                        @php
                            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);
                            
                            $colSpan = '';
                            if ($mediaCount === 3 && $index === 0) $colSpan = 'col-span-2 row-span-1';
                            elseif ($mediaCount === 2) $colSpan = 'col-span-1 row-span-2';
                            elseif ($mediaCount === 1) $colSpan = 'col-span-1 row-span-2';
                        @endphp
                        
                        <div class="relative w-full h-full overflow-hidden {{ $colSpan }} bg-gray-100 dark:bg-gray-800">
                            @if($isImage)
                                <img src="{{ (function_exists('tenant_asset') && tenant()) ? tenant_asset($path) : asset('storage/'.$path) }}" class="w-full h-full object-cover cursor-pointer hover:scale-110 transition-transform duration-700" @click="openViewer({{ $index }})" loading="lazy">
                            @elseif($isVideo)
                                <video class="w-full h-full object-cover" controls>
                                    <source src="{{ (function_exists('tenant_asset') && tenant()) ? tenant_asset($path) : asset('storage/'.$path) }}" type="video/mp4">
                                </video>
                            @endif

                            @if($index === 3 && $mediaCount > 4)
                                <div class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center cursor-pointer group/more" @click="openGallery()">
                                    <span class="text-white font-black text-3xl group-hover:scale-125 transition-transform">+{{ $mediaCount - 4 }}</span>
                                    <span class="text-white/70 text-xs font-bold uppercase tracking-widest mt-1">View All</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        </div>
    </div>

    {{-- OUTER REACTIONS & COMMENTS (OUTSIDE THE BORDER) --}}
    <div class="flex flex-col gap-6 px-2">
        {{-- Footer/Reactions Section --}}
        <div class="flex items-center justify-between pt-2">
            @if($showReactions ?? true)
                <div class="flex items-center gap-3">
                    @foreach($reactionEmojis as $type => $emoji)
                        <button 
                            type="button" 
                            @click="toggleReaction('{{ $type }}')"
                            class="group/emoji flex items-center gap-2 px-4 py-2 rounded-2xl text-[14px] font-bold transition-all border shadow-sm active:scale-95"
                            :class="reactions.{{ $type }}.active ? 'bg-white dark:bg-gray-800 border-{{ $type === 'heart' ? 'red' : ($type === 'like' ? 'blue' : ($type === 'fire' ? 'orange' : 'gray')) }}-200' : 'bg-black/5 border-transparent hover:bg-white dark:hover:bg-gray-800 hover:border-black/5'"
                        >
                            <span class="group-hover/emoji:scale-125 transition-transform duration-300" :class="reactions.{{ $type }}.active ? 'scale-110' : ''">{{ $emoji }}</span>
                            <span class="text-xs" :class="reactions.{{ $type }}.active ? 'text-{{ $type === 'heart' ? 'red' : ($type === 'like' ? 'blue' : ($type === 'fire' ? 'orange' : 'gray')) }}-600' : ''" x-text="reactions.{{ $type }}.count"></span>
                        </button>
                    @endforeach
                </div>
            @endif
            
            <button type="button" @click="commentsOpen = !commentsOpen" class="flex items-center gap-2 px-6 py-2.5 bg-gray-100 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-700 rounded-2xl text-gray-600 dark:text-gray-400 font-bold transition-all border border-transparent hover:border-black/5 active:scale-95 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h9m-9 3h3m-12 1.5l.5-1.5a9 9 0 1111.21 3H2.25z" />
                </svg>
                <span class="text-sm">Comments</span>
                @php $actualCommentCount = $announcement->comments->count(); @endphp
                <span class="px-2 py-0.5 bg-white dark:bg-gray-700 rounded-lg text-[10px] shadow-inner" x-text="{{ $actualCommentCount }}"></span>
            </button>
        </div>

        {{-- Comments Section (Expandable) --}}
        <div x-show="commentsOpen" x-collapse class="border-t border-black/5 mt-6 pt-6 space-y-6">
            <div x-data="{ 
                commentContent: '', 
                isSubmitting: false,
                async postComment() {
                    if (!this.commentContent.trim() || this.isSubmitting) return;
                    this.isSubmitting = true;
                    try {
                        const response = await fetch('{{ route("tenant.announcements.comment.store", $announcement) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ content: this.commentContent })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.commentContent = '';
                            window.location.reload(); // Simple reload to show new comment
                        }
                    } catch (error) {
                        console.error('Error posting comment:', error);
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }" class="flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-black shrink-0 shadow-lg shadow-emerald-500/20">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 relative">
                    <textarea 
                        x-model="commentContent"
                        placeholder="Write a comment..." 
                        class="w-full px-6 py-4 bg-gray-100 dark:bg-gray-800/50 border border-transparent focus:border-emerald-500 dark:focus:border-emerald-400 rounded-[2rem] text-sm focus:ring-4 focus:ring-emerald-500/10 transition-all resize-none min-h-[56px] custom-scrollbar dark:text-gray-200"
                        rows="1"
                        @keydown.enter.prevent="postComment()"
                    ></textarea>
                    <button 
                        @click="postComment()"
                        :disabled="!commentContent.trim() || isSubmitting"
                        class="absolute right-2 top-2 p-2 bg-emerald-500 text-white rounded-full shadow-lg shadow-emerald-500/20 hover:scale-105 active:scale-95 transition-all disabled:opacity-50 disabled:scale-100"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.125A59.769 59.769 0 0121.485 12 59.768 59.768 0 013.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Comments List --}}
            <div class="space-y-6 pl-4">
                @forelse($announcement->comments->where('parent_id', null) as $comment)
                    <div class="flex gap-4 group/comment" x-data="{ replying: false, replyContent: '' }">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 flex items-center justify-center font-bold shrink-0">
                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 space-y-2">
                            <div class="bg-gray-100 dark:bg-gray-800/80 rounded-[2rem] px-6 py-4 inline-block max-w-full shadow-sm border border-transparent dark:border-gray-700/50">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-black text-gray-900 dark:text-white">{{ $comment->user->name }}</span>
                                    <span class="text-[10px] text-gray-400 font-bold">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed break-words">{{ $comment->content }}</p>
                            </div>
                            <div class="flex items-center gap-4 pl-4">
                                <button @click="replying = !replying" class="text-xs font-black text-gray-400 hover:text-emerald-500 transition-colors">Reply</button>
                            </div>

                            {{-- Reply Input --}}
                            <div x-show="replying" class="mt-4 flex gap-3 pl-4 animate-modal-enter">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-black text-xs shrink-0 shadow-lg shadow-emerald-500/20">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 relative" x-data="{ isSubmittingReply: false }">
                                    <input 
                                        x-model="replyContent"
                                        type="text" 
                                        placeholder="Write a reply..." 
                                        class="w-full px-5 py-2.5 bg-gray-100 dark:bg-gray-800/50 border border-transparent focus:border-emerald-500 rounded-2xl text-xs focus:ring-4 focus:ring-emerald-500/10 transition-all dark:text-gray-200"
                                        @keydown.enter.prevent="
                                            if(!replyContent.trim() || isSubmittingReply) return;
                                            isSubmittingReply = true;
                                            fetch('{{ route('tenant.announcements.comment.store', $announcement) }}', {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                body: JSON.stringify({ content: replyContent, parent_id: {{ $comment->id }} })
                                            }).then(r => r.json()).then(d => { if(d.success) window.location.reload(); })
                                        "
                                    >
                                </div>
                            </div>

                            {{-- Replies List --}}
                            @if($comment->replies->count() > 0)
                                <div class="mt-4 space-y-4 pl-4 border-l-2 border-black/5">
                                    @foreach($comment->replies as $reply)
                                        <div class="flex gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-400 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                            </div>
                                            <div class="bg-gray-100 dark:bg-gray-800/60 rounded-3xl px-5 py-3 inline-block max-w-full border border-transparent dark:border-gray-700/30 shadow-sm">
                                                <div class="flex items-center gap-2 mb-0.5">
                                                    <span class="text-xs font-black text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                                                    <span class="text-[9px] text-gray-400 font-bold uppercase">{{ $reply->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed break-words">{{ $reply->content }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center space-y-2 opacity-30">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <p class="text-xs font-bold uppercase tracking-widest">No comments yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Gallery Grid Modal --}}
    <template x-teleport="body">
        <div 
            x-show="galleryOpen" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/90"
            x-cloak
        >
            <div class="relative w-full max-w-4xl max-h-[90vh] bg-white dark:bg-gray-900 rounded-2xl overflow-hidden flex flex-col" @click.away="closeGallery()">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">All Photos & Videos</h3>
                    <button @click="closeGallery()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <template x-for="(item, index) in media" :key="index">
                            <div 
                                class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer group"
                                @click="openViewer(index)"
                            >
                                <template x-if="item.type === 'image'">
                                    <img :src="item.url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
                                </template>
                                <template x-if="item.type === 'video'">
                                    <div class="w-full h-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-10 w-10 text-gray-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347c-.75.412-1.667-.13-1.667-.986V5.653Z" />
                                        </svg>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Viewer Modal --}}
    <template x-teleport="body">
        <div 
            x-show="viewerOpen" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-black/95 select-none"
            @keydown.window.escape="closeViewer()"
            @keydown.window.arrow-right="next()"
            @keydown.window.arrow-left="prev()"
            x-cloak
        >
            <button @click="closeViewer()" class="absolute top-6 right-6 z-[80] p-2 bg-white/10 hover:bg-white/20 text-white rounded-full transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <template x-if="media.length > 1">
                <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-4 sm:px-10 z-[75] pointer-events-none">
                    <button @click.stop="prev()" class="p-3 bg-white/10 hover:bg-white/20 text-white rounded-full transition-colors pointer-events-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <button @click.stop="next()" class="p-3 bg-white/10 hover:bg-white/20 text-white rounded-full transition-colors pointer-events-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>
            </template>

            <div class="w-full h-full flex items-center justify-center p-4" @click="closeViewer()">
                <template x-for="(item, index) in media" :key="index">
                    <div 
                        x-show="currentIndex === index" 
                        class="w-full h-full flex items-center justify-center"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="scale-95 opacity-0"
                        x-transition:enter-end="scale-100 opacity-100"
                        @click.stop
                    >
                        <template x-if="item.type === 'image'">
                            <img :src="item.url" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" />
                        </template>
                        <template x-if="item.type === 'video'">
                            <video controls class="max-w-full max-h-full rounded-lg shadow-2xl" autoplay>
                                <source :src="item.url" type="video/mp4">
                            </video>
                        </template>
                    </div>
                </template>
            </div>

            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/70 text-sm font-medium">
                <span x-text="currentIndex + 1"></span> / <span x-text="media.length"></span>
            </div>
        </div>
    </template>
</div>
