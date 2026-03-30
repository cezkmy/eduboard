<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EduBoard - Multi-Tenant School Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Vite Styles and Scripts -->
    @vite(['resources/css/central/app.css', 'resources/css/central/auth.css', 'resources/js/central/app.js'])
    
    <style>
        :root {
            --primary: #2c7a6e;  /* Green teal */
            --primary-dark: #1e5a50;
            --primary-light: #e8f3f1;
            --accent: #f4a261;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --sidebar-bg: #1a1e2b;
            --sidebar-text: #a0aec0;
         }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background: white;
            padding-top: 76px;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .navbar-brand {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            color: var(--text-dark) !important;
        }

        .navbar-brand i {
            color: var(--primary) !important;  /* Green dapat */
        }

        .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: var(--primary) !important;
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background: var(--primary-dark) !important;
            border-color: var(--primary-dark) !important;
        }

        .btn-outline-primary {
            border-color: var(--primary) !important;
            color: var(--primary) !important;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
        }

        .btn-outline-primary:hover {
            background: var(--primary) !important;
            color: white !important;
        }

        .btn-ghost {
            border: none;
            color: var(--text-light);
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
            background: transparent;
        }

        .btn-ghost:hover {
            background: rgba(0,0,0,0.05);
        }

        /* Hero Section */
        .hero {
            position: relative;
            overflow: hidden;
            padding: 3rem 0;
            background: white !important;  /* Force white background */
        }

        /* Hero background decoration - FIXED COLORS */
        .hero .bg-primary {
            background-color: var(--primary) !important;
            opacity: 0.05 !important;  /* Very light green */
        }

        .hero .bg-accent {
            background-color: var(--accent) !important;
            opacity: 0.05 !important;
        }

        .hero-badge {
            background: var(--primary-light) !important;
            color: var(--primary) !important;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(44, 122, 110, 0.2);
        }

        .hero-badge i {
            color: var(--primary) !important;
        }

        .hero h1 {
            font-size: 3rem;
            line-height: 1.2;
            color: var(--text-dark) !important;
        }

        .hero .lead, .hero p {
            color: var(--text-light) !important;
        }

        .text-gradient {
            background: linear-gradient(135deg, var(--primary), #2c7a9e) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            background-clip: text !important;
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
            margin-top: 1.5rem;
        }

        .hero-stat {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light) !important;
        }

        .hero-stat i {
            color: var(--primary) !important;
            font-size: 1.5rem;
        }

        /* Hero Image */
        .hero img {
            border-radius: 1rem !important;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;  /* Shadow lang, no border */
            border: none !important;  /* Remove any border */
            max-width: 75%;
            height: auto;
            margin-left: auto;  /* Push image to the right side */
            display: block;     /* Ensure margin-left:auto works */
        }

        /* Features */
        .features-section {
            background: var(--bg-light);
            padding: 4rem 0;
        }

        .feature-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s;
            height: 100%;
            text-align: left;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-light) !important;
            color: var(--primary) !important;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .feature-icon i {
            color: var(--primary) !important;
        }

        /* Pricing */
        .pricing-section {
            padding: 4rem 0;
        }

        .pricing-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 2rem;
            height: 100%;
            position: relative;
            transition: all 0.3s;
        }

        .pricing-card.highlight {
            border-color: var(--primary) !important;
            box-shadow: 0 10px 30px rgba(44, 122, 110, 0.15);
            background: rgba(44, 122, 110, 0.02);
        }

        .popular-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary) !important;
            color: white;
            padding: 0.25rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .price {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-dark);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .price small {
            font-size: 1rem;
            font-weight: 400;
            color: var(--text-light);
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            color: var(--text-light);
        }

        .feature-list li i {
            color: var(--primary) !important;
            font-size: 1.25rem;
        }

        /* About & Contact Sections */
        #about, #contact {
            padding: 4rem 0;
        }

        #about p, #contact p {
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            color: var(--text-light);
        }

        #contact i {
            color: var(--primary) !important;
        }

        #contact a {
            color: var(--text-light) !important;
        }

        #contact a:hover {
            color: var(--primary) !important;
        }

        /* Footer fixes */
        footer {
            background: var(--bg-light);
            border-top: 1px solid #e2e8f0;
            padding: 2rem 0;  /* Gi-reduce ang padding */
            font-size: 0.9rem;  /* Smaller text */
        }

        footer .navbar-brand {
            padding: 0;  /* Remove extra padding */
            margin: 0;
        }

        footer .navbar-brand i {
            color: var(--primary) !important;
            font-size: 1.75rem;  /* Mas gamay nga icon */
        }

        footer .navbar-brand span {
            color: var(--text-dark) !important;
            font-size: 1.1rem !important;  /* Mas gamay nga text */
        }

        footer p {
            color: var(--text-light) !important;
            font-size: 0.85rem !important;  /* Mas gamay nga copyright */
        }

        /* Admin Sidebar */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand i {
            color: var(--primary) !important;
        }

        .sidebar-brand span {
            color: white !important;
        }

        .sidebar-brand .badge {
            background: var(--primary) !important;
            color: white;
        }

        .sidebar-nav {
            padding: 1.5rem 1rem;
        }

        .sidebar-nav .nav-link {
            color: var(--sidebar-text) !important;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s;
        }

        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white !important;
        }

        .sidebar-nav .nav-link.active {
            background: var(--primary) !important;
            color: white !important;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.25rem;
            color: currentColor;
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .content-header {
            background: white;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 2rem;
        }

        .content-body {
            padding: 2rem;
        }

        .btn-logout {
            color: var(--sidebar-text);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            text-align: left;
            background: transparent;
            border: none;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero .lead {
                max-width: 100%;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    @include('central.components.navbar')
    
    <main>
        @yield('content')
    </main>
    
    @include('central.components.footer')
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
    
    @yield('scripts')
</body>
</html>



