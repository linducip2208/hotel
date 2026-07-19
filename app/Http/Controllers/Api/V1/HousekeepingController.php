<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HkTask;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function rooms(Request $request)
    {
        $query = Room::where('property_id', $this->property()->id)
            ->with('roomType')
            ->when($request->query('hk_status'), fn ($q, $s) => $q->where('hk_status', $s))
            ->when($request->query('floor'), fn ($q, $f) => $q->where('floor', $f))
            ->orderBy('number');

        return response()->json($query->get());
    }

    public function createTask(Request $request)
    {
        $validated = $request->validate([
            'room_id'        => 'required|integer|exists:rooms,id',
            'type'           => 'required|in:checkout,stayover,turndown,deep_clean,maintenance',
            'priority'       => 'nullable|in:low,normal,high,urgent',
            'scheduled_date' => 'required|date',
            'notes'          => 'nullable|string|max:500',
            'assigned_to'    => 'nullable|integer|exists:users,id',
        ]);

        $validated['property_id'] = $this->property()->id;

        return response()->json(HkTask::create($validated), 201);
    }

    public function updateTaskStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,done,skipped',
        ]);

        $task = HkTask::where('property_id', $this->property()->id)->findOrFail($id);
        $task->update($validated);

        if ($task->status === 'done') {
            $task->update(['completed_at' => now()]);
        }

        return response()->json($task->fresh());
    }
}
