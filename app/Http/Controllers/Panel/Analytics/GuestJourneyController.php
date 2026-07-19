<?php

namespace App\Http\Controllers\Panel\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\GuestJourneyService;
use Illuminate\Http\Request;

class GuestJourneyController extends Controller
{
    public function index(Request $request)
    {
        $property = app('current_property');
        $from = $request->query('from');
        $to   = $request->query('to');

        $service = app(GuestJourneyService::class);
        $funnel  = $service->getFunnel($property, $from, $to);
        $trend   = $service->getConversionTrend($property, 30);

        return view('panel.analytics.guest-journey', compact('funnel', 'trend'));
    }
}
