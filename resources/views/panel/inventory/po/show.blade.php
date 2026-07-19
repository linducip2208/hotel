@extends('panel.layout')
@section('title', $po->po_number)
@section('content')

<div class="max-w-2xl">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $po->po_number }}</h1>
            <p class="text-sm text-gray-500">Vendor: {{ $po->vendor?->name }} — {{ $po->order_date?->format('d M Y') }}</p>
        </div>
        <span class="text-xs font-medium bg-{{ ['draft'=>'gray','sent'=>'blue','partial'=>'amber','received'=>'emerald'][$po->status]??'gray' }}-50 text-{{ ['draft'=>'gray','sent'=>'blue','partial'=>'amber','received'=>'emerald'][$po->status]??'gray' }}-700 px-3 py-1.5 rounded-full capitalize">{{ $po->status }}</span>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100"><h2 class="text-sm font-semibold text-gray-700">Line Items</h2></div>
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50/80"><th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Item</th><th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">Qty</th><th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">Unit Price</th><th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">Total</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($po->lines as $line)
                <tr><td class="px-4 py-2.5">{{ $line->description }}</td><td class="px-4 py-2.5 text-right font-mono">{{ $line->quantity }}</td><td class="px-4 py-2.5 text-right">Rp {{ number_format($line->unit_price,0,',','.') }}</td><td class="px-4 py-2.5 text-right font-semibold">Rp {{ number_format($line->total,0,',','.') }}</td></tr>
                @endforeach
            </tbody>
            <tfoot><tr class="border-t border-gray-200 bg-gray-50/50"><td colspan="3" class="px-4 py-3 text-right text-sm font-semibold">Grand Total</td><td class="px-4 py-3 text-right font-bold">Rp {{ number_format($po->total,0,',','.') }}</td></tr></tfoot>
        </table>
    </div>

    @if(in_array($po->status, ['draft','sent','partial']))
    <a href="{{ route('panel.inventory.gr.create', ['po_id' => $po->id]) }}" class="inline-block mt-4 bg-emerald-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-emerald-700 transition-colors">Create Goods Receipt</a>
    @endif
</div>

@endsection
