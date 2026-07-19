<?php

namespace App\Console\Commands\License;

use App\Services\License\LicenseManager;
use Illuminate\Console\Command;

class StatusCommand extends Command
{
    protected $signature = 'license:status {--json}';
    protected $description = 'Print license status JSON';

    public function handle(LicenseManager $manager): int
    {
        $status = $manager->status();
        if ($this->option('json')) {
            $this->line(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        $this->info('License status:');
        foreach ($status as $k => $v) {
            $this->line(sprintf('  %-15s %s', $k, is_scalar($v) ? var_export($v, true) : json_encode($v)));
        }
        return self::SUCCESS;
    }
}
