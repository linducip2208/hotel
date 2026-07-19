<?php

namespace App\Services\Fo;

use App\Models\Folio;
use App\Models\FolioCharge;
use App\Models\FolioPayment;
use App\Services\Accounting\JournalPoster;
use App\Services\Accounting\Pb1Calculator;
use App\Services\Accounting\PpnCalculator;
use Illuminate\Support\Facades\DB;

class FolioService
{
    public function __construct(
        protected JournalPoster $journal,
        protected Pb1Calculator $pb1,
        protected PpnCalculator $ppn,
    ) {}

    public function postCharge(Folio $folio, array $data): FolioCharge
    {
        return DB::transaction(function () use ($folio, $data) {
            $taxAmount = 0;
            if (! empty($data['is_taxable'])) {
                $taxAmount = match ($data['tax_code'] ?? null) {
                    'PB1' => $this->pb1->calculate($folio->property, $data['amount']),
                    'PPN_OUT' => $this->ppn->calculate($data['amount']),
                    default => 0,
                };
            }

            $charge = $folio->charges()->create([
                'property_id' => $folio->property_id,
                'charge_date' => $data['charge_date'] ?? now()->toDateString(),
                'description' => $data['description'],
                'category' => $data['category'] ?? 'other',
                'qty' => $data['qty'] ?? 1,
                'unit_price' => $data['unit_price'] ?? $data['amount'],
                'amount' => $data['amount'],
                'tax_code' => $data['tax_code'] ?? null,
                'tax_amount' => $taxAmount,
                'is_taxable' => (bool) ($data['is_taxable'] ?? false),
                'source_type' => $data['source_type'] ?? 'manual',
                'source_ref' => $data['source_ref'] ?? null,
                'posted_by_user_id' => $data['posted_by_user_id'] ?? null,
            ]);

            $folio->recalculate();
            return $charge;
        });
    }

    public function postPayment(Folio $folio, array $data): FolioPayment
    {
        return DB::transaction(function () use ($folio, $data) {
            $payment = $folio->payments()->create([
                'property_id' => $folio->property_id,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'amount' => $data['amount'],
                'method' => $data['method'],
                'provider_id' => $data['provider_id'] ?? null,
                'reference_no' => $data['reference_no'] ?? null,
                'mdr_amount' => $data['mdr_amount'] ?? 0,
                'gateway_payload' => $data['gateway_payload'] ?? null,
                'cashier_id' => $data['cashier_id'] ?? null,
            ]);

            $folio->recalculate();
            $this->journal->postFolioPayment($folio, $payment);
            return $payment;
        });
    }

    public function applyDiscount(Folio $folio, float $amount, string $reason, ?int $userId = null): FolioCharge
    {
        return $this->postCharge($folio, [
            'description' => 'Discount: '.$reason,
            'category' => 'discount',
            'amount' => -abs($amount),
            'tax_code' => null,
            'is_taxable' => false,
            'source_type' => 'manual',
            'posted_by_user_id' => $userId,
        ]);
    }

    public function transfer(Folio $from, Folio $to, float $amount, string $description = 'Folio transfer'): void
    {
        DB::transaction(function () use ($from, $to, $amount, $description) {
            $this->postCharge($from, [
                'description' => $description.' (transfer out)',
                'category' => 'other',
                'amount' => -abs($amount),
                'is_taxable' => false,
                'source_type' => 'transfer',
            ]);
            $this->postCharge($to, [
                'description' => $description.' (transfer in)',
                'category' => 'other',
                'amount' => abs($amount),
                'is_taxable' => false,
                'source_type' => 'transfer',
            ]);
        });
    }
}
