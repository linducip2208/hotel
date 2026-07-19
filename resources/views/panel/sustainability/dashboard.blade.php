@extends('panel.layout')
@section('title', 'Sustainability')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Sustainability Dashboard</h1>
    <p class="text-sm text-gray-500 mt-0.5">Energy, water, waste, and carbon footprint tracking</p>
</div>

{{-- CO₂ card --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 shadow-card border border-emerald-100">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">This month</span>
        </div>
        <div class="text-3xl font-bold text-gray-900 tabular-nums">{{ number_format($monthCo2, 0, ',', '.') }}</div>
        <div class="text-sm text-gray-500 mt-0.5">kg CO₂ equivalent</div>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Metrics log --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Recent Measurements</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Metric</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Value</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Source</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($metrics as $m)
                        @php
                            $metricColors = ['energy_kwh' => 'amber', 'water_m3' => 'blue', 'waste_kg' => 'gray', 'recycled_pct' => 'emerald', 'renewable_pct' => 'green'];
                            $mc = $metricColors[$m->metric] ?? 'primary';
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3 text-sm text-gray-600">{{ $m->measurement_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-medium bg-{{ $mc }}-50 text-{{ $mc }}-700 px-2 py-0.5 rounded-full">{{ str_replace('_', ' ', $m->metric) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-sm text-gray-900">{{ number_format($m->value, 4) }} {{ $m->unit }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 capitalize">{{ $m->source }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-sm text-gray-400">No measurements logged yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Log metric form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Log Metric</h2>
        </div>
        <form method="POST" action="{{ route('panel.sustainability.metrics.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date <span class="text-red-500">*</span></label>
                <input type="date" name="measurement_date" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Metric <span class="text-red-500">*</span></label>
                <select name="metric" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="energy_kwh">Energy (kWh)</option>
                    <option value="water_m3">Water (m³)</option>
                    <option value="waste_kg">Waste (kg)</option>
                    <option value="recycled_pct">Recycled %</option>
                    <option value="renewable_pct">Renewable %</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Value <span class="text-red-500">*</span></label>
                <input type="number" step="0.0001" name="value" required placeholder="e.g. 1234.5"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Unit</label>
                <input type="text" name="unit" placeholder="kWh, m³, kg…"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Source</label>
                <select name="source"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="meter">Meter</option>
                    <option value="invoice">Invoice</option>
                    <option value="estimate">Estimate</option>
                </select>
            </div>
            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Log Measurement
            </button>
        </form>
    </div>

</div>

@endsection
