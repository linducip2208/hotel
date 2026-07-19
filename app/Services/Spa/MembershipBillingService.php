<?php

declare(strict_types=1);

namespace App\Services\Spa;

use App\Models\SpaMembership;
use App\Models\SpaMembershipUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

final class MembershipBillingService
{
    /**
     * Check for memberships expiring in 7 days and log reminders.
     */
    public function renewExpiring(): int
    {
        $expiring = SpaMembership::where('status', 'active')
            ->whereDate('end_date', '<=', Carbon::now()->addDays(7)->toDateString())
            ->whereDate('end_date', '>=', Carbon::now()->toDateString())
            ->with('guest')
            ->get();

        $count = 0;
        foreach ($expiring as $membership) {
            Log::info("Spa membership expiring: {$membership->membership_number} for guest {$membership->guest?->full_name} on {$membership->end_date->toDateString()}");
            $count++;
        }

        return $count;
    }

    /**
     * Process auto-renewals for memberships that have expired and have auto_renew enabled.
     */
    public function processRenewals(): int
    {
        $toRenew = SpaMembership::where('status', 'active')
            ->where('auto_renew', true)
            ->whereDate('end_date', '<', Carbon::now()->toDateString())
            ->get();

        $count = 0;
        foreach ($toRenew as $membership) {
            $newStart = $membership->end_date->copy()->addDay();
            $newEnd = match ($membership->plan_type) {
                'monthly' => $newStart->copy()->addMonth(),
                'quarterly' => $newStart->copy()->addMonths(3),
                'annual' => $newStart->copy()->addYear(),
                default => $newStart->copy()->addMonth(),
            };

            $membership->update([
                'start_date' => $newStart,
                'end_date' => $newEnd,
                'status' => 'active',
            ]);

            Log::info("Spa membership renewed: {$membership->membership_number} until {$newEnd->toDateString()}");
            $count++;
        }

        return $count;
    }

    /**
     * Count visits this month for a guest's active membership.
     */
    public function checkUsage(int $guestId, int $year, int $month): int
    {
        $membership = SpaMembership::where('guest_id', $guestId)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', Carbon::now()->toDateString())
            ->whereDate('end_date', '>=', Carbon::now()->toDateString())
            ->first();

        if (!$membership) {
            return 0;
        }

        return SpaMembershipUsage::where('membership_id', $membership->id)
            ->whereHas('appointment', function ($q) use ($year, $month) {
                $q->whereYear('start_at', $year)->whereMonth('start_at', $month);
            })
            ->count();
    }
}
