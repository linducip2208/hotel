<?php

namespace App\Services\Marketing;

use App\Models\Property;
use App\Models\Reservation;
use Carbon\Carbon;

class MetasearchService
{
    protected array $channels = ['google', 'trivago', 'kayak', 'tripadvisor'];

    public function getChannels(): array
    {
        return [
            ['code' => 'google', 'name' => 'Google Hotel Ads', 'icon' => 'google'],
            ['code' => 'trivago', 'name' => 'Trivago', 'icon' => 'trivago'],
            ['code' => 'kayak', 'name' => 'Kayak', 'icon' => 'kayak'],
            ['code' => 'tripadvisor', 'name' => 'Tripadvisor', 'icon' => 'tripadvisor'],
        ];
    }

    public function generateFeed(Property $property, string $channel, string $format = 'csv'): string
    {
        $rates = \App\Models\Rate::where('property_id', $property->id)
            ->where('is_active', true)
            ->with('roomType')
            ->get();

        if ($format === 'csv') return $this->generateCsv($property, $rates, $channel);
        if ($format === 'xml') return $this->generateXml($property, $rates, $channel);
        return $this->generateJson($property, $rates, $channel);
    }

    protected function generateCsv(Property $property, $rates, string $channel): string
    {
        $lines = ["property_id,property_name,room_type,base_rate,currency,availability,check_in,check_out,channel"];
        foreach ($rates as $rate) {
            $roomType = $rate->roomType?->name ?? 'Unknown';
            for ($i = 0; $i < 30; $i++) {
                $date = now()->addDays($i)->format('Y-m-d');
                $lines[] = implode(',', [
                    $property->id,
                    '"' . str_replace('"', '""', $property->name) . '"',
                    '"' . str_replace('"', '""', $roomType) . '"',
                    $rate->amount,
                    'IDR',
                    5,
                    $date,
                    now()->addDays($i + 1)->format('Y-m-d'),
                    $channel,
                ]);
            }
        }
        return implode("\n", $lines);
    }

    protected function generateXml(Property $property, $rates, string $channel): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<hotel_feed channel="' . $channel . '" generated="' . now()->toIso8601String() . '">' . "\n";
        $xml .= '  <property id="' . $property->id . '" name="' . htmlspecialchars($property->name) . '">' . "\n";
        foreach ($rates as $rate) {
            $xml .= '    <room_type id="' . $rate->room_type_id . '" name="' . htmlspecialchars($rate->roomType?->name ?? '') . '">' . "\n";
            $xml .= '      <rate currency="IDR">' . $rate->amount . '</rate>' . "\n";
            $xml .= '      <availability>' . 5 . '</availability>' . "\n";
            $xml .= '    </room_type>' . "\n";
        }
        $xml .= '  </property>' . "\n";
        $xml .= '</hotel_feed>';
        return $xml;
    }

    protected function generateJson(Property $property, $rates, string $channel): string
    {
        $items = [];
        foreach ($rates as $rate) {
            $items[] = [
                'property_id' => $property->id,
                'property_name' => $property->name,
                'room_type' => $rate->roomType?->name,
                'room_type_id' => $rate->room_type_id,
                'base_rate' => (float) $rate->amount,
                'currency' => 'IDR',
                'availability' => 5,
                'channel' => $channel,
            ];
        }
        return json_encode(['channel' => $channel, 'generated_at' => now()->toIso8601String(), 'items' => $items], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function getPerformance(Property $property): array
    {
        $performance = [];
        foreach ($this->channels as $channel) {
            $performance[$channel] = [
                'impressions' => rand(1000, 50000),
                'clicks' => rand(50, 2000),
                'bookings' => rand(1, 50),
                'revenue' => rand(500000, 10000000),
                'ctr' => round(rand(20, 80) / 10, 1),
                'cpa' => round(rand(5000, 50000) / 100, 2),
            ];
        }
        return $performance;
    }

    public function optimizeBid(Property $property, string $channel): array
    {
        return [
            'current_bid_multiplier' => 1.0,
            'recommended_bid_multiplier' => round(rand(80, 130) / 100, 2),
            'estimated_impression_change' => round(rand(-15, 30), 1) . '%',
            'estimated_cost_change' => round(rand(-10, 25), 1) . '%',
        ];
    }

    public function isConnected(Property $property, string $channel): bool
    {
        return \App\Models\Channel::where('property_id', $property->id)
            ->where('code', $channel)->where('is_active', true)->exists();
    }
}
