<?php

namespace App\Http\Controllers\Panel\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\ArInvoice;
use App\Models\ChartOfAccount;
use App\Models\EFakturRecord;
use App\Models\Folio;
use App\Models\JournalLine;
use App\Models\NightAudit;
use App\Services\Coretax\EfakturXmlGenerator;
use App\Services\Compliance\NsfpService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function dashboard()
    {
        return view('panel.accounting.dashboard');
    }

    public function trialBalance(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $accounts = ChartOfAccount::where('property_id', app('current_property')->id)
            ->whereIn('type', ['asset', 'liability', 'equity', 'revenue', 'expense'])
            ->orderBy('code')
            ->get()
            ->map(function ($a) use ($year, $month) {
                $sum = JournalLine::whereHas('entry', function ($q) use ($year, $month) {
                        $q->where('property_id', app('current_property')->id)
                            ->where('period_year', '<=', $year)
                            ->where(fn ($qq) => $qq->where('period_year', '<', $year)->orWhere('period_month', '<=', $month))
                            ->where('status', 'posted');
                    })
                    ->where('account_id', $a->id);
                $a->total_debit = (float) (clone $sum)->sum('debit');
                $a->total_credit = (float) (clone $sum)->sum('credit');
                $a->balance = $a->normal_balance === 'debit' ? $a->total_debit - $a->total_credit : $a->total_credit - $a->total_debit;
                return $a;
            });
        return view('panel.accounting.reports.trial-balance', compact('accounts', 'year', 'month'));
    }

    public function profitLoss(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $revenue = JournalLine::whereHas('entry', fn ($q) => $q->where('property_id', app('current_property')->id)->where('period_year', $year)->where('period_month', $month)->where('status', 'posted'))
            ->whereHas('account', fn ($q) => $q->where('type', 'revenue'))
            ->sum('credit');
        $expense = JournalLine::whereHas('entry', fn ($q) => $q->where('property_id', app('current_property')->id)->where('period_year', $year)->where('period_month', $month)->where('status', 'posted'))
            ->whereHas('account', fn ($q) => $q->where('type', 'expense'))
            ->sum('debit');
        return view('panel.accounting.reports.profit-loss', compact('year', 'month', 'revenue', 'expense'));
    }

    public function dailyRevenue(Request $request)
    {
        $audit = NightAudit::where('property_id', app('current_property')->id)
            ->whereDate('audit_date', $request->query('date', now()->subDay()->toDateString()))
            ->first();
        return view('panel.accounting.reports.daily-revenue', compact('audit'));
    }

    public function closePeriod(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $period = AccountingPeriod::firstOrCreate(['property_id' => app('current_property')->id, 'year' => $year, 'month' => $month]);
        $period->update(['status' => 'locked', 'locked_at' => now(), 'locked_by_user_id' => $request->user()?->id]);
        return back();
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $request->query('date', now()->toDateString());

        $accounts = ChartOfAccount::where('property_id', app('current_property')->id)
            ->whereIn('type', ['asset', 'liability', 'equity'])
            ->orderBy('code')
            ->get()
            ->map(function ($a) use ($asOf) {
                $sum = JournalLine::whereHas('entry', function ($q) use ($asOf) {
                        $q->where('property_id', app('current_property')->id)
                            ->where('journal_date', '<=', $asOf)
                            ->where('status', 'posted');
                    })
                    ->where('account_id', $a->id);
                $a->total_debit = (float) (clone $sum)->sum('debit');
                $a->total_credit = (float) (clone $sum)->sum('credit');
                $a->balance = $a->normal_balance === 'debit'
                    ? $a->total_debit - $a->total_credit
                    : $a->total_credit - $a->total_debit;
                return $a;
            });

        $assets = $accounts->where('type', 'asset');
        $liabilities = $accounts->where('type', 'liability');
        $equity = $accounts->where('type', 'equity');

        $currentAssets = $assets->filter(fn ($a) => str_starts_with($a->code, '1-1'));
        $fixedAssets = $assets->filter(fn ($a) => str_starts_with($a->code, '1-2'));
        $currentLiabilities = $liabilities->filter(fn ($a) => str_starts_with($a->code, '2-1'));
        $longTermLiabilities = $liabilities->filter(fn ($a) => str_starts_with($a->code, '2-2'));

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');

        return view('panel.accounting.reports.balance-sheet', compact(
            'asOf', 'assets', 'liabilities', 'equity',
            'currentAssets', 'fixedAssets', 'currentLiabilities', 'longTermLiabilities',
            'totalAssets', 'totalLiabilities', 'totalEquity'
        ));
    }

    public function unlockPeriod(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        AccountingPeriod::where(['property_id' => app('current_property')->id, 'year' => $year, 'month' => $month])
            ->update(['status' => 'open', 'locked_at' => null]);
        return back();
    }

    public function coretaxIndex(Request $request)
    {
        $propertyId = app('current_property')->id;

        $folios = Folio::where('property_id', $propertyId)
            ->with(['guest', 'reservation.primaryGuest', 'reservation.company', 'charges'])
            ->whereNotNull('closed_at')
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        $invoices = ArInvoice::where('property_id', $propertyId)
            ->with(['arAccount', 'lines'])
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        $generated = null;

        if ($request->filled('folio_id')) {
            $folio = Folio::where('property_id', $propertyId)->findOrFail((int) $request->folio_id);
            $generator = new EfakturXmlGenerator();
            $generated = $generator->generateForFolio($folio, $request->filled('nsfp') ? $request->nsfp : null);
        }

        if ($request->filled('invoice_id')) {
            $invoice = ArInvoice::where('property_id', $propertyId)->findOrFail((int) $request->invoice_id);
            $generator = new EfakturXmlGenerator();
            $generated = $generator->generateForInvoice($invoice, $request->filled('nsfp') ? $request->nsfp : null);
        }

        $nsfpPool = EFakturRecord::where('property_id', $propertyId)
            ->whereIn('status', ['normal', 'available'])
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        $history = EFakturRecord::where('property_id', $propertyId)
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        return view('panel.accounting.coretax', compact('folios', 'invoices', 'generated', 'nsfpPool', 'history'));
    }

    public function downloadXml(int $id)
    {
        $efaktur = EFakturRecord::findOrFail($id);
        $xml = $efaktur->response_payload['xml'] ?? '';

        if (empty($xml) && $efaktur->request_payload) {
            $xml = $efaktur->request_payload['xml'] ?? '';
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="e-faktur-' . $efaktur->nomor_faktur . '.xml"',
        ]);
    }

    public function generateNsfp(Request $request)
    {
        $count = min((int) ($request->count ?? 10), 50);
        $propertyId = app('current_property')->id;
        $rangeStart = (int) (now()->format('ymd') . '00000');

        for ($i = 0; $i < $count; $i++) {
            $nsfp = str_pad((string) ($rangeStart + $i), 13, '0', STR_PAD_LEFT);

            EFakturRecord::updateOrCreate(
                ['nomor_faktur' => $nsfp, 'property_id' => $propertyId],
                [
                    'kode_status' => '01',
                    'status' => 'available',
                    'npwp_penjual' => app('current_property')->npwp ?? config('coretax.npwp_penjual', '00.000.000.0-000.000'),
                    'request_payload' => ['source' => 'manual_pool'],
                    'response_payload' => [],
                ]
            );
        }

        return back()->with('success', "{$count} NSFP berhasil digenerate.");
    }
}
