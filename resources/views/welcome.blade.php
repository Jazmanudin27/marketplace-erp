@extends('layouts.app')

@section('title', 'Marketplace ERP')

@section('content')
    <div class="row align-items-center g-4">
        <div class="col-lg-6">
            <div class="page-hero p-4 p-lg-5">
                <span class="badge text-bg-primary mb-3">Marketplace ERP</span>
                <h1 class="display-5 fw-bold mb-3">Kelola akun marketplace dan produk dalam satu tempat.</h1>
                <p class="lead text-muted mb-4">Bootstrap 5, jQuery, dan popup alert sudah disiapkan di layout aplikasi. Login untuk masuk ke dashboard dan sinkronisasi produk.</p>
                <div class="d-flex flex-wrap gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Buka Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Login</a>
                        <a href="{{ route('register.form') }}" class="btn btn-outline-primary btn-lg">Register</a>
                    @endauth
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card page-card">
                <div class="card-body p-4 p-lg-5">
                    <h2 class="h4 fw-bold mb-4">Fitur utama</h2>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                            <span>Sinkronisasi produk marketplace</span>
                            <span class="badge text-bg-success">Ready</span>
                        </div>
                        <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                            <span>Detail akun per platform</span>
                            <span class="badge text-bg-success">Ready</span>
                        </div>
                        <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                            <span>Alert success/error popup</span>
                            <span class="badge text-bg-success">Ready</span>
                        </div>
                        <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                            <span>Tampilan Bootstrap 5</span>
                            <span class="badge text-bg-success">Ready</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
