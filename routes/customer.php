<?php

use App\Http\Controllers\Portal\AuthController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\OrderController;
use App\Http\Controllers\Portal\GuestAppController;
use App\Http\Controllers\Portal\MyBookingController;
use App\Http\Controllers\Portal\RoomServiceController;
use App\Http\Controllers\Portal\GuestRequestController;
use Illuminate\Support\Facades\Route;

Route::name('customer.')->group(function () {

    Route::middleware('guest:customer')->group(function () {
        Route::get('/portal/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/portal/login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:customer')->group(function () {
        Route::get('/portal', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/portal/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/portal/bookings', [OrderController::class, 'index'])->name('bookings');
        Route::get('/portal/bookings/{id}', [OrderController::class, 'show'])->name('bookings.show');

        Route::get('/portal/invoices', [OrderController::class, 'invoices'])->name('invoices');
        Route::get('/portal/invoices/{id}', [OrderController::class, 'invoiceShow'])->name('invoices.show');
        Route::post('/portal/invoices/{id}/upload', [OrderController::class, 'storePayment'])->name('invoices.payment');

        // Guest self-service portal
        Route::prefix('guest-portal')->name('guest.')->group(function () {
            Route::get('/', [GuestAppController::class, 'dashboard'])->name('dashboard');
            Route::get('booking', [MyBookingController::class, 'index'])->name('booking');
            Route::get('room-service', [RoomServiceController::class, 'index'])->name('room-service');
            Route::post('room-service/order', [RoomServiceController::class, 'order'])->name('room-service.order');
            Route::get('requests', [GuestRequestController::class, 'index'])->name('requests');
            Route::post('requests', [GuestRequestController::class, 'store'])->name('requests.store');
            Route::get('chat', [GuestAppController::class, 'chat'])->name('chat');
        });
    });

});
