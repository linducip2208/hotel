<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\KeycardInventory;
use App\Models\KeycardType;
use App\Models\Reservation;
use App\Services\Fo\KeycardService;
use Illuminate\Http\Request;

class KeycardController extends Controller
{
    protected KeycardService $service;

    public function __construct(KeycardService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $propertyId = app('current_property')->id;
        $overview = $this->service->getInventoryOverview($propertyId);
        $availableCards = $this->service->getAvailableCards($propertyId);
        $activeAssignments = $this->service->getActiveAssignments($propertyId);
        $types = $this->service->getTypes($propertyId);
        $reservations = Reservation::where('property_id', $propertyId)
            ->whereIn('status', ['checked_in', 'confirmed'])
            ->with('primaryGuest')
            ->orderBy('check_in', 'desc')
            ->limit(50)
            ->get();

        return view('panel.fo.keycards', compact(
            'overview', 'availableCards', 'activeAssignments', 'types', 'reservations'
        ));
    }

    public function issue(Request $request)
    {
        $data = $request->validate([
            'card_id' => 'required|exists:keycard_inventory,id',
            'reservation_id' => 'required|exists:reservations,id',
            'room_id' => 'nullable|exists:rooms,id',
            'guest_id' => 'nullable|exists:guests,id',
        ]);

        try {
            $card = $this->service->issue(
                $data['card_id'],
                $data['reservation_id'],
                $data['room_id'] ?? null,
                $data['guest_id'] ?? null
            );
            return back()->with('success', "Kartu {$card->card_number} berhasil dikeluarkan.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function return(Request $request, int $id)
    {
        $propertyId = app('current_property')->id;
        $card = KeycardInventory::where('property_id', $propertyId)->findOrFail($id);

        try {
            $card = $this->service->returnCard($card->id);
            return back()->with('success', "Kartu {$card->card_number} berhasil dikembalikan.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function lost(Request $request, int $id)
    {
        $propertyId = app('current_property')->id;
        $card = KeycardInventory::where('property_id', $propertyId)->findOrFail($id);
        $this->service->markLost($card->id);

        return back()->with('success', "Kartu {$card->card_number} ditandai hilang.");
    }

    public function types(Request $request)
    {
        $propertyId = app('current_property')->id;

        if ($request->isMethod('get')) {
            $types = $this->service->getTypes($propertyId);
            return view('panel.fo.keycard-types', compact('types'));
        }

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'encoding_type' => 'required|string|max:50',
                'color' => 'nullable|string|max:50',
                'is_active' => 'sometimes|boolean',
            ]);

            KeycardType::create([
                'property_id' => $propertyId,
                'name' => $data['name'],
                'encoding_type' => $data['encoding_type'],
                'color' => $data['color'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return back()->with('success', 'Tipe kartu berhasil ditambahkan.');
        }

        if ($request->isMethod('put') || $request->isMethod('patch')) {
            $type = KeycardType::where('property_id', $propertyId)->findOrFail(request('id'));
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'encoding_type' => 'sometimes|string|max:50',
                'color' => 'nullable|string|max:50',
                'is_active' => 'sometimes|boolean',
            ]);
            $type->update($data);
            return back()->with('success', 'Tipe kartu berhasil diperbarui.');
        }

        if ($request->isMethod('delete')) {
            $type = KeycardType::where('property_id', $propertyId)->findOrFail(request('id'));
            $type->delete();
            return back()->with('success', 'Tipe kartu berhasil dihapus.');
        }

        return abort(405);
    }
}
