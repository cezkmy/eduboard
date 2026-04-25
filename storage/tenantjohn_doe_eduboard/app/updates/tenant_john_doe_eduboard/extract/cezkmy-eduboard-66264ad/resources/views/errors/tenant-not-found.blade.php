<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tenant not found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; }
        .card { border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .badge-domain { word-break: break-all; }
    </style>
</head>
<body class="d-flex align-items-center" style="min-height: 100vh;">
    <div class="container" style="max-width: 720px;">
        <div class="card p-4 p-md-5">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger flex-shrink-0" style="width: 48px; height: 48px;">
                    <span class="fw-bold">!</span>
                </div>
                <div class="flex-grow-1">
                    <h1 class="h4 fw-bold mb-2">School domain not found</h1>
                    <p class="text-secondary mb-3">
                        We couldn’t identify a tenant for this domain:
                        <span class="badge bg-secondary bg-opacity-10 text-dark badge-domain">{{ $domain }}</span>
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('home') }}" class="btn btn-success">
                            Back to Central
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                            Login
                        </a>
                    </div>
                    <hr class="my-4">
                    <p class="small text-secondary mb-0">
                        If you just created your school, go to <strong>Central → Domain</strong> and click <strong>Visit</strong> to open the correct tenant domain.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

