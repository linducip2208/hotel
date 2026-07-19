<?php

namespace App\Http\Controllers\Panel\Guest;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Reservation;
use App\Services\Guest\PreferenceLearningService;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    protected PreferenceLearningService $learning;

    public function __construct(PreferenceLearningService $learning)
    {
        $this->learning = $learning;
    }

    public function show(Request $request, int $guest)
    {
        $propertyId = app('current_property')->id;
        $guest = Guest::where('property_id', $propertyId)->findOrFail($guest);
        $preferences = $this->learning->getPreferences($guest);

        $recentReservations = Reservation::where('primary_guest_id', $guest->id)
            ->whereIn('status', ['checked_out', 'checked_in', 'confirmed'])
            ->latest('check_in')
            ->limit(10)
            ->get();

        return view('panel.guests.preferences', compact('guest', 'preferences', 'recentReservations'));
    }

    public function update(Request $request, int $guest)
    {
        $propertyId = app('current_property')->id;
        $guest = Guest::where('property_id', $propertyId)->findOrFail($guest);

        $data = $request->validate([
            'auto_apply' => 'sometimes|boolean',
            'overrides' => 'sometimes|array',
            'overrides.*.key' => 'required|string',
            'overrides.*.value' => 'nullable',
        ]);

        $existing = $guest->preferences ?? [];

        if ($request->has('auto_apply')) {
            $existing['auto_apply'] = (bool) $request->input('auto_apply');
        }

        if ($request->has('overrides')) {
            foreach ($request->input('overrides', []) as $override) {
                $key = $override['key'];
                $existing[$key] = [
                    'value' => $override['value'] ?? null,
                    'confidence' => 100,
                    'stays' => 1,
                    'last_updated' => now()->toDateTimeString(),
                    'manual_override' => true,
                ];

                $existing['history'][] = [
                    'key' => $key,
                    'value' => is_string($override['value'] ?? null) ? $override['value'] : json_encode($override['value'] ?? null),
                    'confidence' => 100,
                    'recorded_at' => now()->toDateTimeString(),
                    'manual' => true,
                ];
            }
        }

        $guest->preferences = $existing;
        $guest->save();

        return back()->with('success', 'Preferensi tamu berhasil diperbarui.');
    }
}
