@extends('panel.layout')
@section('title', 'Loyalty Tiers')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Loyalty Tiers</h1>
    <p class="text-sm text-gray-500 mt-0.5">Define membership levels and their benefits</p>
</div>

<div class="grid md:grid-cols-3 gap-5">

    {{-- Tiers list --}}
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tier</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Points Threshold</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Rate Discount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($tiers as $t)
                        @php
                            $tierColors = ['platinum' => 'violet', 'gold' => 'amber', 'silver' => 'gray', 'bronze' => 'orange'];
                            $tc = $tierColors[strtolower($t->slug ?? $t->name)] ?? 'primary';
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-{{ $tc }}-50 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-{{ $tc }}-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $t->name }}</div>
                                        @if ($t->slug)
                                        <div class="text-xs font-mono text-gray-400">{{ $t->slug }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="text-sm font-semibold text-gray-800 tabular-nums">{{ number_format($t->points_threshold) }}</span>
                                <span class="text-xs text-gray-400 ml-1">pts</span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                @if ($t->rate_discount_pct > 0)
                                <span class="text-sm font-bold text-primary-700">{{ $t->rate_discount_pct }}%</span>
                                @else
                                <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-10 text-center text-sm text-gray-400">No tiers yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add tier form --}}
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 divide-y divide-gray-50 h-fit">
        <div class="px-5 py-4">
            <h2 class="text-sm font-semibold text-gray-700">Add Tier</h2>
        </div>
        <form method="POST" action="{{ route('panel.loyalty.tiers.store') }}" class="p-5 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Silver, Gold, Platinum"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Slug <span class="text-red-500">*</span></label>
                <input type="text" name="slug" required placeholder="silver"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm font-mono outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Points Threshold <span class="text-red-500">*</span></label>
                <input type="number" name="points_threshold" required min="0" placeholder="5000"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                <p class="text-xs text-gray-400 mt-1">Lifetime points required to qualify</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rate Discount %</label>
                <input type="number" step="0.001" name="rate_discount_pct" placeholder="5"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-sm outline-none focus:bg-white focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <button type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm transition-colors">
                Create Tier
            </button>
        </form>
    </div>

</div>

@endsection
