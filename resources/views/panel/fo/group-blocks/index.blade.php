@extends('panel.layout')
@section('title', 'Group Blocks')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Group Block</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola reservasi grup / rombongan</p>
    </div>
    <a href="{{ route('panel.fo.group-blocks.create') }}"
       class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Group Block Baru
    </a>
</div>

<div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-100">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Grup</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-in</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Check-out</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Kamar</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Rate</th>
                    <th class="px-5 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse ($blocks as $block)
                @php
                    $statusBadge = match ($block->status) {
                        'tentative' => 'bg-yellow-100 text-yellow-700',
                        'definite' => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                        'completed' => 'bg-gray-100 text-gray-600',
                        default => 'bg-gray-100 text-gray-500',
                    };
                    $statusLabel = match ($block->status) {
                        'tentative' => 'Tentative',
                        'definite' => 'Definite',
                        'cancelled' => 'Dibatalkan',
                        'completed' => 'Selesai',
                        default => ucfirst($block->status),
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded-lg">{{ $block->block_code }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="font-medium text-gray-900">{{ $block->group_name }}</span>
                        @if ($block->company)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $block->company->name }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $block->check_in->format('d M Y') }}</td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $block->check_out->format('d M Y') }}</td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="text-sm font-semibold text-gray-800">{{ $block->rooms_count }}</span>
                        <span class="text-xs text-gray-400">/ {{ $block->rooms->first()?->rooms_count ?? '-' }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusBadge }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-right font-mono text-sm text-gray-800">
                        {{ $block->negotiated_rate ? 'Rp '.number_format($block->negotiated_rate, 0, ',', '.') : '—' }}
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('panel.fo.group-blocks.show', $block->id) }}"
                           class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 text-xs font-medium transition">
                            Detail
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Belum ada data</p>
                                <p class="text-xs text-gray-400 mt-0.5">Buat group block pertama Anda</p>
                            </div>
                            <a href="{{ route('panel.fo.group-blocks.create') }}"
                               class="mt-1 inline-flex items-center gap-1.5 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-xs font-medium transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Buat Group Block
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($blocks->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
        {{ $blocks->links() }}
    </div>
    @endif
</div>

@endsection
