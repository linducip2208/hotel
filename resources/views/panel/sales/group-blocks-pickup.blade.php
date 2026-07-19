@extends('panel.layout')
@section('title', 'Pickup Tracking — ' . $block->block_code)
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <a href="{{ route('panel.fo.group-blocks.show', $block->id) }}" class="text-sm text-indigo-600 hover:underline mb-1 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Group Block
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Pickup Tracking — {{ $block->block_code }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $block->group_name }} &middot; {{ $block->check_in?->translatedFormat('d M Y') }} &rarr; {{ $block->check_out?->translatedFormat('d M Y') }}</p>
    </div>
    @if($block->status === 'definite')
    <form method="POST" action="{{ route('panel.fo.group-blocks.release', $block->id) }}" onsubmit="return confirm('Lepaskan semua kamar yang belum di-pickup? Tindakan ini tidak bisa dibatalkan.')">
        @csrf
        <button type="submit" class="inline-flex items-center gap-1.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-3.5 py-2 rounded-xl transition-colors shadow-sm shadow-amber-500/25">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Release Unpicked
        </button>
    </form>
    @endif
</div>

{{-- Stats cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @php
        $totalBlocked = $block->rooms->sum('rooms_count');
        $totalPicked = $block->rooms->sum('rooms_picked_up');
        $totalRemaining = $totalBlocked - $totalPicked;
        $pickupPct = $totalBlocked > 0 ? round(($totalPicked / $totalBlocked) * 100, 1) : 0;
    @endphp
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="text-2xl font-bold text-gray-900 tabular-nums">{{ $totalBlocked }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Total Diblokir</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="text-2xl font-bold text-emerald-600 tabular-nums">{{ $totalPicked }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Sudah Dipick-up</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="text-2xl font-bold text-amber-600 tabular-nums">{{ $totalRemaining }}</div>
        <div class="text-xs text-gray-500 mt-0.5">Belum Dipick-up</div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-card border border-gray-100">
        <div class="text-2xl font-bold tabular-nums @if($pickupPct >= 80) text-emerald-600 @elseif($pickupPct >= 50) text-amber-600 @else text-rose-600 @endif">{{ $pickupPct }}%</div>
        <div class="text-xs text-gray-500 mt-0.5">Pickup Rate</div>
        <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
            <div class="h-1.5 rounded-full @if($pickupPct >= 80) bg-emerald-500 @elseif($pickupPct >= 50) bg-amber-400 @else bg-rose-500 @endif"
                 style="width: {{ $pickupPct }}%"></div>
        </div>
    </div>
</div>

{{-- Pickup table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-800">Detail Pickup per Tipe Kamar</h2>
        <span class="text-xs text-gray-400">Cutoff: {{ $block->cutoff_date?->translatedFormat('d M Y H:i') ?? 'Tidak ada' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Tipe Kamar</th>
                    <th class="px-5 py-3 text-right">Diblokir</th>
                    <th class="px-5 py-3 text-right">Dipick-up</th>
                    <th class="px-5 py-3 text-right">Sisa</th>
                    <th class="px-5 py-3 text-right">Pickup %</th>
                    <th class="px-5 py-3">Status Bar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($block->rooms as $room)
                @php
                    $rPicked = (int) ($room->rooms_picked_up ?? 0);
                    $rBlocked = (int) ($room->rooms_count ?? 0);
                    $rRemaining = $rBlocked - $rPicked;
                    $rPct = $rBlocked > 0 ? round(($rPicked / $rBlocked) * 100, 1) : 0;
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-4 font-medium text-gray-900">{{ $room->roomType?->name ?? 'Tipe #' . $room->room_type_id }}</td>
                    <td class="px-5 py-4 text-right font-mono text-xs text-gray-700">{{ $rBlocked }}</td>
                    <td class="px-5 py-4 text-right font-mono text-xs text-emerald-700 font-semibold">{{ $rPicked }}</td>
                    <td class="px-5 py-4 text-right font-mono text-xs @if($rRemaining > 0) text-amber-700 font-semibold @else text-gray-400 @endif">{{ $rRemaining }}</td>
                    <td class="px-5 py-4 text-right font-mono text-xs font-semibold @if($rPct >= 80) text-emerald-600 @elseif($rPct >= 50) text-amber-600 @else text-rose-600 @endif">{{ $rPct }}%</td>
                    <td class="px-5 py-4">
                        <div class="w-full bg-gray-100 rounded-full h-2.5 max-w-[120px]">
                            <div class="h-2.5 rounded-full @if($rPct >= 80) bg-emerald-500 @elseif($rPct >= 50) bg-amber-400 @else bg-rose-500 @endif"
                                 style="width: {{ max($rPct, 3) }}%"></div>
                        </div>
                    </td>
                </tr>
                @endforeach

                @if($block->rooms->isEmpty())
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-gray-400">Belum ada kamar di group block ini.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- Info --}}
<div class="mt-6 bg-slate-50 rounded-2xl border border-slate-200 p-5">
    <h3 class="font-semibold text-gray-700 text-sm mb-2">Tentang Pickup Tracking</h3>
    <ul class="space-y-1.5 text-xs text-gray-500">
        <li>&bull; <strong>Pickup</strong> = jumlah kamar yang sudah di-booking oleh grup dari alokasi yang diblokir.</li>
        <li>&bull; <strong>Cutoff Date</strong> = batas waktu grup harus melakukan pickup. Setelah lewat, kamar yang belum di-pickup akan di-release otomatis ke inventory.</li>
        <li>&bull; <strong>Release Unpicked</strong> = lepaskan kamar yang belum di-pickup secara manual sebelum cutoff date.</li>
        <li>&bull; Auto-release berjalan setiap hari pukul 02:00 via scheduler <code class="bg-slate-200 px-1 py-0.5 rounded">groups:release-expired</code>.</li>
    </ul>
</div>

@endsection
