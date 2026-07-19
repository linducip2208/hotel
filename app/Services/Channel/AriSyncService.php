<?php

namespace App\Services\Channel;

use App\Adapters\Channel\BaseChannelAdapter;
use App\Models\AriSyncLog;
use App\Models\Channel;
use Illuminate\Support\Facades\Log;

class AriSyncService
{
    protected function adapters(): array
    {
        return config('integrations.channel_adapters', []);
    }

    public function adapter(Channel $channel): BaseChannelAdapter
    {
        $class = $this->adapters()[$channel->code] ?? \App\Adapters\Channel\BookingComAdapter::class;
        return new $class($channel);
    }

    public function pushAri(Channel $channel, array $updates): array
    {
        $log = AriSyncLog::create([
            'channel_id' => $channel->id,
            'operation' => 'push_availability',
            'status' => 'running',
            'started_at' => now(),
            'payload_summary' => ['count' => count($updates)],
        ]);

        try {
            $result = $this->adapter($channel)->pushAvailability($updates);
            $log->update([
                'status' => $result['ok'] ? 'success' : 'failed',
                'finished_at' => now(),
                'response_summary' => $result,
                'error' => $result['ok'] ? null : ($result['message'] ?? 'unknown'),
            ]);
            return $result;
        } catch (\Throwable $e) {
            Log::channel('channel-manager')->error('ARI push failed', ['error' => $e->getMessage()]);
            $log->update(['status' => 'failed', 'finished_at' => now(), 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function fetchBookings(Channel $channel): array
    {
        $log = AriSyncLog::create(['channel_id' => $channel->id, 'operation' => 'fetch_bookings', 'status' => 'running', 'started_at' => now()]);
        try {
            $result = $this->adapter($channel)->fetchBookings($channel->last_sync_at);
            $log->update(['status' => 'success', 'finished_at' => now(), 'response_summary' => ['count' => count($result['bookings'] ?? [])]]);
            $channel->update(['last_sync_at' => now(), 'last_sync_status' => 'ok']);
            return $result;
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'finished_at' => now(), 'error' => $e->getMessage()]);
            $channel->update(['last_sync_status' => 'failed']);
            throw $e;
        }
    }
}
