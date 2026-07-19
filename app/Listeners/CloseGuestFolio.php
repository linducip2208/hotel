<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCheckedOut;
use App\Models\Folio;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class CloseGuestFolio implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationCheckedOut $event): void
    {
        $reservation = $event->reservation;

        Folio::where('reservation_id', $reservation->id)
            ->where('status', 'open')
            ->each(function (Folio $folio): void {
                $folio->recalculate();
                if ((float) $folio->balance <= 0) {
                    $folio->update([
                        'status'    => 'settled',
                        'closed_at' => now(),
                    ]);
                }
            });
    }
}
