<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCreated;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogReservationAudit implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationCreated $event): void
    {
        $reservation = $event->reservation;

        AuditLog::create([
            'property_id'   => $reservation->property_id,
            'entity_type'   => 'reservation',
            'entity_id'     => $reservation->id,
            'action'        => 'created',
            'description'   => sprintf(
                'Reservation #%s created for guest ID %d, check-in %s',
                $reservation->ref,
                $reservation->primary_guest_id,
                $reservation->check_in?->toDateString(),
            ),
            'metadata'      => [
                'reservation_ref'  => $reservation->ref,
                'guest_id'         => $reservation->primary_guest_id,
                'check_in'         => $reservation->check_in?->toDateString(),
                'check_out'        => $reservation->check_out?->toDateString(),
                'grand_total'      => $reservation->grand_total,
                'status'           => $reservation->status,
            ],
        ]);
    }
}
