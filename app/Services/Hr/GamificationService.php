<?php

namespace App\Services\Hr;

use App\Models\Employee;
use App\Models\EmployeeBadge;
use App\Models\EmployeePoint;
use App\Models\GamificationBadge;
use App\Models\HkTask;
use App\Models\InspectionChecklist;
use App\Models\Property;

class GamificationService
{
    public function awardPoints(Employee $employee, int $points, string $reason, string $category): void
    {
        EmployeePoint::create([
            'property_id' => $employee->property_id,
            'employee_id' => $employee->id,
            'points' => $points,
            'reason' => $reason,
            'category' => $category,
            'earned_at' => now(),
        ]);

        $this->checkBadges($employee);
    }

    protected function checkBadges(Employee $employee): void
    {
        $badges = GamificationBadge::where('property_id', $employee->property_id)
            ->whereIn('category', [$employee->department, 'all'])->get();

        foreach ($badges as $badge) {
            $alreadyAwarded = EmployeeBadge::where('employee_id', $employee->id)
                ->where('gamification_badge_id', $badge->id)->exists();
            if ($alreadyAwarded) continue;

            $count = match ($badge->criteria) {
                'rooms_cleaned' => HkTask::where('assignee_id', $employee->id)->where('status', 'done')->count(),
                'perfect_scores' => InspectionChecklist::where('inspector_id', $employee->id)->where('score', 100)->count(),
                'total_points' => EmployeePoint::where('employee_id', $employee->id)->sum('points'),
                default => 0,
            };

            if ($count >= $badge->threshold) {
                EmployeeBadge::create([
                    'property_id' => $employee->property_id,
                    'employee_id' => $employee->id,
                    'gamification_badge_id' => $badge->id,
                    'awarded_at' => now(),
                ]);
            }
        }
    }

    public function getLeaderboard(Property $property, string $period = 'monthly'): array
    {
        $from = match ($period) {
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $points = EmployeePoint::where('property_id', $property->id)
            ->where('earned_at', '>=', $from)
            ->selectRaw('employee_id, SUM(points) as total_points')
            ->groupBy('employee_id')
            ->orderByDesc('total_points')
            ->with(['employee' => function ($q) {
                $q->where('is_active', true);
            }])
            ->limit(20)->get();

        $leaderboard = [];
        $rank = 1;
        foreach ($points as $p) {
            if (!$p->employee) continue;
            $badges = EmployeeBadge::where('employee_id', $p->employee_id)
                ->with('badge')->get();
            $leaderboard[] = [
                'rank' => $rank++,
                'name' => $p->employee->full_name,
                'department' => $p->employee->department ?? '',
                'points' => $p->total_points,
                'badges' => $badges->pluck('badge.name')->toArray(),
            ];
        }
        return $leaderboard;
    }

    public function getTopPerformers(Property $property): array
    {
        return $this->getLeaderboard($property, 'monthly');
    }

    public function getEmployeeStats(Employee $employee): array
    {
        $totalPoints = EmployeePoint::where('employee_id', $employee->id)->sum('points');
        $monthlyPoints = EmployeePoint::where('employee_id', $employee->id)
            ->where('earned_at', '>=', now()->startOfMonth())->sum('points');
        $badges = EmployeeBadge::where('employee_id', $employee->id)
            ->with('badge')->get();
        $recentPoints = EmployeePoint::where('employee_id', $employee->id)
            ->orderByDesc('earned_at')->limit(10)->get();

        return [
            'total_points' => $totalPoints,
            'monthly_points' => $monthlyPoints,
            'badge_count' => $badges->count(),
            'badges' => $badges,
            'recent_points' => $recentPoints,
        ];
    }
}
