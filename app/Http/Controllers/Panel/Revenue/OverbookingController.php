<?php

namespace App\Http\Controllers\Panel\Revenue;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Services\Revenue\OverbookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OverbookingController extends Controller
{
    protected OverbookingService $service;

    public function __construct(OverbookingService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $property = app('current_property');
        $date = $request->filled('date') ? Carbon::parse($request->input('date')) : now();

        $risks = $this->service->getOverbookingRisk($property, $date);
        $mitigations = $this->service->suggestMitigation($risks);

        $stats = [
            'total_rooms' => collect($risks)->sum('total'),
            'total_booked' => collect($risks)->sum('booked'),
            'total_blocked' => collect($risks)->sum('blocked'),
            'overall_occupancy' => collect($risks)->count() > 0
                ? round(collect($risks)->avg('occupancy_pct'), 1)
                : 0,
        ];

        $criticalCount = collect($risks)->where('risk_level', 'critical')->count();
        $highCount = collect($risks)->where('risk_level', 'high')->count();

        return view('panel.revenue.overbooking', compact('date', 'risks', 'mitigations', 'stats', 'criticalCount', 'highCount'));
    }
}
