@extends('panel.layout')
@section('title', 'AI Assign Kamar')
@section('content')

<div class="mb-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">AI Assign Kamar</h1>
            <p class="text-sm text-stone-500 mt-1">Alokasi kamar otomatis berdasarkan preferensi tamu dan kualitas kamar</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}"
                       class="text-sm border border-stone-200 rounded-lg px-3 py-2 bg-white text-stone-700 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <button type="submit" class="px-3 py-2 text-sm font-medium bg-stone-100 hover:bg-stone-200 text-stone-600 rounded-lg transition">Lihat</button>
            </form>
            <form method="POST" action="{{ route('panel.fo.room-assignment.auto') }}"
                  onsubmit="return confirm('Auto-assign semua reservasi tanpa kamar untuk {{ $date }}?')">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-sm shadow-indigo-500/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Auto Assign
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Stats Row --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-amber-600">{{ $unassigned->count() }}</div>
        <div class="text-xs text-stone-500 mt-0.5 font-medium">Belum Ditempatkan</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-emerald-600">{{ $availableRooms->where('fo_status', 'vacant')->where('hk_status', 'clean')->count() }}</div>
        <div class="text-xs text-stone-500 mt-0.5 font-medium">Kamar Tersedia</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-blue-600">{{ $availableRooms->where('fo_status', 'occupied')->count() }}</div>
        <div class="text-xs text-stone-500 mt-0.5 font-medium">Terisi</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm text-center">
        <div class="text-2xl font-bold text-stone-500">{{ $recentAssignments->count() }}</div>
        <div class="text-xs text-stone-500 mt-0.5 font-medium">Penempatan (7 hari)</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Left: Unassigned Reservations --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-stone-100">
            <h2 class="text-sm font-semibold text-stone-900">Reservasi Belum Ditempatkan — {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-50/80 border-b border-stone-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Ref</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Tamu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Tipe Kamar</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Preferensi</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                @forelse ($unassigned as $res)
                    <tr class="hover:bg-stone-50 transition-colors">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-indigo-600 font-medium">{{ $res->ref }}</span>
                            @if ($res->primaryGuest?->profile?->isHighValue())
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 ml-1">VIP</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-stone-900">{{ $res->primaryGuest?->full_name ?: '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-stone-600">{{ $res->roomType?->name ?: '—' }}</td>
                        <td class="px-4 py-3">
                            @php $p = $res->primaryGuest?->profile; @endphp
                            <div class="flex flex-wrap gap-1">
                                @if ($p?->preferred_floor)
                                    <span class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded">Lt.{{ $p->preferred_floor }}</span>
                                @endif
                                @if ($p?->preferred_bed_type)
                                    <span class="text-[10px] bg-cyan-100 text-cyan-700 px-1.5 py-0.5 rounded">{{ $p->preferred_bed_type }}</span>
                                @endif
                                @if (($p?->upsell_score ?? 0) >= 80)
                                    <span class="text-[10px] bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded">Upsell Hot</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center gap-1 justify-end">
                                <select onchange="document.getElementById('assignRoom_'+{{ $res->id }}).value=this.value; document.getElementById('assignForm_'+{{ $res->id }}).submit()"
                                        class="text-xs border border-stone-200 rounded-lg px-2 py-1.5 w-28 bg-white">
                                    <option value="">Pilih Kamar</option>
                                    @foreach($availableRooms->where('room_type_id', $res->room_type_id)->where('fo_status', 'vacant')->where('hk_status', 'clean') as $r)
                                        <option value="{{ $r->id }}">{{ $r->number }} (Lt.{{ $r->floor }})
                                            @php $sc = app(\App\Services\Fo\RoomAssignmentAiService::class)->getAssignmentScore($r, $res); @endphp
                                            @if($sc) — {{ $sc }}pt @endif
                                        </option>
                                    @endforeach
                                </select>
                                <form id="assignForm_{{ $res->id }}" method="POST" action="{{ route('panel.fo.room-assignment.assign') }}">
                                    @csrf
                                    <input type="hidden" name="reservation_id" value="{{ $res->id }}">
                                    <input type="hidden" name="room_id" id="assignRoom_{{ $res->id }}" value="">
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-stone-400 text-sm">Semua reservasi sudah ditempatkan.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right: Available Rooms Grid --}}
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-stone-100">
            <h2 class="text-sm font-semibold text-stone-900">Status Kamar — {{ $date }}</h2>
        </div>
        <div class="p-4 max-h-[600px] overflow-y-auto">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
                @foreach($availableRooms as $room)
                    @php
                        $foClass = match($room->fo_status) {
                            'vacant'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'occupied' => 'border-rose-200 bg-rose-50 text-rose-700',
                            'reserved' => 'border-amber-200 bg-amber-50 text-amber-700',
                            default    => 'border-stone-200 bg-stone-50 text-stone-500',
                        };
                        $hkBadge = match($room->hk_status) {
                            'clean'     => 'bg-emerald-100 text-emerald-700',
                            'dirty'     => 'bg-amber-100 text-amber-700',
                            'inspected' => 'bg-blue-100 text-blue-700',
                            default     => 'bg-stone-100 text-stone-500',
                        };
                    @endphp
                    <div class="border {{ $foClass }} rounded-xl p-2 text-center text-xs transition hover:shadow-md cursor-default">
                        <div class="font-bold text-[13px]">{{ $room->number }}</div>
                        <div class="text-[10px] opacity-70">Lt.{{ $room->floor }}</div>
                        @if($room->view)
                            <div class="text-[10px] mt-0.5 capitalize">{{ $room->view }}</div>
                        @endif
                        <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[9px] font-medium {{ $hkBadge }}">{{ $room->hk_status }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- Recent Assignments --}}
<div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-stone-100">
        <h2 class="text-sm font-semibold text-stone-900">Log Penempatan Terbaru</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-stone-50/80 border-b border-stone-100">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Ref</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Kamar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Check-in</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-stone-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
            @forelse ($recentAssignments as $a)
                <tr class="hover:bg-stone-50 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs text-indigo-600">{{ $a->ref }}</td>
                    <td class="px-4 py-3 font-medium text-stone-900">{{ $a->primaryGuest?->full_name ?: '—' }}</td>
                    <td class="px-4 py-3 text-stone-600">{{ $a->room?->number ? 'Kamar ' . $a->room->number : '—' }}</td>
                    <td class="px-4 py-3 text-stone-600">{{ $a->check_in?->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        @php
                            $stBadge = match($a->status) {
                                'confirmed' => 'bg-blue-100 text-blue-700',
                                'checked_in' => 'bg-emerald-100 text-emerald-700',
                                'checked_out' => 'bg-stone-100 text-stone-500',
                                'cancelled' => 'bg-red-100 text-red-700',
                                default => 'bg-amber-100 text-amber-700',
                            };
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium {{ $stBadge }}">{{ ucfirst($a->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-stone-400 text-sm">Belum ada penempatan dalam 7 hari terakhir.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
