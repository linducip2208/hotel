<?php

namespace App\Services;

use App\Models\GuestProfile;
use App\Models\Guest;
use App\Models\RfmSegmentRule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RfmSegmentationService
{
    public function calculateAll(int $propertyId): int
    {
        $profiles = GuestProfile::whereHas('guest', function ($q) use ($propertyId) {
            $q->whereHas('reservations', function ($r) use ($propertyId) {
                $r->where('property_id', $propertyId);
            });
        })->get();

        if ($profiles->isEmpty()) return 0;

        $this->scoreRecency($profiles);
        $this->scoreFrequency($profiles);
        $this->scoreMonetary($profiles);

        $count = 0;
        foreach ($profiles as $p) {
            $rfm = ($p->recency_score ?? 0) + ($p->frequency_score ?? 0) + ($p->monetary_score ?? 0);
            $p->rfm_score = $rfm;
            $p->rfm_segment = $this->determineSegment($rfm, $p);
            $p->rfm_calculated_at = now();
            $p->save();

            // Apply guest tags
            $guest = Guest::find($p->guest_id);
            if ($guest) {
                $tags = $guest->tags ?? [];
                $tags = array_filter($tags, fn($t) => !in_array($t, ['vip', 'at_risk', 'lost', 'new', 'potential', 'regular', 'hibernating']));
                $tags[] = $p->rfm_segment;
                if (in_array($p->rfm_segment, ['vip', 'at_risk'])) {
                    $guest->is_vip = $p->rfm_segment === 'vip';
                }
                $guest->tags = array_values($tags);
                $guest->save();
            }

            // Auto-actions from segment rules
            $this->executeSegmentActions($propertyId, $guest, $p);
            $count++;
        }

        return $count;
    }

    protected function scoreRecency(Collection $profiles): void
    {
        if ($profiles->isEmpty()) return;
        $max = $profiles->max('total_stays');
        if ($max <= 0) return;

        // Recency is based on days since last stay — lower is better
        $guests = Guest::whereIn('id', $profiles->pluck('guest_id'))->get()->keyBy('id');
        $today = now();

        $recencyDays = [];
        foreach ($profiles as $p) {
            $guest = $guests->get($p->guest_id);
            if ($guest) {
                $lastRes = $guest->reservations()->where('status', 'checked_out')
                    ->orderBy('check_out', 'desc')->first();
                $days = $lastRes ? $today->diffInDays(Carbon::parse($lastRes->check_out)) : 999;
            } else {
                $days = 999;
            }
            $recencyDays[$p->id] = $days;
        }

        $sorted = $recencyDays;
        asort($sorted);
        $total = count($sorted);
        $quintile = max(1, (int) ceil($total / 5));

        $rank = 0;
        foreach ($sorted as $pid => $days) {
            $score = 5 - (int) floor($rank / $quintile);
            $profiles->firstWhere('id', $pid)?->setAttribute('recency_score', $score);
            $rank++;
        }
    }

    protected function scoreFrequency(Collection $profiles): void
    {
        if ($profiles->isEmpty()) return;
        $values = $profiles->pluck('total_stays')->toArray();
        sort($values);
        $total = count($values);
        if ($total <= 1) {
            $profiles->each(fn($p) => $p->setAttribute('frequency_score', 3));
            return;
        }
        $quintile = max(1, (int) ceil($total / 5));
        foreach ($profiles as $p) {
            $rank = 0;
            foreach ($values as $v) { if ($v < $p->total_stays) $rank++; }
            $p->setAttribute('frequency_score', min(5, 1 + (int) floor($rank / $quintile)));
        }
    }

    protected function scoreMonetary(Collection $profiles): void
    {
        if ($profiles->isEmpty()) return;
        $values = $profiles->pluck('total_lifetime_value')->toArray();
        sort($values);
        $total = count($values);
        if ($total <= 1) {
            $profiles->each(fn($p) => $p->setAttribute('monetary_score', 3));
            return;
        }
        $quintile = max(1, (int) ceil($total / 5));
        foreach ($profiles as $p) {
            $rank = 0;
            foreach ($values as $v) { if ($v < ($p->total_lifetime_value ?? 0)) $rank++; }
            $p->setAttribute('monetary_score', min(5, 1 + (int) floor($rank / $quintile)));
        }
    }

    protected function determineSegment(int $rfmScore, GuestProfile $profile): string
    {
        if ($rfmScore >= 13) return 'vip';
        if ($rfmScore >= 10) return 'regular';
        if ($rfmScore >= 7) return 'potential';
        if ($profile->recency_score <= 2 && $profile->monetary_score >= 4) return 'hibernating';
        if ($profile->recency_score <= 1 && $profile->frequency_score <= 2) return 'lost';
        if ($profile->total_stays <= 1) return 'new';
        if ($profile->recency_score <= 2) return 'at_risk';
        return 'regular';
    }

    protected function executeSegmentActions(int $propertyId, ?Guest $guest, GuestProfile $profile): void
    {
        if (!$guest) return;
        $rules = RfmSegmentRule::where('property_id', $propertyId)
            ->where('segment_name', $profile->rfm_segment)
            ->where('is_active', true)
            ->get();

        foreach ($rules as $rule) {
            $actions = $rule->auto_actions ?? [];
            foreach ($actions as $action) {
                if ($action['type'] === 'tag_guest' && isset($action['tag'])) {
                    $tags = $guest->tags ?? [];
                    if (!in_array($action['tag'], $tags)) {
                        $tags[] = $action['tag'];
                        $guest->tags = $tags;
                        $guest->save();
                    }
                }
                if ($action['type'] === 'notify_staff') {
                    \App\Models\NotificationLog::create([
                        'property_id' => $propertyId,
                        'notifiable_type' => $guest->getMorphClass(),
                        'notifiable_id' => $guest->id,
                        'channel' => 'system',
                        'event' => 'rfm_segment_changed',
                        'recipient' => 'front_desk',
                        'status' => 'sent',
                        'metadata' => json_encode([
                            'guest_name' => $guest->getFullNameAttribute(),
                            'segment' => $profile->rfm_segment,
                            'rfm_score' => $profile->rfm_score,
                        ]),
                    ]);
                }
            }
        }
    }
}
