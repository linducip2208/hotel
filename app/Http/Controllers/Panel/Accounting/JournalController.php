<?php

namespace App\Http\Controllers\Panel\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalPoster;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $entries = JournalEntry::where('property_id', app('current_property')->id)
            ->with('lines.account')->latest('posted_at')->paginate(50);

        return view('panel.accounting.journal.index', compact('entries'));
    }

    public function create()
    {
        $accounts = ChartOfAccount::where('property_id', app('current_property')->id)
            ->where('is_active', true)
            ->where('type', '!=', 'header')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type', 'normal_balance']);

        return view('panel.accounting.journal.create', compact('accounts'));
    }

    public function store(Request $request, JournalPoster $poster)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_code' => 'required|string',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ]);

        try {
            $entry = $poster->post(app('current_property')->id, $data['description'], $data['lines'], 'manual');
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['lines' => $e->getMessage()]);
        }

        return redirect()->route('panel.accounting.journal.show', $entry->id)
            ->with('success', 'Jurnal berhasil diposting.');
    }

    public function show(int $id)
    {
        $entry = JournalEntry::where('property_id', app('current_property')->id)->with('lines.account')->findOrFail($id);

        return view('panel.accounting.journal.show', compact('entry'));
    }

    public function void(Request $request, int $id)
    {
        $entry = JournalEntry::where('property_id', app('current_property')->id)->findOrFail($id);

        if (! $request->user()->can('acc.journal.void')) {
            abort(403, 'Anda tidak memiliki izin membatalkan jurnal.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($entry->status === 'void') {
            return back()->withErrors(['void' => 'Jurnal ini sudah dibatalkan.']);
        }

        // Posted journals are immutable — void creates the audit trail; use a
        // reversing entry for corrections instead of editing lines.
        $entry->update([
            'status' => 'void',
            'voided_at' => now(),
            'voided_by_user_id' => $request->user()?->id,
            'void_reason' => $data['reason'],
        ]);

        app(AuditLogger::class)->record('journal.voided', $entry, [
            'reason' => $data['reason'],
            'total_debit' => (float) $entry->total_debit,
        ]);

        return back()->with('success', 'Jurnal dibatalkan. Buat jurnal reversing untuk koreksi.');
    }
}
