@extends('panel.layout')
@section('title', 'Chart of Accounts')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Chart of Accounts</h1>
        <p class="text-sm text-gray-500 mt-0.5">General ledger account structure</p>
    </div>
    <span class="text-xs text-gray-400">{{ $accounts->count() }} accounts</span>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Account Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Normal Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($accounts as $a)
                @php
                    $isParent = !str_contains($a->code, '-') && !str_contains($a->code, '.');
                    $typeColors = ['asset' => 'blue', 'liability' => 'red', 'equity' => 'violet', 'revenue' => 'emerald', 'expense' => 'amber', 'contra' => 'gray'];
                    $tc = $typeColors[strtolower($a->type)] ?? 'gray';
                @endphp
                <tr class="{{ $isParent ? 'bg-gray-50/60' : 'hover:bg-gray-50/40' }} transition-colors">
                    <td class="px-5 py-3">
                        <span class="font-mono text-sm {{ $isParent ? 'font-bold text-gray-800' : 'text-gray-600' }}">{{ $a->code }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm {{ $isParent ? 'font-bold text-gray-900' : 'text-gray-700 pl-3' }}">{{ $a->name }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium bg-{{ $tc }}-50 text-{{ $tc }}-700 px-2 py-0.5 rounded-full capitalize">{{ $a->type }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-medium {{ $a->normal_balance === 'debit' ? 'text-blue-600' : 'text-emerald-600' }} uppercase tracking-wide">{{ $a->normal_balance }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
