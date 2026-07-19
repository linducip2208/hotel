<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\DailyFlashReport;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MultiPropertyController extends Controller
{
    public function dashboard(Request $request)
    {
        $properties = Property::where('is_active', true)->get();

        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to', now()->toDateString());

        $totalRooms = Room::whereIn('property_id', $properties->pluck('id'))->count();

        $totalRevenue = Reservation::whereIn('property_id', $properties->pluck('id'))
            ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('check_in', [$from, $to])
            ->sum('grand_total');

        $totalGuests = Reservation::whereIn('property_id', $properties->pluck('id'))
            ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('check_in', [$from, $to])
            ->sum(DB::raw('adults + children'));

        $perProperty = [];
        foreach ($properties as $property) {
            $flash = DailyFlashReport::where('property_id', $property->id)
                ->whereBetween('report_date', [$from, $to])
                ->get();

            $propRevenue = Reservation::where('property_id', $property->id)
                ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                ->whereBetween('check_in', [$from, $to])
                ->sum('grand_total');

            $sold = $flash->sum(fn ($r) => ($r->rooms_kpi['sold'] ?? 0));
            $available = $flash->sum(fn ($r) => ($r->rooms_kpi['available'] ?? 0));
            $occPct = $available > 0 ? round(($sold / $available) * 100, 1) : 0;
            $adr = $sold > 0 ? round($flash->sum(fn ($r) => ($r->rooms_kpi['adr'] ?? 0) * ($r->rooms_kpi['sold'] ?? 0)) / $sold, 0) : 0;
            $revpar = $available > 0 ? round($flash->sum(fn ($r) => $r->total_revenue ?? 0) / $available, 0) : 0;

            $perProperty[] = [
                'name' => $property->name,
                'city' => $property->city,
                'rooms' => Room::where('property_id', $property->id)->count(),
                'occupancy' => $occPct,
                'adr' => $adr,
                'revpar' => $revpar,
                'revenue' => $propRevenue,
            ];
        }

        return view('panel.multi-property.dashboard', compact(
            'properties', 'totalRooms', 'totalRevenue', 'totalGuests',
            'perProperty', 'from', 'to'
        ));
    }
}
