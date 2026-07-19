@extends('panel.layout')
@section('title', 'POS')
@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">POS Outlets</h1>
            <p class="text-sm text-gray-500 mt-1">Pilih outlet untuk membuka sesi kasir</p>
        </div>
    </div>
</div>

@if ($outlets->isEmpty())
    {{-- Empty State --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 px-5 py-20 text-center">
        <div class="flex flex-col items-center gap-4 max-w-sm mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-700">Belum ada outlet POS</p>
                <p class="text-xs text-gray-400 mt-1">Tambahkan outlet melalui halaman Settings untuk mulai menggunakan POS</p>
            </div>
            <a href="{{ route('panel.settings.property') }}"
               class="inline-flex items-center gap-1.5 border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Buka Settings
            </a>
        </div>
    </div>
@else
    {{-- Outlet Grid --}}
    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
        @foreach ($outlets as $o)
            @php
                $typeIcon = match (strtolower($o->type ?? '')) {
                    'restaurant', 'resto'  => ['bg-orange-100', 'text-orange-600', 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                    'bar', 'lounge'        => ['bg-purple-100', 'text-purple-600', 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                    'spa'                  => ['bg-rose-100', 'text-rose-600', 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                    default                => ['bg-primary-100', 'text-primary-600', 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                };
            @endphp
            <a href="{{ route('panel.pos.tables', $o->id) }}"
               class="group bg-white rounded-2xl shadow-card border border-gray-100 p-5 hover:shadow-md hover:border-primary-100 transition-all duration-200 flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-xl {{ $typeIcon[0] }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 {{ $typeIcon[1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $typeIcon[2] }}"/>
                        </svg>
                    </div>
                    <div class="w-8 h-8 rounded-xl border border-gray-100 flex items-center justify-center group-hover:bg-primary-50 group-hover:border-primary-200 transition">
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-primary-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900 group-hover:text-primary-700 transition">{{ $o->name }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ ucfirst($o->type ?? 'Outlet') }}</p>
                </div>
                <div class="pt-3 border-t border-gray-100">
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-primary-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Buka Kasir
                    </span>
                </div>
            </a>
        @endforeach
    </div>
@endif

@endsection
