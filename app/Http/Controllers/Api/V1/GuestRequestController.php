<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GuestRequest;
use Illuminate\Http\Request;

class GuestRequestController extends Controller
{
    public function index(Request $request)
    {
        $property = $request->user()->property;
        $query = GuestRequest::where('property_id', $property->id)
            ->with(['guest', 'room', 'assignee', 'reservation'])
            ->orderByDesc('opened_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(25));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_id'      => 'nullable|exists:guests,id',
            'reservation_id'=> 'nullable|exists:reservations,id',
            'room_id'       => 'nullable|exists:rooms,id',
            'category'      => 'required|string',
            'description'   => 'required|string',
            'priority'      => 'in:low,normal,high,urgent',
        ]);

        $request = GuestRequest::create([
            ...$data,
            'property_id' => $request->user()->property->id,
            'status'      => 'open',
            'opened_at'   => now(),
        ]);

        return response()->json($request, 201);
    }

    public function update(Request $request, int $id)
    {
        $property = $request->user()->property;
        $guestRequest = GuestRequest::where('property_id', $property->id)->findOrFail($id);
        $data = $request->validate([
            'assignee_id' => 'nullable|exists:users,id',
            'status'      => 'in:open,in_progress,resolved,cancelled',
            'priority'    => 'in:low,normal,high,urgent',
        ]);
        $guestRequest->update($data);
        return response()->json($guestRequest);
    }

    public function resolve(Request $request, int $id)
    {
        $property = $request->user()->property;
        $guestRequest = GuestRequest::where('property_id', $property->id)->findOrFail($id);
        $guestRequest->markResolved($request->notes);
        return response()->json($guestRequest);
    }
}
