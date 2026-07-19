<?php

namespace App\Services\Channel;

use App\Models\Channel;
use App\Models\ChannelParityAlert;
use App\Models\Property;
use App\Models\RateShopperSnapshot;
use App\Services\Pricing\OpenPricingService;
use Carbon\Carbon;

/**
 * Channel Parity Monitor: compares direct booking rate vs OTA rate from
 * RateShopperSnapshot and creates ChannelParityAlert when a breach is found.
 * Severity rules:
 *   - gap <= -5%  : low
 *   - gap <= -10% : medium
 *   - gap <= -20% : high
 *   - gap  > -20% : critical
 */
class ParityMonitorService
{
    public function __construct(private OpenPricingService $pricing) {}

    public function checkAndAlert(Property $property): array
    {
        $alerts = [];
        $today  = Carbon::today()->toDateString();
        $window = Carbon::today()->addDays(30)->toDateString();

        $snapshots = RateShopperSnapshot::where('property_id', $property->id)
            ->whereBetween('check_date', [$today, $window])
            ->get();

        foreach ($snapshots as $snap) {
            $directPricing = $this->pricing->effectivePrice(
                $property->id,
                $snap->room_type_id ?? $property->roomTypes()->value('id'),
                null, // direct = no channel
                $snap->check_date->toDateString()
            );

            $directRate  = $directPricing['price'];
            $channelRate = (float) $snap->competitor_rate;

            if ($directRate <= 0 || $channelRate <= 0) {
                continue;
            }

            $gapAmount = $directRate - $channelRate;
            $gapPct    = ($gapAmount / $directRate) * 100;

            // Only alert when OTA is cheaper than direct (negative gap = breach)
            if ($gapPct >= -2) {
                continue; // within tolerance
            }

            $severity = match (true) {
                $gapPct > -5   => 'low',
                $gapPct > -10  => 'medium',
                $gapPct > -20  => 'high',
                default        => 'critical',
            };

            $channel = Channel::where('property_id', $property->id)
                ->where('code', $snap->source)
                ->first();

            $existing = ChannelParityAlert::where('property_id', $property->id)
                ->where('channel_id', $channel?->id)
                ->where('check_date', $snap->check_date->toDateString())
                ->where('status', 'open')
                ->first();

            if (! $existing) {
                $alert = ChannelParityAlert::create([
                    'property_id'  => $property->id,
                    'room_type_id' => $snap->room_type_id ?? $property->roomTypes()->value('id'),
                    'channel_id'   => $channel?->id,
                    'check_date'   => $snap->check_date->toDateString(),
                    'direct_rate'  => $directRate,
                    'channel_rate' => $channelRate,
                    'gap_amount'   => $gapAmount,
                    'gap_pct'      => $gapPct,
                    'severity'     => $severity,
                    'status'       => 'open',
                ]);
                $alerts[] = $alert;
            }
        }

        return $alerts;
    }

    public function acknowledge(ChannelParityAlert $alert, int $userId, ?string $notes = null): void
    {
        $alert->update([
            'status'                => 'acknowledged',
            'resolved_by_user_id'   => $userId,
            'notes'                 => $notes,
        ]);
    }

    public function resolve(ChannelParityAlert $alert, int $userId, string $action, ?string $notes = null): void
    {
        $alert->update([
            'status'                => 'resolved',
            'action_taken'          => $action,
            'resolved_by_user_id'   => $userId,
            'resolved_at'           => now(),
            'notes'                 => $notes,
        ]);
    }
}
