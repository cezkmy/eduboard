<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', (tenant('school_name') ?? 'EduBoard') . ' - Authentication')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Vite Styles -->
    @vite(['resources/css/tenant/app.css', 'resources/css/tenant/auth.css'])
    
    <style>
        :root {
            --teal: #0D9488;
            --teal-bg: #e6f7f6;
            --bg: #F0F4F3;
            --surface: #ffffff;
            --text-main: #1a1a1a;
            --text-muted: #8a9399;
            --border: rgba(0,0,0,0.08);
        }

        [data-theme="dark"] {
            --bg: #111827;
            --surface: #1f2937;
            --text-main: #f9fafb;
            --text-muted: #9ca3af;
            --border: rgba(255,255,255,0.08);
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    
    <!-- Scripts -->
    @stack('scripts')
</body>
</html>
