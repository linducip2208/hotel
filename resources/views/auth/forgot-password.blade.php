<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Forgot Password</title>@vite(['resources/css/app.css'])</head>
<body class="bg-gray-50 min-h-screen flex items-center">
<main class="max-w-sm mx-auto bg-white rounded shadow p-6 border w-full">
    <h1 class="text-xl font-bold mb-4">Reset password</h1>
    @if (session('status')) <div class="bg-green-50 text-green-800 p-2 rounded mb-3 text-sm">{{ session('status') }}</div> @endif
    <form method="POST" action="{{ route('password.email') }}" class="space-y-3">
        @csrf
        <input type="email" name="email" required placeholder="Email" class="w-full border rounded p-2">
        <button class="w-full bg-primary-600 text-white py-2 rounded">Send reset link</button>
    </form>
</main>
</body></html>
