<?php

namespace App\Services\Compliance;

use App\Models\Property;
use App\Models\Reservation;
use App\Models\WnaLog;
use Carbon\Carbon;

class WnaReporter
{
    /** Capture WNA arrival saat check-in. */
    public function captureFromReservation(Reservation $reservation): void
    {
        $guest = $reservation->primaryGuest;
        if (! $guest || $guest->country === 'ID' || $guest->id_type !== 'passport') {
            return;
        }

        WnaLog::firstOrCreate(
            ['reservation_id' => $reservation->id, 'guest_id' => $guest->id],
            [
                'property_id' => $reservation->property_id,
                'check_in_date' => $reservation->check_in,
                'check_out_date' => $reservation->check_out,
                'passport_no' => $guest->id_number,
                'nationality' => $guest->nationality ?? $guest->country,
                'passport_expires_at' => $guest->id_expires_at,
            ]
        );
    }

    /** Generate monthly WNA report (CSV format) for upload to imigrasi portal. */
    public function exportMonth(Property $property, int $year, int $month): string
    {
        $rows = WnaLog::where('property_id', $property->id)
            ->whereYear('check_in_date', $year)
            ->whereMonth('check_in_date', $month)
            ->with('guest')->get();

        $out = ["passport_no,nationality,name,check_in,check_out,property"];
        foreach ($rows as $r) {
            $g = $r->guest;
            $out[] = sprintf('%s,%s,"%s",%s,%s,"%s"',
                $r->passport_no, $r->nationality,
                trim($g->first_name.' '.$g->last_name),
                $r->check_in_date->toDateString(), $r->check_out_date->toDateString(),
                $property->name
            );
        }
        return implode("\n", $out);
    }
}
