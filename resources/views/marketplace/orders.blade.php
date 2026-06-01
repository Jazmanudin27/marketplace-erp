@extends('layouts.app')

@section('title', 'Orders Marketplace - Marketplace ERP')

@section('content')
    <div class="page-hero p-4 p-lg-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold mb-2">Sinkronisasi Pesanan</h1>
                <p class="text-muted mb-0">Daftar pesanan untuk akun
                    {{ $account->shop_name ?? ucfirst($account->platform) }} yang tersimpan di tabel orders.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                @if (in_array($account->platform, ['shopee', 'tokopedia', 'tiktok']))
                    <a href="{{ route('marketplace.sync-orders', $account->id) }}" class="btn btn-primary me-2">Sync Pesanan</a>
                @endif
                <a href="{{ route('marketplace.show', $account) }}" class="btn btn-outline-primary">Detail Akun</a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small">Total Orders</div>
                    <div class="display-6 fw-bold mb-0">{{ $summary['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small">Pending</div>
                    <div class="display-6 fw-bold mb-0">{{ $summary['pending'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small">Paid</div>
                    <div class="display-6 fw-bold mb-0">{{ $summary['paid'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card page-card h-100">
                <div class="card-body p-4">
                    <div class="text-muted small">Completed</div>
                    <div class="display-6 fw-bold mb-0">{{ $summary['completed'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card page-card">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 class="h4 fw-bold mb-1">Daftar Orders</h2>
                    <p class="text-muted mb-0">Setiap baris mewakili satu pesanan yang tersimpan di database.</p>
                </div>
            </div>

            @if ($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Pembayaran</th>
                                <th>Total</th>
                                <th>Items</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php
                                    $shippingAddress = json_decode($order->shipping_address, true) ?: [];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $order->order_number }}</div>
                                        <div class="text-muted small">{{ $order->marketplace_order_id }}</div>
                                        <div class="text-muted small">{{ $order->order_date?->format('d M Y H:i') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $order->customer_name }}</div>
                                        <div class="text-muted small">{{ $order->customer_phone ?? '-' }}</div>
                                    </td>
                                    <td><span class="badge text-bg-secondary">{{ $order->order_status }}</span></td>
                                    <td><span class="badge text-bg-info">{{ $order->payment_status }}</span></td>
                                    <td>Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</td>
                                    <td>{{ $order->items->count() }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#orderModal{{ $order->id }}">Detail</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>

                @foreach ($orders as $order)
                    @php
                        $shippingAddress = json_decode($order->shipping_address, true) ?: [];
                    @endphp
                    <div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ $order->order_number }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-4 mb-4">
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3 h-100">
                                                <h6 class="fw-bold mb-3">Informasi Order</h6>
                                                <div class="small d-grid gap-2">
                                                    <div><strong>Marketplace:</strong> {{ ucfirst($order->marketplace) }}</div>
                                                    <div><strong>Order ID:</strong> {{ $order->marketplace_order_id }}</div>
                                                    <div><strong>Status:</strong> {{ $order->order_status }}</div>
                                                    <div><strong>Pembayaran:</strong> {{ $order->payment_status }}</div>
                                                    <div><strong>Metode Bayar:</strong> {{ $order->payment_method }}</div>
                                                    <div><strong>Subtotal:</strong> Rp
                                                        {{ number_format((float) $order->subtotal, 0, ',', '.') }}
                                                    </div>
                                                    <div><strong>Ongkir:</strong> Rp
                                                        {{ number_format((float) $order->shipping_fee, 0, ',', '.') }}
                                                    </div>
                                                    <div><strong>Total:</strong> Rp
                                                        {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3 h-100">
                                                <h6 class="fw-bold mb-3">Customer & Alamat</h6>
                                                <div class="small d-grid gap-2">
                                                    <div><strong>Nama:</strong> {{ $order->customer_name }}</div>
                                                    <div><strong>Email:</strong> {{ $order->customer_email ?? '-' }}</div>
                                                    <div><strong>Phone:</strong> {{ $order->customer_phone ?? '-' }}</div>
                                                    <div><strong>Penerima:</strong> {{ $shippingAddress['name'] ?? '-' }}</div>
                                                    <div>
                                                        <strong>Alamat:</strong>
                                                        {{ \Illuminate\Support\Str::limit($shippingAddress['address'] ?? '-', 100, '') }}
                                                    </div>
                                                    <div><strong>Kota:</strong> {{ $shippingAddress['city'] ?? '-' }}</div>
                                                    <div><strong>Provinsi:</strong> {{ $shippingAddress['province'] ?? '-' }}</div>
                                                    <div><strong>Kode Pos:</strong> {{ $shippingAddress['postal_code'] ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border rounded-3 p-3">
                                        <h6 class="fw-bold mb-3">Items</h6>
                                        @if ($order->items->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Produk</th>
                                                            <th>SKU</th>
                                                            <th>Qty</th>
                                                            <th>Harga</th>
                                                            <th>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($order->items as $item)
                                                            <tr>
                                                                <td>{{ $item->product_name }}</td>
                                                                <td>{{ $item->sku ?? '-' }}</td>
                                                                <td>{{ $item->quantity }}</td>
                                                                <td>Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                                                <td>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-muted">Belum ada item yang tersimpan.</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <h3 class="h5 fw-bold mb-2">Belum ada pesanan</h3>
                    <p class="text-muted mb-4">Klik sync pesanan untuk menarik order dari marketplace.</p>
                    @if (in_array($account->platform, ['shopee', 'tokopedia', 'tiktok']))
                        <a href="{{ route('marketplace.sync-orders', $account->id) }}" class="btn btn-primary">Sync Pesanan</a>
                    @else
                        <div class="alert alert-warning d-inline-block mb-0">Sinkronisasi pesanan belum didukung untuk platform ini.
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
