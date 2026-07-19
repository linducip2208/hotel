@extends('panel.layout')
@section('title', 'Journal Entry')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.accounting.journal.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">{{ $entry->entry_no }}</h1>
            @php $sc = $entry->status === 'posted' ? 'emerald' : 'amber'; @endphp
            <span class="text-xs font-semibold bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize shrink-0">{{ $entry->status }}</span>
        </div>
        <p class="text-sm text-gray-500 mt-0.5">{{ $entry->description }} · {{ $entry->posted_at->format('d M Y') }}</p>
    </div>
</div>

<div class="max-w-3xl space-y-5">

    {{-- Balance check --}}
    @php
        $balanced = abs($entry->total_debit - $entry->total_credit) < 0.01;
    @endphp
    @if (!$balanced)
    <div class="bg-red-50 border border-red-100 rounded-2xl px-5 py-3.5 flex items-center gap-3">
        <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <span class="text-sm font-medium text-red-700">Warning: This journal entry is not balanced (debits ≠ credits)</span>
    </div>
    @endif

    {{-- Journal lines table --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Journal Lines</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Account</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Debit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Credit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($entry->lines as $l)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md">{{ $l->account?->code }}</span>
                            <span class="text-sm text-gray-800 ml-2">{{ $l->account?->name }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-sm text-gray-500">{{ $l->description ?: '—' }}</td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm {{ $l->debit > 0 ? 'text-gray-900 font-medium' : 'text-gray-300' }}">
                            {{ $l->debit > 0 ? 'Rp '.number_format($l->debit, 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm {{ $l->credit > 0 ? 'text-gray-900 font-medium' : 'text-gray-300' }}">
                            {{ $l->credit > 0 ? 'Rp '.number_format($l->credit, 0, ',', '.') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50/80 border-t border-gray-200">
                        <td colspan="2" class="px-5 py-3.5 text-sm font-bold text-gray-700 text-right">Total</td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-gray-900">
                            Rp {{ number_format($entry->total_debit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-sm font-bold {{ $balanced ? 'text-gray-900' : 'text-red-600' }}">
                            Rp {{ number_format($entry->total_credit, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Balance indicator --}}
    @if ($balanced)
    <div class="flex items-center gap-2 text-sm text-emerald-700">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span class="font-medium">Balanced — debits equal credits</span>
    </div>
    @endif

</div>

@endsection
