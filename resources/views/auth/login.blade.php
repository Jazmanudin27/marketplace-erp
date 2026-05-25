<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Marketplace ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Marketplace ERP</h1>
                <p class="text-gray-600 mt-2">Kelola semua marketplace Anda di satu tempat</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded" />
                        <span class="ml-2 text-gray-700">Ingat saya</span>
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200"
                >
                    Login
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">Belum punya akun?
                    <a href="{{ route('register.form') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                        Daftar di sini
                    </a>
                </p>
            </div>

            <!-- Test Credentials -->
            <div class="mt-8 p-4 bg-gray-100 rounded-lg text-sm">
                <p class="font-semibold text-gray-700 mb-2">Akun Test:</p>
                <p class="text-gray-600"><strong>Admin:</strong> admin@test.com</p>
                <p class="text-gray-600"><strong>Manager:</strong> manager@test.com</p>
                <p class="text-gray-600"><strong>Staff:</strong> staff@test.com</p>
                <p class="text-gray-600"><strong>Password:</strong> password</p>
            </div>
        </div>
    </div>
</body>
</html>
