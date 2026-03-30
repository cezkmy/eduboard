<x-app-layout>
    <x-slot name="title">Announcement Templates - EduBoard Admin</x-slot>

    <div class="admin-content">
        {{-- Page Header --}}
        <div class="content-header flex items-center justify-between mb-8">
            <div>
                <h1 class="content-title">Announcement Templates</h1>
                <p class="content-subtitle">Choose a pre-designed layout for your next announcement</p>
            </div>
        </div>

        {{-- Templates Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @php
                $templateLimit = tenant()->getLimit('templates');
                $isUnlimited = $templateLimit === -1;
            @endphp
            @for ($i = 1; $i <= 10; $i++)
                @php
                    $isLocked = !$isUnlimited && $i > $templateLimit;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-3xl border {{ $isLocked ? 'border-gray-200 dark:border-gray-800 opacity-70' : 'border-gray-100 dark:border-gray-700' }} overflow-hidden shadow-sm hover:shadow-xl transition-all group flex flex-col relative">
                    @if($isLocked)
                    <div class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-[2px] z-10 flex flex-col items-center justify-center">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-5 py-2.5 rounded-xl text-xs font-black shadow-xl flex items-center gap-2 transform group-hover:scale-105 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Unlock with Ultimate
                        </div>
                    </div>
                    @endif
                    <div class="aspect-video bg-gray-50 dark:bg-gray-900 relative overflow-hidden flex items-center justify-center border-b {{ $isLocked ? 'border-gray-200 dark:border-gray-800' : 'border-gray-100 dark:border-gray-700' }}">
                        {{-- Template Preview Placeholder --}}
                        <div class="text-center p-6">
                            <div class="w-16 h-16 bg-teal-50 dark:bg-teal-900/20 rounded-2xl flex items-center justify-center text-teal-500 mx-auto mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                </svg>
                            </div>
                            <h4 class="font-bold text-gray-900 dark:text-gray-100">Template {{ $i }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Professional layout for school updates</p>
                        </div>
                    </div>
                    <div class="p-6 flex items-center justify-between mt-auto {{ $isLocked ? 'pointer-events-none' : '' }}">
                        <div>
                            <span class="px-2 py-1 bg-teal-50 text-teal-600 text-[10px] font-bold uppercase rounded-md tracking-wider">Ready to use</span>
                        </div>
                        <button class="px-4 py-2 {{ $isLocked ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 hover:opacity-90' }} rounded-xl text-xs font-bold transition-all" {{ $isLocked ? 'disabled' : '' }}>
                            Use Template
                        </button>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</x-app-layout>
