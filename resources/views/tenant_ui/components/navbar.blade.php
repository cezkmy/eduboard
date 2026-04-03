<nav class="navbar" data-student-page="{{ route('tenant.student.page') }}">
    <div class="navbar-inner">
        <a href="#" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;" class="navbar-brand">
            @if(!empty($appearance['customLogo']))
                <img src="{{ asset('storage/' . $appearance['customLogo']) }}" alt="EduBoard Logo" class="brand-icon" style="object-fit: contain;">
            @else
                <img src="{{ asset('images/Logo.jpg') }}" alt="EduBoard Logo" class="brand-icon">
            @endif
            <span class="brand-name">EduBoard</span>
        </a>

        <div class="navbar-nav">
            <a href="{{ route('tenant.student.page') }}" class="nav-item {{ request()->routeIs('tenant.student.page') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                Announcements
            </a>
        </div>

        <div class="navbar-actions">
            {{-- Dark mode --}}
            <button class="action-btn" title="Toggle theme">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
            </button>

            {{-- Notifications --}}
            <div class="dropdown">
                <button class="action-btn" id="notifBtn" title="Notifications">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <span class="notif-dot"></span>
                </button>

                <div class="dropdown-menu notif-menu" id="notifMenu">
                    {{-- Header --}}
                    <div class="dropdown-header notif-header">
                        <span class="name">Notifications</span>
                        <span class="notif-count">{{ auth()->user()->unreadNotifications->count() }} new</span>
                    </div>

                    {{-- Notification Items --}}
                    <div style="max-height: 300px; overflow-y: auto;">
                        @forelse(auth()->user()->unreadNotifications as $notification)
                            <div class="notif-item unread">
                                <div class="notif-icon" style="background: rgba(var(--accent-rgb), 0.10); color: var(--accent);">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                    </svg>
                                </div>
                                <div class="notif-content">
                                    <div class="notif-title">{{ $notification->data['title'] ?? 'New Notification' }}</div>
                                    <div class="notif-desc">{{ $notification->data['desc'] ?? '' }}</div>
                                    <div class="notif-time">{{ $notification->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="notif-unread-dot"></div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs text-gray-500">
                                No new notifications
                            </div>
                        @endforelse
                    </div>

                    {{-- Footer --}}
                    <div class="notif-footer">
                        <a href="{{ route('tenant.notifications.read') }}" class="text-xs font-bold text-emerald-500 hover:text-emerald-600 transition-colors">Mark all as read</a>
                        <a href="#">View all</a>
                    </div>
                </div>
            </div>

            {{-- Account Dropdown --}}
            <div class="dropdown">
                <button class="action-btn user" id="accountBtn" title="Account">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </button>

                <div class="dropdown-menu" id="accountMenu">
                    <div class="dropdown-header">
                        <div class="name">{{ auth()->user()->name }}</div>
                        <div class="email">{{ auth()->user()->email }}</div>
                    </div>
                    <a href="{{ route('tenant.profile.edit') }}" class="dropdown-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
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
    </div>
</nav>









