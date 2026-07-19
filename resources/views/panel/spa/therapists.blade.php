@extends('panel.layout')
@section('title', 'Therapists')
@section('content')

<div class="mb-6" x-data="{ editing: null }">
    <h1 class="text-2xl font-bold text-gray-900">Therapists</h1>
    <p class="text-sm text-gray-500 mt-0.5">Profil dan ketersediaan therapist spa</p>
</div>

<div class="grid md:grid-cols-3 gap-5" x-data="{ editing: null }">

    {{-- Therapists list --}}
    <div class="md:col-span-2">
        {{-- Search --}}
        <form method="GET" action="{{ route('panel.spa.therapists') }}" class="mb-4">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama therapist..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
        </form>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="divide-y divide-gray-50">
                @forelse ($therapists as $tp)
                @php
                    $initials = collect(explode(' ', $tp->name))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                    $genderColor = match($tp->gender) { 'M' => 'blue', 'F' => 'rose', default => 'gray' };
                @endphp
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-{{ $genderColor }}-100 text-{{ $genderColor }}-700 flex items-center justify-center text-sm font-bold shrink-0">
                        {{ $initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900">{{ $tp->name }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">
                            {{ $tp->gender === 'M' ? 'Pria' : ($tp->gender === 'F' ? 'Wanita' : 'Tidak diketahui') }}
                        </div>
                    </div>
                    @if ($tp->is_active)
                    <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                    </span>
                    @else
                    <span class="text-xs font-medium bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full shrink-0">Nonaktif</span>
                    @endif
                    {{-- Actions --}}
                    <div class="flex items-center gap-1 shrink-0">
                        <button @click="editing = (editing === {{ $tp->id }} ? null : {{ $tp->id }})"
                                class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-1 rounded-lg hover:bg-amber-100 transition-colors">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('panel.spa.therapists.destroy', $tp->id) }}"
                              onsubmit="return confirm('Hapus therapist {{ $tp->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-xs font-semibold text-red-600 bg-red-50 px-2 py-1 rounded-lg hover:bg-red-100 transition-colors">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
                {{-- Inline edit --}}
                <div x-show="editing === {{ $tp->id }}" x-cloak class="bg-amber-50/30 px-5 py-3">
                    <form method="POST" action="{{ route('panel.spa.therapists.update', $tp->id) }}" class="flex items-end gap-3">
                        @csrf @method('PUT')
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Nama</label>
                            <input type="text" name="name" required value="{{ $tp->name }}"
                                   class="w-full rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Gender</label>
                            <select name="gender"
                                    class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                                <option value="">—</option>
                                <option value="M" {{ $tp->gender === 'M' ? 'selected' : '' }}>Pria</option>
                                <option value="F" {{ $tp->gender === 'F' ? 'selected' : '' }}>Wanita</option>
                            </select>
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
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10">
                    <p class="text-sm text-gray-400">Belum ada therapist.</p>
                </div>
                @endforelse
            </div>
        </div>
        <div class="mt-3 px-1">
            {{ $therapists->links() }}
        </div>
    </div>

    {{-- Add therapist form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Tambah Therapist</h2>
        </div>
        <form method="POST" action="{{ route('panel.spa.therapists.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Nama therapist"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Gender</label>
                <select name="gender"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="">— tidak ditentukan —</option>
                    <option value="M">Pria</option>
                    <option value="F">Wanita</option>
                </select>
            </div>
            <button type="submit"
                    class="w-full bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Tambah Therapist
            </button>
        </form>
    </div>

</div>

@endsection
