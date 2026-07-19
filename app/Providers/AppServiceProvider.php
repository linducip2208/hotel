<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        $this->configureRateLimiters();

        $this->commands([
            \App\Console\Commands\Tenant\LifecycleCommand::class,
            \App\Console\Commands\Audit\VerifyChainCommand::class,
            \App\Console\Commands\Audit\CheckpointCommand::class,
        ]);

        // Share current property to every public/panel view so layouts never
        // hit "Undefined variable $property" when a controller forgets to pass it.
        View::composer(['public.*', 'panel.*'], function ($view) {
            $property = app()->bound('current_property')
                ? app('current_property')
                : \App\Models\Property::orderBy('id')->first();

            $view->with([
                'property' => $view->getData()['property'] ?? $property,
                'currentProperty' => $property,
            ]);
        });
    }

    /**
     * Rate limiters — protect login, booking, and crawler-facing routes from abuse.
     */
    protected function configureRateLimiters(): void
    {
        RateLimiter::for('login', fn (Request $r) => [
            Limit::perMinute(5)->by($r->input('email') ?: $r->ip()),
            Limit::perMinute(20)->by($r->ip()),
        ]);

        RateLimiter::for('booking', fn (Request $r) => Limit::perMinute(10)->by($r->ip()));

        RateLimiter::for('pseo', fn (Request $r) => Limit::perMinute(120)->by($r->ip()));

        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(60)->by(
            $r->user()?->id ?: $r->ip()
        ));

        RateLimiter::for('webhook', fn (Request $r) => Limit::perMinute(300)->by($r->ip()));
    }
}
