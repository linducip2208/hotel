@extends('panel.layout')
@section('title', 'Neraca')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('panel.accounting.dashboard') }}"
           class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 shadow-card transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Neraca</h1>
            <p class="text-sm text-gray-500 mt-0.5">Laporan posisi keuangan per {{ \Carbon\Carbon::parse($asOf)->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    <form method="GET" class="flex items-center gap-2">
        <input type="date" name="date" value="{{ $asOf }}"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors">Tampilkan</button>
    </form>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Aset —--}}
    <div>
        <div class="bg-white rounded-xl shadow-card border border-gray-100 overflow-hidden mb-4">
            <div class="px-5 py-3 bg-emerald-50 border-b border-emerald-100">
                <h2 class="text-base font-bold text-emerald-800">Aset Lancar</h2>
            </div>
            <table class="w-full text-sm">
                @php $subCurrentAssets = 0; @endphp
                @forelse($currentAssets as $a)
                @php $subCurrentAssets += $a->balance; @endphp
                <tr class="border-b border-gray-50">
                    <td class="px-5 py-2 text-gray-600">{{ $a->code }} — {{ $a->name }}</td>
                    <td class="px-5 py-2 text-right font-mono text-gray-900 text-xs">Rp {{ number_format($a->balance, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td class="px-5 py-4 text-center text-gray-400 text-sm" colspan="2">Tidak ada data</td></tr>
                @endforelse
                <tr class="bg-emerald-50/50 font-semibold">
                    <td class="px-5 py-2 text-emerald-800">Total Aset Lancar</td>
                    <td class="px-5 py-2 text-right font-mono text-emerald-800 text-xs">Rp {{ number_format($subCurrentAssets, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-3 bg-emerald-50 border-b border-emerald-100">
                <h2 class="text-base font-bold text-emerald-800">Aset Tetap</h2>
            </div>
            <table class="w-full text-sm">
                @php $subFixedAssets = 0; @endphp
                @forelse($fixedAssets as $a)
                @php $subFixedAssets += $a->balance; @endphp
                <tr class="border-b border-gray-50">
                    <td class="px-5 py-2 text-gray-600">{{ $a->code }} — {{ $a->name }}</td>
                    <td class="px-5 py-2 text-right font-mono text-gray-900 text-xs">Rp {{ number_format($a->balance, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td class="px-5 py-4 text-center text-gray-400 text-sm" colspan="2">Tidak ada data</td></tr>
                @endforelse
                <tr class="bg-emerald-50/50 font-semibold">
                    <td class="px-5 py-2 text-emerald-800">Total Aset Tetap</td>
                    <td class="px-5 py-2 text-right font-mono text-emerald-800 text-xs">Rp {{ number_format($subFixedAssets, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl shadow-card p-4 mt-4">
            <p class="text-emerald-50 text-xs uppercase tracking-wide">Total Aset</p>
            <p class="text-white text-2xl font-bold mt-0.5">Rp {{ number_format($totalAssets, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Kewajiban & Ekuitas —--}}
    <div>
        <div class="bg-white rounded-xl shadow-card border border-gray-100 overflow-hidden mb-4">
            <div class="px-5 py-3 bg-rose-50 border-b border-rose-100">
                <h2 class="text-base font-bold text-rose-800">Kewajiban Jangka Pendek</h2>
            </div>
            <table class="w-full text-sm">
                @php $subCurrentLiab = 0; @endphp
                @forelse($currentLiabilities as $a)
                @php $subCurrentLiab += $a->balance; @endphp
                <tr class="border-b border-gray-50">
                    <td class="px-5 py-2 text-gray-600">{{ $a->code }} — {{ $a->name }}</td>
                    <td class="px-5 py-2 text-right font-mono text-gray-900 text-xs">Rp {{ number_format($a->balance, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td class="px-5 py-4 text-center text-gray-400 text-sm" colspan="2">Tidak ada data</td></tr>
                @endforelse
                <tr class="bg-rose-50/50 font-semibold">
                    <td class="px-5 py-2 text-rose-800">Total Kewajiban Jk Pendek</td>
                    <td class="px-5 py-2 text-right font-mono text-rose-800 text-xs">Rp {{ number_format($subCurrentLiab, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow-card border border-gray-100 overflow-hidden mb-4">
            <div class="px-5 py-3 bg-rose-50 border-b border-rose-100">
                <h2 class="text-base font-bold text-rose-800">Kewajiban Jangka Panjang</h2>
            </div>
            <table class="w-full text-sm">
                @php $subLongLiab = 0; @endphp
                @forelse($longTermLiabilities as $a)
                @php $subLongLiab += $a->balance; @endphp
                <tr class="border-b border-gray-50">
                    <td class="px-5 py-2 text-gray-600">{{ $a->code }} — {{ $a->name }}</td>
                    <td class="px-5 py-2 text-right font-mono text-gray-900 text-xs">Rp {{ number_format($a->balance, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td class="px-5 py-4 text-center text-gray-400 text-sm" colspan="2">Tidak ada data</td></tr>
                @endforelse
                <tr class="bg-rose-50/50 font-semibold">
                    <td class="px-5 py-2 text-rose-800">Total Kewajiban Jk Panjang</td>
                    <td class="px-5 py-2 text-right font-mono text-rose-800 text-xs">Rp {{ number_format($subLongLiab, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow-card border border-gray-100 overflow-hidden">
            <div class="px-5 py-3 bg-blue-50 border-b border-blue-100">
                <h2 class="text-base font-bold text-blue-800">Ekuitas</h2>
            </div>
            <table class="w-full text-sm">
                @forelse($equity as $a)
                <tr class="border-b border-gray-50">
                    <td class="px-5 py-2 text-gray-600">{{ $a->code }} — {{ $a->name }}</td>
                    <td class="px-5 py-2 text-right font-mono text-gray-900 text-xs">Rp {{ number_format($a->balance, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td class="px-5 py-4 text-center text-gray-400 text-sm" colspan="2">Tidak ada data</td></tr>
                @endforelse
                <tr class="bg-blue-50/50 font-semibold">
                    <td class="px-5 py-2 text-blue-800">Total Ekuitas</td>
                    <td class="px-5 py-2 text-right font-mono text-blue-800 text-xs">Rp {{ number_format($totalEquity, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="bg-gradient-to-r from-slate-600 to-slate-700 rounded-xl shadow-card p-4 mt-4">
            <p class="text-slate-200 text-xs uppercase tracking-wide">Total Kewajiban + Ekuitas</p>
            <p class="text-white text-2xl font-bold mt-0.5">Rp {{ number_format($totalLiabilities + $totalEquity, 0, ',', '.') }}</p>
        </div>
    </div>
</div>

{{-- Persamaan Akuntansi --}}
@php $balanced = abs($totalAssets - ($totalLiabilities + $totalEquity)) < 1; @endphp
<div class="mt-6 p-4 rounded-xl border text-center {{ $balanced ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' }}">
    <p class="text-sm font-semibold {{ $balanced ? 'text-emerald-700' : 'text-rose-700' }}">
        Aset (Rp {{ number_format($totalAssets, 0, ',', '.') }})
        = Kewajiban (Rp {{ number_format($totalLiabilities, 0, ',', '.') }})
        + Ekuitas (Rp {{ number_format($totalEquity, 0, ',', '.') }})
    </p>
    @if($balanced)
    <p class="text-xs text-emerald-500 mt-1">✅ Persamaan akuntansi seimbang</p>
    @else
    <p class="text-xs text-rose-500 mt-1">⚠️ Persamaan akuntansi tidak seimbang — periksa jurnal</p>
    @endif
</div>

@endsection
