@extends('panel.layout')
@section('title', 'New Performance Review')
@section('content')

<div class="max-w-2xl" x-data="reviewForm()">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">New Performance Review</h1>

    <form method="POST" action="{{ route('panel.hr.performance.store') }}" class="bg-white rounded-2xl shadow-card border border-gray-100 p-6 space-y-5">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Employee <span class="text-red-500">*</span></label>
                <select name="employee_id" x-model="employeeId" x-on:change="generateScores()" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
                    <option value="">Select employee</option>
                    @foreach ($employees as $e)
                    <option value="{{ $e->id }}">{{ $e->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Review Date <span class="text-red-500">*</span></label>
                <input type="date" name="review_date" required value="{{ now()->toDateString() }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Period Start <span class="text-red-500">*</span></label>
                <input type="date" name="period_start" required value="{{ now()->subMonths(6)->toDateString() }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Period End <span class="text-red-500">*</span></label>
                <input type="date" name="period_end" required value="{{ now()->toDateString() }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
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
            <select name="overall_rating" required x-model="overallRating" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm">
                @for($i=5;$i>=1;$i--)
                <option value="{{ $i }}">{{ $i }} — {{ ['','Needs Improvement','Fair','Good','Excellent','Outstanding'][$i] }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Strengths</label>
            <textarea name="strengths" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm"></textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Areas for Improvement</label>
            <textarea name="improvements" rows="2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm"></textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Goals (one per line)</label>
            <textarea name="goals" rows="3" placeholder="Goal 1&#10;Goal 2" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm"></textarea>
        </div>

        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold py-3 rounded-xl shadow-sm transition-colors">
            Save Review
        </button>
    </form>
</div>

<script>
function reviewForm() {
    return {
        employeeId: '',
        scores: { attendance: 3, punctuality: 3, quality: 3, teamwork: 3, leadership: 3 },
        overallRating: 3,
        async generateScores() {
            if (!this.employeeId) return;
            try {
                const res = await fetch('/panel/hr/performance/generate?employee_id=' + this.employeeId);
                const data = await res.json();
                if (data.scores) {
                    this.scores = data.scores;
                    this.overallRating = data.overall_rating;
                }
            } catch(e) {}
        }
    };
}
</script>

@endsection
