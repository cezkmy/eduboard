<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ tenant('school_name') }} - EduBoard Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #1e40af; /* Primary School Blue */
            --secondary-color: #64748b;
        }
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; }
        .sidebar { min-height: 100vh; background: #0f172a; color: white; width: 260px; border-right: 1px solid rgba(255,255,255,0.1); }
        .nav-link { color: rgba(255,255,255,0.7); padding: 0.8rem 1rem; border-radius: 8px; margin-bottom: 0.5rem; transition: all 0.2s; }
        .nav-link:hover, .nav-link.active { color: white; background: var(--primary-color); }
        .header { background: white; border-bottom: 1px solid #e2e8f0; padding: 1rem 2rem; }
        .card { border: none; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border-radius: 12px; }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-4 d-none d-md-block">
            <div class="d-flex align-items-center gap-2 mb-5 px-2">
                <i class="bi bi-mortarboard-fill fs-3 text-primary"></i>
                <h4 class="fw-bold mb-0">EduBoard</h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="#" class="nav-link active"><i class="bi bi-grid-1x2-fill me-2"></i> Dashboard</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-people-fill me-2"></i> Student Roster</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-person-video3 me-2"></i> Faculty List</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-journal-bookmark-fill me-2"></i> Curriculum</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-calendar-event-fill me-2"></i> School Events</a></li>
            </ul>
        </div>
        <div class="flex-grow-1">
            <header class="header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold text-secondary">{{ tenant('school_name') }} Portal</h5>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=1e40af&color=fff" class="rounded-circle" width="32">
                        <span>Admin Profile</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg">
                        <li><a class="dropdown-item py-2" href="#"><i class="bi bi-person me-2"></i> Profile Settings</a></li>
                        <li><a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i> School Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('tenant.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </header>
            <main class="p-4 p-lg-5">
                <div class="mb-5">
                    <h2 class="fw-bold text-dark mb-2">Welcome to {{ tenant('school_name') }} eduboard</h2>
                    <p class="text-secondary">Official Administration Panel for {{ tenant('school_name') }}</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div>
                                    <div class="text-secondary small fw-medium">Total Students</div>
                                    <div class="h3 fw-bold mb-0">1,854</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-person-video3"></i>
                                </div>
                                <div>
                                    <div class="text-secondary small fw-medium">Active Teachers</div>
                                    <div class="h3 fw-bold mb-0">94</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-book"></i>
                                </div>
                                <div>
                                    <div class="text-secondary small fw-medium">Active Courses</div>
                                    <div class="h3 fw-bold mb-0">58</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="card p-4 text-center bg-white">
                        <img src="https://img.icons8.com/bubbles/200/education.png" alt="Welcome" width="120" class="mb-3 mx-auto">
                        <h4 class="fw-bold mb-3">Get Started with {{ tenant('school_name') }} Administration</h4>
                        <p class="text-secondary mb-4 mx-auto" style="max-width: 500px;">Manage student records, track faculty performance, and oversee the entire school operations from this central dashboard.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-primary px-4 py-2">View Reports</button>
                            <button class="btn btn-outline-secondary px-4 py-2">School Settings</button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>














