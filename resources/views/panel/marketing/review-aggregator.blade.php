@extends('panel.layout')
@section('title', 'Agregator Ulasan')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Agregator Ulasan</h1>
        <p class="text-sm text-gray-500 mt-0.5">Pantau dan kumpulkan ulasan dari berbagai sumber dalam satu dashboard</p>
    </div>
    <form method="POST" action="{{ route('panel.marketing.review-aggregator.pull') }}">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Tarik Ulasan
        </button>
    </form>
</div>

{{-- Stats Cards --}}
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Ulasan</p>
        <p class="text-3xl font-bold text-gray-900">{{ $total }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Rating Rata-Rata</p>
        <div class="flex items-center gap-2">
            <p class="text-3xl font-bold text-gray-900">{{ number_format($avgRating, 1) }}</p>
            <div class="flex items-center text-amber-400">
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= round($avgRating))
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    @endif
                @endfor
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Sumber Ulasan</p>
        <div class="flex flex-wrap gap-2 mt-1">
            @foreach ($bySource as $src)
            <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded-full font-medium">
                {{ ucfirst($src['source']) }}
                <span class="text-indigo-400">({{ $src['count'] }})</span>
            </span>
            @endforeach
            @if (empty($bySource))
            <span class="text-sm text-gray-400">—</span>
            @endif
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-card border border-gray-100 p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Rating Tertinggi</p>
        <p class="text-3xl font-bold text-gray-900">{{ array_key_last(array_filter($byRating)) ?? '—' }}<span class="text-lg text-gray-400 font-normal"> bintang</span></p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Rating Distribution --}}
    <div class="lg:col-span-1 bg-white rounded-2xl shadow-card border border-gray-100 p-5 h-fit">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Distribusi Rating</h2>
        @php $maxCount = max($byRating ?: [0]); @endphp
        <div class="space-y-2.5">
            @for ($i = 5; $i >= 1; $i--)
            @php $count = $byRating[$i] ?? 0; $pct = $maxCount > 0 ? ($count / $maxCount) * 100 : 0; @endphp
            <div class="flex items-center gap-3 text-xs">
                <span class="w-8 text-right font-semibold text-gray-600 shrink-0">{{ $i }}<span class="text-amber-400 ml-0.5">&#9733;</span></span>
                <div class="flex-1 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                </div>
                <span class="w-8 text-right font-mono text-gray-500 shrink-0">{{ $count }}</span>
            </div>
            @endfor
        </div>
    </div>

    {{-- Recent Reviews Table --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Ulasan Terbaru</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Sumber</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Penulis</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Rating</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Komentar</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($recent as $review)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1 text-xs bg-{{ $review->source === 'google' ? 'blue' : ($review->source === 'direct' ? 'violet' : 'gray') }}-50 text-{{ $review->source === 'google' ? 'blue' : ($review->source === 'direct' ? 'violet' : 'gray') }}-700 px-2 py-0.5 rounded-full font-medium capitalize">
                                {{ $review->source }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-sm font-medium text-gray-800">{{ $review->author_name ?? $review->guest?->full_name ?? 'Anonim' }}</td>
                        <td class="px-4 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-0.5 text-amber-400">
                                @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'fill-current' : '' }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                @endfor
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-sm text-gray-600 max-w-xs truncate">{{ Str::limit($review->comment, 80) }}</td>
                        <td class="px-4 py-3.5 text-right text-sm text-gray-500 whitespace-nowrap">{{ $review->reviewed_at?->format('d M Y') ?? $review->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600">Belum ada ulasan</p>
                                <p class="text-xs text-gray-400 mt-1">Klik "Tarik Ulasan" untuk mengimpor dari Google</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('reviewChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($byRating)) !!},
            datasets: [{
                data: {!! json_encode(array_values($byRating)) !!},
                backgroundColor: ['#fbbf24','#f59e0b','#fb923c','#f97316','#ef4444'].slice(0, {{ count($byRating) }}),
                borderWidth: 0
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
    });
});
</script>
@endpush

@endsection
