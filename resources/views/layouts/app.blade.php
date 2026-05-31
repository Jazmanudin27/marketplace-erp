<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Marketplace ERP'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="app-shell">
    <nav class="navbar navbar-expand-lg navbar-dark app-navbar shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="{{ auth()->check() ? route('dashboard') : url('/') }}">Marketplace ERP</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNavbar" aria-controls="appNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="appNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('marketplace.*') ? 'active' : '' }}" href="{{ route('marketplace.accounts') }}">Marketplace</a></li>
                    @endauth
                </ul>
                <div class="d-flex align-items-center gap-3">
                    @auth
                        <span class="text-white-50 small d-none d-md-inline">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Login</a>
                        <a href="{{ route('register.form') }}" class="btn btn-light btn-sm">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="app-main py-4 py-lg-5">
        <div class="container">
            @if (session('success') || session('error') || $errors->any())
                <div class="d-none" id="flash-data"
                    data-success="{{ session('success') }}"
                    data-error="{{ session('error') }}"
                    data-validation="{{ $errors->any() ? $errors->first() : '' }}"></div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            const flash = $('#flash-data');
            const success = flash.data('success');
            const error = flash.data('error') || flash.data('validation');

            if (success && window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: success,
                    timer: 2200,
                    showConfirmButton: false
                });
            }

            if (error && window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi kesalahan',
                    text: error
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
