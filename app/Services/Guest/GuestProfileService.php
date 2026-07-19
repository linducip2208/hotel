<?php

namespace App\Services\Guest;

use App\Models\FolioCharge;
use App\Models\Guest;
use App\Models\GuestProfile;
use App\Models\Reservation;
use App\Models\Review;

/**
 * Guest 360 Intelligence Service.
 * Rebuilds GuestProfile from actual transaction history after each checkout.
 * Scores: upsell_score, churn_risk_score, loyalty_score (0-100).
 */
class GuestProfileService
{
    public function rebuild(Guest $guest): GuestProfile
    {
        $reservations = Reservation::where('primary_guest_id', $guest->id)
            ->whereIn('status', ['checked_out'])
            ->with(['rooms.roomType', 'folios.charges'])
            ->get();

        $totalStays  = $reservations->count();
        $totalNights = $reservations->sum('nights');

        if ($totalStays === 0) {
            return GuestProfile::updateOrCreate(
                ['guest_id' => $guest->id],
                ['total_stays' => 0, 'last_built_at' => now()]
            );
        }

        // ── Revenue breakdown ────────────────────────────────────────────
        $totalLtv      = $reservations->sum('grand_total');
        $avgDailyRate  = $totalNights > 0 ? $reservations->sum('total_room') / $totalNights : 0;

        $allFolioIds = $reservations->flatMap(fn ($r) => $r->folios->pluck('id'));

        $fnbTotal = FolioCharge::whereIn('folio_id', $allFolioIds)
            ->whereIn('category', ['fnb', 'minibar', 'restaurant'])
            ->where('is_void', false)->sum('amount');

        $spaTotal = FolioCharge::whereIn('folio_id', $allFolioIds)
            ->where('category', 'spa')
            ->where('is_void', false)->sum('amount');

        $avgFnb = $totalStays > 0 ? $fnbTotal / $totalStays : 0;
        $avgSpa = $totalStays > 0 ? $spaTotal / $totalStays : 0;

        // ── Preferences ──────────────────────────────────────────────────
        $roomTypeCounts = $reservations->flatMap(fn ($r) => $r->rooms)
            ->groupBy(fn ($rr) => $rr->room_type_id)
            ->map->count();
        $preferredRtId  = $roomTypeCounts->sortDesc()->keys()->first();

        $checkInDays = $reservations->map(fn ($r) => $r->check_in->dayName)->countBy()->sortDesc();
        $preferredDay = $checkInDays->keys()->first();

        $avgParty = (int) round($reservations->avg('adults') ?? 1);
        $avgLead  = (int) round($reservations->avg(fn ($r) =>
            max(0, $r->check_in->diffInDays($r->created_at))
        ) ?? 0);
        $avgLen   = (int) round($reservations->avg('nights') ?? 1);

        $primarySource = $reservations->groupBy('source')->map->count()->sortDesc()->keys()->first();

        $typicallyBreakfast = $reservations->flatMap(fn ($r) => $r->addons)
            ->filter(fn ($a) => str_contains(strtolower($a->code ?? ''), 'breakfast'))
            ->count() > ($totalStays * 0.5);

        // ── Visit frequency ───────────────────────────────────────────────
        $firstStay    = $reservations->min('check_in');
        $monthsActive = max(1, now()->diffInMonths($firstStay));
        $staysPerMonth = $totalStays / $monthsActive;

        $frequency = match (true) {
            $staysPerMonth >= 3  => 'weekly',
            $staysPerMonth >= 1  => 'monthly',
            $staysPerMonth >= 0.25 => 'quarterly',
            $staysPerMonth >= 0.1  => 'annual',
            default => 'one_time',
        };

        // ── Reviews / Sentiment ────────────────────────────────────────────
        $reviews   = Review::where('guest_id', $guest->id)->get();
        $avgScore  = $reviews->avg('overall_score');
        $sentiment = null;
        if ($avgScore !== null) {
            $sentiment = match (true) {
                $avgScore >= 4   => 'positive',
                $avgScore >= 2.5 => 'neutral',
                default          => 'negative',
            };
        }

        // ── Scores ────────────────────────────────────────────────────────
        $upsellScore  = min(100, (int) (
            ($avgSpa > 0 ? 20 : 0) +
            ($avgFnb > 100000 ? 20 : 0) +
            ($totalStays > 3 ? 20 : 0) +
            ($avgDailyRate > 500000 ? 20 : 0) +
            ($staysPerMonth >= 0.25 ? 20 : 0)
        ));

        $loyaltyScore = min(100, (int) (
            min(40, $totalStays * 5) +
            min(30, ($totalNights / 50) * 30) +
            ($frequency === 'monthly' ? 20 : ($frequency === 'quarterly' ? 10 : 0)) +
            ($avgScore !== null && $avgScore >= 4 ? 10 : 0)
        ));

        $lastStay     = $reservations->max('check_out');
        $daysSinceLast = now()->diffInDays($lastStay);
        $churnRisk = match (true) {
            $daysSinceLast < 90   => max(0, 30 - $loyaltyScore / 2),
            $daysSinceLast < 180  => max(20, 50 - $loyaltyScore / 2),
            $daysSinceLast < 365  => 70,
            default               => 90,
        };

        return GuestProfile::updateOrCreate(
            ['guest_id' => $guest->id],
            [
                'total_stays'               => $totalStays,
                'total_nights'              => $totalNights,
                'total_lifetime_value'      => round($totalLtv, 2),
                'avg_daily_rate'            => round($avgDailyRate, 2),
                'avg_fnb_spend_per_stay'    => round($avgFnb, 2),
                'avg_spa_spend_per_stay'    => round($avgSpa, 2),
                'preferred_room_type_id'    => $preferredRtId,
                'preferred_check_in_day'    => $preferredDay,
                'avg_party_size'            => $avgParty,
                'avg_lead_days'             => $avgLead,
                'avg_stay_length'           => $avgLen,
                'primary_booking_source'    => $primarySource,
                'visit_frequency'           => $frequency,
                'typically_books_breakfast' => $typicallyBreakfast,
                'typically_uses_spa'        => $avgSpa > 0,
                'typically_uses_fnb'        => $avgFnb > 50000,
                'avg_review_score'          => $avgScore ? round($avgScore, 2) : null,
                'total_reviews'             => $reviews->count(),
                'sentiment'                 => $sentiment,
                'upsell_score'              => $upsellScore,
                'churn_risk_score'          => (int) $churnRisk,
                'loyalty_score'             => $loyaltyScore,
                'last_built_at'             => now(),
            ]
        );
    }
}
