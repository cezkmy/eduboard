<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Suspended - {{ $school }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --edu-teal: #dc3545;
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
            background: rgba(220, 53, 69, 0.1);
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
            background-color: #bb2d3b;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="card error-card p-5 text-center">
        <div class="icon-circle">
            <i class="bi bi-exclamation-octagon fs-1"></i>
        </div>
        <h2 class="fw-bold mb-3">School Suspended</h2>
        <p class="text-secondary mb-4">
            Access to <strong>{{ $school }}</strong> portal has been suspended by the administrator. 
            Please contact support if you believe this is an error.
        </p>
        <div class="d-grid gap-2">
            <a href="mailto:support@eduboard.app" class="btn btn-edu">
                <i class="bi bi-envelope me-2"></i>Contact Support
            </a>
            <a href="{{ config('app.central_url', 'http://eduboard.app:8000') }}" class="btn btn-link text-secondary">
                Back to EduBoard
            </a>
        </div>
    </div>
</body>
</html>













