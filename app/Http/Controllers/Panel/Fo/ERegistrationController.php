<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\ERegistrationCard;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ERegistrationController extends Controller
{
    public function index(Request $request)
    {
        $propertyId = app('current_property')->id;

        $cards = ERegistrationCard::where('property_id', $propertyId)
            ->with(['reservation.primaryGuest', 'guest', 'verifiedByStaff'])
            ->when($request->query('date'), fn ($q, $d) => $q->whereDate('created_at', $d))
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('panel.fo.e-registration.index', compact('cards'));
    }

    public function show(int $id)
    {
        $card = ERegistrationCard::where('property_id', app('current_property')->id)
            ->with(['reservation.primaryGuest', 'guest', 'verifiedByStaff'])
            ->findOrFail($id);

        return view('panel.fo.e-registration.show', compact('card'));
    }

    public function create(int $reservationId)
    {
        $reservation = Reservation::where('property_id', app('current_property')->id)
            ->with(['primaryGuest', 'rooms.roomType'])
            ->findOrFail($reservationId);

        $existingCard = ERegistrationCard::where('reservation_id', $reservationId)->first();

        return view('panel.fo.e-registration.form', compact('reservation', 'existingCard'));
    }

    public function store(Request $request, int $reservationId)
    {
        $reservation = Reservation::where('property_id', app('current_property')->id)
            ->with(['primaryGuest'])
            ->findOrFail($reservationId);

        $data = $request->validate([
            'full_name'         => 'required|string|max:200',
            'id_type'           => 'required|string|in:KTP,PASSPORT,SIM,KITAS',
            'id_number'         => 'required|string|max:50',
            'nationality'       => 'required|string|max:100',
            'date_of_birth'     => 'required|date',
            'address'           => 'nullable|string|max:500',
            'phone'             => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:100',
            'vehicle_plate'     => 'nullable|string|max:20',
            'purpose_of_stay'   => 'nullable|string|max:200',
            'next_destination'  => 'nullable|string|max:200',
            'signature_image'   => 'nullable|string',
        ]);

        // Handle signature base64 PNG upload
        $signaturePath = null;
        if (! empty($data['signature_image']) && str_starts_with($data['signature_image'], 'data:image')) {
            $image = $data['signature_image'];
            $image = substr($image, strpos($image, ',') + 1);
            $image = base64_decode($image);
            $filename = 'signatures/sig-'.$reservationId.'-'.time().'.png';
            Storage::disk('public')->put($filename, $image);
            $signaturePath = $filename;
        }

        $submittedData = [
            'full_name'        => $data['full_name'],
            'id_type'          => $data['id_type'],
            'id_number'        => $data['id_number'],
            'nationality'      => $data['nationality'],
            'date_of_birth'    => $data['date_of_birth'],
            'address'          => $data['address'] ?? null,
            'phone'            => $data['phone'] ?? null,
            'email'            => $data['email'] ?? null,
            'vehicle_plate'    => $data['vehicle_plate'] ?? null,
            'purpose_of_stay'  => $data['purpose_of_stay'] ?? null,
            'next_destination' => $data['next_destination'] ?? null,
        ];

        $card = ERegistrationCard::updateOrCreate(
            ['reservation_id' => $reservationId],
            [
                'property_id'           => app('current_property')->id,
                'guest_id'              => $reservation->primary_guest_id,
                'signed_at'             => now(),
                'signature_image_path'  => $signaturePath,
                'submitted_data'        => $submittedData,
                'ip_address'            => $request->ip(),
                'user_agent'            => $request->userAgent(),
            ]
        );

        return redirect()->route('panel.fo.e-registration.show', $card->id)
            ->with('success', 'E-Registration completed successfully.');
    }

    public function verify(int $id)
    {
        $card = ERegistrationCard::where('property_id', app('current_property')->id)->findOrFail($id);

        $card->update([
            'is_verified'         => true,
            'verified_by_staff_id' => auth()->id(),
        ]);

        return back()->with('success', 'E-Registration card verified.');
    }

    public function reject(int $id, Request $request)
    {
        $card = ERegistrationCard::where('property_id', app('current_property')->id)->findOrFail($id);

        $reason = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $card->update([
            'is_verified' => false,
        ]);

        return back()->with('error', 'E-Registration rejected: '.$reason['reason']);
    }
}
