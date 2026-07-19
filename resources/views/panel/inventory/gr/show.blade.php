@extends('panel.layout')
@section('title', $gr->gr_number)
@section('content')

<div class="max-w-2xl">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $gr->gr_number }}</h1>
            <p class="text-sm text-gray-500">PO: {{ $gr->purchaseOrder?->po_number }} — Vendor: {{ $gr->purchaseOrder?->vendor?->name }}</p>
        </div>
        <span class="text-xs font-medium bg-{{ ['pending'=>'amber','accepted'=>'emerald','rejected'=>'red'][$gr->status]??'gray' }}-50 text-{{ ['pending'=>'amber','accepted'=>'emerald','rejected'=>'red'][$gr->status]??'gray' }}-700 px-3 py-1.5 rounded-full capitalize">{{ $gr->status }}</span>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50/80"><th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Item</th><th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">Received</th><th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">Accepted</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($gr->lines as $line)
                <tr><td class="px-4 py-2.5">{{ $line->stockItem?->name ?? $line->id }}</td><td class="px-4 py-2.5 text-right font-mono">{{ $line->quantity_received }}</td><td class="px-4 py-2.5 text-right font-mono {{ $line->quantity_accepted > 0 ? 'text-emerald-600' : 'text-red-500' }}">{{ $line->quantity_accepted }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($gr->status === 'pending')
    <form method="POST" action="{{ route('panel.inventory.gr.accept', $gr->id) }}">@csrf<button class="bg-emerald-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-emerald-700 transition-colors">Accept & Update Stock</button></form>
    @endif
</div>

@endsection
