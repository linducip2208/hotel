<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\JournalEntryPosted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class VerifyAccountingBalance implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(JournalEntryPosted $event): void
    {
        $journal = $event->journalEntry->loadMissing('lines');

        $totalDebit  = (float) $journal->lines->sum('debit');
        $totalCredit = (float) $journal->lines->sum('credit');

        $isBalanced = abs($totalDebit - $totalCredit) < 0.01;

        if (! $isBalanced) {
            // Log imbalance for review
            \App\Models\AuditLog::create([
                'property_id' => $journal->property_id,
                'entity_type' => 'journal_entry',
                'entity_id'   => $journal->id,
                'action'      => 'imbalanced',
                'description' => sprintf(
                    'Journal entry #%d is imbalanced. Debit: %.2f, Credit: %.2f',
                    $journal->id,
                    $totalDebit,
                    $totalCredit,
                ),
                'metadata'    => [
                    'journal_id'   => $journal->id,
                    'total_debit'  => $totalDebit,
                    'total_credit' => $totalCredit,
                    'difference'   => round($totalDebit - $totalCredit, 2),
                ],
            ]);

            // Flag the journal entry
            $journal->update([
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
            ]);
        }
    }
}
