<?php

namespace App\Providers;

use App\Services\Accounting\JournalPoster;
use App\Services\Accounting\NightAuditService;
use App\Services\Accounting\Pb1Calculator;
use App\Services\Accounting\PpnCalculator;
use Illuminate\Support\ServiceProvider;

class AccountingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(JournalPoster::class);
        $this->app->singleton(NightAuditService::class);
        $this->app->singleton(Pb1Calculator::class);
        $this->app->singleton(PpnCalculator::class);
    }

    public function boot(): void
    {
        $this->commands([
            \App\Console\Commands\NightAuditCloseCommand::class,
            \App\Console\Commands\Accounting\ExportDailyCommand::class,
        ]);
    }
}
