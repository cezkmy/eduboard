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

    <!-- Google Fonts for Modern Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #10b981; /* Premium Emerald */
            --primary-dark: #059669;
            --sidebar-bg: #1e5a50; /* Green sidebar */
            --sidebar-hover: #16423b;
            --sidebar-text: #e2e8f0;
            --sidebar-active-bg: rgba(255, 255, 255, 0.1);
            --sidebar-brand: #ffffff;
            --border-color: rgba(0, 0, 0, 0.08);
            --content-bg: #f8fafc;
            --card-bg: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        [data-bs-theme="dark"] {
            --primary: #10b981;
            --primary-dark: #34d399;
            --sidebar-bg: #0f172a; /* Slate 900 */
            --sidebar-hover: #1e293b;
            --sidebar-text: #cbd5e1;
            --sidebar-active-bg: rgba(16, 185, 129, 0.1);
            --sidebar-brand: #ffffff;
            --border-color: rgba(255, 255, 255, 0.08);
            --content-bg: #0f172a; /* Same as sidebar-bg */
            --card-bg: #1e293b; /* Distinct Card Bg */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.5);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--content-bg);
            color: var(--bs-body-color);
            overflow-x: hidden;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .admin-wrapper { display: flex; min-height: 100vh; }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            position: fixed; height: 100vh; z-index: 1000;
            transition: background-color 0.2s ease, border-color 0.2s ease;
            display: flex; flex-direction: column;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            display: flex; align-items: center; gap: 0.75rem;
            color: var(--sidebar-brand);
            height: 70px;
            border-bottom: 1px solid var(--border-color);
        }
        .sidebar-brand i { font-size: 1.5rem; color: var(--primary); }
        .sidebar-brand span { font-size: 1.15rem; font-weight: 700; letter-spacing: -0.02em; }

        .sidebar-nav { padding: 1rem 0.75rem; flex: 1; overflow-y: auto; }
        .sidebar-nav .nav-item { margin-bottom: 0.2rem; }

        .sidebar-nav .nav-link {
            color: var(--sidebar-text); padding: 0.65rem 1rem; border-radius: 8px;
            display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s;
            text-decoration: none; font-size: 0.95rem; font-weight: 500;
        }
        .sidebar-nav .nav-link:hover { background: var(--sidebar-hover); color: var(--sidebar-brand); }
        .sidebar-nav .nav-link.active { background: var(--sidebar-active-bg); color: var(--primary); font-weight: 600; }
        .sidebar-nav .nav-link.active i { color: var(--primary); }
        .sidebar-nav .nav-link i { font-size: 1.1rem; opacity: 0.8; }

        .sidebar-footer {
            padding: 1rem; border-top: 1px solid var(--border-color);
        }
        .sidebar-user {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem; 
            border-radius: 8px; transition: background 0.2s; text-decoration: none;
        }
        .sidebar-user:hover { background: var(--sidebar-hover); }
        .sidebar-user .avatar {
            width: 36px; height: 36px; background: var(--primary); color: white;
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 0.9rem; flex-shrink: 0;
        }
        .sidebar-user .info { flex: 1; min-width: 0; }
        .sidebar-user .name { color: var(--sidebar-brand); font-weight: 600; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2; }
        .sidebar-user .role { color: var(--sidebar-text); font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .main-content {
            flex: 1; margin-left: var(--sidebar-width); min-height: 100vh;
            background: var(--content-bg); transition: background-color 0.2s ease;
        }

        .top-bar {
            background: var(--card-bg); height: 70px; padding: 0 2rem;
            border-bottom: 1px solid var(--border-color); display: flex;
            justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 999;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .page-title { font-size: 1.25rem; font-weight: 600; color: var(--bs-heading-color); margin: 0; letter-spacing: -0.01em; }

        .theme-toggle-btn, .notification-btn {
            background: none; border: none; padding: 0.5rem; width: 38px; height: 38px;
            font-size: 1.1rem; cursor: pointer; color: var(--bs-body-color);
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px; transition: background 0.2s; position: relative;
        }
        .theme-toggle-btn:hover, .notification-btn:hover { background: rgba(16, 185, 129, 0.1); color: var(--primary); }

        .user-dropdown-btn {
            background: none; border: none; display: flex; align-items: center; gap: 0.75rem;
            padding: 0.4rem 0.5rem 0.4rem 1rem; border-radius: 8px; cursor: pointer; transition: all 0.2s;
        }
        .user-dropdown-btn:hover { background: rgba(16, 185, 129, 0.1); }
        .user-info { text-align: right; }
        .user-name { font-weight: 600; color: var(--bs-body-color); font-size: 0.85rem; }
        .user-role { font-size: 0.75rem; color: var(--bs-secondary-color); }
        
        .user-avatar {
            width: 32px; height: 32px; background: var(--primary); color: white;
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 0.85rem;
        }

        .dropdown-menu {
            position: absolute; right: 0; top: 100%; background: var(--card-bg);
            border-radius: 12px; box-shadow: var(--shadow); min-width: 220px;
            display: none; z-index: 1000; margin-top: 0.5rem;
            border: 1px solid var(--border-color); overflow: hidden; padding: 0.5rem;
        }
        .dropdown-menu.show { display: block; }
        .dropdown-item {
            padding: 0.6rem 1rem; display: flex; align-items: center; gap: 0.75rem;
            color: var(--bs-body-color); text-decoration: none; transition: all 0.2s;
            width: 100%; border: none; background: none; cursor: pointer; font-size: 0.85rem; border-radius: 8px;
        }
        .dropdown-item:hover { background: rgba(16, 185, 129, 0.1); color: var(--primary); }
        .dropdown-item:hover i { color: var(--primary); }
        .dropdown-item i { font-size: 1rem; color: var(--bs-secondary-color); transition: color 0.2s; }
        .dropdown-divider { height: 1px; background: var(--border-color); margin: 0.5rem 0; }

        .content-area { padding: 2rem; }

        .card {
            background: var(--card-bg); border: 1px solid var(--border-color);
            border-radius: 12px; box-shadow: var(--shadow-sm);
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .card-header {
            background: transparent; border-bottom: 1px solid var(--border-color); padding: 1.25rem 1.5rem;
        }
        .card-body { padding: 1.5rem; }

        .table { margin: 0; color: var(--bs-body-color); }
        .table > :not(caption) > * > * { background: transparent; color: var(--bs-body-color); border-bottom-color: var(--border-color); }
        .table thead th {
            border-bottom: 1px solid var(--border-color); color: var(--bs-secondary-color);
            font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 1rem 1.5rem;
        }
        .table tbody td {
            padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color); font-size: 0.875rem; vertical-align: middle;
        }
        .table tbody tr:last-child td { border-bottom: none; }

        .badge { padding: 0.35rem 0.65rem; font-weight: 600; font-size: 0.7rem; border-radius: 6px; letter-spacing: 0.02em; }
        .badge.bg-success { background: rgba(16, 185, 129, 0.1) !important; color: var(--primary) !important; border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge.bg-warning { background: rgba(245, 158, 11, 0.1) !important; color: #f59e0b !important; border: 1px solid rgba(245, 158, 11, 0.2); }
        .badge.bg-danger { background: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important; border: 1px solid rgba(239, 68, 68, 0.2); }
        .badge.bg-secondary { background: var(--bs-secondary-bg) !important; color: var(--bs-secondary-color) !important; }

        .btn-success { background: var(--primary); border: none; padding: 0.5rem 1rem; font-size: 0.875rem; border-radius: 8px; font-weight: 500; color: white;}
        .btn-success:hover { background: var(--primary-dark); }
        .btn-outline-secondary {
            border: 1px solid var(--border-color); color: var(--bs-body-color);
            padding: 0.5rem 1rem; font-size: 0.875rem; border-radius: 8px; font-weight: 500;
        }
        .btn-outline-secondary:hover { background: var(--bs-secondary-bg); border-color: var(--border-color); color: var(--bs-body-color); }

        .form-control, .input-group-text, .form-select {
            background-color: var(--card-bg); border-color: var(--border-color); color: var(--bs-body-color); font-size: 0.9rem;
        }
        .form-control:focus, .form-select:focus {
            background-color: var(--card-bg); border-color: var(--primary); color: var(--bs-body-color);
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
        }

        /* Modern Tabs override */
        .nav-tabs { border-bottom: 1px solid var(--border-color); }
        .nav-tabs .nav-link { 
            color: var(--bs-secondary-color); 
            border: none; border-bottom: 2px solid transparent; 
            padding: 1rem 1.5rem; margin-bottom: -1px; font-weight: 500; transition: all 0.2s;
        }
        .nav-tabs .nav-link:hover { border-color: transparent; color: var(--primary); }
        .nav-tabs .nav-link.active { 
            color: var(--primary) !important; 
            background: transparent !important; 
            border: none; 
            border-bottom: 2px solid var(--primary) !important; 
        }

        @media (max-width: 768px) {
            .sidebar { width: 0; left: -260px; }
            .main-content { margin-left: 0; }
            .content-area { padding: 1rem; }
            .top-bar { padding: 0 1rem; }
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

            <div class="sidebar-footer">
                <a href="{{ route('central.admin.profile') }}" class="sidebar-user">
                    <div class="avatar">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="info">
                        <div class="name">{{ auth()->user()->name }}</div>
                        <div class="role">{{ ucfirst(auth()->user()->role) }}</div>
                    </div>
                </a>
            </div>
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
                
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <!-- Theme Toggle -->
                    <button class="theme-toggle-btn" id="centralThemeToggle" title="Toggle theme">
                        <i class="bi bi-moon-fill dark-icon" style="display: none;"></i>
                        <i class="bi bi-sun-fill light-icon" style="font-size: 1.1rem;"></i>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div class="notifications-dropdown">
                        <button class="notification-btn" onclick="toggleNotifications()">
                            <i class="bi bi-bell"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="notification-badge" style="position: absolute; top:-2px; right:-2px; background:#ef4444; color:white; font-size: 0.65rem; padding: 0.15rem 0.35rem; border-radius: 10px; border: 2px solid var(--bs-body-bg);">{{ auth()->user()->unreadNotifications->count() }}</span>
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



