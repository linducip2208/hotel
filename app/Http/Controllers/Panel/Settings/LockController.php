<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Reservation;
use App\Services\Lock\LockService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class LockController extends Controller
{
    public function __construct(protected LockService $svc) {}

    public function index()
    {
        $propertyId = app('current_property')->id;
        $providers = \App\Models\Provider::where('property_id', $propertyId)
            ->where('api_format', 'door_lock')->get();
        $rooms = Room::where('property_id', $propertyId)->where('is_active', true)->with('roomType')->paginate(25);

        return view('panel.settings.locks.index', compact('providers', 'rooms'));
    }

    public function configure(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'vendor' => 'required|in:salto,onity,vingcard,dormakaba,miwa',
            'base_url' => 'required|url',
            'api_key' => 'required|string',
            'facility_code' => 'nullable|string',
            'hotel_code' => 'nullable|string',
            'property_key' => 'nullable|string',
            'hotel_id' => 'nullable|string',
        ]);

        \App\Models\Provider::updateOrCreate(
            [
                'property_id' => app('current_property')->id,
                'api_format' => 'door_lock',
            ],
            [
                'name' => $data['name'],
                'base_url' => $data['base_url'],
                'api_key_encrypted' => encrypt($data['api_key']),
                'extra_headers' => array_filter([
                    'vendor' => $data['vendor'],
                    'facility_code' => $data['facility_code'] ?? null,
                    'hotel_code' => $data['hotel_code'] ?? null,
                    'property_key' => $data['property_key'] ?? null,
                    'hotel_id' => $data['hotel_id'] ?? null,
                ]),
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Door lock provider configured.');
    }

    public function test(Request $request): JsonResponse
    {
        try {
            $room = Room::where('property_id', app('current_property')->id)
                ->where('is_active', true)->firstOrFail();

            $status = $this->svc->getStatus($room);

            return response()->json(['ok' => true, 'status' => $status]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function issueKey(Request $request, int $roomId): JsonResponse
    {
        $request->validate([
            'guest_id' => 'required|integer',
            'reservation_id' => 'required|integer',
            'mobile_key' => 'nullable|boolean',
        ]);

        $room = Room::where('property_id', app('current_property')->id)->findOrFail($roomId);
        $guest = \App\Models\Guest::findOrFail($request->input('guest_id'));
        $reservation = Reservation::findOrFail($request->input('reservation_id'));

        try {
            $result = $this->svc->issueKey($room, $guest, $reservation, [
                'mobile_key' => $request->boolean('mobile_key'),
            ]);

            return response()->json(['ok' => true, 'key' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function revokeKey(Request $request, int $roomId): JsonResponse
    {
        $request->validate(['key_id' => 'nullable|string']);

        $room = Room::where('property_id', app('current_property')->id)->findOrFail($roomId);

        try {
            $result = $this->svc->revokeKey($room, $request->input('key_id'));
            return response()->json(['ok' => true, 'revoked' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
