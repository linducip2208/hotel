<?php

namespace App\Services\Revenue;

use App\Models\UpsellCampaign;
use App\Models\UpsellCampaignLog;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\UpsellOffer;
use App\Models\UpsellPresentation;
use App\Models\NotificationLog;
use Carbon\Carbon;

class UpsellPreArrivalService
{
    public function runCampaign(int $campaignId): array
    {
        $campaign = UpsellCampaign::with('property')->findOrFail($campaignId);
        if ($campaign->status !== 'active') return ['error' => 'Campaign not active'];

        $offerIds = $campaign->offer_ids ?? [];
        $offers = UpsellOffer::whereIn('id', $offerIds)->where('is_active', true)->get();
        if ($offers->isEmpty()) return ['error' => 'No active offers'];

        $targetDate = today()->addDays($campaign->days_before_arrival);
        $targetDateStr = $targetDate->toDateString();

        $reservations = Reservation::where('property_id', $campaign->property_id)
            ->where('check_in', $targetDateStr)
            ->where('status', 'confirmed')
            ->where('pre_checkin_complete', false)
            ->with('primaryGuest')
            ->get();

        $sent = 0;
        foreach ($reservations as $res) {
            $guest = $res->primaryGuest;
            if (!$guest) continue;

            if (!$this->matchesFilter($guest, $res, $campaign->guest_filters ?? [])) continue;

            $alreadySent = UpsellCampaignLog::where('upsell_campaign_id', $campaignId)
                ->where('reservation_id', $res->id)
                ->exists();
            if ($alreadySent) continue;

            $this->sendOffer($campaign, $res, $guest, $offers);
            $sent++;
        }

        $campaign->increment('sent_count', $sent);
        if ($campaign->started_at === null) {
            $campaign->started_at = now();
        }
        $campaign->save();

        return ['sent' => $sent, 'campaign' => $campaign->name];
    }

    protected function matchesFilter(Guest $guest, Reservation $res, array $filters): bool
    {
        if (empty($filters)) return true;

        if (isset($filters['tier']) && $filters['tier'] !== 'all') {
            if (($guest->is_vip && $filters['tier'] !== 'vip') ||
                (!$guest->is_vip && $filters['tier'] === 'vip')) {
                return false;
            }
        }

        if (isset($filters['nationality']) && $filters['nationality'] !== 'all') {
            if ($guest->nationality !== $filters['nationality']) return false;
        }

        if (isset($filters['min_lifetime_value'])) {
            $profile = $guest->profile;
            if (!$profile || $profile->total_lifetime_value < (float) $filters['min_lifetime_value']) {
                return false;
            }
        }

        return true;
    }

    protected function sendOffer(UpsellCampaign $campaign, Reservation $res, Guest $guest, $offers): void
    {
        $channel = $campaign->channel;
        $guestContact = $channel === 'whatsapp' ? $guest->phone : $guest->email;
        if (!$guestContact) return;

        $offerList = [];
        foreach ($offers as $offer) {
            $offerList[] = "• {$offer->name} — Rp " . number_format($offer->price, 0, ',', '.');
            UpsellPresentation::create([
                'property_id' => $campaign->property_id,
                'reservation_id' => $res->id,
                'upsell_offer_id' => $offer->id,
                'status' => 'offered',
                'offered_at' => now(),
                'price_offered' => $offer->price,
            ]);
        }

        $message = "Halo {$guest->first_name},\n\n" .
            "Check-in Anda di " . config('app.name') . " tanggal {$res->check_in->format('d M Y')}.\n\n" .
            "Tingkatkan pengalaman Anda:\n" . implode("\n", $offerList) . "\n\n" .
            "Balas pesan ini untuk menerima penawaran.";

        $channelSent = $channel === 'both' ? ['whatsapp', 'email'] : [$channel];

        foreach ($channelSent as $ch) {
            UpsellCampaignLog::create([
                'upsell_campaign_id' => $campaign->id,
                'property_id' => $campaign->property_id,
                'reservation_id' => $res->id,
                'guest_id' => $guest->id,
                'channel' => $ch,
                'status' => 'sent',
                'sent_at' => now(),
                'raw_response' => $message,
            ]);

            NotificationLog::create([
                'property_id' => $campaign->property_id,
                'notifiable_type' => Reservation::class,
                'notifiable_id' => $res->id,
                'channel' => $ch,
                'event' => 'upsell_pre_arrival',
                'recipient' => $ch === 'whatsapp' ? $guest->phone : $guest->email,
                'status' => 'sent',
                'metadata' => json_encode([
                    'campaign_id' => $campaign->id,
                    'offer_ids' => $offers->pluck('id')->toArray(),
                    'message' => $message,
                ]),
            ]);
        }
    }

    public function runAllActiveCampaigns(): array
    {
        $results = [];
        $campaigns = UpsellCampaign::where('status', 'active')->get();

        foreach ($campaigns as $campaign) {
            $results[] = $this->runCampaign($campaign->id);
        }

        return $results;
    }

    public function recordAcceptance(int $presentationId): void
    {
        $presentation = UpsellPresentation::findOrFail($presentationId);
        $presentation->update([
            'status' => 'accepted',
            'responded_at' => now(),
            'price_accepted' => $presentation->price_offered,
        ]);

        // Update campaign stats
        $log = UpsellCampaignLog::where('reservation_id', $presentation->reservation_id)
            ->latest()
            ->first();
        if ($log) {
            $log->update(['status' => 'accepted', 'responded_at' => now()]);
            $campaign = UpsellCampaign::find($log->upsell_campaign_id);
            if ($campaign) {
                $campaign->increment('accepted_count');
                $campaign->increment('revenue_generated', $presentation->price_offered);
            }
        }
    }
}
