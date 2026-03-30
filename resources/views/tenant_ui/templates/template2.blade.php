<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ tenant('school_name') }} - Nature Eco Green</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #198754;
            --secondary-color: #20c997;
        }
        body { background-color: #f0fdf4; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background: #14532d; color: white; width: 260px; }
        .nav-link { color: rgba(255,255,255,0.7); }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
        .header { background: #dcfce7; border-bottom: 1px solid #bbf7d0; padding: 1rem 2rem; }
        .card { border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 1rem; }
        .btn-success { background-color: var(--primary-color); border: none; border-radius: 0.5rem; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3 d-none d-md-block">
            <div class="d-flex align-items-center mb-4 px-2">
                <i class="bi bi-leaf-fill fs-3 text-success me-2"></i>
                <h4 class="fw-bold mb-0 text-white">{{ tenant('school_name') }}</h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="#" class="nav-link active rounded"><i class="bi bi-house-door me-2"></i> Dashboard</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-mortarboard me-2"></i> Academics</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-journal-text me-2"></i> Attendance</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-gear me-2"></i> Settings</a></li>
            </ul>
        </div>
        <div class="flex-grow-1">
            <header class="header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-success fw-semibold">Nature Eco Green Template</h5>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person me-1"></i> Admin
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Account</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#">Logout</a></li>
                    </ul>
                </div>
            </header>
            <main class="p-4">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card p-4 bg-white">
                            <h3 class="fw-bold text-success mb-3">Welcome to your school management!</h3>
                            <p class="text-secondary">This green-themed template is designed for a refreshing and eco-friendly education environment.</p>
                            <button class="btn btn-success mt-2 px-4">Get Started</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-4 bg-success text-white">
                            <h5 class="fw-bold mb-3">School Stats</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Active Students</span>
                                <span class="fw-bold">850</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-white" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>














