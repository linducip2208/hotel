<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Hr;

use App\Http\Controllers\Controller;
use App\Models\PerformanceReview;
use App\Models\Employee;
use Illuminate\Http\Request;

final class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $reviews = PerformanceReview::where('property_id', app('current_property')->id)
            ->with('employee', 'reviewer')
            ->orderByDesc('review_date')->paginate(50);

        return view('panel.hr.performance.index', compact('reviews'));
    }

    public function create(Request $request)
    {
        $employees = Employee::where('property_id', app('current_property')->id)
            ->where('is_active', true)->orderBy('first_name')->get();

        return view('panel.hr.performance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'review_date' => 'required|date',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'scores' => 'required|array',
            'scores.*' => 'numeric|between:1,5',
            'strengths' => 'nullable|string',
            'improvements' => 'nullable|string',
            'overall_rating' => 'required|integer|between:1,5',
            'goals' => 'nullable|array',
        ]);

        PerformanceReview::create([
            'property_id' => app('current_property')->id,
            'employee_id' => $data['employee_id'],
            'reviewer_id' => $request->user()?->id,
            'review_date' => $data['review_date'],
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'scores' => $data['scores'],
            'strengths' => $data['strengths'],
            'improvements' => $data['improvements'],
            'overall_rating' => $data['overall_rating'],
            'goals' => $data['goals'] ?? [],
            'status' => 'completed',
        ]);

        return redirect()->route('panel.hr.performance.index')->with('success', 'Review created.');
    }

    public function show(int $id)
    {
        $review = PerformanceReview::where('property_id', app('current_property')->id)
            ->with('employee', 'reviewer')->findOrFail($id);

        return view('panel.hr.performance.show', compact('review'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'period' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($request->input('employee_id'));
        $start = now()->subMonths(6);

        $attendanceCount = \App\Models\AttendanceLog::where('employee_id', $employee->id)
            ->where('date', '>=', $start)->count();
        $presentCount = \App\Models\AttendanceLog::where('employee_id', $employee->id)
            ->where('date', '>=', $start)->where('status', 'present')->count();
        $lateCount = \App\Models\AttendanceLog::where('employee_id', $employee->id)
            ->where('date', '>=', $start)->where('status', 'late')->count();

        $attendanceScore = $attendanceCount > 0 ? round(($presentCount / $attendanceCount) * 5, 1) : 3;
        $punctualityScore = $attendanceCount > 0
            ? max(1, 5 - round(($lateCount / max($attendanceCount, 1)) * 4, 1))
            : 3;

        return response()->json([
            'employee' => $employee->full_name,
            'period' => $start->toDateString() . ' to ' . now()->toDateString(),
            'scores' => [
                'attendance' => $attendanceScore,
                'punctuality' => $punctualityScore,
                'quality' => 3,
                'teamwork' => 3,
                'leadership' => 3,
            ],
            'overall_rating' => 3,
        ]);
    }

    public function edit(int $id)
    {
        $review = PerformanceReview::where('property_id', app('current_property')->id)
            ->with('employee', 'reviewer')->findOrFail($id);
        $employees = Employee::where('property_id', app('current_property')->id)
            ->where('is_active', true)->orderBy('first_name')->get();

        return view('panel.hr.performance.edit', compact('review', 'employees'));
    }

    public function update(Request $request, int $id)
    {
        $review = PerformanceReview::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'review_date' => 'required|date',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'scores' => 'required|array',
            'scores.*' => 'numeric|between:1,5',
            'strengths' => 'nullable|string',
            'improvements' => 'nullable|string',
            'overall_rating' => 'required|integer|between:1,5',
            'goals' => 'nullable|array',
        ]);

        $review->update($data);
        return redirect()->route('panel.hr.performance.index')->with('success', 'Review updated.');
    }

    public function destroy(int $id)
    {
        $review = PerformanceReview::where('property_id', app('current_property')->id)->findOrFail($id);
        $review->delete();
        return redirect()->route('panel.hr.performance.index')->with('success', 'Review deleted.');
    }

    public function acknowledge(Request $request, int $id)
    {
        $review = PerformanceReview::where('property_id', app('current_property')->id)->findOrFail($id);

        if ($review->status !== 'completed') {
            return back()->with('error', 'Review must be completed first.');
        }

        $review->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
        ]);

        return back()->with('success', 'Review acknowledged.');
    }
}
