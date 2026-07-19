<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCheckedOut;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogCheckOutAudit implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ReservationCheckedOut $event): void
    {
        $reservation = $event->reservation;

        AuditLog::create([
            'property_id'   => $reservation->property_id,
            'entity_type'   => 'reservation',
            'entity_id'     => $reservation->id,
            'action'        => 'checked_out',
            'description'   => sprintf(
                'Guest checked out for reservation #%s at %s',
                $reservation->ref,
                now()->toDateTimeString(),
            ),
            'metadata'      => [
                'reservation_ref' => $reservation->ref,
                'guest_id'        => $reservation->primary_guest_id,
                'checked_out_by'  => $event->checkedOutByUserId,
                'total_revenue'   => $reservation->grand_total,
                'nights'          => $reservation->check_in
                    ? $reservation->check_in->diffInDays($reservation->check_out)
                    : 0,
            ],
        ]);
    }
}
