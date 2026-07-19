@extends('panel.layout')
@section('title', 'RFM Segmentation')
@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">RFM Guest Segmentation</h2>
        <p class="text-sm text-slate-500">Auto-label guest: VIP, Regular, At-Risk, Lost, New</p>
    </div>
    <form method="POST" action="{{ route('panel.rfm.calculate') }}">
        @csrf
        <button class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700">Hitung Ulang RFM</button>
    </form>
</div>

@if(!empty($distribution))
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
    @foreach(['vip','regular','potential','at_risk','lost','new','hibernating'] as $seg)
    @php $count = $distribution[$seg] ?? 0; @endphp
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm text-center">
        <div class="text-2xl font-bold text-slate-800">{{ $count }}</div>
        <div class="text-xs font-semibold uppercase text-slate-500 mt-1">{{ str_replace('_', ' ', $seg) }}</div>
    </div>
    @endforeach
</div>
@endif

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">Guest</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">R</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">F</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">M</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">Score</th>
                <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase">Segment</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($profiles as $p)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-medium text-slate-800">{{ $p->guest->first_name ?? 'Guest' }} {{ $p->guest->last_name ?? '' }}</td>
                <td class="px-4 py-3">{{ $p->recency_score ?? '—' }}</td>
                <td class="px-4 py-3">{{ $p->frequency_score ?? '—' }}</td>
                <td class="px-4 py-3">{{ $p->monetary_score ?? '—' }}</td>
                <td class="px-4 py-3 font-bold">{{ $p->rfm_score ?? '—' }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 text-xs font-bold rounded-full
                        @if($p->rfm_segment === 'vip') bg-amber-100 text-amber-700
                        @elseif($p->rfm_segment === 'lost') bg-rose-100 text-rose-700
                        @elseif($p->rfm_segment === 'at_risk') bg-orange-100 text-orange-700
                        @else bg-slate-100 text-slate-600
                        @endif
                    ">{{ str_replace('_', ' ', $p->rfm_segment ?? 'unsegmented') }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
