@extends('panel.layout')
@section('title', 'Edit Outlet')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.pos.outlets.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Outlet</h1>
        <p class="text-sm text-gray-500 mt-0.5 font-mono">{{ $outlet->code }}</p>
    </div>
</div>

<div class="max-w-xl bg-white rounded-2xl shadow-card border border-gray-100 p-5">
    <form method="POST" action="{{ route('panel.pos.outlets.update', $outlet->id) }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Outlet <span class="text-red-500">*</span></label>
            <input type="text" name="name" required maxlength="100" value="{{ old('name', $outlet->name) }}"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe <span class="text-red-500">*</span></label>
            <select name="type" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                @foreach (['restaurant' => 'Restoran', 'bar' => 'Bar', 'spa' => 'Spa', 'minibar' => 'Minibar', 'room_service' => 'Room Service', 'other' => 'Lainnya'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('type', $outlet->type) === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="charge_to_room_enabled" value="0">
                <input type="checkbox" name="charge_to_room_enabled" value="1" {{ old('charge_to_room_enabled', $outlet->charge_to_room_enabled) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-primary-600">
                Izinkan charge ke kamar (post ke folio)
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="takeaway_enabled" value="0">
                <input type="checkbox" name="takeaway_enabled" value="1" {{ old('takeaway_enabled', $outlet->takeaway_enabled) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-primary-600">
                Izinkan takeaway
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $outlet->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-primary-600">
                Outlet aktif
            </label>
        </div>
        <div class="flex gap-2">
            <button type="submit" :disabled="submitting"
                    class="bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-colors">
                <span x-show="!submitting">Simpan Perubahan</span>
                <span x-show="submitting" x-cloak>Menyimpan…</span>
            </button>
            <a href="{{ route('panel.pos.outlets.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 py-2.5">Batal</a>
        </div>
    </form>
</div>

@endsection
