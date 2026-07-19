@extends('panel.layout')
@section('title', 'New Event')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.banquet.events.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">New Event</h1>
        <p class="text-sm text-gray-500 mt-0.5">Create a new banquet or function event</p>
    </div>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('panel.banquet.events.store') }}" class="space-y-5">
        @csrf

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">Event Details</h2>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Event Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required placeholder="Annual Gala Dinner 2025"
                       value="{{ old('title') }}"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Event Type <span class="text-red-500">*</span></label>
                    <select name="event_type" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="wedding">Wedding</option>
                        <option value="meeting">Meeting</option>
                        <option value="gala">Gala</option>
                        <option value="conference">Conference</option>
                        <option value="seminar">Seminar</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Function Room <span class="text-red-500">*</span></label>
                    <select name="function_room_id" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        @foreach ($rooms as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">Schedule</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="event_date" required value="{{ old('event_date') }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Start Time <span class="text-red-500">*</span></label>
                    <input type="time" name="start_time" required value="{{ old('start_time') }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">End Time <span class="text-red-500">*</span></label>
                    <input type="time" name="end_time" required value="{{ old('end_time') }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">Setup & Pricing</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Attendees <span class="text-red-500">*</span></label>
                    <input type="number" name="expected_attendees" required min="1" placeholder="100"
                           value="{{ old('expected_attendees') }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Room Setup</label>
                    <select name="setup"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="classroom">Classroom</option>
                        <option value="theatre">Theatre</option>
                        <option value="banquet">Banquet</option>
                        <option value="ushape">U-Shape</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Venue Rate (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="venue_rate" required step="1" min="0" placeholder="5000000"
                           value="{{ old('venue_rate') }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Notes</label>
                <textarea name="notes" rows="3" placeholder="Special requirements, AV setup, dietary restrictions…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-colors">
                Create Event
            </button>
            <a href="{{ route('panel.banquet.events.index') }}"
               class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

@endsection
