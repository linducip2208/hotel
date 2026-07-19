<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCheckedIn;
use App\Jobs\BuildGuestProfileJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class UpdateGuestProfileVisit implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationCheckedIn $event): void
    {
        if ($event->reservation->primary_guest_id) {
            BuildGuestProfileJob::dispatch($event->reservation->primary_guest_id);
        }
    }
}
