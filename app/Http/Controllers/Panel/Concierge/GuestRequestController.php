<?php

namespace App\Http\Controllers\Panel\Concierge;

use App\Http\Controllers\Controller;
use App\Models\GuestRequest;
use Illuminate\Http\Request;

class GuestRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = GuestRequest::where('property_id', app('current_property')->id)
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->with('guest', 'room', 'assignee')->latest('opened_at')->paginate(50);
        return view('panel.concierge.requests', compact('requests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => 'nullable|integer',
            'guest_id' => 'nullable|integer',
            'room_id' => 'nullable|integer',
            'category' => 'required|in:amenity,housekeeping,maintenance,fnb,concierge,complaint,other',
            'subject' => 'required|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ]);
        GuestRequest::create($data + ['property_id' => app('current_property')->id, 'status' => 'open']);
        return back();
    }

    public function update(Request $request, int $id)
    {
        $r = GuestRequest::where('property_id', app('current_property')->id)->findOrFail($id);
        if ($action = $request->input('action')) {
            match ($action) {
                'respond' => $r->markResponded(),
                'resolve' => $r->markResolved($request->input('notes')),
                'assign' => $r->update(['assignee_id' => $request->input('assignee_id'), 'status' => 'in_progress']),
                default => null,
            };
        }
        return back();
    }
}
