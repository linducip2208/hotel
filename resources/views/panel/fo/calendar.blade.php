@extends('panel.layout')
@section('title', 'Kalender Reservasi')
@section('content')

@php
    $statusColors = [
        'confirmed' => ['bg' => 'bg-primary-500', 'label' => 'Confirmed'],
        'checked_in' => ['bg' => 'bg-emerald-500', 'label' => 'Check-in'],
        'tentative' => ['bg' => 'bg-amber-400', 'label' => 'Tentative'],
        'completed' => ['bg' => 'bg-gray-400', 'label' => 'Selesai'],
        'checked_out' => ['bg' => 'bg-gray-400', 'label' => 'Selesai'],
        'cancelled' => ['bg' => 'bg-red-400', 'label' => 'Batal'],
        'no_show' => ['bg' => 'bg-orange-500', 'label' => 'No Show'],
    ];
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kalender Reservasi</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $from->isoFormat('D MMM') }} → {{ $to->isoFormat('D MMM Y') }} · {{ $days }} hari · {{ $rooms->count() }} kamar
        </p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="from" value="{{ $from->toDateString() }}"
               class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
        <input type="date" name="to" value="{{ $to->toDateString() }}"
               class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
        <button type="submit"
                class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-colors no-print">
            Terapkan
        </button>
        <a href="{{ route('panel.fo.calendar') }}"
           class="border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 text-sm font-medium px-3 py-2 rounded-xl transition-colors no-print">
            Hari Ini
        </a>
    </form>
</div>

{{-- Legend --}}
<div class="flex flex-wrap items-center gap-3 mb-4 text-xs text-gray-600">
    @foreach ($statusColors as $key => $color)
    <div class="flex items-center gap-1.5">
        <div class="w-3.5 h-3.5 rounded {{ $color['bg'] }}"></div>
        <span>{{ $color['label'] }}</span>
    </div>
    @endforeach
    <div class="flex items-center gap-1.5">
        <div class="w-3.5 h-3.5 rounded bg-gray-100 border border-gray-200"></div>
        <span>Tersedia</span>
    </div>
</div>

{{-- Room selector --}}
<div class="mb-4 flex items-center gap-2 no-print">
    <label class="text-xs font-semibold text-gray-500">Tampilkan:</label>
    <select class="rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all" onchange="filterRooms(this.value)">
        <option value="all">Semua Kamar</option>
        @foreach (\App\Models\RoomType::where('property_id', app('current_property')->id)->where('is_active', true)->orderBy('name')->get() as $rt)
        <option value="type-{{ $rt->id }}">{{ $rt->name }}</option>
        @endforeach
    </select>
    <button onclick="document.querySelectorAll('.room-row').forEach(r=>r.classList.remove('hidden'))" class="text-xs text-primary-600 hover:text-primary-800 font-medium">Reset</button>
</div>

{{-- Tape Chart Grid --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto" id="calendar-scroll">
        <table class="text-xs w-full" style="border-collapse:separate;border-spacing:0">
            <thead class="sticky top-0 z-10">
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-4 py-3 text-left sticky left-0 bg-gray-50 z-20 border-r border-gray-200 font-semibold text-gray-600 uppercase tracking-wide" style="min-width:140px">
                        <select class="text-xs font-semibold bg-transparent border-0 text-gray-600 uppercase tracking-wide outline-none cursor-pointer" onchange="scrollToRoom(this.value)">
                            <option value="">Kamar ({{ $rooms->count() }})</option>
                            @foreach ($rooms as $room)
                            <option value="room-{{ $room->id }}">{{ $room->number }} · {{ $room->roomType?->name }}</option>
                            @endforeach
                        </select>
                    </th>
                    @foreach ($dates as $d)
                    <th class="py-3 text-center border-r {{ in_array($d->dayOfWeek, [0, 6]) ? 'bg-amber-50/60' : 'border-gray-100' }}" style="min-width:56px">
                        <div class="font-bold text-gray-800 text-sm">{{ $d->format('d') }}</div>
                        <div class="text-gray-400 text-[10px] uppercase tracking-wide">{{ $d->isoFormat('ddd') }}</div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($rooms as $room)
                <tr class="group hover:bg-gray-50/40 transition-colors room-row" data-room-type="{{ $room->room_type_id }}" id="room-{{ $room->id }}">
                    <td class="px-4 py-2.5 sticky left-0 bg-white group-hover:bg-gray-50/40 z-10 border-r border-gray-100" style="min-width:140px">
                        <div class="font-semibold text-gray-900 text-[13px]">{{ $room->number }}</div>
                        <div class="text-gray-400 text-[10px] uppercase tracking-wide mt-0.5">{{ $room->roomType?->name ?? '—' }} · Lt.{{ $room->floor }}</div>
                    </td>
                    @php
                        $roomReservations = collect($reservations)->filter(fn ($r) =>
                            (int)$r['room_id'] === $room->id
                        );
                    @endphp
                    @foreach ($dates as $d)
                    @php
                        $matching = $roomReservations->filter(fn ($r) =>
                            $d->between(\Carbon\Carbon::parse($r['check_in']), \Carbon\Carbon::parse($r['check_out'])->subDay())
                        );
                        $r = $matching->first();
                        $status = $r['status'] ?? null;
                        $isFirstDay = $r && $d->toDateString() === $r['check_in'];
                        $isLastDay = $r && $d->toDateString() === \Carbon\Carbon::parse($r['check_out'])->subDay()->toDateString();
                        $color = $statusColors[$status] ?? null;
                        $isWeekend = in_array($d->dayOfWeek, [0, 6]);
                    @endphp
                    <td class="p-0.5 border-r border-gray-50 text-center {{ $isWeekend && !$r ? 'bg-amber-50/20' : '' }}"
                        style="min-width:56px; height:38px">
                        @if ($r)
                        <a href="{{ route('panel.fo.reservations.show', $r['id']) }}"
                           class="relative group/tooltip block h-full rounded-md {{ $color['bg'] ?? 'bg-gray-300' }} text-white text-[10px] font-medium transition-all hover:ring-2 hover:ring-offset-1 hover:ring-primary-300 overflow-hidden"
                           style="{{ $isFirstDay ? 'border-radius: 4px 0 0 4px;' : '' }}{{ $isLastDay ? 'border-radius: 0 4px 4px 0;' : '' }}">
                           <span class="block px-1 py-0.5 truncate leading-tight">
                               {{ \Illuminate\Support\Str::limit($r['guest_name'], 8) }}
                           </span>
                           {{-- Tooltip --}}
                           <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 hidden group-hover/tooltip:block z-50 pointer-events-none">
                               <div class="bg-gray-900 text-white text-[11px] rounded-lg px-2.5 py-1.5 shadow-xl whitespace-nowrap">
                                   <p class="font-semibold">{{ $r['guest_name'] }}</p>
                                   <p class="text-gray-300 mt-0.5">#{{ $r['ref'] }} · {{ \Carbon\Carbon::parse($r['check_in'])->format('d/m') }}–{{ \Carbon\Carbon::parse($r['check_out'])->format('d/m') }}</p>
                               </div>
                           </div>
                        </a>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Navigation helpers --}}
<div class="flex items-center justify-between mt-4 text-sm text-gray-500 no-print">
    <div class="flex items-center gap-2">
        <a href="?from={{ $from->copy()->subDays(14)->toDateString() }}&to={{ $from->copy()->subDay()->toDateString() }}"
           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors text-gray-600 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            2 Minggu Sebelumnya
        </a>
    </div>
    <span class="text-xs">Gulir horizontal untuk lihat lebih banyak →</span>
    <div class="flex items-center gap-2">
        <a href="?from={{ $to->copy()->addDay()->toDateString() }}&to={{ $to->copy()->addDays(14)->toDateString() }}"
           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors text-gray-600 font-medium">
            2 Minggu Selanjutnya
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
</div>

<script>
function filterRooms(value) {
    if (value === 'all') {
        document.querySelectorAll('.room-row').forEach(r => r.classList.remove('hidden'));
        return;
    }
    const typeId = value.replace('type-', '');
    document.querySelectorAll('.room-row').forEach(r => {
        if (r.dataset.roomType === typeId) {
            r.classList.remove('hidden');
        } else {
            r.classList.add('hidden');
        }
    });
}

function scrollToRoom(value) {
    if (!value) return;
    const el = document.getElementById(value);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const scrollContainer = document.getElementById('calendar-scroll');
    const todayCol = document.querySelector('th[data-today]');
    if (scrollContainer && todayCol) {
        scrollContainer.scrollLeft = todayCol.offsetLeft - 200;
    }
});
</script>

<style>
@media print {
    .no-print { display: none !important; }
    body { font-size: 10px; }
    table { border-collapse: collapse !important; }
    th, td { border: 0.5px solid #d1d5db !important; padding: 4px 6px !important; }
}
</style>

@endsection
