<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ tenant('school_name') }} - Orange Landing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #f97316; /* Orange */
            --secondary-color: #fb923c; /* Peach */
        }
        body { background-color: #fff; font-family: 'Inter', sans-serif; }
        .sidebar { min-height: 100vh; background: #9a3412; color: white; width: 260px; }
        .nav-link { color: rgba(255,255,255,0.7); margin-bottom: 0.5rem; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.10); }
        .header { background: white; border-bottom: 1px solid #fed7aa; padding: 1rem 2rem; }
        .card { border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border-radius: 1rem; }
        .btn-orange { background: var(--primary-color); color: white; border: none; border-radius: 0.75rem; }
        .btn-orange:hover { background: var(--secondary-color); color: #111827; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3 d-none d-md-block">
            <div class="d-flex align-items-center gap-2 mb-4 px-2">
                <i class="bi bi-fire text-orange" style="color: var(--primary-color) !important;"></i>
                <h4 class="fw-bold mb-0" style="color: white;">{{ tenant('school_name') }}</h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="#" class="nav-link active rounded"><i class="bi bi-grid-1x2 me-2"></i> Dashboard</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-people me-2"></i> Students</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-person-badge me-2"></i> Teachers</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-journal-text me-2"></i> Courses</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link rounded"><i class="bi bi-calendar-event me-2"></i> School Events</a></li>
            </ul>
        </div>

        <div class="flex-grow-1">
            <header class="header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold" style="color: #9a3412;">Orange Landing Template</h5>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 px-3" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person me-1" style="color: var(--primary-color) !important;"></i> Admin
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
                            <h3 class="fw-bold mb-2" style="color: var(--primary-color);">Energized</h3>
                            <p class="text-secondary small mb-0">A bold orange theme for active school communities.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4" style="background: rgba(249,115,22,0.08);">
                            <h5 class="fw-bold mb-3">School Highlights</h5>
                            <div class="d-flex justify-content-between small text-secondary mb-2">
                                <span>Active Students</span>
                                <span class="fw-bold text-dark">850</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" style="width: 75%; background: var(--primary-color);"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4">
                            <h5 class="fw-bold mb-3">Quick Actions</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-orange px-4" type="button">Get Started</button>
                                <button class="btn btn-outline-secondary px-4" type="button">Manage</button>
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

