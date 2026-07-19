<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FolioCharged;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class PostToNightAuditJournal implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FolioCharged $event): void
    {
        $charge = $event->folioCharge;
        $folio  = $event->folio;

        $journal = JournalEntry::create([
            'property_id'  => $folio->property_id,
            'journal_type' => 'revenue',
            'reference'    => 'folio_charge_' . $charge->id,
            'posted_at'    => now()->toDateString(),
            'description'  => 'Revenue from folio charge - ' . $charge->description,
            'source_type'  => get_class($charge),
            'source_id'    => $charge->id,
            'total_debit'  => 0,
            'total_credit' => 0,
        ]);

        // Debit: Accounts Receivable
        JournalLine::create([
            'journal_entry_id' => $journal->id,
            'account_code'     => '1200-01',
            'description'      => 'Guest AR - ' . $charge->description,
            'debit'            => $charge->amount,
            'credit'           => 0,
        ]);

        // Credit: Room Revenue
        JournalLine::create([
            'journal_entry_id' => $journal->id,
            'account_code'     => '4100-01',
            'description'      => 'Room Revenue - ' . $charge->description,
            'debit'            => 0,
            'credit'           => $charge->amount,
        ]);

        $journal->update([
            'total_debit'  => $charge->amount,
            'total_credit' => $charge->amount,
        ]);
    }
}
