<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCancelled;
use App\Models\Folio;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class CancelFoliosForReservation implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationCancelled $event): void
    {
        $reservation = $event->reservation;

        $folios = Folio::where('reservation_id', $reservation->id)
            ->where('status', '!=', 'settled')
            ->get();

        foreach ($folios as $folio) {
            $folio->update([
                'status'    => 'voided',
                'closed_at' => now(),
            ]);

            // Void all open charges
            $folio->charges()->where('is_void', false)->update([
                'is_void' => true,
            ]);

            // Void all open payments
            $folio->payments()->where('is_void', false)->update([
                'is_void' => true,
            ]);

            $folio->recalculate();
        }
    }
}
