@extends('panel.layout')
@section('title', 'Bank Reconciliation')
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Bank Reconciliation</h1>
        <p class="text-sm text-gray-500 mt-0.5">Match bank statements with PMS ledger entries</p>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="document.getElementById('importForm').classList.toggle('hidden')"
                class="bg-white border border-gray-200 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-xl transition-colors">
            Import Statement
        </button>
    </div>
</div>

{{-- Import form --}}
<div id="importForm" class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 mb-6 hidden">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Import Bank Statement</h3>
    <form method="POST" action="{{ route('panel.finance.bank-recon.import') }}" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Bank Account</label>
                <select name="bank_account_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
                    <option value="">Select account</option>
                    @foreach($accounts ?? [] as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->bank_name }} ({{ $acc->account_no }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Statement File</label>
                <input type="file" name="file" accept=".csv,.ofx,.qfx" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">Import</button>
            <button type="button" onclick="this.closest('#importForm').classList.add('hidden')"
                    class="bg-gray-100 hover:bg-gray-200 text-sm font-medium px-4 py-2 rounded-xl transition-colors">Cancel</button>
        </div>
    </form>
</div>

{{-- Statements list --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Account</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Period</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Closing Balance</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Lines</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($statements as $s)
                @php $sc = match($s->status) { 'reconciled' => 'emerald', 'reconciling' => 'blue', 'imported' => 'amber', default => 'gray' }; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <span class="text-sm font-medium text-gray-800">{{ $s->bankAccount?->bank_name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">
                        {{ $s->period_from?->format('d M') }} – {{ $s->period_to?->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-semibold text-gray-900">
                        Rp {{ number_format($s->closing_balance, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right text-sm">{{ $s->lines_count ?? $s->lines()->count() }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-2.5 py-1 rounded-full capitalize">{{ $s->status }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <a href="?statement_id={{ $s->id }}" class="text-xs font-medium text-primary-600 hover:underline">Match</a>
                        <form method="POST" action="{{ route('panel.finance.bank-recon.auto-match') }}" class="inline ml-2">
                            @csrf
                            <input type="hidden" name="statement_id" value="{{ $s->id }}">
                            <button type="submit" class="text-xs font-medium text-emerald-600 hover:underline" onclick="return confirm('Auto-match transaksi dari statement ini?')">Auto Match</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-12 text-center text-sm text-gray-400">No bank statements imported.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($statements->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $statements->links() }}</div>
    @endif
</div>

{{-- Matching results --}}
@if(isset($matchResult))
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">Matching Results</h2>
        <div class="flex gap-3 text-xs">
            <span class="text-emerald-600 font-medium">{{ count($matchResult['matched'] ?? []) }} matched</span>
            <span class="text-amber-600 font-medium">{{ count($matchResult['suggested'] ?? []) }} suggested</span>
            <span class="text-red-500 font-medium">{{ count($matchResult['bank_only'] ?? []) }} bank only</span>
            <span class="text-violet-500 font-medium">{{ count($matchResult['pms_only'] ?? []) }} PMS only</span>
        </div>
    </div>

    {{-- Matched --}}
    @if(!empty($matchResult['matched']))
    <div class="px-5 py-3 border-b border-gray-50">
        <h3 class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-lg inline-block mb-2">Auto-Matched (exact amount + date + reference)</h3>
        <div class="space-y-2">
            @foreach($matchResult['matched'] as $m)
            <div class="flex items-center justify-between text-xs bg-gray-50 rounded-lg px-3 py-2">
                <div>
                    <span class="text-gray-700 font-medium">{{ $m['bank_line']->description }}</span>
                    <span class="text-gray-400 ml-2">{{ $m['bank_line']->transaction_date?->format('d M') }}</span>
                </div>
                <div>
                    <span class="font-mono font-semibold text-emerald-700">Rp {{ number_format($m['bank_amount'],0,',','.') }}</span>
                    <span class="text-gray-400 mx-2">↔</span>
                    <span class="font-mono font-semibold text-gray-700">Rp {{ number_format($m['folio_amount'],0,',','.') }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Suggested --}}
    @if(!empty($matchResult['suggested']))
    <div class="px-5 py-3 border-b border-gray-50">
        <h3 class="text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-1 rounded-lg inline-block mb-2">Suggested Matches (need review)</h3>
        <div class="space-y-2">
            @foreach($matchResult['suggested'] as $m)
            <div class="flex items-center justify-between text-xs bg-gray-50 rounded-lg px-3 py-2">
                <div>
                    <span class="text-gray-700 font-medium">{{ $m['bank_line']->description }}</span>
                    <span class="text-gray-400 ml-2">{{ $m['bank_line']->transaction_date?->format('d M') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono font-semibold text-amber-700">Rp {{ number_format($m['bank_amount'],0,',','.') }}</span>
                    @if($m['folio_payment'])
                    <span class="text-gray-400">↔</span>
                    <span class="font-mono font-semibold text-gray-700">Rp {{ number_format($m['folio_amount'],0,',','.') }}</span>
                    <form method="POST" action="{{ route('panel.finance.bank-recon.match') }}">
                        @csrf
                        <input type="hidden" name="bank_line_id" value="{{ $m['bank_line']->id }}">
                        <input type="hidden" name="folio_payment_id" value="{{ $m['folio_payment']->id }}">
                        <button class="text-xs bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-2 py-1 rounded-lg transition-colors">Confirm</button>
                    </form>
                    @else
                    <span class="text-red-400">No payment match</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Bank Only --}}
    @if(!empty($matchResult['bank_only']))
    <div class="px-5 py-3 border-b border-gray-50">
        <h3 class="text-xs font-semibold text-red-700 bg-red-50 px-2 py-1 rounded-lg inline-block mb-2">Bank Only (no PMS match)</h3>
        <div class="space-y-1">
            @foreach($matchResult['bank_only'] as $m)
            <div class="flex justify-between text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                <span>{{ $m['bank_line']->description }}</span>
                <span class="font-mono">Rp {{ number_format($m['bank_amount'],0,',','.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- PMS Only --}}
    @if(!empty($matchResult['pms_only']))
    <div class="px-5 py-3">
        <h3 class="text-xs font-semibold text-violet-700 bg-violet-50 px-2 py-1 rounded-lg inline-block mb-2">PMS Only (no bank match)</h3>
        <div class="space-y-1">
            @foreach($matchResult['pms_only'] as $m)
            <div class="flex justify-between text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                <span>Folio payment #{{ $m['folio_payment']->id }}</span>
                <span class="font-mono">Rp {{ number_format($m['folio_amount'],0,',','.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

@endsection
