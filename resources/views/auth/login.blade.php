@extends('layouts.app')

@section('title', 'Login - Marketplace ERP')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="card page-card">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <h1 class="h3 fw-bold mb-2">Marketplace ERP</h1>
                        <p class="text-muted mb-0">Kelola semua marketplace Anda di satu tempat.</p>
                    </div>

                    <form action="{{ route('login.submit') }}" method="POST" class="d-grid gap-3">
                        @csrf

                        <div>
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        </div>

                        <div>
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </form>

                    <div class="mt-4 text-center">
                        <span class="text-muted">Belum punya akun?</span>
                        <a href="{{ route('register.form') }}" class="fw-semibold text-decoration-none">Daftar di sini</a>
                    </div>

                    <div class="mt-4 alert alert-light border small mb-0">
                        <div class="fw-semibold mb-2">Akun Test</div>
                        <div>Admin: admin@test.com</div>
                        <div>Manager: manager@test.com</div>
                        <div>Staff: staff@test.com</div>
                        <div>Password: password</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
