@php
    $categoryColors = [
        'General' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'Academic' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'Events' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'Administrative' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
        'Emergency' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    ];
    $reactionEmojis = ['heart' => '❤️', 'like' => '👍', 'fire' => '🔥', 'sad' => '😢'];
    $mediaPaths = is_array($announcement->media_paths) ? $announcement->media_paths : json_decode($announcement->media_paths ?? '[]', true) ?? [];
    $mediaCount = count($mediaPaths);
    $isStacked = $mediaCount >= 4;
@endphp

<div 
    x-data="{ 
        galleryOpen: false, 
        viewerOpen: false, 
        currentIndex: 0, 
        media: {{ json_encode(array_map(fn($path) => [
            'url' => asset('storage/'.$path),
            'type' => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['mp4', 'mov', 'avi']) ? 'video' : 'image'
        ], $mediaPaths)) }},
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
    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm"
>
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-2">
                @if($announcement->is_pinned ?? false)
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5v16M3 12h16.5m-16.5 0L7.5 7.5m-4.5 4.5L7.5 16.5" />
                        </svg>
                        Pinned
                    </span>
                @endif
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium {{ $categoryColors[$announcement->category ?? 'General'] ?? $categoryColors['General'] }}">
                    {{ $announcement->category ?? 'General' }}
                </span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 leading-tight">{{ $announcement->title }}</h3>
        </div>
    </div>

    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500 dark:text-gray-400">
        <span class="inline-flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            {{ $announcement->postedBy?->name ?? 'System' }}
        </span>
        <span class="inline-flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0h18M12 12.75h.008v.008H12v-.008Z" />
            </svg>
            {{ $announcement->created_at->diffForHumans() }}
        </span>
    </div>

    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 leading-relaxed line-clamp-3">{{ Str::limit($announcement->content, 200) }}</p>

    {{-- Display uploaded media --}}
    @if($mediaPaths && $mediaCount > 0)
        @if($isStacked)
            {{-- Facebook-style grid layout for 4+ images --}}
            <div class="mt-3 grid grid-cols-2 gap-2">
                @foreach($mediaPaths as $index => $path)
                    @if($index < 4)
                        @php
                            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                            $isVideo = in_array($extension, ['mp4', 'mov', 'avi']);
                        @endphp
                        <div 
                            class="relative h-[250px] sm:h-[300px] rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer group"
                            @click="{{ $index === 3 && $mediaCount > 4 ? 'openGallery()' : 'openViewer('.$index.')' }}"
                        >
                            @if($isImage)
                                <img 
                                    src="{{ asset('storage/'.$path) }}" 
                                    alt="{{ $announcement->title }}" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                />
                            @elseif($isVideo)
                                <video 
                                    class="w-full h-full object-cover bg-gray-50 dark:bg-gray-900 group-hover:scale-105 transition-transform duration-200"
                                >
                                    <source src="{{ asset('storage/'.$path) }}" type="video/mp4">
                                </video>
                            @endif
                            
                            {{-- Overlay for 4th image only --}}
                            @if($index === 3 && $mediaCount > 4)
                                <div class="absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center">
                                    <span class="text-white text-2xl font-bold">+{{ $mediaCount - 4 }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            {{-- Grid layout for 1-3 images --}}
            <div class="mt-3 {{ $mediaCount === 1 ? 'space-y-2' : 'grid grid-cols-2 gap-2' }}">
                @foreach($mediaPaths as $index => $path)
                    @php
                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                        $isVideo = in_array($extension, ['mp4', 'mov', 'avi']);
                    @endphp
                    <div 
                        class="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer hover:opacity-90 transition-opacity {{ $mediaCount === 1 ? 'h-[400px] sm:h-[500px]' : 'h-[250px] sm:h-[350px]' }} {{ $mediaCount === 3 && $index === 0 ? 'col-span-2 sm:h-[450px]' : '' }}"
                        @click="openViewer({{ $index }})"
                    >
                        @if($isImage)
                            <img 
                                src="{{ asset('storage/'.$path) }}" 
                                alt="{{ $announcement->title }}" 
                                class="w-full h-full object-cover"
                            />
                        @elseif($isVideo)
                            <video class="w-full h-full object-cover bg-gray-50 dark:bg-gray-900">
                                <source src="{{ asset('storage/'.$path) }}" type="video/mp4">
                            </video>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    @if($showReactions ?? true)
        <div class="flex items-center gap-2 mt-4 flex-wrap">
            @foreach($reactionEmojis as $type => $emoji)
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border transition-all bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600"
                >
                    {{ $emoji }} {{ $announcement->{$type.'_count'} ?? 0 }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Full Gallery Grid Modal --}}
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

    {{-- Image/Video Viewer Modal --}}
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
            {{-- Close Button --}}
            <button @click="closeViewer()" class="absolute top-6 right-6 z-[80] p-2 bg-white/10 hover:bg-white/20 text-white rounded-full transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Navigation Buttons --}}
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

            {{-- Media Content --}}
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

            {{-- Counter --}}
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/70 text-sm font-medium">
                <span x-text="currentIndex + 1"></span> / <span x-text="media.length"></span>
            </div>
        </div>
    </template>
</div>










