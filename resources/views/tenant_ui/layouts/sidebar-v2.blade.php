{{-- V2 Modern Sidebar Design --}}
<aside class="w-72 bg-gray-900 border-r border-gray-800 text-gray-400 h-screen flex flex-col shadow-2xl transition-all duration-300 relative z-20">
    
    {{-- Decorative Header Background --}}
    <div class="absolute top-0 left-0 w-full h-40 bg-gradient-to-br from-teal-900/40 via-teal-900/10 to-transparent pointer-events-none"></div>

    <div class="px-8 pt-10 pb-6 relative z-10 flex items-center justify-between">
        <a href="{{ route('tenant.admin.dashboard') }}" class="group flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center group-hover:bg-teal-500 group-hover:text-white transition-all shadow-[0_0_15px_rgba(20,184,166,0.3)]">
                <svg fill="currentColor" viewBox="0 0 24 24" class="w-6 h-6 transform group-hover:rotate-12 transition-transform">
                    <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z" />
                </svg>
            </div>
            <div>
                <h1 class="font-black text-xl text-white tracking-tight">{{ tenant('school_name') ?? 'Buksu' }}</h1>
                <p class="text-[10px] font-bold text-teal-500 uppercase tracking-widest leading-none">EduBoard V2</p>
            </div>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-8 sidebar-hide-scrollbar relative z-10">
        @php
            $tenant = tenant();
            $hasCategories = $tenant && $tenant->hasFeature('categories');
            $hasReports = $tenant && $tenant->hasFeature('reports');
            $hasTemplates = $tenant && $tenant->hasFeature('pre_built_templates');
            $isBasic = !$tenant || $tenant->plan === 'Basic';

            $isDeactivated = $tenant && $tenant->status === 'Deactivated';
            $expiresAt = $tenant && $tenant->expires_at ? \Carbon\Carbon::parse($tenant->expires_at) : null;
            $isExpired = $expiresAt && $expiresAt->isPast();
            $isLocked = $isDeactivated || $isExpired;
        @endphp

        @if(auth()->user()->role === 'admin')
            <div class="space-y-2">
                <p class="px-4 text-[11px] font-black text-gray-500 uppercase tracking-widest mb-3">Primary Tools</p>
                
                <a href="{{ $isLocked ? '#' : route('tenant.admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.dashboard') ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/25' : 'hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zm13.5 0a2.25 2.25 0 012.25 2.25v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zm-13.5 9.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zm13.5 0a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                    <span class="font-bold text-sm">Dashboard</span>
                </a>

                <a href="{{ $isLocked ? '#' : route('tenant.admin.announcements') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.announcements') ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/25' : 'hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                    <span class="font-bold text-sm">Announcements</span>
                </a>

                <a href="{{ $isLocked ? '#' : route('tenant.admin.my-announcements') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.my-announcements') ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/25' : 'hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8"></path><path stroke-linecap="round" stroke-linejoin="round" d="M8 13h6"></path></svg>
                    <span class="font-bold text-sm">My Feed</span>
                </a>
            </div>

            <div class="space-y-2">
                <p class="px-4 text-[11px] font-black text-gray-500 uppercase tracking-widest mb-3">Management</p>
                
                <a href="{{ $isLocked ? '#' : route('tenant.admin.users') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.users') ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/25' : 'hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                    <span class="font-bold text-sm">Users & Directory</span>
                </a>

                @if($hasTemplates)
                <a href="{{ $isLocked ? '#' : route('tenant.admin.templates') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.templates') ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/25' : 'hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    <span class="font-bold text-sm">Templates Lib</span>
                </a>
                @endif
                
                @if($hasReports)
                <a href="{{ $isLocked ? '#' : route('tenant.admin.reports') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.reports') ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/25' : 'hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <span class="font-bold text-sm">Reporting</span>
                </a>
                @endif
            </div>

            <div class="space-y-2 mt-auto pt-8">
                <a href="{{ route('tenant.admin.subscription') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.subscription') ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/25' : 'bg-gray-800 hover:bg-gray-700 hover:text-white' }}">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                    <span class="font-bold text-sm text-gray-200">Subscription Plan</span>
                </a>
            </div>
        @endif
    </nav>

    <div class="p-6 mt-auto border-t border-gray-800 bg-gray-950">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-teal-500 to-indigo-500 shadow-md flex items-center justify-center text-white font-black">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 overflow-hidden">
                <p class="font-bold text-sm text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role) }}</p>
            </div>
        </div>
    </div>
</aside>
<style>
.sidebar-hide-scrollbar::-webkit-scrollbar { display: none; }
.sidebar-hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
