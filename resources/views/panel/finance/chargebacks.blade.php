@extends('panel.layout')
@section('title', 'Chargeback')
@section('content')

@php
    $fmtRupiah = function($val) { return 'Rp ' . number_format(abs($val), 0, ',', '.'); };
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Penanganan Chargeback</h1>
    <p class="text-sm text-slate-500 mt-0.5">Monitor dan kelola sengketa pembayaran (chargeback) dari bank/payment gateway</p>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Open</p>
        <p class="text-2xl font-bold text-blue-700">{{ $stats['open'] }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Under Review</p>
        <p class="text-2xl font-bold text-amber-700">{{ $stats['under_review'] }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Won</p>
        <p class="text-2xl font-bold text-emerald-700">{{ $stats['won'] }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Lost</p>
        <p class="text-2xl font-bold text-rose-700">{{ $stats['lost'] }}</p>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Win Rate</p>
        <p class="text-2xl font-bold {{ $stats['win_rate'] >= 50 ? 'text-emerald-700' : 'text-rose-700' }}">{{ $stats['win_rate'] }}%</p>
    </div>
</div>

{{-- Deadline Alerts --}}
@if($alerts['overdue'] > 0)
<div class="bg-rose-50 border border-rose-200 rounded-xl p-3 mb-4 flex items-center gap-3 text-sm">
    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="font-semibold text-rose-800">{{ $alerts['overdue'] }} chargeback melewati batas waktu!</span>
</div>
@endif
@if($alerts['due_this_week'] > 0)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-4 flex items-center gap-3 text-sm">
    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span class="font-semibold text-amber-800">{{ $alerts['due_this_week'] }} chargeback jatuh tempo minggu ini!</span>
</div>
@endif

{{-- Filters --}}
<div class="mb-4 flex flex-wrap items-center gap-3">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <select name="status" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-400">
            <option value="">Semua Status</option>
            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
            <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
            <option value="won" {{ request('status') === 'won' ? 'selected' : '' }}>Won</option>
            <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Lost</option>
            <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}" onchange="this.form.submit()"
               class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-400">
        <input type="date" name="to" value="{{ request('to') }}" onchange="this.form.submit()"
               class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-400">
        @if(request()->anyFilled(['status', 'from', 'to']))
            <a href="{{ route('panel.finance.chargebacks.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Reset</a>
        @endif
    </form>
    <div class="flex-1"></div>
    <button onclick="document.getElementById('registerModal').classList.remove('hidden')"
            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Catat Chargeback
    </button>
</div>

{{-- Chargebacks Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Reservasi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Kartu</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Jumlah</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Alasan</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Tenggat</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($chargebacks as $cb)
                @php
                    $deadlineColor = 'slate';
                    if ($cb->evidence_deadline && $cb->evidence_deadline->isPast() && in_array($cb->status, ['open', 'under_review'])) {
                        $deadlineColor = 'rose';
                    } elseif ($cb->evidence_deadline && $cb->evidence_deadline->diffInDays(now()) <= 7 && in_array($cb->status, ['open', 'under_review'])) {
                        $deadlineColor = 'amber';
                    }

                    $sc = match($cb->status) {
                        'open' => 'blue',
                        'under_review' => 'amber',
                        'won' => 'emerald',
                        'lost' => 'rose',
                        'accepted' => 'gray',
                        default => 'gray'
                    };
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm text-slate-700">{{ \Carbon\Carbon::parse($cb->chargeback_date)->format('d M Y') }}</td>
                    <td class="px-4 py-3.5">
                        @if($cb->reservation)
                            <a href="{{ route('panel.finance.chargebacks.show', $cb->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                {{ $cb->reservation->ref }}
                            </a>
                        @else
                            <span class="text-sm text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-sm text-slate-600">
                        {{ $cb->card_brand ? $cb->card_brand . ' •••• ' . $cb->card_last_4 : '—' }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-slate-900">{{ $fmtRupiah($cb->amount) }}</td>
                    <td class="px-4 py-3.5 text-sm text-slate-500 max-w-[160px] truncate">
                        {{ $cb->reason_code ? $cb->reason_code . ': ' : '' }}{{ \Illuminate\Support\Str::limit($cb->reason_description, 40) }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($cb->evidence_deadline)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                bg-{{ $deadlineColor }}-50 text-{{ $deadlineColor }}-700">
                                {{ $cb->evidence_deadline->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $sc }}-50 text-{{ $sc }}-700">
                            {{ match($cb->status) {
                                'open' => 'Open',
                                'under_review' => 'Under Review',
                                'won' => 'Won',
                                'lost' => 'Lost',
                                'accepted' => 'Accepted',
                                default => $cb->status
                            } }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <a href="{{ route('panel.finance.chargebacks.show', $cb->id) }}"
                           class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                            Detail &rarr;
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-sm font-medium text-slate-600">Belum ada chargeback</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($chargebacks->hasPages())
    <div class="px-5 py-3 border-t border-slate-100">{{ $chargebacks->links() }}</div>
    @endif
</div>

{{-- Register Chargeback Modal --}}
<div id="registerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Catat Chargeback Baru</h2>
            <button onclick="document.getElementById('registerModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
        </div>
        <form action="{{ route('panel.finance.chargebacks.register') }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ID Pembayaran <span class="text-red-500">*</span></label>
                    <input type="number" name="payment_transaction_id" required placeholder="FolioPayment ID"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ID Reservasi</label>
                    <input type="number" name="reservation_id" placeholder="Reservation ID"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tgl Chargeback <span class="text-red-500">*</span></label>
                    <input type="date" name="chargeback_date" required value="{{ now()->toDateString() }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" required step="0.01" min="0"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono outline-none focus:border-indigo-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kode Alasan</label>
                    <select name="reason_code" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                        <option value="">— pilih —</option>
                        <option value="10.1">10.1 - EMV Liability Shift Counterfeit</option>
                        <option value="10.4">10.4 - Other Fraud</option>
                        <option value="13.1">13.1 - Service Not Provided</option>
                        <option value="13.2">13.2 - Canceled Recurring</option>
                        <option value="13.3">13.3 - Not as Described</option>
                        <option value="13.6">13.6 - Credit Not Processed</option>
                        <option value="13.7">13.7 - Canceled Merchandise</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Merk Kartu</label>
                    <input type="text" name="card_brand" placeholder="Visa / Mastercard"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi Alasan</label>
                <textarea name="reason_description" rows="2" placeholder="Detail alasan chargeback..."
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tenggat Bukti</label>
                <input type="date" name="evidence_deadline" value="{{ now()->addDays(14)->toDateString() }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan Internal</label>
                <textarea name="internal_notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('registerModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2 rounded-xl shadow-sm transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
