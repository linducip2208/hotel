<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestProfile;
use App\Models\GuestPreferenceHistory;
use App\Models\RoomType;
use App\Models\Reservation;
use App\Models\ReservationRoom;

class GuestPreferenceEngine
{
    public function learnFromReservation(Reservation $reservation): void
    {
        $guest = Guest::find($reservation->primary_guest_id);
        if (!$guest) return;

        $preferences = $guest->preferences ?? [];
        $confidence = $guest->preference_confidence ?? [];

        foreach ($reservation->reservationRooms as $rr) {
            if ($rr->room_id) {
                $room = \App\Models\Room::find($rr->room_id);
                if ($room) {
                    $this->recordPreference(
                        $reservation->property_id,
                        $guest->id,
                        $reservation->id,
                        'auto_learned',
                        'preferred_floor',
                        (string) $room->floor,
                        0.6
                    );

                    if ($room->room_type_id) {
                        $rt = RoomType::find($room->room_type_id);
                        if ($rt) {
                            $this->recordPreference(
                                $reservation->property_id,
                                $guest->id,
                                $reservation->id,
                                'auto_learned',
                                'preferred_room_type',
                                $rt->name,
                                0.5
                            );
                        }
                    }
                }
            }
        }

        // Update GuestProfile with learned preferences
        $this->syncGuestProfile($guest, $reservation->property_id);
    }

    protected function recordPreference(int $propertyId, int $guestId, ?int $reservationId, string $source, string $key, string $value, float $confidence): void
    {
        GuestPreferenceHistory::create([
            'property_id' => $propertyId,
            'guest_id' => $guestId,
            'reservation_id' => $reservationId,
            'source' => $source,
            'preference_key' => $key,
            'preference_value' => $value,
            'confidence' => $confidence,
        ]);

        $guest = Guest::find($guestId);
        if ($guest) {
            $prefs = $guest->preferences ?? [];
            $prefConf = $guest->preference_confidence ?? [];

            $existing = $prefs[$key] ?? null;
            if ($existing === $value) {
                $prefConf[$key] = min(1.0, ($prefConf[$key] ?? 0.5) + 0.1);
            } elseif ($existing) {
                $prefConf[$key] = max(0.3, ($prefConf[$key] ?? 0.5) - 0.1);
            } else {
                $prefs[$key] = $value;
                $prefConf[$key] = $confidence;
            }

            $guest->preferences = $prefs;
            $guest->preference_confidence = $prefConf;
            $guest->save();
        }
    }

    protected function syncGuestProfile(Guest $guest, int $propertyId): void
    {
        $profile = GuestProfile::firstOrCreate(
            ['guest_id' => $guest->id],
            ['property_id' => $propertyId]
        );

        $prefs = $guest->preferences ?? [];
        if (!empty($prefs['preferred_floor'])) {
            $profile->preferred_floor = $prefs['preferred_floor'];
        }
        if (!empty($prefs['preferred_bed_type'])) {
            $profile->preferred_bed_type = $prefs['preferred_bed_type'];
        }

        $profile->save();
    }

    public function suggestRoomAssignment(Reservation $reservation): array
    {
        $guest = Guest::with('profile')->find($reservation->primary_guest_id);
        if (!$guest) return [];

        $suggestions = [];
        $prefs = $guest->preferences ?? [];
        $profile = $guest->profile;

        $requestedRoomTypeId = $reservation->reservationRooms()->first()?->room_type_id;

        // Preferred floor
        if (!empty($prefs['preferred_floor']) || $profile?->preferred_floor) {
            $floor = $profile?->preferred_floor ?? $prefs['preferred_floor'];
            $suggestions['preferred_floor'] = [
                'value' => $floor,
                'confidence' => $guest->preference_confidence['preferred_floor'] ?? 0.7,
                'priority' => 'high',
            ];
        }

        // Preferred room type from profile
        if ($profile && $profile->preferred_room_type_id && $profile->preferred_room_type_id != $requestedRoomTypeId) {
            $suggestions['upgrade_to_room_type'] = [
                'room_type_id' => $profile->preferred_room_type_id,
                'name' => RoomType::find($profile->preferred_room_type_id)?->name,
                'priority' => 'medium',
            ];
        }

        return $suggestions;
    }

    public function autoAssignWithPreferences(Reservation $reservation): array
    {
        $suggestions = $this->suggestRoomAssignment($reservation);
        $results = ['applied' => [], 'ignored' => []];

        $guest = Guest::find($reservation->primary_guest_id);
        if (!$guest || empty($guest->preference_confidence)) return $results;

        foreach ($guest->preference_confidence as $key => $conf) {
            if ($conf >= 0.7) {
                $results['applied'][] = [
                    'preference' => $key,
                    'value' => $guest->preferences[$key] ?? 'unknown',
                    'confidence' => $conf,
                ];
            } else {
                $results['ignored'][] = [
                    'preference' => $key,
                    'value' => $guest->preferences[$key] ?? 'unknown',
                    'confidence' => $conf,
                    'reason' => 'confidence_too_low',
                ];
            }
        }

        return $results;
    }
}
