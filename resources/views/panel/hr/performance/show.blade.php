@extends('panel.layout')
@section('title', 'Performance Review')
@section('content')

<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Performance Review</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $review->employee?->full_name }} — {{ $review->review_date?->format('d M Y') }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Reviewer</span>
                    <p class="text-sm font-medium text-gray-800">{{ $review->reviewer?->name }}</p>
                </div>
                @php $sc = ['draft' => 'gray', 'completed' => 'emerald', 'acknowledged' => 'blue'][$review->status] ?? 'gray'; @endphp
                <span class="text-xs font-medium bg-{{ $sc }}-50 text-{{ $sc }}-700 px-3 py-1.5 rounded-full capitalize">{{ $review->status }}</span>
            </div>
        </div>
        <div class="p-6 space-y-5">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Period: {{ $review->period_start?->format('d M Y') }} – {{ $review->period_end?->format('d M Y') }}</h3>
            </div>

            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Scores</h3>
                <div class="space-y-2">
                    @foreach(($review->scores ?? []) as $key => $val)
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 w-24 capitalize">{{ $key }}</span>
                        <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-500 rounded-full" style="width:{{ ($val/5)*100 }}%"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-800 w-6 text-right">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-4 border-t border-gray-100 pt-4">
                <span class="text-xs text-gray-500">Overall Rating:</span>
                <span class="text-2xl font-bold text-gray-900">{{ $review->overall_rating }}/5</span>
            </div>

            @if($review->strengths)
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Strengths</h3>
                <p class="text-sm text-gray-700 bg-emerald-50 rounded-xl p-3">{{ $review->strengths }}</p>
            </div>
            @endif

            @if($review->improvements)
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Areas for Improvement</h3>
                <p class="text-sm text-gray-700 bg-amber-50 rounded-xl p-3">{{ $review->improvements }}</p>
            </div>
            @endif

            @if(!empty($review->goals))
            <div>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Goals</h3>
                <ul class="list-disc pl-5 text-sm text-gray-700 space-y-1">
                    @foreach($review->goals as $goal)
                    <li>{{ $goal }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($review->acknowledged_at)
            <div class="text-xs text-gray-400 border-t border-gray-100 pt-4">
                Acknowledged: {{ $review->acknowledged_at->format('d M Y H:i') }}
            </div>
            @endif
        </div>
    </div>

    <a href="{{ route('panel.hr.performance.index') }}" class="inline-flex items-center gap-1 text-sm text-primary-600 hover:underline mt-4">« Back to Reviews</a>
</div>

@endsection
