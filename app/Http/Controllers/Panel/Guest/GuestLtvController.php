<?php

namespace App\Http\Controllers\Panel\Guest;

use App\Http\Controllers\Controller;
use App\Models\GuestProfile;
use Illuminate\Http\Request;

class GuestLtvController extends Controller
{
    public function index(Request $request)
    {
        $property = app('current_property');
        $segment = $request->query('segment');

        $baseQuery = GuestProfile::whereHas('guest', fn($q) => $q->where('property_id', $property->id));

        $stats = [
            'total_guests' => (clone $baseQuery)->count(),
            'total_ltv' => (clone $baseQuery)->sum('total_lifetime_value'),
            'avg_ltv' => round((clone $baseQuery)->avg('total_lifetime_value') ?? 0, 0),
            'vip_count' => (clone $baseQuery)->where('upsell_score', '>=', 70)->count(),
            'at_risk_count' => (clone $baseQuery)->where('churn_risk_score', '>=', 60)->count(),
        ];

        $rfm = [
            'champions' => (clone $baseQuery)->where('total_stays', '>=', 5)->where('total_lifetime_value', '>=', 5000000)->count(),
            'loyal' => (clone $baseQuery)->where('total_stays', '>=', 3)->where('total_stays', '<', 5)->count(),
            'potential' => (clone $baseQuery)->where('total_stays', 2)->count(),
            'new' => (clone $baseQuery)->where('total_stays', 1)->count(),
        ];

        $profiles = match ($segment) {
            'vip' => (clone $baseQuery)->where('upsell_score', '>=', 70),
            'at_risk' => (clone $baseQuery)->where('churn_risk_score', '>=', 60),
            'champions' => (clone $baseQuery)->where('total_stays', '>=', 5)->where('total_lifetime_value', '>=', 5000000),
            'loyal' => (clone $baseQuery)->where('total_stays', '>=', 3)->where('total_stays', '<', 5),
            'potential' => (clone $baseQuery)->where('total_stays', 2),
            'new' => (clone $baseQuery)->where('total_stays', 1),
            default => $baseQuery,
        };

        $profiles = $profiles->with('guest')->orderByDesc('total_lifetime_value')->paginate(25)->appends($request->query());

        return view('panel.guests.ltv-dashboard', compact('stats', 'rfm', 'profiles', 'segment'));
    }

    public function show($id)
    {
        $property = app('current_property');
        $profile = GuestProfile::whereHas('guest', fn($q) => $q->where('property_id', $property->id))
            ->with(['guest.reservations' => fn($q) => $q->latest()->limit(20)])
            ->findOrFail($id);

        return view('panel.guests.ltv-detail', compact('profile'));
    }
}
