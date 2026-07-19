<?php

namespace App\Services\Loyalty;

use App\Models\Guest;
use App\Models\LoyaltyMember;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyTransaction;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoyaltyService
{
    public const POINTS_PER_RUPIAH = 0.0001; // 1 point per Rp 10.000

    public function enroll(Guest $guest): LoyaltyMember
    {
        return LoyaltyMember::firstOrCreate(
            ['guest_id' => $guest->id],
            [
                'property_id' => $guest->property_id ?? 1,
                'membership_no' => 'LM-'.now()->format('Y').'-'.Str::upper(Str::random(8)),
                'points_balance' => 0,
                'lifetime_points' => 0,
            ]
        );
    }

    public function awardForStay(Reservation $reservation): ?LoyaltyTransaction
    {
        $guest = $reservation->primaryGuest;
        if (! $guest) return null;

        $member = LoyaltyMember::where('guest_id', $guest->id)->first();
        if (! $member) return null;

        $points = (int) round($reservation->total_room * self::POINTS_PER_RUPIAH);
        if ($points <= 0) return null;

        return DB::transaction(function () use ($member, $points, $reservation) {
            $tx = LoyaltyTransaction::create([
                'member_id' => $member->id,
                'type' => 'earn',
                'points' => $points,
                'source_type' => 'reservation',
                'source_id' => $reservation->id,
                'description' => "Stay {$reservation->ref}",
            ]);
            $member->increment('points_balance', $points);
            $member->increment('lifetime_points', $points);
            $this->upgradeTierIfEligible($member);
            return $tx;
        });
    }

    public function redeem(LoyaltyMember $member, int $points, string $description): ?LoyaltyTransaction
    {
        if ($member->points_balance < $points) return null;

        return DB::transaction(function () use ($member, $points, $description) {
            $tx = LoyaltyTransaction::create([
                'member_id' => $member->id,
                'type' => 'redeem',
                'points' => -$points,
                'description' => $description,
            ]);
            $member->decrement('points_balance', $points);
            return $tx;
        });
    }

    protected function upgradeTierIfEligible(LoyaltyMember $member): void
    {
        $tier = LoyaltyTier::where('property_id', $member->property_id)
            ->where('points_threshold', '<=', $member->lifetime_points)
            ->orderByDesc('points_threshold')
            ->first();
        if ($tier && $tier->id !== $member->tier_id) {
            $member->update(['tier_id' => $tier->id]);
        }
    }
}
