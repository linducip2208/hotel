<?php

namespace App\Providers;

use App\Console\Commands\License\BootstrapCommand;
use App\Console\Commands\License\DiagnosticCommand;
use App\Console\Commands\License\HeartbeatCommand;
use App\Console\Commands\License\HeartbeatRetryCommand;
use App\Console\Commands\License\StatusCommand;
use App\Services\License\FingerprintGenerator;
use App\Services\License\LicenseClient;
use App\Services\License\LicenseManager;
use App\Services\License\TokenVerifier;
use Illuminate\Support\ServiceProvider;

class LicenseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (class_exists(TokenVerifier::class)) {
            $this->app->singleton(TokenVerifier::class);
        }
        if (class_exists(FingerprintGenerator::class)) {
            $this->app->singleton(FingerprintGenerator::class);
        }
        if (class_exists(LicenseClient::class)) {
            $this->app->singleton(LicenseClient::class);
        }
        if (class_exists(LicenseManager::class)) {
            $this->app->singleton(LicenseManager::class);
        }
    }

    public function boot(): void
    {
        $commands = array_filter([
            BootstrapCommand::class,
            HeartbeatCommand::class,
            HeartbeatRetryCommand::class,
            StatusCommand::class,
            DiagnosticCommand::class,
        ], 'class_exists');

        if (! empty($commands)) {
            $this->commands($commands);
        }
    }
}
