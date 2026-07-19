<?php

namespace App\Services\Finance;

use App\Models\OwnerDistribution;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OwnerPortalService
{
    public function getFinancialSummary(Property $property, string $period): array
    {
        $date = Carbon::parse($period);
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        $revenue = \App\Models\FolioCharge::where('property_id', $property->id)
            ->whereBetween('charge_date', [$start, $end])
            ->where('is_void', false)
            ->sum('amount');

        $expense = \App\Models\ApBill::where('property_id', $property->id)
            ->whereBetween('bill_date', [$start, $end])
            ->sum('total');

        $gop = $revenue - $expense;

        $nights = \App\Models\Reservation::where('property_id', $property->id)
            ->where('status', 'checked_out')
            ->whereBetween('check_out', [$start, $end])
            ->sum('nights');

        $totalRooms = $property->rooms()->count();
        $daysInMonth = $start->daysInMonth;
        $availableRoomNights = $totalRooms * $daysInMonth;
        $occupancy = $availableRoomNights > 0 ? round(($nights / $availableRoomNights) * 100, 1) : 0;

        $adr = $nights > 0 ? round($revenue / $nights, 2) : 0;
        $revpar = $availableRoomNights > 0 ? round($revenue / $availableRoomNights, 2) : 0;

        return [
            'month' => $date->isoFormat('MMMM Y'),
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'total_revenue' => $revenue,
            'total_expense' => $expense,
            'gop' => $gop,
            'nop' => $gop,
            'occupancy' => $occupancy,
            'adr' => $adr,
            'revpar' => $revpar,
            'total_rooms' => $totalRooms,
            'available_room_nights' => $availableRoomNights,
            'sold_room_nights' => $nights,
        ];
    }

    public function calculateDistribution(Property $property, string $period): array
    {
        $summary = $this->getFinancialSummary($property, $period);

        $owners = PropertyOwner::where('property_id', $property->id)
            ->where('is_active', true)
            ->get();

        $distributions = [];
        foreach ($owners as $owner) {
            $share = $summary['nop'] * ($owner->ownership_pct / 100);
            $distributions[] = [
                'owner_user_id' => $owner->user_id,
                'owner_name' => $owner->user->name,
                'ownership_pct' => (float) $owner->ownership_pct,
                'distribution_amount' => round($share, 2),
            ];
        }

        return [
            'summary' => $summary,
            'distributions' => $distributions,
        ];
    }

    public function getOwnerDashboard(User $user, Property $property): array
    {
        $owner = PropertyOwner::where('property_id', $property->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$owner) {
            return ['summary' => null, 'distributions' => [], 'monthly_trend' => [], 'documents' => []];
        }

        $summary = $this->getFinancialSummary($property, now()->startOfMonth()->toDateString());

        $distributions = OwnerDistribution::where('property_id', $property->id)
            ->where('owner_user_id', $user->id)
            ->orderByDesc('period_start')
            ->take(12)
            ->get();

        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $s = $this->getFinancialSummary($property, $m->startOfMonth()->toDateString());
            $trend[] = [
                'month' => $m->isoFormat('MMM Y'),
                'revenue' => $s['total_revenue'],
                'expense' => $s['total_expense'],
                'nop' => $s['nop'],
                'share' => round($s['nop'] * ($owner->ownership_pct / 100), 2),
            ];
        }

        $documents = \App\Models\OwnerDocument::where('property_id', $property->id)
            ->where('owner_user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return [
            'owner' => $owner,
            'ownership_pct' => (float) $owner->ownership_pct,
            'summary' => $summary,
            'distributions' => $distributions,
            'monthly_trend' => $trend,
            'documents' => $documents,
        ];
    }

    public function getMonthlyPnl(Property $property, string $period): array
    {
        $summary = $this->getFinancialSummary($property, $period);

        $start = Carbon::parse($period)->startOfMonth();
        $end = Carbon::parse($period)->endOfMonth();

        $roomRevenue = \App\Models\FolioCharge::where('property_id', $property->id)
            ->whereBetween('charge_date', [$start, $end])
            ->where('charge_type', 'room')
            ->where('is_void', false)
            ->sum('amount');

        $fnbRevenue = \App\Models\FolioCharge::where('property_id', $property->id)
            ->whereBetween('charge_date', [$start, $end])
            ->where('charge_type', 'fnb')
            ->where('is_void', false)
            ->sum('amount');

        $otherRevenue = \App\Models\FolioCharge::where('property_id', $property->id)
            ->whereBetween('charge_date', [$start, $end])
            ->whereNotIn('charge_type', ['room', 'fnb', 'tax', 'discount'])
            ->where('is_void', false)
            ->sum('amount');

        $taxCollected = \App\Models\FolioCharge::where('property_id', $property->id)
            ->whereBetween('charge_date', [$start, $end])
            ->where('charge_type', 'tax')
            ->where('is_void', false)
            ->sum('amount');

        $discounts = \App\Models\FolioCharge::where('property_id', $property->id)
            ->whereBetween('charge_date', [$start, $end])
            ->where('charge_type', 'discount')
            ->where('is_void', false)
            ->sum('amount');

        $payrollExpense = \App\Models\ApBill::where('property_id', $property->id)
            ->whereBetween('bill_date', [$start, $end])
            ->where('category', 'payroll')
            ->sum('total');

        $utilityExpense = \App\Models\ApBill::where('property_id', $property->id)
            ->whereBetween('bill_date', [$start, $end])
            ->where('category', 'utility')
            ->sum('total');

        $maintenanceExpense = \App\Models\ApBill::where('property_id', $property->id)
            ->whereBetween('bill_date', [$start, $end])
            ->where('category', 'maintenance')
            ->sum('total');

        $otherExpense = \App\Models\ApBill::where('property_id', $property->id)
            ->whereBetween('bill_date', [$start, $end])
            ->whereNotIn('category', ['payroll', 'utility', 'maintenance'])
            ->sum('total');

        $grossRevenue = $roomRevenue + $fnbRevenue + $otherRevenue;
        $totalExpenses = $payrollExpense + $utilityExpense + $maintenanceExpense + $otherExpense;
        $noi = $grossRevenue - $totalExpenses - abs($discounts);

        return array_merge($summary, [
            'room_revenue' => $roomRevenue,
            'fnb_revenue' => $fnbRevenue,
            'other_revenue' => $otherRevenue,
            'gross_revenue' => $grossRevenue,
            'discounts' => $discounts,
            'tax_collected' => $taxCollected,
            'payroll_expense' => $payrollExpense,
            'utility_expense' => $utilityExpense,
            'maintenance_expense' => $maintenanceExpense,
            'other_expense' => $otherExpense,
            'total_expenses' => $totalExpenses,
            'noi' => $noi,
        ]);
    }
}
