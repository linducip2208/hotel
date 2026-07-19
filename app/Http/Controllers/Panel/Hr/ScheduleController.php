<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Hr;

use App\Http\Controllers\Controller;
use App\Models\ShiftSchedule;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

final class ScheduleController extends Controller
{
    public function calendar(Request $request)
    {
        $date = Carbon::parse($request->query('date', now()->toDateString()));
        $view = $request->query('view', 'weekly'); // weekly|monthly

        $days = $view === 'monthly' ? $date->daysInMonth : 7;
        $start = $view === 'monthly' ? $date->copy()->startOfMonth() : $date->copy()->startOfWeek();

        $schedules = ShiftSchedule::where('property_id', app('current_property')->id)
            ->whereBetween('date', [$start->toDateString(), $start->copy()->addDays($days - 1)->toDateString()])
            ->with('employee')
            ->get()
            ->groupBy(fn ($s) => $s->date->toDateString());

        $employees = Employee::where('property_id', app('current_property')->id)
            ->where('is_active', true)->orderBy('first_name')->get();

        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = $start->copy()->addDays($i);
        }

        return view('panel.hr.schedule.calendar', compact('schedules', 'employees', 'dates', 'date', 'view'));
    }

    public function assign(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'date' => 'required|date',
            'shift_type' => 'required|in:morning,afternoon,night,off',
            'department' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        ShiftSchedule::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date' => $data['date']],
            [
                'property_id' => app('current_property')->id,
                'shift_type' => $data['shift_type'],
                'department' => $data['department'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]
        );

        return back()->with('success', 'Shift assigned.');
    }

    public function swap(Request $request)
    {
        $data = $request->validate([
            'employee_1_id' => 'required|integer|exists:employees,id',
            'employee_2_id' => 'required|integer|different:employee_1_id|exists:employees,id',
            'date' => 'required|date',
        ]);

        $s1 = ShiftSchedule::where('employee_id', $data['employee_1_id'])
            ->where('date', $data['date'])->first();
        $s2 = ShiftSchedule::where('employee_id', $data['employee_2_id'])
            ->where('date', $data['date'])->first();

        if ($s1 && $s2) {
            $s1Type = $s1->shift_type;
            $s1->update(['shift_type' => $s2->shift_type, 'employee_id' => $data['employee_2_id']]);
            $s2->update(['shift_type' => $s1Type, 'employee_id' => $data['employee_1_id']]);
        } elseif ($s1) {
            $s1->update(['employee_id' => $data['employee_2_id']]);
        } elseif ($s2) {
            $s2->update(['employee_id' => $data['employee_1_id']]);
        }

        return back()->with('success', 'Shifts swapped.');
    }
}
