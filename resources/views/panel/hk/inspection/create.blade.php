@extends('panel.layout')
@section('title', 'Inspect Room ' . $room->number)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.hk.inspection.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Inspect Room {{ $room->number }}</h1>
        <p class="text-sm text-gray-500">{{ $room->roomType?->name ?? 'Room' }} · Floor {{ $room->floor }}</p>
    </div>
</div>

<form method="POST" action="{{ route('panel.hk.inspection.store') }}" class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 space-y-4">
    @csrf
    <input type="hidden" name="room_id" value="{{ $room->id }}">

    <div class="space-y-3">
        @foreach ($checklistItems as $key => $label)
        <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900">{{ $label }}</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Pass/Fail toggle --}}
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="items[{{ $key }}][status]" value="pass" checked class="text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs font-medium text-emerald-700">Pass</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="items[{{ $key }}][status]" value="fail" class="text-red-600 focus:ring-red-500">
                    <span class="text-xs font-medium text-red-700">Fail</span>
                </label>
                {{-- Photo upload placeholder --}}
                <input type="text" name="items[{{ $key }}][photo]" placeholder="Photo path (optional)" class="w-36 px-2 py-1 text-xs border border-gray-200 rounded-lg text-gray-500">
                {{-- Comment --}}
                <input type="text" name="items[{{ $key }}][comment]" placeholder="Comment" class="w-44 px-2 py-1 text-xs border border-gray-200 rounded-lg">
            </div>
        </div>
        @endforeach
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-100 focus:border-primary-400 outline-none" placeholder="Overall inspection notes..."></textarea>
    </div>

    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-5 py-2.5 rounded-xl text-sm shadow-sm transition-colors">
        Submit Inspection
    </button>
</form>

@endsection
