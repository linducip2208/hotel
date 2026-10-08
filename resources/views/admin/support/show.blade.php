@extends('admin.layout')
@section('title', 'Ticket Details')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Ticket Details</h1>
</div>

<div class="bg-white rounded-xl border border-gray-200 py-16 text-center max-w-lg mx-auto">
    <p class="text-sm font-medium text-gray-600">Sistem tiket belum tersedia pada instalasi ini.</p>
    <p class="text-xs text-gray-400 mt-2">Data dukungan saat ini mengalir melalui kanal komunikasi tenant (in-app messaging) dan email.</p>
    <a href="{{ route('admin.support.kb') }}" class="inline-block mt-4 text-sm text-primary-600 underline">Lihat Knowledge Base</a>
</div>
@endsection
