<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ChannelBookingReceived;
use App\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class CreateReservationFromChannel implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ChannelBookingReceived $event): void
    {
        $reservation = $event->reservation;
        $payload = $event->rawPayload;

        // Ingestion already happened (reservation model is passed).
        // Post-processing: map OTA-specific fields, attach virtual card, etc.
        if (! empty($payload['ota_virtual_card'] ?? null)) {
            $reservation->otaVirtualCard()->create([
                'property_id'    => $reservation->property_id,
                'card_data'      => $payload['ota_virtual_card'],
                'expires_at'     => now()->addDays(30),
            ]);
        }
    }
}
