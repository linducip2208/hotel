@extends('panel.layout')
@section('title', 'Event & Wedding')
@section('content')

@php
$statusLabels = [
    'inquiry' => 'Inquiry',
    'tentative' => 'Tentatif',
    'confirmed' => 'Dikonfirmasi',
    'cancelled' => 'Dibatalkan',
    'completed' => 'Selesai',
];
$statusColors = [
    'inquiry' => 'amber',
    'tentative' => 'indigo',
    'confirmed' => 'emerald',
    'cancelled' => 'rose',
    'completed' => 'gray',
];
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Event & Wedding</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola event, wedding, meeting, dan acara lainnya</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.sales.events.index', ['view' => $view === 'calendar' ? 'list' : 'calendar']) }}"
           class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                @if($view === 'calendar')
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                @else
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                @endif
            </svg>
            {{ $view === 'calendar' ? 'Tampilan List' : 'Tampilan Kalender' }}
        </a>
        <a href="{{ route('panel.sales.events.create') }}"
           class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Booking Baru
        </a>
    </div>
</div>

@if($view === 'calendar')
{{-- Calendar View --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden mb-6">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('panel.sales.events.index', ['month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m'), 'view' => 'calendar']) }}"
               class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-sm font-semibold text-gray-700">{{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</h2>
            <a href="{{ route('panel.sales.events.index', ['month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m'), 'view' => 'calendar']) }}"
               class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    @php
    $start = \Carbon\Carbon::parse($month)->startOfMonth();
    $end = $start->copy()->endOfMonth();
    $firstDayOfWeek = (int) $start->copy()->startOfMonth()->dayOfWeek;
    $daysInMonth = $end->day;
    @endphp

    <div class="p-4">
        <div class="grid grid-cols-7 gap-px bg-gray-100 rounded-xl overflow-hidden">
            @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $d)
            <div class="bg-gray-50 px-3 py-2 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wide">{{ $d }}</div>
            @endforeach

            @for($i = 0; $i < $firstDayOfWeek; $i++)
            <div class="bg-white p-2 min-h-[80px]"></div>
            @endfor

            @for($day = 1; $day <= $daysInMonth; $day++)
            @php
            $date = $start->copy()->setDay($day)->toDateString();
            $dayEvents = collect($calendar['events'] ?? [])->filter(fn($e) => \Illuminate\Support\Str::startsWith($e['start'], $date));
            $isToday = $date === now()->toDateString();
            @endphp
            <div class="bg-white p-1.5 min-h-[80px] {{ $isToday ? 'ring-2 ring-indigo-400 ring-inset' : '' }}">
                <div class="text-xs font-semibold mb-1 {{ $isToday ? 'text-indigo-700 bg-indigo-50 w-6 h-6 flex items-center justify-center rounded-full' : 'text-gray-500' }}">{{ $day }}</div>
                <div class="space-y-0.5">
                    @foreach($dayEvents as $ev)
                    <a href="{{ $ev['url'] }}" class="block px-1.5 py-0.5 text-[10px] font-medium rounded truncate" style="background-color:{{ $ev['backgroundColor'] }}20;color:{{ $ev['backgroundColor'] }};border-left:2px solid {{ $ev['backgroundColor'] }}">
                        {{ \Carbon\Carbon::parse($ev['start'])->format('H:i') }} {{ $ev['title'] }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- Legend --}}
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center gap-4 text-xs text-gray-500">
        @foreach($statusLabels as $key => $label)
        <span class="inline-flex items-center gap-1">
            <span class="w-2.5 h-2.5 rounded-full" style="background-color:{{ $statusColors[$key] === 'amber' ? '#f59e0b' : ($statusColors[$key] === 'indigo' ? '#6366f1' : ($statusColors[$key] === 'emerald' ? '#10b981' : ($statusColors[$key] === 'rose' ? '#ef4444' : '#6b7280'))) }}"></span>
            {{ $label }}
        </span>
        @endforeach
    </div>
</div>
@endif

{{-- Upcoming Events List --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">{{ $view === 'list' ? 'Semua Event' : 'Event Mendatang' }}</h2>
        <span class="text-xs text-gray-400">{{ $upcoming->total() }} event</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Event</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Peserta</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Nilai</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($upcoming as $b)
                @php $sc = $statusColors[$b->status] ?? 'gray'; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-gray-900">{{ $b->event_name }}</td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full border border-indigo-100">
                            {{ $b->eventType?->name ?? '-' }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-gray-600">{{ $b->guest?->full_name ?? '-' }}</td>
                    <td class="px-4 py-3.5 text-gray-600">{{ $b->event_date?->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-center text-gray-600">{{ number_format($b->expected_guests, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-gray-900">Rp {{ number_format($b->total_quoted, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-{{ $sc }}-700 bg-{{ $sc }}-50 px-2 py-0.5 rounded-full border border-{{ $sc }}-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $sc }}-500"></span>{{ $statusLabels[$b->status] }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <a href="{{ route('panel.sales.events.show', $b->id) }}"
                           class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 px-2.5 py-1.5 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-10 text-center text-sm text-gray-400">Belum ada event.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($upcoming->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $upcoming->links() }}
    </div>
    @endif
</div>

@endsection
