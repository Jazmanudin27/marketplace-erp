<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Marketplace ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Marketplace ERP</h1>
                <p class="text-gray-600 mt-2">Daftar sekarang dan mulai mengelola toko Anda</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Perusahaan</label>
                    <input
                        type="text"
                        name="company_name"
                        value="{{ old('company_name') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Email Perusahaan</label>
                    <input
                        type="email"
                        name="company_email"
                        value="{{ old('company_email') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nomor Telepon (Opsional)</label>
                    <input
                        type="tel"
                        name="company_phone"
                        value="{{ old('company_phone') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <hr class="my-6">

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Anda</label>
                    <input
                        type="text"
                        name="user_name"
                        value="{{ old('user_name') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Email Anda</label>
                    <input
                        type="email"
                        name="user_email"
                        value="{{ old('user_email') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Konfirmasi Password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200"
                >
                    Daftar
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                        Login di sini
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
