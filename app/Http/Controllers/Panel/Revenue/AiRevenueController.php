<?php

namespace App\Http\Controllers\Panel\Revenue;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Services\Revenue\AiRevenueAgentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AiRevenueController extends Controller
{
    public function __construct(protected AiRevenueAgentService $service) {}

    public function index(Request $request)
    {
        $property = app('current_property');
        $date = $request->filled('date') ? Carbon::parse($request->input('date')) : now();
        $roomTypes = RoomType::where('property_id', $property->id)->where('is_active', true)->get();

        return view('panel.revenue.ai-revenue', compact('property', 'date', 'roomTypes'));
    }

    public function analyze(Request $request)
    {
        $property = app('current_property');
        $date = $request->filled('date') ? Carbon::parse($request->input('date')) : now();

        $result = $this->service->analyze($property, $date);

        return response()->json($result);
    }

    public function apply(Request $request)
    {
        $property = app('current_property');
        $date = Carbon::parse($request->input('date'));
        $recommendations = $request->input('recommendations', []);

        if (empty($recommendations)) {
            return response()->json(['ok' => false, 'message' => 'Tidak ada rekomendasi untuk diterapkan.']);
        }

        $applied = $this->service->applyRecommendations($property, $date, $recommendations);

        return response()->json([
            'ok' => true,
            'applied' => $applied,
            'message' => "{$applied} rekomendasi harga berhasil diterapkan untuk tanggal {$date->toDateString()}.",
        ]);
    }

    public function batchAnalyze(Request $request)
    {
        $property = app('current_property');
        $insights = $this->service->getWeeklyInsights($property);

        return response()->json([
            'ok' => true,
            'insights' => $insights,
        ]);
    }
}
