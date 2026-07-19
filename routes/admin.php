<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\TelemetryController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware(['auth:admin'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('licenses', LicenseController::class);
        Route::post('licenses/{id}/revoke', [LicenseController::class, 'revoke'])->name('licenses.revoke');
        Route::post('licenses/{id}/extend', [LicenseController::class, 'extend'])->name('licenses.extend');
        Route::post('licenses/{id}/regenerate', [LicenseController::class, 'regenerate'])->name('licenses.regenerate');

        Route::resource('tenants', TenantController::class);
        Route::post('tenants/{id}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{id}/resume', [TenantController::class, 'resume'])->name('tenants.resume');
        Route::post('tenants/{id}/provision', [TenantController::class, 'provision'])->name('tenants.provision');
        Route::post('tenants/{id}/impersonate', [TenantController::class, 'impersonate'])->name('tenants.impersonate');

        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/', [BillingController::class, 'index'])->name('index');
            Route::get('subscriptions', [BillingController::class, 'subscriptions'])->name('subscriptions');
            Route::get('invoices', [BillingController::class, 'invoices'])->name('invoices');
            Route::get('coupons', [BillingController::class, 'coupons'])->name('coupons');
            Route::post('coupons', [BillingController::class, 'storeCoupon'])->name('coupons.store');
            Route::get('failed-payments', [BillingController::class, 'failedPayments'])->name('failed');
        });

        Route::prefix('telemetry')->name('telemetry.')->group(function () {
            Route::get('/', [TelemetryController::class, 'index'])->name('index');
            Route::get('errors', [TelemetryController::class, 'errors'])->name('errors');
            Route::get('health', [TelemetryController::class, 'health'])->name('health');
        });

        Route::prefix('support')->name('support.')->group(function () {
            Route::get('tickets', [SupportController::class, 'tickets'])->name('tickets');
            Route::get('tickets/{id}', [SupportController::class, 'showTicket'])->name('tickets.show');
            Route::post('tickets/{id}/reply', [SupportController::class, 'reply'])->name('tickets.reply');
            Route::get('kb', [SupportController::class, 'kb'])->name('kb');
        });

        Route::resource('admin-users', AdminUserController::class);

        Route::prefix('system')->name('system.')->group(function () {
            Route::get('feature-flags', [SystemController::class, 'flags'])->name('flags');
            Route::patch('feature-flags', [SystemController::class, 'updateFlags'])->name('flags.update');
            Route::get('plans', [SystemController::class, 'plans'])->name('plans');
            Route::post('plans', [SystemController::class, 'storePlan'])->name('plans.store');
            Route::get('email-templates', [SystemController::class, 'emailTemplates'])->name('email-templates');
            Route::get('audit-log', [SystemController::class, 'auditLog'])->name('audit');
        });
    });
});
