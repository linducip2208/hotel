@extends('panel.layout')
@section('title', $pr->pr_number)
@section('content')

<div class="max-w-2xl">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $pr->pr_number }}</h1>
            <p class="text-sm text-gray-500 mt-1">Requested by {{ $pr->requester?->name }} — {{ $pr->created_at->format('d M Y') }}</p>
        </div>
        <span class="text-xs font-medium bg-{{ ['draft'=>'gray','pending'=>'amber','approved'=>'emerald','rejected'=>'red'][$pr->status]??'gray' }}-50 text-{{ ['draft'=>'gray','pending'=>'amber','approved'=>'emerald','rejected'=>'red'][$pr->status]??'gray' }}-700 px-3 py-1.5 rounded-full capitalize">{{ $pr->status }}</span>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="p-6 space-y-4">
            <div class="flex gap-8 text-sm"><span class="text-gray-500 w-24">Department</span><span class="font-medium">{{ $pr->department ?? '—' }}</span></div>
            <div class="flex gap-8 text-sm"><span class="text-gray-500 w-24">Required</span><span class="font-medium">{{ $pr->required_date?->format('d M Y') ?? '—' }}</span></div>
            <div class="flex gap-8 text-sm"><span class="text-gray-500 w-24">Notes</span><span>{{ $pr->notes ?? '—' }}</span></div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mt-6">
        <div class="px-5 py-4 border-b border-gray-100"><h2 class="text-sm font-semibold text-gray-700">Line Items</h2></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50/80"><th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Item</th><th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">Qty</th><th class="px-4 py-2 text-left text-xs font-semibold text-gray-500">Unit</th><th class="px-4 py-2 text-right text-xs font-semibold text-gray-500">Est. Price</th></tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($pr->lines as $line)
                    <tr>
                        <td class="px-4 py-2.5 text-gray-800">{{ $line->description }}</td>
                        <td class="px-4 py-2.5 text-right font-mono">{{ $line->quantity }}</td>
                        <td class="px-4 py-2.5 text-gray-500">{{ $line->unit }}</td>
                        <td class="px-4 py-2.5 text-right">{{ $line->estimated_price ? 'Rp '.number_format($line->estimated_price,0,',','.') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($pr->status === 'pending')
    <div class="flex gap-3 mt-4">
        <form method="POST" action="{{ route('panel.inventory.pr.approve', $pr->id) }}">@csrf<button class="bg-emerald-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-emerald-700 transition-colors">Approve PR</button></form>
        <form method="POST" action="{{ route('panel.inventory.pr.reject', $pr->id) }}" class="flex gap-2">@csrf
            <input type="text" name="reason" placeholder="Rejection reason" class="rounded-xl border border-gray-200 px-3 py-2 text-sm">
            <button class="bg-red-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-red-600 transition-colors">Reject</button>
        </form>
    </div>
    @endif
</div>

@endsection
