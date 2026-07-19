@extends('panel.layout')
@section('title', 'Kurs Mata Uang')
@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Kurs Mata Uang</h1>
        <p class="text-sm text-gray-500 mt-0.5">Nilai tukar multi-currency real-time & kalkulator konversi</p>
    </div>
    <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('panel.finance.fx-rates.refresh') }}" class="inline">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-3.5 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh Live
            </button>
        </form>
    </div>
</div>

{{-- Currency Converter Calculator --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 mb-6" x-data="{ amount: 1000000, from: 'IDR', to: 'USD', result: null, loading: false }">
    <h2 class="text-sm font-semibold text-gray-700 mb-4">Kalkulator Konversi</h2>
    <div class="grid sm:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah</label>
            <input type="number" x-model="amount" min="0" step="any"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-mono outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Dari</label>
            <select x-model="from"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                <option value="IDR">🇮🇩 IDR</option>
                <option value="USD">🇺🇸 USD</option>
                <option value="SGD">🇸🇬 SGD</option>
                <option value="MYR">🇲🇾 MYR</option>
                <option value="AUD">🇦🇺 AUD</option>
                <option value="EUR">🇪🇺 EUR</option>
                <option value="JPY">🇯🇵 JPY</option>
                <option value="CNY">🇨🇳 CNY</option>
                <option value="GBP">🇬🇧 GBP</option>
                <option value="THB">🇹🇭 THB</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Ke</label>
            <select x-model="to"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                <option value="IDR">🇮🇩 IDR</option>
                <option value="USD">🇺🇸 USD</option>
                <option value="SGD">🇸🇬 SGD</option>
                <option value="MYR">🇲🇾 MYR</option>
                <option value="AUD">🇦🇺 AUD</option>
                <option value="EUR">🇪🇺 EUR</option>
                <option value="JPY">🇯🇵 JPY</option>
                <option value="CNY">🇨🇳 CNY</option>
                <option value="GBP">🇬🇧 GBP</option>
                <option value="THB">🇹🇭 THB</option>
            </select>
        </div>
        <div>
            <button @click="async () => {
                loading = true;
                const res = await fetch('{{ route('panel.finance.fx-rates.convert') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ amount: amount, from: from, to: to })
                });
                const data = await res.json();
                result = data;
                loading = false;
            }"
                    :disabled="loading"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors disabled:opacity-50 shadow-sm">
                <span x-show="!loading">Konversi</span>
                <span x-show="loading">...</span>
            </button>
        </div>
    </div>
    <div x-show="result" x-transition class="mt-4 p-4 bg-indigo-50 rounded-xl border border-indigo-100">
        <div class="text-center">
            <p class="text-sm text-indigo-600 mb-1">Hasil Konversi</p>
            <p class="text-2xl font-bold text-indigo-900 font-mono">
                <span x-text="result.from_formatted || ''"></span>
                <span class="mx-2 text-indigo-400">&rarr;</span>
                <span x-text="result.to_formatted || ''"></span>
            </p>
            <p class="text-xs text-indigo-500 mt-1" x-text="'Rate: 1 ' + (result.from_currency || '') + ' = ' + (result.rate || '') + ' ' + (result.to_currency || '')"></p>
        </div>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Live Rate Cards --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">Kurs Real-Time</h2>
            </div>
            <div class="grid grid-cols-3 sm:grid-cols-3 gap-px bg-gray-100">
                @php
                    $currencyFlags = [
                        'IDR' => '🇮🇩', 'USD' => '🇺🇸', 'SGD' => '🇸🇬', 'MYR' => '🇲🇾',
                        'AUD' => '🇦🇺', 'EUR' => '🇪🇺', 'JPY' => '🇯🇵', 'CNY' => '🇨🇳',
                        'GBP' => '🇬🇧', 'THB' => '🇹🇭',
                    ];
                    $liveRates = app(\App\Services\Finance\FxRateService::class)->getRateCard('IDR');
                @endphp
                @foreach($liveRates as $lr)
                <div class="bg-white p-4 text-center hover:bg-gray-50/60 transition-colors">
                    <p class="text-xl mb-1">{{ $currencyFlags[$lr['currency']] ?? '💱' }}</p>
                    <p class="text-xs font-bold text-gray-900">{{ $lr['currency'] }}</p>
                    <p class="text-lg font-bold text-indigo-900 font-mono mt-1">
                        {{ $lr['rate'] > 0 ? number_format($lr['rate'], $lr['rate'] < 1 ? 6 : 2) : '—' }}
                    </p>
                    <p class="text-[10px] text-gray-400 mt-0.5">1 IDR</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Set FX rate form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Tambah Kurs Manual</h2>
        </div>
        <form method="POST" action="{{ route('panel.finance.fx-rates.store') }}" class="p-5 space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Base <span class="text-red-500">*</span></label>
                    <input type="text" name="base_currency" required placeholder="IDR" maxlength="3"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-mono uppercase outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Quote <span class="text-red-500">*</span></label>
                    <input type="text" name="quote_currency" required placeholder="USD" maxlength="3"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-mono uppercase outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="rate_date" required value="{{ today()->toDateString() }}"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rate <span class="text-red-500">*</span></label>
                <input type="number" step="0.00000001" name="rate" required placeholder="15500"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Sumber</label>
                <select name="source"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                    <option value="manual">Manual</option>
                    <option value="bi">Bank Indonesia</option>
                    <option value="api">API</option>
                </select>
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Simpan Kurs
            </button>
        </form>
    </div>

</div>

{{-- Historical rates table --}}
<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden mt-6">
    <div class="p-5 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Riwayat Kurs</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Pair</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Rate</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Sumber</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($rates as $r)
                @php $sourceColor = match($r->source) { 'bi' => 'blue', 'open.er-api.com' => 'violet', 'api' => 'violet', default => 'gray' }; @endphp
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-sm font-semibold text-gray-900 bg-gray-100 px-2.5 py-1 rounded-md">
                            {{ $r->base_currency }}/{{ $r->quote_currency }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-600">{{ $r->rate_date instanceof \Carbon\Carbon ? $r->rate_date->translatedFormat('d M Y') : $r->rate_date }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-semibold text-gray-900">
                        {{ number_format($r->rate, 4) }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-xs font-medium bg-{{ $sourceColor }}-50 text-{{ $sourceColor }}-700 px-2 py-0.5 rounded-full capitalize">{{ $r->source }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-10 text-center text-sm text-gray-400">Belum ada data kurs. Klik Refresh Live untuk mengambil data.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
