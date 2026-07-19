<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');

    Route::get('two-factor', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
    Route::post('two-factor', [TwoFactorChallengeController::class, 'store']);
    Route::post('two-factor/recovery', [TwoFactorChallengeController::class, 'verifyRecoveryCode'])->name('two-factor.recovery.verify');
});

Route::middleware('auth')->group(function () {
    Route::get('two-factor/setup', [TwoFactorChallengeController::class, 'setup'])->name('two-factor.setup');
    Route::post('two-factor/enable', [TwoFactorChallengeController::class, 'enable'])->name('two-factor.enable');
    Route::post('two-factor/disable', [TwoFactorChallengeController::class, 'disable'])->name('two-factor.disable');
    Route::get('two-factor/recovery', [TwoFactorChallengeController::class, 'recoveryCodes'])->name('two-factor.recovery');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
