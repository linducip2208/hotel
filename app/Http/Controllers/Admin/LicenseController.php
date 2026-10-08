<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseEvent;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function index()
    {
        return view('admin.licenses.index', ['events' => LicenseEvent::latest()->paginate(50)]);
    }

    public function show(int $id)
    {
        return view('admin.licenses.show', ['event' => LicenseEvent::findOrFail($id)]);
    }

    public function create()
    {
        return view('admin.licenses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'license_key' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $issued = LicenseEvent::create([
            'event' => 'issued',
            'payload' => [
                'license_key' => substr($data['license_key'], 0, 8).'****'.substr($data['license_key'], -4),
                'key_fingerprint' => hash('sha256', $data['license_key']),
                'domain' => $data['domain'],
            ],
            'source_ip' => $request->ip(),
            'error' => $data['notes'] ?? null,
        ]);

        return redirect()->route('admin.licenses.show', $issued->id)
            ->with('status', 'License tercatat sebagai issued.');
    }

    public function edit(int $id)
    {
        abort(404);
    }

    public function update(Request $request, int $id)
    {
        abort(404);
    }

    public function destroy(Request $request, int $id)
    {
        $event = LicenseEvent::findOrFail($id);
        LicenseEvent::create([
            'event' => 'revoked',
            'payload' => $event->payload,
            'source_ip' => $request->ip(),
        ]);
        $event->delete();

        return back()->with('status', 'License dihapus.');
    }

    public function revoke(Request $request, int $id)
    {
        $event = LicenseEvent::findOrFail($id);
        LicenseEvent::create([
            'event' => 'revoked',
            'payload' => $event->payload,
            'source_ip' => $request->ip(),
        ]);

        return back()->with('status', 'License dicabut.');
    }

    public function extend(Request $request, int $id)
    {
        $event = LicenseEvent::findOrFail($id);
        $payload = $event->payload ?? [];
        $payload['extended_at'] = now()->toISOString();

        $event->update(['payload' => $payload]);

        return back()->with('status', 'License diperpanjang.');
    }

    public function regenerate(Request $request, int $id)
    {
        $event = LicenseEvent::findOrFail($id);
        LicenseEvent::create([
            'event' => 'regenerated',
            'payload' => $event->payload,
            'source_ip' => $request->ip(),
        ]);

        return back()->with('status', 'License diregenerasi.');
    }
}
