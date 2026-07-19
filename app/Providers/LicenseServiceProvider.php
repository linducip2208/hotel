<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class LicenseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (class_exists(\App\Services\License\TokenVerifier::class)) {
            $this->app->singleton(\App\Services\License\TokenVerifier::class);
        }
        if (class_exists(\App\Services\License\FingerprintGenerator::class)) {
            $this->app->singleton(\App\Services\License\FingerprintGenerator::class);
        }
        if (class_exists(\App\Services\License\LicenseClient::class)) {
            $this->app->singleton(\App\Services\License\LicenseClient::class);
        }
        if (class_exists(\App\Services\License\LicenseManager::class)) {
            $this->app->singleton(\App\Services\License\LicenseManager::class);
        }
    }

    public function boot(): void
    {
        $commands = array_filter([
            \App\Console\Commands\License\BootstrapCommand::class,
            \App\Console\Commands\License\HeartbeatCommand::class,
            \App\Console\Commands\License\HeartbeatRetryCommand::class,
            \App\Console\Commands\License\StatusCommand::class,
            \App\Console\Commands\License\DiagnosticCommand::class,
        ], 'class_exists');

        if (! empty($commands)) {
            $this->commands($commands);
        }
    }
}
