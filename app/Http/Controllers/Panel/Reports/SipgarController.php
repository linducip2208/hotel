<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Reports;

use App\Http\Controllers\Controller;
use App\Services\Indonesia\SipgarReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SipgarController extends Controller
{
    public function __construct(protected SipgarReportService $svc) {}

    public function index(Request $request)
    {
        $month = Carbon::parse($request->query('month', now()->startOfMonth()->toDateString()));
        $propertyId = app('current_property')->id;
        $data = $this->svc->generateMonthlyReport($propertyId, $month);

        return view('panel.reports.sipgar', compact('data', 'month'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'month' => 'required|date',
            'format' => 'required|in:excel,csv',
        ]);

        $propertyId = app('current_property')->id;
        $month = Carbon::parse($request->input('month'))->startOfMonth();

        if ($request->input('format') === 'excel') {
            $path = $this->svc->exportExcel($propertyId, $month);
        } else {
            $path = $this->svc->exportCsv($propertyId, $month);
        }

        return response()->download($path)->deleteFileAfterSend();
    }
}
