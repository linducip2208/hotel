@extends('panel.layout')
@section('title', 'New Journal Entry')
@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('panel.accounting.journal.index') }}"
       class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">New Journal Entry</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manual double-entry journal — debits must equal credits</p>
    </div>
</div>

@if ($errors->any())
<div class="mb-5 bg-red-50 border border-red-100 rounded-2xl px-5 py-4">
    <div class="flex items-center gap-2 mb-1.5">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
        <span class="text-sm font-semibold text-red-700">Please fix the following errors:</span>
    </div>
    <p class="text-sm text-red-600">{{ $errors->first() }}</p>
</div>
@endif

<div class="max-w-3xl"
     x-data="{ lines: [{account_code:'', debit:'', credit:'', description:''}, {account_code:'', debit:'', credit:'', description:''}] }">
    <form method="POST" action="{{ route('panel.accounting.journal.store') }}">
        @csrf

        {{-- Description --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-5">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Description <span class="text-red-500">*</span></label>
            <input type="text" name="description" required placeholder="e.g. Manual adjustment — prepaid revenue Dec 2025"
                   value="{{ old('description') }}"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Journal lines --}}
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mb-5">
            <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Journal Lines</h2>
                <button type="button" @click="lines.push({account_code:'', debit:'', credit:'', description:''})"
                        class="text-xs font-medium text-primary-600 hover:text-primary-800 transition-colors">
                    + Add Line
                </button>
            </div>

            {{-- Column headers --}}
            <div class="grid grid-cols-12 gap-3 px-5 py-2.5 bg-gray-50/80 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <div class="col-span-3">Account Code</div>
                <div class="col-span-5">Description</div>
                <div class="col-span-2 text-right">Debit (Rp)</div>
                <div class="col-span-2 text-right">Credit (Rp)</div>
            </div>

            <div class="divide-y divide-gray-50 px-5 py-3 space-y-2">
                <template x-for="(l, i) in lines" :key="i">
                    <div class="grid grid-cols-12 gap-3 items-center py-1.5">
                        <div class="col-span-3">
                            <input :name="'lines['+i+'][account_code]'" x-model="l.account_code"
                                   placeholder="1100"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-mono outline-none focus:border-primary-400 transition-all">
                        </div>
                        <div class="col-span-5">
                            <input :name="'lines['+i+'][description]'" x-model="l.description"
                                   placeholder="Line description"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:border-primary-400 transition-all">
                        </div>
                        <div class="col-span-2">
                            <input :name="'lines['+i+'][debit]'" x-model="l.debit"
                                   type="number" step="1" min="0" placeholder="0"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-right font-mono outline-none focus:border-primary-400 transition-all">
                        </div>
                        <div class="col-span-2 flex items-center gap-1.5">
                            <input :name="'lines['+i+'][credit]'" x-model="l.credit"
                                   type="number" step="1" min="0" placeholder="0"
                                   class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-right font-mono outline-none focus:border-primary-400 transition-all">
                            <button type="button" @click="lines.length > 2 && lines.splice(i, 1)"
                                    :class="lines.length <= 2 ? 'opacity-20 cursor-not-allowed' : 'hover:text-red-400'"
                                    class="text-gray-300 transition-colors shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Totals row --}}
            <div class="grid grid-cols-12 gap-3 px-5 py-3.5 bg-gray-50/80 border-t border-gray-100">
                <div class="col-span-8 text-xs font-semibold text-gray-500 flex items-center">Total — must balance</div>
                <div class="col-span-2 text-right">
                    <span class="text-sm font-bold font-mono text-gray-900"
                          x-text="'Rp ' + lines.reduce((s,l)=>s+(parseFloat(l.debit)||0),0).toLocaleString('id-ID')"></span>
                </div>
                <div class="col-span-2 text-right">
                    <span class="text-sm font-bold font-mono text-gray-900"
                          x-text="'Rp ' + lines.reduce((s,l)=>s+(parseFloat(l.credit)||0),0).toLocaleString('id-ID')"></span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-colors">
                Post Journal Entry
            </button>
            <a href="{{ route('panel.accounting.journal.index') }}"
               class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

@endsection
