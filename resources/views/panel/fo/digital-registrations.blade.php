@extends('panel.layout')
@section('title', 'Registrasi Digital')
@section('content')

<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Registrasi Digital</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola link registrasi digital yang dikirim ke tamu</p>
        </div>
    </div>
</div>

{{-- Filter bar --}}
<div class="flex flex-wrap items-center gap-3 mb-5">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ref / nama tamu..."
               class="rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm text-gray-700 shadow-card focus:border-primary-400 outline-none w-64">
        <select name="status" onchange="this.form.submit()"
                class="rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm text-gray-700 shadow-card focus:border-primary-400 outline-none">
            <option value="">Semua Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Terkirim</option>
            <option value="viewed" {{ request('status') === 'viewed' ? 'selected' : '' }}>Dilihat</option>
            <option value="signed" {{ request('status') === 'signed' ? 'selected' : '' }}>Ditandatangani</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
        </select>
        <button type="submit"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-primary-600 px-4 py-2 rounded-xl hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Filter
        </button>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Reservasi</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Dikirim</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Dilihat</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Ditandatangani</th>
                    <th class="px-5 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse ($registrations as $reg)
                @php
                    $statusColors = [
                        'pending'   => 'bg-slate-100 text-slate-700',
                        'sent'      => 'bg-blue-100 text-blue-700',
                        'viewed'    => 'bg-indigo-100 text-indigo-700',
                        'signed'    => 'bg-amber-100 text-amber-700',
                        'completed' => 'bg-emerald-100 text-emerald-700',
                    ];
                    $statusLabels = [
                        'pending'   => 'Pending',
                        'sent'      => 'Terkirim',
                        'viewed'    => 'Dilihat',
                        'signed'    => 'Ditandatangani',
                        'completed' => 'Selesai',
                    ];
                    $badge = $statusColors[$reg->status] ?? 'bg-gray-100 text-gray-700';
                    $label = $statusLabels[$reg->status] ?? $reg->status;
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded-lg">{{ $reg->reservation?->ref }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="font-medium text-gray-900">{{ $reg->guest?->full_name ?: '—' }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $reg->sent_at?->format('d M Y H:i') ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $reg->viewed_at?->format('d M Y H:i') ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $reg->signed_at?->format('d M Y H:i') ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if (in_array($reg->status, ['pending']))
                            <form method="POST" action="{{ route('panel.fo.digital-registrations.send', $reg->id) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-xs font-medium transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    Kirim
                                </button>
                            </form>
                            @endif
                            @if ($reg->status === 'signed')
                            <form method="POST" action="{{ route('panel.fo.digital-registrations.complete', $reg->id) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-800 text-xs font-medium transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Selesai
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('panel.fo.digital-registrations.show', $reg->id) }}"
                               class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 text-xs font-medium transition">
                                Detail
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Belum ada registrasi digital</p>
                                <p class="text-xs text-gray-400 mt-0.5">Buat registrasi dari halaman reservasi</p>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($registrations->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">
        {{ $registrations->links() }}
    </div>
    @endif
</div>

@endsection
