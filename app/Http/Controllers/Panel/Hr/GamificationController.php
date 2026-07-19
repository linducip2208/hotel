<?php

namespace App\Http\Controllers\Panel\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeBadge;
use App\Models\EmployeePoint;
use App\Models\GamificationBadge;
use App\Services\Hr\GamificationService;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    public function __construct(
        protected GamificationService $svc
    ) {}

    public function index(Request $request)
    {
        $period = $request->query('period', 'monthly');
        $property = app('current_property');

        $leaderboard = $this->svc->getLeaderboard($property, $period);
        $badges = GamificationBadge::where('property_id', $property->id)->get();
        $recentAwards = EmployeeBadge::where('property_id', $property->id)
            ->with(['employee', 'badge'])
            ->orderByDesc('awarded_at')
            ->limit(20)->get();
        $recentPoints = EmployeePoint::where('property_id', $property->id)
            ->with('employee')
            ->orderByDesc('earned_at')
            ->limit(20)->get();

        return view('panel.hr.gamification', compact(
            'leaderboard', 'badges', 'recentAwards', 'recentPoints', 'period'
        ));
    }

    public function create()
    {
        $property = app('current_property');
        $badges = GamificationBadge::where('property_id', $property->id)->get();
        return view('panel.hr.gamification', compact('badges'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'icon' => 'required|string|in:star,fire,crown,bolt,heart',
            'color' => 'required|string|in:amber,emerald,violet,rose,sky',
            'category' => 'required|string|in:hk,fo,all',
            'criteria' => 'required|string|in:rooms_cleaned,checkins_handled,perfect_scores,streak,total_points',
            'threshold' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        GamificationBadge::create($data + [
            'property_id' => app('current_property')->id,
        ]);

        return back()->with('success', 'Lencana berhasil dibuat.');
    }

    public function leaderboard(Request $request)
    {
        $period = $request->query('period', 'monthly');
        $property = app('current_property');
        $leaderboard = $this->svc->getLeaderboard($property, $period);

        return response()->json($leaderboard);
    }

    public function employeeStats(int $id)
    {
        $employee = Employee::where('property_id', app('current_property')->id)->findOrFail($id);
        $stats = $this->svc->getEmployeeStats($employee);

        return response()->json($stats);
    }

    public function awardPoints(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'points' => 'required|integer|min:1',
            'reason' => 'required|string',
            'category' => 'required|string',
        ]);

        $employee = Employee::where('property_id', app('current_property')->id)->findOrFail($data['employee_id']);
        $this->svc->awardPoints($employee, $data['points'], $data['reason'], $data['category']);

        return back()->with('success', 'Poin diberikan kepada '.$employee->full_name.'.');
    }

    public function destroyBadge(int $id)
    {
        $badge = GamificationBadge::where('property_id', app('current_property')->id)->findOrFail($id);
        $badge->delete();

        return back()->with('success', 'Lencana dihapus.');
    }
}
