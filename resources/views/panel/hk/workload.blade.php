@extends('panel.layout')
@section('title', 'Workload Forecast')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Daily Workload Forecast</h1>
        <p class="text-sm text-gray-500 mt-0.5">Prediksi beban kerja housekeeping untuk perencanaan staffing</p>
    </div>
</div>

{{-- Date picker --}}
<form method="GET" class="flex items-center gap-3 mb-6">
    <input type="date" name="date" value="{{ $date->toDateString() }}" class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-primary-100 focus:border-primary-400 outline-none">
    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">View Forecast</button>
</form>

{{-- Summary cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-card text-center">
        <div class="text-2xl font-bold text-gray-900">{{ $forecast['total_rooms'] }}</div>
        <div class="text-xs text-gray-500 mt-0.5 font-medium">Total Rooms</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-card text-center">
        <div class="text-2xl font-bold text-primary-600">{{ $forecast['total_minutes'] }}</div>
        <div class="text-xs text-gray-500 mt-0.5 font-medium">Total Minutes ({{ round($forecast['total_minutes'] / 60, 1) }}h)</div>
    </div>
    <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-card text-center">
        <div class="text-2xl font-bold text-amber-600">{{ $forecast['attendants_needed'] }}</div>
        <div class="text-xs text-amber-600 mt-0.5 font-medium">Attendants Needed</div>
    </div>
</div>

{{-- Assignments --}}
@if (!empty($assignments))
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    @foreach ($assignments as $assignment)
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-semibold text-gray-900">Attendant #{{ $assignment['attendant'] }}</h3>
            <div class="flex items-center gap-3 text-xs text-gray-500">
                <span>{{ $assignment['room_count'] }} rooms</span>
                <span class="font-medium">{{ $assignment['total_minutes'] }} min</span>
            </div>
        </div>
        <div class="space-y-1">
            @foreach ($assignment['rooms'] as $room)
            <div class="flex items-center gap-2 py-1.5 px-3 bg-gray-50 rounded-lg text-sm">
                <span class="w-2 h-2 rounded-full {{ $room['color'] === 'red' ? 'bg-red-400' : ($room['color'] === 'blue' ? 'bg-blue-400' : 'bg-gray-400') }} shrink-0"></span>
                <span class="font-medium text-gray-900">Room {{ $room['room_number'] }}</span>
                <span class="text-xs text-gray-500">({{ $room['type'] }})</span>
                <span class="ml-auto text-xs font-medium text-gray-600">{{ $room['estimated_minutes'] }} min</span>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Room list table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-900">Room List — {{ $date->format('d M Y') }}</h2>
        <form method="POST" action="{{ route('panel.hk.workload.assign') }}" class="flex items-center gap-2">
            @csrf
            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
            <input type="number" name="attendants" value="{{ $forecast['attendants_needed'] }}" min="1" max="50"
                   class="w-20 px-2 py-1 text-xs border border-gray-200 rounded-lg text-center">
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                Auto-Assign
            </button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Room</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Type</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Clean Type</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Est. Time</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Priority</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($forecast['rooms'] as $room)
                @php
                    $priBadge = match($room['priority']) {
                        'high' => 'bg-red-50 text-red-700',
                        'normal' => 'bg-blue-50 text-blue-700',
                        default => 'bg-gray-50 text-gray-600',
                    };
                    $typeBadge = match($room['clean_type']) {
                        'full' => 'bg-red-50 text-red-700',
                        'light' => 'bg-blue-50 text-blue-700',
                        default => 'bg-gray-50 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50">
                    <td class="px-5 py-3">
                        <span class="font-medium text-gray-900">Room {{ $room['room_number'] }}</span>
                        <span class="text-xs text-gray-400 block">Floor {{ $room['floor'] }}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $room['type'] }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize {{ $typeBadge }}">{{ $room['clean_type'] }}</span>
                    </td>
                    <td class="px-5 py-3 text-center font-medium">{{ $room['estimated_minutes'] }} min</td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full capitalize {{ $priBadge }}">{{ $room['priority'] }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-16 text-center text-gray-400">No rooms to clean for this date — all caught up!</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
