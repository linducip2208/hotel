@extends('panel.layout')
@section('title', 'Payroll')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Payroll</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}</p>
    </div>
    <form method="POST" action="{{ route('panel.hr.payroll.generate') }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="month" value="{{ $month }}">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors"
                :disabled="loading">
            <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span x-text="loading ? 'Generating...' : 'Generate Payslips'"></span>
        </button>
    </form>
</div>

@if (session('status'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('status') }}
</div>
@endif

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Employee</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Gross</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">PPh 21</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">BPJS</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Net Salary</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($payslips as $p)
                @php
                    $statusColors = ['draft' => 'gray', 'approved' => 'blue', 'paid' => 'emerald'];
                    $sc = $statusColors[$p->status] ?? 'gray';
                    $initials = collect(explode(' ', $p->employee?->full_name ?? 'E'))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold shrink-0">{{ $initials }}</div>
                            <span class="text-sm font-medium text-gray-800">{{ $p->employee?->full_name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">{{ number_format($p->gross_total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-red-600">{{ number_format($p->pph_21, 0, ',', '.') }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-amber-600">
                        {{ number_format($p->bpjs_kesehatan_employee + $p->bpjs_tk_employee, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-gray-900">
                        {{ number_format($p->net_salary, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-0.5 rounded-full capitalize">{{ $p->status }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center gap-1 justify-end">
                            <a href="{{ route('panel.hr.payslips.show', $p->id) }}"
                               class="text-xs font-medium text-primary-600 bg-primary-50 px-2.5 py-1 rounded-lg hover:bg-primary-100 transition-colors">View</a>
                            @if ($p->status === 'draft')
                            <form method="POST" action="{{ route('panel.hr.payslips.approve', $p->id) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg hover:bg-blue-100 transition-colors">Approve</button>
                            </form>
                            @endif
                            @if ($p->status === 'approved')
                            <form method="POST" action="{{ route('panel.hr.payslips.paid', $p->id) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg hover:bg-emerald-100 transition-colors">Mark Paid</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-sm text-gray-400">No payslips generated for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($payslips->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $payslips->links() }}
    </div>
    @endif
</div>

@endsection
