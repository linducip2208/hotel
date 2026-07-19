@extends('panel.layout')
@section('title', 'Detail Chargeback')
@section('content')

@php
    $fmtRupiah = function($val) { return 'Rp ' . number_format(abs($val), 0, ',', '.'); };

    $sc = match($chargeback->status) {
        'open' => 'blue',
        'under_review' => 'amber',
        'won' => 'emerald',
        'lost' => 'rose',
        'accepted' => 'gray',
        default => 'gray'
    };
@endphp

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('panel.finance.chargebacks.index') }}" class="p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Detail Chargeback #{{ $chargeback->id }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $sc }}-50 text-{{ $sc }}-700">
                {{ match($chargeback->status) {
                    'open' => 'Open',
                    'under_review' => 'Under Review',
                    'won' => 'Won',
                    'lost' => 'Lost',
                    'accepted' => 'Accepted',
                    default => $chargeback->status
                } }}
            </span>
        </p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Left column: Info + Evidence --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Chargeback Info Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Informasi Chargeback</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Tanggal</p>
                    <p class="text-sm font-medium text-slate-800">{{ \Carbon\Carbon::parse($chargeback->chargeback_date)->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Jumlah</p>
                    <p class="text-sm font-bold text-slate-900 font-mono">{{ $fmtRupiah($chargeback->amount) }}</p>
                </div>
                @if($chargeback->reservation)
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Reservasi</p>
                    <p class="text-sm font-medium text-slate-800">{{ $chargeback->reservation->ref }}</p>
                    <p class="text-xs text-slate-400">{{ $chargeback->reservation->primaryGuest?->full_name }}</p>
                </div>
                @endif
                @if($chargeback->card_brand)
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Kartu</p>
                    <p class="text-sm font-medium text-slate-800">{{ $chargeback->card_brand }} &bull;&bull;&bull;&bull; {{ $chargeback->card_last_4 }}</p>
                </div>
                @endif
                @if($chargeback->reason_code)
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Kode Alasan</p>
                    <p class="text-sm font-medium text-slate-800">{{ $chargeback->reason_code }}</p>
                </div>
                @endif
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Tenggat Bukti</p>
                    <p class="text-sm font-medium {{ $chargeback->evidence_deadline && $chargeback->evidence_deadline->isPast() ? 'text-rose-700' : 'text-slate-800' }}">
                        {{ $chargeback->evidence_deadline ? $chargeback->evidence_deadline->format('d M Y') : '—' }}
                        @if($chargeback->evidence_deadline && $chargeback->evidence_deadline->isPast() && in_array($chargeback->status, ['open', 'under_review']))
                            <span class="inline-flex items-center gap-1 text-xs text-rose-600 font-semibold ml-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Overdue
                            </span>
                        @endif
                    </p>
                </div>
                @if($chargeback->disputed_by)
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Disput By</p>
                    <p class="text-sm font-medium text-slate-800">{{ $chargeback->disputed_by }}</p>
                </div>
                @endif
                @if($chargeback->recovered_amount > 0)
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Recovered</p>
                    <p class="text-sm font-bold text-emerald-700 font-mono">{{ $fmtRupiah($chargeback->recovered_amount) }}</p>
                </div>
                @endif
            </div>
            @if($chargeback->reason_description)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Deskripsi</p>
                <p class="text-sm text-slate-600">{{ $chargeback->reason_description }}</p>
            </div>
            @endif
            @if($chargeback->internal_notes)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Catatan Internal</p>
                <p class="text-sm text-slate-500 italic">{{ $chargeback->internal_notes }}</p>
            </div>
            @endif
        </div>

        {{-- Evidence Gallery --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-700">Bukti Pendukung ({{ $chargeback->evidence->count() }})</h2>
                <button onclick="document.getElementById('evidenceModal').classList.remove('hidden')"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                    + Tambah Bukti
                </button>
            </div>

            @if($chargeback->evidence->isEmpty())
                <div class="text-center py-8 text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm">Belum ada bukti diunggah</p>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($chargeback->evidence as $ev)
                    <div class="border border-slate-200 rounded-xl p-3 hover:border-indigo-300 transition-colors">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-600">
                                {{ match($ev->evidence_type) {
                                    'reservation_details' => 'Detail Reservasi',
                                    'folio' => 'Folio',
                                    'checkin_record' => 'Check-in',
                                    'signature' => 'Tanda Tangan',
                                    'id_document' => 'KTP/ID',
                                    'email' => 'Email',
                                    'other' => 'Lainnya',
                                    default => $ev->evidence_type
                                } }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-700 truncate mb-2">{{ basename($ev->file_path) }}</p>
                        @if($ev->description)
                            <p class="text-xs text-slate-400">{{ $ev->description }}</p>
                        @endif
                        <p class="text-[10px] text-slate-400 mt-2">{{ $ev->uploaded_at?->format('d M Y H:i') }}</p>
                        <form action="{{ route('panel.finance.chargebacks.evidence.delete', [$chargeback->id, $ev->id]) }}" method="POST" class="mt-2">
                            @csrf @method('DELETE')
                            <button class="text-[10px] text-rose-500 hover:text-rose-700">Hapus</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Right column: Actions --}}
    <div class="space-y-6">
        {{-- Timeline / Actions card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Aksi</h2>

            @if($chargeback->status === 'open' || $chargeback->status === 'under_review')
                <div class="space-y-4">
                    @if($chargeback->status === 'open')
                    <form action="{{ route('panel.finance.chargebacks.submit', $chargeback->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                            Submit Respon
                        </button>
                    </form>
                    @endif

                    <div class="border-t border-slate-100 pt-4">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Catat Hasil</p>
                        <form action="{{ route('panel.finance.chargebacks.outcome', $chargeback->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <select name="decision" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                                    <option value="won">Won</option>
                                    <option value="lost">Lost</option>
                                    <option value="accepted">Accepted</option>
                                </select>
                            </div>
                            <div>
                                <input type="number" name="recovered_amount" step="0.01" min="0" placeholder="Jumlah dikembalikan (opsional)"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono outline-none focus:border-indigo-400">
                            </div>
                            <button type="submit"
                                    class="w-full bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                                Catat Hasil
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="text-sm text-slate-500">
                    <p>Chargeback sudah diselesaikan.</p>
                    @if($chargeback->final_decision)
                        <p class="mt-2 font-semibold text-slate-700">Keputusan: {{ ucfirst($chargeback->final_decision) }}</p>
                        <p class="text-xs text-slate-400">{{ $chargeback->final_decision_date?->format('d M Y') }}</p>
                    @endif
                    @if($chargeback->recovered_amount > 0)
                        <p class="mt-2 font-semibold text-emerald-700">Recovered: {{ $fmtRupiah($chargeback->recovered_amount) }}</p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Timeline --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Linimasa</h2>
            <div class="space-y-4">
                <div class="flex gap-3">
                    <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-xs text-slate-400">{{ $chargeback->created_at->format('d M Y H:i') }}</p>
                        <p class="text-sm text-slate-700">Chargeback dicatat — {{ $fmtRupiah($chargeback->amount) }}</p>
                    </div>
                </div>
                @if($chargeback->response_submitted_at)
                <div class="flex gap-3">
                    <div class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-xs text-slate-400">{{ $chargeback->response_submitted_at->format('d M Y H:i') }}</p>
                        <p class="text-sm text-slate-700">Respon disubmit — status: Under Review</p>
                    </div>
                </div>
                @endif
                @if($chargeback->final_decision)
                <div class="flex gap-3">
                    <div class="w-2 h-2 rounded-full {{ $chargeback->status === 'won' ? 'bg-emerald-500' : 'bg-rose-500' }} mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-xs text-slate-400">{{ $chargeback->final_decision_date?->format('d M Y') }}</p>
                        <p class="text-sm text-slate-700">Hasil: {{ ucfirst($chargeback->final_decision) }}
                            @if($chargeback->recovered_amount > 0)
                                — Recovered {{ $fmtRupiah($chargeback->recovered_amount) }}
                            @endif
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Add Evidence Modal --}}
<div id="evidenceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Tambah Bukti</h2>
            <button onclick="document.getElementById('evidenceModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
        </div>
        <form action="{{ route('panel.finance.chargebacks.evidence.store', $chargeback->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tipe Bukti <span class="text-red-500">*</span></label>
                <select name="evidence_type" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400">
                    <option value="reservation_details">Detail Reservasi</option>
                    <option value="folio">Folio / Invoice</option>
                    <option value="checkin_record">Catatan Check-in</option>
                    <option value="signature">Tanda Tangan</option>
                    <option value="id_document">KTP / Dokumen ID</option>
                    <option value="email">Email / Komunikasi</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">File <span class="text-red-500">*</span></label>
                <input type="file" name="file" required
                       class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi</label>
                <textarea name="description" rows="2" placeholder="Deskripsi bukti..."
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-indigo-400"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('evidenceModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2 rounded-xl shadow-sm transition-colors">Upload</button>
            </div>
        </form>
    </div>
</div>
@endsection
