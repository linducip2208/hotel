<?php

namespace App\Http\Controllers\Panel\Accounting;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalPoster;
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
        return view('panel.accounting.journal.create');
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
        $entry = $poster->post(app('current_property')->id, $data['description'], $data['lines'], 'manual');
        return redirect()->route('panel.accounting.journal.show', $entry->id);
    }

    public function show(int $id)
    {
        $entry = JournalEntry::where('property_id', app('current_property')->id)->with('lines.account')->findOrFail($id);
        return view('panel.accounting.journal.show', compact('entry'));
    }

    public function void(Request $request, int $id)
    {
        $entry = JournalEntry::where('property_id', app('current_property')->id)->findOrFail($id);
        $entry->update([
            'status' => 'void',
            'voided_at' => now(),
            'voided_by_user_id' => $request->user()?->id,
            'void_reason' => $request->input('reason'),
        ]);
        return back();
    }
}
