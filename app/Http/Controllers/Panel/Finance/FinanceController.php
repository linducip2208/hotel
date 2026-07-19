<?php

namespace App\Http\Controllers\Panel\Finance;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\BudgetLine;
use App\Models\BudgetPeriod;
use App\Models\FolioPayment;
use App\Models\FxRate;
use App\Models\OwnerStatement;
use App\Services\Accounting\BankReconciliationService;
use App\Services\Finance\FxRateService;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct(protected BankReconciliationService $reconSvc) {}

    public function convertFx(Request $request, FxRateService $fxSvc)
    {
        $amount = (float) $request->input('amount', 0);
        $from = strtoupper((string) $request->input('from', 'IDR'));
        $to = strtoupper((string) $request->input('to', 'USD'));

        $result = $fxSvc->convert($amount, $from, $to);
        $rate = $amount > 0 ? $result / $amount : 0;

        $format = function (float $val, string $cur) {
            $decimals = in_array($cur, ['IDR', 'JPY', 'VND', 'KRW']) ? 0 : 2;
            return number_format($val, $decimals, ',', '.') . ' ' . $cur;
        };

        return response()->json([
            'ok' => true,
            'amount' => $amount,
            'from_currency' => $from,
            'to_currency' => $to,
            'result' => $result,
            'rate' => $rate,
            'from_formatted' => $format($amount, $from),
            'to_formatted' => $format($result, $to),
        ]);
    }

    public function refreshFxRates(FxRateService $fxSvc)
    {
        $fxSvc->fetchLive('IDR');
        return back()->with('success', 'Kurs berhasil diperbarui dari API real-time.');
    }

    public function bankAccounts()
    {
        $accounts = BankAccount::where('property_id', app('current_property')->id)
            ->with('coaAccount')->paginate(50);
        $coa = \App\Models\ChartOfAccount::where('property_id', app('current_property')->id)
            ->where('type', 'asset')->orderBy('code')->get();
        return view('panel.finance.bank-accounts', compact('accounts', 'coa'));
    }

    public function storeBankAccount(Request $request)
    {
        $data = $request->validate([
            'coa_account_id' => 'required|integer',
            'bank_name' => 'required|string',
            'account_no' => 'required|string',
            'account_holder' => 'required|string',
            'currency' => 'nullable|string',
        ]);
        BankAccount::create($data + ['property_id' => app('current_property')->id]);
        return back();
    }

    public function bankRecon(Request $request)
    {
        $pid = app('current_property')->id;
        $statements = BankStatement::whereHas('bankAccount', fn ($q) => $q->where('property_id', $pid))
            ->with('bankAccount', 'lines')
            ->orderByDesc('statement_date')->paginate(50);

        $statementId = (int) $request->query('statement_id');
        $matchResult = null;

        if ($statementId) {
            $statement = BankStatement::with('lines')->findOrFail($statementId);
            $bankLines = $statement->lines;

            $folioPayments = FolioPayment::where('property_id', $pid)
                ->whereDate('payment_date', '>=', $statement->period_from)
                ->whereDate('payment_date', '<=', $statement->period_to)
                ->get();

            $matchResult = $this->reconSvc->match(collect($bankLines), collect($folioPayments));
        }

        $accounts = BankAccount::where('property_id', $pid)->get();

        return view('panel.finance.bank-recon', compact('statements', 'matchResult', 'statementId', 'accounts'));
    }

    public function reconcileMatch(Request $request)
    {
        $data = $request->validate([
            'bank_line_id' => 'required|integer|exists:bank_statement_lines,id',
            'folio_payment_id' => 'required|integer|exists:folio_payments,id',
        ]);

        $this->reconSvc->reconcile($data['bank_line_id'], $data['folio_payment_id']);

        return back()->with('success', 'Transaction reconciled.');
    }

    public function autoMatch(Request $request)
    {
        $request->validate([
            'statement_id' => 'required|integer|exists:bank_statements,id',
        ]);

        $result = $this->reconSvc->autoMatch((int) $request->input('statement_id'));

        $message = "Auto-match selesai: {$result['matched']} cocok dari {$result['total']} transaksi.";

        return back()->with('success', $message);
    }

    public function importStatement(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|integer|exists:bank_accounts,id',
            'file' => 'required|file|mimes:csv,txt,ofx,qfx|max:10240',
        ]);

        $path = $request->file('file')->store('bank-statements');

        $statement = $this->reconSvc->importBankStatement(
            storage_path('app/'.$path),
            (int) $request->input('bank_account_id')
        );

        return back()->with('success', 'Statement imported. '.$statement->lines()->count().' transactions loaded.');
    }

    public function budget(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $period = BudgetPeriod::firstOrCreate(
            ['property_id' => app('current_property')->id, 'year' => $year],
            ['status' => 'draft']
        );
        $lines = $period->lines()->with('account')->orderBy('account_id')->orderBy('month')->get();
        $coa = \App\Models\ChartOfAccount::where('property_id', app('current_property')->id)
            ->whereIn('type', ['revenue', 'expense'])->orderBy('code')->get();
        return view('panel.finance.budget', compact('period', 'lines', 'coa', 'year'));
    }

    public function storeBudgetLine(Request $request)
    {
        $data = $request->validate([
            'budget_period_id' => 'required|integer',
            'account_id' => 'required|integer',
            'month' => 'required|integer|between:1,12',
            'amount' => 'required|numeric',
        ]);
        BudgetLine::updateOrCreate(
            ['budget_period_id' => $data['budget_period_id'], 'account_id' => $data['account_id'], 'month' => $data['month']],
            ['amount' => $data['amount']]
        );
        return back();
    }

    public function ownerStatements()
    {
        $statements = OwnerStatement::where('property_id', app('current_property')->id)
            ->with('room')->orderByDesc('year')->orderByDesc('month')->paginate(50);
        return view('panel.finance.owner-statements', compact('statements'));
    }

    public function fxRates()
    {
        $rates = FxRate::orderByDesc('rate_date')->paginate(100);
        return view('panel.finance.fx-rates', compact('rates'));
    }

    public function storeFxRate(Request $request)
    {
        $data = $request->validate([
            'base_currency' => 'required|string|size:3',
            'quote_currency' => 'required|string|size:3',
            'rate_date' => 'required|date',
            'rate' => 'required|numeric',
            'source' => 'nullable|string',
        ]);
        FxRate::updateOrCreate(
            ['base_currency' => strtoupper($data['base_currency']), 'quote_currency' => strtoupper($data['quote_currency']), 'rate_date' => $data['rate_date']],
            ['rate' => $data['rate'], 'source' => $data['source'] ?? 'manual']
        );
        return back();
    }
}
