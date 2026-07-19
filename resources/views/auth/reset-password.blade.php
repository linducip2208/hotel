<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Reset Password</title>@vite(['resources/css/app.css'])</head>
<body class="bg-gray-50 min-h-screen flex items-center">
<main class="max-w-sm mx-auto bg-white rounded shadow p-6 border w-full">
    <h1 class="text-xl font-bold mb-4">Set new password</h1>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-3">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <input type="email" name="email" value="{{ $request->email }}" required class="w-full border rounded p-2">
        <input type="password" name="password" required minlength="10" placeholder="New password" class="w-full border rounded p-2">
        <input type="password" name="password_confirmation" required placeholder="Confirm" class="w-full border rounded p-2">
        <button class="w-full bg-primary-600 text-white py-2 rounded">Reset</button>
    </form>
</main>
</body></html>
