<?php

namespace App\Services\Marketing;

use App\Models\Guest;
use App\Models\Referral;
use App\Models\ReferralCode;
use Illuminate\Support\Str;

class ReferralService
{
    public function generateCode(Guest $guest): ReferralCode
    {
        $existing = ReferralCode::where('owner_guest_id', $guest->id)->first();
        if ($existing) {
            return $existing;
        }

        return ReferralCode::create([
            'property_id' => $guest->property_id,
            'owner_guest_id' => $guest->id,
            'code' => strtoupper(Str::random(8)),
            'is_active' => true,
        ]);
    }

    public function trackReferral(string $code, Guest $referredGuest): ?Referral
    {
        $refCode = ReferralCode::where('code', $code)->where('is_active', true)->first();
        if (!$refCode || $refCode->owner_guest_id === $referredGuest->id) {
            return null;
        }

        return Referral::create([
            'property_id' => $refCode->property_id,
            'referrer_guest_id' => $refCode->owner_guest_id,
            'referred_guest_id' => $referredGuest->id,
            'referral_code_id' => $refCode->id,
            'status' => 'pending',
            'referred_at' => now(),
        ]);
    }

    public function completeReferral(Referral $referral, float $rewardAmount = 50000): void
    {
        $referral->update([
            'status' => 'completed',
            'reward_amount' => $rewardAmount,
            'is_rewarded' => true,
            'completed_at' => now(),
        ]);

        $refCode = $referral->referralCode;
        if ($refCode) {
            $refCode->increment('total_referrals');
            $refCode->increment('total_rewards_earned', $rewardAmount);
        }
    }

    public function getTopReferrers(int $propertyId, int $limit = 10): array
    {
        return ReferralCode::where('property_id', $propertyId)
            ->where('total_referrals', '>', 0)
            ->with('ownerGuest')
            ->orderByDesc('total_referrals')
            ->limit($limit)
            ->get()->toArray();
    }

    public function getReferralStats(int $propertyId): array
    {
        $codes = ReferralCode::where('property_id', $propertyId);

        return [
            'total_codes' => $codes->count(),
            'active_codes' => (clone $codes)->where('is_active', true)->count(),
            'total_referrals' => (int) Referral::where('property_id', $propertyId)->count(),
            'total_completed' => (int) Referral::where('property_id', $propertyId)->where('status', 'completed')->count(),
            'total_rewards' => (float) Referral::where('property_id', $propertyId)->sum('reward_amount'),
            'total_pending' => (int) Referral::where('property_id', $propertyId)->where('status', 'pending')->count(),
        ];
    }
}
