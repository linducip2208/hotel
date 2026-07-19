<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DigitalRegistration;
use App\Services\Fo\DigitalRegistrationService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function show(string $token)
    {
        $reg = DigitalRegistration::where('token', $token)->firstOrFail();

        $service = app(DigitalRegistrationService::class);
        $service->recordView($reg, request()->ip(), request()->userAgent());

        $guest    = $reg->guest;
        $property = $reg->property;

        return view('public.registration-form', compact('reg', 'guest', 'property'));
    }

    public function submit(Request $request, string $token)
    {
        $reg = DigitalRegistration::where('token', $token)->firstOrFail();

        $data = $request->validate([
            'full_name'    => 'required|string|max:200',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:100',
            'id_number'    => 'required|string|max:50',
            'id_type'      => 'required|string|in:KTP,SIM,PASSPORT,KITAS',
            'nationality'  => 'required|string|max:100',
            'vehicle_plate'=> 'nullable|string|max:20',
            'special_requests' => 'nullable|string|max:500',
            'signature'    => 'required|string',
            'id_document'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'agreement'    => 'required|accepted',
        ]);

        $idDocumentPath = null;
        if ($request->hasFile('id_document')) {
            $idDocumentPath = $request->file('id_document')->store('id-documents', 'public');
        }

        $formData = [
            'full_name'        => $data['full_name'],
            'phone'            => $data['phone'] ?? null,
            'email'            => $data['email'] ?? null,
            'id_number'        => $data['id_number'],
            'id_type'          => $data['id_type'],
            'nationality'      => $data['nationality'],
            'vehicle_plate'    => $data['vehicle_plate'] ?? null,
            'special_requests' => $data['special_requests'] ?? null,
        ];

        $service = app(DigitalRegistrationService::class);
        $service->sign($reg, $formData, $data['signature'], $idDocumentPath);

        return redirect()->route('registration.thanks');
    }

    public function thanks()
    {
        return view('public.registration-thanks');
    }
}
