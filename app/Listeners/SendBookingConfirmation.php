<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCreated;
use App\Jobs\SendBookingConfirmationJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendBookingConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(ReservationCreated $event): void
    {
        SendBookingConfirmationJob::dispatch($event->reservation->id);
    }
}
