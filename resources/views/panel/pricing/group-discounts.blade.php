@extends('panel.layout')
@section('title', 'Group Pricing')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Group Pricing (Diskon per Grup)</h1>
    <p class="text-sm text-gray-500 mt-0.5">Atur harga khusus per grup tamu (pemerintah, member, senior, dll.) — otomatis diterapkan di booking engine</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Form --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Tambah Diskon Grup</h2>
            <form method="POST" action="{{ route('panel.pricing.group-discounts.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Grup</label>
                    <input type="text" name="name" required placeholder="cth: Pemerintah / Member" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Kode (opsional)</label>
                    <input type="text" name="code" placeholder="cth: GOV" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Tipe</label>
                        <select name="discount_type" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="percent">Persen (%)</option>
                            <option value="fixed">Nominal (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Nilai</label>
                        <input type="number" name="discount_value" required step="0.01" min="0" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Tipe Kamar</label>
                        <select name="room_type_id" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua tipe</option>
                            @foreach($roomTypes as $rt)
                            <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Rate Plan</label>
                        <select name="rate_plan_id" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Semua plan</option>
                            @foreach($ratePlans as $rp)
                            <option value="{{ $rp->id }}">{{ $rp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Min Malam</label>
                        <input type="number" name="min_nights" value="1" min="1" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Berlaku Sampai</label>
                        <input type="date" name="ends_at" class="w-full rounded-xl border-gray-200 text-sm px-3 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700 pt-1">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600"> Aktif
                </label>
                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">Simpan</button>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Daftar Diskon Grup</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Grup</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Diskon</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Berlaku Untuk</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Min Malam</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($discounts as $d)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $d->name }}</p>
                                @if($d->code)<span class="text-xs font-mono text-indigo-600">{{ $d->code }}</span>@endif
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-indigo-600">
                                {{ $d->discount_type === 'fixed' ? 'Rp '.number_format($d->discount_value,0,',','.') : rtrim(rtrim(number_format($d->discount_value,2),'0'),'.').'%' }}
                            </td>
                            <td class="px-4 py-3.5 text-gray-600">
                                {{ $d->roomType?->name ?? 'Semua tipe' }}
                                <span class="text-gray-300">/</span>
                                {{ $d->ratePlan?->name ?? 'Semua plan' }}
                            </td>
                            <td class="px-4 py-3.5 text-center text-gray-600">{{ $d->min_nights }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($d->is_active)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif</span>
                                @else
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-400"><span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>Non-aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <form method="POST" action="{{ route('panel.pricing.group-discounts.destroy', $d->id) }}" onsubmit="return confirm('Hapus diskon grup ini?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Belum ada diskon grup.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
