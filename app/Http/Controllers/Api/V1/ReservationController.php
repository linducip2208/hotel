<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Reservation;
use App\Services\Fo\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(protected ReservationService $svc) {}

    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function index(Request $request)
    {
        $query = Reservation::where('property_id', $this->property()->id);

        if ($s = $request->query('status')) {
            $query->whereIn('status', explode(',', $s));
        }

        return response()->json($query->paginate(min(200, (int) $request->query('limit', 25))));
    }

    public function show(int $id)
    {
        return response()->json(
            Reservation::where('property_id', $this->property()->id)
                ->with('rooms', 'addons', 'folios')
                ->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_id'       => 'nullable|integer|exists:guests,id',
            'room_type_id'   => 'required|integer|exists:room_types,id',
            'check_in'       => 'required|date',
            'check_out'      => 'required|date|after:check_in',
            'adults'         => 'required|integer|min:1',
            'children'       => 'nullable|integer|min:0',
            'rate_plan_id'   => 'nullable|integer|exists:rate_plans,id',
            'source'         => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:1000',
            'guest_first_name' => 'nullable|string|max:100',
            'guest_last_name'  => 'nullable|string|max:100',
            'guest_email'      => 'nullable|email|max:191',
            'guest_phone'      => 'nullable|string|max:30',
        ]);

        $validated['property_id'] = $this->property()->id;

        return response()->json($this->svc->create($validated), 201);
    }

    public function update(Request $request, int $id)
    {
        $reservation = Reservation::where('property_id', $this->property()->id)->findOrFail($id);

        $validated = $request->validate([
            'adults'       => 'sometimes|integer|min:1',
            'children'     => 'nullable|integer|min:0',
            'check_in'     => 'sometimes|date',
            'check_out'    => 'sometimes|date|after:check_in',
            'rate_plan_id' => 'nullable|integer|exists:rate_plans,id',
            'notes'        => 'nullable|string|max:1000',
            'source'       => 'nullable|string|max:50',
        ]);

        $reservation->update($validated);

        return response()->json($reservation->fresh());
    }

    public function destroy(int $id)
    {
        return response()->json(
            Reservation::where('property_id', $this->property()->id)->findOrFail($id)->delete()
        );
    }

    public function cancel(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason'  => 'nullable|string|max:500',
            'penalty' => 'nullable|numeric|min:0',
        ]);

        $reservation = Reservation::where('property_id', $this->property()->id)->findOrFail($id);

        return response()->json($this->svc->cancel(
            $reservation,
            $validated['reason'] ?? '-',
            (float) ($validated['penalty'] ?? 0)
        ));
    }

    public function checkIn(int $id)
    {
        $reservation = Reservation::where('property_id', $this->property()->id)->findOrFail($id);

        return response()->json($this->svc->checkIn($reservation));
    }

    public function checkOut(int $id)
    {
        $reservation = Reservation::where('property_id', $this->property()->id)->findOrFail($id);

        return response()->json($this->svc->checkOut($reservation));
    }

    public function noShow(int $id)
    {
        $reservation = Reservation::where('property_id', $this->property()->id)->findOrFail($id);

        $reservation->update([
            'status'              => 'no_show',
            'cancelled_at'        => now(),
            'cancellation_reason' => 'no_show',
        ]);

        return response()->json($reservation->fresh());
    }

    public function moveRoom(Request $request, int $id)
    {
        $validated = $request->validate([
            'reservation_room_id' => 'required|integer',
            'to_room_id'          => 'required|integer|exists:rooms,id',
        ]);

        $reservation = Reservation::where('property_id', $this->property()->id)->findOrFail($id);

        return response()->json($this->svc->moveRoom(
            $reservation,
            $validated['reservation_room_id'],
            $validated['to_room_id']
        ));
    }
}
