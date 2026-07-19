@extends('panel.layout')
@section('title', 'Dynamic Pricing Rules')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dynamic Pricing Rules</h1>
        <p class="text-sm text-gray-500 mt-0.5">Automated price adjustments triggered by occupancy, pace, or lead time</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.pricing.calendar') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 px-4 py-2 rounded-xl shadow-card transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Rate Calendar
        </a>
        <form method="POST" action="{{ route('panel.pricing.rules.apply') }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Apply Now
            </button>
        </form>
    </div>
</div>

@if (session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-5 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

{{-- Add Rule Form --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Add New Rule</h2>
    </div>
    <form method="POST" action="{{ route('panel.pricing.rules.store') }}" class="p-5">
        @csrf
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="col-span-2 md:col-span-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rule Name <span class="text-red-500">*</span></label>
                <input name="name" required placeholder="e.g. High Season Boost"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Room Type</label>
                <select name="room_type_id"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                    <option value="">All Room Types</option>
                    @foreach ($roomTypes as $rt)
                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Channel</label>
                <select name="channel_id"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                    <option value="">All Channels</option>
                    @foreach ($channels as $ch)
                    <option value="{{ $ch->id }}">{{ $ch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Trigger Metric</label>
                <select name="trigger_metric"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                    <option value="occupancy_pct">Occupancy %</option>
                    <option value="days_to_arrival">Days to Arrival</option>
                    <option value="pickup_pace">Pickup Pace</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Operator</label>
                <select name="operator"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                    <option value="gte">≥ (gte)</option>
                    <option value="lte">≤ (lte)</option>
                    <option value="between">between</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Threshold Low <span class="text-red-500">*</span></label>
                <input name="threshold_low" type="number" step="0.01" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Threshold High <span class="text-gray-400 font-normal">(between)</span></label>
                <input name="threshold_high" type="number" step="0.01"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Action</label>
                <select name="action"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                    <option value="pct_increase">% Increase</option>
                    <option value="pct_decrease">% Decrease</option>
                    <option value="fixed_increase">Fixed Increase</option>
                    <option value="fixed_decrease">Fixed Decrease</option>
                    <option value="stop_sell">Stop Sell</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Action Value <span class="text-red-500">*</span></label>
                <input name="action_value" type="number" step="0.01" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Lookahead Days</label>
                <input name="lookahead_days" type="number" value="30" min="1" max="365"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Min Price Floor (IDR)</label>
                <input name="min_price_floor" type="number" placeholder="optional"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Max Price Ceiling (IDR)</label>
                <input name="max_price_ceiling" type="number" placeholder="optional"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div class="col-span-2 md:col-span-4 flex items-center gap-4 pt-1">
                <label class="flex items-center gap-2 text-sm text-gray-700 font-medium cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    Active
                </label>
                <button type="submit"
                        class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-colors">
                    Save Rule
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Rules List --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Room Type</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Trigger</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Threshold</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Action</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Lookahead</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Last Applied</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($rules as $rule)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5 font-semibold text-gray-800">{{ $rule->name }}</td>
                    <td class="px-4 py-3.5 text-center text-xs text-gray-500">{{ $rule->roomType?->name ?? 'All' }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-mono bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md">{{ $rule->trigger_metric }} {{ $rule->operator }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center text-xs font-mono text-gray-600">
                        {{ $rule->threshold_low }}{{ $rule->threshold_high ? ' – ' . $rule->threshold_high : '' }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @php
                            $actionColor = str_contains($rule->action, 'increase') ? 'emerald' : (str_contains($rule->action, 'decrease') ? 'red' : 'gray');
                        @endphp
                        <span class="text-xs font-medium bg-{{ $actionColor }}-50 text-{{ $actionColor }}-700 px-2 py-0.5 rounded-full">
                            {{ $rule->action }} {{ $rule->action_value }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center text-xs text-gray-500">{{ $rule->lookahead_days }}d</td>
                    <td class="px-4 py-3.5 text-center text-xs text-gray-400">{{ $rule->last_applied_at?->diffForHumans() ?? '—' }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium px-2.5 py-0.5 rounded-full {{ $rule->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $rule->is_active ? 'Active' : 'Off' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-sm text-gray-400">No rules yet. Add your first dynamic pricing rule above.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
