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
            min-height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .auth-wrapper {
            flex: 1;
            display: flex;
            padding: 3rem 1rem;
            width: 100%;
            box-sizing: border-box;
        }
        
        .auth-container {
            width: 100%;
            max-width: 440px;
            margin: auto;
        }
        
        .auth-card {
            background: var(--surface);
            border-radius: 24px;
            padding: 2.5rem 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.03), 0 1px 3px rgba(0,0,0,0.02);
            border: 1px solid var(--border);
        }
        
        .auth-brand { margin-bottom: 2.5rem; }
        
        .auth-header h1 { font-family: 'Sora', sans-serif; font-size: 1.8rem; letter-spacing: -0.5px; }
        
        .auth-form input[type="email"], .auth-form input[type="password"], .auth-form input[type="text"], .form-select {
            background-color: var(--bg) !important;
            border: 1.5px solid var(--border) !important;
            border-radius: 14px !important;
            padding: 0.85rem 1.25rem !important;
            transition: all 0.2s ease !important;
            color: var(--text-main) !important;
        }
        
        .auth-form input:focus, .form-select:focus {
            background-color: var(--surface) !important;
            border-color: var(--teal) !important;
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.15) !important;
        }
        
        .btn-auth {
            background: var(--teal) !important;
            color: white !important;
            padding: 0.85rem 2rem !important;
            border-radius: 100px !important;
            font-weight: 600 !important;
            transition: all 0.2s ease !important;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.2) !important;
        }
        
        .btn-auth:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 10px 20px rgba(13, 148, 136, 0.3) !important;
            background: #0f766e !important;
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
