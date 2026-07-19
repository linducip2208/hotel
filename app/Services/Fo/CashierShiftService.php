<?php

namespace App\Services\Fo;

use App\Models\CashierShift;
use App\Models\FolioPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CashierShiftService
{
    public function open(User $cashier, float $openingFloat = 0): CashierShift
    {
        return DB::transaction(function () use ($cashier, $openingFloat) {
            // Close any stale open shift untuk cashier ini
            CashierShift::where('cashier_id', $cashier->id)
                ->whereNull('closed_at')
                ->each(fn ($s) => $this->forceClose($s));

            return CashierShift::create([
                'property_id' => $cashier->property_id ?? app('current_property')?->id,
                'cashier_id' => $cashier->id,
                'opened_at' => now(),
                'opening_float' => $openingFloat,
            ]);
        });
    }

    public function close(CashierShift $shift, float $actualCash, ?string $notes = null): CashierShift
    {
        return DB::transaction(function () use ($shift, $actualCash, $notes) {
            $expectedCash = $this->calculateExpectedCash($shift);
            $variance = $actualCash - $expectedCash;

            $shift->update([
                'closed_at' => now(),
                'expected_cash' => $expectedCash,
                'actual_cash' => $actualCash,
                'cash_variance' => $variance,
                'breakdown' => $this->buildBreakdown($shift),
                'notes' => $notes,
            ]);

            return $shift->fresh();
        });
    }

    public function forceClose(CashierShift $shift): void
    {
        $shift->update([
            'closed_at' => now(),
            'notes' => 'Auto-closed (system).',
        ]);
    }

    public function currentForCashier(User $cashier): ?CashierShift
    {
        return CashierShift::where('cashier_id', $cashier->id)->whereNull('closed_at')->latest()->first();
    }

    protected function calculateExpectedCash(CashierShift $shift): float
    {
        $cashCollected = (float) FolioPayment::where('shift_id', $shift->id)
            ->where('method', 'cash')
            ->where('is_void', false)
            ->sum('amount');
        return (float) $shift->opening_float + $cashCollected;
    }

    protected function buildBreakdown(CashierShift $shift): array
    {
        $byMethod = FolioPayment::where('shift_id', $shift->id)
            ->where('is_void', false)
            ->selectRaw('method, count(*) as count, sum(amount) as total')
            ->groupBy('method')->get()->keyBy('method');

        return [
            'opening_float' => (float) $shift->opening_float,
            'by_method' => $byMethod->map(fn ($r) => ['count' => $r->count, 'total' => (float) $r->total])->toArray(),
            'total_collected' => (float) FolioPayment::where('shift_id', $shift->id)->where('is_void', false)->sum('amount'),
        ];
    }
}
