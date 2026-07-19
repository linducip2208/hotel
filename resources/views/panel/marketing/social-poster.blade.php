@extends('panel.layout')
@section('title', 'Social Media Auto-Poster')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Social Media Auto-Poster</h1>
    <p class="text-sm text-gray-500 mt-0.5">Publikasikan konten otomatis ke Instagram</p>
</div>

@if(!$connected)
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center mb-6">
    <svg class="w-12 h-12 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <h3 class="text-lg font-semibold text-amber-800 mb-1">Provider Instagram Belum Dikonfigurasi</h3>
    <p class="text-sm text-amber-600 mb-4">Tambahkan provider social dengan format Instagram Graph di halaman integrasi untuk mulai auto-posting.</p>
    <a href="{{ route('panel.settings.integrations') }}" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066"/></svg>
        Buka Pengaturan Integrasi
    </a>
</div>
@endif

{{-- Status Card --}}
@if($connected)
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-6">
    <div class="flex items-center gap-4 flex-wrap">
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900">Instagram Connected</p>
                <p class="text-xs text-gray-500">Siap posting otomatis</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs text-gray-600">Terkonfigurasi</span>
        </div>
    </div>
</div>
@endif

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Quick Post Form --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Posting Cepat</h2>
        </div>
        <form method="POST" action="{{ route('panel.marketing.social-poster.post') }}" class="p-5 space-y-4" x-data="{ type: 'weekend' }">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis Konten</label>
                <select name="type" x-model="type"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="weekend">Weekend Getaway</option>
                    <option value="flash_sale">Flash Sale 24 Jam</option>
                    <option value="new_year">Paket Tahun Baru</option>
                    <option value="availability">Kamar Tersedia Hari Ini</option>
                    <option value="custom">Custom Caption</option>
                </select>
            </div>

            <template x-if="type === 'custom'">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Caption</label>
                    <textarea name="caption" rows="6"
                              class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all"
                              placeholder="Tulis caption Instagram Anda..."></textarea>
                </div>
            </template>

            {{-- Preview --}}
            <template x-if="type !== 'custom'">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Pratinjau Caption</label>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 whitespace-pre-line max-h-48 overflow-y-auto font-sans leading-relaxed">
                        {{ $captions['weekend'] ?? '' }}
                    </div>
                </div>
            </template>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white font-semibold text-sm py-2.5 rounded-xl transition-all shadow-md shadow-rose-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Posting Sekarang
            </button>
        </form>
    </div>

    {{-- Caption Templates --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Template Caption</h2>
        </div>
        <div class="p-5 space-y-3">
            @foreach ($templates as $key => $label)
            <div class="bg-gray-50 rounded-xl p-3 text-xs">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-700 mb-2">{{ $label }}</span>
                <p class="text-gray-600 whitespace-pre-line leading-relaxed line-clamp-3">{{ $captions[$key] ?? '' }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Post History --}}
@if ($history && $history->isNotEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 mt-6">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Riwayat Posting</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Channel</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($history as $h)
                @php
                    $payload = $h->payload ?? [];
                    $badgeColor = $h->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';
                    $badgeLabel = $h->status === 'sent' ? 'Berhasil' : 'Gagal';
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 text-gray-600 text-xs">{{ $h->created_at->format('d M Y H:i') }}</td>
                    <td class="px-5 py-3 text-gray-700 text-xs font-medium">{{ ucfirst($h->channel) }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badgeColor }}">{{ $badgeLabel }}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs font-mono">{{ $payload['media_id'] ?? $payload['message'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Schedule Section --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 mt-6">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Jadwalkan Posting</h2>
    </div>
    <form method="POST" action="{{ route('panel.marketing.social-poster.schedule') }}" class="p-5 space-y-4">
        @csrf
        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis Konten</label>
                <select name="type"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="weekend">Weekend Getaway</option>
                    <option value="flash_sale">Flash Sale 24 Jam</option>
                    <option value="new_year">Paket Tahun Baru</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jadwal Posting</label>
                <input type="datetime-local" name="scheduled_at" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 text-sm font-semibold text-white bg-indigo-600 px-4 py-2 rounded-xl hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Jadwalkan
                </button>
            </div>
        </div>
    </form>
</div>

@endsection
