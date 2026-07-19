<?php

namespace App\Services\Sales;

use App\Models\CorporateAccount;
use App\Models\CorporateBooking;
use App\Models\CorporateRate;
use App\Models\Property;
use App\Models\Reservation;
use Carbon\Carbon;

class CorporateAccountService
{
    public function list(Property $property, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return CorporateAccount::where('property_id', $property->id)
            ->when($filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where(fn($q2) =>
                $q2->where('company_name', 'like', "%{$s}%")
                   ->orWhere('contact_person', 'like', "%{$s}%")
                   ->orWhere('email', 'like', "%{$s}%")
            ))
            ->withCount(['bookings', 'rates'])
            ->orderBy('company_name')
            ->paginate(20);
    }

    public function create(Property $property, array $data): CorporateAccount
    {
        $data['property_id'] = $property->id;
        $data['status'] = $data['status'] ?? 'active';
        return CorporateAccount::create($data);
    }

    public function update(CorporateAccount $account, array $data): CorporateAccount
    {
        $account->update($data);
        return $account->fresh();
    }

    public function calculateNegotiatedRate(CorporateAccount $account, int $roomTypeId, float $baseRate): float
    {
        $corpRate = $account->rates()->where('room_type_id', $roomTypeId)->where('is_active', true)->first();
        if ($corpRate) return (float) $corpRate->negotiated_rate;
        if ($account->rate_agreement_type === 'percentage_discount') {
            return round($baseRate * (1 - $account->discount_pct / 100), 2);
        }
        return $baseRate;
    }

    public function trackNightCommitment(Property $property): void
    {
        $accounts = CorporateAccount::where('property_id', $property->id)
            ->where('status', 'active')
            ->where('annual_room_night_commitment', '>', 0)
            ->get();

        foreach ($accounts as $account) {
            $actualNights = Reservation::whereIn('id',
                CorporateBooking::where('corporate_account_id', $account->id)->pluck('reservation_id')
            )->where('status', 'checked_out')->whereYear('check_out', now()->year)->count();

            $account->update(['actual_room_nights' => $actualNights]);
        }
    }

    public function creditUtilization(CorporateAccount $account): float
    {
        if ($account->credit_limit <= 0) return 0;
        $reservationIds = CorporateBooking::where('corporate_account_id', $account->id)->pluck('reservation_id');
        $totalOutstanding = \App\Models\Folio::whereIn('reservation_id', $reservationIds)
            ->where('balance', '>', 0)->sum('balance');
        return round(($totalOutstanding / $account->credit_limit) * 100, 1);
    }

    public function performanceReport(CorporateAccount $account): array
    {
        $bookings = $account->bookings()->with('reservation')->get();
        $totalRevenue = $bookings->sum('rate_applied');
        $totalDiscount = $bookings->sum('discount_amount');
        $totalNights = 0;
        foreach ($bookings as $b) {
            if ($b->reservation) {
                $totalNights += Carbon::parse($b->reservation->check_in)->diffInDays(Carbon::parse($b->reservation->check_out));
            }
        }

        $monthlyRevenue = $account->bookings()
            ->whereHas('reservation')
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, SUM(rate_applied) as revenue')
            ->groupBy('month')->orderBy('month')
            ->pluck('revenue', 'month')->toArray();

        return [
            'total_bookings' => $bookings->count(),
            'total_revenue' => (float) $totalRevenue,
            'total_discount' => (float) $totalDiscount,
            'total_nights' => $totalNights,
            'night_commitment_pct' => $account->nightCommitmentPct(),
            'credit_utilization_pct' => $this->creditUtilization($account),
            'monthly_revenue' => $monthlyRevenue,
        ];
    }

    public function saveRate(Property $property, CorporateAccount $account, array $data): CorporateRate
    {
        $data['property_id'] = $property->id;
        $data['corporate_account_id'] = $account->id;
        $data['blackout_dates'] = $data['blackout_dates'] ?? [];
        return CorporateRate::updateOrCreate(
            ['corporate_account_id' => $account->id, 'room_type_id' => $data['room_type_id']],
            $data
        );
    }

    public function deleteRate(CorporateRate $rate): void
    {
        $rate->delete();
    }
}
