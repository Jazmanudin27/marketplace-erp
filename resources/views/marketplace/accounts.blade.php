@extends('layouts.app')

@section('title', 'Akun Marketplace - Marketplace ERP')

@section('content')
    @php
        $platforms = [
            'shopee' => ['label' => 'Shopee', 'class' => 'warning'],
            'tokopedia' => ['label' => 'Tokopedia', 'class' => 'success'],
            'tiktok' => ['label' => 'TikTok', 'class' => 'dark'],
            'lazada' => ['label' => 'Lazada', 'class' => 'primary'],
        ];
    @endphp

    <div class="page-hero p-4 p-lg-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold mb-2">Kelola Akun Marketplace</h1>
                <p class="text-muted mb-0">Hubungkan akun Shopee, Tokopedia, TikTok, atau Lazada untuk sinkronisasi produk.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>

    <div class="card page-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap gap-2">
                @foreach ($platforms as $platform => $meta)
                    <form action="{{ route('marketplace.connect') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="platform" value="{{ $platform }}">
                        <button type="submit" class="btn btn-{{ $meta['class'] }}">+ {{ $meta['label'] }}</button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>

    @if ($accounts->count() > 0)
        <div class="row g-4">
            @foreach ($accounts as $account)
                <div class="col-md-6 col-xl-4">
                    <div class="card page-card h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h3 class="h5 fw-bold mb-1">{{ $account->shop_name ?? ucfirst($account->platform) }}</h3>
                                    <div class="text-muted small">{{ $account->shop_id }}</div>
                                </div>
                                <span class="badge text-bg-secondary">{{ ucfirst($account->platform) }}</span>
                            </div>

                            <div class="small text-muted mb-4">
                                <div class="mb-2"><strong>Terhubung:</strong> {{ $account->created_at->format('d M Y H:i') }}</div>
                                @if ($account->expired_at)
                                    <div><strong>Kadaluarsa:</strong> {{ $account->expired_at->format('d M Y H:i') }}</div>
                                @endif
                            </div>

                            <div class="mt-auto d-grid gap-2">
                                <a href="{{ route('marketplace.show', $account) }}" class="btn btn-primary">Lihat Detail</a>
                                <a href="{{ route('marketplace.products', $account) }}" class="btn btn-outline-success">Lihat Produk</a>
                                <form action="{{ route('marketplace.disconnect', $account) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutus koneksi?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100">Putus Hubung</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card page-card">
            <div class="card-body text-center py-5">
                <h2 class="h4 fw-bold mb-2">Belum ada akun marketplace</h2>
                <p class="text-muted mb-0">Hubungkan akun marketplace Anda untuk mulai mengelola toko.</p>
            </div>
        </div>
    @endif
@endsection
