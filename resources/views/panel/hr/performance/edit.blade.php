@extends('panel.layout')
@section('title', 'Edit Performance Review')
@section('content')

<div class="max-w-2xl" x-data="reviewForm()">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Performance Review</h1>

    <form method="POST" action="{{ route('panel.hr.performance.update', $review->id) }}" class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 space-y-5">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Employee <span class="text-red-500">*</span></label>
                <select name="employee_id" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
                    <option value="">Select employee</option>
                    @foreach ($employees as $e)
                    <option value="{{ $e->id }}" {{ $review->employee_id == $e->id ? 'selected' : '' }}>{{ $e->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Review Date <span class="text-red-500">*</span></label>
                <input type="date" name="review_date" required value="{{ old('review_date', $review->review_date?->toDateString()) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Period Start <span class="text-red-500">*</span></label>
                <input type="date" name="period_start" required value="{{ old('period_start', $review->period_start?->toDateString()) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Period End <span class="text-red-500">*</span></label>
                <input type="date" name="period_end" required value="{{ old('period_end', $review->period_end?->toDateString()) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
            </div>
        </div>

        <div class="border-t border-gray-100 pt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Scores (1-5)</h3>
            <div class="grid grid-cols-2 gap-4">
                @php $criteria = ['attendance' => 'Attendance', 'punctuality' => 'Punctuality', 'quality' => 'Work Quality', 'teamwork' => 'Teamwork', 'leadership' => 'Leadership']; @endphp
                @foreach ($criteria as $key => $label)
                <div>
                    <label class="flex justify-between text-xs text-gray-600 mb-1">
                        <span>{{ $label }}</span>
                        <span class="font-bold text-primary-600" x-text="scores.{{ $key }}"></span>
                    </label>
                    <input type="range" name="scores[{{ $key }}]" x-model="scores.{{ $key }}" min="1" max="5" step="0.5" class="w-full accent-primary-500">
                </div>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Overall Rating <span class="text-red-500">*</span></label>
            <select name="overall_rating" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
                @for($i=5;$i>=1;$i--)
                <option value="{{ $i }}" {{ $review->overall_rating == $i ? 'selected' : '' }}>{{ $i }} — {{ ['','Needs Improvement','Fair','Good','Excellent','Outstanding'][$i] }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Strengths</label>
            <textarea name="strengths" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">{{ old('strengths', $review->strengths) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Areas for Improvement</label>
            <textarea name="improvements" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">{{ old('improvements', $review->improvements) }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Goals (one per line)</label>
            <textarea name="goals" rows="3" placeholder="Goal 1&#10;Goal 2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">{{ old('goals', is_array($review->goals) ? implode("\n", $review->goals) : '') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('panel.hr.performance.index') }}"
               class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-3 rounded-xl shadow-sm transition-colors">
                Batal
            </a>
            <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-3 rounded-xl shadow-sm transition-colors">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
function reviewForm() {
    return {
        scores: {
            attendance: {{ $review->scores['attendance'] ?? 3 }},
            punctuality: {{ $review->scores['punctuality'] ?? 3 }},
            quality: {{ $review->scores['quality'] ?? 3 }},
            teamwork: {{ $review->scores['teamwork'] ?? 3 }},
            leadership: {{ $review->scores['leadership'] ?? 3 }},
        }
    };
}
</script>

@endsection
