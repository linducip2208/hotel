<?php

use App\Http\Controllers\Admin\TelemetryReceiverController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Portal\OwnerPortalController;
use App\Http\Controllers\Public\AvailabilityWidgetController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\BookingButtonController;
use App\Http\Controllers\Public\BookingEngineController;
use App\Http\Controllers\Public\CartRecoveryController;
use App\Http\Controllers\Public\CmsPageController;
use App\Http\Controllers\Public\CurrencyController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\IcalController;
use App\Http\Controllers\Public\KioskController;
use App\Http\Controllers\Public\NewsletterController;
use App\Http\Controllers\Public\QrMenuController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Public\RoomController;
use App\Http\Controllers\Public\TenantSignupController;
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
    Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk');
    Route::post('/kiosk/lookup', [KioskController::class, 'lookup'])->name('kiosk.lookup');
    Route::post('/kiosk/checkin', [KioskController::class, 'checkin'])->name('kiosk.checkin');
    Route::get('/kiosk/print/{id}', [KioskController::class, 'printReceipt'])->name('kiosk.print');
});

// Public SaaS signup
Route::get('/signup', [TenantSignupController::class, 'show'])->name('saas.signup.show');
Route::post('/signup', [TenantSignupController::class, 'store'])->name('saas.signup');

// Abandoned cart recovery
Route::post('/booking/cart/track', [CartRecoveryController::class, 'track'])->name('booking.cart.track');
Route::get('/booking/cart/recover/{token}', [CartRecoveryController::class, 'recover'])->name('booking.cart.recover');

// QR Menu — public guest scan
Route::get('/menu/{outletId}/{tableId}', [QrMenuController::class, 'show'])->name('qr-menu');
Route::post('/menu/order', [QrMenuController::class, 'placeOrder'])->name('qr-menu.order');

// Telemetry receiver — public endpoint for client deployments
Route::post('/api/license/heartbeat-receive', [TelemetryReceiverController::class, 'heartbeat'])->withoutMiddleware(['web']);

// Digital Registration public form
Route::get('registration/{token}', [RegistrationController::class, 'show'])->name('registration.form');
Route::post('registration/{token}', [RegistrationController::class, 'submit'])->name('registration.submit');
Route::get('registration-thanks', [RegistrationController::class, 'thanks'])->name('registration.thanks');

// Public blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/feed.xml', [BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Public docs site (license-exempt)
Route::get('/docs', [DocsController::class, 'index'])->name('docs.index');
Route::get('/docs/{slug}.md', [DocsController::class, 'raw'])->name('docs.raw')->where('slug', '[A-Za-z0-9_-]+');
Route::get('/docs/{slug}', [DocsController::class, 'show'])->name('docs.show')->where('slug', '[A-Za-z0-9_-]+');

// Language switcher
Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Currency selector
Route::get('currency/switch', [CurrencyController::class, 'switch'])->name('currency.switch');

// Newsletter
Route::middleware(['license'])->group(function () {
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
    Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

    // ICS / iCal export for a booking
    Route::get('/booking/{ref}/ical', [IcalController::class, 'download'])->name('booking.ical');

    // CMS static pages
    Route::get('/page/{slug}', [CmsPageController::class, 'show'])->name('page.show')->where('slug', '[A-Za-z0-9\-]+');

    // Public embeddable widgets
    Route::get('/widget/availability', [AvailabilityWidgetController::class, 'show'])->name('widget.availability');
    Route::get('/widget/book-button', [BookingButtonController::class, 'show'])->name('widget.book-button');
});

require __DIR__.'/auth.php';

// Owner Portal (Investor Dashboard)
Route::middleware(['auth'])->prefix('owner-portal')->name('owner-portal.')->group(function () {
    Route::get('/', [OwnerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('financials', [OwnerPortalController::class, 'financials'])->name('financials');
    Route::get('distributions', [OwnerPortalController::class, 'distributions'])->name('distributions');
    Route::get('documents/{id}/download', [OwnerPortalController::class, 'downloadDocument'])->name('documents.download');
});

// License pairing v3 (whitelabel.co.id marketplace)
require __DIR__.'/pair-routes.php';
