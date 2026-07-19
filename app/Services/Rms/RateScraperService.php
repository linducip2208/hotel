<?php

namespace App\Services;

use App\Models\RateScraperTarget;
use App\Models\RateScraperLog;
use App\Models\RateScraperAlert;
use App\Models\RateShopperSnapshot;
use Carbon\Carbon;

class RateScraperService
{
    protected int $defaultGapThresholdPct = 15;

    public function scrapeAll(int $propertyId): array
    {
        $targets = RateScraperTarget::where('property_id', $propertyId)
            ->where('is_active', true)
            ->get();

        $results = [];
        foreach ($targets as $target) {
            $results[] = $this->scrapeTarget($target);
        }

        return $results;
    }

    public function scrapeTarget(RateScraperTarget $target): array
    {
        $results = [];
        $dates = [today(), today()->addDay(), today()->addDays(2), today()->addDays(3)];

        foreach ($dates as $date) {
            $log = $this->scrapeForDate($target, $date);
            $results[] = $log;
        }

        return $results;
    }

    protected function scrapeForDate(RateScraperTarget $target, Carbon $date): array
    {
        $rates = [];
        $otaUrls = $target->ota_urls ?? [];

        // Simulated scraping — in production, use HTTP client to fetch real rates
        foreach ($otaUrls as $source => $url) {
            $simulatedRates = $this->simulateScrape($target, $source, $date);
            $rates[$source] = $simulatedRates;
        }

        // Try direct website
        if ($target->website_url) {
            $simulatedRates = $this->simulateScrape($target, 'direct', $date);
            $rates['direct'] = $simulatedRates;
        }

        $ourPrice = $this->getOurLowestPrice($target->property_id, $date);
        $minComp = empty($rates) ? null : min(array_map(function ($sourceRates) {
            return min(array_column($sourceRates, 'rate'));
        }, $rates));

        $priceGapPct = ($ourPrice && $minComp) ? round((($ourPrice - $minComp) / $minComp) * 100, 2) : null;

        $log = RateScraperLog::create([
            'property_id' => $target->property_id,
            'rate_scraper_target_id' => $target->id,
            'scraped_for_date' => $date,
            'source' => 'auto',
            'rates_found' => $rates,
            'our_price' => $ourPrice,
            'min_competitor_price' => $minComp,
            'price_gap_pct' => $priceGapPct,
            'status' => empty($rates) ? 'failed' : 'success',
        ]);

        // Create snapshot for charts
        if ($ourPrice && $minComp) {
            RateShopperSnapshot::create([
                'property_id' => $target->property_id,
                'rate_scraper_target_id' => $target->id,
                'check_date' => today(),
                'shopped_for_date' => $date,
                'competitor_set' => [$target->name],
                'our_rate' => $ourPrice,
                'avg_competitor_rate' => $minComp,
                'rate_index' => $ourPrice > 0 ? round($ourPrice / max($minComp, 1), 3) : 0,
                'alert_sent' => false,
                'alert_threshold_pct' => null,
            ]);
        }

        // Trigger alert if gap exceeds threshold
        if ($priceGapPct && abs($priceGapPct) > $this->defaultGapThresholdPct) {
            $this->createAlert($target->property_id, $log->id, $priceGapPct, $target);
        }

        return $log->toArray();
    }

    protected function simulateScrape(RateScraperTarget $target, string $source, Carbon $date): array
    {
        // In production: use Http::get($url) with proper headers, parse JSON/HTML
        $baseRate = 350000 + (rand(-10, 10) * 5000);
        $roomTypeMapping = $target->room_type_mapping ?? [];

        $rates = [];
        foreach ($roomTypeMapping as $ourType => $theirName) {
            $variation = rand(-5, 15) * 10000;
            $rates[] = [
                'room_type' => $theirName,
                'rate' => $baseRate + $variation,
                'currency' => 'IDR',
                'available' => rand(0, 1) === 1,
            ];
        }

        // If no mapping, generate generic rates
        if (empty($rates)) {
            foreach (['Standard', 'Deluxe', 'Suite'] as $roomName) {
                $variation = rand(-5, 15) * 10000;
                $rates[] = [
                    'room_type' => $roomName,
                    'rate' => $baseRate + $variation,
                    'currency' => 'IDR',
                    'available' => rand(0, 1) === 1,
                ];
            }
        }

        return $rates;
    }

    protected function getOurLowestPrice(int $propertyId, Carbon $date): ?float
    {
        $rates = \App\Models\Rate::where('property_id', $propertyId)
            ->where('date', $date->toDateString())
            ->where('cta', false)
            ->where('ctd', false)
            ->where('closed', false)
            ->orderBy('amount')
            ->take(3)
            ->pluck('amount');

        if ($rates->isEmpty()) {
            $roomType = \App\Models\RoomType::where('property_id', $propertyId)
                ->where('is_active', true)
                ->orderBy('base_rate')
                ->first();
            return $roomType ? (float) $roomType->base_rate : null;
        }

        return (float) $rates->first();
    }

    protected function createAlert(int $propertyId, int $logId, float $gapPct, RateScraperTarget $target): void
    {
        $severity = abs($gapPct) > 30 ? 'critical' : (abs($gapPct) > 20 ? 'warning' : 'info');
        $direction = $gapPct > 0 ? 'lebih mahal' : 'lebih murah';

        RateScraperAlert::create([
            'property_id' => $propertyId,
            'rate_scraper_log_id' => $logId,
            'price_gap_pct' => $gapPct,
            'alert_type' => 'price_gap',
            'severity' => $severity,
            'message' => "Harga kita {$direction} {$gapPct}% dibanding {$target->name}",
        ]);
    }

    public function getUnreadAlerts(int $propertyId): int
    {
        return RateScraperAlert::where('property_id', $propertyId)
            ->where('is_read', false)
            ->count();
    }
}
