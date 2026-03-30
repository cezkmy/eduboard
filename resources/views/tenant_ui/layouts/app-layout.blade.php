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

</head>
<body class="admin-body min-h-screen" style="margin:0; padding:0; background: #F0F4F3; font-family: sans-serif;">
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
    @stack('scripts')
</body>
</html>
