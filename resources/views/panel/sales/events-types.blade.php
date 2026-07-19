@extends('panel.layout')
@section('title', 'Tipe Event')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Tipe Event</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola tipe event: Wedding, Meeting, Birthday, dll</p>
    </div>
    <a href="{{ route('panel.sales.events.index') }}"
       class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Kalender Event
    </a>
</div>

{{-- Add Form --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card mb-6">
    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-700">Tambah Tipe Event</h2>
    </div>
    <form method="POST" action="{{ route('panel.sales.events.types.store') }}" class="p-5">
        @csrf
        <div class="grid md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all"
                       placeholder="cth: Wedding">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Icon</label>
                <input type="text" name="icon" value="cake"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Min Tamu</label>
                <input type="number" name="min_guests" value="10"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Max Tamu</label>
                <input type="number" name="max_guests" value="500"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div class="flex items-end gap-2">
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs font-semibold text-gray-600">Aktif</span>
                </label>
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Types List --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Icon</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Min Tamu</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Max Tamu</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($types as $t)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-lg">{{ match($t->icon) { 'cake' => '🎂', 'ring' => '💍', 'briefcase' => '💼', 'star' => '⭐', 'heart' => '❤️', default => '📋' } }}</td>
                    <td class="px-4 py-3.5 font-medium text-gray-900">{{ $t->name }}</td>
                    <td class="px-4 py-3.5 text-center text-gray-600">{{ $t->min_guests }}</td>
                    <td class="px-4 py-3.5 text-center text-gray-600">{{ $t->max_guests }}</td>
                    <td class="px-4 py-3.5 text-center">
                        @if($t->is_active)
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>Non-aktif
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <button onclick="document.getElementById('editModal{{ $t->id }}').classList.remove('hidden')"
                                class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('panel.sales.events.types.destroy', $t->id) }}" class="inline"
                              onsubmit="return confirm('Hapus tipe event ini?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>

                        {{-- Edit Modal --}}
                        <div id="editModal{{ $t->id }}" class="hidden fixed inset-0 z-50 overflow-y-auto">
                            <div class="min-h-screen flex items-center justify-center p-4">
                                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('editModal{{ $t->id }}').classList.add('hidden')"></div>
                                <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-bold text-gray-900">Edit {{ $t->name }}</h3>
                                        <button onclick="document.getElementById('editModal{{ $t->id }}').classList.add('hidden')"
                                                class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <form method="POST" action="{{ route('panel.sales.events.types.update', $t->id) }}" class="space-y-3">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $t->name }}" required
                                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                                        <input type="text" name="icon" value="{{ $t->icon }}"
                                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                                        <div class="grid grid-cols-2 gap-3">
                                            <input type="number" name="min_guests" value="{{ $t->min_guests }}"
                                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                                            <input type="number" name="max_guests" value="{{ $t->max_guests }}"
                                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                                        </div>
                                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1" {{ $t->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-xs font-semibold text-gray-600">Aktif</span>
                                        </label>
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
                                            Simpan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-10 text-center text-sm text-gray-400">Belum ada tipe event.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($types->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $types->links() }}
    </div>
    @endif
</div>

@endsection
