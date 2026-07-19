@extends('panel.layout')
@section('title', 'Purchase Orders')
@section('content')

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900">Purchase Orders</h1>
    <a href="{{ route('panel.inventory.po.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">New PO</a>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50/80 border-b"><th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">PO #</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vendor</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order Date</th><th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th><th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th><th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th></tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($pos as $po)
            @php $sc = ['draft'=>'gray','sent'=>'blue','partial'=>'amber','received'=>'emerald','cancelled'=>'red'][$po->status]??'gray'; @endphp
            <tr class="hover:bg-gray-50/60">
                <td class="px-5 py-3.5 font-medium"><a href="{{ route('panel.inventory.po.show', $po->id) }}" class="text-primary-600 hover:underline">{{ $po->po_number }}</a></td>
                <td class="px-4 py-3.5 text-gray-700">{{ $po->vendor?->name }}</td>
                <td class="px-4 py-3.5 text-gray-600">{{ $po->order_date?->format('d M Y') }}</td>
                <td class="px-4 py-3.5 text-right font-mono">Rp {{ number_format($po->total,0,',','.') }}</td>
                <td class="px-4 py-3.5 text-center"><span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $po->status }}</span></td>
                <td class="px-4 py-3.5 text-right space-x-2">
                    @if($po->status === 'draft')<form method="POST" action="{{ route('panel.inventory.po.send', $po->id) }}" class="inline">@csrf<button class="text-xs text-blue-600 hover:underline">Send</button></form>@endif
                    <a href="{{ route('panel.inventory.gr.create', ['po_id' => $po->id]) }}" class="text-xs text-emerald-600 hover:underline">Receive</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-10 text-center text-sm text-gray-400">No purchase orders.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
