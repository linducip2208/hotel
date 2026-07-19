@extends('panel.layout')
@section('title', 'Spa Treatments')
@section('content')

<div class="mb-6" x-data="{ editing: null }">
    <h1 class="text-2xl font-bold text-gray-900">Spa Treatments</h1>
    <p class="text-sm text-gray-500 mt-0.5">Kelola menu treatment, durasi, dan harga</p>
</div>

<div class="grid md:grid-cols-3 gap-5" x-data="{ editing: null }">

    {{-- Treatments list --}}
    <div class="md:col-span-2">
        {{-- Search --}}
        <form method="GET" action="{{ route('panel.spa.treatments') }}" class="mb-4">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode treatment..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
        </form>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Treatment</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Durasi</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Harga</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($treatments as $t)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $t->name }}</div>
                                        <div class="text-xs font-mono text-gray-400">{{ $t->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-sm font-medium text-gray-700">{{ $t->duration_minutes }}</span>
                                <span class="text-xs text-gray-400 ml-1">menit</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-900 font-medium">
                                Rp {{ number_format($t->price, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="editing = (editing === {{ $t->id }} ? null : {{ $t->id }})"
                                            class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-1 rounded-lg hover:bg-amber-100 transition-colors">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('panel.spa.treatments.destroy', $t->id) }}"
                                          onsubmit="return confirm('Hapus treatment {{ $t->name }}?')">
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
                        <tr x-show="editing === {{ $t->id }}" x-cloak class="bg-amber-50/30">
                            <td colspan="4" class="px-5 py-3">
                                <form method="POST" action="{{ route('panel.spa.treatments.update', $t->id) }}" class="grid grid-cols-4 gap-3">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Kode</label>
                                        <input type="text" name="code" required value="{{ $t->code }}"
                                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-mono outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Nama</label>
                                        <input type="text" name="name" required value="{{ $t->name }}"
                                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Durasi (menit)</label>
                                        <input type="number" name="duration_minutes" required min="15" value="{{ $t->duration_minutes }}"
                                               class="w-full rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Harga (Rp)</label>
                                        <div class="flex gap-1.5">
                                            <input type="number" name="price" step="1" required value="{{ $t->price }}"
                                                   class="w-full rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                            <button type="submit"
                                                    class="shrink-0 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                                Simpan
                                            </button>
                                            <button type="button" @click="editing = null"
                                                    class="shrink-0 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                                Batal
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-sm text-gray-400">Belum ada treatment.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3 px-1">
            {{ $treatments->links() }}
        </div>
    </div>

    {{-- Add treatment form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Tambah Treatment</h2>
        </div>
        <form method="POST" action="{{ route('panel.spa.treatments.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="code" required placeholder="MASSAGE-60"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Swedish Massage"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Durasi (menit) <span class="text-red-500">*</span></label>
                <input type="number" name="duration_minutes" required min="15" placeholder="60"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Harga (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="price" step="1" required placeholder="250000"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deskripsi</label>
                <textarea name="description" rows="3" placeholder="Deskripsi singkat…"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            </div>
            <button type="submit"
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Tambah Treatment
            </button>
        </form>
    </div>

</div>

@endsection
