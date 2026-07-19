<?php

namespace App\Console\Commands\License;

use App\Models\LocalLicense;
use App\Services\License\FingerprintGenerator;
use Illuminate\Console\Command;

class BootstrapCommand extends Command
{
    protected $signature = 'license:bootstrap {--force : Regenerate fingerprint and install_id}';
    protected $description = 'Generate install_id and device fingerprint for first-time install';

    public function handle(FingerprintGenerator $gen): int
    {
        $local = LocalLicense::firstOrCreate(['id' => 1], ['status' => 'unpaired']);

        if ($local->fingerprint && ! $this->option('force')) {
            $this->info("License already bootstrapped.");
            $this->line("  install_id  : {$local->install_id}");
            $this->line("  fingerprint : {$local->fingerprint}");
            $this->line("  status      : {$local->status}");
            return self::SUCCESS;
        }

        $installId = $gen->newInstallId();
        $fingerprint = $gen->generate($installId);
        $local->fill([
            'install_id' => $installId,
            'fingerprint' => $fingerprint,
            'status' => 'unpaired',
        ])->save();

        $this->info("Bootstrap complete.");
        $this->line("  install_id  : {$installId}");
        $this->line("  fingerprint : {$fingerprint}");
        $this->line('');
        $this->line("Next: open /setup/wizard in browser to pair license.");
        return self::SUCCESS;
    }
}
