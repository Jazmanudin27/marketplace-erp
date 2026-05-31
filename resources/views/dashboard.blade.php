@extends('layouts.app')

@section('title', 'Dashboard - Marketplace ERP')

@section('content')
    @php
        $company = auth()->user()->company;
        $accounts = $company?->marketplaceAccounts ?? collect();
    @endphp

    <div class="page-hero p-4 p-lg-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold mb-2">Dashboard Marketplace ERP</h1>
                <p class="text-muted mb-0">Pantau perusahaan, akun marketplace, dan hasil sinkronisasi produk dari satu tempat.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('marketplace.accounts') }}" class="btn btn-primary btn-lg">Kelola Akun Marketplace</a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">Perusahaan Anda</h2>
                    @if ($company)
                        <div class="d-grid gap-2 small text-muted">
                            <div><strong>Nama:</strong> {{ $company->name }}</div>
                            <div><strong>Email:</strong> {{ $company->email }}</div>
                            <div><strong>Status:</strong> <span class="badge text-bg-success">{{ $company->status }}</span></div>
                            <div><strong>Telepon:</strong> {{ $company->phone ?? 'N/A' }}</div>
                        </div>
                    @else
                        <p class="text-muted mb-0">Tidak ada data perusahaan.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">Informasi Anda</h2>
                    <div class="d-grid gap-2 small text-muted">
                        <div><strong>Nama:</strong> {{ auth()->user()->name }}</div>
                        <div><strong>Email:</strong> {{ auth()->user()->email }}</div>
                        <div>
                            <strong>Role:</strong>
                            @foreach (auth()->user()->roles as $role)
                                <span class="badge text-bg-primary me-1">{{ $role->display_name }}</span>
                            @endforeach
                        </div>
                        <div><strong>Login Terakhir:</strong> {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d M Y H:i') : 'Belum login' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="h4 fw-bold mb-1">Akun Marketplace Terhubung</h2>
                    <p class="text-muted mb-0">Daftar akun yang sudah terhubung ke perusahaan Anda.</p>
                </div>
                <a href="{{ route('marketplace.accounts') }}" class="btn btn-outline-primary">Buka halaman akun</a>
            </div>

            @if ($accounts->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Platform</th>
                                <th>Nama Toko</th>
                                <th>Terhubung Sejak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($accounts as $account)
                                <tr>
                                    <td><span class="badge text-bg-info">{{ ucfirst($account->platform) }}</span></td>
                                    <td>{{ $account->shop_name ?? $account->shop_id }}</td>
                                    <td>{{ $account->created_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">Belum ada akun marketplace yang terhubung.</div>
            @endif
        </div>
    </div>
@endsection
