@props(['title' => 'Dashboard'])

<header class="admin-topbar" data-dashboard-url="{{ route('tenant.admin.dashboard') }}">
    <span class="topbar-title">{{ $title }}</span>
    <div class="topbar-actions">

        {{-- Dark mode --}}
        <button class="topbar-btn" id="themeBtn" title="Toggle theme">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
        </button>

        {{-- Notifications --}}
        <div class="admin-dropdown">
            <button class="topbar-btn" id="notifBtn" title="Notifications">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span class="topbar-notif-dot"></span>
            </button>
            <div class="admin-dropdown-menu notif-menu" id="notifMenu">
                <div class="dropdown-header notif-header">
                    <span class="name">Notifications</span>
                    <span class="notif-count">3 new</span>
                </div>

                <div class="notif-item unread" data-target="ann-card-1">
                    <div class="notif-icon emergency">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374l7.418-12.748c.866-1.5 3.032-1.5 3.898 0l1.29 2.223" />
                        </svg>
                    </div>
                    <div class="notif-content">
                        <div class="notif-title">New emergency announcement</div>
                        <div class="notif-desc">Dr. Santos posted a new alert</div>
                        <div class="notif-time">2 hours ago</div>
                    </div>
                    <div class="notif-unread-dot"></div>
                </div>

                <div class="notif-item unread" data-target="ann-card-2">
                    <div class="notif-icon events">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                    <div class="notif-content">
                        <div class="notif-title">Foundation Day Celebration</div>
                        <div class="notif-desc">Events Committee posted an announcement</div>
                        <div class="notif-time">5 hours ago</div>
                    </div>
                    <div class="notif-unread-dot"></div>
                </div>

                <div class="notif-item unread" data-target="ann-card-3">
                    <div class="notif-icon events">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75.125v-1.875m0 0a1.125 1.125 0 011.125-1.125H6m-3.75 0V7.875A1.125 1.125 0 013.375 6.75h17.25a1.125 1.125 0 011.125 1.125v9.75" />
                        </svg>
                    </div>
                    <div class="notif-content">
                        <div class="notif-title">Foundation Day Highlights Video</div>
                        <div class="notif-desc">Events Committee uploaded a new video</div>
                        <div class="notif-time">1 day ago</div>
                    </div>
                    <div class="notif-unread-dot"></div>
                </div>

                <div class="notif-footer">
                    <a href="#">Mark all as read</a>
                    <a href="#">View all</a>
                </div>
            </div>
        </div>

        {{-- Account --}}
        <div class="admin-dropdown">
            <button class="topbar-btn user overflow-hidden" id="accountBtn" title="Account" style="padding: 0;">
                <img src="{{ asset('images/download.jpg') }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
            </button>
            <div class="admin-dropdown-menu" id="accountMenu">
                <div class="dropdown-header">
                    <div class="name">{{ auth()->user()->name }}</div>
                    <div class="email">{{ auth()->user()->email }}</div>
                </div>
                <a href="{{ route('tenant.profile.edit') }}" class="dropdown-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
                    </svg>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item logout">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        Log Out
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>










