<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCheckedOut;
use App\Jobs\SendPostStayFollowupJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendPostStaySurvey implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationCheckedOut $event): void
    {
        // Schedule survey 24h after checkout
        SendPostStayFollowupJob::dispatch($event->reservation->id)
            ->delay(now()->addHours(24));
    }
}
