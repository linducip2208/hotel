<?php

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\RoomController;
use App\Http\Controllers\Public\BookingEngineController;
use App\Http\Controllers\Setup\WizardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['license'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
    Route::get('/terms', [HomeController::class, 'terms'])->name('terms');

    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/{slug}', [RoomController::class, 'show'])->name('rooms.show');

    Route::get('/booking', [BookingEngineController::class, 'search'])->name('booking.search');
    Route::post('/booking/search', [BookingEngineController::class, 'results'])->name('booking.results');
    Route::get('/booking/checkout', [BookingEngineController::class, 'checkout'])->name('booking.checkout');
    Route::post('/booking/checkout', [BookingEngineController::class, 'submit'])->name('booking.submit');
    Route::get('/booking/confirmation/{ref}', [BookingEngineController::class, 'confirmation'])->name('booking.confirmation');
    Route::post('/booking/{ref}/payment-callback', [BookingEngineController::class, 'paymentCallback'])
        ->name('booking.payment-callback')
        ->withoutMiddleware(['web']);
    Route::get('/booking/{ref}/payment-return', [BookingEngineController::class, 'paymentReturn'])
        ->name('booking.payment-return');
});

Route::prefix('setup')->name('setup.')->group(function () {
    Route::get('/wizard', [WizardController::class, 'show'])->name('wizard');
    Route::post('/wizard/connection-check', [WizardController::class, 'connectionCheck'])->name('wizard.check');
    Route::post('/wizard/pair', [WizardController::class, 'pair'])->name('wizard.pair');
    Route::post('/wizard/property', [WizardController::class, 'property'])->name('wizard.property');
    Route::post('/wizard/admin', [WizardController::class, 'createAdmin'])->name('wizard.admin');
    Route::get('/wizard/done', [WizardController::class, 'done'])->name('wizard.done');
});

// Kiosk self check-in
Route::middleware(['license'])->group(function () {
    Route::get('/kiosk', [App\Http\Controllers\Public\KioskController::class, 'index'])->name('kiosk');
    Route::post('/kiosk/lookup', [App\Http\Controllers\Public\KioskController::class, 'lookup'])->name('kiosk.lookup');
    Route::post('/kiosk/checkin', [App\Http\Controllers\Public\KioskController::class, 'checkin'])->name('kiosk.checkin');
    Route::get('/kiosk/print/{id}', [App\Http\Controllers\Public\KioskController::class, 'printReceipt'])->name('kiosk.print');
});

// Public SaaS signup
Route::get('/signup', [\App\Http\Controllers\Public\TenantSignupController::class, 'show'])->name('saas.signup.show');
Route::post('/signup', [\App\Http\Controllers\Public\TenantSignupController::class, 'store'])->name('saas.signup');

// Abandoned cart recovery
Route::post('/booking/cart/track', [App\Http\Controllers\Public\CartRecoveryController::class, 'track'])->name('booking.cart.track');
Route::get('/booking/cart/recover/{token}', [App\Http\Controllers\Public\CartRecoveryController::class, 'recover'])->name('booking.cart.recover');

// QR Menu — public guest scan
Route::get('/menu/{outletId}/{tableId}', [App\Http\Controllers\Public\QrMenuController::class, 'show'])->name('qr-menu');
Route::post('/menu/order', [App\Http\Controllers\Public\QrMenuController::class, 'placeOrder'])->name('qr-menu.order');

// Telemetry receiver — public endpoint for client deployments
Route::post('/api/license/heartbeat-receive', [\App\Http\Controllers\Admin\TelemetryReceiverController::class, 'heartbeat'])->withoutMiddleware(['web']);

// Digital Registration public form
Route::get('registration/{token}', [\App\Http\Controllers\Public\RegistrationController::class, 'show'])->name('registration.form');
Route::post('registration/{token}', [\App\Http\Controllers\Public\RegistrationController::class, 'submit'])->name('registration.submit');
Route::get('registration-thanks', [\App\Http\Controllers\Public\RegistrationController::class, 'thanks'])->name('registration.thanks');

// Public blog
Route::get('/blog', [\App\Http\Controllers\Public\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/feed.xml', [\App\Http\Controllers\Public\BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/category/{slug}', [\App\Http\Controllers\Public\BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [\App\Http\Controllers\Public\BlogController::class, 'show'])->name('blog.show');

// Public docs site (license-exempt)
Route::get('/docs', [\App\Http\Controllers\DocsController::class, 'index'])->name('docs.index');
Route::get('/docs/{slug}.md', [\App\Http\Controllers\DocsController::class, 'raw'])->name('docs.raw')->where('slug', '[A-Za-z0-9_-]+');
Route::get('/docs/{slug}', [\App\Http\Controllers\DocsController::class, 'show'])->name('docs.show')->where('slug', '[A-Za-z0-9_-]+');

// Language switcher
Route::get('locale/{locale}', [\App\Http\Controllers\LocaleController::class, 'switch'])->name('locale.switch');

require __DIR__.'/auth.php';

// Owner Portal (Investor Dashboard)
Route::middleware(['auth'])->prefix('owner-portal')->name('owner-portal.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Portal\OwnerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('financials', [\App\Http\Controllers\Portal\OwnerPortalController::class, 'financials'])->name('financials');
    Route::get('distributions', [\App\Http\Controllers\Portal\OwnerPortalController::class, 'distributions'])->name('distributions');
    Route::get('documents/{id}/download', [\App\Http\Controllers\Portal\OwnerPortalController::class, 'downloadDocument'])->name('documents.download');
});

// License pairing v3 (whitelabel.co.id marketplace)
require __DIR__.'/pair-routes.php';
