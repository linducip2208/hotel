@extends('panel.layout')
@section('title', 'Linen Physical Audit')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.hk.linen.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Physical Audit</h1>
        <p class="text-sm text-gray-500">{{ now()->toDateString() }} — Count actual linen and compare with system</p>
    </div>
</div>

<form method="POST" action="{{ route('panel.hk.linen.audit.save') }}" class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    @csrf
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Linen Item</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Type</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">System Stock</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Actual Count</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Difference</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($items as $item)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $item->name }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full capitalize">{{ str_replace('_', ' ', $item->type) }}</span>
                    </td>
                    <td class="px-5 py-3 text-center font-medium">{{ $item->current_stock }}</td>
                    <td class="px-5 py-3 text-center">
                        <input type="number" name="counts[{{ $item->id }}]" value="{{ $item->current_stock }}" min="0"
                               class="w-24 px-3 py-1.5 border border-gray-200 rounded-lg text-center text-sm focus:ring-2 focus:ring-primary-100 focus:border-primary-400 outline-none">
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="diff-cell text-xs font-medium" data-system="{{ $item->current_stock }}">—</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($items->isEmpty())
    <div class="py-16 text-center text-gray-400">
        <p class="text-sm font-medium">No linen items to audit</p>
    </div>
    @else
    <div class="px-5 py-4 border-t border-gray-100 flex justify-end">
        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-5 py-2.5 rounded-xl text-sm shadow-sm transition-colors">
            Save Audit Results
        </button>
    </div>
    @endif
</form>

<script>
document.querySelectorAll('.diff-cell').forEach(function(cell) {
    const systemVal = parseInt(cell.dataset.system);
    const row = cell.closest('tr');
    const input = row.querySelector('input[type=number]');
    function update() {
        const actual = parseInt(input.value) || 0;
        const diff = actual - systemVal;
        if (diff > 0) {
            cell.textContent = '+' + diff;
            cell.className = 'diff-cell text-xs font-medium text-emerald-600';
        } else if (diff < 0) {
            cell.textContent = diff;
            cell.className = 'diff-cell text-xs font-medium text-red-600';
        } else {
            cell.textContent = '0';
            cell.className = 'diff-cell text-xs font-medium text-gray-400';
        }
    }
    input.addEventListener('input', update);
    update();
});
</script>

@endsection
