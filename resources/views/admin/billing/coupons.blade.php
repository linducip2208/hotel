@extends('admin.layout')
@section('title', 'Coupons')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Coupons</h1>
</div>

@if ($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded text-sm">{{ $errors->first() }}</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 py-16 text-center max-w-lg mx-auto">
    <p class="text-sm font-medium text-gray-600">Sistem kupon diskon belum tersedia pada instalasi ini.</p>
    <p class="text-xs text-gray-400 mt-2">Diskon tingkat tenant saat ini dikelola manual melalui penyesuaian invoice.</p>
</div>
@endsection
