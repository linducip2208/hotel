@extends('panel.layout')
@section('title', 'Detail Registrasi Digital')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="{{ route('panel.fo.digital-registrations') }}" class="text-sm text-primary-600 hover:text-primary-800 font-medium inline-flex items-center gap-1 mb-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke daftar
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Detail Registrasi Digital</h1>
    </div>
    <div class="flex items-center gap-2">
        @if (in_array($reg->status, ['pending']))
        <form method="POST" action="{{ route('panel.fo.digital-registrations.send', $reg->id) }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-blue-600 px-4 py-2 rounded-xl hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Kirim Link
            </button>
        </form>
        @endif
        @if ($reg->status === 'signed')
        <form method="POST" action="{{ route('panel.fo.digital-registrations.complete', $reg->id) }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-emerald-600 px-4 py-2 rounded-xl hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Tandai Selesai
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Status Timeline --}}
@php
    $steps = [
        ['key' => 'pending', 'label' => 'Pending',      'date' => $reg->created_at],
        ['key' => 'sent',    'label' => 'Terkirim',      'date' => $reg->sent_at],
        ['key' => 'viewed',  'label' => 'Dilihat',       'date' => $reg->viewed_at],
        ['key' => 'signed',  'label' => 'Ditandatangani','date' => $reg->signed_at],
        ['key' => 'completed','label'=> 'Selesai',       'date' => null],
    ];
    $currentIdx = array_search($reg->status, array_column($steps, 'key'));
@endphp

<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5 mb-6">
    <div class="flex items-center gap-1">
        @foreach ($steps as $i => $step)
            @php
                $done = $i <= $currentIdx;
                $isCurrent = $i === $currentIdx;
                $dotColor = $done ? 'bg-primary-500 text-white' : 'bg-gray-200 text-gray-400';
                $lineColor = $done ? 'bg-primary-300' : 'bg-gray-200';
            @endphp
            <div class="flex-1 flex items-center">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full {{ $dotColor }} flex items-center justify-center text-[11px] font-bold">{{ $i + 1 }}</div>
                    <span class="text-[10px] font-semibold text-gray-500 mt-1 whitespace-nowrap">{{ $step['label'] }}</span>
                    @if ($step['date'])
                    <span class="text-[9px] text-gray-400">{{ $step['date']->format('d/m H:i') }}</span>
                    @endif
                </div>
                @if ($i < count($steps) - 1)
                <div class="flex-1 h-1 {{ $lineColor }} rounded-full mx-1 mt-[-1.25rem]"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Reservation & Guest Info --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Info Reservasi & Tamu</h2>
        </div>
        <div class="p-5 space-y-3">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-xs text-gray-400 block">No. Reservasi</span>
                    <span class="font-mono font-semibold text-gray-800">{{ $reg->reservation?->ref ?: '—' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block">Tamu</span>
                    <span class="font-semibold text-gray-800">{{ $reg->guest?->full_name ?: '—' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block">Check-in</span>
                    <span class="text-gray-700">{{ $reg->reservation?->check_in?->format('d M Y') ?: '—' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block">Check-out</span>
                    <span class="text-gray-700">{{ $reg->reservation?->check_out?->format('d M Y') ?: '—' }}</span>
                </div>
            </div>
            <div>
                <span class="text-xs text-gray-400 block">Link Publik</span>
                <a href="{{ route('registration.form', $reg->token) }}" target="_blank"
                   class="text-sm text-primary-600 font-mono break-all hover:underline">
                    {{ route('registration.form', $reg->token) }}
                </a>
            </div>
            @if ($reg->ip_address)
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-xs text-gray-400 block">IP Address</span>
                    <span class="text-gray-700 font-mono">{{ $reg->ip_address }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block">User Agent</span>
                    <span class="text-gray-700 text-xs truncate block max-w-[200px]">{{ $reg->user_agent ?: '—' }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Form Data --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Data Registrasi</h2>
        </div>
        <div class="p-5">
            @if ($reg->form_data)
            <div class="space-y-3">
                @foreach($reg->form_data as $key => $val)
                <div>
                    <span class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold block">{{ str_replace('_', ' ', $key) }}</span>
                    <span class="text-sm text-gray-800">{{ $val ?: '—' }}</span>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="text-sm text-gray-500">Belum ada data registrasi</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Signature --}}
    @if ($reg->signature_path)
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Tanda Tangan</h2>
        </div>
        <div class="p-5">
            <img src="{{ asset('storage/' . $reg->signature_path) }}" alt="Tanda Tangan"
                 class="max-w-[300px] border border-gray-200 rounded-xl bg-white p-4">
        </div>
    </div>
    @endif

    {{-- ID Document --}}
    @if ($reg->id_document_path)
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Dokumen Identitas</h2>
        </div>
        <div class="p-5">
            <a href="{{ asset('storage/' . $reg->id_document_path) }}" target="_blank"
               class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 hover:text-primary-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Lihat Dokumen
            </a>
        </div>
    </div>
    @endif
</div>

@endsection
