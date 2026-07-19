@extends('panel.layout')
@section('title', 'Agen Travel')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Agen Travel</h1>
    <p class="text-sm text-gray-500 mt-0.5">Kelola agen perjalanan & komisi</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Table --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            @if(($agents ?? collect())->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode IATA</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Komisi %</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Batas Kredit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Booking</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($agents as $agent)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5 text-sm font-medium text-gray-900">{{ $agent->name }}</td>
                            <td class="px-4 py-3.5">
                                @if($agent->iata_code)
                                <span class="text-xs font-mono font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md">{{ $agent->iata_code }}</span>
                                @else
                                <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right text-sm text-gray-700 tabular-nums">{{ number_format($agent->default_commission_pct ?? 0, 1) }}%</td>
                            <td class="px-4 py-3.5 text-right text-sm text-gray-700 tabular-nums">Rp {{ number_format($agent->credit_limit ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right text-sm text-gray-700 tabular-nums">
                                <a href="{{ route('panel.sales.travel-agents.show', $agent->id) }}" class="font-semibold text-indigo-600 hover:text-indigo-700">
                                    {{ $agent->reservations_count ?? $agent->total_bookings ?? 0 }}
                                </a>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-0.5 rounded-full {{ ($agent->is_active ?? true) ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ ($agent->is_active ?? true) ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    {{ ($agent->is_active ?? true) ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('panel.sales.travel-agents.show', $agent->id) }}"
                                       class="text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg transition-colors">
                                        Lihat
                                    </a>
                                    <a href="{{ route('panel.sales.travel-agents.edit', $agent->id) }}"
                                       class="text-xs font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 px-2.5 py-1.5 rounded-lg transition-colors">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('panel.sales.travel-agents.delete', $agent->id) }}" onsubmit="return confirm('Hapus agen {{ $agent->name }}? Semua alotmen terkait juga akan dihapus.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-500 bg-rose-50 hover:bg-rose-100 px-2.5 py-1.5 rounded-lg transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(($agents ?? collect())->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $agents->links() }}
            </div>
            @endif
            @else
            <div class="flex flex-col items-center justify-center py-20">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-base font-semibold text-gray-600">Belum ada agen travel</p>
                <p class="text-sm text-gray-400 mt-1.5 text-center max-w-sm">Tambah agen travel dari form di samping untuk mulai mengelola komisi dan alotmen.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Form Tambah Agen --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Tambah Agen</h2>
        </div>
        <form method="POST" action="{{ route('panel.sales.travel-agents.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Nama agen travel"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kode IATA</label>
                <input type="text" name="iata_code" placeholder="Contoh: 12345678"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all uppercase">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Komisi Default (%)</label>
                <input type="number" name="default_commission_pct" step="0.1" min="0" max="100" placeholder="10" value="10"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Batas Kredit (Rp)</label>
                <input type="number" name="credit_limit" min="0" placeholder="10000000" value="0"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
            </div>
            <div class="flex items-center gap-2.5">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="ta_is_active" value="1" checked
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="ta_is_active" class="text-xs font-semibold text-gray-600">Aktif</label>
            </div>
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Simpan
            </button>
        </form>
    </div>

</div>

@endsection
