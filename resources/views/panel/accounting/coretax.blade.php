@extends('panel.layout')
@section('title', 'Coretax DJP')
@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-md shadow-amber-500/30">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-900">Coretax DJP — e-Faktur</h1>
            <p class="text-sm text-slate-500">Generate & preview e-Faktur XML format DJP 4.0</p>
        </div>
    </div>

    {{-- Section 1: Generate e-Faktur --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Generate e-Faktur
        </h2>

        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[280px]">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pilih Folio (yang sudah diselesaikan)</label>
                <select name="folio_id" class="w-full border border-slate-300 rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
                    <option value="">— Pilih folio —</option>
                    @foreach($folios as $f)
                        <option value="{{ $f->id }}" {{ (int) request('folio_id') === $f->id ? 'selected' : '' }}>
                            Folio #{{ $f->id }} — {{ $f->guest?->full_name ?? 'Tamu' }} (IDR {{ number_format($f->total_charges, 0) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">NSFP (opsional)</label>
                <input type="text" name="nsfp" value="{{ request('nsfp') }}" placeholder="Auto-generate"
                       class="w-full border border-slate-300 rounded-xl py-2 px-3 text-sm font-mono focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gradient-to-br from-amber-500 to-orange-600 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md shadow-amber-500/20 hover:shadow-lg hover:shadow-amber-500/30 hover:-translate-y-0.5 transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Generate XML
            </button>
        </form>

        {{-- Or generate from AR Invoice --}}
        <form method="GET" class="flex flex-wrap gap-4 items-end mt-4 pt-4 border-t border-slate-100">
            <div class="flex-1 min-w-[280px]">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Atau pilih AR Invoice</label>
                <select name="invoice_id" class="w-full border border-slate-300 rounded-xl py-2 px-3 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
                    <option value="">— Pilih invoice —</option>
                    @foreach($invoices as $inv)
                        <option value="{{ $inv->id }}" {{ (int) request('invoice_id') === $inv->id ? 'selected' : '' }}>
                            {{ $inv->invoice_no }} — {{ $inv->arAccount?->name }} (IDR {{ number_format($inv->grand_total, 0) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-white border-2 border-amber-500 text-amber-700 font-semibold px-5 py-2.5 rounded-xl hover:bg-amber-50 transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Generate XML (AR)
            </button>
        </form>
    </div>

    {{-- Section 2: XML Preview --}}
    @if($generated)
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6" x-data="{ copied: false }">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                XML Preview
            </h2>
            <div class="flex gap-2">
                <button @click="navigator.clipboard.writeText($refs.xmlCode.textContent); copied=true; setTimeout(()=>copied=false, 2000)"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition-colors">
                    <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    <svg x-show="copied" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="copied ? 'Disalin!' : 'Salin'"></span>
                </button>
                <a href="{{ route('panel.accounting.coretax.download', $generated['efaktur_id']) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-gradient-to-br from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 px-3 py-1.5 rounded-lg shadow-sm transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download .xml
                </a>
            </div>
        </div>

        {{-- Faktur info card --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">NSFP</p>
                <p class="text-sm font-mono font-bold text-slate-800">{{ $generated['nsfp'] }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Nomor Faktur</p>
                <p class="text-sm font-mono font-bold text-slate-800">{{ $generated['nomor_faktur'] }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Status</p>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">Normal</span>
            </div>
            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Record ID</p>
                <p class="text-sm font-mono font-bold text-slate-800">#{{ $generated['efaktur_id'] }}</p>
            </div>
        </div>

        {{-- XML Code Block --}}
        <div class="bg-slate-900 rounded-xl p-4 overflow-x-auto">
            <pre class="text-xs text-emerald-400 font-mono leading-relaxed" x-ref="xmlCode">{{ $generated['xml'] }}</pre>
        </div>
    </div>
    @endif

    {{-- Section 3: NSFP Pool --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                NSFP Pool
            </h2>
            <form method="POST" action="{{ route('panel.accounting.coretax.nsfp-generate') }}" class="flex gap-2">
                @csrf
                <input type="number" name="count" value="10" min="1" max="50"
                       class="w-20 border border-slate-300 rounded-lg py-1.5 px-2 text-xs text-center focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-gradient-to-br from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 px-3 py-1.5 rounded-lg shadow-sm transition-all">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Generate NSFP
                </button>
            </form>
        </div>

        @if($nsfpPool->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="pb-3 pr-4 font-semibold">ID</th>
                        <th class="pb-3 pr-4 font-semibold">Nomor Faktur (NSFP)</th>
                        <th class="pb-3 pr-4 font-semibold">Kode Status</th>
                        <th class="pb-3 pr-4 font-semibold">Status</th>
                        <th class="pb-3 pr-4 font-semibold">Dibuat</th>
                        <th class="pb-3 pr-4 font-semibold">Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nsfpPool as $record)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="py-2.5 pr-4 font-mono text-xs text-slate-500">#{{ $record->id }}</td>
                        <td class="py-2.5 pr-4 font-mono text-xs font-bold text-slate-800">{{ $record->nomor_faktur }}</td>
                        <td class="py-2.5 pr-4">
                            <span class="text-xs font-mono text-slate-600">{{ $record->kode_status ?? '—' }}</span>
                        </td>
                        <td class="py-2.5 pr-4">
                            @php
                                $badge = match ($record->status) {
                                    'normal' => 'bg-emerald-100 text-emerald-700',
                                    'draft' => 'bg-slate-100 text-slate-600',
                                    'available' => 'bg-emerald-100 text-emerald-700',
                                    'failed' => 'bg-rose-100 text-rose-700',
                                    'cancelled' => 'bg-slate-100 text-slate-500',
                                    default => 'bg-blue-100 text-blue-700',
                                };
                            @endphp
                            <span class="inline-flex text-[10px] font-semibold px-2 py-0.5 rounded {{ $badge }}">
                                {{ $record->status }}
                            </span>
                        </td>
                        <td class="py-2.5 pr-4 text-xs text-slate-400">{{ $record->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="py-2.5 text-xs text-slate-500">{{ $record->invoice_no ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="text-center py-12 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <p class="text-sm">Belum ada NSFP. Klik "Generate NSFP" untuk membuat pool.</p>
            </div>
        @endif
    </div>

    {{-- Section 4: Recent e-Faktur Records --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Riwayat e-Faktur
        </h2>
        @if($history->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="pb-3 pr-4 font-semibold">ID</th>
                        <th class="pb-3 pr-4 font-semibold">NSFP</th>
                        <th class="pb-3 pr-4 font-semibold">Invoice</th>
                        <th class="pb-3 pr-4 font-semibold">DPP</th>
                        <th class="pb-3 pr-4 font-semibold">PPN</th>
                        <th class="pb-3 pr-4 font-semibold">Status</th>
                        <th class="pb-3 pr-4 font-semibold">Tanggal</th>
                        <th class="pb-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $record)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="py-2.5 pr-4 font-mono text-xs text-slate-500">#{{ $record->id }}</td>
                        <td class="py-2.5 pr-4 font-mono text-xs font-bold text-slate-800">{{ $record->nomor_faktur }}</td>
                        <td class="py-2.5 pr-4 text-xs text-slate-600">{{ $record->invoice_no ?? '—' }}</td>
                        <td class="py-2.5 pr-4 text-xs font-mono text-slate-700">IDR {{ number_format($record->dpp, 0) }}</td>
                        <td class="py-2.5 pr-4 text-xs font-mono text-slate-700">IDR {{ number_format($record->ppn, 0) }}</td>
                        <td class="py-2.5 pr-4">
                            @php
                                $hBadge = match ($record->status) {
                                    'normal' => 'bg-emerald-100 text-emerald-700',
                                    'draft' => 'bg-slate-100 text-slate-600',
                                    '05' => 'bg-emerald-100 text-emerald-700',
                                    'failed' => 'bg-rose-100 text-rose-700',
                                    'cancelled' => 'bg-slate-200 text-slate-500',
                                    default => 'bg-blue-100 text-blue-700',
                                };
                            @endphp
                            <span class="inline-flex text-[10px] font-semibold px-2 py-0.5 rounded {{ $hBadge }}">
                                {{ $record->status }}
                            </span>
                        </td>
                        <td class="py-2.5 pr-4 text-xs text-slate-400">{{ $record->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="py-2.5">
                            <a href="{{ route('panel.accounting.coretax.download', $record->id) }}"
                               class="text-xs font-semibold text-amber-600 hover:text-amber-800 hover:underline">Download XML</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="text-center py-8 text-slate-400 text-sm">
                Belum ada record e-Faktur. Generate dari folio atau invoice di atas.
            </div>
        @endif
    </div>

</div>
@stop
