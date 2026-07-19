@extends('panel.layout')
@section('title', 'OTA Virtual Card Detail')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.channel.vcc.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Detail Virtual Card</h1>
        <p class="text-sm text-gray-500 mt-0.5">Informasi lengkap kartu virtual OTA</p>
    </div>
</div>

@php
    $vcc = $vcc ?? null;
    if (!$vcc) {
        echo '<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-12 text-center text-gray-500">Data tidak ditemukan.</div>';
        return;
    }
    $statusColors = [
        'active'  => 'emerald',
        'charged' => 'blue',
        'expired' => 'gray',
        'invalid' => 'red',
    ];
    $sc = $statusColors[$vcc->status] ?? 'gray';
    $statusLabel = match($vcc->status) {
        'active' => 'Aktif',
        'charged' => 'Tertagih',
        'expired' => 'Kedaluwarsa',
        'invalid' => 'Tidak Valid',
        default => \Illuminate\Support\Str::title($vcc->status),
    };
@endphp

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Card Display --}}
    <div class="lg:col-span-2">
        <div class="bg-gradient-to-br from-slate-800 via-slate-700 to-indigo-900 rounded-2xl shadow-xl p-6 lg:p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-20 pointer-events-none"
                 style="background-image:radial-gradient(circle at 20% 10%,rgba(99,102,241,.5),transparent 50%),radial-gradient(circle at 80% 90%,rgba(139,92,246,.4),transparent 50%);"></div>
            <div class="absolute top-0 right-0 text-[14rem] leading-none opacity-[0.04] -mt-16 -mr-8 font-mono font-bold">
                {{ strtoupper($vcc->brand ?? '') }}
            </div>
            <div class="relative">
                {{-- Card header --}}
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8 text-white/80" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <div>
                            <p class="text-xs text-white/50 uppercase tracking-widest font-semibold">Virtual Card</p>
                            <p class="text-lg font-bold">{{ $vcc->brand ?? 'Card' }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full bg-{{ $sc }}-500/20 text-{{ $sc }}-300 border border-{{ $sc }}-500/30">
                        <span class="w-2 h-2 rounded-full bg-{{ $sc }}-400 {{ $vcc->status === 'active' ? 'animate-pulse' : '' }}"></span>
                        {{ $statusLabel }}
                    </span>
                </div>

                {{-- Card number --}}
                <div class="mb-6">
                    <p class="text-[10px] text-white/40 uppercase tracking-[0.15em] font-semibold mb-1.5">Nomor Kartu</p>
                    <p class="text-2xl font-mono font-bold tracking-[0.12em] text-white">
                        {{ $vcc->masked_number ?? str_repeat('•', 4) . ' ' . str_repeat('•', 4) . ' ' . str_repeat('•', 4) . ' ' . ($vcc->last_four ?? '••••') }}
                    </p>
                </div>

                {{-- Card details row --}}
                <div class="grid grid-cols-3 gap-6 mb-6">
                    <div>
                        <p class="text-[10px] text-white/40 uppercase tracking-[0.15em] font-semibold mb-1.5">Pemegang Kartu</p>
                        <p class="text-sm font-semibold text-white truncate">{{ $vcc->card_holder ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-white/40 uppercase tracking-[0.15em] font-semibold mb-1.5">Berlaku Hingga</p>
                        <p class="text-sm font-semibold text-white">{{ $vcc->expiry_date ?? $vcc->valid_until?->format('m/y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-white/40 uppercase tracking-[0.15em] font-semibold mb-1.5">CVV</p>
                        <p class="text-sm font-mono font-semibold text-white">•••</p>
                    </div>
                </div>

                {{-- Amounts --}}
                <div class="grid grid-cols-2 gap-6 pt-6 border-t border-white/10">
                    <div>
                        <p class="text-[10px] text-white/40 uppercase tracking-[0.15em] font-semibold mb-1.5">Jumlah Otorisasi</p>
                        <p class="text-xl font-bold text-white tabular-nums">
                            Rp {{ number_format($vcc->amount ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] text-white/40 uppercase tracking-[0.15em] font-semibold mb-1.5">Jumlah Tertagih</p>
                        <p class="text-xl font-bold text-emerald-300 tabular-nums">
                            Rp {{ number_format($vcc->charged_amount ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                {{-- Valid period --}}
                @if($vcc->valid_from || $vcc->valid_until)
                <div class="pt-4 border-t border-white/10 mt-4">
                    <p class="text-[10px] text-white/40 uppercase tracking-[0.15em] font-semibold mb-1">Periode Berlaku</p>
                    <p class="text-sm text-white/70">
                        {{ $vcc->valid_from?->format('d M Y') ?? '—' }}
                        <span class="mx-1.5">–</span>
                        {{ $vcc->valid_until?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                @endif
            </div>
        </div>

        {{-- Charge Attempts --}}
        @if(($vcc->chargeAttempts ?? collect())->isNotEmpty())
        <div class="mt-6 bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-900">Riwayat Tagihan</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($vcc->chargeAttempts as $attempt)
                        @php $ok = $attempt->status === 'success'; @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3 text-sm text-gray-600">{{ $attempt->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-800 tabular-nums">Rp {{ number_format($attempt->amount ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full {{ $ok ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                                    {{ $ok ? 'Berhasil' : 'Gagal' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar Info --}}
    <div class="space-y-5">
        {{-- Reservation Info --}}
        @if($vcc->reservation)
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Reservasi Terkait
            </h2>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Ref</span>
                    <a href="{{ route('panel.fo.reservations.show', $vcc->reservation_id) }}" class="font-semibold text-indigo-600 hover:text-indigo-700">
                        {{ $vcc->reservation->ref ?? '#' . $vcc->reservation_id }}
                    </a>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tamu</span>
                    <span class="font-medium text-gray-800">{{ $vcc->reservation->primaryGuest?->full_name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Check-in</span>
                    <span class="text-gray-700">{{ $vcc->reservation->check_in?->format('d M Y') ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Check-out</span>
                    <span class="text-gray-700">{{ $vcc->reservation->check_out?->format('d M Y') ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="font-semibold text-gray-800">{{ $vcc->reservation->status_label ?? $vcc->reservation->status ?? '—' }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Channel Info --}}
        @if($vcc->channel)
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                Channel
            </h2>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Nama</span>
                    <span class="font-medium text-gray-800">{{ $vcc->channel->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Kode</span>
                    <span class="font-mono text-xs text-gray-600 uppercase">{{ $vcc->channel->code ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Status</span>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full {{ ($vcc->channel->is_active ?? false) ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ ($vcc->channel->is_active ?? false) ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        {{-- Meta --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
            <h2 class="text-sm font-bold text-gray-900 mb-4">Informasi</h2>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">ID</span>
                    <span class="font-mono text-gray-700">#{{ $vcc->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Dibuat</span>
                    <span class="text-gray-700">{{ $vcc->created_at->format('d M Y, H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Diperbarui</span>
                    <span class="text-gray-700">{{ $vcc->updated_at->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
