<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ReservationCreated;
use App\Jobs\SendBookingConfirmationJob;
use App\Models\AuditLog;
use App\Models\Folio;
use App\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class CreateFolioForReservation implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public function handle(ReservationCreated $event): void
    {
        $reservation = $event->reservation;

        $folio = Folio::create([
            'property_id'    => $reservation->property_id,
            'reservation_id' => $reservation->id,
            'guest_id'       => $reservation->primary_guest_id,
            'folio_type'     => 'guest',
            'status'         => 'open',
            'opened_at'      => now(),
            'total_charges'  => 0,
            'total_payments' => 0,
            'balance'        => 0,
        ]);

        // Post room charges as initial folio charges
        $folio->charges()->create([
            'property_id'     => $reservation->property_id,
            'charge_date'     => $reservation->check_in,
            'description'     => 'Room Charges - Reservation #' . $reservation->ref,
            'amount'          => $reservation->total_room ?? 0,
            'tax_amount'      => $reservation->tax_total ?? 0,
            'is_taxable'      => true,
            'charge_category' => 'room',
        ]);

        $folio->recalculate();
    }
}
