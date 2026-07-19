@extends('panel.layout')
@section('title', 'Group Block Baru')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.fo.group-blocks.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Group Block Baru</h1>
        <p class="text-sm text-gray-500 mt-0.5">Buat reservasi grup untuk rombongan</p>
    </div>
</div>

@if ($errors->any())
<div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-start gap-2">
    <svg class="w-4 h-4 mt-0.5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
    <span>{{ $errors->first() }}</span>
</div>
@endif

<form method="POST" action="{{ route('panel.fo.group-blocks.store') }}" class="space-y-5 max-w-2xl">
    @csrf

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">1</span>
                Info Grup
            </h2>
        </div>
        <div class="p-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Grup <span class="text-red-500">*</span></label>
                <input type="text" name="group_name" value="{{ old('group_name') }}" required placeholder="Contoh: Tour Jogja, Pernikahan Budi"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tamu Master <span class="text-red-500">*</span></label>
                <select name="guest_id" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                    <option value="">— pilih tamu —</option>
                    @foreach ($guests as $guest)
                    <option value="{{ $guest->id }}" @selected(old('guest_id') == $guest->id)>{{ $guest->full_name }} · {{ $guest->phone ?? '—' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan</label>
                <textarea name="notes" rows="2" placeholder="Info tambahan grup..."
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none resize-none">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">2</span>
                Tanggal Menginap
            </h2>
        </div>
        <div class="p-5 grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Check-in <span class="text-red-500">*</span></label>
                <input type="date" name="check_in" value="{{ old('check_in') }}" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Check-out <span class="text-red-500">*</span></label>
                <input type="date" name="check_out" value="{{ old('check_out') }}" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Batas Cutoff</label>
                <input type="date" name="cutoff_date" value="{{ old('cutoff_date') }}"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold">3</span>
                Detail Kamar
            </h2>
        </div>
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe Kamar <span class="text-red-500">*</span></label>
                    <select name="room_type_id" required
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                        <option value="">— pilih tipe —</option>
                        @foreach ($roomTypes as $rt)
                        <option value="{{ $rt->id }}" @selected(old('room_type_id') == $rt->id)>{{ $rt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah Kamar <span class="text-red-500">*</span></label>
                    <input type="number" name="rooms_count" value="{{ old('rooms_count', 1) }}" required min="1" max="999"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rate Plan</label>
                    <select name="rate_plan_id"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                        <option value="">— pilih —</option>
                        @foreach ($ratePlans as $rp)
                        <option value="{{ $rp->id }}" @selected(old('rate_plan_id') == $rp->id)>{{ $rp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rate Negosiasi (per malam)</label>
                    <input type="number" name="negotiated_rate" value="{{ old('negotiated_rate') }}" step="0.01" min="0" placeholder="Rp"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 pt-1">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Buat Group Block
        </button>
        <a href="{{ route('panel.fo.group-blocks.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Batal</a>
    </div>
</form>

@endsection
