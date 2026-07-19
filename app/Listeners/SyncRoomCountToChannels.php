<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\NightAuditCompleted;
use App\Jobs\SyncAriToChannelsJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SyncRoomCountToChannels implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(NightAuditCompleted $event): void
    {
        SyncAriToChannelsJob::dispatch($event->nightAudit->property_id);
    }
}
