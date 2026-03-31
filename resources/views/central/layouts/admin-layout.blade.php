<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EduBoard - Admin Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Theme Setup Script -->
    <script>
        const getPreferredTheme = () => {
            const storedTheme = localStorage.getItem('central-theme');
            if (storedTheme) { return storedTheme; }
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        };
        document.documentElement.setAttribute('data-bs-theme', getPreferredTheme());
    </script>

    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #2c7a6e;
            --primary-dark: #1e5a50;
            --sidebar-bg: #1a2634;
            --sidebar-hover: #2c3e50;
            --border-color: var(--bs-border-color);
        }

        [data-bs-theme="dark"] {
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
            overflow-x: hidden;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0,0,0,0.03);
            transition: background-color 0.2s ease;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand i { font-size: 1.75rem; color: var(--primary); }
        .sidebar-brand span { font-size: 1.1rem; font-weight: 600; letter-spacing: 0.3px; }

        .sidebar-nav { padding: 1.25rem 1rem; }
        .sidebar-nav .nav-item { margin-bottom: 0.25rem; }

        .sidebar-nav .nav-link {
            color: #a0aec0;
            padding: 0.7rem 1rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .sidebar-nav .nav-link:hover { background: var(--sidebar-hover); color: white; }
        .sidebar-nav .nav-link.active { background: var(--primary); color: white; }
        .sidebar-nav .nav-link i { font-size: 1.2rem; }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: var(--bs-body-bg);
        }

        .top-bar {
            background: var(--bs-body-bg);
            padding: 0.85rem 2rem;
            border-bottom: 1px solid var(--bs-border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .page-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--bs-heading-color);
            margin: 0;
        }

        .user-dropdown-btn {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.4rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .user-dropdown-btn:hover { background: var(--bs-secondary-bg); }
        .user-info { text-align: right; }
        .user-name { font-weight: 600; color: var(--bs-body-color); font-size: 0.9rem; }
        .user-role { font-size: 0.75rem; color: var(--bs-secondary-color); }
        
        .user-avatar {
            width: 36px; height: 36px;
            background: var(--primary);
            color: white; border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 0.9rem;
        }

        .dropdown-menu {
            position: absolute; right: 0; top: 100%;
            background: var(--bs-body-bg); border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); min-width: 200px;
            display: none; z-index: 1000; margin-top: 0.5rem;
            border: 1px solid var(--bs-border-color); overflow: hidden;
        }

        .dropdown-menu.show { display: block; }
        .dropdown-item {
            padding: 0.7rem 1rem; display: flex; align-items: center; gap: 0.75rem;
            color: var(--bs-body-color); text-decoration: none; transition: all 0.2s;
            width: 100%; border: none; background: none; cursor: pointer; font-size: 0.9rem;
        }
        .dropdown-item:hover { background: var(--bs-secondary-bg); }
        .dropdown-item i { font-size: 1rem; color: var(--bs-secondary-color); }
        .dropdown-divider { height: 1px; background: var(--bs-border-color); margin: 0.25rem 0; }

        .content-area { padding: 1.5rem 2rem; }

        .card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color); border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .card-header {
            background: var(--bs-body-bg);
            border-bottom: 1px solid var(--bs-border-color); padding: 1rem 1.5rem;
        }
        .card-body { padding: 1.5rem; }

        .table { margin: 0; color: var(--bs-body-color); }
        .table thead th {
            border-bottom: 1px solid var(--bs-border-color);
            color: var(--bs-secondary-color); font-weight: 500; font-size: 0.85rem;
            text-transform: uppercase; letter-spacing: 0.3px; padding: 0.75rem 1rem;
        }
        .table tbody td {
            padding: 1rem; border-bottom: 1px solid var(--bs-border-color);
            font-size: 0.9rem; vertical-align: middle;
        }
        .table tbody tr:last-child td { border-bottom: none; }

        .badge { padding: 0.4rem 0.75rem; font-weight: 500; font-size: 0.75rem; border-radius: 20px; }
        .badge.bg-success { background: rgba(44, 122, 110, 0.1) !important; color: var(--primary) !important; }
        .badge.bg-warning { background: rgba(244, 162, 97, 0.1) !important; color: #f4a261 !important; }

        .btn-success { background: var(--primary); border: none; padding: 0.5rem 1rem; font-size: 0.9rem; border-radius: 6px; color: white;}
        .btn-success:hover { background: var(--primary-dark); }
        .btn-outline-secondary {
            border: 1px solid var(--bs-border-color); color: var(--bs-body-color);
            padding: 0.5rem 1rem; font-size: 0.9rem; border-radius: 6px;
        }
        .btn-outline-secondary:hover { background: var(--bs-secondary-bg); border-color: var(--primary); color: var(--primary); }

        @media (max-width: 768px) {
            .sidebar { width: 0; left: -260px; }
            .main-content { margin-left: 0; }
            .content-area { padding: 1rem; }
            .top-bar { padding: 0.85rem 1rem; }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="bi bi-mortarboard"></i>
                <span>EduBoard</span>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="{{ route('central.admin.dashboard') }}" class="nav-link {{ request()->routeIs('central.admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('central.admin.tenants') }}" class="nav-link {{ request()->routeIs('central.admin.tenants') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        Tenants
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('central.admin.plans') }}" class="nav-link {{ request()->routeIs('central.admin.plans') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i>
                        Plans
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('central.admin.payments') }}" class="nav-link {{ request()->routeIs('central.admin.payments') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i>
                        Payments
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('central.admin.reports') }}" class="nav-link {{ request()->routeIs('central.admin.reports') ? 'active' : '' }}">
                        <i class="bi bi-graph-up"></i>
                        Reports
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('central.admin.templates') }}" class="nav-link {{ request()->routeIs('central.admin.templates') ? 'active' : '' }}">
                        <i class="bi bi-file-text"></i>
                        Templates
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar - Permanent for all admin pages -->
            <div class="top-bar">
                <h1 class="page-title">
                    @hasSection('page-title')
                        @yield('page-title')
                    @else
                        {{ ucfirst(str_replace('central.admin.', '', request()->route()->getName() ?? 'Dashboard')) }}
                    @endif
                </h1>
                
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <!-- Theme Toggle -->
                    <button class="theme-toggle-btn" id="centralThemeToggle" title="Toggle theme" style="background:none; border:none; padding: 0.5rem; font-size:1.3rem; cursor:pointer; color: var(--bs-body-color);">
                        <i class="bi bi-moon-fill dark-icon" style="display: none;"></i>
                        <i class="bi bi-sun-fill light-icon"></i>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div class="notifications-dropdown">
                        <button style="background:none; border:none; padding: 0.5rem; font-size:1.3rem; relative; cursor:pointer;" onclick="toggleNotifications()">
                            <i class="bi bi-bell"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="notification-badge" style="position: absolute; top:0; right:0; background:#dc3545; color:white; font-size: 0.7rem; padding: 0.15rem 0.4rem; border-radius: 20px;">{{ auth()->user()->unreadNotifications->count() }}</span>
                            @endif
                        </button>
                        
                        <div class="dropdown-menu" id="notificationsMenu" style="width: 350px;">
                            <div class="p-3 border-bottom d-flex justify-content-between">
                                <h6 class="mb-0">Notifications</h6>
                                <span class="text-success">{{ auth()->user()->unreadNotifications->count() }} new</span>
                            </div>
                            
                            <div style="max-height: 300px; overflow-y: auto;">
                                @forelse(auth()->user()->unreadNotifications as $notification)
                                    @php
                                        $link = '#';
                                        if(isset($notification->data['icon'])) {
                                            if($notification->data['icon'] == 'school') $link = route('central.admin.tenants');
                                            elseif($notification->data['icon'] == 'upgrade') $link = route('central.admin.tenants');
                                        }
                                    @endphp
                                    <a href="{{ $link }}" class="dropdown-item d-flex align-items-start gap-3 p-3 border-bottom @if($notification->unread()) bg-light @endif" style="font-size: 0.85rem; white-space: normal;">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2">
                                            @if(isset($notification->data['icon']) && $notification->data['icon'] == 'school')
                                                <i class="bi bi-bank"></i>
                                            @elseif(isset($notification->data['icon']) && $notification->data['icon'] == 'upgrade')
                                                <i class="bi bi-arrow-up-circle"></i>
                                            @else
                                                <i class="bi bi-megaphone"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="mb-1 fw-bold text-dark">{{ $notification->data['title'] ?? 'New Notification' }}</p>
                                            <p class="mb-1 text-muted">{{ $notification->data['desc'] ?? '' }}</p>
                                            <span class="text-muted" style="font-size: 0.75rem;">{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-3 text-center text-muted">
                                        No new notifications.
                                    </div>
                                @endforelse
                            </div>
                            
                            @if(auth()->user()->unreadNotifications->count() > 0)
                            <div class="p-2 text-center border-top">
                                <a href="{{ route('central.notifications.read') }}" class="text-primary text-decoration-none">Mark All As Read</a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="user-dropdown">
                        <button class="user-dropdown-btn" onclick="toggleDropdown()">
                            <div class="user-info">
                                <div class="user-name">{{ auth()->user()->name }}</div>
                                <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                            </div>
                            <div class="user-avatar">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </button>
                        
                        <div class="dropdown-menu" id="userDropdown">
                            <a href="{{ route('central.admin.profile') }}" class="dropdown-item">
                                <i class="bi bi-person"></i>
                                My Profile
                            </a>
                            <a href="{{ route('central.admin.settings') }}" class="dropdown-item">
                                <i class="bi bi-gear"></i>
                                Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
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
            
            <!-- Content Area with normal padding -->
            <div class="content-area position-relative">
                
                @if(session('success'))
                <div class="position-absolute top-0 end-0 p-4" style="z-index: 1060; margin-top: -1rem;">
                    <div class="toast align-items-center text-bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" id="successToast">
                        <div class="d-flex">
                            <div class="toast-body fs-6 py-3">
                                <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> 
                                <span class="align-middle">{{ session('success') }}</span>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
                <!-- Inline script to show the toast immediately without waiting for stack -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var toastEl = document.getElementById('successToast');
                        if (toastEl) {
                            var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
                            toast.show();
                        }
                    });
                </script>
                @endif
                
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

        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('centralThemeToggle');
            if (toggleBtn) {
                const lightIcon = toggleBtn.querySelector('.light-icon');
                const darkIcon = toggleBtn.querySelector('.dark-icon');

                const refreshToggleState = () => {
                    if (document.documentElement.getAttribute('data-bs-theme') === 'dark') {
                        lightIcon.style.display = 'none';
                        darkIcon.style.display = 'inline-block';
                    } else {
                        lightIcon.style.display = 'inline-block';
                        darkIcon.style.display = 'none';
                    }
                };
                refreshToggleState();

                toggleBtn.addEventListener('click', () => {
                    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-bs-theme', newTheme);
                    localStorage.setItem('central-theme', newTheme);
                    refreshToggleState();
                });
            }
        });

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.closest('.user-dropdown-btn') && !event.target.closest('#userDropdown')) {
                var userDropdown = document.getElementById('userDropdown');
                if (userDropdown && userDropdown.classList.contains('show')) {
                    userDropdown.classList.remove('show');
                }
            }
            if (!event.target.closest('.notifications-dropdown')) {
                var notifDropdown = document.getElementById('notificationsMenu');
                if (notifDropdown && notifDropdown.classList.contains('show')) {
                    notifDropdown.classList.remove('show');
                }
            }
        }
    </script>
    
    @stack('scripts')
</body>
</html>



