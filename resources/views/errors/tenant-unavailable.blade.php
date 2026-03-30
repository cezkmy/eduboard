<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Unavailable | EduBoard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .unavailable-card {
            background: #fff;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .icon-circle i {
            font-size: 2.5rem;
            color: #ef4444;
        }
    </style>
</head>

<body>

    <div class="unavailable-card">
        <div class="icon-circle">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h2 class="fw-bold mb-3">Site Unavailable</h2>
        <p class="text-secondary mb-4">
            This site is currently unavailable. Please contact your administrator or school representative for more
            information.
        </p>

        @auth
            @if(auth()->user()->role !== 'admin')
                <form action="{{ route('tenant.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                    </button>
                </form>
            @else
                <a href="{{ route('tenant.dashboard') }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                </a>
                <form action="{{ route('tenant.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                    </button>
                </form>
            @endif
        @endauth

        @guest
            <a href="{{ route('tenant.login') }}" class="btn btn-outline-primary w-100">
                <i class="bi bi-person-fill-lock me-2"></i>Admin Login
            </a>
        @endguest
    </div>

</body>

</html>