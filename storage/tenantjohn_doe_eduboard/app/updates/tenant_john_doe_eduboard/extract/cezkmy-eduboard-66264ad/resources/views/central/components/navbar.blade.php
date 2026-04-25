<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <i class="bi bi-mortarboard fs-2 text-primary"></i>
            <span class="fw-bold">EduBoard</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#features">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#pricing">Pricing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact">Contact</a>
                </li>
            </ul>
        </div>

        <div class="d-flex align-items-center gap-2">
            @auth
                @if(Auth::user()->role === 'admin')
                    <span class="badge bg-primary d-none d-sm-inline-block me-1">Admin</span>
                    <a href="{{ route('central.admin.dashboard') }}" class="btn btn-outline-primary btn-sm">Dashboard</a>
                @else
                    <a href="{{ route('central.user.dashboard') }}" class="btn btn-outline-primary btn-sm">Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
            @endauth
        </div>
    </div>
</nav>



