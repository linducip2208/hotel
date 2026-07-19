<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailyFlashReport;
use App\Models\Reservation;
use App\Models\FolioCharge;
use App\Services\Reports\DailyFlashService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private DailyFlashService $flashSvc) {}

    public function dailyFlash(Request $request)
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        $property = $request->user()->property;

        $report = DailyFlashReport::where('property_id', $property->id)
            ->where('report_date', $request->date)
            ->first();

        if (! $report) {
            $report = $this->flashSvc->generate($property, $request->date);
        }

        return response()->json($report);
    }

    public function occupancy(Request $request)
    {
        $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to'   => 'required|date_format:Y-m-d|after_or_equal:from',
        ]);

        $property = $request->user()->property;

        $reports = DailyFlashReport::where('property_id', $property->id)
            ->whereBetween('report_date', [$request->from, $request->to])
            ->orderBy('report_date')
            ->get(['report_date', 'rooms_kpi', 'total_revenue']);

        return response()->json($reports);
    }

    public function revenueBySource(Request $request)
    {
        $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to'   => 'required|date_format:Y-m-d|after_or_equal:from',
        ]);

        $property = $request->user()->property;

        $data = Reservation::where('property_id', $property->id)
            ->whereIn('status', ['checked_in', 'checked_out'])
            ->whereBetween('check_in', [$request->from, $request->to])
            ->selectRaw('source, COUNT(*) as count, SUM(grand_total) as revenue')
            ->groupBy('source')
            ->orderByDesc('revenue')
            ->get();

        return response()->json($data);
    }
}
