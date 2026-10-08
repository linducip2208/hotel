@extends('panel.layout')
@section('title', 'New Reservation')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.fo.reservations.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">New Reservation</h1>
        <p class="text-sm text-gray-500 mt-0.5">Create a booking — multiple rooms supported</p>
    </div>
</div>

@if ($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-start gap-2">
    <svg class="w-4 h-4 mt-0.5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
    <span>{{ $errors->first() }}</span>
</div>
@endif

<form method="POST" action="{{ route('panel.fo.reservations.store') }}" class="space-y-5 max-w-3xl" x-data="resForm()">

    {{-- 1. Stay dates --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">1</span>
                Stay
            </h2>
        </div>
        <div class="p-5 grid md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Check-in <span class="text-red-500">*</span></label>
                <input type="date" name="check_in" value="{{ old('check_in') }}" required x-model="checkIn" x-on:change="updateNights()"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Check-out <span class="text-red-500">*</span></label>
                <input type="date" name="check_out" value="{{ old('check_out') }}" required x-model="checkOut" x-on:change="updateNights()"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
            </div>
            <div class="text-sm text-gray-500 pb-2.5">
                <span x-show="nights > 0" x-cloak><span class="font-semibold text-gray-800" x-text="nights"></span> malam</span>
                <span x-show="nights <= 0">Pilih tanggal menginap</span>
            </div>
        </div>
    </div>

    {{-- 2. Rooms (multi) --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">2</span>
                Rooms
            </h2>
            <button type="button" @click="addRoom()"
                    class="text-xs font-medium text-primary-600 hover:text-primary-800 transition-colors">+ Tambah Kamar</button>
        </div>
        <div class="p-5 space-y-4">
            <template x-for="(room, i) in rooms" :key="i">
                <div class="rounded-xl border border-gray-100 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide" x-text="'Kamar ' + (i + 1)"></span>
                        <button type="button" @click="rooms.length > 1 && rooms.splice(i, 1)"
                                :class="rooms.length <= 1 ? 'opacity-20 cursor-not-allowed' : 'text-gray-300 hover:text-red-400'"
                                class="text-xs font-medium transition-colors">Hapus</button>
                    </div>
                    <div class="grid md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Room Type <span class="text-red-500">*</span></label>
                            <select :name="'rooms['+i+'][room_type_id]'" required x-model="room.room_type_id"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 transition-all outline-none">
                                <option value="">— pilih —</option>
                                @foreach ($roomTypes as $rt)
                                    <option value="{{ $rt->id }}">{{ $rt->name }} (max {{ $rt->max_occupancy }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rate Plan <span class="text-red-500">*</span></label>
                            <select :name="'rooms['+i+'][rate_plan_id]'" required x-model="room.rate_plan_id"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 transition-all outline-none">
                                <option value="">— pilih —</option>
                                @foreach ($ratePlans as $rp)
                                    <option value="{{ $rp->id }}">{{ $rp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Adults <span class="text-red-500">*</span></label>
                            <input type="number" :name="'rooms['+i+'][adults]'" required min="1" max="10" value="{{ old('rooms.0.adults', 1) }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Children</label>
                            <input type="number" :name="'rooms['+i+'][children]'" min="0" max="10" value="0"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 transition-all outline-none">
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- 3. Primary Guest --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">3</span>
                Primary Guest
            </h2>
        </div>
        <div class="p-5 space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="primary_guest[first_name]" value="{{ old('primary_guest.first_name') }}" required maxlength="100" placeholder="John"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Last Name</label>
                    <input type="text" name="primary_guest[last_name]" value="{{ old('primary_guest.last_name') }}" maxlength="100" placeholder="Doe"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email</label>
                    <input type="email" name="primary_guest[email]" value="{{ old('primary_guest.email') }}" placeholder="john@example.com"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Phone / WhatsApp</label>
                    <input type="tel" name="primary_guest[phone]" value="{{ old('primary_guest.phone') }}" maxlength="30" placeholder="+62 812 ..."
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Source & Notes --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center text-xs font-bold">4</span>
                Source &amp; Notes
            </h2>
        </div>
        <div class="p-5 space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Booking Source</label>
                    <select name="source"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 transition-all outline-none">
                        @foreach ($sources as $key => $label)
                            <option value="{{ $key }}" @selected(old('source', 'direct') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Special Requests</label>
                <textarea name="special_requests" rows="3" maxlength="1000" placeholder="Late check-in, extra pillows, honeymoon setup…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none resize-none">{{ old('special_requests') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center gap-3 pt-1">
        <button type="submit" x-on:click="submitting = true"
                class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Create Reservation
        </button>
        <a href="{{ route('panel.fo.reservations.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Cancel</a>
    </div>

</form>

@endsection

@push('scripts')
<script>
function resForm() {
    return {
        checkIn: '{{ old('check_in') }}',
        checkOut: '{{ old('check_out') }}',
        nights: 0,
        submitting: false,
        rooms: [{ room_type_id: '{{ old('rooms.0.room_type_id') }}', rate_plan_id: '{{ old('rooms.0.rate_plan_id') }}' }],
        addRoom() { this.rooms.push({ room_type_id: '', rate_plan_id: '' }); },
        updateNights() {
            if (!this.checkIn || !this.checkOut) { this.nights = 0; return; }
            const a = new Date(this.checkIn), b = new Date(this.checkOut);
            const diff = Math.round((b - a) / 86400000);
            this.nights = diff > 0 ? diff : 0;
        },
        init() { this.updateNights(); },
    };
}
</script>
@endpush
