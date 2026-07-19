<?php

namespace App\Http\Controllers\Panel\Hk;

use App\Http\Controllers\Controller;
use App\Models\HkTask;
use App\Models\Room;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    public function board()
    {
        $rooms = Room::where('property_id', app('current_property')->id)
            ->orderBy('floor')->orderBy('number')->get();
        return view('panel.hk.board', compact('rooms'));
    }

    public function rooms() { return $this->board(); }

    public function updateStatus(Request $request, int $id)
    {
        $room = Room::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate(['status' => 'required|in:clean,dirty,inspected,out_of_order']);
        $room->update(['hk_status' => $data['status']]);
        return back();
    }

    public function tasks(Request $request)
    {
        $tasks = HkTask::where('property_id', app('current_property')->id)
            ->whereDate('scheduled_date', $request->query('date', now()->toDateString()))
            ->with('room', 'assignee')->paginate(50);
        return view('panel.hk.tasks', compact('tasks'));
    }

    public function storeTask(Request $request)
    {
        $data = $request->validate([
            'room_id' => 'required|integer',
            'type' => 'required|string',
            'priority' => 'nullable|string',
            'assignee_id' => 'nullable|integer',
            'scheduled_date' => 'required|date',
        ]);
        HkTask::create($data + ['property_id' => app('current_property')->id, 'status' => 'pending']);
        return back();
    }

    public function updateTask(Request $request, int $id)
    {
        $task = HkTask::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate(['status' => 'required|in:pending,in_progress,done,skipped']);
        $task->update($data);
        if ($data['status'] === 'done') $task->update(['completed_at' => now()]);
        return back();
    }
}
