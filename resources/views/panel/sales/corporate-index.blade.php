@extends('panel.layout')
@section('title', 'Corporate Account')
@section('content')

@php $prop = app('current_property'); @endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Corporate Account</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola akun korporat, tarif khusus, dan tracking komitmen malam</p>
    </div>
    <a href="{{ route('panel.sales.corporate.create') }}"
       class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Tambah Corporate
    </a>
</div>

{{-- Filters --}}
<div class="mb-6 flex flex-wrap items-center gap-3">
    <form method="GET" class="flex items-center gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari perusahaan atau kontak..."
               class="bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-72">
        <select name="status" onchange="this.form.submit()"
                class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
        </select>
        <button type="submit"
                class="inline-flex items-center gap-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Filter
        </button>
    </form>
</div>

{{-- Stats --}}
@php
$totalActive = \App\Models\CorporateAccount::where('property_id', $prop->id)->where('status', 'active')->count();
$totalRevenue = \App\Models\CorporateBooking::where('property_id', $prop->id)->sum('rate_applied');
$totalNights = \App\Models\CorporateAccount::where('property_id', $prop->id)->where('status', 'active')->sum('actual_room_nights');
@endphp
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Total Corporate</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $accounts->total() }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Akun Aktif</p>
        <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $totalActive }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Total Revenue</p>
        <p class="text-3xl font-bold text-indigo-600 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Room Nights (Tahun Ini)</p>
        <p class="text-3xl font-bold text-violet-600 mt-1">{{ number_format($totalNights, 0, ',', '.') }}</p>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Perusahaan</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kontak</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Rate</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Komitmen Malam</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Revenue</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($accounts as $account)
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-5 py-4">
                        <p class="font-semibold text-gray-900">{{ $account->company_name }}</p>
                        <p class="text-xs text-gray-400">{{ $account->industry ?? '-' }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-gray-700">{{ $account->contact_person ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $account->email ?? $account->phone ?? '-' }}</p>
                    </td>
                    <td class="px-5 py-4">
                        @php
                        $typeLabels = ['fixed'=>'Fixed','percentage_discount'=>'Diskon %','dynamic'=>'Dynamic'];
                        @endphp
                        <span class="text-xs font-medium bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">{{ $typeLabels[$account->rate_agreement_type] ?? $account->rate_agreement_type }}</span>
                        @if($account->rate_agreement_type === 'percentage_discount')
                        <span class="text-xs text-gray-400 ml-1">{{ $account->discount_pct }}%</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @if($account->annual_room_night_commitment > 0)
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-full bg-gray-100 rounded-full h-2 max-w-[120px]">
                                <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ min(100, $account->nightCommitmentPct()) }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ $account->actual_room_nights }}/{{ $account->annual_room_night_commitment }} ({{ $account->nightCommitmentPct() }}%)</span>
                        </div>
                        @else
                        <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right font-semibold text-gray-900">
                        Rp {{ number_format($account->totalRevenue(), 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        @php
                        $sColors = ['active'=>'emerald','suspended'=>'amber','expired'=>'rose'];
                        $sLabels = ['active'=>'Aktif','suspended'=>'Ditangguhkan','expired'=>'Kadaluarsa'];
                        @endphp
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-{{ $sColors[$account->status] ?? 'gray' }}-50 text-{{ $sColors[$account->status] ?? 'gray' }}-700 border border-{{ $sColors[$account->status] ?? 'gray' }}-200">
                            {{ $sLabels[$account->status] ?? $account->status }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <a href="{{ route('panel.sales.corporate.show', $account->id) }}"
                           class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <p class="text-sm">Belum ada corporate account</p>
                        <a href="{{ route('panel.sales.corporate.create') }}" class="text-indigo-600 text-sm font-semibold hover:underline mt-1 inline-block">Tambah Corporate Account</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($accounts->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/30">
        {{ $accounts->links() }}
    </div>
    @endif
</div>
@endsection
