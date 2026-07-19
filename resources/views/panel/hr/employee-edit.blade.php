@extends('panel.layout')
@section('title', 'Edit Employee')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.hr.employees') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Employee</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $employee->full_name }} — {{ $employee->employee_no }}</p>
    </div>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('panel.hr.employees.update', $employee->id) }}" class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Depan <span class="text-red-500">*</span></label>
                <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required placeholder="Budi"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Belakang</label>
                <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" placeholder="Santoso"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">NIK</label>
                <input type="text" name="nik" value="{{ old('nik', $employee->nik) }}" placeholder="3201012345678901"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email', $employee->email) }}" placeholder="budi@hotel.test"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Telepon</label>
            <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" placeholder="08123456789"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="position" value="{{ old('position', $employee->position) }}" required placeholder="Front Desk Agent"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Departemen <span class="text-red-500">*</span></label>
                <input type="text" name="department" value="{{ old('department', $employee->department) }}" required placeholder="Front Office"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal Masuk <span class="text-red-500">*</span></label>
            <input type="date" name="joined_at" value="{{ old('joined_at', $employee->joined_at?->toDateString()) }}" required
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Gaji Pokok (Rp) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="basic_salary" value="{{ old('basic_salary', $employee->basic_salary) }}" required placeholder="4500000"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe Karyawan</label>
                <select name="employment_type"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="permanent" {{ $employee->employment_type === 'permanent' ? 'selected' : '' }}>Permanent</option>
                    <option value="contract" {{ $employee->employment_type === 'contract' ? 'selected' : '' }}>Contract</option>
                    <option value="daily" {{ $employee->employment_type === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="outsource" {{ $employee->employment_type === 'outsource' ? 'selected' : '' }}>Outsource</option>
                </select>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('panel.hr.employees') }}"
               class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="flex-1 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@endsection
