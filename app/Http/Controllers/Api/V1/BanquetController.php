<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FunctionRoom;
use App\Models\Property;
use App\Services\Banquet\EventService;
use Illuminate\Http\Request;

class BanquetController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function functionRooms()
    {
        return response()->json(
            FunctionRoom::where('property_id', $this->property()->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
        );
    }

    public function events(Request $request)
    {
        $query = Event::where('property_id', $this->property()->id)
            ->with('functionRoom')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('date'), fn ($q, $d) => $q->whereDate('event_date', $d));

        return response()->json($query->paginate(50));
    }

    public function showEvent(int $id)
    {
        return response()->json(
            Event::where('property_id', $this->property()->id)
                ->with('functionRoom', 'menuItems')
                ->findOrFail($id)
        );
    }

    public function storeEvent(Request $request, EventService $svc)
    {
        $validated = $request->validate([
            'function_room_id' => 'required|integer|exists:function_rooms,id',
            'client_name'      => 'required|string|max:191',
            'event_date'       => 'required|date',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'pax'              => 'required|integer|min:1',
            'type'             => 'nullable|string|max:50',
            'notes'            => 'nullable|string',
        ]);

        $validated['property_id'] = $this->property()->id;

        return response()->json($svc->create($validated), 201);
    }

    public function addMenu(Request $request, int $id, EventService $svc)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:191',
            'quantity'  => 'required|integer|min:1',
            'unit_price'=> 'required|numeric|min:0',
            'notes'     => 'nullable|string|max:255',
        ]);

        $event = Event::where('property_id', $this->property()->id)->findOrFail($id);

        return response()->json($svc->addMenuItem($event, $validated), 201);
    }

    public function beo(int $id, EventService $svc)
    {
        $event = Event::where('property_id', $this->property()->id)
            ->with('functionRoom', 'menuItems')
            ->findOrFail($id);

        return response()->json($svc->generateBeo($event));
    }
}
