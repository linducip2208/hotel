<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\RateScraperTarget;
use App\Models\RateScraperAlert;
use App\Services\Rms\RateScraperService;
use Illuminate\Http\Request;

class RateScraperController extends Controller
{
    public function index()
    {
        $property = app('current_property');
        $targets = RateScraperTarget::where('property_id', $property->id)
            ->with(['logs' => fn($q) => $q->latest()->limit(5)])
            ->get();
        $alerts = RateScraperAlert::where('property_id', $property->id)
            ->where('is_read', false)
            ->latest()
            ->take(20)
            ->get();

        return view('panel.rms.scraper.index', compact('property', 'targets', 'alerts'));
    }

    public function storeTarget(Request $request)
    {
        $property = app('current_property');
        RateScraperTarget::create(array_merge($request->all(), [
            'property_id' => $property->id,
            'ota_urls' => json_decode($request->input('ota_urls', '{}'), true),
            'room_type_mapping' => json_decode($request->input('room_type_mapping', '{}'), true),
        ]));
        return back()->with('success', 'Kompetitor ditambahkan.');
    }

    public function updateTarget(Request $request, $id)
    {
        $target = RateScraperTarget::findOrFail($id);
        $target->update($request->only(['name', 'website_url', 'stars', 'address', 'distance_km', 'is_active']));
        if ($request->has('ota_urls')) {
            $target->ota_urls = json_decode($request->ota_urls, true);
            $target->save();
        }
        return back()->with('success', 'Target diupdate.');
    }

    public function destroyTarget($id)
    {
        RateScraperTarget::findOrFail($id)->delete();
        return back()->with('success', 'Target dihapus.');
    }

    public function scrapeTarget(RateScraperService $service, $id)
    {
        $target = RateScraperTarget::findOrFail($id);
        $results = $service->scrapeTarget($target);
        return back()->with('success', "Berhasil scrape {$target->name}.");
    }

    public function scrapeAll(RateScraperService $service)
    {
        $property = app('current_property');
        $results = $service->scrapeAll($property->id);
        return back()->with('success', "Berhasil scrape " . count($results) . " target.");
    }

    public function alerts()
    {
        $property = app('current_property');
        $alerts = RateScraperAlert::where('property_id', $property->id)
            ->latest()
            ->paginate(30);
        return view('panel.rms.scraper.alerts', compact('property', 'alerts'));
    }

    public function markAlertRead($id)
    {
        $alert = RateScraperAlert::findOrFail($id);
        $alert->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
