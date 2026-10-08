<?php

namespace App\Http\Controllers\Panel\Reports;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuestCycleController extends Controller
{
    public function index(Request $request)
    {
        $propertyId = app('current_property')->id;

        $date = $request->query('date', now()->toDateString());
        $range = $request->query('range', '7');

        $start = Carbon::parse($date)->startOfDay();
        $days = max(1, min((int) $range, 31));
        $end = $start->copy()->addDays($days - 1)->endOfDay();

        $status = $request->query('status', 'arrivals');

        // Arrivals: check_in within range
        $arrivals = Reservation::where('property_id', $propertyId)
            ->whereBetween('check_in', [$start, $end])
            ->with('primaryGuest')
            ->orderBy('check_in')
            ->get();

        // Departures: check_out within range
        $departures = Reservation::where('property_id', $propertyId)
            ->whereBetween('check_out', [$start, $end])
            ->with('primaryGuest')
            ->orderBy('check_out')
            ->get();

        // In-house: checked in and staying today
        $inHouse = Reservation::where('property_id', $propertyId)
            ->where('status', 'checked_in')
            ->with(['primaryGuest', 'rooms.room'])
            ->orderBy('room_no')
            ->get();

        // Build a per-day occupancy timeline
        $timeline = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $timeline[$day->toDateString()] = [
                'date' => $day,
                'arrivals' => $arrivals->filter(fn ($r) => $r->check_in->isSameDay($day))->count(),
                'departures' => $departures->filter(fn ($r) => $r->check_out->isSameDay($day))->count(),
                'in_house' => $arrivals->filter(fn ($r) => $r->check_in->lte($day) && $r->check_out->gt($day))->count(),
            ];
        }

        return view('panel.reports.guest-cycle', compact(
            'arrivals',
            'departures',
            'inHouse',
            'timeline',
            'date',
            'days',
            'status'
        ));
    }
}
