<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ tenant('school_name') }} - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
        }
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .sidebar { min-height: 100vh; background: #212529; color: white; width: 260px; }
        .nav-link { color: rgba(255,255,255,0.7); }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
        .header { background: white; border-bottom: 1px solid #dee2e6; padding: 1rem 2rem; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3 d-none d-md-block">
            <h4 class="fw-bold mb-4 px-2">{{ tenant('school_name') }}</h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="#" class="nav-link active rounded"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-people me-2"></i> Students</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-person-badge me-2"></i> Teachers</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-book me-2"></i> Courses</a></li>
            </ul>
        </div>
        <div class="flex-grow-1">
            <header class="header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Modern Blue Template</h5>
                <div class="dropdown">
                    <button class="btn btn-link text-dark dropdown-toggle text-decoration-none" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> Admin
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Logout</a></li>
                    </ul>
                </div>
            </header>
            <main class="p-4">
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="card p-3 border-start border-primary border-4">
                            <div class="text-secondary small">Total Students</div>
                            <div class="h4 fw-bold mb-0">1,240</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-3 border-start border-success border-4">
                            <div class="text-secondary small">Total Teachers</div>
                            <div class="h4 fw-bold mb-0">86</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card p-3 border-start border-info border-4">
                            <div class="text-secondary small">Active Courses</div>
                            <div class="h4 fw-bold mb-0">42</div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 text-center text-secondary">
                    <p>Welcome to <strong>{{ tenant('school_name') }}</strong> administration panel.</p>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>














