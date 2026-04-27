@if(tenant('system_version') === 'v2.0')
    @include('tenant_ui.layouts.sidebar-v2')
@else
<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('tenant.landing') }}" class="sidebar-brand-link">
            <div class="sidebar-brand-icon" style="overflow: hidden; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.18);">
                @if(tenant('logo'))
                    <img src="{{ tenant_asset(tenant('logo')) }}" style="width: 100%; height: 100%; object-fit: contain; padding: 2px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <svg style="display:none; width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                @else
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 24px; height: 24px;">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                @endif
            </div>
            <div class="sidebar-brand-name-wrapper">
                <span class="sidebar-brand-name">{{ tenant('school_short_name') ?? tenant('school_name') ?? 'Buksu' }}</span>
                <span class="sidebar-brand-sub">EDUBOARD</span>
            </div>
        </a>
    </div>

    <nav class="sidebar-nav">
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

        <div class="sidebar-label">Main</div>

        @if($user->hasPermission('page_admin_dashboard'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.dashboard') }}"
                class="sidebar-item {{ request()->routeIs('tenant.admin.dashboard') ? 'active' : '' }}" @if($isLocked)
                style="opacity: 0.5; pointer-events: none;" tabindex="-1" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                Dashboard
            </a>
        @endif

        @if($user->role === 'teacher' && $user->hasPermission('page_teacher_dashboard'))
            <a href="{{ route('tenant.teacher.dashboard') }}"
                class="sidebar-item {{ request()->routeIs('tenant.teacher.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                Interactions
            </a>
        @endif

        @if($user->hasPermission('page_admin_announcements'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.announcements') }}"
                class="sidebar-item {{ request()->routeIs('tenant.admin.announcements') ? 'active' : '' }}" @if($isLocked)
                style="opacity: 0.5; pointer-events: none;" tabindex="-1" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                Announcements
            </a>
        @elseif($user->hasPermission('page_teacher_announcements'))
            <a href="{{ route('tenant.teacher.announcements') }}"
                class="sidebar-item {{ request()->routeIs('tenant.teacher.announcements') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                Announcements
            </a>
        @elseif($user->hasPermission('page_student_studentpage'))
            <a href="{{ route('tenant.dashboard') }}"
                class="sidebar-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                Announcements
            </a>
        @endif

        @if($user->hasPermission('page_admin_my_announcements'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.my-announcements') }}"
                class="sidebar-item {{ request()->routeIs('tenant.admin.my-announcements') ? 'active' : '' }}"
                @if($isLocked) style="opacity: 0.5; pointer-events: none;" tabindex="-1" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 13h6" /></svg>
                My Announcements
            </a>
        @elseif($user->hasPermission('page_teacher_my_announcements'))
            <a href="{{ route('tenant.teacher.my-announcements') }}"
                class="sidebar-item {{ request()->routeIs('tenant.teacher.my-announcements') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 13h6" /></svg>
                My Announcements
            </a>
        @endif

        @if($hasCategories && $user->hasPermission('page_admin_categories'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.categories') }}"
                class="sidebar-item {{ request()->routeIs('tenant.admin.categories') ? 'active' : '' }}" @if($isLocked)
                style="opacity: 0.5; pointer-events: none;" aria-disabled="true" tabindex="-1" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                Categories
            </a>
        @endif

        @if($user->role !== 'teacher' && ($user->hasPermission('page_admin_users') || $user->hasPermission('page_admin_roles') || $hasTemplates || $hasReports))
            <div class="sidebar-label">Management</div>
        @endif

        @if($user->hasPermission('page_admin_users'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.users') }}"
                class="sidebar-item {{ request()->routeIs('tenant.admin.users') ? 'active' : '' }}" @if($isLocked)
                style="opacity: 0.5; pointer-events: none;" tabindex="-1" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                Users
            </a>
        @endif

        @if($hasTemplates && $user->hasPermission('page_admin_templates'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.templates') }}"
                class="sidebar-item {{ request()->routeIs('tenant.admin.templates') ? 'active' : '' }}" @if($isLocked)
                style="opacity: 0.5; pointer-events: none;" aria-disabled="true" tabindex="-1" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                Templates
            </a>
        @endif

        @if($hasReports && $user->hasPermission('page_admin_reports'))
            <a href="{{ $isLocked ? '#' : route('tenant.admin.reports') }}"
                class="sidebar-item {{ request()->routeIs('tenant.admin.reports') ? 'active' : '' }}" @if($isLocked)
                style="opacity: 0.5; pointer-events: none;" aria-disabled="true" tabindex="-1" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Reports
            </a>
        @endif

        @if($user->role !== 'teacher')
            <div class="sidebar-label">Account</div>

            {{-- Profile is now managed from the topbar account dropdown for admin/teacher views --}}
        @endif

        @if($user->role === 'admin')
            <div class="sidebar-label">System</div>

            @if($user->hasPermission('page_admin_settings'))
                @if($isBasic)
                <a href="{{ $isLocked ? '#' : route('tenant.admin.settings') }}"
                    class="sidebar-item {{ request()->routeIs('tenant.admin.settings') ? 'active' : '' }}" @if($isLocked)
                    style="opacity: 0.5; pointer-events: none;" tabindex="-1" @endif>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Settings
                </a>
                @endif
                
                <a href="{{ $isLocked ? '#' : route('tenant.admin.system.update') }}"
                    class="sidebar-item {{ request()->routeIs('tenant.admin.system.update') ? 'active' : '' }}" @if($isLocked)
                    style="opacity: 0.5; pointer-events: none;" tabindex="-1" @endif>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    System Update
                </a>
            @endif

            @if($user->role === 'admin')
            <a href="{{ route('tenant.admin.subscription') }}"
                class="sidebar-item {{ request()->routeIs('tenant.admin.subscription') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" /></svg>
                Subscription
            </a>
            @endif

        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar overflow-hidden" id="sidebar-avatar" style="background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                @if(auth()->user()->profile_photo)
                    <img src="{{ (function_exists('tenant_asset') && tenant()) ? tenant_asset(auth()->user()->profile_photo) : asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile" class="w-full h-full object-cover" onerror="this.style.display='none'; this.parentElement.innerText='{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}'">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="user-info">
                <span class="name">{{ auth()->user()->name }}</span>
                <span class="role">{{ ucfirst(auth()->user()->role) }}</span>
            </div>
        </div>
    </div>
</aside>
@endif