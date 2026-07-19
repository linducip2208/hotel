<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/health',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/portal.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/customer.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/pseo.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'license'     => \App\Http\Middleware\EnsureLicenseValid::class,
            'pseo.cache'  => \App\Http\Middleware\CachePseoResponse::class,
            'role'        => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'  => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'property'    => \App\Http\Middleware\ResolveCurrentProperty::class,
            'idempotency' => \App\Http\Middleware\IdempotencyKey::class,
            'captcha'     => \App\Http\Middleware\VerifyCaptcha::class,
            'tenancy'     => \App\Http\Middleware\InitializeTenancy::class,
            'guest'       => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\InjectLicenseStatus::class,
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->web(prepend: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\RequirePair::class,
        ]);

        // Trust reverse-proxy headers (X-Forwarded-*) — needed for force-HTTPS detection.
        $middleware->trustProxies(at: '*');

        if (env('APP_MODE', 'standalone') === 'saas') {
            $middleware->web(prepend: [
                \App\Http\Middleware\InitializeTenancy::class,
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        \App\Providers\AppServiceProvider::class,
        \App\Providers\EventServiceProvider::class,
        \App\Providers\LicenseServiceProvider::class,
        \App\Providers\IntegrationServiceProvider::class,
        \App\Providers\AccountingServiceProvider::class,
        \App\Providers\PseoServiceProvider::class,
        \App\Providers\ObserverServiceProvider::class,
    ])
    ->create();
