<?php

namespace App\Jobs;

use App\Models\Channel;
use App\Models\Inventory;
use App\Models\Rate;
use App\Services\Channel\AriSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAriToChannelsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 120;
    public int $timeout = 120;

    public function __construct(
        public int $channelId,
        public string $fromDate,
        public string $toDate
    ) {}

    public function handle(AriSyncService $svc): void
    {
        $channel = Channel::with('mappings')->findOrFail($this->channelId);

        if (! $channel->is_active) {
            return;
        }

        $updates = [];

        foreach ($channel->mappings as $mapping) {
            $inventories = Inventory::where('property_id', $channel->property_id)
                ->where('room_type_id', $mapping->room_type_id)
                ->whereBetween('date', [$this->fromDate, $this->toDate])
                ->get();

            foreach ($inventories as $inv) {
                $updates[] = [
                    'channel_room_id' => $mapping->channel_room_id,
                    'date'            => $inv->date->toDateString(),
                    'available'       => max(0, $inv->total - $inv->sold - $inv->blocked - $inv->out_of_order),
                ];
            }
        }

        if (! empty($updates)) {
            $svc->pushAri($channel, $updates);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("ARI sync failed", ['channel' => $this->channelId, 'error' => $e->getMessage()]);
    }
}
