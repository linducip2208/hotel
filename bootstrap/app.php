<?php

use App\Http\Middleware\CachePseoResponse;
use App\Http\Middleware\EnsureLicenseValid;
use App\Http\Middleware\IdempotencyKey;
use App\Http\Middleware\InitializeTenancy;
use App\Http\Middleware\InjectLicenseStatus;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RequirePair;
use App\Http\Middleware\ResolveCurrentProperty;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\VerifyCaptcha;
use App\Providers\AccountingServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\IntegrationServiceProvider;
use App\Providers\LicenseServiceProvider;
use App\Providers\ObserverServiceProvider;
use App\Providers\PseoServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/health',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/portal.php'));
            Route::middleware('web')
                ->group(base_path('routes/customer.php'));
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            Route::middleware('web')
                ->group(base_path('routes/pseo.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'license' => EnsureLicenseValid::class,
            'pseo.cache' => CachePseoResponse::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'property' => ResolveCurrentProperty::class,
            'idempotency' => IdempotencyKey::class,
            'captcha' => VerifyCaptcha::class,
            'tenancy' => InitializeTenancy::class,
            'guest' => RedirectIfAuthenticated::class,
        ]);

        $middleware->web(append: [
            InjectLicenseStatus::class,
            SetLocale::class,
        ]);

        $middleware->web(prepend: [
            SecurityHeaders::class,
            RequirePair::class,
        ]);

        // Trust reverse-proxy headers (X-Forwarded-*) — needed for force-HTTPS detection.
        $middleware->trustProxies(at: '*');

        if (env('APP_MODE', 'standalone') === 'saas') {
            $middleware->web(prepend: [
                InitializeTenancy::class,
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        AppServiceProvider::class,
        EventServiceProvider::class,
        LicenseServiceProvider::class,
        IntegrationServiceProvider::class,
        AccountingServiceProvider::class,
        PseoServiceProvider::class,
        ObserverServiceProvider::class,
    ])
    ->create();
