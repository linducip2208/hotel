@extends('panel.layout')
@section('title', 'Preferensi Tamu — ' . $guest->full_name)
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.guests.show', $guest->id) }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">Preferensi Tamu</h1>
            <span class="text-xs font-medium bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full">AI Learning</span>
        </div>
        <p class="text-sm text-gray-500 mt-0.5">{{ $guest->full_name }} · {{ $guest->email }}</p>
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

{{-- Auto-apply Toggle --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-5">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-gray-800">Auto-Apply Preferensi</h3>
            <p class="text-sm text-gray-500 mt-0.5">Terapkan otomatis preferensi yang dipelajari saat check-in berikutnya</p>
        </div>
        <form method="POST" action="{{ route('panel.guests.preferences.update', $guest->id) }}">
            @csrf
            <input type="hidden" name="auto_apply" value="{{ ($preferences['auto_apply'] ?? true) ? '0' : '1' }}">
            <button type="submit"
                    class="relative w-12 h-7 rounded-full transition-colors {{ ($preferences['auto_apply'] ?? true) ? 'bg-emerald-500' : 'bg-gray-300' }}">
                <span class="absolute top-0.5 left-0.5 w-6 h-6 rounded-full bg-white shadow-sm transition-transform {{ ($preferences['auto_apply'] ?? true) ? 'translate-x-5' : '' }}"></span>
            </button>
        </form>
    </div>
</div>

{{-- Preference Cards Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    @php
    $prefCards = [
        ['key' => 'preferred_floor', 'icon' => 'buildings', 'label' => 'Lantai Kamar', 'unit' => 'lantai', 'type' => 'text'],
        ['key' => 'pillow_type', 'icon' => 'heart', 'label' => 'Jenis Bantal', 'unit' => '', 'type' => 'select', 'options' => ['soft' => 'Lunak', 'firm' => 'Keras', 'hypoallergenic' => 'Hypoallergenic']],
        ['key' => 'extra_blankets', 'icon' => 'fire', 'label' => 'Selimut Tambahan', 'unit' => '', 'type' => 'bool'],
        ['key' => 'newspaper', 'icon' => 'newspaper', 'label' => 'Koran', 'unit' => '', 'type' => 'select', 'options' => ['Kompas' => 'Kompas', 'Jawa Pos' => 'Jawa Pos', 'Jakarta Post' => 'Jakarta Post', 'Tidak' => 'Tidak']],
        ['key' => 'ac_temperature', 'icon' => 'temperature', 'label' => 'Suhu AC', 'unit' => '°C', 'type' => 'number', 'min' => 16, 'max' => 28],
        ['key' => 'wake_up_time', 'icon' => 'clock', 'label' => 'Wake-up Call', 'unit' => '', 'type' => 'time'],
        ['key' => 'minibar_items', 'icon' => 'drink', 'label' => 'Item Minibar', 'unit' => '', 'type' => 'tags'],
    ];
    $icons = [
        'buildings' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'heart' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>',
        'fire' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>',
        'newspaper' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>',
        'temperature' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
        'clock' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'drink' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
    ];
    @endphp

    @foreach ($prefCards as $card)
    @php
        $entry = $preferences[$card['key']] ?? null;
        $value = $entry['value'] ?? null;
        $confidence = $entry['confidence'] ?? 0;
        $lastUpdated = $entry['last_updated'] ?? null;
        $isManual = $entry['manual_override'] ?? false;

        $confColor = $confidence >= 70 ? 'emerald' : ($confidence >= 40 ? 'amber' : 'gray');
    @endphp
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                {!! $icons[$card['icon']] !!}
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-gray-800">{{ $card['label'] }}</h3>
                <p class="text-xs text-gray-400">{{ $card['unit'] }}</p>
            </div>
            @if ($isManual)
            <span class="text-xs font-medium bg-violet-50 text-violet-600 px-2 py-0.5 rounded-full">Manual</span>
            @endif
        </div>

        <div class="mb-3">
            @if ($value === null || $value === '')
                <p class="text-sm text-gray-400 italic">Belum ada data</p>
            @elseif (is_array($value))
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($value as $item)
                    <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">{{ $item }}</span>
                    @endforeach
                </div>
            @elseif (is_bool($value))
                <span class="text-sm font-semibold {{ $value ? 'text-emerald-600' : 'text-gray-400' }}">
                    {{ $value ? 'Ya' : 'Tidak' }}
                </span>
            @else
                <p class="text-lg font-bold text-gray-800">{{ $value }}{{ $card['unit'] ? ' ' . $card['unit'] : '' }}</p>
            @endif
        </div>

        <div class="flex items-center justify-between text-xs text-gray-500">
            <div class="flex items-center gap-2">
                <span class="font-medium">Keyakinan:</span>
                <div class="flex items-center gap-1.5">
                    <span class="font-bold text-{{ $confColor }}-600">{{ $confidence }}%</span>
                    <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $confColor }}-500 rounded-full" style="width:{{ $confidence }}%"></div>
                    </div>
                </div>
            </div>
            @if ($lastUpdated)
            <span>{{ \Carbon\Carbon::parse($lastUpdated)->diffForHumans() }}</span>
            @endif
        </div>

        {{-- Manual override button --}}
        <button onclick="document.getElementById('override-{{ $card['key'] }}').classList.toggle('hidden')"
                class="mt-3 text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
            Override Manual
        </button>

        <form id="override-{{ $card['key'] }}" method="POST"
              action="{{ route('panel.guests.preferences.update', $guest->id) }}" class="hidden mt-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
            @csrf
            <input type="hidden" name="overrides[0][key]" value="{{ $card['key'] }}">
            @if ($card['type'] === 'bool')
            <select name="overrides[0][value]" class="w-full text-sm border border-gray-200 rounded-lg py-1.5 px-3 mb-2">
                <option value="1">Ya</option>
                <option value="0">Tidak</option>
            </select>
            @elseif ($card['type'] === 'select' && isset($card['options']))
            <select name="overrides[0][value]" class="w-full text-sm border border-gray-200 rounded-lg py-1.5 px-3 mb-2">
                <option value="">-- Pilih --</option>
                @foreach ($card['options'] as $optVal => $optLabel)
                <option value="{{ $optVal }}" {{ (string)$value === (string)$optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                @endforeach
            </select>
            @elseif ($card['type'] === 'number')
            <input type="number" name="overrides[0][value]" value="{{ $value }}" min="{{ $card['min'] ?? 16 }}" max="{{ $card['max'] ?? 28 }}"
                   class="w-full text-sm border border-gray-200 rounded-lg py-1.5 px-3 mb-2">
            @elseif ($card['type'] === 'time')
            <input type="time" name="overrides[0][value]" value="{{ $value }}"
                   class="w-full text-sm border border-gray-200 rounded-lg py-1.5 px-3 mb-2">
            @elseif ($card['type'] === 'tags')
            <input type="text" name="overrides[0][value]" value="{{ is_array($value) ? implode(', ', $value) : $value }}" placeholder="Pisahkan dengan koma"
                   class="w-full text-sm border border-gray-200 rounded-lg py-1.5 px-3 mb-2">
            @else
            <input type="text" name="overrides[0][value]" value="{{ $value }}" class="w-full text-sm border border-gray-200 rounded-lg py-1.5 px-3 mb-2">
            @endif
            <button type="submit" class="w-full text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-1.5 rounded-lg transition-colors">
                Simpan Override
            </button>
        </form>
    </div>
    @endforeach
</div>

{{-- Preference History Timeline --}}
@php $history = $preferences['history'] ?? []; @endphp
@if (!empty($history))
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Riwayat Preferensi</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Preferensi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nilai</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Keyakinan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach (array_reverse(array_slice($history, -20)) as $h)
                @php
                    $labelMap = [
                        'preferred_floor' => 'Lantai Kamar',
                        'pillow_type' => 'Jenis Bantal',
                        'extra_blankets' => 'Selimut Tambahan',
                        'newspaper' => 'Koran',
                        'ac_temperature' => 'Suhu AC',
                        'wake_up_time' => 'Wake-up Call',
                        'minibar_items' => 'Item Minibar',
                    ];
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3 text-sm text-gray-700">{{ $labelMap[$h['key']] ?? $h['key'] }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $h['value'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-semibold text-gray-600">{{ $h['confidence'] }}%</span>
                    </td>
                    <td class="px-4 py-3 text-right text-xs text-gray-400">{{ \Carbon\Carbon::parse($h['recorded_at'])->format('d M Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
