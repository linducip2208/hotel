<?php

namespace App\Http\Controllers\Panel\Marketing;

use App\Http\Controllers\Controller;
use App\Services\Marketing\GoogleHotelAdsService;
use Illuminate\Http\Request;

class GoogleHotelAdsController extends Controller
{
    public function index(GoogleHotelAdsService $service)
    {
        $status = $service->getStatus(app('current_property'));
        $metrics = $service->getPerformanceMetrics(app('current_property'));
        $priceFeed = $service->generatePriceFeed(app('current_property'));

        return view('panel.marketing.google-hotel-ads', compact('status', 'metrics', 'priceFeed'));
    }

    public function syncPriceFeed(GoogleHotelAdsService $service)
    {
        $feed = $service->generatePriceFeed(app('current_property'));

        return back()->with('success', 'Price feed berhasil di-sync. ' . count($feed) . ' entri diperbarui.');
    }
}
