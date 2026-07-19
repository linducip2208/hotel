@extends('panel.layout')
@section('title', 'Owner Statements')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Owner Statements</h1>
    <p class="text-sm text-gray-500 mt-0.5">Monthly revenue sharing statements for villa-titip-kelola owners</p>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Period</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Owner</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Room</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Gross Revenue</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Mgmt Fee</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Net Payable</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($statements as $s)
                @php
                    $sc = match($s->status) { 'paid' => 'emerald', 'pending' => 'amber', 'approved' => 'blue', 'cancelled' => 'red', default => 'gray' };
                @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm font-medium text-gray-800">
                        {{ \Carbon\Carbon::create($s->year, $s->month, 1)->isoFormat('MMMM Y') }}
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-700">{{ $s->owner_name }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md">{{ $s->room?->number }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-gray-700">
                        Rp {{ number_format($s->gross_revenue, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-red-500">
                        (Rp {{ number_format($s->mgmt_fee_amount, 0, ',', '.') }})
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-emerald-700">
                        Rp {{ number_format($s->net_payable_to_owner, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $s->status }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="flex flex-col items-center justify-center py-12">
                            <p class="text-sm font-medium text-gray-600">No owner statements yet</p>
                            <p class="text-xs text-gray-400 mt-1">Generate from Accounting once monthly revenue is finalized</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
