<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Required - {{ $school }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --edu-teal: #24a887;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            max-width: 500px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: rgba(36, 168, 135, 0.1);
            color: var(--edu-teal);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px;
        }
        .btn-edu {
            background-color: var(--edu-teal);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-edu:hover {
            background-color: #1e8e72;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="card error-card p-5 text-center">
        <div class="icon-circle">
            <i class="bi bi-shield-lock fs-1"></i>
        </div>
        <h2 class="fw-bold mb-3">Subscription Required</h2>
        <p class="text-secondary mb-4">
            Access to <strong>{{ $school }}</strong> portal has been suspended because your subscription has expired.
            Please renew your subscription to reactivate your school domain.
        </p>
        <div class="d-grid gap-2">
            <a href="{{ config('app.central_url', 'http://eduboard.app:8000') }}/central/user/subscription" class="btn btn-edu">
                <i class="bi bi-credit-card me-2"></i>Renew Now
            </a>
            <a href="{{ config('app.central_url', 'http://eduboard.app:8000') }}" class="btn btn-link text-secondary">
                Back to EduBoard
            </a>
        </div>
    </div>
</body>
</html>













