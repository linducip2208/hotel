<?php

namespace App\Console\Commands\Channel;

use App\Models\Channel;
use App\Services\Channel\AriSyncService;
use Illuminate\Console\Command;

class FetchBookingsCommand extends Command
{
    protected $signature = 'channel:fetch-bookings';
    protected $description = 'Fetch incoming bookings from OTA channels';

    public function handle(AriSyncService $svc): int
    {
        Channel::where('is_active', true)->each(function (Channel $c) use ($svc) {
            try { $svc->fetchBookings($c); }
            catch (\Throwable $e) { $this->error("[$c->code] {$e->getMessage()}"); }
        });
        return self::SUCCESS;
    }
}
