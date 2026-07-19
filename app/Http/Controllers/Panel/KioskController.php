<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\Fo\KioskService;

class KioskController extends Controller
{
    public function sessions(KioskService $service)
    {
        $property = app('current_property');
        $sessions = $service->getActiveSessions($property->id);
        return view('panel.kiosk.sessions', compact('property', 'sessions'));
    }

    public function showSession(KioskService $service, $id)
    {
        $session = \App\Models\KioskSession::with('reservation.primaryGuest', 'guest')->findOrFail($id);
        return view('panel.kiosk.show', compact('session'));
    }
}
