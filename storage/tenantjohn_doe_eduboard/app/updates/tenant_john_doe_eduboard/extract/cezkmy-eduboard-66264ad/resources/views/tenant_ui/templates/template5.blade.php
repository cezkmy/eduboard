<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ tenant('school_name') }} - Yellow Landing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #facc15; /* Yellow */
            --secondary-color: #f59e0b; /* Amber */
        }
        body { background-color: #fff7ed; font-family: 'Inter', sans-serif; }
        .sidebar { min-height: 100vh; background: #92400e; color: white; width: 260px; }
        .nav-link { color: rgba(255,255,255,0.7); }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.10); }
        .header { background: white; border-bottom: 1px solid #fef3c7; padding: 1rem 2rem; }
        .card { border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 1rem; }
        .btn-amber { background: var(--primary-color); color: #111827; border: none; border-radius: 0.75rem; }
        .btn-amber:hover { background: var(--secondary-color); color: white; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3 d-none d-md-block">
            <div class="d-flex align-items-center gap-2 mb-4 px-2">
                <i class="bi bi-sun-fill text-warning fs-3"></i>
                <h4 class="fw-bold mb-0">{{ tenant('school_name') }}</h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="#" class="nav-link active rounded"><i class="bi bi-house-door me-2"></i> Dashboard</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-people me-2"></i> Students</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-person-badge me-2"></i> Teachers</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-book me-2"></i> Courses</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-calendar-event me-2"></i> School Events</a></li>
            </ul>
        </div>

        <div class="flex-grow-1">
            <header class="header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-semibold">Yellow Landing Template</h5>
                <div class="dropdown">
                    <button class="btn btn-amber dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
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
                    <div class="col-md-4">
                        <div class="card p-4">
                            <h3 class="fw-bold mb-2 text-warning">Bright & Clear</h3>
                            <p class="text-secondary small mb-0">A warm yellow theme that feels welcoming and energetic.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4 bg-warning bg-opacity-10">
                            <h5 class="fw-bold mb-2 text-dark">School Highlights</h5>
                            <div class="d-flex justify-content-between small text-secondary mb-2">
                                <span>Active Students</span>
                                <span class="fw-bold text-dark">850</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4">
                            <h5 class="fw-bold mb-2 text-dark">Quick Actions</h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-amber px-4">Get Started</button>
                                <button class="btn btn-outline-secondary px-4">Manage</button>
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

