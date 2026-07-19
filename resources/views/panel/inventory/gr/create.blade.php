@extends('panel.layout')
@section('title', 'Create Goods Receipt')
@section('content')

<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Goods Receipt @if($po) <span class="text-sm font-normal text-gray-500">for {{ $po->po_number }}</span> @endif</h1>

    <form method="POST" action="{{ route('panel.inventory.gr.store') }}" class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 space-y-4">
        @csrf
        @if($po)<input type="hidden" name="po_id" value="{{ $po->id }}">@else
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Purchase Order <span class="text-red-500">*</span></label>
            <select name="po_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
                <option value="">Select PO</option>
            </select>
        </div>
        @endif
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Received Date <span class="text-red-500">*</span></label>
            <input type="date" name="received_date" required value="{{ now()->toDateString() }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
        </div>

        <div class="border-t pt-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Line Items</h3>
            </div>
            @if($po)
                @foreach($po->lines as $i => $line)
                <div class="flex gap-2 mb-2">
                    <span class="flex-1 text-sm py-2 text-gray-700">{{ $line->description }}</span>
                    <input type="hidden" name="lines[{{ $i }}][stock_item_id]" value="{{ $line->stock_item_id }}">
                    <input type="number" name="lines[{{ $i }}][quantity_received]" value="{{ $line->quantity }}" step="0.001" min="0" class="w-24 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-right">
                    <span class="text-xs text-gray-400 self-center">of {{ $line->quantity }}</span>
                </div>
                @endforeach
            @endif
        </div>

        <div><label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label><textarea name="notes" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm"></textarea></div>

        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-3 rounded-xl shadow-sm transition-colors">Save Goods Receipt</button>
    </form>
</div>

@endsection
