@extends('panel.layout')
@section('title', 'Buat Event Booking')
@section('content')

<div class="max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('panel.sales.events.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Daftar Event
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Buat Event Booking</h1>
        <p class="text-sm text-gray-500 mt-0.5">Buat reservasi event, wedding, atau acara baru</p>
    </div>

    <form method="POST" action="{{ route('panel.sales.events.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-gray-100 shadow-card divide-y divide-gray-50">
            {{-- Info Dasar --}}
            <div class="p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Info Dasar
                </h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Event <span class="text-rose-500">*</span></label>
                        <input type="text" name="event_name" value="{{ old('event_name') }}" required
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                               placeholder="cth: Wedding Anniversary Budi & Ani">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe Event <span class="text-rose-500">*</span></label>
                        <select name="event_type_id" required
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="">-- Pilih --</option>
                            @foreach($eventTypes as $et)
                            <option value="{{ $et->id }}" {{ old('event_type_id') == $et->id ? 'selected' : '' }}>{{ $et->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tamu / Klien <span class="text-rose-500">*</span></label>
                        <select name="guest_id" required
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="">-- Pilih Tamu --</option>
                            @foreach($guests as $g)
                            <option value="{{ $g->id }}" {{ old('guest_id') == $g->id ? 'selected' : '' }}>{{ $g->full_name }} — {{ $g->phone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Penanggung Jawab</label>
                        <select name="assigned_to_user_id"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="">-- Pilih --</option>
                            @foreach(\App\Models\User::where('property_id', app('current_property')->id)->get() as $u)
                            <option value="{{ $u->id }}" {{ old('assigned_to_user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Tanggal & Waktu --}}
            <div class="p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Tanggal & Waktu
                </h2>
                <div class="grid md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="event_date" value="{{ old('event_date') }}" required
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jam Mulai <span class="text-rose-500">*</span></label>
                        <input type="time" name="start_time" value="{{ old('start_time') }}" required
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jam Selesai <span class="text-rose-500">*</span></label>
                        <input type="time" name="end_time" value="{{ old('end_time') }}" required
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah Tamu <span class="text-rose-500">*</span></label>
                        <input type="number" name="expected_guests" value="{{ old('expected_guests') }}" required min="1"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
            </div>

            {{-- Venue & Finansial --}}
            <div class="p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Venue & Finansial
                </h2>
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Venue / Ruangan</label>
                        <select name="venue_id"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach($rooms as $r)
                            <option value="{{ $r->id }}" {{ old('venue_id') == $r->id ? 'selected' : '' }}>{{ $r->room_number }} — {{ $r->roomType?->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Total Penawaran (Rp)</label>
                        <input type="number" name="total_quoted" value="{{ old('total_quoted', 0) }}" step="1000"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deposit Dibayar (Rp)</label>
                        <input type="number" name="deposit_paid" value="{{ old('deposit_paid', 0) }}" step="1000"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    </div>
                </div>
            </div>

            {{-- Setup & Catering --}}
            <div class="p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066"/></svg>
                    Setup & Catering
                </h2>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Setup Requirements (JSON)</label>
                        <textarea name="setup_requirements" rows="3"
                                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                                  placeholder='["stage","sound_system","projector","lighting"]'>{{ old('setup_requirements') }}</textarea>
                        <p class="text-[11px] text-gray-400 mt-1">Format JSON array</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catering Requirements (JSON)</label>
                        <textarea name="catering_requirements" rows="3"
                                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                                  placeholder='{"menu":"buffet","dietary":["vegetarian","no_pork"],"pax":150}'>{{ old('catering_requirements') }}</textarea>
                        <p class="text-[11px] text-gray-400 mt-1">Format JSON object</p>
                    </div>
                </div>
            </div>

            {{-- Catatan --}}
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Permintaan Khusus Tamu</label>
                        <textarea name="special_requests" rows="3"
                                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                                  placeholder="Permintaan khusus dari tamu...">{{ old('special_requests') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan Internal</label>
                        <textarea name="internal_notes" rows="3"
                                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                                  placeholder="Catatan untuk tim internal...">{{ old('internal_notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm shadow-indigo-500/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Simpan Booking
            </button>
            <a href="{{ route('panel.sales.events.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
        </div>
    </form>
</div>

@endsection
