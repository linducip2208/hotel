@extends('panel.layout')
@section('title', 'Rate Scraper — Competitor Intel')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">Rate Scraper — Competitor Intel</h2>
        <p class="text-sm text-slate-500">Monitor harga kompetitor & dapatkan alert jika selisih > 15%</p>
    </div>
    <div class="flex gap-2">
        <form method="POST" action="{{ route('panel.rms.scraper.scrape-all') }}">
            @csrf
            <button class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700">Scrape All</button>
        </form>
    </div>
</div>

@if($alerts->isNotEmpty())
<div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
    <div class="flex items-center gap-2 mb-2">
        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <h3 class="font-semibold text-amber-800">{{ $alerts->count() }} alert belum dibaca</h3>
    </div>
    <ul class="space-y-2 text-sm text-amber-700">
        @foreach($alerts->take(5) as $alert)
        <li class="flex items-center justify-between">
            <span>{{ $alert->message }}</span>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-{{ $alert->severity === 'critical' ? 'rose' : 'amber' }}-100 text-{{ $alert->severity === 'critical' ? 'rose' : 'amber' }}-700">{{ $alert->severity }}</span>
        </li>
        @endforeach
    </ul>
    @if($alerts->count() > 5)
    <a href="{{ route('panel.rms.scraper.alerts') }}" class="text-xs text-amber-700 underline mt-2 block">Lihat semua alert</a>
    @endif
</div>
@endif

@if($targets->isEmpty())
<div class="bg-white rounded-2xl p-12 border border-slate-200 shadow-sm text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </div>
    <p class="text-slate-500 mb-4">Belum ada target kompetitor</p>
    <button onclick="document.getElementById('add-target-form').classList.toggle('hidden')" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm hover:bg-indigo-700">+ Tambah Kompetitor</button>
</div>
@endif

<form id="add-target-form" method="POST" action="{{ route('panel.rms.scraper.targets.store') }}" class="{{ $targets->isEmpty() ? 'hidden' : '' }} bg-white rounded-2xl p-5 border border-slate-200 shadow-sm mb-4">
    @csrf
    <h3 class="font-semibold text-slate-800 mb-3">Tambah Target Kompetitor</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <input name="name" placeholder="Nama Hotel" required class="border-slate-300 rounded-xl text-sm px-3 py-2">
        <input name="website_url" placeholder="URL Website" class="border-slate-300 rounded-xl text-sm px-3 py-2">
        <input name="stars" type="number" min="1" max="5" placeholder="Bintang" class="border-slate-300 rounded-xl text-sm px-3 py-2">
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
        <textarea name="ota_urls" placeholder='{"booking": "url", "agoda": "url"}' class="border-slate-300 rounded-xl text-sm px-3 py-2" rows="3"></textarea>
        <textarea name="room_type_mapping" placeholder='{"1": "Deluxe Room", "2": "Suite"}' class="border-slate-300 rounded-xl text-sm px-3 py-2" rows="3"></textarea>
    </div>
    <button class="mt-3 bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-700">Simpan</button>
</form>

<div class="grid gap-4">
    @foreach($targets as $target)
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="font-semibold text-slate-800">{{ $target->name }}</p>
                <p class="text-xs text-slate-500">{{ $target->stars ? str_repeat('*', $target->stars) : '?' }} bintang · {{ $target->distance_km ? $target->distance_km . ' km' : '' }}</p>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('panel.rms.scraper.scrape', $target->id) }}">
                    @csrf
                    <button class="bg-indigo-100 text-indigo-700 text-xs px-3 py-1.5 rounded-lg hover:bg-indigo-200">Scrape Now</button>
                </form>
            </div>
        </div>
        @if($target->logs->isNotEmpty())
        <div class="grid grid-cols-3 gap-2 text-xs">
            @foreach($target->logs->take(3) as $log)
            <div class="bg-slate-50 rounded-lg p-2">
                <p class="font-mono text-slate-600">{{ $log->scraped_for_date->format('d M') }}</p>
                <p class="font-bold text-slate-800">Rp {{ number_format($log->min_competitor_price ?? 0, 0, ',', '.') }}</p>
                @if($log->price_gap_pct)
                <p class="text-{{ $log->price_gap_pct > 0 ? 'rose' : 'emerald' }}-600">Gap {{ $log->price_gap_pct }}%</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach
</div>
@endsection
