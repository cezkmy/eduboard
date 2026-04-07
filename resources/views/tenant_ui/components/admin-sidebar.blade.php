<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('tenant.admin.dashboard') }}" class="sidebar-brand-link">
            <div class="sidebar-brand-icon">
                @if(!empty($appearance['customLogo']))
                    <img src="{{ asset('storage/' . $appearance['customLogo']) }}" alt="EduBoard Logo" style="width: 100%; height: 100%; object-fit: contain;">
                @else
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 3L2 12h3v8h14v-8h3L12 3zm0 4.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5zm3 10.5H9v-4h6v4z"/>
                    </svg>
                @endif
            </div>
            <span class="sidebar-brand-name">EduBoard</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        @php
            $tenant = tenant();
            $hasCategories = $tenant && $tenant->hasFeature('categories');
            $hasReports = $tenant && $tenant->hasFeature('reports');
            $hasTemplates = $tenant && $tenant->hasFeature('pre_built_templates');

            $isDeactivated = $tenant->status === 'Deactivated';
            $expiresAt = $tenant->expires_at ? \Carbon\Carbon::parse($tenant->expires_at) : null;
            $isExpired = $expiresAt && $expiresAt->isPast();
            
            $isLocked = $isDeactivated || $isExpired;
        @endphp
        <div class="sidebar-label">Main</div>

        <a href="{{ $isLocked ? '#' : route('tenant.admin.dashboard') }}" 
           class="sidebar-item {{ request()->routeIs('tenant.admin.dashboard') ? 'active' : '' }}"
           @if($isLocked) style="opacity: 0.5; pointer-events: none;" tabindex="-1" @endif>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            Dashboard
            @if($isLocked)
                <svg class="w-4 h-4 ml-auto" style="margin-left: auto;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
            @endif
        </a>

        <a href="{{ $isLocked ? '#' : route('tenant.admin.announcements') }}" 
           class="sidebar-item {{ request()->routeIs('tenant.admin.announcements') ? 'active' : '' }}"
           @if($isLocked) style="opacity: 0.5; pointer-events: none;" tabindex="-1" @endif>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>
            Announcements
            @if($isLocked)
                <svg class="w-4 h-4 ml-auto" style="margin-left: auto;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
            @endif
        </a>

        <a href="{{ $isLocked ? '#' : route('tenant.admin.my-announcements') }}" 
           class="sidebar-item {{ request()->routeIs('tenant.admin.my-announcements') ? 'active' : '' }}"
           @if($isLocked) style="opacity: 0.5; pointer-events: none;" tabindex="-1" @endif>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 9h8" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 13h6" />
            </svg>
            My Announcements
            @if($isLocked)
                <svg class="w-4 h-4 ml-auto" style="margin-left: auto;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
            @endif
        </a>

        @if($hasCategories)
        <a href="{{ $isLocked ? '#' : route('tenant.admin.categories') }}" 
           class="sidebar-item {{ request()->routeIs('tenant.admin.categories') ? 'active' : '' }}"
           @if($isLocked) style="opacity: 0.5; pointer-events: none;" aria-disabled="true" tabindex="-1" @endif>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
            </svg>
            Categories
            @if($isLocked)
                <svg class="w-4 h-4 ml-auto" style="margin-left: auto;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
            @endif
        </a>
        @endif

        @if($hasTemplates)
        <a href="{{ $isLocked ? '#' : route('tenant.admin.templates') }}" 
           class="sidebar-item {{ request()->routeIs('tenant.admin.templates') ? 'active' : '' }}"
           @if($isLocked) style="opacity: 0.5; pointer-events: none;" aria-disabled="true" tabindex="-1" @endif>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Templates
            @if($isLocked)
                <svg class="w-4 h-4 ml-auto" style="margin-left: auto;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
            @endif
        </a>
        @endif

        <div class="sidebar-label">Management</div>

        <a href="{{ $isLocked ? '#' : route('tenant.admin.users') }}" 
           class="sidebar-item {{ request()->routeIs('tenant.admin.users') ? 'active' : '' }}"
           @if($isLocked) style="opacity: 0.5; pointer-events: none;" tabindex="-1" @endif>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            Users
            @if($isLocked)
                <svg class="w-4 h-4 ml-auto" style="margin-left: auto;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
            @endif
        </a>


        @if($hasReports)
        <a href="{{ $isLocked ? '#' : route('tenant.admin.reports') }}" 
           class="sidebar-item {{ request()->routeIs('tenant.admin.reports') ? 'active' : '' }}"
           @if($isLocked) style="opacity: 0.5; pointer-events: none;" aria-disabled="true" tabindex="-1" @endif>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Reports
            @if($isLocked)
                <svg class="w-4 h-4 ml-auto" style="margin-left: auto;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
            @endif
        </a>
        @endif

        <div class="sidebar-label">System</div>

        <a href="{{ route('tenant.admin.subscription') }}" class="sidebar-item {{ request()->routeIs('tenant.admin.subscription') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Subscription
        </a>

        <a href="{{ $isLocked ? '#' : route('tenant.admin.settings') }}" 
           class="sidebar-item {{ request()->routeIs('tenant.admin.settings*') ? 'active' : '' }}"
           @if($isLocked) style="opacity: 0.5; pointer-events: none;" tabindex="-1" @endif>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings
            @if($isLocked)
                <svg class="w-4 h-4 ml-auto" style="margin-left: auto;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
            @endif
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user-wrapper">
            <div class="sidebar-user">
                <div class="sidebar-avatar" style="position: relative;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    <span class="status-indicator" style="
                        position: absolute;
                        bottom: -2px;
                        right: -2px;
                        width: 12px;
                        height: 12px;
                        background-color: #22c55e;
                        border: 2px solid var(--color-sidebar-bg, #1e293b);
                        border-radius: 50%;
                    "></span>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-user-role">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
            </div>
        </div>
    </div>
</aside>









