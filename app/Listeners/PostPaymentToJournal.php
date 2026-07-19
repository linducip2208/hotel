<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\FolioPaymentReceived;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class PostPaymentToJournal implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FolioPaymentReceived $event): void
    {
        $payment = $event->folioPayment;
        $folio   = $event->folio;

        $journal = JournalEntry::create([
            'property_id'  => $folio->property_id,
            'journal_type' => 'payment',
            'reference'    => 'folio_payment_' . $payment->id,
            'posted_at'    => now()->toDateString(),
            'description'  => 'Guest payment via ' . ($payment->payment_method ?? 'cash'),
            'source_type'  => get_class($payment),
            'source_id'    => $payment->id,
            'total_debit'  => 0,
            'total_credit' => 0,
        ]);

        // Debit: Cash/Bank
        JournalLine::create([
            'journal_entry_id' => $journal->id,
            'account_code'     => '1100-01',
            'description'      => 'Cash received - ' . ($payment->payment_method ?? 'cash'),
            'debit'            => $payment->amount,
            'credit'           => 0,
        ]);

        // Credit: Accounts Receivable
        JournalLine::create([
            'journal_entry_id' => $journal->id,
            'account_code'     => '1200-01',
            'description'      => 'Guest AR reduction',
            'debit'            => 0,
            'credit'           => $payment->amount,
        ]);

        $journal->update([
            'total_debit'  => $payment->amount,
            'total_credit' => $payment->amount,
        ]);
    }
}
