@extends('panel.layout')
@section('title', 'Report Builder')
@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Custom Report Builder</h1>
        <p class="text-sm text-gray-500 mt-0.5">Buat laporan kustom dengan widget pilihan Anda</p>
    </div>
    <a href="{{ route('panel.reports.custom-reports.create') }}"
       class="bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-colors flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Buat Laporan
    </a>
</div>

@if (session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 4000)"
     class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="grid lg:grid-cols-3 gap-5">

    {{-- Available Widgets --}}
    <div>
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Widget Tersedia</h2>
                <p class="text-xs text-gray-400 mt-0.5">Drag widget ke laporan saat edit</p>
            </div>
            @php
                $catLabels = ['revenue' => 'Revenue', 'operations' => 'Operasional', 'guests' => 'Tamu', 'finance' => 'Keuangan'];
                $catIcons = ['revenue' => '📈', 'operations' => '🔧', 'guests' => '👥', 'finance' => '💰'];
            @endphp
            @foreach(['revenue', 'operations', 'guests', 'finance'] as $cat)
            <div class="border-b border-gray-50 last:border-0">
                <div class="px-5 py-2.5 bg-gray-50/50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ $catIcons[$cat] }} {{ $catLabels[$cat] }}
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($widgets as $key => $w)
                        @if($w['category'] === $cat)
                        <div class="px-5 py-2.5 flex items-center gap-2 text-sm">
                            <span class="text-gray-400 text-xs">{{ $w['type'] === 'line_chart' ? '📉' : ($w['type'] === 'bar_chart' ? '📊' : ($w['type'] === 'doughnut_chart' || $w['type'] === 'pie_chart' ? '🥧' : ($w['type'] === 'table' ? '📋' : '📊'))) }}</span>
                            <span class="text-sm text-gray-700">{{ $w['name'] }}</span>
                            <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded ml-auto">{{ $key }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Saved Reports --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Laporan Tersimpan</h2>
                <span class="text-xs text-gray-400">{{ $reports->total() }} laporan</span>
            </div>
            @if($reports->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm text-gray-500">Belum ada laporan kustom</p>
                <a href="{{ route('panel.reports.custom-reports.create') }}" class="mt-2 text-sm text-primary-600 hover:underline font-medium">Buat laporan pertama</a>
            </div>
            @else
            <div class="divide-y divide-gray-50">
                @foreach($reports as $r)
                @php $catColor = match($r->category) { 'revenue' => 'bg-emerald-50 text-emerald-700', 'operations' => 'bg-sky-50 text-sky-700', 'guests' => 'bg-violet-50 text-violet-700', 'finance' => 'bg-blue-50 text-blue-700', default => 'bg-gray-50 text-gray-500' }; @endphp
                <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/60 transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                        {{ $catIcons[$r->category] ?? '📊' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('panel.reports.custom-reports.show', $r->id) }}" class="text-sm font-semibold text-gray-900 hover:text-primary-600 transition-colors">{{ $r->name }}</a>
                        <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-400">
                            <span class="px-1.5 py-0.5 rounded {{ $catColor }} text-[10px] font-medium">{{ $catLabels[$r->category] ?? $r->category }}</span>
                            <span>&middot;</span>
                            <span>{{ $r->createdBy->name ?? 'System' }}</span>
                            <span>&middot;</span>
                            <span>{{ $r->updated_at->diffForHumans() }}</span>
                            @if($r->is_public)
                            <span class="text-[10px] bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded">Publik</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <a href="{{ route('panel.reports.custom-reports.show', $r->id) }}"
                           class="text-xs font-medium text-primary-600 bg-primary-50 px-2.5 py-1 rounded-lg hover:bg-primary-100 transition-colors">Lihat</a>
                        <a href="{{ route('panel.reports.custom-reports.edit', $r->id) }}"
                           class="text-xs font-medium text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg hover:bg-amber-100 transition-colors">Edit</a>
                        <form method="POST" action="{{ route('panel.reports.custom-reports.destroy', $r->id) }}" onsubmit="return confirm('Hapus laporan ini?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-xs font-medium text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 transition-colors">Hapus</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            @if($reports->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $reports->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
