@extends('panel.layout')
@section('title', 'Payslip')
@section('content')

<div class="flex items-center justify-between mb-6 print:hidden">
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.hr.payroll') }}"
           class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Payslip</h1>
    </div>
    <button onclick="window.print()"
            class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Print
    </button>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-8 max-w-2xl mx-auto print:shadow-none print:border-none print:rounded-none">

    {{-- Header --}}
    <div class="text-center mb-8 pb-6 border-b-2 border-gray-200">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Slip Gaji</p>
        <h1 class="text-2xl font-bold text-gray-900">
            {{ \Carbon\Carbon::create($payslip->year, $payslip->month, 1)->isoFormat('MMMM Y') }}
        </h1>
    </div>

    {{-- Employee info --}}
    <div class="flex items-center gap-4 mb-8 p-4 bg-gray-50/60 rounded-xl border border-gray-100">
        @php
            $initials = collect(explode(' ', $payslip->employee?->full_name ?? 'E'))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
        @endphp
        <div class="w-12 h-12 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-base font-bold shrink-0">
            {{ $initials }}
        </div>
        <div>
            <div class="text-base font-bold text-gray-900">{{ $payslip->employee?->full_name }}</div>
            <div class="text-sm text-gray-500 mt-0.5">
                <span class="font-mono">{{ $payslip->employee?->employee_no }}</span> · {{ $payslip->employee?->position }}
            </div>
        </div>
    </div>

    {{-- Earnings --}}
    <div class="mb-6">
        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Pendapatan</h2>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Gaji Pokok</span>
                <span class="font-mono text-gray-800">Rp {{ number_format($payslip->basic_salary, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Tunjangan</span>
                <span class="font-mono text-gray-800">Rp {{ number_format($payslip->allowances_total, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Lembur</span>
                <span class="font-mono text-gray-800">Rp {{ number_format($payslip->overtime_pay, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Service Charge</span>
                <span class="font-mono text-gray-800">Rp {{ number_format($payslip->service_charge, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between pt-2 mt-2 border-t border-gray-100 font-semibold">
                <span class="text-gray-800">Total Bruto</span>
                <span class="font-mono text-gray-900">Rp {{ number_format($payslip->gross_total, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Deductions --}}
    <div class="mb-8">
        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Potongan</h2>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">BPJS Kesehatan</span>
                <span class="font-mono text-red-500">(Rp {{ number_format($payslip->bpjs_kesehatan_employee, 0, ',', '.') }})</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">BPJS TK</span>
                <span class="font-mono text-amber-600">(Rp {{ number_format($payslip->bpjs_tk_employee, 0, ',', '.') }})</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">PPh 21</span>
                <span class="font-mono text-red-500">(Rp {{ number_format($payslip->pph_21, 0, ',', '.') }})</span>
            </div>
            <div class="flex justify-between pt-2 mt-2 border-t border-gray-100 font-semibold">
                <span class="text-gray-800">Total Potongan</span>
                <span class="font-mono text-red-600">(Rp {{ number_format($payslip->deductions_total, 0, ',', '.') }})</span>
            </div>
        </div>
    </div>

    {{-- Net Salary --}}
    <div class="flex justify-between items-center p-5 bg-primary-50 border border-primary-100 rounded-2xl">
        <span class="text-base font-bold text-primary-800 uppercase tracking-wide">Gaji Bersih</span>
        <span class="text-2xl font-bold text-primary-700 font-mono">Rp {{ number_format($payslip->net_salary, 0, ',', '.') }}</span>
    </div>

</div>

@endsection
