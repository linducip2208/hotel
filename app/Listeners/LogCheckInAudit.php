<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCheckedIn;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogCheckInAudit implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationCheckedIn $event): void
    {
        $reservation = $event->reservation;

        AuditLog::create([
            'property_id'   => $reservation->property_id,
            'entity_type'   => 'reservation',
            'entity_id'     => $reservation->id,
            'action'        => 'checked_in',
            'description'   => sprintf(
                'Guest checked in for reservation #%s at %s',
                $reservation->ref,
                now()->toDateTimeString(),
            ),
            'metadata'      => [
                'reservation_ref' => $reservation->ref,
                'guest_id'        => $reservation->primary_guest_id,
                'checked_in_by'   => $event->checkedInByUserId,
            ],
        ]);
    }
}
