<?php

namespace App\Services\Revenue;

use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomUpgrade;
use App\Models\UpsellOffer;
use App\Models\UpsellPresentation;

class UpsellService
{
    public function getEligibleOffers(Reservation $reservation): array
    {
        $profile = $reservation->primaryGuest?->profile;
        $tier = $profile?->upsellTier() ?? 'cold';
        $nights = $reservation->check_in && $reservation->check_out
            ? $reservation->check_in->diffInDays($reservation->check_out)
            : 1;

        return UpsellOffer::where('property_id', $reservation->property_id)
            ->where('is_active', true)
            ->where(function ($q) use ($tier) {
                $q->where('target_guest_tier', $tier)
                    ->orWhere('target_guest_tier', 'all')
                    ->orWhereNull('target_guest_tier');
            })
            ->where('min_stay_nights', '<=', $nights)
            ->get()
            ->toArray();
    }

    public function presentOffer(Reservation $reservation, UpsellOffer $offer): UpsellPresentation
    {
        return UpsellPresentation::create([
            'property_id' => $reservation->property_id,
            'reservation_id' => $reservation->id,
            'upsell_offer_id' => $offer->id,
            'status' => 'offered',
            'offered_at' => now(),
            'price_offered' => $offer->price,
        ]);
    }

    public function acceptOffer(UpsellPresentation $presentation, int $userId, ?float $negotiatedPrice = null): void
    {
        $presentation->update([
            'status' => 'accepted',
            'responded_at' => now(),
            'price_accepted' => $negotiatedPrice ?? $presentation->price_offered,
            'accepted_by_user_id' => $userId,
        ]);

        // If room upgrade, create room upgrade record
        if ($presentation->offer->type === 'room_upgrade' && $presentation->offer->upgrade_to_room_type_id) {
            $this->processRoomUpgrade(
                $presentation->reservation,
                $presentation->offer->upgrade_to_room_type_id,
                $userId,
                (float) ($negotiatedPrice ?? $presentation->price_offered)
            );
        }
    }

    public function declineOffer(UpsellPresentation $presentation): void
    {
        $presentation->update([
            'status' => 'declined',
            'responded_at' => now(),
        ]);
    }

    public function processRoomUpgrade(Reservation $reservation, int $toRoomTypeId, int $userId, float $fee = 0): RoomUpgrade
    {
        $existingRoom = $reservation->rooms()->with('room')->first();
        $oldRoom = $existingRoom?->room;

        $newRoom = Room::where('room_type_id', $toRoomTypeId)
            ->where('property_id', $reservation->property_id)
            ->where('fo_status', 'clean')
            ->first();

        $upgrade = RoomUpgrade::create([
            'property_id' => $reservation->property_id,
            'reservation_id' => $reservation->id,
            'from_room_id' => $oldRoom?->id,
            'to_room_id' => $newRoom?->id,
            'upgrade_fee' => $fee,
            'processed_by_user_id' => $userId,
        ]);

        if ($newRoom && $existingRoom) {
            $existingRoom->update(['room_id' => $newRoom->id]);
        }

        return $upgrade;
    }

    public function autoSuggestUpsells(Reservation $reservation): array
    {
        $eligible = $this->getEligibleOffers($reservation);
        $results = [];

        foreach ($eligible as $offer) {
            $alreadyOffered = UpsellPresentation::where('reservation_id', $reservation->id)
                ->where('upsell_offer_id', $offer['id'])
                ->exists();

            if (! $alreadyOffered) {
                $presentation = $this->presentOffer($reservation, UpsellOffer::find($offer['id']));
                $results[] = $presentation;
            }
        }

        return $results;
    }
}
