<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EduBoard') }} - {{ tenant('school_name') ?? 'Admin' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts -->
    @php
        $user = auth()->user();
        $cssFiles = ['resources/css/tenant/app.css', 'resources/css/tenant/admin.css', 'resources/js/tenant/app.js', 'resources/js/tenant/admin.js'];
        
        if ($user) {
            if ($user->role === 'teacher') {
                $cssFiles = ['resources/css/tenant/app.css', 'resources/css/tenant/teacher.css', 'resources/js/tenant/app.js', 'resources/js/tenant/admin.js'];
            } elseif ($user->role === 'student') {
                $cssFiles = ['resources/css/tenant/app.css', 'resources/css/tenant/student.css', 'resources/js/tenant/app.js', 'resources/js/tenant/admin.js'];
            }
        }
    @endphp
    @vite($cssFiles)
    @include('tenant_ui.partials.appearance-script')
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @php
        $tenantThemeColor = tenant('theme_color');
        
        $theme = match ($tenantThemeColor) {
            'blue' => ['accent' => '#0d6efd', 'accent_dark' => '#0b5ed7', 'rgb' => '13, 110, 253'],
            'green' => ['accent' => '#198754', 'accent_dark' => '#157347', 'rgb' => '25, 135, 84'],
            'pink' => ['accent' => '#ec4899', 'accent_dark' => '#be185d', 'rgb' => '236, 72, 153'],
            'yellow' => ['accent' => '#facc15', 'accent_dark' => '#eab308', 'rgb' => '250, 204, 21'],
            'orange' => ['accent' => '#f97316', 'accent_dark' => '#ea580c', 'rgb' => '249, 115, 22'],
            default => ['accent' => '#0d6efd', 'accent_dark' => '#0b5ed7', 'rgb' => '13, 110, 253'],
        };
    @endphp

    <style>
        :root{
            --accent: {{ $theme['accent'] }};
            --accent-dark: {{ $theme['accent_dark'] }};
            --accent-rgb: {{ $theme['rgb'] }};

            /* Override tenant CSS theme tokens (default is emerald). */
            --color-primary: var(--accent);
            --color-primary-dark: var(--accent-dark);
            --color-sidebar-item-active: var(--accent);
            --sidebar-bg: var(--accent);
            --sidebar-border: rgba(var(--accent-rgb), 0.35);
            --sidebar-text: rgba(255, 255, 255, 0.84);
            --sidebar-heading: rgba(255, 255, 255, 0.80);
            --sidebar-title: #ffffff;
            --sidebar-hover: rgba(255, 255, 255, 0.12);
            --color-sidebar-bg: var(--sidebar-bg);
            --color-sidebar-text: var(--sidebar-text);
            --color-sidebar-text-active: #ffffff;
        }

        [data-theme="dark"]{
            --sidebar-bg: #0f172a;
            --sidebar-border: #1f2937;
            --sidebar-text: #9ca3af;
            --sidebar-heading: #6b7280;
            --sidebar-title: #ffffff;
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --color-sidebar-bg: var(--sidebar-bg);
            --color-sidebar-text: var(--sidebar-text);
        }

        /* Light mode sidebar hover: white text/line */
        html:not([data-theme="dark"]) .sidebar-item:hover {
            color: #ffffff !important;
            border-left: 2px solid #ffffff;
            padding-left: 10px;
        }

        /* Global dark-mode surface + text fixes for tenant pages */
        [data-theme="dark"] .bg-white,
        [data-theme="dark"] .profile-card,
        [data-theme="dark"] .ann-card,
        [data-theme="dark"] .card,
        [data-theme="dark"] .admin-dropdown-menu {
            background: var(--bg-card) !important;
            border-color: var(--border-color) !important;
        }

        [data-theme="dark"] .text-gray-900,
        [data-theme="dark"] .text-gray-800,
        [data-theme="dark"] .text-slate-900,
        [data-theme="dark"] .text-black {
            color: var(--text-main) !important;
        }

        [data-theme="dark"] .text-gray-700,
        [data-theme="dark"] .text-gray-600,
        [data-theme="dark"] .text-gray-500,
        [data-theme="dark"] .text-slate-600 {
            color: var(--text-muted) !important;
        }

        [data-theme="dark"] .bg-gray-50,
        [data-theme="dark"] .bg-gray-100,
        [data-theme="dark"] .bg-slate-50 {
            background: #111827 !important;
            color: var(--text-main) !important;
        }

        /* Topbar + dropdown global dark-mode corrections */
        [data-theme="dark"] .admin-topbar {
            background: var(--bg-card) !important;
            border-bottom-color: var(--border-color) !important;
        }
        [data-theme="dark"] .admin-topbar-title,
        [data-theme="dark"] .topbar-title {
            color: var(--text-main) !important;
        }
        [data-theme="dark"] .topbar-btn {
            background: #111827 !important;
            color: var(--text-muted) !important;
        }
        [data-theme="dark"] .topbar-btn:hover {
            background: #1f2937 !important;
            color: var(--text-main) !important;
        }
        [data-theme="dark"] .admin-dropdown-menu .notif-title,
        [data-theme="dark"] .admin-dropdown-menu .name {
            color: var(--text-main) !important;
        }
        [data-theme="dark"] .admin-dropdown-menu .notif-desc,
        [data-theme="dark"] .admin-dropdown-menu .notif-time,
        [data-theme="dark"] .admin-dropdown-menu .email {
            color: var(--text-muted) !important;
        }
        [data-theme="dark"] .notif-footer {
            border-top-color: var(--border-color) !important;
        }

        /* Sidebar readability in light mode */
        html:not([data-theme="dark"]) .sidebar-label {
            color: rgba(255, 255, 255, 0.78) !important;
        }
        html:not([data-theme="dark"]) .sidebar-brand-name,
        html:not([data-theme="dark"]) .sidebar-brand-sub {
            color: #ffffff !important;
        }
        html:not([data-theme="dark"]) .sidebar-brand-icon {
            background: rgba(255, 255, 255, 0.18) !important;
            color: #ffffff !important;
        }

        /* V2 sidebar in light mode: white-ish brand and labels */
        html:not([data-theme="dark"]) aside[data-sidebar-v2] .brand-title,
        html:not([data-theme="dark"]) aside[data-sidebar-v2] .brand-subtitle {
            color: #ffffff !important;
        }
        html:not([data-theme="dark"]) aside[data-sidebar-v2] .sidebar-section-label {
            color: rgba(255, 255, 255, 0.80) !important;
        }

        [data-theme="dark"] input,
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background: #111827 !important;
            color: var(--text-main) !important;
            border-color: var(--border-color) !important;
        }

        .bg-accent { background: var(--accent) !important; }
        .text-accent { color: var(--accent) !important; }
        .shadow-accent { box-shadow: 0 12px 28px rgba(var(--accent-rgb), 0.25) !important; }
        .ring-accent { box-shadow: 0 0 0 4px rgba(var(--accent-rgb), 0.18) !important; }
    </style>

</head>
<body class="admin-body min-h-screen" style="margin:0; padding:0; background: var(--bg-main); font-family: sans-serif;">
    <div class="admin-layout {{ ($appearance['navPos'] ?? 'left') === 'top' ? 'nav-top' : (($appearance['navPos'] ?? 'left') === 'right' ? 'nav-right' : 'nav-left') }} min-h-screen w-full">
        @if(auth()->user()->role !== 'student')
            @include('tenant_ui.layouts.sidebar')
        @endif

        <div class="admin-main" style="{{ auth()->user()->role === 'student' ? 'margin-left: 0;' : '' }}">
            @if(auth()->user()->role === 'student')
                <x-student-topbar :title="$title ?? 'Announcements'" />
            @else
                @include('tenant_ui.layouts.top-navbar')
            @endif

            <main class="admin-content">
                {{ $slot }}
            </main>
        </div>
    </div>
    <script>
        (function () {
            const root = document.documentElement;
            const themeKey = "theme";

            const getSystemTheme = () =>
                window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";

            const moonPath = "M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z";
            const sunPath = "M12 3v1.5m0 15V21m8.485-8.485H19m-14 0H3m14.364 6.364l-1.06-1.06M7.757 7.757l-1.06-1.06m10.606 0l-1.06 1.06M7.757 16.243l-1.06 1.06M15 12a3 3 0 11-6 0 3 3 0 016 0z";

            const updateThemeButtons = (isDark) => {
                const buttons = document.querySelectorAll("#themeBtn");
                buttons.forEach((btn) => {
                    const path = btn.querySelector("svg path");
                    if (!path) return;
                    path.setAttribute("d", isDark ? sunPath : moonPath);
                    btn.setAttribute("title", isDark ? "Switch to light mode" : "Switch to dark mode");
                });
            };

            const applyTheme = (theme, persist = true) => {
                const resolved = theme === "system" ? getSystemTheme() : theme;
                root.setAttribute("data-theme", resolved);
                root.classList.toggle("dark", resolved === "dark");
                updateThemeButtons(resolved === "dark");
                if (persist) {
                    localStorage.setItem(themeKey, theme);
                }
            };

            const savedTheme = localStorage.getItem(themeKey);
            if (savedTheme) {
                applyTheme(savedTheme, false);
            } else if (!root.getAttribute("data-theme")) {
                applyTheme("light", false);
            }

            document.addEventListener("click", function (event) {
                const toggleBtn = event.target.closest("#themeBtn");
                if (!toggleBtn) return;

                const current = root.getAttribute("data-theme") === "dark" ? "dark" : "light";
                applyTheme(current === "dark" ? "light" : "dark");
            });

            window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", () => {
                if (localStorage.getItem(themeKey) === "system") {
                    applyTheme("system", false);
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
