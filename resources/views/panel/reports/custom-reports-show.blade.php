@extends('panel.layout')
@section('title', $report->name)
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="{{ route('panel.reports.custom-reports.index') }}" class="text-xs text-primary-600 hover:underline mb-1 inline-block">&larr; Kembali ke Report Builder</a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $report->name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ \App\Services\Reports\CustomReportService::class }} &middot; {{ $report->createdBy->name ?? 'System' }}</p>
    </div>
    <a href="{{ route('panel.reports.custom-reports.edit', $report->id) }}"
       class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-colors">
        Edit Laporan
    </a>
</div>

@if(empty($report->widgets))
<div class="flex flex-col items-center justify-center py-16 text-gray-400 bg-white rounded-2xl shadow-card border border-gray-100">
    <p class="text-sm text-gray-500">Laporan ini belum memiliki widget</p>
</div>
@else
<div class="grid {{ count($report->widgets) > 2 ? 'lg:grid-cols-2' : 'grid-cols-1' }} gap-5">
    @foreach($report->widgets as $widgetKey)
        @php
            $def = $widgetDefs[$widgetKey] ?? ['name' => $widgetKey, 'type' => 'card'];
            $data = $widgetData[$widgetKey] ?? [];
        @endphp
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">{{ $def['name'] ?? $widgetKey }}</h3>
                <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ $widgetKey }}</span>
            </div>
            <div class="p-5">
                @if(in_array($def['type'] ?? '', ['line_chart', 'bar_chart', 'doughnut_chart', 'pie_chart']))
                    @if(!empty($data['labels']) && !empty($data['data']))
                    <canvas id="chart-{{ $loop->index }}" height="220"></canvas>
                    <script>
                    (function() {
                        var ctx = document.getElementById('chart-{{ $loop->index }}');
                        if (!ctx) return;
                        new Chart(ctx, {
                            type: '{{ $def['type'] === 'line_chart' ? 'line' : ($def['type'] === 'bar_chart' ? 'bar' : ($def['type'] === 'doughnut_chart' ? 'doughnut' : 'pie')) }}',
                            data: {
                                labels: {!! json_encode($data['labels']) !!},
                                datasets: [{
                                    label: '{{ $def['name'] }}',
                                    data: {!! json_encode($data['data']) !!},
                                    backgroundColor: ['#4f46e5','#8b5cf6','#a78bfa','#c4b5fd','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#6366f1'],
                                    borderColor: '#4f46e5',
                                    borderWidth: 2,
                                    tension: 0.3,
                                    fill: false,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: '{{ $def['type'] }}' === 'line_chart' || '{{ $def['type'] }}' === 'bar_chart' ? false : true } },
                                scales: '{{ $def['type'] }}' === 'doughnut_chart' || '{{ $def['type'] }}' === 'pie_chart' ? {} : { y: { beginAtZero: true } },
                            }
                        });
                    })();
                    </script>
                    @else
                    <p class="text-xs text-gray-400 text-center py-10">Tidak ada data</p>
                    @endif
                @elseif($def['type'] === 'table')
                    @if(!empty($data['labels']) && !empty($data['data']))
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($data['labels'] as $i => $label)
                                <tr>
                                    <td class="py-2 text-sm text-gray-700">{{ $label }}</td>
                                    <td class="py-2 text-sm text-gray-700 text-right font-mono">{{ $data['data'][$i] ?? 0 }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-xs text-gray-400 text-center py-10">Tidak ada data</p>
                    @endif
                @elseif($def['type'] === 'stats_cards')
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($data as $key => $value)
                        <div class="bg-gray-50 rounded-xl p-3 text-center">
                            <div class="text-2xl font-black text-gray-900">{{ is_numeric($value) ? number_format($value) : $value }}</div>
                            <div class="text-[11px] text-gray-500 mt-1 uppercase tracking-wider">{{ str_replace('_', ' ', $key) }}</div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400 text-center py-10">Widget tidak dikenali</p>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endif

@endsection
