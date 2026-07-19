<?php

namespace App\Providers;

use App\Services\Integrations\AdapterFactory;
use App\Services\Integrations\ProviderRegistry;
use Illuminate\Support\ServiceProvider;

class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProviderRegistry::class);
        $this->app->singleton(AdapterFactory::class);
    }

    public function boot(): void
    {
        //
    }
}
