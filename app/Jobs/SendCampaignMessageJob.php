<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Guest;
use App\Models\MarketingCampaign;
use App\Models\MessageThread;
use App\Services\Communication\MessagingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class SendCampaignMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $backoff = 30;

    public function __construct(
        public int $campaignId,
        public int $guestId,
    ) {}

    public function handle(MessagingService $messaging): void
    {
        $campaign = MarketingCampaign::find($this->campaignId);
        if (! $campaign || $campaign->status === 'paused') {
            $this->release(60);
            return;
        }

        $guest = Guest::find($this->guestId);
        if (! $guest) {
            return;
        }

        $subject = $campaign->subject ?? ($campaign->template?->subject ?? 'Special Offer');
        $body = $campaign->body ?? $campaign->template?->body ?? '';

        if (empty(trim($body))) {
            return;
        }

        // Personalize message
        $body = str_replace(
            ['{guest_name}', '{first_name}', '{last_name}'],
            [$guest->full_name, $guest->first_name, $guest->last_name ?? ''],
            $body
        );

        try {
            $channel = $campaign->channel;

            if (in_array($channel, ['email', 'both'])) {
                $thread = MessageThread::firstOrCreate([
                    'property_id' => $campaign->property_id,
                    'guest_id' => $guest->id,
                    'channel' => 'email',
                ], ['last_message_at' => now()]);

                $messaging->reply($thread, $body, null);
            }

            if (in_array($channel, ['whatsapp', 'both'])) {
                if ($guest->phone) {
                    $thread = MessageThread::firstOrCreate([
                        'property_id' => $campaign->property_id,
                        'guest_id' => $guest->id,
                        'channel' => 'whatsapp',
                    ], ['last_message_at' => now()]);

                    $messaging->reply($thread, $body, null);
                }
            }

            MarketingCampaign::where('id', $this->campaignId)->increment('sent_count');

        } catch (\Throwable $e) {
            Log::error("Campaign message failed: {$e->getMessage()}", [
                'campaign_id' => $this->campaignId,
                'guest_id' => $this->guestId,
            ]);

            if ($this->attempts() >= $this->tries) {
                Log::error("Campaign message permanently failed after {$this->tries} attempts.");
                return;
            }

            throw $e;
        }
    }
}
