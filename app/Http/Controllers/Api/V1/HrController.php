<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Property;
use App\Services\Hr\PayrollService;
use Illuminate\Http\Request;

class HrController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function employees()
    {
        return response()->json(
            Employee::where('property_id', $this->property()->id)->paginate(50)
        );
    }

    public function showEmployee(int $id)
    {
        $employee = Employee::where('property_id', $this->property()->id)
            ->with('attendance', 'payslips')
            ->findOrFail($id);

        return response()->json($employee);
    }

    public function clockIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'date'        => 'required|date',
            'clock_in'    => 'nullable|date_format:H:i:s',
            'clock_out'   => 'nullable|date_format:H:i:s',
            'status'      => 'nullable|in:present,absent,sick,leave,holiday,late',
            'notes'       => 'nullable|string|max:500',
        ]);

        Employee::where('property_id', $this->property()->id)
            ->findOrFail($validated['employee_id']);

        $log = AttendanceLog::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            $validated
        );

        return response()->json($log);
    }

    public function payslips(Request $request)
    {
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        return response()->json(
            Payslip::with('employee')
                ->whereHas('employee', fn ($q) => $q->where('property_id', $this->property()->id))
                ->where('year', $year)
                ->where('month', $month)
                ->paginate(50)
        );
    }

    public function generatePayroll(Request $request, PayrollService $svc)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'year'        => 'nullable|integer|min:2000|max:2100',
            'month'       => 'nullable|integer|between:1,12',
        ]);

        $employee = Employee::where('property_id', $this->property()->id)
            ->findOrFail($validated['employee_id']);

        return response()->json($svc->generatePayslip(
            $employee,
            (int) ($validated['year'] ?? now()->year),
            (int) ($validated['month'] ?? now()->month)
        ));
    }
}
