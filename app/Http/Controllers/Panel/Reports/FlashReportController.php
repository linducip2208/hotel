<?php

namespace App\Http\Controllers\Panel\Reports;

use App\Http\Controllers\Controller;
use App\Models\DailyFlashReport;
use App\Services\Reports\DailyFlashService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FlashReportController extends Controller
{
    public function show(Request $request, DailyFlashService $svc)
    {
        $property = app('current_property');

        $from = $request->query('from', now()->toDateString());
        $to   = $request->query('to',   now()->toDateString());

        // Fallback for old single-date parameter
        if ($request->has('date') && !$request->has('from')) {
            $from = $request->query('date', now()->toDateString());
            $to   = $from;
        }

        // Build the latest report for display (use $to as the reference date)
        $date  = Carbon::parse($to);
        $report = $svc->build($property, $date);

        // Fetch 7-day revenue trend
        $trendFrom = Carbon::parse($to)->subDays(6);
        $trendData = DailyFlashReport::where('property_id', $property->id)
            ->whereBetween('report_date', [$trendFrom->toDateString(), $to])
            ->orderBy('report_date')
            ->pluck('total_revenue', 'report_date')
            ->toArray();

        return view('panel.reports.flash', compact('report', 'from', 'to', 'trendData'));
    }
}
