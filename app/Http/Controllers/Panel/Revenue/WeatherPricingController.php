<?php

namespace App\Http\Controllers\Panel\Revenue;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use App\Services\Revenue\WeatherPricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WeatherPricingController extends Controller
{
    public function __construct(protected WeatherPricingService $service) {}

    public function index(Request $request)
    {
        $property = app('current_property');
        $roomTypes = RoomType::where('property_id', $property->id)->where('is_active', true)->get();

        $forecasts = [];
        for ($d = 0; $d < 7; $d++) {
            $date = now()->addDays($d)->toDateString();
            $dayForecast = $this->service->getForecast($property, $date);
            $adjustments = [];

            foreach ($roomTypes as $rt) {
                $adj = $this->service->suggestPriceAdjustment($property, $rt, $date);
                $adjustments[] = $adj;
            }

            $forecasts[] = [
                'date' => $date,
                'day_name' => Carbon::parse($date)->translatedFormat('l'),
                'day_short' => Carbon::parse($date)->translatedFormat('D'),
                'forecast' => $dayForecast,
                'adjustments' => $adjustments,
            ];
        }

        $hasWeatherProvider = \App\Models\Provider::where('property_id', $property->id)
            ->where('integration_type', 'weather')
            ->where('is_active', true)->exists();

        $hasCoordinates = $property->lat && $property->lng;

        return view('panel.revenue.weather-pricing', compact(
            'property', 'roomTypes', 'forecasts', 'hasWeatherProvider', 'hasCoordinates'
        ));
    }

    public function apply(Request $request)
    {
        $property = app('current_property');
        $date = $request->input('date', now()->toDateString());

        $applied = $this->service->applyWeatherPricing($property, $date);

        return redirect()
            ->route('panel.revenue.weather-pricing')
            ->with('success', "{$applied} penyesuaian harga cuaca diterapkan untuk tanggal {$date}.");
    }
}
