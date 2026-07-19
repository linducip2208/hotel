<?php

namespace App\Console\Commands\Telemetry;

use App\Services\License\LicenseClient;
use App\Services\Tenancy\MrrCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PushCommand extends Command
{
    protected $signature   = 'telemetry:push';
    protected $description = 'Push anonymized usage telemetry to the vendor license server';

    public function handle(LicenseClient $client, MrrCalculator $mrr): int
    {
        try {
            $payload = [
                'active_properties' => DB::table('properties')->where('is_active', true)->count(),
                'total_reservations_mtd' => DB::table('reservations')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'php_version'  => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timestamp'    => now()->toIso8601String(),
            ];

            $client->heartbeat($payload);
            $this->info('Telemetry pushed successfully.');
        } catch (\Throwable $e) {
            $this->warn("Telemetry push failed (non-fatal): {$e->getMessage()}");
        }

        return self::SUCCESS;
    }
}
