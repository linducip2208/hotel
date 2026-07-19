@extends('panel.layout')
@section('title', 'Open Pricing Calendar')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Open Pricing — Rate Calendar</h1>
        <p class="text-sm text-gray-500 mt-0.5">View and override per-date prices across room types and channels</p>
    </div>
    <a href="{{ route('panel.pricing.rules') }}"
       class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 bg-primary-50 hover:bg-primary-100 px-4 py-2 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 4H4m2 0a2 2 0 100 4m-2-4a2 2 0 110 4m12-4h2m-2 0a2 2 0 100 4m2-4a2 2 0 110 4"/></svg>
        Dynamic Rules
    </a>
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-5">
    <div class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Room Type</label>
            <select id="roomTypeSelect"
                    class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                @foreach ($roomTypes as $rt)
                <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Channel</label>
            <select id="channelSelect"
                    class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
                <option value="">All / Direct</option>
                @foreach ($channels as $ch)
                <option value="{{ $ch->id }}">{{ $ch->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">From</label>
            <input type="date" id="fromDate" value="{{ now()->toDateString() }}"
                   class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">To</label>
            <input type="date" id="toDate" value="{{ now()->addDays(30)->toDateString() }}"
                   class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 transition-all">
        </div>
        <button onclick="loadGrid()"
                class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2 rounded-xl shadow-sm transition-colors">
            Load
        </button>
    </div>
</div>

{{-- Grid --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-5">
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="priceGrid">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Price</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Source</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Stop Sell</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">CTA</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Min Stay</th>
                </tr>
            </thead>
            <tbody id="gridBody" class="divide-y divide-gray-50">
                <tr>
                    <td colspan="6" class="py-12 text-center text-sm text-gray-400">Select filters and click Load to view pricing grid.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Bulk Override --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50">
        <h2 class="text-sm font-semibold text-gray-700">Bulk Override</h2>
        <p class="text-xs text-gray-400 mt-0.5">Apply overrides to all dates in the loaded grid</p>
    </div>
    <div class="p-5">
        <div class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Price (IDR)</label>
                <input type="number" id="bulkPrice" placeholder="500000"
                       class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all w-40">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Min Stay</label>
                <input type="number" id="bulkMinStay" min="1"
                       class="rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all w-24">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700 font-medium cursor-pointer">
                <input type="checkbox" id="bulkStopSell" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                Stop Sell
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 font-medium cursor-pointer">
                <input type="checkbox" id="bulkCta" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                Closed to Arrival
            </label>
            <button onclick="saveBulk()"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-5 py-2 rounded-xl shadow-sm transition-colors">
                Save Overrides
            </button>
        </div>
    </div>
</div>

<script>
let currentGrid = [];

async function loadGrid() {
    const rt   = document.getElementById('roomTypeSelect').value;
    const ch   = document.getElementById('channelSelect').value;
    const from = document.getElementById('fromDate').value;
    const to   = document.getElementById('toDate').value;

    const tbody = document.getElementById('gridBody');
    tbody.innerHTML = '<tr><td colspan="6" class="py-10 text-center text-sm text-gray-400">Loading…</td></tr>';

    const params = new URLSearchParams({ room_type_id: rt, from, to });
    if (ch) params.append('channel_id', ch);

    const res  = await fetch(`{{ route('panel.pricing.calendar.data') }}?${params}`);
    const data = await res.json();
    currentGrid = data;

    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="py-10 text-center text-sm text-gray-400">No pricing data found for the selected range.</td></tr>';
        return;
    }

    tbody.innerHTML = data.map(d => {
        const sourceColors = {
            override: 'bg-amber-50 text-amber-700',
            dynamic:  'bg-violet-50 text-violet-700',
            base:     'bg-gray-100 text-gray-600',
        };
        const sc = sourceColors[d.source] || 'bg-gray-100 text-gray-600';
        return `<tr class="hover:bg-gray-50/60 transition-colors ${d.stop_sell ? 'bg-red-50/40' : ''}">
            <td class="px-5 py-3 text-sm text-gray-700">${d.date}</td>
            <td class="px-4 py-3 text-right font-mono text-sm font-semibold text-gray-900">Rp ${Number(d.price).toLocaleString('id-ID')}</td>
            <td class="px-4 py-3 text-center">
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full ${sc} capitalize">${d.source}</span>
            </td>
            <td class="px-4 py-3 text-center text-sm">${d.stop_sell ? '<span class="text-red-500 font-bold">●</span>' : '<span class="text-gray-300">—</span>'}</td>
            <td class="px-4 py-3 text-center text-sm">${d.closed_to_arrival ? '<span class="text-amber-500 font-bold">●</span>' : '<span class="text-gray-300">—</span>'}</td>
            <td class="px-4 py-3 text-center text-sm text-gray-600">${d.min_stay || '—'}</td>
        </tr>`;
    }).join('');
}

async function saveBulk() {
    if (!currentGrid.length) { alert('Load the pricing grid first.'); return; }
    const rt      = document.getElementById('roomTypeSelect').value;
    const ch      = document.getElementById('channelSelect').value;
    const price   = parseFloat(document.getElementById('bulkPrice').value);
    const minStay = parseInt(document.getElementById('bulkMinStay').value) || null;
    const stopSell = document.getElementById('bulkStopSell').checked;
    const cta      = document.getElementById('bulkCta').checked;

    const overrides = currentGrid.map(d => ({
        room_type_id: parseInt(rt),
        channel_id: ch ? parseInt(ch) : null,
        date: d.date,
        price,
        min_stay: minStay,
        stop_sell: stopSell,
        closed_to_arrival: cta,
    }));

    const res  = await fetch('{{ route('panel.pricing.calendar.save') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ overrides }),
    });
    const json = await res.json();
    alert(`${json.upserted} overrides saved.`);
    loadGrid();
}
</script>

@endsection
