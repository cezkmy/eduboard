@props(['title' => 'Announcements'])

<header class="admin-topbar">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-teal-500 rounded-xl flex items-center justify-center text-white shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
            </svg>
        </div>
        <div class="flex flex-col leading-[0.9]">
            <span class="text-xl font-bold text-gray-900 tracking-tight">Buksu</span>
            <span class="text-[10px] font-bold text-teal-600 uppercase tracking-[0.2em]">Eduboard</span>
        </div>
    </div>
    
    <div class="topbar-actions">
        {{-- Dark mode --}}
        <button class="topbar-btn" title="Toggle theme">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>
        </button>

        {{-- Notifications --}}
        <div class="admin-dropdown" x-data="{ open: false }" @click.away="open = false">
            <button class="topbar-btn" @click="open = !open" title="Notifications">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span class="topbar-notif-dot"></span>
            </button>
            <div class="admin-dropdown-menu notif-menu" x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <div class="dropdown-header notif-header">
                    <span class="name">Notifications</span>
                    <span class="notif-count">3 new</span>
                </div>

                <div class="notif-item unread">
                    <div class="notif-icon" style="background: #f0fdf4; color: #10b981;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374l7.418-12.748c.866-1.5 3.032-1.5 3.898 0l1.29 2.223" />
                        </svg>
                    </div>
                    <div class="notif-content">
                        <div class="notif-title">New announcement</div>
                        <div class="notif-desc">Check out the latest school updates</div>
                        <div class="notif-time">2 hours ago</div>
                    </div>
                </div>

                <div class="notif-footer">
                    <a href="#">Mark all as read</a>
                    <a href="#">View all</a>
                </div>
            </div>
        </div>

        {{-- Account --}}
        <div class="admin-dropdown" x-data="{ open: false }" @click.away="open = false">
            <button class="topbar-btn user overflow-hidden" @click="open = !open" title="Account" style="padding: 0;">
                <div class="user-avatar" style="width: 100%; height: 100%; border-radius: 0; background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Profile" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
            </button>
            <div class="admin-dropdown-menu" x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <div class="dropdown-header">
                    <div class="name">{{ auth()->user()->name }}</div>
                    <div class="email">{{ auth()->user()->email }}</div>
                </div>
                <a href="{{ route('tenant.profile.edit') }}" class="dropdown-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 18px; height: 18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
                    </svg>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; color: #ef4444;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="width: 18px; height: 18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
