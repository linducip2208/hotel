@extends('panel.layout')
@section('title', 'Kids Club — Aktivitas Anak')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kids Club</h1>
        <p class="text-sm text-gray-500 mt-0.5">Aktivitas dan kegiatan anak-anak di hotel</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('panel.kids-club.bookings') }}" class="inline-flex items-center gap-1.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Booking List
        </a>
        <button onclick="document.getElementById('add-activity-modal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Aktivitas
        </button>
    </div>
</div>

@if (session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('error') }}
</div>
@endif

@if ($activities->isEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 px-5 py-16 text-center">
    <div class="flex flex-col items-center gap-3">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-700">Belum ada aktivitas Kids Club</p>
        <p class="text-xs text-gray-400">Tambahkan aktivitas untuk memulai.</p>
    </div>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach ($activities as $activity)
    @php
        $slots = $activity->available_slots;
        $pct = $activity->capacity > 0 ? round(($slots / $activity->capacity) * 100) : 0;
        $capColor = $pct > 50 ? 'emerald' : ($pct > 0 ? 'amber' : 'red');
    @endphp
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ $activity->name }}</h3>
                <span class="text-xs font-medium text-gray-500 mt-0.5 block">
                    {{ $activity->duration_minutes }} menit · Rp {{ number_format($activity->price, 0, ',', '.') }}
                </span>
            </div>
            <span class="text-xs font-semibold bg-purple-50 text-purple-700 px-2.5 py-1 rounded-full">
                {{ $activity->age_range }}
            </span>
        </div>

        <div class="mb-2">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Tersedia: {{ $slots }} / {{ $activity->capacity }}</span>
                <span>{{ $pct }}%</span>
            </div>
            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-{{ $capColor }}-500 rounded-full transition-all duration-300" style="width:{{ $pct }}%"></div>
            </div>
        </div>

        @if ($activity->schedule)
        <div class="text-xs text-gray-500 mb-3">
            @php $sched = is_string($activity->schedule) ? json_decode($activity->schedule, true) : $activity->schedule; @endphp
            @if (is_array($sched))
            <span class="font-medium">Jadwal:</span>
            @foreach ($sched as $day => $times)
            <span class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">{{ $day }}: {{ is_array($times) ? implode(', ', $times) : $times }}</span>
            @endforeach
            @endif
        </div>
        @endif

        <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
            <button onclick="openEditActivity({{ $activity->id }}, '{{ addslashes($activity->name) }}', {{ $activity->age_min }}, {{ $activity->age_max }}, {{ $activity->capacity }}, {{ $activity->price }}, {{ $activity->duration_minutes }})"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                Edit
            </button>
            <form method="POST" action="{{ route('panel.kids-club.destroy', $activity->id) }}" onsubmit="return confirm('Hapus aktivitas ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">Hapus</button>
            </form>
            <span class="flex-1"></span>
            @if ($activity->is_active)
            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Aktif</span>
            @else
            <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Nonaktif</span>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Add Activity Modal --}}
<div id="add-activity-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="this.parentElement.parentElement.classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Tambah Aktivitas Kids Club</h2>
                <button onclick="document.getElementById('add-activity-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('panel.kids-club.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Aktivitas</label>
                        <input type="text" name="name" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usia Min</label>
                            <input type="number" name="age_min" value="3" min="0" max="17" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usia Max</label>
                            <input type="number" name="age_max" value="12" min="1" max="18" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas</label>
                            <input type="number" name="capacity" value="10" min="1" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                            <input type="number" name="price" value="0" min="0" step="1000" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (menit)</label>
                        <input type="number" name="duration_minutes" value="60" min="15" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label class="text-sm text-gray-700">Aktif</label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('add-activity-modal').classList.add('hidden')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Activity Modal --}}
<div id="edit-activity-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="this.parentElement.parentElement.classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Edit Aktivitas</h2>
                <button onclick="document.getElementById('edit-activity-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="edit-activity-form" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Aktivitas</label>
                        <input type="text" name="name" id="edit-name" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usia Min</label>
                            <input type="number" name="age_min" id="edit-age-min" min="0" max="17" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usia Max</label>
                            <input type="number" name="age_max" id="edit-age-max" min="1" max="18" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas</label>
                            <input type="number" name="capacity" id="edit-capacity" min="1" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp)</label>
                            <input type="number" name="price" id="edit-price" min="0" step="1000" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (menit)</label>
                        <input type="number" name="duration_minutes" id="edit-duration" min="15" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('edit-activity-modal').classList.add('hidden')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditActivity(id, name, ageMin, ageMax, capacity, price, duration) {
    document.getElementById('edit-activity-form').action = '{{ url('panel/kids-club') }}/' + id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-age-min').value = ageMin;
    document.getElementById('edit-age-max').value = ageMax;
    document.getElementById('edit-capacity').value = capacity;
    document.getElementById('edit-price').value = price;
    document.getElementById('edit-duration').value = duration;
    document.getElementById('edit-activity-modal').classList.remove('hidden');
}
</script>

@endsection
