<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\DigitalRegistration;
use App\Models\Reservation;
use App\Services\Fo\DigitalRegistrationService;
use Illuminate\Http\Request;

class DigitalRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $propertyId = app('current_property')->id;

        $registrations = DigitalRegistration::where('property_id', $propertyId)
            ->with(['reservation.primaryGuest', 'guest'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('search'), fn ($q, $search) => $q->whereHas('reservation', fn ($r) => $r->where('ref', 'like', "%{$search}%"))->orWhereHas('guest', fn ($g) => $g->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")))
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('panel.fo.digital-registrations', compact('registrations'));
    }

    public function show(int $id)
    {
        $reg = DigitalRegistration::where('property_id', app('current_property')->id)
            ->with(['reservation.primaryGuest', 'guest'])
            ->findOrFail($id);

        return view('panel.fo.digital-registration-detail', compact('reg'));
    }

    public function send(int $id)
    {
        $reg = DigitalRegistration::where('property_id', app('current_property')->id)->findOrFail($id);

        $service = app(DigitalRegistrationService::class);
        $service->sendToGuest($reg);

        return back()->with('success', 'Link registrasi digital telah dikirim ke tamu.');
    }

    public function complete(int $id)
    {
        $reg = DigitalRegistration::where('property_id', app('current_property')->id)->findOrFail($id);

        $service = app(DigitalRegistrationService::class);
        $service->markCompleted($reg);

        return back()->with('success', 'Registrasi digital ditandai selesai.');
    }

    public function createForReservation(int $reservationId)
    {
        $reservation = Reservation::where('property_id', app('current_property')->id)
            ->with(['primaryGuest', 'rooms.roomType'])
            ->findOrFail($reservationId);

        $service = app(DigitalRegistrationService::class);
        $reg = $service->createForReservation($reservation);

        return redirect()->route('panel.fo.digital-registrations.show', $reg->id)
            ->with('success', 'Registrasi digital berhasil dibuat.');
    }
}
