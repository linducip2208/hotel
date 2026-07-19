<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <main class="max-w-3xl mx-auto p-8">
        <h1 class="text-3xl font-bold mb-4">{{ config('app.name') }}</h1>
        <p class="text-gray-600">Hotel Management System — Laravel 11.</p>
        <p class="mt-4">
            <a href="{{ route('booking.search') }}" class="text-primary-600 underline">Booking Engine</a>
            ·
            <a href="/setup/wizard" class="text-primary-600 underline">Setup Wizard</a>
            ·
            <a href="/admin" class="text-primary-600 underline">Admin</a>
            ·
            <a href="/panel" class="text-primary-600 underline">Panel</a>
        </p>
    </main>
</body>
</html>
