@extends('panel.layout')
@section('title', 'Tape Chart')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tape Chart</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $from->isoFormat('D MMM') }} → {{ $to->isoFormat('D MMM Y') }} · {{ $days }} days
        </p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="from" value="{{ $from->toDateString() }}"
               class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm shadow-card focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
        <input type="date" name="to" value="{{ $to->toDateString() }}"
               class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm shadow-card focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
        <button type="submit"
                class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-colors">
            Apply
        </button>
    </form>
</div>

{{-- Legend --}}
<div class="flex items-center gap-4 mb-4 text-xs text-gray-600">
    <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 rounded bg-primary-500"></div>
        <span>Booked</span>
    </div>
    <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 rounded bg-amber-50 border border-amber-200"></div>
        <span>Weekend</span>
    </div>
    <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 rounded bg-gray-100"></div>
        <span>Available</span>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="text-xs w-full" style="border-collapse:separate;border-spacing:0">
            <thead class="sticky top-0 z-10">
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-4 py-3 text-left sticky left-0 bg-gray-50 z-20 border-r border-gray-200 font-semibold text-gray-600 uppercase tracking-wide" style="min-width:130px">Room</th>
                    @foreach ($dates as $d)
                    <th class="py-3 text-center border-r border-gray-100 {{ in_array($d->dayOfWeek, [0, 6]) ? 'bg-amber-50' : '' }}" style="min-width:56px">
                        <div class="font-bold text-gray-800 text-sm">{{ $d->format('d') }}</div>
                        <div class="text-gray-400 text-[10px] uppercase tracking-wide">{{ $d->format('D') }}</div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($rooms as $room)
                <tr class="group hover:bg-gray-50/40 transition-colors">
                    <td class="px-4 py-2.5 sticky left-0 bg-white group-hover:bg-gray-50/40 z-10 border-r border-gray-100" style="min-width:130px">
                        <div class="font-semibold text-gray-900">{{ $room->number }}</div>
                        <div class="text-gray-400 text-[10px] uppercase tracking-wide mt-0.5">{{ $room->roomType?->name }}</div>
                    </td>
                    @foreach ($dates as $d)
                    @php
                        $bookings = collect($reservations)->filter(fn ($r) =>
                            $r['room_id'] === $room->id &&
                            $d->between(\Carbon\Carbon::parse($r['check_in']), \Carbon\Carbon::parse($r['check_out'])->subDay())
                        );
                        $hasBooking = $bookings->count() > 0;
                        $r = $bookings->first();
                        $isWeekend = in_array($d->dayOfWeek, [0, 6]);
                    @endphp
                    <td class="p-1 border-r border-gray-50 text-center {{ $hasBooking ? 'bg-primary-50' : ($isWeekend ? 'bg-amber-50/40' : '') }}"
                        @if ($hasBooking) title="{{ $r['guest_name'] }} ({{ $r['ref'] }})" @endif
                        style="min-width:56px">
                        @if ($hasBooking)
                        <a href="{{ route('panel.fo.reservations.show', $r['id']) }}"
                           class="block bg-primary-600 hover:bg-primary-700 text-white rounded-md px-1 py-0.5 text-[10px] font-medium truncate transition-colors leading-tight"
                           style="max-width:52px">
                            {{ Str::limit($r['guest_name'], 7) }}
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

@endsection
