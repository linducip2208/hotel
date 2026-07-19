<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function index()
    {
        return response()->json(
            RoomType::where('property_id', $this->property()->id)
                ->with('rooms')
                ->orderBy('display_order')
                ->get()
        );
    }

    public function show(int $id)
    {
        return response()->json(
            RoomType::where('property_id', $this->property()->id)
                ->with('rooms', 'rateOverrides')
                ->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'              => 'required|string|max:20',
            'name'              => 'required|string|max:100',
            'slug'              => 'required|string|max:100',
            'description'       => 'nullable|string',
            'max_occupancy'     => 'integer|min:1|max:20',
            'max_adults'        => 'integer|min:1|max:20',
            'max_children'      => 'integer|min:0|max:10',
            'base_rate'         => 'numeric|min:0',
            'amenities'         => 'nullable|array',
            'photos'            => 'nullable|array',
            'size_sqm'          => 'nullable|integer|min:1',
            'view'              => 'nullable|string|max:100',
            'bed_config'        => 'nullable|string|max:100',
            'smoking'           => 'boolean',
            'display_order'     => 'integer|min:0',
            'is_active'         => 'boolean',
        ]);

        $validated['property_id'] = $this->property()->id;
        return response()->json(RoomType::create($validated), 201);
    }

    public function update(Request $request, int $id)
    {
        $roomType = RoomType::where('property_id', $this->property()->id)->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'sometimes|string|max:100',
            'description'   => 'nullable|string',
            'base_rate'     => 'numeric|min:0',
            'amenities'     => 'nullable|array',
            'photos'        => 'nullable|array',
            'display_order' => 'integer|min:0',
            'is_active'     => 'boolean',
        ]);

        $roomType->update($validated);
        return response()->json($roomType->fresh());
    }

    public function destroy(int $id)
    {
        RoomType::where('property_id', $this->property()->id)->findOrFail($id)->delete();
        return response()->json(['deleted' => true]);
    }

    public function rooms(Request $request)
    {
        $query = Room::where('property_id', $this->property()->id)
            ->with('roomType')
            ->when($request->query('hk_status'), fn ($q, $s) => $q->where('hk_status', $s))
            ->when($request->query('fo_status'), fn ($q, $s) => $q->where('fo_status', $s))
            ->when($request->query('floor'), fn ($q, $f) => $q->where('floor', $f))
            ->orderBy('number');

        return response()->json($query->get());
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'hk_status' => 'nullable|in:clean,dirty,inspected,out_of_order',
            'fo_status' => 'nullable|in:vacant,occupied,reserved',
        ]);

        $room = Room::where('property_id', $this->property()->id)->findOrFail($id);
        $room->update(array_filter($validated, fn ($v) => $v !== null));
        return response()->json($room->fresh());
    }
}
