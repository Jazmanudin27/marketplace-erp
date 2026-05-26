<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Akun Marketplace</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="flex justify-between items-center h-16">

                <div class="flex items-center gap-8">
                    <a href="{{ route('dashboard') }}"
                        class="text-2xl font-bold text-blue-600">
                        Marketplace ERP
                    </a>

                    <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                        <a href="{{ route('dashboard') }}"
                            class="text-gray-600 hover:text-blue-600 transition">
                            Dashboard
                        </a>

                        <a href="{{ route('marketplace.accounts') }}"
                            class="text-blue-600 font-semibold">
                            Marketplace
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Administrator
                        </p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf

                        <button type="submit"
                            class="text-red-600 hover:text-red-700 text-sm font-semibold">
                            Logout
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="max-w-6xl mx-auto px-4 lg:px-8 py-8">

        <!-- Back -->
        <a href="{{ route('marketplace.accounts') }}"
            class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium mb-6">
            ← Kembali ke Akun Marketplace
        </a>

        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">

            <!-- Header -->
            <div class="p-8 border-b border-gray-100">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                    <div class="flex items-center gap-5">

                        <div
                            class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-bold text-white
                            @if ($account->platform == 'shopee') bg-orange-500
                            @elseif($account->platform == 'tokopedia') bg-green-500
                            @elseif($account->platform == 'tiktok') bg-black
                            @elseif($account->platform == 'lazada') bg-blue-600
                            @else bg-gray-500 @endif">

                            {{ strtoupper(substr($account->platform, 0, 1)) }}
                        </div>

                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                {{ $account->shop_name ?? 'Marketplace Account' }}
                            </h1>

                            <p class="text-gray-500 mt-1">
                                {{ ucfirst($account->platform) }} Shop Account
                            </p>

                            <div class="mt-4 flex flex-wrap gap-3">

                                <span
                                    class="px-4 py-2 rounded-xl text-sm font-semibold
                                    @if ($account->platform == 'shopee') bg-orange-100 text-orange-700
                                    @elseif($account->platform == 'tokopedia') bg-green-100 text-green-700
                                    @elseif($account->platform == 'tiktok') bg-gray-900 text-white
                                    @elseif($account->platform == 'lazada') bg-blue-100 text-blue-700
                                    @else bg-gray-100 text-gray-700 @endif">

                                    {{ ucfirst($account->platform) }}
                                </span>

                                @if ($account->expired_at && $account->expired_at->isPast())
                                    <span
                                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-100 text-red-700">
                                        Token Kadaluarsa
                                    </span>
                                @else
                                    <span
                                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-green-100 text-green-700">
                                        Terhubung
                                    </span>
                                @endif

                            </div>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3">

                        <a href="{{ route('marketplace.sync-products', $account->id) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-2xl transition duration-200 shadow-sm">
                            Sync Produk
                        </a>

                        <form action="{{ route('marketplace.disconnect', $account) }}"
                            method="POST">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                onclick="return confirm('Yakin ingin memutus koneksi akun ini?')"
                                class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-2xl transition duration-200 shadow-sm">
                                Putuskan
                            </button>

                        </form>

                    </div>

                </div>

            </div>

            <!-- Body -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-8">

                <!-- Informasi Akun -->
                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">

                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        Informasi Akun
                    </h2>

                    <div class="space-y-5">

                        <div>
                            <p class="text-sm text-gray-500 mb-1">
                                Shop ID
                            </p>

                            <div
                                class="bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold text-gray-800 break-all">
                                {{ $account->shop_id }}
                            </div>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 mb-1">
                                Shop Name
                            </p>

                            <div
                                class="bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold text-gray-800">
                                {{ $account->shop_name ?? 'N/A' }}
                            </div>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 mb-1">
                                Platform
                            </p>

                            <div
                                class="bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold text-gray-800">
                                {{ ucfirst($account->platform) }}
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Status -->
                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">

                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        Status Koneksi
                    </h2>

                    <div class="space-y-5">

                        <div>
                            <p class="text-sm text-gray-500 mb-1">
                                Terhubung Sejak
                            </p>

                            <div
                                class="bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold text-gray-800">
                                {{ $account->created_at->format('d M Y H:i') }}
                            </div>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 mb-1">
                                Token Kadaluarsa
                            </p>

                            <div
                                class="bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold">

                                @if ($account->expired_at)

                                    @if ($account->expired_at->isPast())
                                        <span class="text-red-600">
                                            {{ $account->expired_at->format('d M Y H:i') }}
                                        </span>
                                    @else
                                        <span class="text-green-600">
                                            {{ $account->expired_at->format('d M Y H:i') }}
                                        </span>
                                    @endif

                                @else
                                    Tidak ada
                                @endif

                            </div>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 mb-1">
                                Terakhir Diperbarui
                            </p>

                            <div
                                class="bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold text-gray-800">
                                {{ $account->updated_at->format('d M Y H:i') }}
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- Info -->
        <div
            class="mt-8 bg-blue-50 border border-blue-200 rounded-2xl p-6">

            <div class="flex items-start gap-4">

                <div
                    class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-2xl">
                    ℹ️
                </div>

                <div>
                    <h3 class="font-bold text-blue-900 text-lg mb-2">
                        Informasi Keamanan
                    </h3>

                    <p class="text-blue-800 text-sm leading-relaxed">
                        Access token dan refresh token marketplace disimpan secara aman
                        di server. Jangan membagikan token ini kepada siapa pun.
                    </p>
                </div>

            </div>

        </div>

    </div>

</body>

</html>
