<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ChannelBookingReceived;
use App\Jobs\SyncAriToChannelsJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SyncInventoryAfterBooking implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ChannelBookingReceived $event): void
    {
        // After an OTA booking reduces inventory, push updated ARI to all channels
        SyncAriToChannelsJob::dispatch($event->reservation->property_id);
    }
}
