<?php

namespace App\Services\Finance;

use App\Models\Chargeback;
use App\Models\ChargebackEvidence;
use App\Models\FolioPayment;
use App\Models\Property;
use Carbon\Carbon;

class ChargebackService
{
    public function register(Property $property, FolioPayment $transaction, array $data): Chargeback
    {
        return Chargeback::create([
            'property_id' => $property->id,
            'reservation_id' => $data['reservation_id'] ?? null,
            'folio_charge_id' => $data['folio_charge_id'] ?? null,
            'payment_transaction_id' => $transaction->id,
            'chargeback_date' => $data['chargeback_date'] ?? now()->toDateString(),
            'amount' => $data['amount'] ?? $transaction->amount,
            'reason_code' => $data['reason_code'] ?? null,
            'reason_description' => $data['reason_description'] ?? null,
            'card_brand' => $data['card_brand'] ?? null,
            'card_last_4' => $data['card_last_4'] ?? null,
            'status' => 'open',
            'disputed_by' => $data['disputed_by'] ?? null,
            'evidence_deadline' => $data['evidence_deadline'] ?? now()->addDays(14)->toDateString(),
            'internal_notes' => $data['internal_notes'] ?? null,
        ]);
    }

    public function addEvidence(Chargeback $chargeback, array $data): ChargebackEvidence
    {
        return ChargebackEvidence::create([
            'chargeback_id' => $chargeback->id,
            'evidence_type' => $data['evidence_type'] ?? 'other',
            'file_path' => $data['file_path'],
            'description' => $data['description'] ?? null,
            'uploaded_at' => now(),
        ]);
    }

    public function submitResponse(Chargeback $chargeback): Chargeback
    {
        $chargeback->update([
            'status' => 'under_review',
            'response_submitted_at' => now(),
        ]);

        return $chargeback->fresh();
    }

    public function recordOutcome(Chargeback $chargeback, string $decision, ?float $recoveredAmount = null): Chargeback
    {
        $chargeback->update([
            'status' => $decision,
            'final_decision' => $decision,
            'final_decision_date' => now()->toDateString(),
            'recovered_amount' => $recoveredAmount ?? ($decision === 'won' ? $chargeback->amount : 0),
        ]);

        return $chargeback->fresh();
    }

    public function getStats(Property $property): array
    {
        $open = Chargeback::where('property_id', $property->id)->where('status', 'open')->count();
        $won = Chargeback::where('property_id', $property->id)->where('status', 'won')->count();
        $lost = Chargeback::where('property_id', $property->id)->where('status', 'lost')->count();
        $underReview = Chargeback::where('property_id', $property->id)->where('status', 'under_review')->count();
        $totalResolved = $won + $lost;
        $winRate = $totalResolved > 0 ? round(($won / $totalResolved) * 100, 1) : 0;

        $totalAmount = Chargeback::where('property_id', $property->id)->where('status', '!=', 'won')->sum('amount');
        $recovered = Chargeback::where('property_id', $property->id)->sum('recovered_amount');

        return [
            'open' => $open,
            'under_review' => $underReview,
            'won' => $won,
            'lost' => $lost,
            'win_rate' => $winRate,
            'total_amount' => $totalAmount,
            'recovered' => $recovered,
        ];
    }

    public function getDeadlineAlerts(Property $property): array
    {
        $today = now()->toDateString();
        $thisWeek = now()->addDays(7)->toDateString();

        $overdue = Chargeback::where('property_id', $property->id)
            ->whereIn('status', ['open', 'under_review'])
            ->where('evidence_deadline', '<', $today)
            ->count();

        $dueThisWeek = Chargeback::where('property_id', $property->id)
            ->whereIn('status', ['open', 'under_review'])
            ->whereBetween('evidence_deadline', [$today, $thisWeek])
            ->count();

        return [
            'overdue' => $overdue,
            'due_this_week' => $dueThisWeek,
        ];
    }
}
