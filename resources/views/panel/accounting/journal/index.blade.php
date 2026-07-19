@extends('panel.layout')
@section('title', 'Journal Entries')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Journal Entries</h1>
        <p class="text-sm text-gray-500 mt-0.5">Double-entry accounting ledger</p>
    </div>
    <a href="{{ route('panel.accounting.journal.create') }}"
       class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Entry
    </a>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Entry No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Description</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total (Dr)</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($entries as $e)
                @php
                    $posted = $e->status === 'posted';
                    $statusClasses = $posted ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700';
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('panel.accounting.journal.show', $e->id) }}"
                           class="font-mono text-sm font-medium text-primary-600 hover:text-primary-800">{{ $e->entry_no }}</a>
                    </td>
                    <td class="px-4 py-3.5 text-gray-600">{{ $e->posted_at->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-gray-800 max-w-xs truncate">{{ $e->description }}</td>
                    <td class="px-4 py-3.5 text-right font-mono font-medium text-gray-900">
                        Rp {{ number_format($e->total_debit, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium px-2.5 py-0.5 rounded-full capitalize {{ $statusClasses }}">{{ $e->status }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-16 text-center">
                        <div class="flex flex-col items-center text-gray-400">
                            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <p class="text-sm font-medium text-gray-500">No journal entries yet</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($entries->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
        {{ $entries->links() }}
    </div>
    @endif
</div>

@endsection
