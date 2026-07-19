@extends('panel.layout')
@section('title', 'Cross Property — Tamu')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Cross-Property Guest Profile</h1>
    <p class="text-sm text-gray-500 mt-0.5">Cari profil tamu di seluruh properti dalam jaringan Anda</p>
</div>

@if(!empty($profile))
{{-- Unified Profile View --}}
<div class="mb-6">
    <a href="{{ route('panel.guests.cross-property') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold inline-flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Pencarian
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Profile Header --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                    {{ strtoupper(substr($profile['name'], 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $profile['name'] }}</h2>
                    <p class="text-sm text-gray-400">{{ $profile['email'] ?? $profile['phone'] ?? 'Kontak tidak tersedia' }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        @if($profile['is_vip'])
                        <span class="text-xs bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full font-semibold">VIP</span>
                        @endif
                        @if($profile['is_blacklisted'])
                        <span class="text-xs bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded-full font-semibold">Blacklisted</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-indigo-600">{{ $profile['total_stays'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Total Stay</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-emerald-600">Rp {{ number_format($profile['total_spent'], 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">Total Spent</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-amber-600">{{ $profile['avg_rating'] ?? '-' }}</p>
                    <p class="text-xs text-gray-400 mt-1">Avg Rating</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-violet-600">{{ $profile['last_visit'] ? \Carbon\Carbon::parse($profile['last_visit'])->format('M y') : '-' }}</p>
                    <p class="text-xs text-gray-400 mt-1">Kunjungan Terakhir</p>
                </div>
            </div>
        </div>

        {{-- By Property --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Riwayat per Properti</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Properti</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stay</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Spent</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kunjungan Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($profile['by_property'] as $propName => $data)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-5 py-3 font-semibold text-gray-900">{{ $propName }}</td>
                            <td class="px-5 py-3 text-center">{{ $data['stays'] }}</td>
                            <td class="px-5 py-3 text-right font-medium">Rp {{ number_format($data['total_spent'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-xs text-gray-500">{{ \Carbon\Carbon::parse($data['last_visit'])->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right --}}
    <div class="space-y-6">
        @if($profile['preferences'])
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <h3 class="font-bold text-gray-900 mb-3">Preferensi</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($profile['preferences'] as $pref)
                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">{{ is_array($pref) ? implode(': ', $pref) : $pref }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@else
{{-- Search Form --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6 mb-6">
    <form method="GET" action="{{ route('panel.guests.cross-property') }}" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[300px]">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Cari Tamu di Semua Properti</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama, email, atau telepon..."
                   class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <button type="submit"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-3 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Cari
        </button>
    </form>
</div>

@if(request()->filled('q') && empty($results))
<div class="bg-white rounded-2xl border border-gray-100 shadow-card p-8 text-center">
    <p class="text-gray-400">Tidak ada tamu ditemukan di seluruh properti dengan kata kunci "{{ request('q') }}"</p>
</div>
@endif

@if(!empty($results))
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-lg font-bold text-gray-900">Hasil Pencarian: {{ request('q') }}</h3>
        <p class="text-xs text-gray-400 mt-0.5">{{ count($results) }} profil ditemukan</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Tamu</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kontak</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Properti</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Stay</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kunjungan Terakhir</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($results as $r)
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-5 py-3 font-semibold text-gray-900">{{ $r['name'] }}</td>
                    <td class="px-5 py-3">
                        <p class="text-xs text-gray-600">{{ $r['email'] ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $r['phone'] ?? '-' }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach($r['properties'] as $pname)
                            <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full font-medium">{{ $pname }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-5 py-3 text-center font-semibold">{{ $r['total_stays'] }}</td>
                    <td class="px-5 py-3 text-right text-xs text-gray-500">{{ $r['last_visit'] ? \Carbon\Carbon::parse($r['last_visit'])->format('d M Y') : '-' }}</td>
                    <td class="px-5 py-3 text-right">
                        <form method="GET" action="{{ route('panel.guests.cross-property.profile') }}">
                            @foreach($r['guest_ids'] as $gid)
                            <input type="hidden" name="ids[]" value="{{ $gid }}">
                            @endforeach
                            <button type="submit" class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold">Lihat Profil</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(!request()->filled('q'))
<div class="bg-white rounded-2xl border border-gray-100 shadow-card p-12 text-center">
    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
    <p class="text-gray-500 font-semibold">Cari tamu di seluruh properti</p>
    <p class="text-sm text-gray-400 mt-1">Gunakan kolom pencarian untuk menemukan tamu berdasarkan nama, email, atau telepon di seluruh properti dalam jaringan Anda</p>
</div>
@endif
@endif
@endsection
