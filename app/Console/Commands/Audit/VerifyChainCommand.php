<?php

namespace App\Console\Commands\Audit;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class VerifyChainCommand extends Command
{
    protected $signature = 'audit:verify-chain {--from-id=} {--to-id=}';
    protected $description = 'Verify audit log hash chain integrity';

    public function handle(): int
    {
        $q = AuditLog::query()->orderBy('id');
        if ($f = $this->option('from-id')) $q->where('id', '>=', (int) $f);
        if ($t = $this->option('to-id')) $q->where('id', '<=', (int) $t);

        $tampered = [];
        $brokenLinks = [];
        $previousHash = null;

        $q->chunk(500, function ($entries) use (&$tampered, &$brokenLinks, &$previousHash) {
            foreach ($entries as $entry) {
                if ($entry->previous_hash !== $previousHash) {
                    $brokenLinks[] = $entry->id;
                }
                if (! $entry->verifyHash()) {
                    $tampered[] = $entry->id;
                }
                $previousHash = $entry->entry_hash;
            }
        });

        if (empty($tampered) && empty($brokenLinks)) {
            $this->info('✓ Audit chain intact.');
            return self::SUCCESS;
        }

        if ($tampered) $this->error('✗ Tampered entries: '.implode(',', $tampered));
        if ($brokenLinks) $this->error('✗ Broken links at: '.implode(',', $brokenLinks));
        return self::FAILURE;
    }
}
