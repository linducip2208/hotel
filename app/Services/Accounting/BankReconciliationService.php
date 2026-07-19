<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\FolioPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class BankReconciliationService
{
    /**
     * Match bank statement lines against PMS folio payments.
     *
     * @param Collection<BankStatementLine> $bankStatements
     * @param Collection<FolioPayment> $folioPayments
     * @return array{matched: array, suggested: array, bank_only: array, pms_only: array}
     */
    public function match(Collection $bankStatements, Collection $folioPayments): array
    {
        $matched = [];
        $suggested = [];
        $bankOnly = [];
        $pmsOnly = [];

        $usedFolioIds = [];
        $usedBankIds = [];

        foreach ($bankStatements as $bankLine) {
            $bankAmount = (float) ($bankLine->credit > 0 ? $bankLine->credit : -$bankLine->debit);
            $bankDate = $bankLine->transaction_date;
            $bankRef = strtolower((string) $bankLine->reference_no);

            $bestMatch = null;
            $bestScore = 0;
            $bestFolioId = null;

            foreach ($folioPayments as $folioPayment) {
                if (in_array($folioPayment->id, $usedFolioIds, true)) {
                    continue;
                }

                $folioAmount = (float) $folioPayment->amount;
                $folioDate = $folioPayment->payment_date;
                $folioRef = strtolower((string) ($folioPayment->gateway_payload['reference'] ?? $folioPayment->id));

                $score = 0;
                $reasons = [];

                // Amount match (±1 for rounding)
                if (abs($bankAmount - $folioAmount) <= 1.0) {
                    $score += 1;
                    $reasons[] = 'amount';
                }

                // Date match (±1 day)
                $dayDiff = abs($bankDate->diffInDays($folioDate));
                if ($dayDiff <= 1) {
                    $score += 1;
                    $reasons[] = 'date';
                }

                // Reference similarity
                if ($bankRef && $folioRef) {
                    $similarity = 0;
                    similar_text($bankRef, $folioRef, $similarity);
                    if ($similarity > 50 || str_contains($bankRef, substr($folioRef, 0, 4)) || str_contains($folioRef, substr($bankRef, 0, 4))) {
                        $score += 1;
                        $reasons[] = 'reference';
                    }
                }

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $folioPayment;
                    $bestFolioId = $folioPayment->id;
                }
            }

            $entry = [
                'bank_line' => $bankLine,
                'folio_payment' => $bestMatch,
                'bank_amount' => $bankAmount,
                'folio_amount' => $bestMatch ? (float) $bestMatch->amount : null,
                'match_score' => $bestScore,
            ];

            if ($bestScore === 3) {
                $matched[] = $entry;
                $usedBankIds[] = $bankLine->id;
                if ($bestFolioId) {
                    $usedFolioIds[] = $bestFolioId;
                }
            } elseif ($bestScore >= 2) {
                $suggested[] = $entry;
                $usedBankIds[] = $bankLine->id;
                if ($bestFolioId) {
                    $usedFolioIds[] = $bestFolioId;
                }
            } elseif ($bestScore === 1) {
                $suggested[] = $entry;
            } else {
                $bankOnly[] = ['bank_line' => $bankLine, 'bank_amount' => $bankAmount];
            }
        }

        // Remaining folio payments not matched
        foreach ($folioPayments as $fp) {
            if (! in_array($fp->id, $usedFolioIds, true)) {
                $pmsOnly[] = ['folio_payment' => $fp, 'folio_amount' => (float) $fp->amount];
            }
        }

        return compact('matched', 'suggested', 'bank_only', 'pms_only');
    }

    /** Manually reconcile a bank statement line with a folio payment. */
    public function reconcile(int $bankStatementLineId, int $folioPaymentId): void
    {
        DB::transaction(function () use ($bankStatementLineId, $folioPaymentId) {
            $bankLine = BankStatementLine::findOrFail($bankStatementLineId);
            if ($bankLine->is_reconciled) {
                throw new RuntimeException('Bank statement line already reconciled.');
            }

            $bankLine->update([
                'is_reconciled' => true,
                'matched_journal_line_id' => $this->findJournalLineId($folioPaymentId),
            ]);

            // Update parent statement status
            $this->recalcStatementStatus($bankLine->statement_id);
        });
    }

    /** Unreconcile a bank statement line. */
    public function unreconcile(int $bankStatementLineId): void
    {
        DB::transaction(function () use ($bankStatementLineId) {
            $bankLine = BankStatementLine::findOrFail($bankStatementLineId);
            if (! $bankLine->is_reconciled) {
                throw new RuntimeException('Bank statement line is not reconciled.');
            }

            $bankLine->update([
                'is_reconciled' => false,
                'matched_journal_line_id' => null,
            ]);

            $this->recalcStatementStatus($bankLine->statement_id);
        });
    }

    /**
     * Auto-match bank statement lines to GL journal entries.
     * Matches by amount + date proximity (±3 days) with confidence scoring.
     *
     * @return array{matched: int, unmatched: int, total: int, pairs: array}
     */
    public function autoMatch(int $statementId): array
    {
        $statement = BankStatement::with('lines')->findOrFail($statementId);
        $bankLines = $statement->lines()->where('is_reconciled', false)->get();

        $propertyId = $statement->bankAccount->property_id ?? null;
        if (!$propertyId) {
            throw new RuntimeException('Bank account has no property.');
        }

        $journalLines = \App\Models\JournalLine::whereHas('entry', function ($q) use ($propertyId, $statement) {
            $q->where('property_id', $propertyId)
                ->where('status', 'posted')
                ->whereBetween('journal_date', [
                    \Carbon\Carbon::parse($statement->period_from)->subDays(3)->toDateString(),
                    \Carbon\Carbon::parse($statement->period_to)->addDays(3)->toDateString(),
                ]);
        })->whereHas('account', fn ($q) => $q->where('type', 'asset'))
            ->get();

        $matched = 0;
        $pairs = [];
        $usedJournalIds = [];

        foreach ($bankLines as $bankLine) {
            $bankAmount = (float) ($bankLine->credit > 0 ? $bankLine->credit : -$bankLine->debit);
            if ($bankAmount == 0) continue;
            $bankDate = \Carbon\Carbon::parse($bankLine->transaction_date);

            $bestMatch = null;
            $bestScore = 0;

            foreach ($journalLines as $jl) {
                if (in_array($jl->id, $usedJournalIds, true)) continue;

                $glAmount = (float) ($jl->credit > 0 ? $jl->credit : -$jl->debit);
                if ($glAmount == 0) continue;
                $glDate = \Carbon\Carbon::parse($jl->entry->journal_date);

                $score = 0;
                $dayDiff = abs($bankDate->diffInDays($glDate));

                if (abs($bankAmount - abs($glAmount)) <= 1.0) {
                    $score += 2;
                }
                if ($dayDiff <= 3) {
                    $score += 1;
                }

                if ($score > $bestScore && $score >= 2) {
                    $bestScore = $score;
                    $bestMatch = $jl;
                }
            }

            if ($bestMatch && $bestScore >= 2) {
                DB::transaction(function () use ($bankLine, $bestMatch) {
                    $bankLine->update([
                        'is_reconciled' => true,
                        'matched_journal_line_id' => $bestMatch->id,
                    ]);
                });

                $usedJournalIds[] = $bestMatch->id;
                $matched++;
                $pairs[] = [
                    'bank_line_id' => $bankLine->id,
                    'bank_amount' => $bankAmount,
                    'journal_line_id' => $bestMatch->id,
                    'gl_amount' => (float) ($bestMatch->credit > 0 ? $bestMatch->credit : -$bestMatch->debit),
                    'confidence' => $bestScore === 3 ? 'high' : 'medium',
                ];
            }
        }

        $this->recalcStatementStatus($statementId);

        return [
            'matched' => $matched,
            'unmatched' => $bankLines->count() - $matched,
            'total' => $bankLines->count(),
            'pairs' => $pairs,
        ];
    }

    /** Parse CSV or OFX bank statement file and import lines. */
    public function importBankStatement(string $filePath, int $bankAccountId): BankStatement
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $lines = match ($ext) {
            'csv' => $this->parseCsv($filePath),
            'ofx', 'qfx' => $this->parseOfx($filePath),
            default => throw new RuntimeException("Unsupported file format: {$ext}"),
        };

        if (empty($lines)) {
            throw new RuntimeException('No transactions found in file.');
        }

        return DB::transaction(function () use ($lines, $bankAccountId, $filePath) {
            $first = $lines[0];
            $last = $lines[count($lines) - 1];

            $statement = BankStatement::create([
                'bank_account_id' => $bankAccountId,
                'statement_date' => now()->toDateString(),
                'period_from' => $first['date'],
                'period_to' => $last['date'],
                'opening_balance' => (float) ($lines[0]['balance'] ?? 0) - (float) ($lines[0]['amount'] ?? 0),
                'closing_balance' => (float) ($last['balance'] ?? 0),
                'source_file' => basename($filePath),
                'status' => 'imported',
            ]);

            foreach ($lines as $line) {
                BankStatementLine::create([
                    'statement_id' => $statement->id,
                    'transaction_date' => $line['date'],
                    'description' => $line['description'] ?? '',
                    'debit' => $line['amount'] < 0 ? abs($line['amount']) : 0,
                    'credit' => $line['amount'] > 0 ? $line['amount'] : 0,
                    'balance' => $line['balance'] ?? null,
                    'reference_no' => $line['reference'] ?? null,
                ]);
            }

            return $statement;
        });
    }

    private function parseCsv(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) < 3) {
                    continue;
                }
                $row = array_combine(array_slice($headers, 0, count($data)), $data);

                $rows[] = [
                    'date' => $this->guessDateColumn($row),
                    'description' => $this->guessDescColumn($row),
                    'amount' => $this->guessAmountColumn($row),
                    'balance' => $this->guessBalanceColumn($row),
                    'reference' => $this->guessRefColumn($row),
                ];
            }
            fclose($handle);
        }
        return $rows;
    }

    private function parseOfx(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $rows = [];

        preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/s', $content, $matches);
        foreach ($matches[1] as $block) {
            preg_match('/<DTPOSTED>(\d{8})/', $block, $dateMatch);
            preg_match('/<TRNAMT>([-\d.]+)/', $block, $amtMatch);
            preg_match('/<NAME>(.*?)<\/NAME>/s', $block, $nameMatch);
            preg_match('/<FITID>(.*?)<\/FITID>/s', $block, $refMatch);

            $date = isset($dateMatch[1]) ? substr($dateMatch[1], 0, 4).'-'.substr($dateMatch[1], 4, 2).'-'.substr($dateMatch[1], 6, 2) : now()->toDateString();

            $rows[] = [
                'date' => $date,
                'description' => trim($nameMatch[1] ?? ''),
                'amount' => (float) ($amtMatch[1] ?? 0),
                'balance' => null,
                'reference' => trim($refMatch[1] ?? ''),
            ];
        }

        return $rows;
    }

    private function findJournalLineId(int $folioPaymentId): ?int
    {
        $entry = \App\Models\JournalEntry::where('source_type', 'folio_payment')
            ->where('source_id', $folioPaymentId)
            ->first();
        return $entry?->lines()->first()?->id;
    }

    private function recalcStatementStatus(int $statementId): void
    {
        $total = BankStatementLine::where('statement_id', $statementId)->count();
        $reconciled = BankStatementLine::where('statement_id', $statementId)->where('is_reconciled', true)->count();

        $status = match (true) {
            $reconciled === 0 => 'imported',
            $reconciled < $total => 'reconciling',
            default => 'reconciled',
        };

        BankStatement::where('id', $statementId)->update(['status' => $status]);
    }

    private function guessDateColumn(array $row): string
    {
        foreach (['date', 'tanggal', 'posting_date', 'transaction_date', 'tgl'] as $key) {
            if (isset($row[$key]) && $row[$key]) {
                $ts = strtotime((string) $row[$key]);
                return $ts ? date('Y-m-d', $ts) : now()->toDateString();
            }
        }
        return now()->toDateString();
    }

    private function guessDescColumn(array $row): string
    {
        foreach (['description', 'keterangan', 'desc', 'memo', 'narration'] as $key) {
            if (isset($row[$key]) && $row[$key]) {
                return (string) $row[$key];
            }
        }
        return '';
    }

    private function guessAmountColumn(array $row): float
    {
        foreach (['amount', 'jumlah', 'amount_cr', 'credit', 'debit', 'amount_dr'] as $key) {
            if (isset($row[$key]) && is_numeric($row[$key])) {
                $val = (float) $row[$key];
                return isset($row['amount_dr']) && $row['amount_dr'] > 0 ? -$val : $val;
            }
        }
        if (isset($row['debit']) && is_numeric($row['debit']) && (float) $row['debit'] > 0) {
            return -(float) $row['debit'];
        }
        return 0;
    }

    private function guessBalanceColumn(array $row): ?float
    {
        foreach (['balance', 'saldo', 'running_balance'] as $key) {
            if (isset($row[$key]) && is_numeric($row[$key])) {
                return (float) $row[$key];
            }
        }
        return null;
    }

    private function guessRefColumn(array $row): ?string
    {
        foreach (['reference', 'ref_no', 'cheque_no', 'check_no', 'fitid'] as $key) {
            if (isset($row[$key]) && $row[$key]) {
                return (string) $row[$key];
            }
        }
        return null;
    }
}
