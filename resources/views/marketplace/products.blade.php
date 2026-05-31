@extends('layouts.app')

@section('title', 'Produk Marketplace - Marketplace ERP')

@section('content')
    <div class="page-hero p-4 p-lg-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold mb-2">Produk Marketplace</h1>
                <p class="text-muted mb-0">Daftar produk dari akun {{ $account->shop_name ?? ucfirst($account->platform) }} yang sudah tersinkron.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('marketplace.sync-products', $account->id) }}" class="btn btn-primary me-2">Sync Ulang</a>
                <a href="{{ route('marketplace.show', $account) }}" class="btn btn-outline-primary">Detail Akun</a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small">Total Produk</div>
                    <div class="display-6 fw-bold mb-0">{{ $products->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small">Platform</div>
                    <div class="h3 fw-bold mb-0">{{ ucfirst($account->platform) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small">Update Terakhir</div>
                    <div class="h6 fw-bold mb-0">{{ optional($products->first())->synced_at ? optional($products->first())->synced_at->format('d M Y H:i') : 'Belum ada data' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 fw-bold mb-1">Daftar Produk</h2>
                    <p class="text-muted mb-0">SKU tampil per baris supaya stok dan harga mudah dipantau.</p>
                </div>
            </div>

            @if ($products->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Seller SKU</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Sync</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $product->name ?? '-' }}</div>
                                        <div class="text-muted small">Product ID: {{ $product->product_id }}</div>
                                    </td>
                                    <td>{{ $product->seller_sku ?? '-' }}</td>
                                    <td>Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td><span class="badge text-bg-secondary">{{ $product->status ?? 'N/A' }}</span></td>
                                    <td>{{ $product->synced_at ? $product->synced_at->format('d M Y H:i') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <h3 class="h5 fw-bold mb-2">Belum ada produk</h3>
                    <p class="text-muted mb-4">Klik sync produk untuk menarik data dari marketplace.</p>
                    <a href="{{ route('marketplace.sync-products', $account->id) }}" class="btn btn-primary">Sync Produk</a>
                </div>
            @endif
        </div>
    </div>
@endsection
