@extends('public.layout')

@push('head')
<style>
    @keyframes floatSlow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-12px); }
    }
    @keyframes fadeSlideUp {
        0% { transform: translateY(40px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }
    @keyframes scaleIn {
        0% { transform: scale(.85); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    @keyframes pingSlow {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }
    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity .7s ease, transform .7s cubic-bezier(.16, 1, .3, 1);
    }
    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .card-lift {
        transition: transform .35s ease, box-shadow .35s ease;
    }
    .card-lift:hover {
        transform: translateY(-6px);
        box-shadow: 0 24px 48px -12px rgba(0, 0, 0, .15);
    }
    .animate-float-slow {
        animation: floatSlow 5s ease-in-out infinite;
    }
    .animate-shimmer {
        background: linear-gradient(90deg, transparent 25%, rgba(255, 255, 255, .15) 50%, transparent 75%);
        background-size: 200% 100%;
        animation: shimmer 1.8s ease-in-out infinite;
    }
</style>
@endpush

@php
    $roomTypes = \App\Models\RoomType::where('is_active', true)
        ->when($property, fn($q) => $q->where('property_id', $property->id))
        ->orderBy('display_order')
        ->orderBy('base_rate')
        ->take(3)
        ->get();

    // ALL room types (untuk filter & data lengkap di grid 100 kamar)
    $allRoomTypes = \App\Models\RoomType::where('is_active', true)
        ->when($property, fn($q) => $q->where('property_id', $property->id))
        ->orderBy('display_order')
        ->orderBy('base_rate')
        ->get();

    // Semua kamar fisik (auto-update saat admin tambah kamar baru di /panel)
    $allRooms = \App\Models\Room::where('is_active', true)
        ->when($property, fn($q) => $q->where('property_id', $property->id))
        ->with('roomType')
        ->orderBy('floor')
        ->orderBy('number')
        ->get();

    $totalRooms = $allRooms->count() ?: ($property?->total_rooms ?? 0);
    $minRate = (int) ($roomTypes->min('base_rate') ?? 0);
    $stars = (int) ($property?->star_rating ?? 0);

    $heroSlogans = [
        'Pengalaman menginap yang tak terlupakan',
        'Kenyamanan dengan sentuhan personal',
        'Tempat sempurna untuk istirahat sejenak',
    ];
    $slogan = $heroSlogans[date('z') % count($heroSlogans)];

    $amenityIcons = [
        'wifi'    => ['label' => 'Wi-Fi Cepat',     'svg' => 'M5.636 5.636a9 9 0 0112.728 0M8.464 8.464a5 5 0 017.072 0M12 12.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z'],
        'ac'      => ['label' => 'AC',              'svg' => 'M12 8v8m-4-4h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z'],
        'tv'      => ['label' => 'Smart TV',        'svg' => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12v8.25a2.25 2.25 0 01-2.25 2.25H5.25a2.25 2.25 0 01-2.25-2.25V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25'],
        'minibar' => ['label' => 'Minibar',         'svg' => 'M5 12V5a2 2 0 012-2h10a2 2 0 012 2v7M5 12h14M5 12v6a2 2 0 002 2h10a2 2 0 002-2v-6M9 8h6'],
        'parking' => ['label' => 'Parkir Gratis',   'svg' => 'M9 8h6a3 3 0 010 6H9V8zm0 0v12M5 4h14a2 2 0 012 2v14H3V6a2 2 0 012-2z'],
        'breakfast'=> ['label' => 'Sarapan',        'svg' => 'M19 13l-2 8h-10L5 13M3 13h18M3 13a4 4 0 014-4h10a4 4 0 014 4M9 9V5a3 3 0 016 0v4'],
    ];
@endphp

@section('title', $property?->name ?? config('app.name') . ' — Pesan Kamar Online')
@section('description', 'Reservasi langsung di ' . ($property?->name ?? 'hotel kami') . ' di ' . ($property?->city ?? 'Indonesia') . '. ' . $totalRooms . ' kamar, ' . $roomTypes->count() . ' tipe, mulai Rp ' . number_format($minRate, 0, ',', '.') . '/malam.')

@section('content')

{{-- ════════════════════════ HERO ════════════════════════ --}}
<section class="relative min-h-[90vh] flex items-center overflow-hidden">
    {{-- Background --}}
    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-indigo-950 to-violet-950"></div>
    <div class="absolute inset-0 opacity-40"
         style="background-image:
            radial-gradient(circle at 20% 30%, rgba(139,92,246,.5), transparent 45%),
            radial-gradient(circle at 80% 70%, rgba(236,72,153,.35), transparent 40%),
            radial-gradient(circle at 50% 110%, rgba(99,102,241,.4), transparent 50%);"></div>
    {{-- Grid texture --}}
    <div class="absolute inset-0 opacity-[0.07]"
         style="background-image:linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px),linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px);background-size:64px 64px;"></div>

    <div class="relative max-w-7xl mx-auto px-4 lg:px-8 py-32 lg:py-40 w-full">
        <div class="max-w-3xl">
            @if($stars > 0)
                <div class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur border border-white/20 rounded-full px-3 py-1.5 mb-6">
                    @for($i = 0; $i < $stars; $i++)
                        <svg class="w-3.5 h-3.5 text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                    <span class="text-xs text-white/80 font-medium ml-1">{{ $stars }}-Star Hotel</span>
                </div>
            @endif

            @if($property?->city)
                <p class="text-indigo-200/90 text-xs uppercase tracking-[0.3em] font-semibold mb-3 flex items-center gap-2">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ $property->city }}{{ $property->province ? ' · ' . $property->province : '' }}
                </p>
            @endif

            <h1 class="font-display text-5xl lg:text-7xl font-bold text-white leading-[1.05] tracking-tight mb-6">
                {{ $property?->name ?? config('app.name') }}
            </h1>
            <p class="text-lg lg:text-xl text-indigo-100/80 max-w-2xl leading-relaxed mb-10">
                {{ $slogan }}. {{ $totalRooms }} kamar dengan layanan profesional 24/7.
            </p>

            {{-- Booking widget --}}
            <form action="{{ route('booking.results') }}" method="POST"
                  class="bg-white/95 backdrop-blur rounded-2xl p-2 lg:p-3 shadow-2xl shadow-indigo-900/30 grid grid-cols-1 md:grid-cols-4 gap-2">
                @csrf
                <div class="md:col-span-1 px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-[0.18em] mb-1">Check-in</label>
                    <input type="date" name="check_in" value="{{ now()->toDateString() }}" required
                           class="w-full text-sm font-semibold text-slate-800 bg-transparent border-0 p-0 focus:ring-0">
                </div>
                <div class="md:col-span-1 px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors md:border-l border-slate-200">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-[0.18em] mb-1">Check-out</label>
                    <input type="date" name="check_out" value="{{ now()->addDay()->toDateString() }}" required
                           class="w-full text-sm font-semibold text-slate-800 bg-transparent border-0 p-0 focus:ring-0">
                </div>
                <div class="md:col-span-1 px-3 py-2 rounded-xl hover:bg-slate-50 transition-colors md:border-l border-slate-200">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-[0.18em] mb-1">Tamu</label>
                    <select name="adults" class="w-full text-sm font-semibold text-slate-800 bg-transparent border-0 p-0 focus:ring-0">
                        @foreach([1,2,3,4] as $n) <option value="{{ $n }}" {{ $n===2?'selected':'' }}>{{ $n }} dewasa</option> @endforeach
                    </select>
                </div>
                <button type="submit"
                        class="bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold rounded-xl px-6 py-3.5 shadow-lg shadow-indigo-500/40 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Cari Kamar
                </button>
            </form>

            <p class="text-xs text-white/60 mt-4">Mulai dari <span class="font-bold text-white">Rp {{ number_format($minRate, 0, ',', '.') }}</span> per malam · Bebas biaya pembatalan H-1</p>
        </div>
    </div>

    {{-- Bottom curve --}}
    <div class="absolute bottom-0 inset-x-0 h-16 bg-gradient-to-t from-stone-50 to-transparent"></div>
</section>

{{-- ════════════════════════ STATS STRIP ════════════════════════ --}}
<section class="relative -mt-12 z-10 max-w-6xl mx-auto px-4 lg:px-8">
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-900/5 border border-slate-100 p-6 lg:p-8 grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
        <div class="text-center reveal">
            <div class="font-display text-3xl lg:text-4xl font-bold text-indigo-600 tabular-nums">{{ $totalRooms }}+</div>
            <p class="text-xs text-slate-500 uppercase tracking-wider mt-1 font-semibold">Kamar</p>
        </div>
        <div class="text-center border-l border-slate-100 lg:border-l reveal">
            <div class="font-display text-3xl lg:text-4xl font-bold text-violet-600 tabular-nums">{{ $roomTypes->count() }}</div>
            <p class="text-xs text-slate-500 uppercase tracking-wider mt-1 font-semibold">Tipe Kamar</p>
        </div>
        <div class="text-center lg:border-l border-slate-100 reveal">
            <div class="font-display text-3xl lg:text-4xl font-bold text-emerald-600 tabular-nums">24/7</div>
            <p class="text-xs text-slate-500 uppercase tracking-wider mt-1 font-semibold">Layanan</p>
        </div>
        <div class="text-center border-l border-slate-100 reveal">
            <div class="font-display text-3xl lg:text-4xl font-bold text-amber-600 tabular-nums">4.8★</div>
            <p class="text-xs text-slate-500 uppercase tracking-wider mt-1 font-semibold">Rating Tamu</p>
        </div>
    </div>
</section>

{{-- ════════════════════════ PROBLEM - SOLUTION ════════════════════════ --}}
<section class="max-w-7xl mx-auto px-4 lg:px-8 py-20 lg:py-28">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
        {{-- Problem --}}
        <div class="reveal bg-white rounded-3xl border border-rose-200 shadow-sm overflow-hidden card-lift">
            <div class="bg-gradient-to-br from-rose-500 to-rose-700 px-8 py-5">
                <p class="text-white text-xs font-bold uppercase tracking-[0.25em]">Masalah yang Umum Terjadi</p>
                <h3 class="font-display text-2xl lg:text-3xl font-bold text-white mt-1">Excel &amp; Catatan Manual</h3>
            </div>
            <div class="p-6 lg:p-8 space-y-5">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <p class="text-slate-700"><strong>Reservasi double-booking</strong> karena catatan booking manual di kertas atau Excel.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <p class="text-slate-700"><strong>Laporan butuh 3 jam</strong> — rekonsiliasi manual pendapatan harian, okupansi, dan AR/AP.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <p class="text-slate-700"><strong>Stok kamar tidak real-time</strong> — front office tidak tahu kamar mana yang ready sebelum tamu datang.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <p class="text-slate-700"><strong>Tidak terintegrasi OTA</strong> — update manual ke Traveloka, Booking.com, Agoda satu per satu.</p>
                </div>
            </div>
        </div>

        {{-- Solution --}}
        <div class="reveal bg-white rounded-3xl border border-indigo-200 shadow-sm overflow-hidden card-lift">
            <div class="bg-gradient-to-br from-indigo-600 to-violet-700 px-8 py-5">
                <p class="text-white text-xs font-bold uppercase tracking-[0.25em]">Solusi Kami</p>
                <h3 class="font-display text-2xl lg:text-3xl font-bold text-white mt-1">HotelHub HMS</h3>
            </div>
            <div class="p-6 lg:p-8 space-y-5">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-slate-700"><strong>Satu dashboard, semua otomatis.</strong> Booking real-time dengan ketersediaan kamar live.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-slate-700"><strong>Laporan 1 klik</strong> — revenue, okupansi, AR/AP otomatis tanpa rekonsiliasi manual.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-slate-700"><strong>Terintegrasi OTA &amp; channel manager</strong> — update otomatis ke semua platform booking.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-slate-700"><strong>Front Office, POS, Accounting, HR</strong> — semua dalam satu sistem terpadu.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════ FEATURED ROOM TYPES ════════════════════════ --}}
<section id="rooms" class="max-w-7xl mx-auto px-4 lg:px-8 py-20 lg:py-28">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-12 reveal">
        <div>
            <p class="text-xs font-bold text-indigo-600 uppercase tracking-[0.25em] mb-3">Akomodasi</p>
            <h2 class="font-display text-3xl lg:text-5xl font-bold text-slate-900 tracking-tight">Tipe Kamar Pilihan</h2>
            <p class="text-slate-600 mt-3 max-w-2xl">Pilih kamar yang paling sesuai dengan kebutuhan Anda. Setiap kamar dilengkapi fasilitas modern dan kenyamanan optimal.</p>
        </div>
        <a href="/rooms" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-semibold text-sm">
            Lihat semua tipe
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
    </div>

    @if($roomTypes->isEmpty())
        <p class="text-slate-500 text-center py-12">Belum ada tipe kamar yang dipublikasikan.</p>
    @else
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
        @foreach($roomTypes as $i => $rt)
            @php
                $gradients = [
                    ['from-indigo-500', 'to-violet-600'],
                    ['from-rose-500', 'to-amber-500'],
                    ['from-emerald-500', 'to-teal-600'],
                ];
                [$from, $to] = $gradients[$i % 3];
                $amenList = is_string($rt->amenities) ? json_decode($rt->amenities, true) : ($rt->amenities ?? []);
                $photos = is_array($rt->photos) ? $rt->photos : (is_string($rt->photos) ? json_decode($rt->photos, true) : []);
                $coverPhoto = $photos[0] ?? null;
            @endphp
            <article class="reveal card-lift group bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all overflow-hidden flex flex-col" style="transition-delay: {{ $i * 100 }}ms;">
                {{-- Image / gradient cover --}}
                <div class="relative h-56 overflow-hidden {{ $coverPhoto ? 'bg-slate-200' : 'bg-gradient-to-br '.$from.' '.$to }}">
                    @if($coverPhoto)
                        <img src="{{ $coverPhoto }}" alt="{{ $rt->name }}" loading="lazy"
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/10 to-transparent"></div>
                    @else
                        <div class="absolute inset-0 opacity-25"
                             style="background-image:radial-gradient(circle at 30% 30%, rgba(255,255,255,.5), transparent 50%);"></div>
                        <svg class="absolute right-5 -bottom-5 w-32 h-32 text-white/20" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    @endif
                    <div class="absolute top-4 left-4 inline-flex items-center gap-1.5 bg-white/20 backdrop-blur text-white text-xs font-semibold rounded-full px-2.5 py-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                        Tersedia
                    </div>
                    @if($rt->size_sqm)
                        <div class="absolute bottom-4 left-4 text-white drop-shadow">
                            <p class="font-display text-3xl font-bold leading-none">{{ $rt->size_sqm }}<span class="text-base font-normal opacity-80">m²</span></p>
                        </div>
                    @endif
                </div>

                {{-- Body --}}
                <div class="p-6 flex flex-col flex-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.18em] mb-1">{{ $rt->code }}</p>
                    <h3 class="font-display text-xl font-bold text-slate-900 mb-2">{{ $rt->name }}</h3>
                    <p class="text-sm text-slate-600 leading-relaxed mb-4 flex-1">{{ $rt->description ?? 'Kamar nyaman dengan fasilitas modern.' }}</p>

                    {{-- Quick specs --}}
                    <div class="flex items-center gap-3 text-xs text-slate-500 mb-4 pb-4 border-b border-slate-100">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $rt->max_occupancy }} tamu
                        </span>
                        @if($rt->bed_config)
                            <span class="text-slate-300">·</span>
                            <span>{{ $rt->bed_config }}</span>
                        @endif
                    </div>

                    {{-- Amenities --}}
                    @if(!empty($amenList))
                        <div class="flex flex-wrap gap-1.5 mb-5">
                            @foreach(array_slice((array) $amenList, 0, 4) as $am)
                                <span class="text-[10px] uppercase tracking-wider font-semibold bg-slate-100 text-slate-600 px-2 py-1 rounded">{{ $am }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Price + CTA --}}
                    <div class="flex items-end justify-between">
                        <div>
                            <p class="text-xs text-slate-400">Mulai dari</p>
                            <p class="font-display text-2xl font-bold text-slate-900 tabular-nums leading-none">Rp {{ number_format($rt->base_rate, 0, ',', '.') }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">/ malam · sudah termasuk pajak</p>
                        </div>
                        <a href="{{ route('rooms.show', $rt->slug ?? $rt->id) }}"
                           class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-700 px-3 py-2 rounded-lg hover:bg-indigo-50 transition-colors">
                            Detail
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    @endif
</section>

{{-- ════════════════════════ ALL ROOMS (100 kamar individual, auto-update dari DB) ════════════════════════ --}}
<section id="all-rooms" x-data="{ filter: 'all' }" class="bg-stone-100/60 border-y border-slate-200 py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-10 reveal">
            <div>
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-[0.25em] mb-3">Inventory Lengkap</p>
                <h2 class="font-display text-3xl lg:text-5xl font-bold text-slate-900 tracking-tight">
                    Semua <span class="text-indigo-600 tabular-nums">{{ $totalRooms }}</span> Kamar Kami
                </h2>
                <p class="text-slate-600 mt-3 max-w-2xl">Setiap kamar dengan foto, fasilitas, harga, dan kategorinya. Daftar ini otomatis ter-update setiap kali kami menambah unit baru.</p>
            </div>

            {{-- Filter pills --}}
            <div class="flex flex-wrap gap-2">
                <button @click="filter='all'"
                        :class="filter==='all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'bg-white text-slate-700 border border-slate-200 hover:border-indigo-300'"
                        class="text-xs font-semibold px-3 py-1.5 rounded-full transition-all">
                    Semua ({{ $allRooms->count() }})
                </button>
                @foreach($allRoomTypes as $rt)
                    @php $cnt = $allRooms->where('room_type_id', $rt->id)->count(); @endphp
                    <button @click="filter='{{ $rt->code }}'"
                            :class="filter==='{{ $rt->code }}' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'bg-white text-slate-700 border border-slate-200 hover:border-indigo-300'"
                            class="text-xs font-semibold px-3 py-1.5 rounded-full transition-all">
                        {{ $rt->name }} ({{ $cnt }})
                    </button>
                @endforeach
            </div>
        </div>

        @php
            $amenityIcons = [
                'wifi'       => '📶', 'ac'         => '❄️', 'tv'         => '📺',
                'minibar'    => '🍷', 'safe'       => '🔒', 'balcony'    => '🌤',
                'living_room'=> '🛋', 'bathtub'    => '🛁', 'breakfast'  => '🍳',
                'parking'    => '🅿️',
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 lg:gap-4">
            @foreach($allRooms as $i => $room)
                @php
                    $rt = $room->roomType;
                    if (! $rt) continue;
                    $rtCode = $rt->code ?? 'XXX';
                    // Guaranteed photo via helper — pool curated + Picsum fallback
                    $photo = \App\Support\RoomPhotos::forRoom($room);
                    $rtAmenities = is_array($rt->amenities)
                        ? $rt->amenities
                        : (is_string($rt->amenities) ? (json_decode($rt->amenities, true) ?? []) : []);
                @endphp
                <a href="{{ route('rooms.show', $rt->slug) }}"
                   x-show="filter==='all' || filter==='{{ $rtCode }}'"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 scale-95"
                   x-transition:enter-end="opacity-100 scale-100"
                   class="group block bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all">
                    <div class="relative aspect-[4/3] bg-gradient-to-br from-indigo-100 to-violet-100 overflow-hidden">
                        <img src="{{ $photo }}" alt="Kamar {{ $room->number }} — {{ $rt->name }}" loading="lazy"
                             onerror="this.onerror=null; this.src='https://picsum.photos/seed/room-{{ $room->id }}/800/600';"
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        {{-- Top: room # + status --}}
                        <div class="absolute inset-x-0 top-0 p-2 flex items-start justify-between">
                            <span class="bg-black/65 backdrop-blur text-white text-[10px] font-bold tracking-wide px-2 py-1 rounded">
                                #{{ $room->number }}
                            </span>
                            <span class="inline-flex items-center gap-1 bg-white/90 backdrop-blur text-[10px] font-semibold px-1.5 py-0.5 rounded shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full {{ ($room->fo_status === 'occupied') ? 'bg-rose-500' : (($room->fo_status === 'reserved') ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                                <span class="text-slate-700 hidden sm:inline">
                                    {{ ($room->fo_status === 'occupied') ? 'Terisi' : (($room->fo_status === 'reserved') ? 'Dipesan' : 'Tersedia') }}
                                </span>
                            </span>
                        </div>
                        {{-- Bottom: type label --}}
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-2.5">
                            <p class="text-white text-[11px] font-semibold leading-tight truncate">{{ $rt->name }}</p>
                            <p class="text-white/70 text-[10px]">Lt. {{ $room->floor }} · {{ $rt->size_sqm ?? '–' }}m² · {{ $rt->max_occupancy }} tamu</p>
                        </div>
                    </div>

                    <div class="p-3 space-y-2">
                        {{-- Fasilitas (icon-only untuk kompak) --}}
                        @if(!empty($rtAmenities))
                            <div class="flex flex-wrap gap-1 min-h-[20px]">
                                @foreach(array_slice($rtAmenities, 0, 5) as $am)
                                    <span class="text-[10px]" title="{{ ucfirst(str_replace('_', ' ', $am)) }}">{{ $amenityIcons[$am] ?? '•' }}</span>
                                @endforeach
                                @if(count($rtAmenities) > 5)
                                    <span class="text-[9px] text-slate-400">+{{ count($rtAmenities) - 5 }}</span>
                                @endif
                            </div>
                        @endif
                        {{-- Harga + kategori --}}
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-[9px] text-slate-400 uppercase tracking-wider">{{ $rt->code }}</p>
                                <p class="font-display text-sm font-bold text-slate-900 tabular-nums leading-none">
                                    Rp {{ number_format($rt->base_rate, 0, ',', '.') }}
                                </p>
                            </div>
                            <svg class="w-4 h-4 text-indigo-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if($allRooms->isEmpty())
            <div class="text-center py-20 bg-white rounded-3xl border border-slate-200">
                <p class="text-slate-500">Belum ada kamar yang dipublikasikan.</p>
            </div>
        @endif

        <div class="mt-10 flex items-center justify-center gap-3 reveal">
            <a href="/booking" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-semibold shadow-lg shadow-indigo-500/30 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Pesan Kamar
            </a>
            <a href="/rooms" class="inline-flex items-center gap-2 bg-white border border-slate-300 hover:border-indigo-400 text-slate-700 hover:text-indigo-700 px-5 py-3 rounded-xl font-semibold transition-colors">
                Detail Per Tipe
            </a>
        </div>
    </div>
</section>

{{-- ════════════════════════ AMENITIES / WHY CHOOSE US ════════════════════════ --}}
<section class="bg-white border-y border-slate-100 py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14 reveal">
            <p class="text-xs font-bold text-violet-600 uppercase tracking-[0.25em] mb-3">Mengapa Kami</p>
            <h2 class="font-display text-3xl lg:text-5xl font-bold text-slate-900 tracking-tight">Fasilitas &amp; Layanan</h2>
            <p class="text-slate-600 mt-3">Setiap detail kami persiapkan untuk membuat masa menginap Anda berkesan.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
            @php
                $features = [
                    ['icon' => 'M5.636 5.636a9 9 0 0112.728 0M8.464 8.464a5 5 0 017.072 0M12 12.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z', 'title' => 'Wi-Fi Cepat', 'desc' => 'Internet fiber 100Mbps di seluruh area hotel, gratis untuk semua tamu.', 'color' => 'indigo'],
                    ['icon' => 'M12 8v8m-4-4h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z', 'title' => 'AC Modern', 'desc' => 'Pendingin ruangan inverter di setiap kamar untuk kenyamanan optimal.', 'color' => 'sky'],
                    ['icon' => 'M19 13l-2 8h-10L5 13M3 13h18M3 13a4 4 0 014-4h10a4 4 0 014 4M9 9V5a3 3 0 016 0v4', 'title' => 'Sarapan Lokal', 'desc' => 'Sarapan tradisional Indonesia dengan menu yang berganti setiap hari.', 'color' => 'amber'],
                    ['icon' => 'M9 8h6a3 3 0 010 6H9V8zm0 0v12M5 4h14a2 2 0 012 2v14H3V6a2 2 0 012-2z', 'title' => 'Parkir Gratis', 'desc' => 'Area parkir luas dan aman dengan CCTV 24 jam, tanpa biaya tambahan.', 'color' => 'emerald'],
                    ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'title' => 'Layanan 24/7', 'desc' => 'Resepsionis dan room service siap melayani sepanjang hari.', 'color' => 'rose'],
                    ['icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'title' => 'Lokasi Strategis', 'desc' => 'Dekat pusat kota, akses mudah ke wisata kuliner dan transportasi.', 'color' => 'violet'],
                ];
            @endphp
            @foreach($features as $f)
                <div class="reveal card-lift group bg-stone-50 hover:bg-white rounded-2xl p-6 hover:shadow-lg hover:-translate-y-0.5 border border-transparent hover:border-{{ $f['color'] }}-200 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-{{ $f['color'] }}-100 to-{{ $f['color'] }}-200 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-{{ $f['color'] }}-700" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/></svg>
                    </div>
                    <h3 class="font-display text-lg font-bold text-slate-900 mb-1.5">{{ $f['title'] }}</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">{{ $f['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ════════════════════════ USE CASES ════════════════════════ --}}
<section class="max-w-7xl mx-auto px-4 lg:px-8 py-20 lg:py-28">
    <div class="text-center max-w-2xl mx-auto mb-14 reveal">
        <p class="text-xs font-bold text-indigo-600 uppercase tracking-[0.25em] mb-3">Cocok Untuk</p>
        <h2 class="font-display text-3xl lg:text-5xl font-bold text-slate-900 tracking-tight">Berbagai Jenis Properti</h2>
        <p class="text-slate-600 mt-3">HotelHub HMS fleksibel untuk berbagai skala dan tipe akomodasi di Indonesia.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $useCases = [
                [
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                    'title' => 'Hotel Butik',
                    'desc' => '5–30 kamar. Pemilik butuh laporan simpel, okupansi real-time, dan booking engine langsung.',
                ],
                [
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>',
                    'title' => 'Villa & Guesthouse',
                    'desc' => '1–10 unit. Butuh channel manager + booking engine + integrasi OTA.',
                ],
                [
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2 M12 3v12m0 0l-4-4m4 4l4-4"/></svg>',
                    'title' => 'Hotel Bintang 2–3',
                    'desc' => '30–100 kamar. Butuh POS + accounting + HR lengkap + manajemen multi-departemen.',
                ],
                [
                    'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>',
                    'title' => 'Serviced Apartment',
                    'desc' => 'Long-stay. Butuh folio tamu + deposit management + AR/AP + recurring billing.',
                ],
            ];
        @endphp
        @foreach($useCases as $uc)
            <div class="reveal card-lift bg-white rounded-2xl border border-slate-200 p-6 hover:border-indigo-200 hover:shadow-lg transition-all">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-100 to-violet-100 flex items-center justify-center mb-4 text-indigo-600">
                    {!! $uc['icon'] !!}
                </div>
                <h3 class="font-display text-lg font-bold text-slate-900 mb-2">{{ $uc['title'] }}</h3>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $uc['desc'] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ════════════════════════ COMPETITOR COMPARISON (compact) ════════════════════════ --}}
<section class="bg-white border-y border-slate-100 py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10 reveal">
            <p class="text-xs font-bold text-indigo-600 uppercase tracking-[0.25em] mb-3">Mengapa HotelHub HMS?</p>
            <h2 class="font-display text-3xl lg:text-5xl font-bold text-slate-900 tracking-tight">Satu Sistem, Semua Kebutuhan Hotel</h2>
            <p class="text-slate-600 mt-3">Dibandingkan dengan solusi open-source hotel lainnya, HotelHub HMS adalah satu-satunya yang menggabungkan PMS + Channel Manager + Accounting + POS + Indonesia Compliance dalam satu platform terpadu.</p>
        </div>

        <div class="reveal bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200">
                            <th class="text-left py-3.5 px-5 text-xs font-bold text-slate-500 uppercase tracking-[0.1em] w-56">Fitur Utama</th>
                            <th class="py-3.5 px-4 text-center text-xs font-bold text-white uppercase tracking-[0.08em] bg-indigo-600 rounded-t-lg">HotelHub HMS</th>
                            <th class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-[0.08em]">QloApps</th>
                            <th class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-[0.08em]">HotelDruid</th>
                            <th class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-[0.08em]">FewohBee</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $rows = [
                                ['PMS Lengkap (FO + HK + Night Audit)',    false, false, false],
                                ['Channel Manager (Booking.com, Agoda, Traveloka)', false, false, false],
                                ['Accounting GL/AR/AP + Trial Balance',   false, false, false],
                                ['Restaurant POS + Kitchen Display',       false, true,  false],
                                ['PB1 + PPN + e-Faktur Coretax',           false, false, false],
                                ['Lapor WNA Imigrasi + KTP OCR',           false, false, false],
                                ['Revenue Management (Dynamic + AI)',      false, false, false],
                                ['AI Tools BYOK (Translate, Concierge)',   false, false, false],
                                ['HR + Payroll + BPJS/PPh21',              false, false, false],
                                ['White-Label + Standalone Install',       true,  true,  false],
                                ['Programmatic SEO Built-in',              false, false, false],
                                ['Mobile Responsive + PWA',                true,  false, true],
                            ];
                            $qloScore = count(array_filter(array_column($rows, 1)));
                            $hdruidScore = count(array_filter(array_column($rows, 2)));
                            $fewohScore = count(array_filter(array_column($rows, 3)));
                        @endphp
                        @foreach ($rows as $row)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-5 text-slate-700 font-medium text-xs">{{ $row[0] }}</td>
                                <td class="py-3 px-4 text-center bg-indigo-50/60">
                                    <svg class="w-4 h-4 text-emerald-500 mx-auto" fill="none" stroke="currentColor" stroke-width="2.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </td>
                                @for ($j = 1; $j <= 3; $j++)
                                    <td class="py-3 px-4 text-center">
                                        @if ($row[$j])
                                            <svg class="w-4 h-4 text-emerald-500 mx-auto" fill="none" stroke="currentColor" stroke-width="2.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5 text-rose-400 mx-auto" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-indigo-50 border-t-2 border-indigo-200">
                            <td class="py-3.5 px-5 text-xs font-bold text-slate-700 uppercase tracking-[0.08em]">Total (dari 12)</td>
                            <td class="py-3.5 px-4 text-center bg-indigo-600">
                                <span class="text-white font-bold text-sm">12/12</span>
                            </td>
                            <td class="py-3.5 px-4 text-center"><span class="text-slate-500 font-semibold text-xs">{{ $qloScore }}/12</span></td>
                            <td class="py-3.5 px-4 text-center"><span class="text-slate-500 font-semibold text-xs">{{ $hdruidScore }}/12</span></td>
                            <td class="py-3.5 px-4 text-center"><span class="text-slate-500 font-semibold text-xs">{{ $fewohScore }}/12</span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-4">
            <a href="/docs#perbandingan-kompetitor" class="text-indigo-600 font-medium hover:underline">Lihat perbandingan lengkap 54 fitur &rarr;</a>
        </p>
    </div>
</section>

{{-- ════════════════════════ PRICING ════════════════════════ --}}
<section class="bg-stone-100/60 border-y border-slate-200 py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14 reveal">
            <p class="text-xs font-bold text-indigo-600 uppercase tracking-[0.25em] mb-3">Harga</p>
            <h2 class="font-display text-3xl lg:text-5xl font-bold text-slate-900 tracking-tight">Pilih Paket Sesuai Kebutuhan</h2>
            <p class="text-slate-600 mt-3">Mulai dari gratis untuk satu properti. Upgrade sesuai skala bisnis Anda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 max-w-5xl mx-auto">
            {{-- Starter --}}
            <div class="reveal card-lift bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 lg:p-8 text-center border-b border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Starter</p>
                    <p class="font-display text-4xl lg:text-5xl font-bold text-slate-900 mb-1">Rp 0</p>
                    <p class="text-sm text-slate-500">per bulan</p>
                </div>
                <div class="p-6 lg:p-8 flex flex-col flex-1">
                    <ul class="space-y-3 mb-8 flex-1">
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            1 properti
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Front Office dasar
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Maks 200 booking/bulan
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Laporan dasar
                        </li>
                    </ul>
                    <a href="/register" class="block text-center w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 rounded-xl transition-colors text-sm">
                        Mulai Gratis
                    </a>
                </div>
            </div>

            {{-- Growth — Highlighted --}}
            <div class="reveal card-lift bg-white rounded-3xl border-2 border-indigo-500 shadow-xl shadow-indigo-500/10 overflow-hidden flex flex-col relative">
                <div class="absolute top-0 inset-x-0 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-center text-xs font-bold py-1.5 uppercase tracking-[0.15em]">
                    Paling Populer
                </div>
                <div class="p-6 lg:p-8 text-center border-b border-slate-100 pt-10">
                    <p class="text-xs font-bold text-indigo-600 uppercase tracking-[0.2em] mb-2">Growth</p>
                    <p class="font-display text-4xl lg:text-5xl font-bold text-slate-900 mb-1">Rp 499rb</p>
                    <p class="text-sm text-slate-500">per bulan</p>
                </div>
                <div class="p-6 lg:p-8 flex flex-col flex-1">
                    <ul class="space-y-3 mb-8 flex-1">
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            3 properti
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Semua modul (FO, POS, Accounting, HR)
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Unlimited booking
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Laporan lengkap + export
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Channel manager
                        </li>
                    </ul>
                    <a href="/register" class="block text-center w-full bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-indigo-500/30 transition-all text-sm">
                        Coba Gratis 14 Hari
                    </a>
                </div>
            </div>

            {{-- Enterprise --}}
            <div class="reveal card-lift bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-6 lg:p-8 text-center border-b border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Enterprise</p>
                    <p class="font-display text-4xl lg:text-5xl font-bold text-slate-900 mb-1">Rp 1.5jt</p>
                    <p class="text-sm text-slate-500">per bulan</p>
                </div>
                <div class="p-6 lg:p-8 flex flex-col flex-1">
                    <ul class="space-y-3 mb-8 flex-1">
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Unlimited properti
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Whitelabel branding
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            API akses penuh
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Priority support 24/7
                        </li>
                        <li class="flex items-start gap-2 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Custom development
                        </li>
                    </ul>
                    <a href="/contact" class="block text-center w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 rounded-xl transition-colors text-sm">
                        Hubungi Sales
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════ TESTIMONIAL / TRUST ════════════════════════ --}}
<section class="max-w-7xl mx-auto px-4 lg:px-8 py-20 lg:py-28">
    <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
        <div class="reveal">
            <p class="text-xs font-bold text-emerald-600 uppercase tracking-[0.25em] mb-3">Apa Kata Mereka</p>
            <h2 class="font-display text-3xl lg:text-5xl font-bold text-slate-900 tracking-tight leading-tight">
                Tamu kami <span class="text-emerald-600">menyukai</span> pengalamannya.
            </h2>
            <p class="text-slate-600 mt-4 leading-relaxed">Dari liburan keluarga hingga perjalanan bisnis, kami selalu berusaha memberikan pelayanan yang melebihi ekspektasi. Inilah beberapa cerita dari mereka.</p>
            <div class="flex items-center gap-6 mt-8">
                <div>
                    <p class="font-display text-4xl font-bold text-slate-900">4.8<span class="text-amber-400">★</span></p>
                    <p class="text-xs text-slate-500 mt-1">Berdasarkan 200+ ulasan</p>
                </div>
                <div class="h-12 w-px bg-slate-200"></div>
                <div>
                    <p class="font-display text-4xl font-bold text-slate-900">95%</p>
                    <p class="text-xs text-slate-500 mt-1">Tamu yang merekomendasikan</p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @php
                $reviews = [
                    ['name' => 'Maria K.', 'role' => 'Family Trip', 'text' => 'Pelayanannya luar biasa! Staff sangat ramah dan kamar bersih. Anak-anak juga senang dengan sarapannya.', 'rating' => 5],
                    ['name' => 'Andi S.',  'role' => 'Business',    'text' => 'Lokasi strategis untuk meeting. Wi-Fi cepat dan kamar tenang — produktivitas saya meningkat selama menginap di sini.', 'rating' => 5],
                    ['name' => 'Linda W.', 'role' => 'Honeymoon',   'text' => 'Suasananya romantis, dekorasi kamar elegan. Akan kembali untuk anniversary tahun depan!', 'rating' => 5],
                ];
            @endphp
            @foreach($reviews as $rv)
                <div class="reveal card-lift bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 text-white flex items-center justify-center font-bold text-sm shrink-0">{{ strtoupper(substr($rv['name'], 0, 1)) }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <p class="font-semibold text-slate-900 text-sm">{{ $rv['name'] }}</p>
                                <span class="text-[10px] uppercase tracking-wider font-semibold text-slate-400">{{ $rv['role'] }}</span>
                            </div>
                            <div class="flex gap-0.5 mb-2">
                                @for($i = 0; $i < $rv['rating']; $i++)
                                    <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed">"{{ $rv['text'] }}"</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ════════════════════════ DEMO ACCOUNTS ════════════════════════ --}}
<section class="bg-white border-y border-slate-100 py-20 lg:py-28">
    <div class="max-w-5xl mx-auto px-4 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 reveal">
            <p class="text-xs font-bold text-indigo-600 uppercase tracking-[0.25em] mb-3">Coba Sendiri</p>
            <h2 class="font-display text-3xl lg:text-5xl font-bold text-slate-900 tracking-tight">Akun Demo</h2>
            <p class="text-slate-600 mt-3">Gunakan akun berikut untuk eksplorasi panel admin HotelHub HMS. Semua data adalah data demo yang bisa Anda reset kapan saja.</p>
        </div>

        <div class="reveal bg-slate-50 rounded-3xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-200/60 text-left">
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-[0.12em]">Role</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-[0.12em]">Email</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-[0.12em]">Password</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-[0.12em] hidden sm:table-cell">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @php
                            $demoAccounts = [
                                ['role' => 'Admin',        'email' => 'admin@hotel.test',      'password' => 'password', 'desc' => 'Akses penuh semua fitur, manajemen user, dan konfigurasi sistem.'],
                                ['role' => 'Front Office', 'email' => 'fo@hotel.test',          'password' => 'password', 'desc' => 'Check-in/out, manajemen reservasi, room assignment, dan folio tamu.'],
                                ['role' => 'Housekeeping', 'email' => 'hk@hotel.test',          'password' => 'password', 'desc' => 'Status kamar, task cleaning, inventaris linen, dan laporan kerusakan.'],
                                ['role' => 'Accounting',   'email' => 'accounting@hotel.test',  'password' => 'password', 'desc' => 'Invoice, payment, journal entry, AR/AP, dan laporan keuangan.'],
                                ['role' => 'Manager',      'email' => 'manager@hotel.test',     'password' => 'password', 'desc' => 'Dashboard overview, laporan revenue, okupansi, dan approval transaksi.'],
                            ];
                        @endphp
                        @foreach($demoAccounts as $acc)
                            <tr class="hover:bg-white transition-colors">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-slate-900">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        {{ $acc['role'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-600">{{ $acc['email'] }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-600">{{ $acc['password'] }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 hidden sm:table-cell">{{ $acc['desc'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center mt-8 reveal">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-semibold px-6 py-3 rounded-xl shadow-lg shadow-indigo-500/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Login ke Panel Demo
            </a>
        </div>
    </div>
</section>

{{-- ════════════════════════ BIG CTA ════════════════════════ --}}
<section class="px-4 lg:px-8 pb-20 lg:pb-28">
    <div class="max-w-7xl mx-auto reveal relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-violet-700 to-fuchsia-700 px-8 py-16 lg:px-16 lg:py-20 shadow-2xl shadow-indigo-900/30">
        <div class="absolute inset-0 opacity-30"
             style="background-image:radial-gradient(circle at 20% 80%, rgba(255,255,255,.4), transparent 40%),radial-gradient(circle at 80% 20%, rgba(236,72,153,.5), transparent 50%);"></div>
        <div class="relative grid lg:grid-cols-2 gap-8 items-center">
            <div>
                <h2 class="font-display text-3xl lg:text-5xl font-bold text-white tracking-tight leading-tight mb-4">Siap untuk pengalaman menginap terbaik?</h2>
                <p class="text-indigo-100/90 text-lg leading-relaxed mb-2">Booking langsung di website kami dan dapatkan harga terbaik — tanpa biaya perantara.</p>
                <p class="text-white font-semibold">Mulai dari <span class="font-display text-2xl">Rp {{ number_format($minRate, 0, ',', '.') }}</span> / malam</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 lg:justify-end">
                <a href="/booking"
                   class="inline-flex items-center justify-center gap-2 bg-white text-indigo-700 hover:bg-slate-100 font-bold text-base px-7 py-4 rounded-2xl shadow-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Pesan Sekarang
                </a>
                <a href="/rooms"
                   class="inline-flex items-center justify-center gap-2 bg-white/10 backdrop-blur border border-white/30 text-white hover:bg-white/20 font-semibold text-base px-7 py-4 rounded-2xl transition-colors">
                    Lihat Kamar
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Scroll reveal --}}
<script>
(function(){
    var observer = new IntersectionObserver(function(entries){
        entries.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('visible'); } });
    }, { threshold: 0.15, rootMargin: '0px 0px -30px 0px' });
    document.querySelectorAll('.reveal').forEach(function(el){ observer.observe(el); });
})();
</script>

@endsection
