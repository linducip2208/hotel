<?php

namespace App\Services\Guest;

use App\Models\FolioCharge;
use App\Models\Guest;
use App\Models\GuestProfile;
use App\Models\Reservation;

class PreferenceLearningService
{
    protected array $preferenceKeys = [
        'preferred_floor',
        'pillow_type',
        'extra_blankets',
        'newspaper',
        'ac_temperature',
        'wake_up_time',
        'minibar_items',
    ];

    public function learn(Reservation $reservation): void
    {
        $guest = $reservation->primaryGuest ?? $reservation->guest;
        if (!$guest) return;

        $learned = $this->extractFromReservation($reservation);
        if (empty($learned)) return;

        $this->mergePreferences($guest, $learned);
    }

    public function getPreferences(Guest $guest): array
    {
        $prefs = $guest->preferences ?? [];
        $result = [];

        foreach ($this->preferenceKeys as $key) {
            $result[$key] = $prefs[$key] ?? null;
        }

        $result['auto_apply'] = $prefs['auto_apply'] ?? true;
        $result['history'] = $prefs['history'] ?? [];

        return $result;
    }

    public function suggestForCheckin(Reservation $reservation): array
    {
        $guest = $reservation->primaryGuest ?? $reservation->guest;
        if (!$guest) return [];

        $prefs = $this->getPreferences($guest);
        if (empty($prefs['auto_apply']) || !($prefs['auto_apply'] ?? true)) {
            return [];
        }

        $suggestions = [];
        foreach ($this->preferenceKeys as $key) {
            $entry = $prefs[$key] ?? null;
            if ($entry && ($entry['confidence'] ?? 0) >= 40) {
                $suggestions[$key] = [
                    'value' => $entry['value'],
                    'confidence' => $entry['confidence'],
                    'last_updated' => $entry['last_updated'] ?? null,
                ];
            }
        }

        return $suggestions;
    }

    public function applyPreferences(Reservation $reservation): void
    {
        $suggestions = $this->suggestForCheckin($reservation);
        $guest = $reservation->primaryGuest ?? $reservation->guest;
        if (!$guest || empty($suggestions)) return;

        $guest->preferences = array_merge($guest->preferences ?? [], [
            'last_applied_to_reservation' => $reservation->id,
            'last_applied_at' => now()->toDateTimeString(),
        ]);
        $guest->save();
    }

    protected function extractFromReservation(Reservation $reservation): array
    {
        $learned = [];

        $room = $reservation->rooms->first();
        if ($room && $room->room) {
            $floor = $room->room->floor ?? $room->room->room_number;
            if ($floor) {
                $learned['preferred_floor'] = is_numeric(substr($floor, 0, 1)) ? (int) substr($floor, 0, 1) : $floor;
            }
        }

        $folios = $reservation->folios;
        if ($folios->isNotEmpty()) {
            $folioIds = $folios->pluck('id');
            $charges = FolioCharge::whereIn('folio_id', $folioIds)
                ->where('is_void', false)->get();

            $minibarItems = $charges->filter(fn ($c) => $c->category === 'minibar')
                ->pluck('description')->filter()->unique()->values()->toArray();
            if (!empty($minibarItems)) {
                $learned['minibar_items'] = $minibarItems;
            }
        }

        $specialRequests = $reservation->special_requests ?? $reservation->notes ?? '';
        if (is_string($specialRequests)) {
            $lower = strtolower($specialRequests);
            if (str_contains($lower, 'lantai')) {
                preg_match('/lantai\s*(\d+)/i', $specialRequests, $m);
                if (isset($m[1])) $learned['preferred_floor'] = (int) $m[1];
            }
            if (str_contains($lower, 'bantal') && str_contains($lower, 'lunak')) {
                $learned['pillow_type'] = 'soft';
            }
            if (str_contains($lower, 'bantal') && str_contains($lower, 'keras')) {
                $learned['pillow_type'] = 'firm';
            }
            if (str_contains($lower, 'hypoallergenic')) {
                $learned['pillow_type'] = 'hypoallergenic';
            }
            if (str_contains($lower, 'selimut') && str_contains($lower, 'tambah')) {
                $learned['extra_blankets'] = true;
            }
            if (str_contains($lower, 'koran') || str_contains($lower, 'newspaper')) {
                $learned['newspaper'] = 'Kompas';
            }
            if (str_contains($lower, 'ac') || str_contains($lower, 'suhu')) {
                preg_match('/(\d{2})\s*(?:°|derajat|C)/i', $specialRequests, $m);
                if (isset($m[1])) $learned['ac_temperature'] = (int) $m[1];
            }
        }

        return $learned;
    }

    protected function mergePreferences(Guest $guest, array $newPrefs): void
    {
        $existing = $guest->preferences ?? [];
        $history = $existing['history'] ?? [];

        foreach ($newPrefs as $key => $value) {
            if (in_array($key, $this->preferenceKeys)) {
                $oldEntry = $existing[$key] ?? null;
                $staysForThis = 1;
                $oldConfidence = 0;

                if ($oldEntry && isset($oldEntry['stays'])) {
                    $staysForThis = $oldEntry['stays'] + 1;
                    $oldConfidence = $oldEntry['confidence'] ?? 0;
                }

                if (is_array($value)) {
                    $sameItems = $oldEntry && isset($oldEntry['value']) && is_array($oldEntry['value'])
                        ? count(array_intersect($oldEntry['value'], $value))
                        : 0;
                    $confidence = min(100, (int) round(($sameItems / max(1, count($value))) * 100));
                } else {
                    $sameVal = $oldEntry && isset($oldEntry['value']) && $oldEntry['value'] === $value;
                    $confidence = min(100, (int) round(($staysForThis / max(1, $staysForThis + 1 - ($sameVal ? 1 : 0))) * 100));
                    if ($sameVal && $confidence < 60) $confidence = 60;
                    if (!$sameVal && $staysForThis === 1) $confidence = 25;
                }

                $existing[$key] = [
                    'value' => $value,
                    'confidence' => $confidence,
                    'stays' => $staysForThis,
                    'last_updated' => now()->toDateTimeString(),
                ];

                $history[] = [
                    'key' => $key,
                    'value' => is_array($value) ? json_encode($value) : (string) $value,
                    'confidence' => $confidence,
                    'recorded_at' => now()->toDateTimeString(),
                ];
            }
        }

        $existing['history'] = array_slice($history, -100);
        $existing['last_learned_at'] = now()->toDateTimeString();

        $guest->preferences = $existing;
        $guest->save();
    }
}
