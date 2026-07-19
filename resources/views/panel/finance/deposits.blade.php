@extends('panel.layout')
@section('title', 'Manajemen Deposit')
@section('content')

@php
    $fmtRupiah = function($val) { return 'Rp ' . number_format(abs($val), 0, ',', '.'); };
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Manajemen Deposit</h1>
    <p class="text-sm text-slate-500 mt-0.5">Kelola deposit tamu: jaminan booking, insidental, event, dan grup</p>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Total Ditahan</p>
                <p class="text-xl font-bold text-slate-900">{{ $fmtRupiah($stats['total_held']) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Refund Bulan Ini</p>
                <p class="text-xl font-bold text-emerald-700">{{ $fmtRupiah($stats['refunded_this_month']) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 rounded-xl bg-rose-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Hangus Bulan Ini</p>
                <p class="text-xl font-bold text-rose-700">{{ $fmtRupiah($stats['forfeited_this_month']) }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Deposit Aktif</p>
                <p class="text-xl font-bold text-slate-900">{{ $stats['total_active'] }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="mb-4 flex flex-wrap items-center gap-3">
    <form method="GET" class="flex flex-wrap items-center gap-3" id="filterForm">
        <select name="status" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-400">
            <option value="">Semua Status</option>
            <option value="held" {{ request('status') === 'held' ? 'selected' : '' }}>Ditahan</option>
            <option value="partially_refunded" {{ request('status') === 'partially_refunded' ? 'selected' : '' }}>Refund Sebagian</option>
            <option value="fully_refunded" {{ request('status') === 'fully_refunded' ? 'selected' : '' }}>Refund Penuh</option>
            <option value="forfeited" {{ request('status') === 'forfeited' ? 'selected' : '' }}>Hangus</option>
        </select>
        <select name="deposit_type" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-400">
            <option value="">Semua Tipe</option>
            <option value="booking_guarantee" {{ request('deposit_type') === 'booking_guarantee' ? 'selected' : '' }}>Jaminan Booking</option>
            <option value="incidental" {{ request('deposit_type') === 'incidental' ? 'selected' : '' }}>Insidental</option>
            <option value="event" {{ request('deposit_type') === 'event' ? 'selected' : '' }}>Event</option>
            <option value="group" {{ request('deposit_type') === 'group' ? 'selected' : '' }}>Grup</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}" placeholder="Dari" onchange="this.form.submit()"
               class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-400">
        <input type="date" name="to" value="{{ request('to') }}" placeholder="Sampai" onchange="this.form.submit()"
               class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-400">
        @if(request()->anyFilled(['status', 'deposit_type', 'from', 'to']))
            <a href="{{ route('panel.finance.deposits.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Reset</a>
        @endif
    </form>
    <div class="flex-1"></div>
    <button onclick="document.getElementById('receiveModal').classList.remove('hidden')"
            class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Terima Deposit
    </button>
</div>

{{-- Deposits Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tamu / Reservasi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipe</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Jumlah</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Refund</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Tgl Terima</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($deposits as $d)
                @php
                    $sc = match($d->status) {
                        'held' => 'blue',
                        'partially_refunded' => 'amber',
                        'fully_refunded' => 'emerald',
                        'forfeited' => 'rose',
                        default => 'gray'
                    };
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-5 py-3.5 text-sm font-mono text-slate-500">#{{ $d->id }}</td>
                    <td class="px-4 py-3.5">
                        @if($d->reservation)
                            <p class="text-sm font-medium text-slate-800">{{ $d->reservation->primaryGuest?->full_name }}</p>
                            <p class="text-xs text-slate-400">{{ $d->reservation->ref }}</p>
                        @elseif($d->guest)
                            <p class="text-sm font-medium text-slate-800">{{ $d->guest->full_name }}</p>
                        @else
                            <span class="text-sm text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs font-medium bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                            {{ match($d->deposit_type) {
                                'booking_guarantee' => 'Jaminan Booking',
                                'incidental' => 'Insidental',
                                'event' => 'Event',
                                'group' => 'Grup',
                                default => $d->deposit_type
                            } }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm font-bold text-slate-900">{{ $fmtRupiah($d->amount) }}</td>
                    <td class="px-4 py-3.5 text-right font-mono text-sm text-slate-600">{{ $d->refunded_amount > 0 ? $fmtRupiah($d->refunded_amount) : '—' }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $sc }}-50 text-{{ $sc }}-700">
                            {{ match($d->status) {
                                'held' => 'Ditahan',
                                'partially_refunded' => 'Refund Sebagian',
                                'fully_refunded' => 'Refund Penuh',
                                'forfeited' => 'Hangus',
                                default => $d->status
                            } }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-slate-500">{{ \Carbon\Carbon::parse($d->received_date)->format('d M Y') }}</td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($d->status === 'held')
                                <button onclick="openRefund({{ $d->id }}, {{ $d->amount - $d->refunded_amount }})"
                                        class="text-xs font-medium text-emerald-600 hover:text-emerald-800 transition-colors">Refund</button>
                                <button onclick="openForfeit({{ $d->id }})"
                                        class="text-xs font-medium text-rose-600 hover:text-rose-800 transition-colors">Hanguskan</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            <p class="text-sm font-medium text-slate-600">Belum ada deposit</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($deposits->hasPages())
    <div class="px-5 py-3 border-t border-slate-100">{{ $deposits->links() }}</div>
    @endif
</div>

{{-- Receive Deposit Modal --}}
<div id="receiveModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Terima Deposit Baru</h2>
            <button onclick="document.getElementById('receiveModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form action="{{ route('panel.finance.deposits.receive') }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Reservasi</label>
                    <input type="number" name="reservation_id" placeholder="ID Reservasi"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tamu</label>
                    <input type="number" name="guest_id" placeholder="ID Tamu"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tipe Deposit <span class="text-red-500">*</span></label>
                    <select name="deposit_type" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                        <option value="incidental">Insidental</option>
                        <option value="booking_guarantee">Jaminan Booking</option>
                        <option value="event">Event</option>
                        <option value="group">Grup</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" required step="0.01" min="1"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono outline-none focus:border-indigo-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Metode Bayar</label>
                    <input type="text" name="payment_method" placeholder="Cash / Transfer / CC"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tgl Diterima <span class="text-red-500">*</span></label>
                    <input type="date" name="received_date" required value="{{ now()->toDateString() }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan</label>
                <textarea name="notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('receiveModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2 rounded-xl shadow-sm transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Refund Modal --}}
<div id="refundModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Refund Deposit</h2>
            <button onclick="document.getElementById('refundModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form id="refundForm" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Jumlah Refund <span class="text-red-500">*</span></label>
                <input type="number" name="amount" id="refundAmount" required step="0.01" min="1"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono outline-none focus:border-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Metode Refund <span class="text-red-500">*</span></label>
                <input type="text" name="refund_method" required placeholder="Transfer / Cash / CC"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Alasan</label>
                <input type="text" name="reason" placeholder="Alasan refund"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('refundModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-5 py-2 rounded-xl shadow-sm transition-colors">Proses Refund</button>
            </div>
        </form>
    </div>
</div>

{{-- Forfeit Modal --}}
<div id="forfeitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Hanguskan Deposit</h2>
            <button onclick="document.getElementById('forfeitModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form id="forfeitForm" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Alasan Hangus <span class="text-red-500">*</span></label>
                <textarea name="reason" required rows="3" placeholder="Jelaskan alasan deposit dihanguskan..."
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('forfeitModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                <button type="submit"
                        class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-5 py-2 rounded-xl shadow-sm transition-colors">Hanguskan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openRefund(id, maxAmount) {
    document.getElementById('refundForm').action = '{{ url("panel/finance/deposits") }}/' + id + '/refund';
    document.getElementById('refundAmount').max = maxAmount;
    document.getElementById('refundAmount').value = maxAmount;
    document.getElementById('refundModal').classList.remove('hidden');
}
function openForfeit(id) {
    document.getElementById('forfeitForm').action = '{{ url("panel/finance/deposits") }}/' + id + '/forfeit';
    document.getElementById('forfeitModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
