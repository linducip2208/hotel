<?php

namespace App\Http\Controllers\Panel\Rms;

use App\Http\Controllers\Controller;
use App\Models\RateShopperSnapshot;
use App\Services\Rms\DemandForecaster;
use App\Services\Rms\RateShopperService;
use App\Services\Rms\YieldReporter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RmsController extends Controller
{
    public function __construct(
        protected DemandForecaster $forecaster, 
        protected YieldReporter $yield, 
        protected RateShopperService $shopper
    ) {}

    public function dashboard()
    {
        $property = app('current_property');
        $summary = $this->yield->summary($property, now()->subDays(30), now());
        return view('panel.rms.dashboard', compact('summary', 'property'));
    }

    public function forecast(Request $request)
    {
        $from = Carbon::parse($request->query('from', now()->toDateString()));
        $to = Carbon::parse($request->query('to', now()->addDays(14)->toDateString()));
        $forecast = $this->forecaster->forecast(app('current_property'), $from, $to);
        return view('panel.rms.forecast', compact('forecast', 'from', 'to'));
    }

    public function yield(Request $request)
    {
        $from = Carbon::parse($request->query('from', now()->subMonth()->toDateString()));
        $to = Carbon::parse($request->query('to', now()->toDateString()));
        $summary = $this->yield->summary(app('current_property'), $from, $to);
        return view('panel.rms.yield', compact('summary', 'from', 'to'));
    }

    public function rateShopper()
    {
        $snapshots = RateShopperSnapshot::where('property_id', app('current_property')->id)
            ->orderByDesc('shopped_for_date')->paginate(50);
        return view('panel.rms.rate-shopper', compact('snapshots'));
    }

    public function triggerRateShopper()
    {
        $property = app('current_property');
        $count = 0;
        for ($d = 0; $d <= 14; $d++) {
            $result = $this->shopper->fetchCompetitorRates($property->id, now()->addDays($d));
            if (!isset($result['cached'])) {
                $count++;
            }
        }
        return back()->with('success', "Rate shopper selesai. {$count} snapshot baru dibuat untuk 14 hari ke depan.");
    }

    public function competitorIntelligence()
    {
        $property = app('current_property');
        $dashboard = $this->shopper->getCompetitorDashboard($property);

        return view('panel.rms.competitor-intelligence', array_merge($dashboard, ['property' => $property]));
    }

    public function pricingLog(Request $request)
    {
        $logs = \App\Models\DynamicPricingLog::where('property_id', app('current_property')->id)
            ->with(['rule', 'roomType', 'channel'])
            ->latest()->paginate(50);
        return view('panel.pricing.logs', compact('logs'));
    }
}
