@extends('panel.layout')
@section('title', 'Kabin Spa')
@section('content')

<div class="mb-6" x-data="{ editing: null }">
    <h1 class="text-2xl font-bold text-gray-900">Kabin Spa</h1>
    <p class="text-sm text-gray-500 mt-0.5">Kelola kabin perawatan spa</p>
</div>

<div class="grid md:grid-cols-3 gap-5" x-data="{ editing: null }">

    {{-- Cabins list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Kabin</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($cabins as $c)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ $c->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @php
                                    $typeLabel = match($c->type) { 'couple' => 'Kabin Pasangan', 'vip' => 'Kabin VIP', default => 'Kabin Single' };
                                    $typeColor = match($c->type) { 'couple' => 'rose', 'vip' => 'amber', default => 'blue' };
                                @endphp
                                <span class="text-xs font-medium bg-{{ $typeColor }}-50 text-{{ $typeColor }}-700 px-2.5 py-1 rounded-full">{{ $typeLabel }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if ($c->is_active)
                                <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                                @else
                                <span class="text-xs font-medium bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="editing = (editing === {{ $c->id }} ? null : {{ $c->id }})"
                                            class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-1 rounded-lg hover:bg-amber-100 transition-colors">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('panel.spa.cabins.destroy', $c->id) }}"
                                          onsubmit="return confirm('Hapus kabin {{ $c->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="text-xs font-semibold text-red-600 bg-red-50 px-2 py-1 rounded-lg hover:bg-red-100 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        {{-- Inline edit row --}}
                        <tr x-show="editing === {{ $c->id }}" x-cloak class="bg-amber-50/30">
                            <td colspan="4" class="px-5 py-3">
                                <form method="POST" action="{{ route('panel.spa.cabins.update', $c->id) }}" class="flex items-end gap-3">
                                    @csrf @method('PUT')
                                    <div class="flex-1">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Kabin</label>
                                        <input type="text" name="name" required value="{{ $c->name }}"
                                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe</label>
                                        <select name="type"
                                                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                            <option value="single" {{ $c->type === 'single' ? 'selected' : '' }}>Single</option>
                                            <option value="couple" {{ $c->type === 'couple' ? 'selected' : '' }}>Pasangan</option>
                                            <option value="vip" {{ $c->type === 'vip' ? 'selected' : '' }}>VIP</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" value="1" {{ $c->is_active ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-amber-600 focus:ring-amber-400">
                                            <span class="text-xs text-gray-600">Aktif</span>
                                        </label>
                                    </div>
                                    <button type="submit"
                                            class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                        Simpan
                                    </button>
                                    <button type="button" @click="editing = null"
                                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                        Batal
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-sm text-gray-400">Belum ada kabin.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3 px-1">
            {{ $cabins->links() }}
        </div>
    </div>

    {{-- Add cabin form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Tambah Kabin</h2>
        </div>
        <form method="POST" action="{{ route('panel.spa.cabins.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Kabin <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Kabin 1"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe <span class="text-red-500">*</span></label>
                <select name="type" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="single">Kabin Single</option>
                    <option value="couple">Kabin Pasangan</option>
                    <option value="vip">Kabin VIP</option>
                </select>
            </div>
            <button type="submit"
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Tambah Kabin
            </button>
        </form>
    </div>

</div>

@endsection
