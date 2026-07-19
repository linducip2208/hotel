<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Plan;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function flags() { return view('admin.system.flags'); }
    public function updateFlags(Request $request) { return back(); }
    public function plans() { return view('admin.system.plans', ['plans' => Plan::all()]); }

    public function storePlan(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:plans,slug',
            'monthly_price_idr' => 'nullable|numeric',
            'per_room_price_idr' => 'nullable|numeric',
            'max_rooms' => 'nullable|integer',
        ]);
        Plan::create($data);
        return back();
    }

    public function emailTemplates() { return view('admin.system.email-templates'); }
    public function auditLog() { return view('admin.system.audit-log', ['logs' => AuditLog::latest()->paginate(100)]); }
}
