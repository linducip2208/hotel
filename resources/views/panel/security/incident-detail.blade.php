@extends('panel.layout')
@section('title', isset($incident->report_number) ? 'Insiden ' . $incident->report_number : 'Lapor Insiden')
@section('content')

@php
$isNew = !isset($incident->report_number);
$edit = $edit ?? false;
$severityColors = ['low'=>'green','medium'=>'amber','high'=>'red','critical'=>'purple'];
$severityLabels = ['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi','critical'=>'Kritis'];
$statusColors = ['open'=>'red','investigating'=>'amber','resolved'=>'emerald','closed'=>'gray'];
$statusLabels = ['open'=>'Terbuka','investigating'=>'Investigasi','resolved'=>'Selesai','closed'=>'Ditutup'];
$typeLabels = [
    'guest_injury'=>'Cedera Tamu','guest_illness'=>'Sakit Tamu','theft'=>'Pencurian',
    'property_damage'=>'Kerusakan Properti','staff_injury'=>'Cedera Staf','security'=>'Keamanan',
    'fire'=>'Kebakaran','flood'=>'Banjir','complaint'=>'Keluhan','other'=>'Lainnya'
];
@endphp

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.security.incidents.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $isNew ? 'Lapor Insiden Baru' : 'Insiden ' . $incident->report_number }}</h1>
        <p class="text-sm text-gray-500">{{ $isNew ? 'Catat laporan insiden keamanan dan keselamatan' : $incident->incident_date->format('d M Y H:i') }}</p>
    </div>
</div>

@if(!$isNew && !($edit ?? false))
{{-- Detail view --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Incident Info --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
            <div class="flex items-center gap-3 mb-4">
                <h2 class="text-lg font-bold text-gray-900">Detail Insiden</h2>
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-{{ $severityColors[$incident->severity] }}-50 text-{{ $severityColors[$incident->severity] }}-700 border border-{{ $severityColors[$incident->severity] }}-200">
                    {{ $severityLabels[$incident->severity] }}
                </span>
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-{{ $statusColors[$incident->status] }}-50 text-{{ $statusColors[$incident->status] }}-700 border border-{{ $statusColors[$incident->status] }}-200">
                    {{ $statusLabels[$incident->status] }}
                </span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm mb-4">
                <div><span class="text-gray-400 text-xs">Tipe</span><p class="font-semibold">{{ $typeLabels[$incident->incident_type] ?? $incident->incident_type }}</p></div>
                <div><span class="text-gray-400 text-xs">Lokasi</span><p class="font-semibold">{{ $incident->location ?? '-' }}</p></div>
                <div><span class="text-gray-400 text-xs">Tanggal</span><p class="font-semibold">{{ $incident->incident_date->format('d M Y H:i') }}</p></div>
                <div><span class="text-gray-400 text-xs">Pelapor</span><p class="font-semibold">{{ $incident->reported_by ?? '-' }}</p></div>
                @if($incident->guest)
                <div><span class="text-gray-400 text-xs">Tamu</span><p class="font-semibold">{{ $incident->guest->first_name }} {{ $incident->guest->last_name }}</p></div>
                @endif
                @if($incident->room)
                <div><span class="text-gray-400 text-xs">Kamar</span><p class="font-semibold">{{ $incident->room->room_number }}</p></div>
                @endif
                <div><span class="text-gray-400 text-xs">Lapor Polisi</span><p class="font-semibold">{{ $incident->police_report_filed ? 'Ya' : 'Tidak' }}</p></div>
                <div><span class="text-gray-400 text-xs">Klaim Asuransi</span><p class="font-semibold">{{ $incident->insurance_claim_filed ? 'Ya' : 'Tidak' }}</p></div>
            </div>

            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">Deskripsi Kejadian</h3>
                    <p class="text-sm text-gray-600 whitespace-pre-wrap bg-gray-50 rounded-xl p-4">{{ $incident->description }}</p>
                </div>
                @if($incident->immediate_actions)
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">Tindakan Segera</h3>
                    <p class="text-sm text-gray-600 whitespace-pre-wrap bg-gray-50 rounded-xl p-4">{{ $incident->immediate_actions }}</p>
                </div>
                @endif

                @if($incident->witness_name)
                <div class="flex gap-6 text-sm">
                    <div><span class="text-gray-400 text-xs">Saksi</span><p class="font-semibold">{{ $incident->witness_name }}</p></div>
                    <div><span class="text-gray-400 text-xs">Kontak Saksi</span><p class="font-semibold">{{ $incident->witness_contact ?? '-' }}</p></div>
                </div>
                @endif
            </div>
        </div>

        {{-- Photos --}}
        @if($incident->photos)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Foto</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($incident->photos as $photo)
                <img src="{{ asset('storage/' . $photo) }}" class="rounded-xl border border-gray-100 w-full h-24 object-cover">
                @endforeach
            </div>
        </div>
        @endif

        {{-- Resolution --}}
        @if(in_array($incident->status, ['resolved', 'closed']) && $incident->resolution)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-2">Penyelesaian</h2>
            <p class="text-sm text-gray-600 whitespace-pre-wrap bg-emerald-50 rounded-xl p-4">{{ $incident->resolution }}</p>
            @if($incident->resolvedBy)
            <p class="text-xs text-gray-400 mt-2">Diselesaikan oleh {{ $incident->resolvedBy->name }} pada {{ $incident->resolved_at?->format('d M Y H:i') }}</p>
            @endif
        </div>
        @endif
    </div>

    {{-- Right sidebar --}}
    <div class="space-y-6">
        {{-- Followups --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-gray-900">Tindak Lanjut</h3>
                <span class="text-xs text-gray-400">{{ $incident->followups->whereNotNull('completed_at')->count() }}/{{ $incident->followups->count() }}</span>
            </div>
            <div class="space-y-2 mb-4">
                @forelse($incident->followups as $followup)
                <div class="flex items-start gap-3 p-3 rounded-xl {{ $followup->completed_at ? 'bg-emerald-50' : ($followup->isOverdue() ? 'bg-rose-50' : 'bg-gray-50') }} text-sm">
                    <form method="POST" action="{{ route('panel.security.incidents.followups.complete', [$incident->id, $followup->id]) }}" class="shrink-0 mt-0.5">
                        @csrf
                        <button class="w-4 h-4 rounded border {{ $followup->completed_at ? 'bg-emerald-500 border-emerald-500' : 'border-gray-300 hover:border-indigo-400' }} flex items-center justify-center">
                            @if($followup->completed_at)
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </button>
                    </form>
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-900 {{ $followup->completed_at ? 'line-through text-gray-400' : '' }}">{{ $followup->action }}</p>
                        <div class="flex items-center gap-2 mt-1 text-xs text-gray-400">
                            @if($followup->assignedTo)<span>{{ $followup->assignedTo->name }}</span>@endif
                            @if($followup->due_date)<span>Due: {{ $followup->due_date->format('d M') }}</span>@endif
                            @if($followup->isOverdue())<span class="text-rose-500 font-semibold">Overdue!</span>@endif
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400">Belum ada tindak lanjut</p>
                @endforelse
            </div>

            @if(!in_array($incident->status, ['closed']))
            <form method="POST" action="{{ route('panel.security.incidents.followups.store', $incident->id) }}" class="space-y-2">
                @csrf
                <input type="text" name="action" placeholder="Tindakan yang diperlukan..." required
                       class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                <div class="flex gap-2">
                    <select name="assigned_to_user_id" class="flex-1 bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs">
                        <option value="">Assign ke...</option>
                        @foreach(\App\Models\User::where('property_id', app('current_property')->id)->limit(50)->get() as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="due_date" class="w-32 bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white text-xs font-semibold py-2 rounded-lg hover:bg-indigo-700 transition-colors">Tambah Tindak Lanjut</button>
            </form>
            @endif
        </div>

        {{-- Resolution Form --}}
        @if(!in_array($incident->status, ['resolved', 'closed']))
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <h3 class="font-bold text-gray-900 mb-3">Selesaikan Insiden</h3>
            <form method="POST" action="{{ route('panel.security.incidents.resolve', $incident->id) }}" class="space-y-3">
                @csrf
                <textarea name="resolution" rows="3" placeholder="Deskripsi penyelesaian..." required
                          class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                <label class="flex items-center gap-2 text-xs text-gray-600">
                    <input type="checkbox" name="close_permanently" value="1" class="rounded"> Tutup permanen
                </label>
                <button type="submit" class="w-full bg-emerald-600 text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-emerald-700 transition-colors">Selesaikan Insiden</button>
            </form>
        </div>
        @endif

        {{-- Edit button --}}
        <a href="{{ route('panel.security.incidents.edit', $incident->id) }}"
           class="block w-full text-center bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold py-3 px-4 rounded-xl transition-colors text-sm">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Insiden
        </a>
    </div>
</div>
@else
{{-- Create/Edit form --}}
<form method="POST" action="{{ $isNew ? route('panel.security.incidents.store') : route('panel.security.incidents.update', $incident->id) }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf
    @if(!$isNew) @method('PUT') @endif

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ $isNew ? 'Informasi Insiden' : 'Edit Insiden' }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tipe Insiden <span class="text-rose-500">*</span></label>
                    <select name="incident_type" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        @foreach($typeLabels as $k => $v)
                        <option value="{{ $k }}" {{ old('incident_type', $incident->incident_type) === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Severity <span class="text-rose-500">*</span></label>
                    <select name="severity" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        @foreach($severityLabels as $k => $v)
                        <option value="{{ $k }}" {{ old('severity', $incident->severity) === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi</label>
                    <input type="text" name="location" value="{{ old('location', $incident->location) }}"
                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Kejadian <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="incident_date" value="{{ old('incident_date', $incident->incident_date instanceof \Carbon\Carbon ? $incident->incident_date->format('Y-m-d\TH:i') : '') }}" required
                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pelapor</label>
                    <input type="text" name="reported_by" value="{{ old('reported_by', $incident->reported_by) }}"
                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Kejadian <span class="text-rose-500">*</span></label>
                    <textarea name="description" rows="4" required
                              class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">{{ old('description', $incident->description) }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tindakan Segera</label>
                    <textarea name="immediate_actions" rows="3"
                              class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">{{ old('immediate_actions', $incident->immediate_actions) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Saksi</label>
                    <input type="text" name="witness_name" value="{{ old('witness_name', $incident->witness_name) }}"
                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kontak Saksi</label>
                    <input type="text" name="witness_contact" value="{{ old('witness_contact', $incident->witness_contact) }}"
                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <h3 class="font-bold text-gray-900 mb-3">Opsi Lain</h3>
            <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="police_report_filed" value="1" {{ old('police_report_filed', $incident->police_report_filed) ? 'checked' : '' }} class="rounded"> Laporan Polisi
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="insurance_claim_filed" value="1" {{ old('insurance_claim_filed', $incident->insurance_claim_filed) ? 'checked' : '' }} class="rounded"> Klaim Asuransi
                </label>
            </div>
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors shadow-sm text-sm">
            {{ $isNew ? 'Buat Laporan Insiden' : 'Simpan Perubahan' }}
        </button>
    </div>
</form>
@endif
@endsection
