<?php

namespace App\Http\Controllers\Panel\Compliance;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Reservation;
use Illuminate\Http\Request;

class PrivacyController extends Controller
{
    public function index(Request $request)
    {
        $guests = collect();
        if ($request->filled('search')) {
            $guests = Guest::where('property_id', app('current_property')->id)
                ->where(function ($q) use ($request) {
                    $q->where('first_name', 'like', "%{$request->search}%")
                      ->orWhere('last_name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%")
                      ->orWhere('phone', 'like', "%{$request->search}%");
                })
                ->withCount('reservations')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return view('panel.compliance.privacy', compact('guests'));
    }

    public function consentLog($id)
    {
        $guest = Guest::where('property_id', app('current_property')->id)->findOrFail($id);
        return response()->json([
            'guest_id' => $guest->id,
            'name' => $guest->first_name . ' ' . ($guest->last_name ?? ''),
            'marketing_consent' => $guest->marketing_consent,
            'data_sharing_consent' => $guest->data_sharing_consent ?? false,
            'forgotten_at' => $guest->forgotten_at,
            'created_at' => $guest->created_at,
            'updated_at' => $guest->updated_at,
        ]);
    }

    public function dataRetention(Request $request)
    {
        $retentionDays = 365;
        $guests = Guest::where('property_id', app('current_property')->id)
            ->where('created_at', '<', now()->subDays($retentionDays))
            ->whereNull('forgotten_at')
            ->withCount('reservations')
            ->paginate(20);

        return view('panel.compliance.privacy', compact('guests'));
    }

    public function export($id)
    {
        $guest = Guest::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $guest->toArray();
        $data['reservations'] = $guest->reservations()->with('rooms', 'folios')->get()->toArray();
        $data['reviews'] = \App\Models\Review::whereHas('reservation', fn($q) => $q->whereIn('id',
            \App\Models\ReservationGuest::where('guest_id', $guest->id)->pluck('reservation_id')
        ))->get()->toArray();

        $filename = 'data-tamu-' . $guest->id . '-' . now()->format('Ymd') . '.json';
        return response(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function anonymize($id)
    {
        $guest = Guest::where('property_id', app('current_property')->id)->findOrFail($id);
        $guest->update([
            'first_name' => 'Anonim',
            'last_name' => 'Guest-' . $guest->id,
            'email' => null,
            'phone' => null,
            'address_line1' => null,
            'city' => null,
            'province' => null,
            'postal_code' => null,
            'id_number' => null,
            'id_photo_path' => null,
            'date_of_birth' => null,
            'preferences' => null,
            'notes' => null,
            'marketing_consent' => false,
            'forgotten_at' => now(),
        ]);

        return back()->with('success', 'Data tamu berhasil dianonimkan (right-to-be-forgotten).');
    }
}
