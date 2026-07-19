<?php

namespace App\Services\Accounting\Export;

use App\Models\JournalEntry;
use App\Models\Property;

class AccountingExporter
{
    /** Generate generic CSV journal export. */
    public function csvExport(Property $property, int $year, int $month): string
    {
        $entries = JournalEntry::where('property_id', $property->id)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->where('status', 'posted')
            ->with('lines.account')
            ->orderBy('posted_at')
            ->get();

        $rows = [['entry_no', 'date', 'description', 'account_code', 'account_name', 'debit', 'credit', 'tax_code']];
        foreach ($entries as $e) {
            foreach ($e->lines as $l) {
                $rows[] = [
                    $e->entry_no,
                    $e->posted_at->toDateString(),
                    $e->description,
                    $l->account?->code,
                    $l->account?->name,
                    $l->debit,
                    $l->credit,
                    $l->tax_code,
                ];
            }
        }

        $out = fopen('php://temp', 'r+');
        foreach ($rows as $r) fputcsv($out, $r);
        rewind($out);
        return stream_get_contents($out);
    }

    /** Mekari Jurnal-friendly CSV format. */
    public function jurnalExport(Property $property, int $year, int $month): string
    {
        return $this->csvExport($property, $year, $month);
    }

    /** Accurate-friendly CSV (different column order). */
    public function accurateExport(Property $property, int $year, int $month): string
    {
        $entries = JournalEntry::where('property_id', $property->id)
            ->where('period_year', $year)->where('period_month', $month)
            ->where('status', 'posted')->with('lines.account')->orderBy('posted_at')->get();

        $rows = [['Date', 'Voucher No', 'Description', 'Account Number', 'Account Name', 'Debit', 'Credit']];
        foreach ($entries as $e) {
            foreach ($e->lines as $l) {
                $rows[] = [
                    $e->posted_at->format('d/m/Y'),
                    $e->entry_no,
                    $e->description,
                    $l->account?->code,
                    $l->account?->name,
                    $l->debit,
                    $l->credit,
                ];
            }
        }

        $out = fopen('php://temp', 'r+');
        foreach ($rows as $r) fputcsv($out, $r);
        rewind($out);
        return stream_get_contents($out);
    }
}
