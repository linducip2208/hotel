@extends('panel.layout')
@section('title', $account->company_name . ' — Corporate')
@section('content')

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.sales.corporate.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $account->company_name }}</h1>
        <p class="text-sm text-gray-500">Corporate Account Detail</p>
    </div>
    <div class="ml-auto flex items-center gap-2">
        <a href="{{ route('panel.sales.corporate.edit', $account->id) }}"
           class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Info Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Informasi Perusahaan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-400">Perusahaan</span><p class="font-semibold text-gray-900">{{ $account->company_name }}</p></div>
                <div><span class="text-gray-400">NPWP</span><p class="font-semibold text-gray-900">{{ $account->tax_id ?? '-' }}</p></div>
                <div><span class="text-gray-400">Industri</span><p class="font-semibold text-gray-900">{{ $account->industry ?? '-' }}</p></div>
                <div><span class="text-gray-400">Kontak</span><p class="font-semibold text-gray-900">{{ $account->contact_person ?? '-' }}</p></div>
                <div><span class="text-gray-400">Telepon</span><p class="font-semibold text-gray-900">{{ $account->phone ?? '-' }}</p></div>
                <div><span class="text-gray-400">Email</span><p class="font-semibold text-gray-900">{{ $account->email ?? '-' }}</p></div>
                <div class="sm:col-span-2"><span class="text-gray-400">Alamat</span><p class="font-semibold text-gray-900">{{ $account->address ?? '-' }}</p></div>
            </div>
        </div>

        {{-- Performance --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Performa</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Booking</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $performance['total_bookings'] }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Revenue</p>
                    <p class="text-2xl font-bold text-emerald-600">Rp {{ number_format($performance['total_revenue'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Diskon</p>
                    <p class="text-2xl font-bold text-amber-600">Rp {{ number_format($performance['total_discount'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Total Malam</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ $performance['total_nights'] }}</p>
                </div>
            </div>
        </div>

        {{-- Commitment Tracker --}}
        @if($account->annual_room_night_commitment > 0)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Commitment Tracker</h2>
            <div class="flex items-center gap-6">
                <div class="flex-1">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-500">Progress Malam</span>
                        <span class="font-bold text-gray-900">{{ $account->actual_room_nights }} / {{ $account->annual_room_night_commitment }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-4">
                        <div class="bg-gradient-to-r from-indigo-500 to-violet-500 h-4 rounded-full transition-all"
                             style="width: {{ min(100, $account->nightCommitmentPct()) }}%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold {{ $account->nightCommitmentPct() >= 100 ? 'text-emerald-600' : 'text-indigo-600' }}">{{ $account->nightCommitmentPct() }}%</p>
                    <p class="text-xs text-gray-400">tercapai</p>
                </div>
            </div>
            @if($account->credit_limit > 0)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Utilisasi Kredit</span>
                    <span class="font-bold text-gray-900">{{ $performance['credit_utilization_pct'] }}% dari Rp {{ number_format($account->credit_limit, 0, ',', '.') }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="bg-{{ $performance['credit_utilization_pct'] > 80 ? 'rose' : 'amber' }}-400 h-3 rounded-full transition-all"
                         style="width: {{ min(100, $performance['credit_utilization_pct']) }}%"></div>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Booking History --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Riwayat Booking</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Reservasi</th>
                            <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Sumber</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Rate</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Diskon</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($account->bookings()->with('reservation')->latest()->limit(10)->get() as $booking)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                @if($booking->reservation)
                                <a href="{{ route('panel.fo.reservations.show', $booking->reservation_id) }}" class="text-indigo-600 font-semibold hover:underline">
                                    #{{ $booking->reservation->id }}
                                </a>
                                @else
                                #{{ $booking->reservation_id }}
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $booking->booking_source }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($booking->rate_applied, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-rose-600">-Rp {{ number_format($booking->discount_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-xs text-gray-400">{{ $booking->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada booking</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right sidebar --}}
    <div class="space-y-6">
        {{-- Status & Contract --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <h3 class="font-bold text-gray-900 mb-3">Status & Kontrak</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-400">Status</span>
                    @php
                    $sColors = ['active'=>'emerald','suspended'=>'amber','expired'=>'rose'];
                    $sLabels = ['active'=>'Aktif','suspended'=>'Ditangguhkan','expired'=>'Kadaluarsa'];
                    @endphp
                    <span class="font-semibold text-{{ $sColors[$account->status] ?? 'gray' }}-600">{{ $sLabels[$account->status] ?? $account->status }}</span>
                </div>
                <div class="flex justify-between"><span class="text-gray-400">Jenis Rate</span><span class="font-semibold">{{ ['fixed'=>'Fixed','percentage_discount'=>'Diskon %','dynamic'=>'Dynamic'][$account->rate_agreement_type] }}</span></div>
                @if($account->rate_agreement_type === 'percentage_discount')
                <div class="flex justify-between"><span class="text-gray-400">Diskon</span><span class="font-semibold">{{ $account->discount_pct }}%</span></div>
                @endif
                <div class="flex justify-between"><span class="text-gray-400">Limit Kredit</span><span class="font-semibold">Rp {{ number_format($account->credit_limit, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Term Bayar</span><span class="font-semibold">{{ $account->payment_terms_days }} hari</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Mulai</span><span class="font-semibold">{{ $account->contract_start ? $account->contract_start->format('d M Y') : '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Selesai</span><span class="font-semibold">{{ $account->contract_end ? $account->contract_end->format('d M Y') : '-' }}</span></div>
            </div>
        </div>

        {{-- Negotiated Rates --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-gray-900">Tarif Negosiasi</h3>
                <button onclick="document.getElementById('addRateForm').classList.toggle('hidden')"
                        class="text-xs text-indigo-600 font-semibold hover:underline">+ Tambah</button>
            </div>
            <form id="addRateForm" method="POST" action="{{ route('panel.sales.corporate.rates', $account->id) }}" class="hidden mb-3 p-3 bg-gray-50 rounded-xl space-y-2">
                @csrf
                <select name="room_type_id" required class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    <option value="">Pilih Tipe Kamar</option>
                    @foreach($roomTypes as $rt)
                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="negotiated_rate" placeholder="Rate (Rp)" required
                       class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded">
                    <label class="text-xs text-gray-600">Aktif</label>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white text-sm font-semibold py-2 rounded-lg hover:bg-indigo-700 transition-colors">Simpan</button>
            </form>
            <div class="space-y-2">
                @forelse($account->rates as $rate)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0 text-sm">
                    <div>
                        <span class="font-semibold text-gray-900">{{ $rate->roomType->name ?? 'Room #'.$rate->room_type_id }}</span>
                        <span class="text-xs {{ $rate->is_active ? 'text-emerald-600' : 'text-gray-400' }} ml-1">({{ $rate->is_active ? 'Aktif' : 'Nonaktif' }})</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-indigo-600">Rp {{ number_format($rate->negotiated_rate, 0, ',', '.') }}</span>
                        <form method="POST" action="{{ route('panel.sales.corporate.rates.delete', [$account->id, $rate->id]) }}" onsubmit="return confirm('Hapus rate ini?')">
                            @csrf @method('DELETE')
                            <button class="text-rose-400 hover:text-rose-600 text-xs">Hapus</button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400">Belum ada tarif khusus</p>
                @endforelse
            </div>
        </div>

        {{-- Notes --}}
        @if($account->notes)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
            <h3 class="font-bold text-gray-900 mb-2">Catatan</h3>
            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $account->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
