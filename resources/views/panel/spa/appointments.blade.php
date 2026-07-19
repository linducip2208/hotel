@extends('panel.layout')
@section('title', 'Spa Appointments')
@section('content')

<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Spa</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($today)->isoFormat('dddd, D MMMM Y') }}</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="date" value="{{ $today }}"
               class="rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm text-gray-700 shadow-card focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none transition-all">
        <button type="submit"
                class="text-sm font-medium text-primary-600 bg-primary-50 px-3.5 py-2 rounded-xl hover:bg-primary-100 transition-colors">Lihat</button>
    </form>
</div>

{{-- Tabs --}}
<div class="flex gap-1 mb-6 border-b border-gray-200 overflow-x-auto" x-data="{ tab: 'appointments' }">
    <button @click="tab = 'appointments'" :class="tab === 'appointments' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors shrink-0">Janji Temu</button>
    <button @click="tab = 'treatments'" :class="tab === 'treatments' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors shrink-0">Treatment</button>
    <button @click="tab = 'therapists'" :class="tab === 'therapists' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors shrink-0">Therapist</button>
    <button @click="tab = 'cabins'" :class="tab === 'cabins' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors shrink-0">Kabin</button>
    <button @click="tab = 'memberships'" :class="tab === 'memberships' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
            class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors shrink-0">Membership</button>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Appointments --}}
    <div class="md:col-span-2">
        {{-- Search for guest name --}}
        <form method="GET" action="{{ route('panel.spa.appointments') }}" class="mb-4">
            <input type="hidden" name="date" value="{{ $today }}">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama tamu..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
        </form>

        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Jadwal Hari Ini</h2>
                <span class="text-xs text-gray-400">{{ count($apps) }} janji temu</span>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse ($apps as $a)
                @php
                    $statusColors = ['booked' => 'blue', 'in_progress' => 'amber', 'completed' => 'emerald', 'cancelled' => 'gray'];
                    $sc = $statusColors[$a->status] ?? 'gray';
                @endphp
                <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50/60 transition-colors">
                    {{-- Time --}}
                    <div class="w-16 text-center shrink-0">
                        <div class="text-sm font-bold text-gray-900">{{ $a->start_at->format('H:i') }}</div>
                        <div class="text-xs text-gray-400">{{ $a->end_at->format('H:i') }}</div>
                    </div>

                    {{-- Divider --}}
                    <div class="w-px h-10 bg-gray-100 shrink-0"></div>

                    {{-- Details --}}
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900">{{ $a->treatment?->name }}</div>
                        <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-500">
                            @if ($a->therapist?->name)
                            <span>{{ $a->therapist->name }}</span>
                            @endif
                            @if ($a->cabin?->name)
                            <span>· {{ $a->cabin->name }}</span>
                            @endif
                            @if ($a->guest?->name)
                            <span>· {{ $a->guest->name }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Status + actions --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ str_replace('_', ' ', $a->status) }}</span>
                        @if ($a->status === 'booked')
                        <form method="POST" action="{{ route('panel.spa.appointments.complete', $a->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg hover:bg-emerald-100 transition-colors">
                                Selesai
                            </button>
                        </form>
                        @endif
                        @if (in_array($a->status, ['booked', 'in_progress']))
                        <form method="POST" action="{{ route('panel.spa.appointments.cancel', $a->id) }}"
                              onsubmit="return confirm('Batalkan janji temu ini?')">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="text-xs font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg hover:bg-amber-100 transition-colors">
                                Batal
                            </button>
                        </form>
                        @endif
                        @if (in_array($a->status, ['completed', 'cancelled']))
                        <form method="POST" action="{{ route('panel.spa.appointments.destroy', $a->id) }}"
                              onsubmit="return confirm('Hapus janji temu ini secara permanen?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-xs font-semibold text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 transition-colors">
                                Hapus
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">Tidak ada janji temu hari ini</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Book form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Buat Janji Temu</h2>
        </div>
        <form method="POST" action="{{ route('panel.spa.appointments.book') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Treatment <span class="text-red-500">*</span></label>
                <select name="treatment_id" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="">— pilih —</option>
                    @foreach ($treatments as $t)
                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->duration_minutes }}m)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Therapist</label>
                <select name="therapist_id"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="">Tersedia manapun</option>
                    @foreach ($therapists as $tp)
                    <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kabin</label>
                <select name="cabin_id"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="">Otomatis</option>
                    @foreach ($cabins as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Waktu Mulai <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="start_at" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Folio ID (charge ke kamar)</label>
                <input type="number" name="folio_id" placeholder="Opsional"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Buat Janji Temu
            </button>
        </form>
    </div>

</div>

@endsection
