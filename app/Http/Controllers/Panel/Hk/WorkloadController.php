<?php

namespace App\Http\Controllers\Panel\Hk;

use App\Http\Controllers\Controller;
use App\Services\Hk\WorkloadForecastService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkloadController extends Controller
{
    public function index(Request $request, WorkloadForecastService $service)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->query('date'))
            : now()->addDay();

        $forecast = $service->forecastForDate($date, app('current_property')->id);
        $assignments = session('workload_assignments', []);

        return view('panel.hk.workload', compact('forecast', 'date', 'assignments'));
    }

    public function assign(Request $request, WorkloadForecastService $service)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'attendants' => 'required|integer|min:1|max:50',
        ]);

        $date = Carbon::parse($data['date']);
        $assignments = $service->generateAssignment(
            app('current_property')->id,
            $date,
            (int) $data['attendants']
        );

        session()->flash('workload_assignments', $assignments);

        return back()->with('success', 'Assignments generated for ' . count($assignments) . ' attendants.');
    }
}
