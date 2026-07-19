@extends('panel.layout')
@section('title', 'Privasi Data — Kepatuhan')
@section('content')

@php $prop = app('current_property'); @endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Privasi Data Tamu</h1>
    <p class="text-sm text-gray-500 mt-0.5">Kelola consent, retensi data, ekspor data tamu, dan hapus data (right-to-be-forgotten)</p>
</div>

{{-- Retention Policy Banner --}}
<div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5 mb-6">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <h3 class="font-bold text-indigo-900">Kebijakan Retensi Data</h3>
            <p class="text-sm text-indigo-700 mt-1">Data tamu otomatis masuk kandidat retensi setelah <strong>365 hari</strong> sejak terakhir kali menginap. Tamu yang belum dikunjungi dapat dianonimkan atau dihapus berdasarkan permintaan. Data yang sudah dianonimkan tidak dapat dipulihkan.</p>
        </div>
    </div>
</div>

{{-- Search --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 mb-6">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[250px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tamu: nama, email, atau telepon..."
                   class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <button type="submit"
                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Cari
        </button>
    </form>
</div>

@if($guests->isNotEmpty() && request()->filled('search'))
{{-- Guest Results --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tamu</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email / Telepon</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stay</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Marketing Consent</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($guests as $guest)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $guest->first_name }} {{ $guest->last_name }}</p>
                        <p class="text-xs text-gray-400">Tamu sejak {{ $guest->created_at->format('M Y') }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-xs text-gray-600">{{ $guest->email ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $guest->phone ?? '-' }}</p>
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-semibold">{{ $guest->reservations_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $guest->marketing_consent ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                            {{ $guest->marketing_consent ? 'Opt-In' : 'Opt-Out' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($guest->forgotten_at)
                        <span class="text-xs bg-rose-50 text-rose-600 px-2 py-0.5 rounded-full font-medium">Dianonimkan</span>
                        @else
                        <span class="text-xs bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full font-medium">Aktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('panel.compliance.privacy.export', $guest->id) }}"
                               class="text-xs bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium px-2.5 py-1.5 rounded-lg border border-gray-200 transition-colors"
                               title="Ekspor data">
                                <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Ekspor
                            </a>
                            @if(!$guest->forgotten_at)
                            <form method="POST" action="{{ route('panel.compliance.privacy.anonymize', $guest->id) }}"
                                  onsubmit="return confirm('Anonimkan data tamu ini? Data tidak dapat dipulihkan.')">
                                @csrf
                                <button class="text-xs bg-rose-50 hover:bg-rose-100 text-rose-600 font-medium px-2.5 py-1.5 rounded-lg border border-rose-200 transition-colors">
                                    Anonimkan
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($guests->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/30">{{ $guests->links() }}</div>
    @endif
</div>
@elseif(request()->filled('search'))
<div class="bg-white rounded-2xl border border-gray-100 shadow-card p-8 text-center">
    <p class="text-gray-400">Tidak ada tamu ditemukan dengan kata kunci "{{ request('search') }}"</p>
</div>
@else
<div class="bg-white rounded-2xl border border-gray-100 shadow-card p-12 text-center">
    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
    <p class="text-gray-500 font-semibold">Cari tamu untuk melihat data privasi</p>
    <p class="text-sm text-gray-400 mt-1">Gunakan kolom pencarian untuk menemukan tamu berdasarkan nama, email, atau telepon</p>
</div>
@endif
@endsection
