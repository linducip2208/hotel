<?php

namespace App\Services\Hk;

use App\Models\HkTask;
use App\Models\Room;
use App\Models\Employee;
use App\Models\Property;
use Carbon\Carbon;

class TaskAutoAssignmentService
{
    public function createCheckoutTasks(Property $property): int
    {
        $checkedOutRooms = Room::where('property_id', $property->id)
            ->where('fo_status', 'checked_out')
            ->where('hk_status', '!=', 'cleaning')
            ->get();

        $count = 0;
        foreach ($checkedOutRooms as $room) {
            $exists = HkTask::where('property_id', $property->id)
                ->where('room_id', $room->id)
                ->whereDate('scheduled_date', now()->toDateString())
                ->whereIn('status', ['pending', 'in_progress'])
                ->exists();
            if ($exists) continue;

            HkTask::create([
                'property_id' => $property->id,
                'room_id' => $room->id,
                'type' => 'cleaning',
                'priority' => $this->determinePriority($room),
                'status' => 'pending',
                'scheduled_date' => now()->toDateString(),
                'duration' => 30,
                'notes' => 'Auto-generated: checkout cleaning',
            ]);

            $room->update(['hk_status' => 'cleaning']);
            $count++;
        }

        return $count;
    }

    protected function determinePriority(Room $room): string
    {
        if ($room->is_vip ?? false) return 'high';

        $tomorrowArrival = \App\Models\Reservation::whereHas('rooms', function ($q) use ($room) {
            $q->where('room_id', $room->id);
        })->whereDate('check_in', now()->addDay()->toDateString())->exists();

        if ($tomorrowArrival) return 'high';

        return 'medium';
    }

    public function autoAssign(Property $property): array
    {
        $tasks = HkTask::where('property_id', $property->id)
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

        if ($tasks->isEmpty() || $attendants->isEmpty()) return ['assigned' => 0, 'total' => $tasks->count()];

        $workload = [];
        foreach ($attendants as $attendant) {
            $workload[$attendant->id] = HkTask::where('assignee_id', $attendant->id)
                ->whereIn('status', ['pending', 'in_progress', 'assigned'])
                ->count();
        }

        $assigned = 0;
        foreach ($tasks as $task) {
            $taskFloor = $task->room->floor ?? 0;

            $bestAttendant = null;
            $bestScore = PHP_INT_MAX;

            foreach ($attendants as $attendant) {
                $attendantFloor = $attendant->default_floor ?? 0;
                $floorBonus = ($taskFloor === $attendantFloor) ? -2 : 0;
                $score = $workload[$attendant->id] + $floorBonus;

                if ($score < $bestScore) {
                    $bestScore = $score;
                    $bestAttendant = $attendant->id;
                }
            }

            if ($bestAttendant) {
                $task->update(['assignee_id' => $bestAttendant, 'status' => 'assigned']);
                $workload[$bestAttendant]++;
                $assigned++;
            }
        }

        return ['assigned' => $assigned, 'total' => $tasks->count()];
    }

    public function getWorkloadSummary(Property $property): array
    {
        $attendants = Employee::where('property_id', $property->id)
            ->where('department', 'housekeeping')
            ->where('is_active', true)
            ->get();

        $summary = [];
        foreach ($attendants as $attendant) {
            $pending = HkTask::where('assignee_id', $attendant->id)
                ->whereIn('status', ['pending', 'assigned'])->count();
            $inProgress = HkTask::where('assignee_id', $attendant->id)
                ->where('status', 'in_progress')->count();
            $completed = HkTask::where('assignee_id', $attendant->id)
                ->where('status', 'done')
                ->whereDate('updated_at', now()->toDateString())->count();
            $summary[] = [
                'id' => $attendant->id,
                'name' => $attendant->full_name,
                'pending' => $pending,
                'in_progress' => $inProgress,
                'completed' => $completed,
                'total' => $pending + $inProgress,
            ];
        }
        return $summary;
    }
}
