<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Marketplace ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-blue-600">Marketplace ERP</h1>
                </div>
                <div class="flex items-center space-x-4">
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Perusahaan Anda</h2>
                @if (auth()->user()->company)
                    <div class="space-y-2">
                        <p><strong>Nama:</strong> {{ auth()->user()->company->name }}</p>
                        <p><strong>Email:</strong> {{ auth()->user()->company->email }}</p>
                        <p><strong>Status:</strong>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                {{ auth()->user()->company->status }}
                            </span>
                        </p>
                        <p><strong>Telepon:</strong> {{ auth()->user()->company->phone ?? 'N/A' }}</p>
                    </div>
                @else
                    <p class="text-gray-600">Tidak ada data perusahaan</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Informasi Anda</h2>
                <div class="space-y-2">
                    <p><strong>Nama:</strong> {{ auth()->user()->name }}</p>
                    <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                    <p><strong>Role:</strong>
                        @foreach (auth()->user()->roles as $role)
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                {{ $role->display_name }}
                            </span>
                        @endforeach
                    </p>
                    <p><strong>Login Terakhir:</strong>
                        {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d M Y H:i') : 'Belum login' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Akun Marketplace Terhubung</h2>
                <a href="{{ route('marketplace.accounts') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Kelola Akun
                </a>
            </div>

            @php
                $accounts = auth()->user()->company->marketplaceAccounts ?? collect();
            @endphp

            @if ($accounts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Platform</th>
                                <th class="px-4 py-2 text-left">Nama Toko</th>
                                <th class="px-4 py-2 text-left">Terhubung Sejak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($accounts as $account)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                            {{ ucfirst($account->platform) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $account->shop_name ?? $account->shop_id }}</td>
                                    <td class="px-4 py-2">{{ $account->created_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600 text-center py-8">Belum ada akun marketplace yang terhubung</p>
            @endif
        </div>
    </div>
</body>
</html>
