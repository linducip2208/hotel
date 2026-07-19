<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Admin Login</title>@vite(['resources/css/app.css'])</head>
<body class="bg-gray-900 min-h-screen flex items-center text-white">
<main class="max-w-sm mx-auto bg-gray-800 rounded shadow p-6 w-full">
    <h1 class="text-xl font-bold mb-4">Vendor Admin</h1>
    @if ($errors->any())<div class="bg-red-900 p-2 rounded mb-3 text-sm">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.login') }}" class="space-y-3">
        @csrf
        <input type="email" name="email" required autofocus placeholder="Email" class="w-full bg-gray-900 border border-gray-700 rounded p-2">
        <input type="password" name="password" required placeholder="Password" class="w-full bg-gray-900 border border-gray-700 rounded p-2">
        <button class="w-full bg-primary-600 text-white py-2 rounded">Sign in</button>
    </form>
</main>
</body></html>
