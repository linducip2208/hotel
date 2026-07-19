<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\SpaAppointment;
use App\Models\SpaTreatment;
use App\Services\Spa\SpaService;
use Illuminate\Http\Request;

class SpaController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function treatments()
    {
        return response()->json(
            SpaTreatment::where('property_id', $this->property()->id)
                ->where('is_active', true)
                ->orderBy('display_order')
                ->get()
        );
    }

    public function appointments(Request $request)
    {
        $query = SpaAppointment::where('property_id', $this->property()->id)
            ->with('treatment', 'therapist', 'cabin', 'guest')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('date'), fn ($q, $d) => $q->whereDate('scheduled_at', $d));

        return response()->json($query->paginate(50));
    }

    public function book(Request $request, SpaService $svc)
    {
        $validated = $request->validate([
            'treatment_id' => 'required|integer|exists:spa_treatments,id',
            'guest_id'     => 'nullable|integer|exists:guests,id',
            'scheduled_at' => 'required|date',
            'therapist_id' => 'nullable|integer|exists:employees,id',
            'cabin_id'     => 'nullable|integer',
            'notes'        => 'nullable|string|max:500',
        ]);

        return response()->json($svc->book($validated), 201);
    }

    public function complete(int $id, SpaService $svc)
    {
        $appointment = SpaAppointment::where('property_id', $this->property()->id)->findOrFail($id);

        $svc->complete($appointment);

        return response()->json($appointment->fresh());
    }
}
