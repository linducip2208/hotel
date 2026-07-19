<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Welcome</title>@vite(['resources/css/app.css'])</head>
<body class="bg-gray-50 min-h-screen flex items-center">
<main class="max-w-xl mx-auto p-8 bg-white border rounded text-center">
    <h1 class="text-3xl font-bold text-green-700 mb-3">🎉 Welcome to HotelHub</h1>
    <p>Trial Anda aktif hingga <strong>{{ $tenant->trial_ends_at?->format('d M Y') }}</strong></p>
    <p class="mt-3">Akses tenant Anda di: <code class="bg-gray-100 px-2">{{ $tenant->slug }}.hotelhub.id</code></p>
    <p class="mt-3 text-sm text-gray-600">Email konfirmasi telah dikirim ke {{ $tenant->owner_email }}.</p>
</main>
</body></html>
