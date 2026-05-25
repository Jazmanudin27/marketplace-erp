<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Akun - Marketplace ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-blue-600">Marketplace ERP</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="{{ route('marketplace.accounts') }}" class="text-gray-600 hover:text-gray-900">Akun Marketplace</a>
                    <span class="text-gray-700">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-12 px-4">
        <a href="{{ route('marketplace.accounts') }}" class="text-blue-600 hover:text-blue-700 mb-6 inline-block">
            ← Kembali ke Akun Marketplace
        </a>

        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $account->shop_name ?? ucfirst($account->platform) }}</h1>
                    <p class="text-gray-600 mt-2">{{ ucfirst($account->platform) }} Shop Account</p>
                </div>
                <span class="px-4 py-2 bg-{{ match($account->platform) { 'shopee' => 'orange', 'tokopedia' => 'green', 'tiktok' => 'black', 'lazada' => 'blue', default => 'gray' } }}-100 text-{{ match($account->platform) { 'shopee' => 'orange', 'tokopedia' => 'green', 'tiktok' => 'black', 'lazada' => 'blue', default => 'gray' } }}-800 rounded-lg font-semibold">
                    {{ ucfirst($account->platform) }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Informasi Akun</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-gray-600 text-sm">Shop ID</p>
                            <p class="text-gray-900 font-semibold">{{ $account->shop_id }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Shop Name</p>
                            <p class="text-gray-900 font-semibold">{{ $account->shop_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Platform</p>
                            <p class="text-gray-900 font-semibold">{{ ucfirst($account->platform) }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Status Koneksi</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-gray-600 text-sm">Terhubung Sejak</p>
                            <p class="text-gray-900 font-semibold">{{ $account->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Token Kadaluarsa</p>
                            <p class="text-gray-900 font-semibold">
                                @if ($account->expired_at)
                                    @if ($account->expired_at->isPast())
                                        <span class="text-red-600">{{ $account->expired_at->format('d M Y H:i') }}</span> (Kadaluarsa)
                                    @else
                                        {{ $account->expired_at->format('d M Y H:i') }}
                                    @endif
                                @else
                                    Tidak ada (Permanent)
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Terakhir Diperbarui</p>
                            <p class="text-gray-900 font-semibold">{{ $account->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t pt-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Aksi</h2>
                <div class="flex space-x-4">
                    <form action="{{ route('marketplace.disconnect', $account) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200"
                            onclick="return confirm('Apakah Anda yakin ingin memutus koneksi akun ini? Anda tidak akan dapat mengakses data toko ini sampai terhubung kembali.')"
                        >
                            Putus Hubung Akun
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-bold text-blue-900 mb-2">ℹ️ Informasi</h3>
            <p class="text-blue-800 text-sm">
                Access token dan refresh token disimpan dengan aman. Jangan bagikan token ini ke siapa pun.
            </p>
        </div>
    </div>
</body>
</html>
