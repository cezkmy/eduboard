<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ tenant('school_name') }} - Royal Education</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #6b21a8;
            --secondary-color: #9333ea;
        }
        body { background-color: #faf5ff; font-family: 'Poppins', sans-serif; }
        .sidebar { min-height: 100vh; background: #581c87; color: white; width: 260px; border-right: 1px solid rgba(255,255,255,0.1); }
        .nav-link { color: rgba(255,255,255,0.7); margin-bottom: 0.5rem; padding: 0.75rem 1rem; border-radius: 0.5rem; }
        .nav-link:hover, .nav-link.active { color: white; background: #7e22ce; box-shadow: 0 4px 12px rgba(126, 34, 206, 0.3); }
        .header { background: white; border-bottom: 1px solid #e9d5ff; padding: 1rem 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .card { border: 1px solid #f3e8ff; border-radius: 1.25rem; transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .btn-royal { background: var(--primary-color); color: white; border: none; border-radius: 0.75rem; }
        .btn-royal:hover { background: var(--secondary-color); color: white; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3 d-none d-md-block">
            <div class="text-center mb-5 mt-3">
                <i class="bi bi-mortarboard-fill fs-1 text-purple-300"></i>
                <h4 class="fw-bold text-white mt-2">{{ tenant('school_name') }}</h4>
            </div>
            <ul class="nav flex-column mt-4">
                <li class="nav-item"><a href="#" class="nav-link active"><i class="bi bi-grid-1x2 me-2"></i> Overview</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-person-lines-fill me-2"></i> Staff Directory</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-calendar3 me-2"></i> Academic Calendar</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-file-earmark-bar-graph me-2"></i> Performance</a></li>
            </ul>
        </div>
        <div class="flex-grow-1">
            <header class="header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-purple-800 fw-bold">Royal Education Template</h5>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-purple-100 p-2 rounded-circle">
                        <i class="bi bi-bell text-purple-700"></i>
                    </div>
                    <button class="btn btn-royal px-4 py-2 shadow-sm">
                        Admin Portal
                    </button>
                </div>
            </header>
            <main class="p-4">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100 p-4 border-0 shadow-sm text-center">
                            <div class="bg-purple-50 p-3 rounded-circle d-inline-block mx-auto mb-3">
                                <i class="bi bi-award fs-3 text-purple-600"></i>
                            </div>
                            <h5 class="fw-bold text-purple-900">Excellence</h5>
                            <p class="text-secondary small">This royal purple theme reflects prestige and academic achievement.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 p-4 border-0 shadow-sm text-center">
                            <div class="bg-blue-50 p-3 rounded-circle d-inline-block mx-auto mb-3">
                                <i class="bi bi-shield-check fs-3 text-primary"></i>
                            </div>
                            <h5 class="fw-bold text-blue-900">Security</h5>
                            <p class="text-secondary small">Your data is isolated and secured with per-tenant databases.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 p-4 border-0 shadow-sm text-center">
                            <div class="bg-orange-50 p-3 rounded-circle d-inline-block mx-auto mb-3">
                                <i class="bi bi-lightning-charge fs-3 text-warning"></i>
                            </div>
                            <h5 class="fw-bold text-orange-900">Speed</h5>
                            <p class="text-secondary small">High performance school management at your fingertips.</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>














