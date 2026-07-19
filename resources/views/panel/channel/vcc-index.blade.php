@extends('panel.layout')
@section('title', 'OTA Virtual Card')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">OTA Virtual Card</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kartu kredit virtual dari channel OTA</p>
    </div>
    <a href="{{ route('panel.channel.index') }}"
       class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 px-3.5 py-2 rounded-xl hover:bg-gray-50 shadow-card transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Channel Manager
    </a>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    @if(($vccs ?? collect())->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reservasi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Channel</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Pemegang Kartu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Brand</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Berlaku Hingga</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Dibuat</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($vccs as $vcc)
                @php
                    $statusColors = [
                        'active'  => 'emerald',
                        'charged' => 'blue',
                        'expired' => 'gray',
                        'invalid' => 'red',
                    ];
                    $sc = $statusColors[$vcc->status] ?? 'gray';
                    $cardHolder = $vcc->card_holder ?? '—';
                    $maskedHolder = strlen($cardHolder) > 2 ? substr($cardHolder, 0, 2) . str_repeat('•', strlen($cardHolder) - 2) : $cardHolder;
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm text-gray-700 tabular-nums">#{{ $vcc->id }}</td>
                    <td class="px-4 py-3.5">
                        @if($vcc->reservation)
                        <a href="{{ route('panel.fo.reservations.show', $vcc->reservation_id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 hover:underline">
                            {{ $vcc->reservation->ref ?? '#' . $vcc->reservation_id }}
                        </a>
                        @else
                        <span class="text-sm text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-800">{{ $vcc->channel?->name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-600 font-mono">{{ $maskedHolder }}</td>
                    <td class="px-4 py-3.5">
                        @if($vcc->brand)
                        <span class="text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-md uppercase">{{ $vcc->brand }}</span>
                        @else
                        <span class="text-sm text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right text-sm text-gray-700 tabular-nums">
                        Rp {{ number_format($vcc->amount ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">
                        {{ $vcc->valid_until ? $vcc->valid_until->format('d M Y') : '—' }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-{{ $sc }}-50 text-{{ $sc }}-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $sc }}-500"></span>
                            {{ \Illuminate\Support\Str::title($vcc->status === 'active' ? 'Aktif' : ($vcc->status === 'charged' ? 'Tertagih' : ($vcc->status === 'expired' ? 'Kedaluwarsa' : ($vcc->status === 'invalid' ? 'Tidak Valid' : $vcc->status)))) }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-500">{{ $vcc->created_at->format('d M Y, H:i') }}</td>
                    <td class="px-4 py-3.5 text-right">
                        <a href="{{ route('panel.channel.vcc.show', $vcc->id) }}"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(($vccs ?? collect())->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $vccs->links() }}
    </div>
    @endif
    @else
    <div class="flex flex-col items-center justify-center py-20">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </div>
        <p class="text-base font-semibold text-gray-600">Belum ada virtual card</p>
        <p class="text-sm text-gray-400 mt-1.5">Virtual card dari OTA akan muncul di sini setelah booking diterima.</p>
    </div>
    @endif
</div>

@endsection
