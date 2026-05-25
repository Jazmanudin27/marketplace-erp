<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Marketplace - Marketplace ERP</title>
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

    <div class="max-w-7xl mx-auto py-12 px-4">
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Kelola Akun Marketplace</h1>
            <div class="flex space-x-2">
                <form action="{{ route('marketplace.connect') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="platform" value="shopee">
                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg">
                        + Shopee
                    </button>
                </form>
                <form action="{{ route('marketplace.connect') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="platform" value="tokopedia">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg">
                        + Tokopedia
                    </button>
                </form>
                <form action="{{ route('marketplace.connect') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="platform" value="tiktok">
                    <button type="submit" class="bg-black hover:bg-gray-800 text-white font-semibold py-2 px-4 rounded-lg">
                        + TikTok
                    </button>
                </form>
                <form action="{{ route('marketplace.connect') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="platform" value="lazada">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                        + Lazada
                    </button>
                </form>
            </div>
        </div>

        @if ($accounts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($accounts as $account)
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-bold text-gray-900">{{ $account->shop_name ?? ucfirst($account->platform) }}</h3>
                            <span class="px-3 py-1 bg-{{ match($account->platform) { 'shopee' => 'orange', 'tokopedia' => 'green', 'tiktok' => 'black', 'lazada' => 'blue', default => 'gray' } }}-100 text-{{ match($account->platform) { 'shopee' => 'orange', 'tokopedia' => 'green', 'tiktok' => 'black', 'lazada' => 'blue', default => 'gray' } }}-800 rounded-full text-xs font-semibold">
                                {{ ucfirst($account->platform) }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-6">
                            <p><strong>Shop ID:</strong> {{ $account->shop_id }}</p>
                            <p><strong>Terhubung:</strong> {{ $account->created_at->format('d M Y H:i') }}</p>
                            @if ($account->expired_at)
                                <p><strong>Kadaluarsa:</strong> {{ $account->expired_at->format('d M Y H:i') }}</p>
                            @endif
                        </div>

                        <div class="flex space-x-2">
                            <a href="{{ route('marketplace.show', $account) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-center">
                                Lihat Detail
                            </a>
                            <form action="{{ route('marketplace.disconnect', $account) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg" onclick="return confirm('Apakah Anda yakin ingin memutus koneksi?')">
                                    Putus Hubung
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Belum ada akun marketplace</h2>
                <p class="text-gray-600 mb-6">Hubungkan akun marketplace Anda untuk mulai mengelola toko</p>
                <p class="text-gray-500 text-sm">Platform yang tersedia: Shopee, Tokopedia, TikTok, Lazada</p>
            </div>
        @endif
    </div>
</body>
</html>
