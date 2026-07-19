<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\Folio;
use App\Models\FolioPayment;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JournalPoster
{
    /** Post double-entry. Lines = [['account_code'=>..., 'debit'=>..., 'credit'=>..., 'description'=>...], ...] */
    public function post(int $propertyId, string $description, array $lines, ?string $sourceType = null, ?int $sourceId = null): JournalEntry
    {
        return DB::transaction(function () use ($propertyId, $description, $lines, $sourceType, $sourceId) {
            $totalDebit = 0;
            $totalCredit = 0;
            $resolved = [];

            foreach ($lines as $i => $l) {
                $account = ChartOfAccount::where('property_id', $propertyId)->where('code', $l['account_code'])->first();
                if (! $account) {
                    throw new \RuntimeException("Unknown COA code {$l['account_code']} for property {$propertyId}");
                }
                $debit = (float) ($l['debit'] ?? 0);
                $credit = (float) ($l['credit'] ?? 0);
                $totalDebit += $debit;
                $totalCredit += $credit;
                $resolved[] = ['account_id' => $account->id, 'description' => $l['description'] ?? $description, 'debit' => $debit, 'credit' => $credit, 'tax_code' => $l['tax_code'] ?? null, 'line_no' => $i + 1];
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new \RuntimeException("Unbalanced journal: debit={$totalDebit} credit={$totalCredit}");
            }

            $entry = JournalEntry::create([
                'property_id' => $propertyId,
                'entry_no' => 'JE-'.now()->format('Ym').'-'.Str::upper(Str::random(6)),
                'posted_at' => now()->toDateString(),
                'period_year' => (int) now()->format('Y'),
                'period_month' => (int) now()->format('m'),
                'description' => $description,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'status' => 'posted',
            ]);

            foreach ($resolved as $r) {
                $entry->lines()->create($r);
            }
            return $entry;
        });
    }

    public function postFolioPayment(Folio $folio, FolioPayment $p): JournalEntry
    {
        $debitAccount = match ($p->method) {
            'cash' => '1-1010',
            'card', 'qris', 'transfer' => '1-1020',
            default => '1-1100',
        };

        return $this->post(
            $folio->property_id,
            "Folio settle #{$folio->folio_no}",
            [
                ['account_code' => $debitAccount, 'debit' => $p->amount - $p->mdr_amount, 'description' => 'Cash/Bank in'],
                $p->mdr_amount > 0
                    ? ['account_code' => '6-1070', 'debit' => $p->mdr_amount, 'description' => 'PG MDR']
                    : ['account_code' => $debitAccount, 'debit' => 0, 'credit' => 0, 'description' => 'noop'],
                ['account_code' => '1-1100', 'credit' => $p->amount, 'description' => 'Settle piutang tamu'],
            ],
            'folio_payment',
            $p->id
        );
    }
}
