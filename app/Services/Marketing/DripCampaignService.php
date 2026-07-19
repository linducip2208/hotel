<?php

namespace App\Services\Marketing;

use App\Models\DripCampaign;
use App\Models\DripQueue;
use App\Models\DripStep;
use App\Models\Guest;
use App\Models\Reservation;

class DripCampaignService
{
    public function createCampaign(array $data, array $steps): DripCampaign
    {
        $campaign = DripCampaign::create([
            'property_id' => app('current_property')->id,
            'name' => $data['name'],
            'trigger_event' => $data['trigger_event'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        foreach ($steps as $i => $step) {
            DripStep::create([
                'drip_campaign_id' => $campaign->id,
                'delay_hours' => $step['delay_hours'],
                'channel' => $step['channel'] ?? 'whatsapp',
                'template_key' => $step['template_key'] ?? 'drip_' . ($i + 1),
                'subject' => $step['subject'] ?? null,
                'message' => $step['message'],
                'sort_order' => $i + 1,
            ]);
        }

        return $campaign->load('steps');
    }

    public function triggerCampaign(string $event, Reservation $reservation): void
    {
        $campaigns = DripCampaign::where('property_id', $reservation->property_id)
            ->where('trigger_event', $event)
            ->where('is_active', true)
            ->with('steps')
            ->get();

        if ($campaigns->isEmpty()) {
            return;
        }

        $guest = $reservation->primaryGuest;
        if (!$guest) {
            return;
        }

        foreach ($campaigns as $campaign) {
            foreach ($campaign->steps as $step) {
                DripQueue::create([
                    'property_id' => $reservation->property_id,
                    'drip_step_id' => $step->id,
                    'guest_id' => $guest->id,
                    'reservation_id' => $reservation->id,
                    'scheduled_at' => now()->addHours($step->delay_hours),
                    'status' => 'pending',
                ]);
            }
        }
    }

    public function processQueue(): array
    {
        $due = DripQueue::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->with(['dripStep', 'guest', 'reservation'])
            ->limit(50)
            ->get();

        $results = ['sent' => 0, 'failed' => 0];

        foreach ($due as $item) {
            try {
                $step = $item->dripStep;
                $guest = $item->guest;

                $message = $this->replacePlaceholders($step->message, $guest, $item->reservation);

                if ($step->channel === 'email') {
                    // Email via notification system
                    \Illuminate\Support\Facades\Mail::raw($message, function ($m) use ($guest, $step) {
                        $m->to($guest->email)
                            ->subject($step->subject ?? 'Pesan dari ' . config('app.name'));
                    });
                } else {
                    // WhatsApp via messaging service
                    $client = app(\App\Services\Communication\MessagingService::class);
                    if (method_exists($client, 'send')) {
                        $client->send($guest->phone, $message);
                    }
                }

                $item->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $results['sent']++;
            } catch (\Throwable $e) {
                $item->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
                $results['failed']++;
            }
        }

        return $results;
    }

    public function getQueueStats(int $propertyId): array
    {
        return [
            'pending' => DripQueue::where('property_id', $propertyId)->where('status', 'pending')->count(),
            'sent' => DripQueue::where('property_id', $propertyId)->where('status', 'sent')->count(),
            'failed' => DripQueue::where('property_id', $propertyId)->where('status', 'failed')->count(),
            'total' => DripQueue::where('property_id', $propertyId)->count(),
        ];
    }

    protected function replacePlaceholders(string $message, Guest $guest, ?Reservation $reservation): string
    {
        $replace = [
            '{guest_name}' => $guest->full_name,
            '{guest_first_name}' => $guest->first_name,
            '{guest_email}' => $guest->email,
            '{guest_phone}' => $guest->phone,
            '{property_name}' => app('current_property')->name ?? config('app.name'),
        ];

        if ($reservation) {
            $replace['{check_in}'] = $reservation->check_in?->format('d M Y') ?? '';
            $replace['{check_out}'] = $reservation->check_out?->format('d M Y') ?? '';
            $replace['{reservation_ref}'] = $reservation->ref;
            $replace['{nights}'] = (string) $reservation->nights;
        }

        return strtr($message, $replace);
    }
}
