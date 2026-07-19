@extends('panel.layout')
@section('title', 'Izin & Lisensi — Kepatuhan')
@section('content')

@php $prop = app('current_property'); @endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Izin & Lisensi Properti</h1>
        <p class="text-sm text-gray-500 mt-0.5">Lacak izin usaha, sertifikasi, dan lisensi properti beserta masa berlakunya</p>
    </div>
    <button onclick="document.getElementById('addLicenseModal').classList.toggle('hidden')"
            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Tambah Izin
    </button>
</div>

{{-- Add Modal --}}
<div id="addLicenseModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">Tambah Izin Baru</h2>
            <button onclick="document.getElementById('addLicenseModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('panel.compliance.licenses.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Izin <span class="text-rose-500">*</span></label>
                <input type="text" name="license_name" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor Izin</label>
                    <input type="text" name="license_number" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Otoritas</label>
                    <input type="text" name="issuing_authority" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Terbit</label>
                    <input type="date" name="issue_date" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Kadaluarsa</label>
                    <input type="date" name="expiry_date" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Pengingat (hari sebelum kadaluarsa)</label>
                <input type="number" name="renewal_reminder_days" value="30" min="1" max="365"
                       class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Dokumen (PDF/JPG)</label>
                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-2.5 rounded-xl hover:bg-indigo-700 transition-colors text-sm">Simpan</button>
        </form>
    </div>
</div>

{{-- License Cards --}}
@if($licenses->isEmpty())
<div class="bg-white rounded-2xl border border-gray-100 shadow-card p-12 text-center">
    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
    <p class="text-gray-500 font-semibold">Belum ada izin terdaftar</p>
    <p class="text-sm text-gray-400 mt-1">Tambahkan izin usaha, sertifikasi, dan lisensi properti Anda</p>
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($licenses as $lic)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-gray-900 truncate">{{ $lic->license_name }}</h3>
                @if($lic->license_number)
                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $lic->license_number }}</p>
                @endif
            </div>
            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full shrink-0 ml-2
                @if($lic->daysUntilExpiry() < 0) bg-rose-50 text-rose-700 border border-rose-200
                @elseif($lic->daysUntilExpiry() <= 30) bg-amber-50 text-amber-700 border border-amber-200
                @else bg-emerald-50 text-emerald-700 border border-emerald-200 @endif">
                <span class="w-1.5 h-1.5 rounded-full
                    @if($lic->daysUntilExpiry() < 0) bg-rose-500
                    @elseif($lic->daysUntilExpiry() <= 30) bg-amber-500
                    @else bg-emerald-500 @endif"></span>
                {{ $lic->expiryLabel() }}
            </span>
        </div>

        <div class="space-y-2 text-sm text-gray-600 mb-4">
            @if($lic->issuing_authority)
            <div class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                <span>{{ $lic->issuing_authority }}</span>
            </div>
            @endif
            @if($lic->issue_date)
            <div class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Terbit: {{ $lic->issue_date->format('d M Y') }}</span>
            </div>
            @endif
            @if($lic->expiry_date)
            <div class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Kadaluarsa: {{ $lic->expiry_date->format('d M Y') }}</span>
            </div>
            @endif
        </div>

        <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
            @if($lic->document_path)
            <a href="{{ asset('storage/' . $lic->document_path) }}" target="_blank"
               class="inline-flex items-center gap-1 text-xs bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium px-3 py-1.5 rounded-lg border border-gray-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh
            </a>
            @endif
            <button onclick="editLicense({{ $lic->id }}, '{{ addslashes($lic->license_name) }}', '{{ addslashes($lic->license_number ?? '') }}', '{{ addslashes($lic->issuing_authority ?? '') }}', '{{ $lic->issue_date?->format('Y-m-d') }}', '{{ $lic->expiry_date?->format('Y-m-d') }}', {{ $lic->renewal_reminder_days }}, '{{ addslashes($lic->notes ?? '') }}')"
                    class="inline-flex items-center gap-1 text-xs bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium px-3 py-1.5 rounded-lg border border-gray-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </button>
            <form method="POST" action="{{ route('panel.compliance.licenses.destroy', $lic->id) }}" onsubmit="return confirm('Hapus izin ini?')" class="inline">
                @csrf @method('DELETE')
                <button class="text-xs text-rose-400 hover:text-rose-600 font-medium">Hapus</button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Edit Modal --}}
<div id="editLicenseModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">Edit Izin</h2>
            <button onclick="document.getElementById('editLicenseModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editLicenseForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Izin <span class="text-rose-500">*</span></label>
                <input type="text" name="license_name" id="edit_name" required class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor Izin</label>
                    <input type="text" name="license_number" id="edit_number" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Otoritas</label>
                    <input type="text" name="issuing_authority" id="edit_authority" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Terbit</label>
                    <input type="date" name="issue_date" id="edit_issue" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Kadaluarsa</label>
                    <input type="date" name="expiry_date" id="edit_expiry" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Pengingat (hari)</label>
                <input type="number" name="renewal_reminder_days" id="edit_reminder" min="1" max="365" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Dokumen Baru (opsional)</label>
                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" id="edit_notes" rows="2" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-2.5 rounded-xl hover:bg-indigo-700 transition-colors text-sm">Simpan Perubahan</button>
        </form>
    </div>
</div>

<script>
function editLicense(id, name, number, authority, issue, expiry, reminder, notes) {
    document.getElementById('editLicenseForm').action = '/panel/compliance/licenses/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_number').value = number;
    document.getElementById('edit_authority').value = authority;
    document.getElementById('edit_issue').value = issue;
    document.getElementById('edit_expiry').value = expiry;
    document.getElementById('edit_reminder').value = reminder;
    document.getElementById('edit_notes').value = notes;
    document.getElementById('editLicenseModal').classList.remove('hidden');
}
</script>
@endsection
