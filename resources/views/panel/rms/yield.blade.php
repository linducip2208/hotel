@extends('panel.layout')
@section('title', 'Yield Report')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Yield Report</h1>
    <p class="text-sm text-gray-500 mt-0.5">
        {{ $from->isoFormat('D MMM') }} → {{ $to->isoFormat('D MMM Y') }}
    </p>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Yield Summary</h2>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse ($summary as $k => $v)
            @php
                $isRevenue = str_contains(strtolower($k), 'revenue') || str_contains(strtolower($k), 'revpar') || str_contains(strtolower($k), 'adr');
                $isPercent = str_contains(strtolower($k), '%') || str_contains(strtolower($k), 'occupancy') || str_contains(strtolower($k), 'rate');
            @endphp
            <div class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50/40 transition-colors">
                <span class="text-sm font-medium text-gray-700 capitalize">{{ str_replace('_', ' ', $k) }}</span>
                <span class="font-mono text-sm {{ $isRevenue ? 'text-emerald-700 font-semibold' : 'text-gray-900' }}">
                    @if (is_numeric($v))
                        @if ($isRevenue)
                            Rp {{ number_format((float) $v, 0, ',', '.') }}
                        @elseif ($isPercent)
                            {{ number_format((float) $v, 1, ',', '.') }}%
                        @else
                            {{ number_format((float) $v, 2, ',', '.') }}
                        @endif
                    @else
                        {{ $v }}
                    @endif
                </span>
            </div>
            @empty
            <div class="px-5 py-12 text-center text-sm text-gray-400">No yield data for this period.</div>
            @endforelse
        </div>
    </div>
</div>

@endsection
