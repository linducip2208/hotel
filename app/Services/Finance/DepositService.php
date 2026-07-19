<?php

namespace App\Services\Finance;

use App\Models\Deposit;
use App\Models\FolioCharge;
use App\Models\Guest;
use App\Models\Property;
use App\Models\Reservation;
use Carbon\Carbon;

class DepositService
{
    public function receive(Property $property, array $data): Deposit
    {
        return Deposit::create([
            'property_id' => $property->id,
            'reservation_id' => $data['reservation_id'] ?? null,
            'guest_id' => $data['guest_id'] ?? null,
            'deposit_type' => $data['deposit_type'] ?? 'incidental',
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? null,
            'payment_reference' => $data['payment_reference'] ?? null,
            'received_date' => $data['received_date'] ?? now()->toDateString(),
            'status' => 'held',
            'notes' => $data['notes'] ?? null,
            'created_by_user_id' => auth()->id(),
        ]);
    }

    public function refund(Deposit $deposit, float $amount, string $method, ?string $reason = null): Deposit
    {
        $newRefunded = $deposit->refunded_amount + $amount;
        $remaining = $deposit->amount - $newRefunded;

        $deposit->update([
            'refunded_amount' => $newRefunded,
            'refund_date' => now()->toDateString(),
            'refund_method' => $method,
            'status' => $remaining <= 0 ? 'fully_refunded' : 'partially_refunded',
            'notes' => $reason ? ($deposit->notes ? $deposit->notes . "\nRefund: " . $reason : 'Refund: ' . $reason) : $deposit->notes,
        ]);

        return $deposit->fresh();
    }

    public function forfeit(Deposit $deposit, string $reason): Deposit
    {
        $deposit->update([
            'status' => 'forfeited',
            'forfeiture_reason' => $reason,
        ]);

        return $deposit->fresh();
    }

    public function applyToFolio(Deposit $deposit, FolioCharge $charge): void
    {
        $deposit->update([
            'folio_charge_id' => $charge->id,
            'status' => 'fully_refunded',
            'refunded_amount' => $deposit->amount,
            'refund_date' => now()->toDateString(),
            'refund_method' => 'folio_application',
        ]);
    }

    public function getStats(Property $property): array
    {
        $totalHeld = Deposit::where('property_id', $property->id)
            ->where('status', 'held')
            ->sum('amount');

        $refundedThisMonth = Deposit::where('property_id', $property->id)
            ->whereIn('status', ['fully_refunded', 'partially_refunded'])
            ->whereMonth('refund_date', now()->month)
            ->whereYear('refund_date', now()->year)
            ->sum('refunded_amount');

        $forfeitedThisMonth = Deposit::where('property_id', $property->id)
            ->where('status', 'forfeited')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('amount');

        $totalActive = Deposit::where('property_id', $property->id)
            ->where('status', 'held')
            ->count();

        return [
            'total_held' => $totalHeld,
            'refunded_this_month' => $refundedThisMonth,
            'forfeited_this_month' => $forfeitedThisMonth,
            'total_active' => $totalActive,
        ];
    }
}
