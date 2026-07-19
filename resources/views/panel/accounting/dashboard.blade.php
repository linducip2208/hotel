@extends('panel.layout')
@section('title', 'Accounting')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Finance & Accounting</h1>
    <p class="text-sm text-gray-500 mt-0.5">General ledger, AR/AP, and financial reporting</p>
</div>

{{-- Module cards --}}
<div class="grid md:grid-cols-3 gap-4 mb-6">

    @php
    $modules = [
        [
            'label' => 'Chart of Accounts',
            'desc'  => 'Manage COA, add / edit accounts',
            'route' => 'panel.accounting.coa.index',
            'color' => 'primary',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>',
        ],
        [
            'label' => 'Journal Entries',
            'desc'  => 'View & post double-entry journals',
            'route' => 'panel.accounting.journal.index',
            'color' => 'violet',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
        ],
        [
            'label' => 'AR (Receivable)',
            'desc'  => 'City ledger, OTA & company billing',
            'route' => 'panel.accounting.ar.index',
            'color' => 'emerald',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
        ],
        [
            'label' => 'AP (Payable)',
            'desc'  => 'Supplier invoices & payments',
            'route' => 'panel.accounting.ap.index',
            'color' => 'amber',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
        ],
        [
            'label' => 'Daily Revenue',
            'desc'  => 'Night audit output & revenue stats',
            'route' => 'panel.accounting.reports.daily',
            'color' => 'blue',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        ],
        [
            'label' => 'Trial Balance',
            'desc'  => 'Period closing balances',
            'route' => 'panel.accounting.reports.tb',
            'color' => 'indigo',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>',
        ],
        [
            'label' => 'Profit & Loss',
            'desc'  => 'Monthly income statement',
            'route' => 'panel.accounting.reports.pl',
            'color' => 'rose',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>',
        ],
    ];
    @endphp

    @foreach ($modules as $mod)
    <a href="{{ route($mod['route']) }}"
       class="group flex items-start gap-4 bg-white rounded-2xl p-5 shadow-card border border-gray-100 hover:shadow-card-hover hover:border-{{ $mod['color'] }}-100 transition-all">
        <div class="w-11 h-11 rounded-xl bg-{{ $mod['color'] }}-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-{{ $mod['color'] }}-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                {!! $mod['icon'] !!}
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold text-gray-900 group-hover:text-{{ $mod['color'] }}-700 transition-colors">{{ $mod['label'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">{{ $mod['desc'] }}</div>
        </div>
        <svg class="w-4 h-4 text-gray-300 group-hover:text-{{ $mod['color'] }}-400 transition-colors mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
    @endforeach

</div>

{{-- Finance shortcuts --}}
<div class="grid md:grid-cols-3 gap-4">
    @foreach ([
        ['label' => 'Budget vs Actual', 'route' => 'panel.finance.budget', 'color' => 'teal'],
        ['label' => 'Bank Reconciliation', 'route' => 'panel.finance.bank-recon', 'color' => 'cyan'],
        ['label' => 'Owner Statements', 'route' => 'panel.finance.owner-statements', 'color' => 'purple'],
    ] as $sh)
    <a href="{{ route($sh['route']) }}"
       class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 border border-gray-100 hover:bg-white hover:shadow-card transition-all group">
        <span class="text-sm font-medium text-gray-600 group-hover:text-{{ $sh['color'] }}-700">{{ $sh['label'] }}</span>
        <svg class="w-4 h-4 text-gray-300 group-hover:text-{{ $sh['color'] }}-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
    @endforeach
</div>

@endsection
