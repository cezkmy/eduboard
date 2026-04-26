{{-- V2 Modern Sidebar Design --}}
<aside data-sidebar-v2 class="w-72 border-r h-screen flex flex-col shadow-2xl transition-all duration-300 relative z-20" style="background: var(--sidebar-bg); border-color: var(--sidebar-border); color: var(--sidebar-text);">
    
    {{-- Decorative Header Background --}}
    <div class="absolute top-0 left-0 w-full h-40 pointer-events-none"
         style="background: linear-gradient(135deg, rgba(var(--accent-rgb), 0.32), rgba(var(--accent-rgb), 0.10), transparent);"></div>

    <div class="px-6 pt-8 pb-6 relative z-10 flex items-center justify-between">
        <a href="{{ route('tenant.landing') }}" class="group flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all overflow-hidden shrink-0"
                 style="background: rgba(255, 255, 255, 0.18); box-shadow: 0 0 15px rgba(var(--accent-rgb), 0.28);"
            >
                @if(tenant('logo'))
                    <img src="{{ tenant_asset(tenant('logo')) }}" 
                         class="w-full h-full object-contain p-1" 
                         alt="{{ tenant('school_short_name') ?? 'School' }}"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <svg style="display:none;" class="w-7 h-7" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                @else
                    <svg class="w-7 h-7 transform group-hover:rotate-12 transition-transform" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                @endif
            </div>
            <div>
                <h1 class="brand-title font-black text-lg tracking-tight leading-none" style="color: var(--sidebar-title);">{{ tenant('school_short_name') ?? tenant('school_name') ?? 'Buksu' }}</h1>
                <p class="brand-subtitle text-[10px] font-bold uppercase tracking-widest leading-none mt-1" style="color: var(--sidebar-title); opacity: 0.7;">EduBoard</p>
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
            $user = auth()->user();
        @endphp

        <div class="space-y-2">
            <p class="sidebar-section-label px-4 text-[10px] font-black uppercase tracking-widest mb-3" style="color: var(--sidebar-heading);">Primary Tools</p>
            
            @if($user->hasPermission('page_admin_dashboard'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.dashboard') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.admin.dashboard') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zm13.5 0a2.25 2.25 0 012.25 2.25v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zm-13.5 9.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zm13.5 0a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                <span class="font-bold text-xs">Admin Dashboard</span>
            </a>
            @endif

            @if($user->role === 'teacher' && $user->hasPermission('page_teacher_dashboard'))
            <a href="{{ route('tenant.teacher.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.teacher.dashboard') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.teacher.dashboard') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zm13.5 0a2.25 2.25 0 012.25 2.25v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zm-13.5 9.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zm13.5 0a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                <span class="font-bold text-xs">Interactions</span>
            </a>
            @endif

            @if($user->hasPermission('page_admin_announcements'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.announcements') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.announcements') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.admin.announcements') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                <span class="font-bold text-sm">Announcements</span>
            </a>
            @elseif($user->hasPermission('page_teacher_announcements'))
            <a href="{{ route('tenant.teacher.announcements') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.teacher.announcements') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.teacher.announcements') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                <span class="font-bold text-sm">Announcements</span>
            </a>
            @elseif($user->hasPermission('page_student_studentpage'))
            <a href="{{ route('tenant.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.dashboard') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.dashboard') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                <span class="font-bold text-sm">Announcements</span>
            </a>
            @endif

            @if($user->hasPermission('page_admin_my_announcements'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.my-announcements') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.my-announcements') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.admin.my-announcements') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8"></path><path stroke-linecap="round" stroke-linejoin="round" d="M8 13h6"></path></svg>
                <span class="font-bold text-sm">My Feed</span>
            </a>
            @elseif($user->hasPermission('page_teacher_my_announcements'))
            <a href="{{ route('tenant.teacher.my-announcements') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.teacher.my-announcements') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.teacher.my-announcements') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8"></path><path stroke-linecap="round" stroke-linejoin="round" d="M8 13h6"></path></svg>
                <span class="font-bold text-sm">My Feed</span>
            </a>
            @endif
        </div>

        @if($user->role !== 'teacher' && ($user->hasPermission('page_admin_users') || $user->hasPermission('page_admin_categories') || $hasTemplates || $hasReports))
        <div class="space-y-2">
            <p class="sidebar-section-label px-4 text-[11px] font-black uppercase tracking-widest mb-3" style="color: var(--sidebar-heading);">Management</p>
            
            @if($user->hasPermission('page_admin_users'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.users') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.users') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.admin.users') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                <span class="font-bold text-sm">Users & Directory</span>
            </a>
            @endif
            
            @if($user->hasPermission('page_admin_categories'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.categories') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.categories') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.admin.categories') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                <span class="font-bold text-sm">Categories</span>
            </a>
            @endif

            @if($hasTemplates && $user->hasPermission('page_admin_templates'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.templates') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.templates') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.admin.templates') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                <span class="font-bold text-sm">Templates Lib</span>
            </a>
            @endif
            
            @if($hasReports && $user->hasPermission('page_admin_reports'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.reports') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.reports') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.admin.reports') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <span class="font-bold text-sm">Reporting</span>
            </a>
            @endif
        </div>
        @if($user->role !== 'teacher')
        <div class="space-y-2">
            <p class="sidebar-section-label px-4 text-[11px] font-black uppercase tracking-widest mb-3" style="color: var(--sidebar-heading);">Account</p>
            {{-- Profile is now managed from the topbar account dropdown for admin/teacher views --}}
        </div>
        @endif

        @if($user->role === 'admin')
            <div class="space-y-2">
                <p class="sidebar-section-label px-4 text-[11px] font-black uppercase tracking-widest mb-3" style="color: var(--sidebar-heading);">System</p>
                
                @if($user->hasPermission('page_admin_settings'))
                <a href="{{ route('tenant.admin.system.update') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.system.update') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.admin.system.update') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    <span class="font-bold text-sm">System Update</span>
                </a>
                @endif

                @if($user->role === 'admin')
                <a href="{{ route('tenant.admin.subscription') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all {{ request()->routeIs('tenant.admin.subscription') ? 'text-white shadow-lg' : 'hover:bg-gray-800 hover:text-white' }}" style="{{ request()->routeIs('tenant.admin.subscription') ? 'background: var(--accent); box-shadow: 0 16px 28px rgba(var(--accent-rgb), 0.22);' : '' }}">
                    <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                    <span class="font-bold text-sm">Subscription</span>
                </a>
                @endif
            </div>
        @endif
    </nav>

    <div class="p-6 mt-auto border-t" style="border-color: var(--sidebar-border); background: rgba(0, 0, 0, 0.12);">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full shadow-md flex items-center justify-center text-white font-black overflow-hidden" id="sidebar-v2-avatar"
                 style="background: linear-gradient(135deg, var(--accent), rgba(var(--accent-rgb), 0.55));">
                @if(auth()->user()->profile_photo)
                    <img src="{{ (function_exists('tenant_asset') && tenant()) ? tenant_asset(auth()->user()->profile_photo) : asset('storage/' . auth()->user()->profile_photo) }}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML = '{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}'">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1 overflow-hidden">
                <p class="font-bold text-sm truncate" style="color: var(--sidebar-title);">{{ auth()->user()->name }}</p>
                <p class="text-xs" style="color: var(--sidebar-heading);">{{ ucfirst(auth()->user()->role) }}</p>
            </div>
        </div>
    </div>
</aside>
<style>
.sidebar-hide-scrollbar::-webkit-scrollbar { display: none; }
.sidebar-hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
aside[data-sidebar-v2] nav a:hover {
    background: var(--sidebar-hover) !important;
    color: #ffffff !important;
    border-left: 2px solid #ffffff !important;
    padding-left: calc(1rem - 2px) !important;
}
</style>
