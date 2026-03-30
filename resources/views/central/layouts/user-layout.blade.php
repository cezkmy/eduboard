<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EduBoard - School Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #2c7a6e;
            --primary-dark: #1e5a50;
            --sidebar-bg: #1a2634;
            --sidebar-hover: #2c3e50;
            --border-color: #e9ecef;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            overflow-x: hidden;
        }

        /* User Wrapper */
        .user-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand i {
            font-size: 1.75rem;
            color: var(--primary);
        }

        .sidebar-brand span {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .sidebar-brand small {
            font-size: 0.7rem;
            color: var(--primary);
            margin-left: auto;
            background: rgba(44, 122, 110, 0.2);
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
        }

        .sidebar-nav {
            padding: 1.25rem 1rem;
            flex: 1;
        }

        .sidebar-nav .nav-item {
            margin-bottom: 0.25rem;
        }

        .sidebar-nav .nav-link {
            color: #a0aec0;
            padding: 0.7rem 1rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .sidebar-nav .nav-link:hover {
            background: var(--sidebar-hover);
            color: white;
        }

        .sidebar-nav .nav-link.active {
            background: var(--primary);
            color: white;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.2rem;
        }

        /* School Info - at the bottom */
        .school-info {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
        }

        .school-name {
            font-weight: 600;
            color: white;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .school-plan {
            font-size: 0.7rem;
            color: var(--primary);
            background: rgba(44, 122, 110, 0.2);
            padding: 0.2rem 0.75rem;
            border-radius: 20px;
            display: inline-block;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: #f8f9fa;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            padding: 0.85rem 2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .page-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }

        /* Top Bar Right Icons */
        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        /* Notifications Dropdown */
        .notifications-dropdown {
            position: relative;
        }

        .notifications-btn {
            background: none;
            border: none;
            color: #718096;
            font-size: 1.3rem;
            position: relative;
            cursor: pointer;
            padding: 0.5rem;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #dc3545;
            color: white;
            font-size: 0.7rem;
            padding: 0.15rem 0.4rem;
            border-radius: 20px;
            min-width: 18px;
            text-align: center;
        }

        .notifications-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 350px;
            display: none;
            z-index: 1000;
            margin-top: 0.5rem;
            border: 1px solid var(--border-color);
        }

        .notifications-menu.show {
            display: block;
        }

        .notifications-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notifications-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            align-items: start;
            gap: 1rem;
            padding: 1rem 1.5rem;
            text-decoration: none;
            border-bottom: 1px solid var(--border-color);
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item.unread {
            background: #f0f9f7;
        }

        .notification-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-text {
            color: #2d3748;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            line-height: 1.4;
        }

        .notification-time {
            color: #718096;
            font-size: 0.75rem;
        }

        .notifications-footer {
            padding: 1rem 1.5rem;
            text-align: center;
            border-top: 1px solid var(--border-color);
        }

        .notifications-footer a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
        }

        /* User Dropdown */
        .user-dropdown {
            position: relative;
        }

        .user-dropdown-btn {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .user-dropdown-btn:hover {
            background: #f8f9fa;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.85rem;
        }

        .user-role {
            font-size: 0.7rem;
            color: #718096;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: var(--primary);
            color: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            min-width: 200px;
            display: none;
            z-index: 1000;
            margin-top: 0.5rem;
            border: 1px solid var(--border-color);
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            padding: 0.7rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #2d3748;
            text-decoration: none;
            transition: all 0.2s;
            width: 100%;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
        }

        .dropdown-item i {
            font-size: 1rem;
            color: #718096;
        }

        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 0.5rem 0;
        }

        /* Content Area */
        .content-area {
            padding: 1.5rem 2rem;
        }

        /* Cards */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Badges */
        .badge {
            padding: 0.35rem 0.7rem;
            font-weight: 500;
            font-size: 0.7rem;
            border-radius: 20px;
        }

        .badge.bg-success {
            background: rgba(44, 122, 110, 0.1) !important;
            color: var(--primary) !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                left: -260px;
            }
            .main-content {
                margin-left: 0;
            }
            .content-area {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="user-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="bi bi-mortarboard"></i>
                <span>EduBoard</span>
                <small>School</small>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('central.user.dashboard') }}" class="nav-link {{ request()->routeIs('central.user.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('central.user.domain') }}" class="nav-link {{ request()->routeIs('central.user.domain') ? 'active' : '' }}">
                        <i class="bi bi-globe2"></i>
                        Domain Management
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('central.user.templates') }}" class="nav-link {{ request()->routeIs('central.user.templates') ? 'active' : '' }}">
                        <i class="bi bi-files"></i>
                        Templates
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('central.user.subscription') }}" class="nav-link {{ request()->routeIs('central.user.subscription') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i>
                        Subscription
                    </a>
                </div>
            </nav>

            <!-- School Info -->
            <div class="school-info">
                <div class="school-name">{{ auth()->user()->school_name ?? 'School Name' }}</div>
                <div class="school-plan">{{ auth()->user()->plan ?? 'Basic Plan' }}</div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1 class="page-title">
                    @hasSection('page-title')
                        @yield('page-title')
                    @else
                        @php
                            $routeName = request()->route()->getName();
                            $title = match(true) {
                                str_contains($routeName, 'dashboard') => 'Dashboard',
                                str_contains($routeName, 'domain') => 'Domain Management',
                                str_contains($routeName, 'templates') => 'Templates',
                                str_contains($routeName, 'subscription') => 'Subscription',
                                str_contains($routeName, 'settings') => 'Settings',
                                str_contains($routeName, 'profile') => 'Profile',
                                default => 'Dashboard'
                            };
                        @endphp
                        {{ $title }}
                    @endif
                </h1>
                
                <div class="top-bar-right">
                    <!-- Notifications Dropdown -->
                    <div class="notifications-dropdown">
                        <button class="notifications-btn" onclick="toggleNotifications()">
                            <i class="bi bi-bell"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="notification-badge">{{ auth()->user()->unreadNotifications->count() }}</span>
                            @endif
                        </button>
                        
                        <div class="notifications-menu" id="notificationsMenu">
                            <div class="notifications-header">
                                <h6 class="mb-0">Notifications</h6>
                                <span class="text-success">{{ auth()->user()->unreadNotifications->count() }} new</span>
                            </div>
                            
                            <div class="notifications-list">
                                @forelse(auth()->user()->unreadNotifications as $notification)
                                    <a href="#" class="notification-item unread">
                                        <div class="notification-icon bg-primary bg-opacity-10">
                                            @if(isset($notification->data['icon']) && $notification->data['icon'] == 'school')
                                                <i class="bi bi-bank text-primary"></i>
                                            @elseif(isset($notification->data['icon']) && $notification->data['icon'] == 'upgrade')
                                                <i class="bi bi-arrow-up-circle text-primary"></i>
                                            @else
                                                <i class="bi bi-megaphone text-primary"></i>
                                            @endif
                                        </div>
                                        <div class="notification-content">
                                            <p class="notification-text">{{ $notification->data['title'] ?? 'New Notification' }}<br/><small>{{ $notification->data['desc'] ?? '' }}</small></p>
                                            <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-3 text-center text-muted border-bottom">
                                        No new notifications.
                                    </div>
                                @endforelse
                            </div>
                            
                            @if(auth()->user()->unreadNotifications->count() > 0)
                            <div class="notifications-footer">
                                <a href="{{ route('central.notifications.read') }}">Mark All As Read</a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn" onclick="toggleDropdown()">
                            <div class="user-info">
                                <div class="user-name">{{ auth()->user()->name ?? 'User' }}</div>
                                <div class="user-role">{{ auth()->user()->role ?? 'School' }}</div>
                            </div>
                            <div class="user-avatar overflow-hidden d-flex align-items-center justify-content-center">
                                @if(auth()->user()->profile_photo)
                                    <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="Avatar" class="w-100 h-100 object-fit-cover">
                                @else
                                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                @endif
                            </div>
                        </button>
                        
                        <div class="dropdown-menu" id="userDropdown">
                            <a href="{{ route('central.user.profile') }}" class="dropdown-item">
                                <i class="bi bi-person"></i>
                                My Profile
                            </a>
                            <a href="{{ route('central.user.settings') }}" class="dropdown-item">
                                <i class="bi bi-gear"></i>
                                Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Dropdown Script -->
    <script>
        function toggleDropdown() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        function toggleNotifications() {
            document.getElementById('notificationsMenu').classList.toggle('show');
        }

        window.onclick = function(event) {
            if (!event.target.matches('.user-dropdown-btn') && !event.target.closest('.user-dropdown-btn')) {
                var dropdowns = document.getElementsByClassName('dropdown-menu');
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
            
            if (!event.target.matches('.notifications-btn') && !event.target.closest('.notifications-btn')) {
                var notifications = document.getElementById('notificationsMenu');
                if (notifications && notifications.classList.contains('show')) {
                    notifications.classList.remove('show');
                }
            }
        }
    </script>
    
    @stack('scripts')
</body>
</html>



