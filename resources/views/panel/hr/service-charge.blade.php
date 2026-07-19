@extends('panel.layout')
@section('title', 'Service Charge Distribution')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Service Charge Distribution</h1>
    <p class="text-sm text-gray-500 mt-0.5">Monthly service charge collection and staff distribution</p>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Period</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Collected</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Staff Share</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($distributions as $d)
                @php
                    $sc = match($d->status) { 'distributed' => 'emerald', 'pending' => 'amber', 'cancelled' => 'red', default => 'gray' };
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="text-sm font-medium text-gray-800">
                            {{ \Carbon\Carbon::create($d->year, $d->month, 1)->isoFormat('MMMM Y') }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-800">
                        Rp {{ number_format($d->total_collected, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-semibold text-emerald-700">
                        Rp {{ number_format($d->staff_share_amount, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $d->status }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-12 text-center text-sm text-gray-400">No distribution records yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
