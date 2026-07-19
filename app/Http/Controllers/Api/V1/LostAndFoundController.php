<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LostAndFound;
use Illuminate\Http\Request;

class LostAndFoundController extends Controller
{
    public function index(Request $request)
    {
        $property = $request->user()->property;

        $query = LostAndFound::where('property_id', $property->id)
            ->with(['room', 'foundByUser', 'claimedByGuest'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('location'), fn ($q, $l) => $q->where('found_location', 'like', "%{$l}%"))
            ->when($request->query('date_from'), fn ($q, $d) => $q->whereDate('found_date', '>=', $d))
            ->when($request->query('date_to'), fn ($q, $d) => $q->whereDate('found_date', '<=', $d))
            ->orderByDesc('found_date');

        return response()->json($query->paginate(25));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description'    => 'required|string|max:500',
            'found_location' => 'nullable|string|max:200',
            'found_date'     => 'required|date',
            'room_id'        => 'nullable|exists:rooms,id',
            'photo_path'     => 'nullable|string',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $item = LostAndFound::create([
            ...$data,
            'property_id'      => $request->user()->property->id,
            'found_by_user_id' => $request->user()->id,
            'status'           => 'found',
        ]);

        return response()->json($item, 201);
    }

    public function show(Request $request, int $id)
    {
        $item = LostAndFound::where('property_id', $request->user()->property->id)
            ->with(['room', 'foundByUser', 'claimedByGuest'])
            ->findOrFail($id);

        return response()->json($item);
    }

    public function update(Request $request, int $id)
    {
        $item = LostAndFound::where('property_id', $request->user()->property->id)->findOrFail($id);

        $data = $request->validate([
            'description'    => 'sometimes|string|max:500',
            'found_location' => 'nullable|string|max:200',
            'found_date'     => 'sometimes|date',
            'room_id'        => 'nullable|exists:rooms,id',
            'photo_path'     => 'nullable|string',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $item->update($data);

        return response()->json($item);
    }

    public function claim(Request $request, int $id)
    {
        $item = LostAndFound::where('property_id', $request->user()->property->id)->findOrFail($id);

        $data = $request->validate([
            'claimed_by_guest_id' => 'nullable|exists:guests,id',
            'notes'               => 'nullable|string|max:500',
        ]);

        $item->update([
            'status'              => 'claimed',
            'claimed_by_guest_id' => $data['claimed_by_guest_id'] ?? null,
            'claimed_date'        => now(),
            'notes'               => $data['notes'] ?? $item->notes,
        ]);

        return response()->json($item);
    }

    public function dispose(Request $request, int $id)
    {
        $item = LostAndFound::where('property_id', $request->user()->property->id)->findOrFail($id);

        $item->update(['status' => 'disposed']);

        return response()->json($item);
    }
}
