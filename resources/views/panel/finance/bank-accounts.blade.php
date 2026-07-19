@extends('panel.layout')
@section('title', 'Bank Accounts')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Bank Accounts</h1>
    <p class="text-sm text-gray-500 mt-0.5">Linked bank accounts for reconciliation</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Accounts list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="divide-y divide-gray-50">
                @forelse ($accounts as $a)
                <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50/60 transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-sm font-semibold text-gray-900">{{ $a->bank_name }}</span>
                            <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded-md">{{ $a->currency }}</span>
                        </div>
                        <div class="text-xs text-gray-400 flex items-center gap-3">
                            <span class="font-mono">{{ $a->account_no }}</span>
                            <span>·</span>
                            <span>{{ $a->account_holder }}</span>
                        </div>
                    </div>
                    @if ($a->coaAccount)
                    <span class="text-xs font-mono bg-primary-50 text-primary-700 px-2 py-0.5 rounded-md shrink-0">
                        {{ $a->coaAccount->code }}
                    </span>
                    @endif
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10">
                    <p class="text-sm text-gray-400">No bank accounts linked yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Add bank account form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Add Bank Account</h2>
        </div>
        <form method="POST" action="{{ route('panel.finance.bank-accounts.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Bank <span class="text-red-500">*</span></label>
                <input type="text" name="bank_name" required placeholder="BCA, Mandiri, BNI…"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Account No <span class="text-red-500">*</span></label>
                <input type="text" name="account_no" required placeholder="1234567890"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Holder Name <span class="text-red-500">*</span></label>
                <input type="text" name="account_holder" required placeholder="PT Hotel Indah"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Linked COA <span class="text-red-500">*</span></label>
                <select name="coa_account_id" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <option value="">— select account —</option>
                    @foreach ($coa as $c)
                    <option value="{{ $c->id }}">{{ $c->code }} {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Currency</label>
                <input type="text" name="currency" value="IDR" placeholder="IDR"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Add Account
            </button>
        </form>
    </div>

</div>

@endsection
