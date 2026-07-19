@extends('panel.layout')
@section('title', 'Edit Event')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.banquet.events.show', $event->id) }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Event</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $event->event_no }} — {{ $event->title }}</p>
    </div>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('panel.banquet.events.update', $event->id) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">Event Details</h2>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Judul Event <span class="text-red-500">*</span></label>
                <input type="text" name="title" required placeholder="Annual Gala Dinner 2025"
                       value="{{ old('title', $event->title) }}"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe Event <span class="text-red-500">*</span></label>
                    <select name="event_type" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="wedding" {{ old('event_type', $event->event_type) === 'wedding' ? 'selected' : '' }}>Wedding</option>
                        <option value="meeting" {{ old('event_type', $event->event_type) === 'meeting' ? 'selected' : '' }}>Meeting</option>
                        <option value="gala" {{ old('event_type', $event->event_type) === 'gala' ? 'selected' : '' }}>Gala</option>
                        <option value="conference" {{ old('event_type', $event->event_type) === 'conference' ? 'selected' : '' }}>Conference</option>
                        <option value="seminar" {{ old('event_type', $event->event_type) === 'seminar' ? 'selected' : '' }}>Seminar</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Function Room <span class="text-red-500">*</span></label>
                    <select name="function_room_id" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        @foreach ($functionRooms as $r)
                        <option value="{{ $r->id }}" {{ old('function_room_id', $event->function_room_id) == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Perusahaan</label>
                    <select name="company_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="">— Pilih Perusahaan —</option>
                        @foreach ($companies as $c)
                        <option value="{{ $c->id }}" {{ old('company_id', $event->company_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kontak Utama</label>
                    <select name="primary_contact_guest_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="">— Pilih Kontak —</option>
                        @foreach ($guests as $g)
                        <option value="{{ $g->id }}" {{ old('primary_contact_guest_id', $event->primary_contact_guest_id) == $g->id ? 'selected' : '' }}>{{ $g->last_name }}, {{ $g->first_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status <span class="text-red-500">*</span></label>
                <select name="status" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="inquiry" {{ old('status', $event->status) === 'inquiry' ? 'selected' : '' }}>Inquiry</option>
                    <option value="tentative" {{ old('status', $event->status) === 'tentative' ? 'selected' : '' }}>Tentative</option>
                    <option value="definite" {{ old('status', $event->status) === 'definite' ? 'selected' : '' }}>Definite</option>
                    <option value="completed" {{ old('status', $event->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">Jadwal</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="event_date" required value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="start_time" required value="{{ old('start_time', $event->start_time?->format('H:i')) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jam Selesai <span class="text-red-500">*</span></label>
                    <input type="time" name="end_time" required value="{{ old('end_time', $event->end_time?->format('H:i')) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-1">Setup & Harga</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah Tamu</label>
                    <input type="number" name="expected_attendees" min="1" placeholder="100"
                           value="{{ old('expected_attendees', $event->expected_attendees) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Setup Ruangan</label>
                    <select name="setup"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        <option value="">— Pilih —</option>
                        <option value="classroom" {{ old('setup', $event->setup) === 'classroom' ? 'selected' : '' }}>Classroom</option>
                        <option value="theatre" {{ old('setup', $event->setup) === 'theatre' ? 'selected' : '' }}>Theatre</option>
                        <option value="banquet" {{ old('setup', $event->setup) === 'banquet' ? 'selected' : '' }}>Banquet</option>
                        <option value="ushape" {{ old('setup', $event->setup) === 'ushape' ? 'selected' : '' }}>U-Shape</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga Venue (Rp)</label>
                    <input type="number" name="venue_rate" step="1" min="0" placeholder="5000000"
                           value="{{ old('venue_rate', $event->venue_rate) }}"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan</label>
                <textarea name="notes" rows="3" placeholder="Special requirements, AV setup, dietary restrictions…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none">{{ old('notes', $event->notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-colors">
                Simpan Perubahan
            </button>
            <a href="{{ route('panel.banquet.events.show', $event->id) }}"
               class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>

@endsection
