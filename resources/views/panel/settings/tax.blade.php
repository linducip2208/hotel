@extends('panel.layout')
@section('title', 'Tax Configuration')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Tax Configuration</h1>
    <p class="text-sm text-gray-500 mt-0.5">PPN, PB1, and NPWP settings for this property</p>
</div>

@if (session('status'))
<div class="mb-5 bg-emerald-50 border border-emerald-100 rounded-2xl px-5 py-3.5 flex items-center gap-3">
    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    <span class="text-sm text-emerald-700 font-medium">{{ session('status') }}</span>
</div>
@endif

<div class="grid md:grid-cols-3 gap-5">

    {{-- Tax config form --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
            <div class="px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-700">Tax Settings</h2>
            </div>
            <form method="POST" action="{{ route('panel.settings.tax.update') }}" class="p-5 space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Region Code <span class="text-red-500">*</span></label>
                    <input type="text" name="region_code" value="{{ $property->region_code }}" required
                           placeholder="e.g. 31 (DKI Jakarta)"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <p class="text-xs text-gray-400 mt-1">Used for PB1 regional tax rate lookup</p>
                </div>

                <div class="flex items-center gap-3 p-4 bg-gray-50/60 rounded-xl border border-gray-100">
                    <input type="hidden" name="is_pkp" value="0">
                    <input type="checkbox" name="is_pkp" id="is_pkp" value="1"
                           {{ $property->is_pkp ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-400">
                    <label for="is_pkp" class="text-sm font-medium text-gray-700">PKP — PPN Collector</label>
                    <span class="ml-auto text-xs text-gray-400">VAT 11% applies</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">NPWP</label>
                    <input type="text" name="npwp" value="{{ $property->npwp }}"
                           placeholder="00.000.000.0-000.000"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">NSFP Series</label>
                    <input type="text" name="nsfp_series" value="{{ $property->nsfp_series }}"
                           placeholder="010"
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <p class="text-xs text-gray-400 mt-1">Tax invoice series code from DJP</p>
                </div>

                <button type="submit"
                        class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                    Save Tax Settings
                </button>
            </form>
        </div>
    </div>

    {{-- PB1 rate history --}}
    <div>
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">PB1 Rate History</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse ($rates as $r)
                <div class="px-5 py-3 hover:bg-gray-50/40 transition-colors">
                    <div class="flex items-center justify-between mb-0.5">
                        <span class="text-sm font-medium text-gray-800">{{ $r->region_name }}</span>
                        <span class="text-sm font-bold text-primary-700">{{ $r->rate }}%</span>
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $r->effective_from->format('d M Y') }} → {{ $r->effective_until?->format('d M Y') ?? '∞' }}
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-sm text-gray-400">No rate history</div>
                @endforelse
            </div>
        </div>
    </div>

</div>

{{-- Deposit Configuration per Rate Plan --}}
<div class="mt-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Konfigurasi Deposit per Rate Plan</h2>
            <p class="text-xs text-gray-400 mt-0.5">Atur kebijakan deposit untuk setiap rate plan</p>
        </div>
        @if($ratePlans->isEmpty())
        <div class="px-5 py-8 text-center text-sm text-gray-400">Belum ada rate plan aktif.</div>
        @else
        <form method="POST" action="{{ route('panel.settings.tax.deposit-config') }}">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Rate Plan</th>
                            <th class="px-5 py-3">Tipe Deposit</th>
                            <th class="px-5 py-3">Nilai Deposit</th>
                            <th class="px-5 py-3">Jatuh Tempo (hari sebelum check-in)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($ratePlans as $rp)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-5 py-3 font-medium text-gray-900">
                                <input type="hidden" name="rate_plan_id[]" value="{{ $rp->id }}">
                                {{ $rp->name }}
                            </td>
                            <td class="px-5 py-3">
                                @php $dc = $rp->deposit_config ?? []; @endphp
                                <select name="deposit_type[]" class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="none" {{ ($dc['type'] ?? 'none') === 'none' ? 'selected' : '' }}>Tanpa Deposit</option>
                                    <option value="percentage" {{ ($dc['type'] ?? '') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                    <option value="fixed" {{ ($dc['type'] ?? '') === 'fixed' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                                    <option value="night_count" {{ ($dc['type'] ?? '') === 'night_count' ? 'selected' : '' }}>Jumlah Malam</option>
                                </select>
                            </td>
                            <td class="px-5 py-3">
                                <input type="number" name="deposit_value[]"
                                       value="{{ $dc['value'] ?? '' }}"
                                       placeholder="{{ ($dc['type'] ?? 'none') === 'percentage' ? 'Contoh: 50' : 'Contoh: 500000' }}"
                                       min="0"
                                       class="w-32 border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </td>
                            <td class="px-5 py-3">
                                <input type="number" name="deposit_due_days[]"
                                       value="{{ $dc['due_days_before_checkin'] ?? '' }}"
                                       placeholder="Contoh: 7"
                                       min="0" max="90"
                                       class="w-20 border border-gray-300 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                    Simpan Konfigurasi Deposit
                </button>
            </div>
        </form>
        @endif
    </div>
</div>

@endsection
