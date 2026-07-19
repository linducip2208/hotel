<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $guest = $request->user('customer');
        $propertyId = $guest->property_id;

        $activeBookings = Reservation::where('primary_guest_id', $guest->id)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        $upcomingCheckin = Reservation::where('primary_guest_id', $guest->id)
            ->where('status', 'confirmed')
            ->whereDate('check_in', '>=', now()->toDateString())
            ->orderBy('check_in')
            ->first();

        $pastStays = Reservation::where('primary_guest_id', $guest->id)
            ->whereIn('status', ['completed', 'checked_out', 'cancelled'])
            ->count();

        $totalSpent = Reservation::where('primary_guest_id', $guest->id)
            ->whereIn('status', ['completed', 'checked_out', 'checked_in'])
            ->sum('grand_total');

        $recentReservations = Reservation::where('primary_guest_id', $guest->id)
            ->latest()
            ->take(5)
            ->get();

        $recentFolios = $guest->folios()->latest()->take(5)->get();

        return view('portal.dashboard', compact(
            'guest',
            'activeBookings',
            'upcomingCheckin',
            'pastStays',
            'totalSpent',
            'recentReservations',
            'recentFolios'
        ));
    }
}
