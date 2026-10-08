<?php

namespace App\Providers;

use App\Console\Commands\Accounting\ExportDailyCommand;
use App\Console\Commands\NightAuditCloseCommand;
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
            NightAuditCloseCommand::class,
            ExportDailyCommand::class,
        ]);
    }
}
