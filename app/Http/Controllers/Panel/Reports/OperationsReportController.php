<?php

namespace App\Http\Controllers\Panel\Reports;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\DailyFlashReport;
use App\Models\FolioPayment;
use App\Models\Reservation;
use App\Services\Reports\DailyFlashService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationsReportController extends Controller
{
    public function occupancy(Request $request, DailyFlashService $svc)
    {
        $property = app('current_property');

        $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()));
        $to   = Carbon::parse($request->query('to',   now()->toDateString()));

        if ($to->diffInDays($from) > 365) {
            $from = $to->copy()->subDays(364);
        }

        // Ensure flash reports exist for the range (builds missing ones on the fly)
        $cursor = $from->copy();
        while ($cursor->lte($to) && $cursor->lte(now())) {
            if (! DailyFlashReport::where('property_id', $property->id)->where('report_date', $cursor->toDateString())->exists()) {
                $svc->build($property, $cursor->copy());
            }
            $cursor->addDay();
        }

        $rows = DailyFlashReport::where('property_id', $property->id)
            ->whereBetween('report_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('report_date')
            ->get()
            ->map(function ($r) {
                $kpi = $r->rooms_kpi ?? [];
                return [
                    'date'        => $r->report_date,
                    'sold'        => $kpi['sold']          ?? 0,
                    'available'   => $kpi['available']     ?? 0,
                    'occ_pct'     => $kpi['occupancy_pct'] ?? 0,
                    'adr'         => $kpi['adr']           ?? 0,
                    'revpar'      => $kpi['revpar']         ?? 0,
                    'total_rev'   => $r->total_revenue     ?? 0,
                ];
            });

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($rows, "occupancy_{$from->toDateString()}_{$to->toDateString()}.csv");
        }

        return view('panel.reports.occupancy', compact('rows', 'from', 'to'));
    }

    public function channelProduction(Request $request)
    {
        $property = app('current_property');

        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to',   now()->toDateString());

        $rows = Reservation::where('property_id', $property->id)
            ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('check_in', [$from, $to])
            ->select('source', DB::raw('count(*) as bookings'), DB::raw('sum(grand_total) as revenue'))
            ->groupBy('source')
            ->orderByDesc('revenue')
            ->get();

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($rows, "channel_production_{$from}_{$to}.csv");
        }

        return view('panel.reports.channel-production', compact('rows', 'from', 'to'));
    }

    public function sourceOfBusiness(Request $request)
    {
        return $this->channelProduction($request);
    }

    public function cashierShift(Request $request)
    {
        $property = app('current_property');

        $from = $request->query('from', now()->toDateString());
        $to   = $request->query('to',   now()->toDateString());

        // Fallback for old single-date parameter
        if ($request->has('date') && !$request->has('from')) {
            $from = $request->query('date', now()->toDateString());
            $to   = $from;
        }

        $shifts = CashierShift::where('property_id', $property->id)
            ->whereBetween(DB::raw('DATE(opened_at)'), [$from, $to])
            ->with('cashier')
            ->orderBy('opened_at')
            ->get()
            ->map(function ($shift) {
                $pmtBreakdown = FolioPayment::where('shift_id', $shift->id)
                    ->where('is_void', false)
                    ->selectRaw('method, sum(amount) as total')
                    ->groupBy('method')
                    ->pluck('total', 'method')
                    ->toArray();

                return [
                    'id'            => $shift->id,
                    'cashier'       => $shift->cashier?->name ?? '—',
                    'opened_at'     => $shift->opened_at,
                    'closed_at'     => $shift->closed_at,
                    'opening_float' => (float) $shift->opening_float,
                    'expected_cash' => (float) $shift->expected_cash,
                    'actual_cash'   => (float) $shift->actual_cash,
                    'variance'      => (float) $shift->cash_variance,
                    'breakdown'     => $pmtBreakdown,
                    'is_open'       => is_null($shift->closed_at),
                ];
            });

        if ($request->query('export') === 'csv') {
            $filename = "cashier_shift_{$from}_{$to}.csv";
            return $this->exportCsv($shifts, $filename);
        }

        return view('panel.reports.cashier-shift', compact('shifts', 'from', 'to'));
    }

    public function guestDemographics(Request $request)
    {
        $property = app('current_property');

        $from = $request->query('from', now()->subMonths(12)->toDateString());
        $to   = $request->query('to', now()->toDateString());

        $reservations = Reservation::where('property_id', $property->id)
            ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
            ->whereBetween('check_in', [$from, $to])
            ->with('primaryGuest')
            ->get();

        $totalGuests = $reservations->unique('primary_guest_id')->count();
        $returningCount = $reservations->groupBy('primary_guest_id')->filter(fn ($g) => $g->count() > 1)->count();
        $newCount = $totalGuests - $returningCount;
        $avgStay = $reservations->avg(fn ($r) => $r->check_in->diffInDays($r->check_out)) ?: 0;
        $avgSpend = $reservations->avg('grand_total') ?: 0;

        $ageGroups = [
            '18-25' => 0, '26-35' => 0, '36-45' => 0,
            '46-55' => 0, '56-65' => 0, '65+' => 0, 'Tidak Diketahui' => 0,
        ];
        $nationality = [];
        $city = [];
        $segment = ['Corporate' => 0, 'Leisure' => 0, 'OTA' => 0, 'Direct' => 0, 'Lainnya' => 0];

        foreach ($reservations->unique('primary_guest_id') as $r) {
            $guest = $r->primaryGuest;
            if (!$guest) continue;

            if ($guest->date_of_birth) {
                $age = $guest->date_of_birth->age;
                if ($age < 18) continue;
                if ($age <= 25) $ageGroups['18-25']++;
                elseif ($age <= 35) $ageGroups['26-35']++;
                elseif ($age <= 45) $ageGroups['36-45']++;
                elseif ($age <= 55) $ageGroups['46-55']++;
                elseif ($age <= 65) $ageGroups['56-65']++;
                else $ageGroups['65+']++;
            } else {
                $ageGroups['Tidak Diketahui']++;
            }

            $nat = $guest->country ?? 'Tidak Diketahui';
            $nationality[$nat] = ($nationality[$nat] ?? 0) + 1;

            $cty = $guest->city ?? 'Tidak Diketahui';
            $city[$cty] = ($city[$cty] ?? 0) + 1;

            $src = strtolower($r->source ?? 'direct');
            if (in_array($src, ['booking_com', 'agoda', 'traveloka', 'tiket_com', 'expedia', 'airbnb', 'trip_com', 'pegipegi'])) {
                $segment['OTA']++;
            } elseif (str_contains($src, 'corp') || $src === 'corporate') {
                $segment['Corporate']++;
            } elseif ($src === 'direct' || $src === 'website' || $src === 'walk_in') {
                $segment['Direct']++;
            } elseif ($src === 'leisure') {
                $segment['Leisure']++;
            } else {
                $segment['Lainnya']++;
            }
        }

        arsort($nationality);
        arsort($city);

        return view('panel.reports.guest-demographics', compact(
            'from', 'to', 'totalGuests', 'returningCount', 'newCount',
            'avgStay', 'avgSpend', 'ageGroups', 'nationality', 'city', 'segment'
        ));
    }

    public function exportPdf(Request $request, string $type)
    {
        $property = app('current_property');

        $data = match ($type) {
            'occupancy' => $this->occupancyPdfData($request),
            'cashier-shift' => $this->cashierShiftPdfData($request),
            'flash' => $this->flashPdfData($request),
            default => abort(404),
        };

        $pdf = Pdf::loadView('panel.reports.pdf.' . $type, $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download($type . '-' . now()->format('Ymd') . '.pdf');
    }

    private function occupancyPdfData(Request $request): array
    {
        $svc = app(DailyFlashService::class);
        $property = app('current_property');

        $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()));
        $to   = Carbon::parse($request->query('to',   now()->toDateString()));

        if ($to->diffInDays($from) > 365) {
            $from = $to->copy()->subDays(364);
        }

        $cursor = $from->copy();
        while ($cursor->lte($to) && $cursor->lte(now())) {
            if (! DailyFlashReport::where('property_id', $property->id)->where('report_date', $cursor->toDateString())->exists()) {
                $svc->build($property, $cursor->copy());
            }
            $cursor->addDay();
        }

        $rows = DailyFlashReport::where('property_id', $property->id)
            ->whereBetween('report_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('report_date')
            ->get()
            ->map(function ($r) {
                $kpi = $r->rooms_kpi ?? [];
                return [
                    'date'        => $r->report_date,
                    'sold'        => $kpi['sold']          ?? 0,
                    'available'   => $kpi['available']     ?? 0,
                    'occ_pct'     => $kpi['occupancy_pct'] ?? 0,
                    'adr'         => $kpi['adr']           ?? 0,
                    'revpar'      => $kpi['revpar']         ?? 0,
                    'total_rev'   => $r->total_revenue     ?? 0,
                ];
            });

        $totalSold  = $rows->sum('sold');
        $totalAvail = $rows->sum('available');
        $totalRev   = $rows->sum('total_rev');
        $avgOcc     = $rows->count() ? round($rows->avg('occ_pct'), 1) : 0;
        $avgAdr     = $totalSold > 0 ? round($rows->sum(fn($r) => $r['adr'] * $r['sold']) / $totalSold, 0) : 0;
        $avgRevpar  = $rows->count() ? round($rows->avg('revpar'), 0) : 0;

        return compact('rows', 'from', 'to', 'totalSold', 'totalAvail', 'totalRev', 'avgOcc', 'avgAdr', 'avgRevpar');
    }

    private function cashierShiftPdfData(Request $request): array
    {
        $property = app('current_property');

        $from = $request->query('from', now()->toDateString());
        $to   = $request->query('to',   now()->toDateString());

        $shifts = CashierShift::where('property_id', $property->id)
            ->whereBetween(DB::raw('DATE(opened_at)'), [$from, $to])
            ->with('cashier')
            ->orderBy('opened_at')
            ->get()
            ->map(function ($shift) {
                $pmtBreakdown = FolioPayment::where('shift_id', $shift->id)
                    ->where('is_void', false)
                    ->selectRaw('method, sum(amount) as total')
                    ->groupBy('method')
                    ->pluck('total', 'method')
                    ->toArray();

                return [
                    'id'            => $shift->id,
                    'cashier'       => $shift->cashier?->name ?? '—',
                    'opened_at'     => $shift->opened_at,
                    'closed_at'     => $shift->closed_at,
                    'opening_float' => (float) $shift->opening_float,
                    'expected_cash' => (float) $shift->expected_cash,
                    'actual_cash'   => (float) $shift->actual_cash,
                    'variance'      => (float) $shift->cash_variance,
                    'breakdown'     => $pmtBreakdown,
                    'is_open'       => is_null($shift->closed_at),
                ];
            });

        return compact('shifts', 'from', 'to');
    }

    private function flashPdfData(Request $request): array
    {
        $svc = app(DailyFlashService::class);
        $property = app('current_property');

        $from = $request->query('from', now()->toDateString());
        $to   = $request->query('to',   now()->toDateString());

        // Build the latest report for display
        $report = $svc->build($property, Carbon::parse($to));

        // Fetch 7-day trend
        $trendFrom = Carbon::parse($to)->subDays(6);
        $trendData = DailyFlashReport::where('property_id', $property->id)
            ->whereBetween('report_date', [$trendFrom->toDateString(), $to])
            ->orderBy('report_date')
            ->pluck('total_revenue', 'report_date')
            ->toArray();

        return compact('report', 'from', 'to', 'trendData');
    }

    private function exportCsv($rows, string $filename)
    {
        $handle = fopen('php://temp', 'r+');

        if ($rows->isNotEmpty()) {
            fputcsv($handle, array_keys((array) $rows->first()));
            foreach ($rows as $row) {
                fputcsv($handle, array_values((array) $row));
            }
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
