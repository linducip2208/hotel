<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\IcalService;

class IcalController extends Controller
{
    public function download(string $ref, IcalService $ical)
    {
        $reservation = Reservation::where('ref', $ref)->firstOrFail();

        $content = $ical->generate($reservation);

        return response($content, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$ical->filename($reservation).'"',
        ]);
    }
}
