<?php

namespace App\Http\Controllers\Panel\Hk;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HkTask;
use App\Services\Hk\TaskAutoAssignmentService;
use Illuminate\Http\Request;

class AutoAssignController extends Controller
{
    public function index(TaskAutoAssignmentService $service)
    {
        $property = app('current_property');

        $workload = $service->getWorkloadSummary($property);

        $unassigned = HkTask::where('property_id', $property->id)
            ->where('status', 'pending')
            ->whereDate('scheduled_date', now()->toDateString())
            ->whereNull('assignee_id')
            ->with('room')
            ->orderBy('priority', 'desc')
            ->get();

        $attendants = Employee::where('property_id', $property->id)
            ->where('department', 'housekeeping')
            ->where('is_active', true)
            ->get();

        return view('panel.hk.auto-assign', compact('workload', 'unassigned', 'attendants'));
    }

    public function generate(TaskAutoAssignmentService $service)
    {
        $property = app('current_property');
        $count = $service->createCheckoutTasks($property);

        return back()->with('success', "{$count} tugas cleaning dibuat dari kamar checkout.");
    }

    public function assign(TaskAutoAssignmentService $service)
    {
        $property = app('current_property');
        $result = $service->autoAssign($property);

        return back()->with('success', "{$result['assigned']} dari {$result['total']} tugas berhasil di-assign.");
    }

    public function reassign(Request $request, HkTask $task)
    {
        $request->validate([
            'assignee_id' => 'required|integer|exists:employees,id',
        ]);

        $task->update([
            'assignee_id' => $request->assignee_id,
            'status' => 'assigned',
        ]);

        return back()->with('success', 'Tugas berhasil di-reassign.');
    }
}
