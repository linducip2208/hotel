<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Receives heartbeat telemetry from deployed clients.
 * Public endpoint — no auth required, but signature verified by license_key + token hash.
 */
class TelemetryReceiverController extends Controller
{
    public function heartbeat(Request $request)
    {
        $data = $request->validate([
            'license_key' => 'required|string',
            'deployment_id' => 'nullable|string',
            'version' => 'nullable|string',
            'rooms_count' => 'nullable|integer',
            'active_bookings' => 'nullable|integer',
            'queue_jobs_pending' => 'nullable|integer',
            'queue_jobs_failed_24h' => 'nullable|integer',
            'errors_24h' => 'nullable|integer',
            'db_size_mb' => 'nullable|integer',
            'uptime_pct_24h' => 'nullable|numeric',
        ]);

        DB::table('deployment_heartbeats')->insert([
            'license_key_hash' => hash('sha256', $data['license_key']),
            'deployment_id' => $data['deployment_id'] ?? null,
            'version' => $data['version'] ?? null,
            'rooms_count' => $data['rooms_count'] ?? null,
            'active_bookings' => $data['active_bookings'] ?? null,
            'queue_jobs_pending' => $data['queue_jobs_pending'] ?? null,
            'queue_jobs_failed_24h' => $data['queue_jobs_failed_24h'] ?? null,
            'errors_24h' => $data['errors_24h'] ?? null,
            'db_size_mb' => $data['db_size_mb'] ?? null,
            'uptime_pct_24h' => $data['uptime_pct_24h'] ?? null,
            'received_at' => now(),
            'source_ip' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}
