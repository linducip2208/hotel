<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::query()
            ->where('property_id', app('current_property')->id)
            ->when($request->query('action'), fn ($q, $a) => $q->where('action', 'like', "%$a%"))
            ->when($request->query('user_id'), fn ($q, $u) => $q->where('user_id', $u))
            ->latest('created_at')
            ->paginate(100);
        return view('panel.audit.index', compact('logs'));
    }

    public function show(int $id)
    {
        $log = AuditLog::where('property_id', app('current_property')->id)->findOrFail($id);
        return view('panel.audit.show', compact('log'));
    }
}
