<?php

namespace App\Console\Commands\License;

use App\Models\LocalLicense;
use Illuminate\Console\Command;

class RevokeCommand extends Command
{
    protected $signature = 'license:revoke
                            {license_id : License ID or install_id to revoke}
                            {--reason= : Reason for revocation (audit trail)}
                            {--force : Skip confirmation prompt}
                            {--json : Output as JSON}';

    protected $description = 'Revoke an issued license and add to revocation list';

    public function handle(): int
    {
        $licenseId = $this->argument('license_id');
        $reason = $this->option('reason') ?: 'Revoked by administrator';

        if (! $this->option('force') && ! $this->confirm("Revoke license '{$licenseId}'? This will immediately disable the property's access.")) {
            $this->info('Revocation cancelled.');
            return self::SUCCESS;
        }

        $license = $this->findLicense($licenseId);

        if (! $license) {
            $this->error("License not found: {$licenseId}");
            return self::FAILURE;
        }

        if ($license->status === 'revoked') {
            $this->warn('License is already revoked.');
            return self::SUCCESS;
        }

        $previousStatus = $license->status;
        $license->update([
            'status'         => 'revoked',
            'degrade_reason' => $reason,
        ]);

        $this->addToRevocationList($licenseId, $reason);

        $this->logRevocationEvent($licenseId, $previousStatus, $reason);

        $this->incrementRevocationVersion();

        if ($this->option('json')) {
            $this->line(json_encode([
                'success'          => true,
                'license_id'       => $licenseId,
                'previous_status'  => $previousStatus,
                'new_status'       => 'revoked',
                'reason'           => $reason,
                'revoked_at'       => now()->toIso8601String(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('License revoked successfully.');
        $this->line("  License ID:  {$licenseId}");
        $this->line("  From:        {$previousStatus}");
        $this->line("  To:          revoked");
        $this->line("  Reason:      {$reason}");
        $this->line("  Revoked at:  ".now()->toIso8601String());
        $this->newLine();
        $this->warn('On the next heartbeat, the client will receive a revocation notice.');
        $this->warn('The property installation will be locked immediately.');

        return self::SUCCESS;
    }

    protected function findLicense(string $licenseId): ?LocalLicense
    {
        return LocalLicense::where('install_id', $licenseId)
            ->orWhere('id', is_numeric($licenseId) ? (int) $licenseId : 0)
            ->orderBy('id')
            ->first();
    }

    protected function addToRevocationList(string $licenseId, string $reason): void
    {
        if (! class_exists(\App\Models\LicenseRevocation::class)) {
            return;
        }

        try {
            \App\Models\LicenseRevocation::updateOrCreate(
                ['license_id' => $licenseId],
                [
                    'reason'       => $reason,
                    'revoked_at'   => now(),
                    'source_ip'    => request()?->ip(),
                ]
            );
        } catch (\Throwable $e) {
            $this->warn('Could not persist to revocation table: '.$e->getMessage());
        }
    }

    protected function logRevocationEvent(string $licenseId, string $previousStatus, string $reason): void
    {
        if (! class_exists(\App\Models\LicenseEvent::class)) {
            return;
        }

        try {
            \App\Models\LicenseEvent::create([
                'event'     => 'revoked',
                'payload'   => [
                    'license_id'       => $licenseId,
                    'previous_status'  => $previousStatus,
                    'reason'           => $reason,
                ],
                'source_ip' => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            $this->warn('Could not log revocation event: '.$e->getMessage());
        }
    }

    protected function incrementRevocationVersion(): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        $currentVersion = 0;
        if (preg_match('/^LICENSE_REVOCATION_VERSION=(\d+)/m', $content, $m)) {
            $currentVersion = (int) $m[1];
            $newVersion = $currentVersion + 1;
            $content = preg_replace(
                '/^LICENSE_REVOCATION_VERSION=.*/m',
                "LICENSE_REVOCATION_VERSION={$newVersion}",
                $content
            );
        } else {
            $content .= "\nLICENSE_REVOCATION_VERSION=1\n";
        }

        file_put_contents($envPath, $content);
    }
}
