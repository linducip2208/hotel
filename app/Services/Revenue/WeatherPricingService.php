<?php

namespace App\Services\Revenue;

use App\Models\Property;
use App\Models\RateOverride;
use App\Models\RoomType;
use GuzzleHttp\Client;

class WeatherPricingService
{
    protected Client $http;

    protected array $weatherMultiplier = [
        'sunny' => 1.15,
        'clear' => 1.10,
        'partly_cloudy' => 1.05,
        'cloudy' => 1.00,
        'rainy' => 0.95,
        'stormy' => 0.90,
        'extreme' => 0.85,
    ];

    public function __construct()
    {
        $this->http = new Client(['timeout' => 10]);
    }

    public function getForecast(Property $property, string $date): ?array
    {
        if (! $property->lat || ! $property->lng) {
            return $this->mockForecast($date);
        }

        $provider = \App\Models\Provider::where('property_id', $property->id)
            ->where('integration_type', 'weather')
            ->where('is_active', true)->first();

        if (! $provider) {
            return $this->mockForecast($date);
        }

        try {
            $apiKey = $provider->getApiKey();
            $baseUrl = rtrim((string) ($provider->base_url ?? 'https://api.openweathermap.org/data/2.5'), '/');
            $resp = $this->http->get("{$baseUrl}/forecast", [
                'query' => [
                    'lat' => (float) $property->lat,
                    'lon' => (float) $property->lng,
                    'appid' => $apiKey,
                    'units' => 'metric',
                ],
            ]);
            $data = json_decode((string) $resp->getBody(), true);
            return $this->parseOpenWeather($data, $date);
        } catch (\Exception $e) {
            \Log::warning("Weather API error: {$e->getMessage()}");
            return $this->mockForecast($date);
        }
    }

    protected function parseOpenWeather(array $data, string $date): ?array
    {
        foreach ($data['list'] ?? [] as $item) {
            if (str_starts_with($item['dt_txt'] ?? '', $date)) {
                $weather = $item['weather'][0]['main'] ?? 'Clear';
                return [
                    'condition' => strtolower($weather),
                    'temp' => $item['main']['temp'] ?? 25,
                    'humidity' => $item['main']['humidity'] ?? 70,
                    'icon' => $item['weather'][0]['icon'] ?? '01d',
                    '_mock' => false,
                ];
            }
        }
        return $this->mockForecast($date);
    }

    protected function mockForecast(string $date): array
    {
        $conditions = ['sunny', 'clear', 'partly_cloudy', 'cloudy'];
        $condition = $conditions[abs(crc32($date)) % count($conditions)];
        return [
            'condition' => $condition,
            'temp' => rand(24, 34),
            'humidity' => rand(60, 90),
            'icon' => '01d',
            '_mock' => true,
        ];
    }

    public function suggestPriceAdjustment(Property $property, RoomType $roomType, string $date): array
    {
        $forecast = $this->getForecast($property, $date);
        if (! $forecast) {
            return ['adjusted' => false];
        }

        $condition = $forecast['condition'];
        $multiplier = $this->weatherMultiplier[$condition] ?? 1.0;
        $baseRate = (float) ($roomType->base_rate ?? 500000);
        $suggestedRate = round($baseRate * $multiplier, -3);
        $adjustmentPct = round(($multiplier - 1) * 100, 1);

        return [
            'adjusted' => true,
            'condition' => $condition,
            'temp' => $forecast['temp'],
            'multiplier' => $multiplier,
            'current_rate' => $baseRate,
            'suggested_rate' => $suggestedRate,
            'adjustment_pct' => $adjustmentPct,
            'icon' => $forecast['icon'],
            'humidity' => $forecast['humidity'],
            '_mock' => $forecast['_mock'] ?? false,
        ];
    }

    public function applyWeatherPricing(Property $property, string $date): int
    {
        $roomTypes = RoomType::where('property_id', $property->id)->where('is_active', true)->get();
        $applied = 0;
        foreach ($roomTypes as $rt) {
            $suggestion = $this->suggestPriceAdjustment($property, $rt, $date);
            if ($suggestion['adjusted'] && $suggestion['adjustment_pct'] != 0) {
                RateOverride::updateOrCreate(
                    [
                        'property_id' => $property->id,
                        'room_type_id' => $rt->id,
                        'override_date' => $date,
                    ],
                    [
                        'price' => $suggestion['suggested_rate'],
                        'reason' => "Cuaca: {$suggestion['condition']} (x{$suggestion['multiplier']})",
                    ]
                );
                $applied++;
            }
        }
        return $applied;
    }
}
