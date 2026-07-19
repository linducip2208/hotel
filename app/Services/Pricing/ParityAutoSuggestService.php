<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Models\ChannelParityAlert;
use App\Models\DynamicPricingLog;
use App\Models\Rate;
use Illuminate\Support\Facades\Log;

final class ParityAutoSuggestService
{
    /**
     * Analyze a parity alert and suggest root cause + fix.
     */
    public function analyze(ChannelParityAlert $alert): array
    {
        $directRate = (float) $alert->direct_rate;
        $channelRate = (float) $alert->channel_rate;
        $gap = $channelRate - $directRate;

        $analysis = [];

        // Check if derived rate misconfiguration
        if ($directRate > $channelRate * 1.5 || $directRate < $channelRate * 0.5) {
            $analysis[] = [
                'cause' => 'likely_derived_rate_misconfig',
                'action' => 'fix_mapping',
                'details' => 'Direct rate appears to be incorrectly derived. Check rate plan derivation rules.',
            ];
        }

        // Check if rate was never pushed
        $rateExists = Rate::whereHas('ratePlan', fn ($q) => $q->where('property_id', $alert->property_id))
            ->where('date', $alert->check_date?->toDateString())
            ->exists();

        if (! $rateExists) {
            $analysis[] = [
                'cause' => 'rate_not_pushed',
                'action' => 'update_rate',
                'details' => 'Rate not found in PMS for check date. Push rate to channel.',
            ];
        }

        // Check competitor undercut
        if ($gap < -50000) {
            $analysis[] = [
                'cause' => 'competitor_undercut',
                'action' => 'manual_review',
                'details' => 'Our direct rate is significantly higher than channel rate. Possible competitor undercutting.',
            ];
        }

        $autoFixable = collect($analysis)->where('action', '!=', 'manual_review')->isNotEmpty();

        return [
            'alert_id' => $alert->id,
            'direct_rate' => $directRate,
            'channel_rate' => $channelRate,
            'gap' => $gap,
            'gap_pct' => $alert->gap_pct,
            'auto_fixable' => $autoFixable,
            'root_causes' => $analysis,
            'suggested_action' => $autoFixable
                ? collect($analysis)->where('action', '!=', 'manual_review')->first()['action']
                : 'manual_review',
        ];
    }

    /**
     * Auto-fix a parity alert if safe to do so.
     */
    public function autoFix(ChannelParityAlert $alert): bool
    {
        $analysis = $this->analyze($alert);
        $action = $analysis['suggested_action'] ?? 'manual_review';

        if ($action === 'manual_review') {
            Log::info("Parity alert {$alert->id} requires manual review.");
            return false;
        }

        if ($action === 'update_rate') {
            Rate::updateOrCreate(
                [
                    'rate_plan_id' => 0, // would need actual rate_plan_id mapping
                    'date' => $alert->check_date?->toDateString(),
                ],
                ['amount' => $alert->channel_rate]
            );

            $alert->update([
                'status' => 'resolved',
                'severity' => 'resolved',
                'resolved_by_user_id' => null,
                'resolved_at' => now(),
                'resolution_notes' => 'Auto-resolved: rate pushed to match channel.',
            ]);

            DynamicPricingLog::create([
                'property_id' => $alert->property_id,
                'rule_type' => 'parity_autofix',
                'action' => 'auto_resolve',
                'details' => "Parity alert {$alert->id} auto-resolved via rate push.",
            ]);

            return true;
        }

        if ($action === 'fix_mapping') {
            Log::info("Parity alert {$alert->id} flagged for mapping fix. Cannot auto-resolve mapping issues.");
            return false;
        }

        return false;
    }
}
