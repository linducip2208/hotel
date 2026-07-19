<?php

namespace App\Http\Controllers\Panel\Hr;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\ServiceChargeDistribution;
use App\Services\Hr\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HrController extends Controller
{
    public function index() { return $this->employees(request()); }

    public function employees(Request $request)
    {
        $query = Employee::where('property_id', app('current_property')->id);
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('employee_no', 'like', "%{$search}%");
            });
        }
        $employees = $query->paginate(50);
        return view('panel.hr.employees', compact('employees'));
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'position' => 'required|string',
            'department' => 'required|string',
            'joined_at' => 'required|date',
            'basic_salary' => 'required|numeric',
            'employment_type' => 'nullable|in:permanent,contract,daily,outsource',
        ]);
        Employee::create($data + [
            'property_id' => app('current_property')->id,
            'employee_no' => 'EMP-'.now()->format('Y').'-'.Str::upper(Str::random(5)),
        ]);
        return back();
    }

    public function editEmployee(int $id)
    {
        $employee = Employee::findOrFail($id);
        return view('panel.hr.employee-edit', compact('employee'));
    }

    public function updateEmployee(Request $request, int $id)
    {
        $employee = Employee::findOrFail($id);
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'nik' => 'nullable|string',
            'position' => 'required|string',
            'department' => 'required|string',
            'joined_at' => 'required|date',
            'basic_salary' => 'required|numeric',
            'employment_type' => 'nullable|in:permanent,contract,daily,outsource',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);
        $employee->update($data);
        return redirect()->route('panel.hr.employees')->with('status', 'Employee updated.');
    }

    public function destroyEmployee(int $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return redirect()->route('panel.hr.employees')->with('status', 'Employee deleted.');
    }

    public function showEmployee(int $id)
    {
        $employee = Employee::with('attendance', 'payslips')->findOrFail($id);
        return view('panel.hr.employee-show', compact('employee'));
    }

    public function attendance(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $query = AttendanceLog::with('employee')
            ->whereHas('employee', fn ($q) => $q->where('property_id', app('current_property')->id))
            ->whereDate('date', $date);

        if ($search = $request->query('search')) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(100);
        $employees = Employee::where('property_id', app('current_property')->id)->where('is_active', true)->get();
        return view('panel.hr.attendance', compact('logs', 'date', 'employees'));
    }

    public function clockIn(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,sick,leave,holiday,late',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
        ]);
        AttendanceLog::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date' => $data['date']],
            $data
        );
        return back();
    }

    public function destroyAttendance(int $id)
    {
        $log = AttendanceLog::findOrFail($id);
        $log->delete();
        return back()->with('status', 'Attendance record deleted.');
    }

    public function payroll(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $query = Payslip::with('employee')
            ->whereHas('employee', fn ($q) => $q->where('property_id', app('current_property')->id))
            ->where('year', $year)->where('month', $month);

        if ($search = $request->query('search')) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $payslips = $query->paginate(50);
        return view('panel.hr.payroll', compact('payslips', 'year', 'month'));
    }

    public function generatePayslips(Request $request, PayrollService $svc)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $count = 0;
        Employee::where('property_id', app('current_property')->id)
            ->where('is_active', true)
            ->each(function (Employee $e) use ($svc, $year, $month, &$count) {
                $svc->generatePayslip($e, $year, $month);
                $count++;
            });
        return back()->with('status', "Generated {$count} payslips for {$year}-{$month}.");
    }

    public function showPayslip(int $id)
    {
        $payslip = Payslip::with('employee')->findOrFail($id);
        return view('panel.hr.payslip-show', compact('payslip'));
    }

    public function approvePayslip(int $id)
    {
        $payslip = Payslip::findOrFail($id);
        $payslip->update(['status' => 'approved']);
        return back()->with('status', 'Payslip approved.');
    }

    public function markPayslipPaid(int $id)
    {
        $payslip = Payslip::findOrFail($id);
        $payslip->update(['status' => 'paid', 'paid_at' => now()]);
        return back()->with('status', 'Payslip marked as paid.');
    }

    public function serviceCharge(Request $request)
    {
        $distributions = ServiceChargeDistribution::where('property_id', app('current_property')->id)
            ->orderByDesc('year')->orderByDesc('month')->paginate(50);
        return view('panel.hr.service-charge', compact('distributions'));
    }
}
