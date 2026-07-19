@extends('panel.layout')
@section('title', 'Kunci Digital')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Kunci Digital PIN</h1>
    <p class="text-sm text-gray-500 mt-0.5">Kelola kunci digital PIN untuk akses kamar tamu</p>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reservasi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kamar</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">PIN</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Berlaku</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($reservations as $r)
                @php
                    $latestKey = $r->doorLockEvents->firstWhere('source', 'mobile_pin');
                    $isRevoked = isset($latestKey?->payload['revoked']) && $latestKey->payload['revoked'];
                    $pin = $latestKey?->payload['pin'] ?? null;
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="text-sm font-semibold text-gray-900">{{ $r->ref }}</span>
                        <p class="text-xs text-gray-500">{{ $r->status === 'checked_in' ? 'Check-in' : 'Dikonfirmasi' }}</p>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $r->primaryGuest?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">
                        @foreach($r->rooms as $rr)
                        <span class="bg-gray-100 px-2 py-0.5 rounded-md text-xs">{{ $rr->room?->room_number }}</span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($pin)
                        <span x-data="{ show: false }" class="relative">
                            <span x-show="!show" @click="show=true" class="font-mono text-sm font-semibold text-gray-900 bg-gray-100 px-3 py-1 rounded-lg cursor-pointer hover:bg-gray-200">
                                ••••••
                            </span>
                            <span x-show="show" @click="show=false" class="font-mono text-sm font-semibold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-lg cursor-pointer">
                                {{ $pin }}
                            </span>
                        </span>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-600">
                        @if($latestKey)
                        <div>{{ \Carbon\Carbon::parse($latestKey->payload['valid_from'] ?? null)?->format('d M Y H:i') ?? '—' }}</div>
                        <div class="text-gray-400">s/d {{ \Carbon\Carbon::parse($latestKey->payload['valid_until'] ?? null)?->format('d M Y H:i') ?? '—' }}</div>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if(!$pin)
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">Belum Ada</span>
                        @elseif($isRevoked)
                        <span class="inline-flex items-center gap-1 text-xs bg-rose-50 text-rose-700 px-2 py-0.5 rounded-full font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Dicabut
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if(!$pin || $isRevoked)
                            <form method="POST" action="{{ route('panel.fo.digital-keys.issue', $r->id) }}">
                                @csrf
                                <button type="submit" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg font-medium transition-colors">
                                    Terbitkan PIN
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('panel.fo.digital-keys.issue', $r->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs bg-amber-100 hover:bg-amber-200 text-amber-700 px-3 py-1.5 rounded-lg font-medium transition-colors">
                                    Terbitkan Ulang
                                </button>
                            </form>
                            <form method="POST" action="{{ route('panel.fo.digital-keys.revoke', $r->id) }}" class="inline" onsubmit="return confirm('Cabut semua kunci digital untuk reservasi ini?')">
                                @csrf
                                <button type="submit" class="text-xs bg-rose-50 hover:bg-rose-100 text-rose-700 px-3 py-1.5 rounded-lg font-medium transition-colors">
                                    Cabut
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-10 text-center text-sm text-gray-400">Tidak ada reservasi aktif.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
