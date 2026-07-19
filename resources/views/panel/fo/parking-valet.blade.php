@extends('panel.layout')
@section('title', 'Valet Parkir')
@section('content')

<div class="mb-6">
    <a href="{{ route('panel.fo.parking') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Parkir
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Valet Parkir</h1>
    <p class="text-sm text-gray-500 mt-0.5">Kelola kendaraan valet dan pelacakan kunci</p>
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Plat</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Slot</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kunci</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Valet Oleh</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-in</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($valetRecords as $r)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-gray-900">{{ $r->vehicle_plate }}</td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full border border-indigo-100">{{ $r->parkingSlot?->slot_number ?? '-' }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-gray-600">{{ $r->guest?->full_name ?? '-' }}</td>
                    <td class="px-4 py-3.5">
                        @if($r->valet_key_location)
                        <span class="text-xs font-mono bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full border border-amber-100">
                            {{ is_array($r->valet_key_location) ? ($r->valet_key_location['location'] ?? json_encode($r->valet_key_location)) : $r->valet_key_location }}
                        </span>
                        @else
                        <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-gray-600">{{ $r->valetByUser?->name ?? '-' }}</td>
                    <td class="px-4 py-3.5 text-gray-600 text-xs">{{ \Carbon\Carbon::parse($r->check_in)->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3.5 text-right">
                        <form method="POST" action="{{ route('panel.fo.parking.checkout', $r->id) }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 px-2.5 py-1.5 rounded-lg transition-colors">
                                Kembalikan
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-10 text-center text-sm text-gray-400">Tidak ada kendaraan valet aktif.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
