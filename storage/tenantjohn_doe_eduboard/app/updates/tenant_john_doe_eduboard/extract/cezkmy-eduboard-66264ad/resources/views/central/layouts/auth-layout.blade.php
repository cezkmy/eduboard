<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EduBoard - Authentication')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts for Modern Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vite Styles -->
    @vite(['resources/css/central/auth.css'])
    
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f2f5;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f0f2f5;
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .auth-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        .auth-header {
            padding: 24px 28px 8px 28px;  /* Gi-reduce ang bottom padding */
            text-align: left;
            background: white;
        }

        .auth-header i {
            font-size: 38px; 
            margin-bottom: 12px;
            display: block;
            color: #2c7a6e;
            text-align: center;
        }

        .auth-header h2 {
            font-size: 22px; 
            font-weight: 600;
            margin: 0 0 2px 0;
            color: #1a1f2c;
            text-align: center;
        }

        .auth-header p {
            font-size: 13px; 
            margin: 0;
            color: #5f6b7a;
            text-align: center;
        }

        .auth-body {
            padding: 8px 28px 24px 28px;  /* Gi-reduce ang top padding */
            background: white;
        }

        .form-group {
            margin-bottom: 16px;  /* Gi-reduce ang gap */
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #1a1f2c;
            margin-bottom: 4px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 14px;  /* Medyo nipis */
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            font-size: 13px;
            color: #1a1f2c;
            background: #fafbfc;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2c7a6e;
            background: white;
        }

        .form-group input::placeholder {
            color: #8f9eb2;
        }

        .btn-auth {
            width: 100%;
            background: #2c7a6e;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px;  /* Medyo nipis */
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin: 12px 0 16px 0;  /* Gi-adjust ang margin */
        }

        .btn-auth:hover {
            background: #236b60;
        }

        .auth-links {
            text-align: center;
            margin-bottom: 16px;  /* Gi-reduce */
        }

        .auth-links span {
            color: #5f6b7a;
            font-size: 13px;
        }

        .auth-links a {
            color: #5f6b7a;
            text-decoration: none;
            font-size: 13px;
            margin-left: 4px;
        }

        .auth-links a:hover {
            color: #2c7a6e;
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
        }

        .back-link a {
            color: #5f6b7a;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .back-link a:hover {
            color: #2c7a6e;
        }

        .back-link i {
            font-size: 13px;
        }
    </style>
</head>
<body>
    @yield('content')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



