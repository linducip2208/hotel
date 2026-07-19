@extends('panel.layout')
@section('title', 'Banquet Calendar')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Banquet Calendar</h1>
    <p class="text-sm text-gray-500 mt-0.5">Upcoming events by function room</p>
</div>

<div class="space-y-4">
    @forelse ($rooms as $r)
    @php $upcoming = $events->where('function_room_id', $r->id)->sortBy('event_date'); @endphp
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-50">
            <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-sm font-semibold text-gray-900">{{ $r->name }}</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ $upcoming->count() }} event{{ $upcoming->count() != 1 ? 's' : '' }} scheduled</p>
            </div>
            @if ($upcoming->count() > 0)
            <span class="text-xs font-semibold bg-violet-50 text-violet-700 px-2.5 py-1 rounded-full">{{ $upcoming->count() }}</span>
            @endif
        </div>
        @if ($upcoming->count())
        <div class="divide-y divide-gray-50">
            @foreach ($upcoming as $e)
            @php $sc = match($e->status) { 'confirmed' => 'emerald', 'tentative' => 'amber', 'cancelled' => 'red', 'completed' => 'blue', default => 'gray' }; @endphp
            <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50/60 transition-colors">
                <div class="w-12 text-center shrink-0">
                    <div class="text-lg font-bold text-gray-900 leading-tight">{{ $e->event_date->format('d') }}</div>
                    <div class="text-xs font-semibold text-gray-400 uppercase">{{ $e->event_date->format('M') }}</div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-gray-900 truncate">{{ $e->title }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        {{ $e->start_time?->format('H:i') }} – {{ $e->end_time?->format('H:i') }} · {{ $e->expected_attendees }} pax
                    </div>
                </div>
                <a href="{{ route('panel.banquet.events.show', $e->id) }}"
                   class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize hover:opacity-80 transition-opacity shrink-0">
                    {{ $e->status }}
                </a>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-6 text-sm text-gray-400">No events scheduled for this room.</div>
        @endif
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 flex flex-col items-center justify-center py-12">
        <p class="text-sm text-gray-400">No function rooms configured yet.</p>
    </div>
    @endforelse
</div>

@endsection
