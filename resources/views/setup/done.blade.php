<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>Setup Done</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <main class="max-w-2xl mx-auto p-8 text-center">
        <h1 class="text-3xl font-bold text-green-700 mb-2">🎉 Setup Selesai</h1>
        <p class="text-gray-600 mb-6">Lisensi aktif, property dan admin sudah dibuat.</p>
        <a href="/panel" class="inline-block bg-primary-600 text-white px-6 py-3 rounded">Masuk Panel Staff</a>
    </main>
</body>
</html>
