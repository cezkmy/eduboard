<aside class="sidebar">
    <div class="sidebar-brand">
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
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-label">Main</div>

        <a href="{{ route('tenant.student.page') }}" class="sidebar-item {{ request()->routeIs('tenant.student.page') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>
            Announcements
        </a>

        <div class="sidebar-label">Account</div>

        <a href="{{ route('tenant.profile.edit') }}" class="sidebar-item {{ request()->routeIs('tenant.profile.edit') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
            </svg>
            My Profile
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                <div class="sidebar-user-role">Student</div>
            </div>
        </div>
    </div>
</aside>










