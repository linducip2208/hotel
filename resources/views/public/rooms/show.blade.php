@extends('public.layout')
@section('title', $roomType->name.' — '.$property->name)
@section('description', \Illuminate\Support\Str::limit(strip_tags($roomType->description ?? ''), 155))

@php
    $photos = is_array($roomType->photos) ? $roomType->photos : (is_string($roomType->photos) ? json_decode($roomType->photos, true) : []);
    $photos = array_values(array_filter($photos ?? []));
    $cover = $photos[0] ?? null;
    $thumbs = array_slice($photos, 1, 4);
    $amenList = is_array($roomType->amenities) ? $roomType->amenities : (is_string($roomType->amenities) ? json_decode($roomType->amenities, true) : []);
@endphp

@section('content')

<section class="pt-24 lg:pt-28">
    {{-- Photo gallery --}}
    @if($cover)
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 md:gap-3 rounded-3xl overflow-hidden">
                <div class="md:col-span-2 md:row-span-2 relative aspect-[4/3] md:aspect-auto md:h-[520px] bg-slate-200">
                    <img src="{{ $cover }}" alt="{{ $roomType->name }}" class="absolute inset-0 w-full h-full object-cover">
                </div>
                @foreach($thumbs as $p)
                    <div class="relative aspect-[4/3] bg-slate-200 hidden md:block">
                        <img src="{{ $p }}" alt="{{ $roomType->name }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                    </div>
                @endforeach
                @for($i = count($thumbs); $i < 4; $i++)
                    <div class="hidden md:block bg-gradient-to-br from-indigo-100 to-violet-100"></div>
                @endfor
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-12 lg:py-16 grid lg:grid-cols-3 gap-10">
        <div class="lg:col-span-2">
            <p class="text-xs font-bold text-indigo-600 uppercase tracking-[0.25em] mb-3">{{ $roomType->code }} · {{ $property->name }}</p>
            <h1 class="font-display text-3xl lg:text-5xl font-bold text-slate-900 tracking-tight mb-4">{{ $roomType->name }}</h1>
            <p class="text-slate-600 text-lg leading-relaxed mb-8">{{ $roomType->description }}</p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10">
                <div class="bg-white border border-slate-200 rounded-2xl p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ukuran</p>
                    <p class="font-display text-xl font-bold text-slate-900 mt-1">{{ $roomType->size_sqm ?? '-' }}<span class="text-sm font-normal text-slate-500"> m²</span></p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tempat Tidur</p>
                    <p class="font-display text-xl font-bold text-slate-900 mt-1">{{ $roomType->bed_config ?? '-' }}</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kapasitas</p>
                    <p class="font-display text-xl font-bold text-slate-900 mt-1">{{ $roomType->max_occupancy }} <span class="text-sm font-normal text-slate-500">tamu</span></p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">View</p>
                    <p class="font-display text-xl font-bold text-slate-900 mt-1">{{ $roomType->view ?? 'Standard' }}</p>
                </div>
            </div>

            @if(!empty($amenList))
                <h2 class="font-display text-2xl font-bold text-slate-900 mb-4">Fasilitas Kamar</h2>
                <div class="flex flex-wrap gap-2 mb-10">
                    @foreach((array) $amenList as $am)
                        <span class="inline-flex items-center gap-1.5 text-sm font-medium bg-slate-100 text-slate-700 px-3 py-1.5 rounded-full">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ ucfirst(str_replace('_', ' ', $am)) }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Sticky booking sidebar --}}
        <aside class="lg:col-span-1">
            <div class="lg:sticky lg:top-28 bg-white border border-slate-200 rounded-3xl shadow-lg p-6">
                <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Mulai dari</p>
                <p class="font-display text-4xl font-bold text-slate-900 tabular-nums leading-none mt-1">Rp {{ number_format($roomType->base_rate, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">/ malam · sudah termasuk pajak</p>

                <a href="{{ route('booking.search') }}" class="mt-5 w-full inline-flex items-center justify-center gap-2 bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold rounded-xl px-6 py-3.5 shadow-lg shadow-indigo-500/40 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Cek Ketersediaan
                </a>

                <div class="mt-5 pt-5 border-t border-slate-100 space-y-2.5 text-sm text-slate-600">
                    <p class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Bebas biaya pembatalan H-1</p>
                    <p class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Konfirmasi instan</p>
                    <p class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Harga termurah dijamin</p>
                </div>
            </div>
        </aside>
    </div>
</section>

@endsection
