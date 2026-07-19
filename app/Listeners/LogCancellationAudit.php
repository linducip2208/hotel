<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCancelled;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogCancellationAudit implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationCancelled $event): void
    {
        $reservation = $event->reservation;

        AuditLog::create([
            'property_id'   => $reservation->property_id,
            'entity_type'   => 'reservation',
            'entity_id'     => $reservation->id,
            'action'        => 'cancelled',
            'description'   => sprintf(
                'Reservation #%s cancelled. Reason: %s',
                $reservation->ref,
                $event->reason ?? 'N/A',
            ),
            'metadata'      => [
                'reservation_ref'   => $reservation->ref,
                'cancellation_fee'  => $reservation->cancellation_penalty,
                'reason'            => $event->reason,
                'cancelled_by'      => $event->cancelledByUserId,
                'was_checked_in'    => $reservation->checked_in_at !== null,
            ],
        ]);
    }
}
