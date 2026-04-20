<header class="admin-topbar">
    <div class="admin-topbar-left" style="display: flex; align-items: center; gap: 12px;">
        @if(tenant('logo') && auth()->check() && auth()->user()->role === 'student')
            <img src="{{ tenant_asset(tenant('logo')) }}" style="width: 32px; height: 32px; object-fit: contain; border-radius: 6px;" onerror="this.style.display='none';">
        @endif
        <h2 class="admin-topbar-title" style="margin: 0;">
            {{ $title ?? (tenant('school_short_name') ?? tenant('school_name') ?? 'EduBoard') }}
        </h2>
    </div>
    <div class="topbar-actions">

        {{-- Dark mode --}}
        <button class="topbar-btn" id="themeBtn" title="Toggle theme" onclick="toggleTheme()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
        </button>

        {{-- Notifications --}}
        <div class="admin-dropdown" x-data="{ open: false }" @click.away="open = false">
            <button class="topbar-btn" @click="open = !open" onclick="toggleDropdown('notif-dropdown')" title="Notifications">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 20px; height: 20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="topbar-notif-dot"></span>
                @endif
            </button>
            <div id="notif-dropdown" class="admin-dropdown-menu notif-menu" :class="{ 'show': open }">
                <div class="dropdown-header notif-header">
                    <span class="name">Notifications</span>
                    <span class="notif-count">{{ auth()->user()->unreadNotifications->count() }} new</span>
                </div>

                <div style="max-height: 300px; overflow-y: auto;">
                    @forelse(auth()->user()->unreadNotifications as $notification)
                        @php
                            $link = '#';
                            if(isset($notification->data['icon'])) {
                                if($notification->data['icon'] == 'system') $link = route('tenant.admin.settings');
                                elseif($notification->data['icon'] == 'user') $link = route('tenant.admin.users');
                                elseif($notification->data['icon'] == 'upgrade') $link = route('tenant.admin.subscription');
                            }
                        @endphp
                        <a href="{{ $link }}" class="notif-item unread" style="text-decoration: none;">
                            <div class="notif-icon" style="background: rgba(var(--accent-rgb), 0.10); color: var(--accent);">
                                @if(isset($notification->data['icon']) && $notification->data['icon'] == 'system')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                                @elseif(isset($notification->data['icon']) && $notification->data['icon'] == 'upgrade')
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>
                                @else
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" /></svg>
                                @endif
                            </div>
                            <div class="notif-content">
                                <div class="notif-title">{{ $notification->data['title'] ?? 'New Notification' }}</div>
                                <div class="notif-desc">{{ $notification->data['desc'] ?? '' }}</div>
                                <div class="notif-time">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                        </a>
                    @empty
                        <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                            No new notifications right now.
                        </div>
                    @endforelse
                </div>

                @if(auth()->user()->unreadNotifications->count() > 0)
                <div class="notif-footer" style="padding: 12px; display: flex; justify-content: space-between; border-top: 1px solid var(--border-color);">
                    <a href="{{ route('tenant.notifications.read') }}" style="font-size: 0.75rem; color: var(--color-primary); font-weight: 600;">Mark all as read</a>
                </div>
                @endif
            </div>
        </div>

        {{-- Account --}}
        <div class="admin-dropdown" x-data="{ open: false }" @click.away="open = false">
            <button class="topbar-btn user overflow-hidden" @click="open = !open" onclick="toggleDropdown('account-dropdown')" title="Account" style="padding: 0;">
                <div class="user-avatar w-full h-full flex items-center justify-center text-white font-bold" style="background: var(--accent);">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ (function_exists('tenant_asset') && tenant()) ? tenant_asset(auth()->user()->profile_photo) : asset('storage/' . auth()->user()->profile_photo) }}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML = '{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}'">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
            </button>
            <div id="account-dropdown" class="admin-dropdown-menu" :class="{ 'show': open }">
                <div class="dropdown-header">
                    <div class="name">{{ auth()->user()->name }}</div>
                    <div class="email">{{ auth()->user()->email }}</div>
                </div>
                <a href="{{ route('tenant.profile.edit') }}" class="dropdown-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
                    </svg>
                    Profile
                </a>

                @php
                    $tenant = tenant();
                    $isBasic = $tenant && $tenant->plan === 'Basic';
                @endphp
                @if(auth()->user()->role === 'admin' && !$isBasic)
                    <a href="{{ route('tenant.admin.settings') }}" class="dropdown-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Admin Settings
                    </a>
                @endif
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item logout" style="color: #ef4444; width: 100%; text-align: left; border: none; background: transparent; cursor: pointer;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>










