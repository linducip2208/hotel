@extends('panel.layout')
@section('title', 'Seragam — Assignment & Return')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Seragam Karyawan</h1>
        <p class="text-sm text-gray-500 mt-0.5">Penugasan dan pengembalian seragam</p>
    </div>
    <a href="{{ route('panel.hk.linen') }}" class="inline-flex items-center gap-1.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
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

{{-- Assign Uniform Form --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-6">
    <h2 class="text-sm font-semibold text-gray-700 mb-4">Tugaskan Seragam</h2>
    <form method="POST" action="{{ route('panel.hk.linen.uniforms.assign') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan</label>
            <select name="employee_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">-- Pilih --</option>
                @foreach (\App\Models\Employee::where('property_id', app('current_property')->id)->orderBy('full_name')->get() as $emp)
                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Linen</label>
            <select name="linen_category_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="">-- Pilih --</option>
                @foreach (\App\Models\LinenCategory::where('property_id', app('current_property')->id)->whereIn('type', ['uniform', 'other'])->orderBy('name')->get() as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah</label>
            <input type="number" name="quantity_assigned" value="1" min="1" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Penugasan</label>
            <input type="date" name="assigned_date" value="{{ today()->toDateString() }}" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Kondisi</label>
            <select name="condition" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                <option value="baik">Baik</option>
                <option value="rusak">Rusak</option>
                <option value="perlu_ganti">Perlu Ganti</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium py-2.5 rounded-xl transition-colors shadow-sm">
                Tugaskan
            </button>
        </div>
    </form>
</div>

{{-- Uniforms Table --}}
@if ($uniforms->isEmpty())
<div class="bg-white rounded-2xl shadow-card border border-gray-100 px-5 py-16 text-center">
    <div class="flex flex-col items-center gap-3">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-700">Belum ada penugasan seragam</p>
        <p class="text-xs text-gray-400">Tugaskan seragam ke karyawan di form di atas.</p>
    </div>
</div>
@else
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Karyawan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Ditugaskan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Dikembalikan</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Kondisi</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($uniforms as $u)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-800">{{ $u->employee?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $u->linenCategory?->name ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-center text-sm text-gray-700 font-semibold">{{ $u->quantity_assigned }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $u->assigned_date->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $u->returned_date ? $u->returned_date->format('d M Y') : '—' }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium {{ $u->condition === 'baik' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }} px-2 py-0.5 rounded-full capitalize">
                            {{ str_replace('_', ' ', $u->condition) }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        @if (!$u->returned_date)
                        <form method="POST" action="{{ route('panel.hk.linen.uniforms.return', $u->id) }}" class="inline-flex items-center gap-2" onsubmit="return confirm('Kembalikan seragam ini?')">
                            @csrf
                            <select name="condition" class="text-xs border border-gray-200 rounded-lg py-1 px-1.5">
                                <option value="baik">Baik</option>
                                <option value="rusak">Rusak</option>
                                <option value="perlu_ganti">Perlu Ganti</option>
                            </select>
                            <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-800 font-medium transition-colors">Kembalikan</button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400">Selesai</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
