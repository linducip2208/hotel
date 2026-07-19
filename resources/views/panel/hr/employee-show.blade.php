@extends('panel.layout')
@section('title', 'Employee Profile')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.hr.employees') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <h1 class="text-2xl font-bold text-gray-900">{{ $employee->full_name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            <span class="font-mono">{{ $employee->employee_no }}</span> · {{ $employee->position }} · {{ $employee->department }}
        </p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('panel.hr.employees.edit', $employee->id) }}"
           class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-3 py-2 rounded-xl hover:bg-amber-100 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit
        </a>
        <form method="POST" action="{{ route('panel.hr.employees.destroy', $employee->id) }}" onsubmit="return confirm('Hapus karyawan ini?')">
            @csrf @method('DELETE')
            <button class="inline-flex items-center gap-1.5 text-xs font-medium text-red-700 bg-red-50 px-3 py-2 rounded-xl hover:bg-red-100 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus
            </button>
        </form>
    </div>
</div>

<div class="space-y-5 max-w-3xl">

    {{-- Payslip history --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Payslip History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Period</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Gross</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Deductions</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Net Salary</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($employee->payslips as $p)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-sm text-gray-800">
                                {{ \Carbon\Carbon::create($p->year, $p->month, 1)->isoFormat('MMMM Y') }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">
                            Rp {{ number_format($p->gross_total, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm text-red-500">
                            (Rp {{ number_format($p->deductions_total, 0, ',', '.') }})
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-gray-900">
                            Rp {{ number_format($p->net_salary, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <a href="{{ route('panel.hr.payslips.show', $p->id) }}"
                               class="text-xs font-medium text-primary-600 hover:text-primary-800 transition-colors">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-sm text-gray-400">No payslips yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
