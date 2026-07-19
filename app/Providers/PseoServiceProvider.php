<?php

namespace App\Providers;

use App\Services\Seo\ContentGenerator;
use App\Services\Seo\SchemaBuilder;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Support\ServiceProvider;

class PseoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SchemaBuilder::class);
        $this->app->singleton(ContentGenerator::class);
        $this->app->singleton(SitemapBuilder::class);
    }

    public function boot(): void
    {
        //
    }
}
