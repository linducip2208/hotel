<?php

namespace App\Console\Commands\Channel;

use App\Models\Channel;
use App\Services\Channel\AriSyncService;
use Illuminate\Console\Command;

class SyncAriCommand extends Command
{
    protected $signature = 'channel:sync-ari';
    protected $description = 'Push pending ARI updates to all active channels';

    public function handle(AriSyncService $svc): int
    {
        Channel::where('is_active', true)->each(function (Channel $c) use ($svc) {
            try { $svc->pushAri($c, []); }
            catch (\Throwable $e) { $this->error("[$c->code] {$e->getMessage()}"); }
        });
        return self::SUCCESS;
    }
}
