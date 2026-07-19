@extends('panel.layout')
@section('title', 'Budget')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Budget {{ $year }}</h1>
    <p class="text-sm text-gray-500 mt-0.5">Annual budget lines vs. actual performance</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Budget table --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Account</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Month</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Budget Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($lines as $l)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs text-gray-500">{{ $l->account?->code }}</span>
                                <span class="ml-2 text-sm text-gray-800">{{ $l->account?->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-600">
                                {{ \Carbon\Carbon::createFromDate($year, $l->month, 1)->format('M') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-sm text-gray-900">
                                Rp {{ number_format($l->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-10 text-center text-sm text-gray-400">No budget lines for {{ $year }}.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add budget line form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Add Budget Line</h2>
        </div>
        <form method="POST" action="{{ route('panel.finance.budget.store') }}" class="p-5 space-y-3">
            @csrf
            <input type="hidden" name="budget_period_id" value="{{ $period->id }}">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Account <span class="text-red-500">*</span></label>
                <select name="account_id" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="">— select account —</option>
                    @foreach ($coa as $c)
                    <option value="{{ $c->id }}">{{ $c->code }} {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Month <span class="text-red-500">*</span></label>
                <select name="month" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Amount (Rp) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="amount" required placeholder="0"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Save Budget Line
            </button>
        </form>
    </div>

</div>

@endsection
