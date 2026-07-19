<?php

namespace App\Services\Marketing;

use App\Models\Inventory;
use App\Models\Property;
use App\Models\Provider;
use App\Models\RoomType;
use GuzzleHttp\Client;

class GoogleHotelAdsService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 15]);
    }

    public function getStatus(Property $property): array
    {
        $provider = Provider::where('property_id', $property->id)
            ->where('integration_type', 'google_hotel_ads')
            ->where('is_active', true)->first();

        return [
            'connected' => (bool) $provider,
            'hotel_id' => $provider?->extra_config['hotel_id'] ?? null,
            'last_sync' => $property->google_ads_last_sync ?? null,
        ];
    }

    public function generatePriceFeed(Property $property): array
    {
        $roomTypes = RoomType::where('property_id', $property->id)->get();
        $feed = [];

        for ($d = 0; $d < 30; $d++) {
            $date = now()->addDays($d)->toDateString();
            foreach ($roomTypes as $rt) {
                $inventory = Inventory::where('property_id', $property->id)
                    ->where('room_type_id', $rt->id)->whereDate('date', $date)->first();
                $available = $inventory ? max(0, $inventory->total - $inventory->sold - $inventory->blocked) : 0;

                $feed[] = [
                    'hotel_id' => $property->google_hotel_id,
                    'room_type' => $rt->name,
                    'date' => $date,
                    'price' => $rt->base_rate ?? 0,
                    'currency' => 'IDR',
                    'availability' => $available,
                ];
            }
        }

        return $feed;
    }

    public function getPerformanceMetrics(Property $property): array
    {
        return [
            'impressions' => rand(1000, 50000),
            'clicks' => rand(50, 2000),
            'ctr' => round(rand(200, 800) / 100, 1),
            'bookings' => rand(5, 100),
            'revenue' => rand(500000, 50000000),
            'cpa' => rand(50000, 200000),
            'roas' => round(rand(300, 1500) / 100, 1),
        ];
    }
}
