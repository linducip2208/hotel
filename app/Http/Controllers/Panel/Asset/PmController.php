<?php

namespace App\Http\Controllers\Panel\Asset;

use App\Http\Controllers\Controller;
use App\Models\PmSchedule;
use App\Models\Vendor;
use App\Services\Asset\PmService;
use Illuminate\Http\Request;

class PmController extends Controller
{
    public function __construct(
        protected PmService $svc
    ) {}

    public function index()
    {
        $property = app('current_property');

        $dueThisWeek = $this->svc->getDueTasks($property);
        $allSchedules = PmSchedule::where('property_id', $property->id)
            ->with(['asset', 'vendor'])
            ->orderBy('next_due_at')
            ->paginate(50);
        $overdueCount = $this->svc->getOverdueCount($property);
        $history = $this->svc->getHistory($property, 20);
        $vendors = Vendor::where('property_id', $property->id)
            ->where('is_active', true)->get();

        return view('panel.asset.pm-schedule', compact(
            'dueThisWeek', 'allSchedules', 'overdueCount', 'history', 'vendors'
        ));
    }

    public function schedule()
    {
        $property = app('current_property');
        $created = $this->svc->autoSchedule($property);

        return back()->with('success', "{$created} jadwal PM berhasil dibuat otomatis.");
    }

    public function complete(Request $request, int $id)
    {
        $schedule = PmSchedule::where('property_id', app('current_property')->id)->findOrFail($id);

        $results = $request->input('checklist', []);
        $notes = $request->input('notes');
        $cost = $request->input('cost', 0);

        $this->svc->completeTask($schedule, $results, $request->user()->id, $cost, $notes);

        return back()->with('success', "PM task '{$schedule->task_name}' selesai.");
    }

    public function history(Request $request)
    {
        $property = app('current_property');
        $history = $this->svc->getHistory($property, 100);

        return view('panel.asset.pm-schedule', compact('history'));
    }

    public function toggle(int $id)
    {
        $schedule = PmSchedule::where('property_id', app('current_property')->id)->findOrFail($id);
        $schedule->update(['is_active' => !$schedule->is_active]);

        return back()->with('success', 'Status jadwal diubah.');
    }
}
