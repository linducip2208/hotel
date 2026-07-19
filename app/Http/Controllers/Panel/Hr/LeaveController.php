<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Hr;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

final class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $leaves = LeaveRequest::where('property_id', app('current_property')->id)
            ->with('employee', 'approver')
            ->orderByDesc('created_at')->paginate(50);

        return view('panel.hr.leave.index', compact('leaves'));
    }

    public function create()
    {
        $employees = Employee::where('property_id', app('current_property')->id)
            ->where('is_active', true)->orderBy('first_name')->get();

        return view('panel.hr.leave.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'type' => 'required|in:annual,sick,maternity,paternity,unpaid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $days = $start->diffInDays($end) + 1;

        LeaveRequest::create([
            'property_id' => app('current_property')->id,
            'employee_id' => $data['employee_id'],
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('panel.hr.leave.index')->with('success', 'Leave request submitted.');
    }

    public function approve(Request $request, int $id)
    {
        $leave = LeaveRequest::where('property_id', app('current_property')->id)->findOrFail($id);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Leave request is not pending.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        // Update leave balance
        $year = (int) $leave->start_date->format('Y');
        $balance = LeaveBalance::firstOrCreate(
            ['property_id' => app('current_property')->id, 'employee_id' => $leave->employee_id, 'year' => $year],
            ['total_annual' => 12, 'used_annual' => 0, 'total_sick' => 12, 'used_sick' => 0]
        );

        if ($leave->type === 'annual') {
            $balance->increment('used_annual', $leave->total_days);
        } elseif ($leave->type === 'sick') {
            $balance->increment('used_sick', $leave->total_days);
        }

        return back()->with('success', 'Leave approved.');
    }

    public function reject(Request $request, int $id)
    {
        $leave = LeaveRequest::where('property_id', app('current_property')->id)->findOrFail($id);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Leave request is not pending.');
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $request->user()?->id,
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', 'Leave rejected.');
    }

    public function destroy(int $id)
    {
        $leave = LeaveRequest::where('property_id', app('current_property')->id)->findOrFail($id);
        $leave->delete();
        return back()->with('success', 'Leave request deleted.');
    }

    public function balance(Request $request, int $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $year = (int) $request->query('year', now()->year);

        $balance = LeaveBalance::firstOrCreate(
            ['property_id' => app('current_property')->id, 'employee_id' => $employeeId, 'year' => $year],
            ['total_annual' => 12, 'used_annual' => 0, 'total_sick' => 12, 'used_sick' => 0]
        );

        return view('panel.hr.leave.balance', compact('employee', 'balance', 'year'));
    }
}
