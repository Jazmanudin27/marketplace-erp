@extends('layouts.app')

@section('title', 'Detail Akun Marketplace - Marketplace ERP')

@section('content')
    <div class="mb-4">
        <a href="{{ route('marketplace.accounts') }}" class="text-decoration-none">&larr; Kembali ke akun marketplace</a>
    </div>

    <div class="card page-card mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                            style="width: 64px; height: 64px; background: {{ $account->platform === 'shopee' ? '#f97316' : ($account->platform === 'tokopedia' ? '#16a34a' : ($account->platform === 'tiktok' ? '#111827' : '#0d6efd')) }};">
                            {{ strtoupper(substr($account->platform, 0, 1)) }}
                        </div>
                        <div>
                            <h1 class="h3 fw-bold mb-1">{{ $account->shop_name ?? 'Marketplace Account' }}</h1>
                            <div class="text-muted">{{ ucfirst($account->platform) }} Shop Account</div>
                        </div>
                    </div>
                    <span class="badge text-bg-primary me-2">{{ ucfirst($account->platform) }}</span>
                    <span
                        class="badge {{ $account->expired_at && $account->expired_at->isPast() ? 'text-bg-danger' : 'text-bg-success' }}">{{ $account->expired_at && $account->expired_at->isPast() ? 'Token Kadaluarsa' : 'Terhubung' }}</span>
                </div>

                <div class="d-flex flex-column gap-2 align-items-stretch align-items-lg-end">
                    <a href="{{ route('marketplace.sync-products', $account->id) }}" class="btn btn-primary">Sync Produk</a>
                    <a href="{{ route('marketplace.products', $account) }}" class="btn btn-outline-success">Lihat Produk</a>
                    @if (in_array($account->platform, ['shopee', 'tokopedia', 'tiktok']))
                        <a href="{{ route('marketplace.sync-orders', $account->id) }}" class="btn btn-success">Sync Pesanan</a>
                        <a href="{{ route('marketplace.orders', $account) }}" class="btn btn-outline-success">Lihat Orders</a>
                    @endif
                    <form action="{{ route('marketplace.disconnect', $account) }}" method="POST"
                        onsubmit="return confirm('Yakin ingin memutus koneksi akun ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">Putuskan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-4">Informasi Akun</h2>
                    <div class="d-grid gap-3 small">
                        <div><strong>Shop ID:</strong> {{ $account->shop_id }}</div>
                        <div><strong>Shop Name:</strong> {{ $account->shop_name ?? 'N/A' }}</div>
                        <div><strong>Platform:</strong> {{ ucfirst($account->platform) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-4">Status Koneksi</h2>
                    <div class="d-grid gap-3 small">
                        <div><strong>Terhubung Sejak:</strong> {{ $account->created_at->format('d M Y H:i') }}</div>
                        <div><strong>Token Kadaluarsa:</strong>
                            {{ $account->expired_at ? $account->expired_at->format('d M Y H:i') : 'Tidak ada' }}</div>
                        <div><strong>Terakhir Diperbarui:</strong> {{ $account->updated_at->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-2">Ringkasan Produk</h2>
            <p class="text-muted mb-0">Gunakan menu produk untuk melihat data yang sudah tersinkron.</p>
        </div>
    </div>
@endsection
