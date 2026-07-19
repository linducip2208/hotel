<?php

use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\Fo\ReservationController;
use App\Http\Controllers\Panel\Fo\WalkinController;
use App\Http\Controllers\Panel\Fo\FolioController;
use App\Http\Controllers\Panel\Fo\NightAuditController;
use App\Http\Controllers\Panel\Hk\HousekeepingController;
use App\Http\Controllers\Panel\Hk\LostFoundController;
use App\Http\Controllers\Panel\Hk\MinibarController;
use App\Http\Controllers\Panel\Marketing\WhatsAppBlastController;
use App\Http\Controllers\Panel\Fo\DigitalKeyController;
use App\Http\Controllers\Panel\Pos\PosController;
use App\Http\Controllers\Panel\ChannelManager\ChannelController;
use App\Http\Controllers\Panel\Accounting\JournalController;
use App\Http\Controllers\Panel\Accounting\ChartOfAccountsController;
use App\Http\Controllers\Panel\Accounting\ArController;
use App\Http\Controllers\Panel\Accounting\ApController;
use App\Http\Controllers\Panel\Accounting\ReportController as AccReportController;
use App\Http\Controllers\Panel\Reports\OperationsReportController;
use App\Http\Controllers\Panel\Settings\PropertyController;
use App\Http\Controllers\Panel\Settings\IntegrationController;
use App\Http\Controllers\Panel\Settings\TaxConfigController;
use App\Http\Controllers\Panel\Settings\UserController;
use App\Http\Controllers\Panel\Settings\LicenseController;
use App\Http\Controllers\Panel\Settings\RoomTypeController;
use App\Http\Controllers\Panel\Settings\RoomController;
use App\Http\Controllers\Panel\GuestPortal\GuestController;
use App\Http\Controllers\Panel\MultiPropertyController;
use App\Http\Controllers\Public\GuestPortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->name('portal.')->middleware(['license'])->group(function () {
    Route::get('booking/{ref}', [GuestPortalController::class, 'manageBooking'])->name('manage');
    Route::get('pre-checkin/{ref}', [GuestPortalController::class, 'preCheckin'])->name('pre-checkin');
    Route::post('pre-checkin/{ref}', [GuestPortalController::class, 'submitPreCheckin'])->name('pre-checkin.submit');
    Route::get('checkin/{ref}', [GuestPortalController::class, 'selfCheckin'])->name('checkin');
    Route::get('stay/{token}', [GuestPortalController::class, 'inStay'])->name('in-stay');
    Route::get('folio/{ref}', [GuestPortalController::class, 'folio'])->name('folio');
    Route::get('review/{ref}', [GuestPortalController::class, 'review'])->name('review');
    Route::post('review/{ref}', [GuestPortalController::class, 'submitReview'])->name('review.submit');
});

Route::prefix('panel')->name('panel.')->middleware(['license', 'auth', 'property'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('search', [\App\Http\Controllers\Panel\SearchController::class, 'global'])->name('search');
    Route::post('property-switch/{id}', [\App\Http\Controllers\Panel\PropertySwitcherController::class, 'switch'])->name('property.switch');
    Route::post('approvals/{id}/approve', [DashboardController::class, 'approveItem'])->name('panel.approvals.approve');
    Route::post('approvals/{id}/reject', [DashboardController::class, 'rejectItem'])->name('panel.approvals.reject');
    Route::get('audit', [\App\Http\Controllers\Panel\AuditController::class, 'index'])->name('audit.index');
    Route::get('audit/{id}', [\App\Http\Controllers\Panel\AuditController::class, 'show'])->name('audit.show');

    Route::prefix('fo/shifts')->name('fo.shifts.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Fo\ShiftController::class, 'index'])->name('index');
        Route::post('open', [\App\Http\Controllers\Panel\Fo\ShiftController::class, 'open'])->name('open');
        Route::post('{id}/close', [\App\Http\Controllers\Panel\Fo\ShiftController::class, 'close'])->name('close');
        Route::get('{id}', [\App\Http\Controllers\Panel\Fo\ShiftController::class, 'show'])->name('show');
    });

    Route::prefix('fo/ooo')->name('fo.ooo.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Fo\OooController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Panel\Fo\OooController::class, 'store'])->name('store');
        Route::patch('{id}/clear', [\App\Http\Controllers\Panel\Fo\OooController::class, 'clear'])->name('clear');
    });

    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('allotments', [\App\Http\Controllers\Panel\Sales\AllotmentController::class, 'index'])->name('allotments.index');
        Route::post('allotments', [\App\Http\Controllers\Panel\Sales\AllotmentController::class, 'store'])->name('allotments.store');
        Route::get('allotments/{id}', [\App\Http\Controllers\Panel\Sales\AllotmentController::class, 'show'])->name('allotments.show');
        Route::put('allotments/{id}', [\App\Http\Controllers\Panel\Sales\AllotmentController::class, 'update'])->name('allotments.update');
        Route::delete('allotments/{id}', [\App\Http\Controllers\Panel\Sales\AllotmentController::class, 'destroy'])->name('allotments.destroy');
        Route::post('allotments/{id}/release', [\App\Http\Controllers\Panel\Sales\AllotmentController::class, 'release'])->name('allotments.release');

        // Travel Agents
        Route::get('travel-agents', [\App\Http\Controllers\Panel\Sales\TravelAgentController::class, 'index'])->name('travel-agents.index');
        Route::post('travel-agents', [\App\Http\Controllers\Panel\Sales\TravelAgentController::class, 'store'])->name('travel-agents.store');
        Route::get('travel-agents/{id}', [\App\Http\Controllers\Panel\Sales\TravelAgentController::class, 'show'])->name('travel-agents.show');
        Route::put('travel-agents/{id}', [\App\Http\Controllers\Panel\Sales\TravelAgentController::class, 'update'])->name('travel-agents.update');
        Route::delete('travel-agents/{id}', [\App\Http\Controllers\Panel\Sales\TravelAgentController::class, 'destroy'])->name('travel-agents.destroy');

        // Event & Wedding
        Route::get('events', [\App\Http\Controllers\Panel\Sales\EventController::class, 'index'])->name('events.index');
        Route::get('events/create', [\App\Http\Controllers\Panel\Sales\EventController::class, 'create'])->name('events.create');
        Route::post('events', [\App\Http\Controllers\Panel\Sales\EventController::class, 'store'])->name('events.store');
        Route::get('events/{id}', [\App\Http\Controllers\Panel\Sales\EventController::class, 'show'])->name('events.show');
        Route::put('events/{id}', [\App\Http\Controllers\Panel\Sales\EventController::class, 'update'])->name('events.update');
        Route::patch('events/{id}/status', [\App\Http\Controllers\Panel\Sales\EventController::class, 'updateStatus'])->name('events.status');
        Route::post('events/{id}/services', [\App\Http\Controllers\Panel\Sales\EventController::class, 'addService'])->name('events.services.store');

        // Event Types
        Route::get('event-types', [\App\Http\Controllers\Panel\Sales\EventController::class, 'types'])->name('events.types');
        Route::post('event-types', [\App\Http\Controllers\Panel\Sales\EventController::class, 'storeType'])->name('events.types.store');
        Route::put('event-types/{id}', [\App\Http\Controllers\Panel\Sales\EventController::class, 'updateType'])->name('events.types.update');
        Route::delete('event-types/{id}', [\App\Http\Controllers\Panel\Sales\EventController::class, 'destroyType'])->name('events.types.destroy');

        // Corporate Accounts
        Route::get('corporate', [\App\Http\Controllers\Panel\Sales\CorporateAccountController::class, 'index'])->name('corporate.index');
        Route::get('corporate/create', [\App\Http\Controllers\Panel\Sales\CorporateAccountController::class, 'create'])->name('corporate.create');
        Route::post('corporate', [\App\Http\Controllers\Panel\Sales\CorporateAccountController::class, 'store'])->name('corporate.store');
        Route::get('corporate/{id}', [\App\Http\Controllers\Panel\Sales\CorporateAccountController::class, 'show'])->name('corporate.show');
        Route::get('corporate/{id}/edit', [\App\Http\Controllers\Panel\Sales\CorporateAccountController::class, 'edit'])->name('corporate.edit');
        Route::put('corporate/{id}', [\App\Http\Controllers\Panel\Sales\CorporateAccountController::class, 'update'])->name('corporate.update');
        Route::delete('corporate/{id}', [\App\Http\Controllers\Panel\Sales\CorporateAccountController::class, 'destroy'])->name('corporate.destroy');
        Route::post('corporate/{id}/rates', [\App\Http\Controllers\Panel\Sales\CorporateAccountController::class, 'rates'])->name('corporate.rates');
        Route::delete('corporate/{id}/rates/{rateId}', [\App\Http\Controllers\Panel\Sales\CorporateAccountController::class, 'deleteRate'])->name('corporate.rates.delete');
        Route::get('corporate/{id}/bookings', [\App\Http\Controllers\Panel\Sales\CorporateAccountController::class, 'bookings'])->name('corporate.bookings');
    });

    Route::prefix('concierge')->name('concierge.')->group(function () {
        Route::get('requests', [\App\Http\Controllers\Panel\Concierge\GuestRequestController::class, 'index'])->name('requests.index');
        Route::post('requests', [\App\Http\Controllers\Panel\Concierge\GuestRequestController::class, 'store'])->name('requests.store');
        Route::patch('requests/{id}', [\App\Http\Controllers\Panel\Concierge\GuestRequestController::class, 'update'])->name('requests.update');
    });

    Route::get('reports/flash', [\App\Http\Controllers\Panel\Reports\FlashReportController::class, 'show'])->name('reports.flash');

    Route::get('settings/cancellation-policies', [\App\Http\Controllers\Panel\Settings\CancellationPolicyController::class, 'index'])->name('settings.cancellation-policies');
    Route::post('settings/cancellation-policies', [\App\Http\Controllers\Panel\Settings\CancellationPolicyController::class, 'store'])->name('settings.cancellation-policies.store');
    Route::get('settings/doc-templates', [\App\Http\Controllers\Panel\Settings\DocumentTemplateController::class, 'index'])->name('settings.doc-templates');
    Route::post('settings/doc-templates', [\App\Http\Controllers\Panel\Settings\DocumentTemplateController::class, 'store'])->name('settings.doc-templates.store');

    Route::prefix('survey')->name('survey.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Survey\SurveyController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Panel\Survey\SurveyController::class, 'store'])->name('store');
        Route::get('{id}/responses', [\App\Http\Controllers\Panel\Survey\SurveyController::class, 'responses'])->name('responses');
    });

    // Blog
    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\BlogController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\Panel\BlogController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Panel\BlogController::class, 'store'])->name('store');
        Route::get('{id}/edit', [\App\Http\Controllers\Panel\BlogController::class, 'edit'])->name('edit');
        Route::put('{id}', [\App\Http\Controllers\Panel\BlogController::class, 'update'])->name('update');
        Route::delete('{id}', [\App\Http\Controllers\Panel\BlogController::class, 'destroy'])->name('destroy');
    });

    // Blog Categories
    Route::prefix('blog/categories')->name('blog.categories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\BlogCategoryController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Panel\BlogCategoryController::class, 'store'])->name('store');
        Route::put('{id}', [\App\Http\Controllers\Panel\BlogCategoryController::class, 'update'])->name('update');
        Route::delete('{id}', [\App\Http\Controllers\Panel\BlogCategoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('referrals', [\App\Http\Controllers\Panel\Marketing\ReferralController::class, 'index'])->name('referrals');
        Route::post('referrals', [\App\Http\Controllers\Panel\Marketing\ReferralController::class, 'store'])->name('referrals.store');
        Route::post('referrals/generate', [\App\Http\Controllers\Panel\Marketing\ReferralController::class, 'generateCode'])->name('referrals.generate');
        Route::get('referrals/settings', [\App\Http\Controllers\Panel\Marketing\ReferralController::class, 'settings'])->name('referrals.settings');
        Route::post('referrals/settings', [\App\Http\Controllers\Panel\Marketing\ReferralController::class, 'saveSettings'])->name('referrals.settings.save');
        Route::get('drip-campaigns', [\App\Http\Controllers\Panel\Marketing\DripCampaignController::class, 'index'])->name('drip-campaigns.index');
        Route::get('drip-campaigns/create', [\App\Http\Controllers\Panel\Marketing\DripCampaignController::class, 'create'])->name('drip-campaigns.create');
        Route::post('drip-campaigns', [\App\Http\Controllers\Panel\Marketing\DripCampaignController::class, 'store'])->name('drip-campaigns.store');
        Route::get('drip-campaigns/{id}/edit', [\App\Http\Controllers\Panel\Marketing\DripCampaignController::class, 'edit'])->name('drip-campaigns.edit');
        Route::put('drip-campaigns/{id}', [\App\Http\Controllers\Panel\Marketing\DripCampaignController::class, 'update'])->name('drip-campaigns.update');
        Route::delete('drip-campaigns/{id}', [\App\Http\Controllers\Panel\Marketing\DripCampaignController::class, 'destroy'])->name('drip-campaigns.destroy');
        Route::get('whatsapp-blast', [WhatsAppBlastController::class, 'index'])->name('whatsapp-blast');
        Route::post('whatsapp-blast/preview', [WhatsAppBlastController::class, 'previewRecipients'])->name('whatsapp-blast.preview');
        Route::post('whatsapp-blast/send', [WhatsAppBlastController::class, 'send'])->name('whatsapp-blast.send');
        Route::post('whatsapp-blast/test', [WhatsAppBlastController::class, 'testSend'])->name('whatsapp-blast.test');
        Route::get('review-aggregator', [\App\Http\Controllers\Panel\Marketing\ReviewAggregatorController::class, 'index'])->name('review-aggregator');
        Route::post('review-aggregator/pull', [\App\Http\Controllers\Panel\Marketing\ReviewAggregatorController::class, 'pull'])->name('review-aggregator.pull');
        Route::get('social-poster', [\App\Http\Controllers\Panel\Marketing\SocialPostController::class, 'index'])->name('social-poster');
        Route::post('social-poster/post', [\App\Http\Controllers\Panel\Marketing\SocialPostController::class, 'postNow'])->name('social-poster.post');
        Route::post('social-poster/schedule', [\App\Http\Controllers\Panel\Marketing\SocialPostController::class, 'schedule'])->name('social-poster.schedule');

        // Google Hotel Ads
        Route::get('google-hotel-ads', [\App\Http\Controllers\Panel\Marketing\GoogleHotelAdsController::class, 'index'])->name('google-hotel-ads');
        Route::post('google-hotel-ads/sync', [\App\Http\Controllers\Panel\Marketing\GoogleHotelAdsController::class, 'syncPriceFeed'])->name('google-hotel-ads.sync');

        // Metasearch
        Route::get('metasearch', [\App\Http\Controllers\Panel\Marketing\MetasearchController::class, 'index'])->name('metasearch');
        Route::get('metasearch/feed', [\App\Http\Controllers\Panel\Marketing\MetasearchController::class, 'feed'])->name('metasearch.feed');
        Route::get('metasearch/download', [\App\Http\Controllers\Panel\Marketing\MetasearchController::class, 'download'])->name('metasearch.download');
        Route::get('metasearch/performance', [\App\Http\Controllers\Panel\Marketing\MetasearchController::class, 'performance'])->name('metasearch.performance');
    });

    Route::prefix('kb')->name('kb.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Kb\KbController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Panel\Kb\KbController::class, 'store'])->name('store');
    });

    Route::prefix('sustainability')->name('sustainability.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Sustainability\SustainabilityController::class, 'dashboard'])->name('dashboard');
        Route::post('metrics', [\App\Http\Controllers\Panel\Sustainability\SustainabilityController::class, 'storeMetric'])->name('metrics.store');
        Route::get('energy', [\App\Http\Controllers\Panel\Sustainability\EnergyController::class, 'index'])->name('energy');

        // Food Waste
        Route::get('food-waste', [\App\Http\Controllers\Panel\Sustainability\FoodWasteController::class, 'index'])->name('food-waste');
        Route::post('food-waste', [\App\Http\Controllers\Panel\Sustainability\FoodWasteController::class, 'store'])->name('food-waste.store');
        Route::post('food-waste/targets', [\App\Http\Controllers\Panel\Sustainability\FoodWasteController::class, 'storeTarget'])->name('food-waste.targets.store');
        Route::post('food-waste/targets/{id}/complete', [\App\Http\Controllers\Panel\Sustainability\FoodWasteController::class, 'completeTarget'])->name('food-waste.targets.complete');
    });

    Route::get('concierge/pois', [\App\Http\Controllers\Panel\Concierge\PoiController::class, 'index'])->name('concierge.pois.index');
    Route::post('concierge/pois', [\App\Http\Controllers\Panel\Concierge\PoiController::class, 'store'])->name('concierge.pois.store');

    Route::prefix('fo')->name('fo.')->group(function () {
        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
        Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::get('reservations/{id}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::patch('reservations/{id}', [ReservationController::class, 'update'])->name('reservations.update');
        Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::post('reservations/{id}/check-in', [ReservationController::class, 'checkIn'])->name('reservations.check-in');
        Route::post('reservations/{id}/check-out', [ReservationController::class, 'checkOut'])->name('reservations.check-out');
        Route::post('reservations/{id}/move-room', [ReservationController::class, 'moveRoom'])->name('reservations.move-room');
        Route::get('arrivals', [ReservationController::class, 'arrivals'])->name('arrivals');
        Route::get('departures', [ReservationController::class, 'departures'])->name('departures');
        Route::get('in-house', [ReservationController::class, 'inHouse'])->name('in-house');
        Route::get('calendar', [ReservationController::class, 'calendar'])->name('calendar');
        Route::get('calendar-data', [ReservationController::class, 'calendarData'])->name('calendar.data');

        Route::get('walkin', [WalkinController::class, 'index'])->name('walkin');
        Route::post('walkin/quick-register', [WalkinController::class, 'quickRegister'])->name('walkin.register');
        Route::get('walkin/room/{id}', [WalkinController::class, 'roomDetail'])->name('walkin.room-detail');

        Route::get('folios/{id}', [FolioController::class, 'show'])->name('folios.show');
        Route::post('folios/{id}/charges', [FolioController::class, 'addCharge'])->name('folios.charges');
        Route::post('folios/{id}/payments', [FolioController::class, 'addPayment'])->name('folios.payments');
        Route::post('folios/{id}/discount', [FolioController::class, 'addDiscount'])->name('folios.discount');
        Route::post('folios/{id}/transfer', [FolioController::class, 'transfer'])->name('folios.transfer');
        Route::post('folios/{id}/settle', [FolioController::class, 'settle'])->name('folios.settle');
        Route::get('folios/{id}/invoice', [FolioController::class, 'invoice'])->name('folios.invoice');

        Route::get('night-audit', [NightAuditController::class, 'index'])->name('night-audit.index');
        Route::post('night-audit/run', [NightAuditController::class, 'run'])->name('night-audit.run');

        Route::prefix('e-registration')->name('e-registration.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Fo\ERegistrationController::class, 'index'])->name('index');
            Route::get('{reservationId}/create', [\App\Http\Controllers\Panel\Fo\ERegistrationController::class, 'create'])->name('create');
            Route::post('{reservationId}', [\App\Http\Controllers\Panel\Fo\ERegistrationController::class, 'store'])->name('store');
            Route::get('card/{id}', [\App\Http\Controllers\Panel\Fo\ERegistrationController::class, 'show'])->name('show');
            Route::post('card/{id}/verify', [\App\Http\Controllers\Panel\Fo\ERegistrationController::class, 'verify'])->name('verify');
            Route::post('card/{id}/reject', [\App\Http\Controllers\Panel\Fo\ERegistrationController::class, 'reject'])->name('reject');
        });

        Route::prefix('digital-registrations')->name('digital-registrations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Fo\DigitalRegistrationController::class, 'index'])->name('index');
            Route::get('{id}', [\App\Http\Controllers\Panel\Fo\DigitalRegistrationController::class, 'show'])->name('show');
            Route::post('{id}/send', [\App\Http\Controllers\Panel\Fo\DigitalRegistrationController::class, 'send'])->name('send');
            Route::post('{id}/complete', [\App\Http\Controllers\Panel\Fo\DigitalRegistrationController::class, 'complete'])->name('complete');
            Route::post('reservation/{reservationId}/create', [\App\Http\Controllers\Panel\Fo\DigitalRegistrationController::class, 'createForReservation'])->name('create');
        });

        Route::get('digital-keys', [DigitalKeyController::class, 'index'])->name('digital-keys');
        Route::post('digital-keys/{reservation}/issue', [DigitalKeyController::class, 'issue'])->name('digital-keys.issue');
        Route::post('digital-keys/{reservation}/revoke', [DigitalKeyController::class, 'revoke'])->name('digital-keys.revoke');

        Route::prefix('room-assignment')->name('room-assignment.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Fo\RoomAssignmentController::class, 'index'])->name('index');
            Route::post('assign', [\App\Http\Controllers\Panel\Fo\RoomAssignmentController::class, 'assign'])->name('assign');
            Route::post('auto', [\App\Http\Controllers\Panel\Fo\RoomAssignmentController::class, 'autoAssign'])->name('auto');
            Route::post('swap', [\App\Http\Controllers\Panel\Fo\RoomAssignmentController::class, 'swap'])->name('swap');
        });

        Route::prefix('group-blocks')->name('group-blocks.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Fo\GroupBlockController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Panel\Fo\GroupBlockController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Panel\Fo\GroupBlockController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Panel\Fo\GroupBlockController::class, 'show'])->name('show');
            Route::get('/{id}/pickup', [\App\Http\Controllers\Panel\Fo\GroupBlockController::class, 'pickup'])->name('pickup');
            Route::post('/{id}/release', [\App\Http\Controllers\Panel\Fo\GroupBlockController::class, 'releaseUnpicked'])->name('release');
            Route::post('/{id}/rooms', [\App\Http\Controllers\Panel\Fo\GroupBlockController::class, 'addRoom'])->name('add-room');
            Route::delete('/{id}/rooms/{roomId}', [\App\Http\Controllers\Panel\Fo\GroupBlockController::class, 'removeRoom'])->name('remove-room');
            Route::post('/{id}/confirm', [\App\Http\Controllers\Panel\Fo\GroupBlockController::class, 'confirm'])->name('confirm');
        });

        // Parking
        Route::get('parking', [\App\Http\Controllers\Panel\Fo\ParkingController::class, 'index'])->name('parking');
        Route::post('parking/checkin', [\App\Http\Controllers\Panel\Fo\ParkingController::class, 'checkIn'])->name('parking.checkin');
        Route::post('parking/{id}/checkout', [\App\Http\Controllers\Panel\Fo\ParkingController::class, 'checkOut'])->name('parking.checkout');
        Route::get('parking/valet', [\App\Http\Controllers\Panel\Fo\ParkingController::class, 'valet'])->name('parking.valet');

        // Fleet & Shuttle
        Route::prefix('fleet')->name('fleet.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'dashboard'])->name('dashboard');
            Route::get('vehicles', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'vehicles'])->name('vehicles');
            Route::post('vehicles', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'storeVehicle'])->name('vehicles.store');
            Route::post('vehicles/{id}', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'updateVehicle'])->name('vehicles.update');
            Route::get('drivers', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'drivers'])->name('drivers');
            Route::post('drivers', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'storeDriver'])->name('drivers.store');
            Route::post('drivers/{id}', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'updateDriver'])->name('drivers.update');
            Route::get('trips', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'trips'])->name('trips');
            Route::post('trips', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'storeTrip'])->name('trips.store');
            Route::post('trips/{id}/start', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'startTrip'])->name('trips.start');
            Route::post('trips/{id}/complete', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'completeTrip'])->name('trips.complete');
            Route::post('trips/{id}/cancel', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'cancelTrip'])->name('trips.cancel');
            Route::post('trips/{id}/charge', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'chargeTrip'])->name('trips.charge');
            Route::get('shuttle', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'shuttle'])->name('shuttle');
            Route::post('shuttle', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'storeShuttle'])->name('shuttle.store');
            Route::post('shuttle/{id}', [\App\Http\Controllers\Panel\Fo\FleetController::class, 'updateShuttle'])->name('shuttle.update');
        });
    });

    Route::prefix('hk/workload')->name('hk.workload.')->group(function () {
        Route::get('/', [App\Http\Controllers\Panel\Hk\WorkloadController::class, 'index'])->name('index');
        Route::post('assign', [App\Http\Controllers\Panel\Hk\WorkloadController::class, 'assign'])->name('assign');
    });

    Route::prefix('hk')->name('hk.')->group(function () {
        Route::get('/', [HousekeepingController::class, 'board'])->name('board');
        Route::get('rooms', [HousekeepingController::class, 'rooms'])->name('rooms');
        Route::patch('rooms/{id}/status', [HousekeepingController::class, 'updateStatus'])->name('rooms.status');
        Route::get('tasks', [HousekeepingController::class, 'tasks'])->name('tasks');
        Route::post('tasks', [HousekeepingController::class, 'storeTask'])->name('tasks.store');
        Route::patch('tasks/{id}', [HousekeepingController::class, 'updateTask'])->name('tasks.update');

        Route::prefix('linen')->name('linen.')->group(function () {
            Route::get('/', [App\Http\Controllers\Panel\Hk\LinenController::class, 'index'])->name('index');
            Route::get('create', [App\Http\Controllers\Panel\Hk\LinenController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Panel\Hk\LinenController::class, 'store'])->name('store');
            Route::post('{id}/in', [App\Http\Controllers\Panel\Hk\LinenController::class, 'stockIn'])->name('in');
            Route::post('{id}/out', [App\Http\Controllers\Panel\Hk\LinenController::class, 'stockOut'])->name('out');
            Route::get('audit', [App\Http\Controllers\Panel\Hk\LinenController::class, 'audit'])->name('audit');
            Route::post('audit', [App\Http\Controllers\Panel\Hk\LinenController::class, 'auditSave'])->name('audit.save');
            Route::get('{id}/history', [App\Http\Controllers\Panel\Hk\LinenController::class, 'history'])->name('history');
        });

        Route::prefix('inspection')->name('inspection.')->group(function () {
            Route::get('/', [App\Http\Controllers\Panel\Hk\InspectionController::class, 'index'])->name('index');
            Route::get('{roomId}/create', [App\Http\Controllers\Panel\Hk\InspectionController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Panel\Hk\InspectionController::class, 'store'])->name('store');
            Route::get('report', [App\Http\Controllers\Panel\Hk\InspectionController::class, 'report'])->name('report');
            Route::get('{id}', [App\Http\Controllers\Panel\Hk\InspectionController::class, 'show'])->name('show')->whereNumber('id');
        });

        Route::prefix('minibar')->name('minibar.')->group(function () {
            Route::get('products', [MinibarController::class, 'index'])->name('products');
            Route::post('products', [MinibarController::class, 'store'])->name('products.store');
            Route::put('products/{id}', [MinibarController::class, 'update'])->name('products.update');
            Route::delete('products/{id}', [MinibarController::class, 'destroy'])->name('products.destroy');
            Route::get('rooms', [MinibarController::class, 'rooms'])->name('rooms');
            Route::get('rooms/{id}', [MinibarController::class, 'roomStock'])->name('room-stock');
            Route::post('consume', [MinibarController::class, 'record'])->name('consume');
            Route::post('restock', [MinibarController::class, 'restock'])->name('restock');
        });

        Route::prefix('lost-found')->name('lost-found.')->group(function () {
            Route::get('/', [LostFoundController::class, 'index'])->name('index');
            Route::post('/', [LostFoundController::class, 'store'])->name('store');
            Route::get('{id}', [LostFoundController::class, 'show'])->name('show');
            Route::patch('{id}', [LostFoundController::class, 'update'])->name('update');
            Route::post('{id}/claim', [LostFoundController::class, 'claim'])->name('claim');
            Route::post('{id}/dispose', [LostFoundController::class, 'dispose'])->name('dispose');
            Route::post('{id}/donate', [LostFoundController::class, 'donate'])->name('donate');
            Route::post('{id}/return', [LostFoundController::class, 'returnToOwner'])->name('return');
        });

        // HK Auto-Assign
        Route::get('auto-assign', [\App\Http\Controllers\Panel\Hk\AutoAssignController::class, 'index'])->name('auto-assign');
        Route::post('auto-assign/generate', [\App\Http\Controllers\Panel\Hk\AutoAssignController::class, 'generate'])->name('auto-assign.generate');
        Route::post('auto-assign/assign', [\App\Http\Controllers\Panel\Hk\AutoAssignController::class, 'assign'])->name('auto-assign.assign');
        Route::post('auto-assign/reassign/{task}', [\App\Http\Controllers\Panel\Hk\AutoAssignController::class, 'reassign'])->name('auto-assign.reassign');
    });

    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('outlets/{id}/tables', [PosController::class, 'tables'])->name('tables');
        Route::post('orders', [PosController::class, 'createOrder'])->name('orders.create');
        Route::patch('orders/{id}', [PosController::class, 'updateOrder'])->name('orders.update');
        Route::post('orders/{id}/settle', [PosController::class, 'settleOrder'])->name('orders.settle');
        Route::get('menu', [PosController::class, 'menu'])->name('menu');

        Route::prefix('laundry')->name('laundry.')->group(function () {
            Route::get('/', [App\Http\Controllers\Panel\Pos\LaundryController::class, 'index'])->name('index');
            Route::get('create', [App\Http\Controllers\Panel\Pos\LaundryController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Panel\Pos\LaundryController::class, 'store'])->name('store');
            Route::get('{id}', [App\Http\Controllers\Panel\Pos\LaundryController::class, 'show'])->name('show');
            Route::patch('{id}/status', [App\Http\Controllers\Panel\Pos\LaundryController::class, 'updateStatus'])->name('status');
            Route::post('{id}/deliver', [App\Http\Controllers\Panel\Pos\LaundryController::class, 'markDelivered'])->name('deliver');
        });

        Route::get('kds', [App\Http\Controllers\Panel\Pos\KdsController::class, 'display'])->name('kds');
        Route::get('kds/orders', [App\Http\Controllers\Panel\Pos\KdsController::class, 'orders'])->name('kds.orders');
        Route::post('kds/{id}/prepare', [App\Http\Controllers\Panel\Pos\KdsController::class, 'startPreparing'])->name('kds.prepare');
        Route::post('kds/{id}/ready', [App\Http\Controllers\Panel\Pos\KdsController::class, 'markReady'])->name('kds.ready');
        Route::post('kds/{id}/recall', [App\Http\Controllers\Panel\Pos\KdsController::class, 'recall'])->name('kds.recall');

        // Table Reservations
        Route::prefix('tables')->name('tables.')->group(function () {
            Route::get('floorplan', [\App\Http\Controllers\Panel\Pos\TableReservationController::class, 'floorplan'])->name('floorplan');
            Route::get('/', [\App\Http\Controllers\Panel\Pos\TableReservationController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Panel\Pos\TableReservationController::class, 'store'])->name('store');
            Route::post('{id}', [\App\Http\Controllers\Panel\Pos\TableReservationController::class, 'update'])->name('update');
            Route::post('{id}/checkin', [\App\Http\Controllers\Panel\Pos\TableReservationController::class, 'checkIn'])->name('checkin');
            Route::post('{id}/complete', [\App\Http\Controllers\Panel\Pos\TableReservationController::class, 'complete'])->name('complete');
            Route::post('{id}/noshow', [\App\Http\Controllers\Panel\Pos\TableReservationController::class, 'noShow'])->name('noshow');
            Route::post('{id}/cancel', [\App\Http\Controllers\Panel\Pos\TableReservationController::class, 'cancel'])->name('cancel');
        });

        // Menu Engineering
        Route::prefix('menu-engineering')->name('menu-engineering.')->group(function () {
            Route::get('matrix', [\App\Http\Controllers\Panel\Pos\MenuEngineeringController::class, 'matrix'])->name('matrix');
            Route::get('recipes', [\App\Http\Controllers\Panel\Pos\MenuEngineeringController::class, 'recipes'])->name('recipes');
            Route::post('recipes', [\App\Http\Controllers\Panel\Pos\MenuEngineeringController::class, 'storeRecipe'])->name('recipe.store');
            Route::post('recipes/{id}', [\App\Http\Controllers\Panel\Pos\MenuEngineeringController::class, 'updateRecipe'])->name('recipe.update');
            Route::delete('recipes/{id}', [\App\Http\Controllers\Panel\Pos\MenuEngineeringController::class, 'destroyRecipe'])->name('recipe.destroy');
            Route::get('recipe/{id}', [\App\Http\Controllers\Panel\Pos\MenuEngineeringController::class, 'recipeDetail'])->name('recipe');
            Route::post('recipe/{recipeId}/ingredients', [\App\Http\Controllers\Panel\Pos\MenuEngineeringController::class, 'storeIngredient'])->name('ingredient.store');
            Route::delete('ingredient/{id}', [\App\Http\Controllers\Panel\Pos\MenuEngineeringController::class, 'destroyIngredient'])->name('ingredient.destroy');
            Route::post('calculate', [\App\Http\Controllers\Panel\Pos\MenuEngineeringController::class, 'calculate'])->name('calculate');
        });
    });

    Route::prefix('channel')->name('channel.')->group(function () {
        Route::get('/', [ChannelController::class, 'index'])->name('index');
        Route::get('providers', [ChannelController::class, 'providers'])->name('providers');
        Route::get('mapping', [ChannelController::class, 'mapping'])->name('mapping');
        Route::post('mapping', [ChannelController::class, 'storeMapping'])->name('mapping.store');
        Route::delete('mapping/{id}', [ChannelController::class, 'deleteMapping'])->name('mapping.delete');
        Route::get('rates', [ChannelController::class, 'rates'])->name('rates');
        Route::patch('rates', [ChannelController::class, 'updateRates'])->name('rates.update');
        Route::get('restrictions', [ChannelController::class, 'restrictions'])->name('restrictions');
        Route::post('restrictions', [ChannelController::class, 'storeRestrictions'])->name('restrictions.store');
        Route::patch('restrictions/{id}', [ChannelController::class, 'updateRestrictions'])->name('restrictions.update');
        Route::get('sync-log', [ChannelController::class, 'syncLog'])->name('sync-log');
        Route::get('conflicts', [ChannelController::class, 'conflicts'])->name('conflicts');
        Route::post('conflicts/{id}/resolve', [ChannelController::class, 'resolveConflict'])->name('conflicts.resolve');

        // Channel Dashboard
        Route::get('dashboard', [ChannelController::class, 'dashboard'])->name('dashboard');
        // Per-OTA detail
        Route::get('detail/{id}', [ChannelController::class, 'detail'])->name('detail');
        // Virtual Cards
        Route::get('virtual-cards', [ChannelController::class, 'virtualCards'])->name('virtual-cards');
        Route::get('virtual-cards/{id}', [ChannelController::class, 'virtualCardDetail'])->name('virtual-cards.show');
        // GDS Bookings
        Route::get('gds', [ChannelController::class, 'gdsBookings'])->name('gds');
        Route::get('gds/{id}', [ChannelController::class, 'gdsBookingDetail'])->name('gds.show');
    });

    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/', [AccReportController::class, 'dashboard'])->name('dashboard');
        Route::get('coa', [ChartOfAccountsController::class, 'index'])->name('coa.index');
        Route::post('coa', [ChartOfAccountsController::class, 'store'])->name('coa.store');
        Route::patch('coa/{id}', [ChartOfAccountsController::class, 'update'])->name('coa.update');
        Route::get('journal', [JournalController::class, 'index'])->name('journal.index');
        Route::get('journal/create', [JournalController::class, 'create'])->name('journal.create');
        Route::post('journal', [JournalController::class, 'store'])->name('journal.store');
        Route::get('journal/{id}', [JournalController::class, 'show'])->name('journal.show');
        Route::post('journal/{id}/void', [JournalController::class, 'void'])->name('journal.void');
        Route::get('ar', [ArController::class, 'index'])->name('ar.index');
        Route::get('ar/{id}', [ArController::class, 'show'])->name('ar.show');
        Route::get('ap', [ApController::class, 'index'])->name('ap.index');
        Route::get('ap/{id}', [ApController::class, 'show'])->name('ap.show');
        Route::get('reports/trial-balance', [AccReportController::class, 'trialBalance'])->name('reports.tb');
        Route::get('reports/profit-loss', [AccReportController::class, 'profitLoss'])->name('reports.pl');
        Route::get('reports/daily-revenue', [AccReportController::class, 'dailyRevenue'])->name('reports.daily');
        Route::get('reports/balance-sheet', [AccReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
        Route::post('period/close', [AccReportController::class, 'closePeriod'])->name('period.close');
        Route::post('period/unlock', [AccReportController::class, 'unlockPeriod'])->name('period.unlock');

        Route::get('coretax', [AccReportController::class, 'coretaxIndex'])->name('coretax.index');
        Route::get('coretax/download/{id}', [AccReportController::class, 'downloadXml'])->name('coretax.download');
        Route::post('coretax/nsfp-generate', [AccReportController::class, 'generateNsfp'])->name('coretax.nsfp-generate');
    });

    // AI Tools (BYOK) — panel UI for AI features
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/',              [\App\Http\Controllers\Panel\Ai\AiController::class, 'hub'])->name('hub');
        Route::get('providers',      [\App\Http\Controllers\Panel\Ai\AiController::class, 'providers'])->name('providers');
        Route::get('concierge',      [\App\Http\Controllers\Panel\Ai\AiController::class, 'concierge'])->name('concierge');
        Route::get('translate',      [\App\Http\Controllers\Panel\Ai\AiController::class, 'translate'])->name('translate');
        Route::get('forecast',       [\App\Http\Controllers\Panel\Ai\AiController::class, 'forecast'])->name('forecast');
        Route::get('review-replies', [\App\Http\Controllers\Panel\Ai\AiController::class, 'reviewReplies'])->name('review-replies');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('occupancy', [OperationsReportController::class, 'occupancy'])->name('occupancy');
        Route::get('channel-production', [OperationsReportController::class, 'channelProduction'])->name('channel');
        Route::get('source-of-business', [OperationsReportController::class, 'sourceOfBusiness'])->name('source');
        Route::get('cashier-shift', [OperationsReportController::class, 'cashierShift'])->name('cashier');
        Route::get('guest-demographics', [OperationsReportController::class, 'guestDemographics'])->name('demographics');
        Route::get('export-pdf/{type}', [OperationsReportController::class, 'exportPdf'])->name('export-pdf');
        // SIPGAR
        Route::get('sipgar', [\App\Http\Controllers\Panel\Reports\SipgarController::class, 'index'])->name('sipgar');
        Route::post('sipgar/export', [\App\Http\Controllers\Panel\Reports\SipgarController::class, 'export'])->name('sipgar.export');

        // Custom Report Builder
        Route::get('custom-reports', [\App\Http\Controllers\Panel\Reports\CustomReportController::class, 'index'])->name('custom-reports.index');
        Route::get('custom-reports/create', [\App\Http\Controllers\Panel\Reports\CustomReportController::class, 'create'])->name('custom-reports.create');
        Route::post('custom-reports', [\App\Http\Controllers\Panel\Reports\CustomReportController::class, 'store'])->name('custom-reports.store');
        Route::get('custom-reports/{id}', [\App\Http\Controllers\Panel\Reports\CustomReportController::class, 'show'])->name('custom-reports.show');
        Route::get('custom-reports/{id}/edit', [\App\Http\Controllers\Panel\Reports\CustomReportController::class, 'edit'])->name('custom-reports.edit');
        Route::put('custom-reports/{id}', [\App\Http\Controllers\Panel\Reports\CustomReportController::class, 'update'])->name('custom-reports.update');
        Route::delete('custom-reports/{id}', [\App\Http\Controllers\Panel\Reports\CustomReportController::class, 'destroy'])->name('custom-reports.destroy');
        Route::get('custom-reports/widget-data/{key}', [\App\Http\Controllers\Panel\Reports\CustomReportController::class, 'widgetData'])->name('custom-reports.widget-data');
    });

    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('guest-journey', [\App\Http\Controllers\Panel\Analytics\GuestJourneyController::class, 'index'])->name('guest-journey');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('property', [PropertyController::class, 'edit'])->name('property');
        Route::patch('property', [PropertyController::class, 'update'])->name('property.update');
        Route::get('integrations', [IntegrationController::class, 'index'])->name('integrations');
        Route::post('integrations', [IntegrationController::class, 'store'])->name('integrations.store');
        Route::patch('integrations/{id}', [IntegrationController::class, 'update'])->name('integrations.update');
        Route::post('integrations/{id}/test', [IntegrationController::class, 'test'])->name('integrations.test');
        Route::delete('integrations/{id}', [IntegrationController::class, 'destroy'])->name('integrations.destroy');

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [IntegrationController::class, 'payments'])->name('index');
            Route::post('/', [IntegrationController::class, 'storePayment'])->name('store');
            Route::patch('/{id}/toggle', [IntegrationController::class, 'togglePayment'])->name('toggle');
            Route::delete('/{id}', [IntegrationController::class, 'destroyPayment'])->name('destroy');
        });
        Route::get('tax', [TaxConfigController::class, 'edit'])->name('tax');
        Route::patch('tax', [TaxConfigController::class, 'update'])->name('tax.update');
        Route::post('tax/deposit-config', [TaxConfigController::class, 'updateDepositConfig'])->name('tax.deposit-config');
        Route::get('users', [UserController::class, 'index'])->name('users');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::patch('users/{id}', [UserController::class, 'update'])->name('users.update');
        // Room Types
        Route::prefix('room-types')->name('room-types.')->group(function () {
            Route::get('/', [RoomTypeController::class, 'index'])->name('index');
            Route::post('/', [RoomTypeController::class, 'store'])->name('store');
            Route::put('/{id}', [RoomTypeController::class, 'update'])->name('update');
            Route::delete('/{id}', [RoomTypeController::class, 'destroy'])->name('destroy');
        });

        // Rooms
        Route::prefix('rooms')->name('rooms.')->group(function () {
            Route::get('/', [RoomController::class, 'index'])->name('index');
            Route::post('/', [RoomController::class, 'store'])->name('store');
            Route::post('/bulk', [RoomController::class, 'bulkStore'])->name('bulk-store');
            Route::put('/{id}', [RoomController::class, 'update'])->name('update');
            Route::delete('/{id}', [RoomController::class, 'destroy'])->name('destroy');
        });

        Route::get('two-factor', [\App\Http\Controllers\Auth\TwoFactorChallengeController::class, 'manage'])->name('two-factor');
        Route::get('license', [LicenseController::class, 'show'])->name('license');
        Route::post('license/refresh', [LicenseController::class, 'refresh'])->name('license.refresh');
        Route::post('license/migrate', [LicenseController::class, 'migrate'])->name('license.migrate');

        // Door Lock Settings
        Route::prefix('locks')->name('locks.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Settings\LockController::class, 'index'])->name('index');
            Route::post('configure', [\App\Http\Controllers\Panel\Settings\LockController::class, 'configure'])->name('configure');
            Route::post('test', [\App\Http\Controllers\Panel\Settings\LockController::class, 'test'])->name('test');
            Route::post('issue/{roomId}', [\App\Http\Controllers\Panel\Settings\LockController::class, 'issueKey'])->name('issue');
            Route::post('revoke/{roomId}', [\App\Http\Controllers\Panel\Settings\LockController::class, 'revokeKey'])->name('revoke');
        });

        // Printer Settings
        Route::get('printer', [\App\Http\Controllers\Panel\Settings\PrinterController::class, 'edit'])->name('printer');
        Route::patch('printer', [\App\Http\Controllers\Panel\Settings\PrinterController::class, 'update'])->name('printer.update');
    });

    Route::prefix('print')->name('print.')->group(function () {
        Route::post('folio/{id}', [\App\Http\Controllers\Panel\PrintController::class, 'printFolio'])->name('folio');
        Route::post('pos-order/{id}', [\App\Http\Controllers\Panel\PrintController::class, 'printPosOrder'])->name('pos-order');
        Route::post('kitchen/{id}', [\App\Http\Controllers\Panel\PrintController::class, 'printKitchenOrder'])->name('kitchen');
        Route::post('test', [\App\Http\Controllers\Panel\PrintController::class, 'testPrint'])->name('test');
    });

    Route::prefix('revenue')->name('revenue.')->group(function () {
        Route::get('overbooking', [\App\Http\Controllers\Panel\Revenue\OverbookingController::class, 'index'])->name('overbooking');

        // AI Revenue Agent
        Route::get('ai-revenue', [\App\Http\Controllers\Panel\Revenue\AiRevenueController::class, 'index'])->name('ai-revenue');
        Route::post('ai-revenue/analyze', [\App\Http\Controllers\Panel\Revenue\AiRevenueController::class, 'analyze'])->name('ai-revenue.analyze');
        Route::post('ai-revenue/apply', [\App\Http\Controllers\Panel\Revenue\AiRevenueController::class, 'apply'])->name('ai-revenue.apply');
        Route::post('ai-revenue/batch', [\App\Http\Controllers\Panel\Revenue\AiRevenueController::class, 'batchAnalyze'])->name('ai-revenue.batch');

        // Weather-Based Pricing
        Route::get('weather-pricing', [\App\Http\Controllers\Panel\Revenue\WeatherPricingController::class, 'index'])->name('weather-pricing');
        Route::post('weather-pricing/apply', [\App\Http\Controllers\Panel\Revenue\WeatherPricingController::class, 'apply'])->name('weather-pricing.apply');

        Route::prefix('upsells')->name('upsells.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Revenue\UpsellController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Panel\Revenue\UpsellController::class, 'store'])->name('store');
            Route::put('{id}', [\App\Http\Controllers\Panel\Revenue\UpsellController::class, 'update'])->name('update');
            Route::delete('{id}', [\App\Http\Controllers\Panel\Revenue\UpsellController::class, 'destroy'])->name('destroy');
            Route::get('reservation/{reservation}', [\App\Http\Controllers\Panel\Revenue\UpsellController::class, 'reservationUpsells'])->name('reservation');
            Route::post('reservation/{reservation}/present', [\App\Http\Controllers\Panel\Revenue\UpsellController::class, 'presentToReservation'])->name('present');
            Route::post('presentation/{id}/accept', [\App\Http\Controllers\Panel\Revenue\UpsellController::class, 'accept'])->name('accept');
            Route::post('presentation/{id}/decline', [\App\Http\Controllers\Panel\Revenue\UpsellController::class, 'decline'])->name('decline');
        });
    });

    Route::prefix('guests')->name('guests.')->group(function () {
        Route::get('/', [GuestController::class, 'index'])->name('index');
        Route::get('ltv', [\App\Http\Controllers\Panel\Guest\GuestLtvController::class, 'index'])->name('ltv');
        Route::get('ltv/{id}', [\App\Http\Controllers\Panel\Guest\GuestLtvController::class, 'show'])->name('ltv.show');
        Route::get('cross-property', [\App\Http\Controllers\Panel\Guest\CrossPropertyController::class, 'search'])->name('cross-property');
        Route::get('cross-property/profile', [\App\Http\Controllers\Panel\Guest\CrossPropertyController::class, 'profile'])->name('cross-property.profile');
        Route::get('{guest}/preferences', [\App\Http\Controllers\Panel\Guest\PreferenceController::class, 'show'])->name('preferences');
        Route::post('{guest}/preferences', [\App\Http\Controllers\Panel\Guest\PreferenceController::class, 'update'])->name('preferences.update');
        Route::get('{id}', [GuestController::class, 'show'])->name('show');
        Route::patch('{id}', [GuestController::class, 'update'])->name('update');
    });

    Route::prefix('banquet')->name('banquet.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'index'])->name('index');
        Route::get('calendar', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'calendar'])->name('calendar');
        Route::get('events', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'index'])->name('events.index');
        Route::get('events/create', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'create'])->name('events.create');
        Route::post('events', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'store'])->name('events.store');
        Route::get('events/{id}', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'show'])->name('events.show');
        Route::get('events/{id}/edit', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'edit'])->name('events.edit');
        Route::put('events/{id}', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'update'])->name('events.update');
        Route::delete('events/{id}', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'destroy'])->name('events.destroy');
        Route::patch('events/{id}/status', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'updateStatus'])->name('events.status');
        Route::post('events/{id}/menu', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'addMenu'])->name('events.menu.store');
        Route::get('events/{id}/beo', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'beo'])->name('events.beo');
        Route::get('function-rooms', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'functionRooms'])->name('function-rooms');
        Route::post('function-rooms', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'storeFunctionRoom'])->name('function-rooms.store');
        Route::put('function-rooms/{id}', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'updateFunctionRoom'])->name('function-rooms.update');
        Route::delete('function-rooms/{id}', [\App\Http\Controllers\Panel\Banquet\EventController::class, 'destroyFunctionRoom'])->name('function-rooms.destroy');
    });

    Route::prefix('spa')->name('spa.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'index'])->name('index');
        Route::get('appointments', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'appointments'])->name('appointments');
        Route::post('appointments', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'book'])->name('appointments.book');
        Route::patch('appointments/{id}/complete', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'complete'])->name('appointments.complete');
        Route::patch('appointments/{id}/cancel', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'cancelAppointment'])->name('appointments.cancel');
        Route::delete('appointments/{id}', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'destroyAppointment'])->name('appointments.destroy');
        Route::get('treatments', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'treatments'])->name('treatments');
        Route::post('treatments', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'storeTreatment'])->name('treatments.store');
        Route::put('treatments/{id}', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'updateTreatment'])->name('treatments.update');
        Route::delete('treatments/{id}', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'destroyTreatment'])->name('treatments.destroy');
        Route::get('therapists', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'therapists'])->name('therapists');
        Route::post('therapists', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'storeTherapist'])->name('therapists.store');
        Route::put('therapists/{id}', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'updateTherapist'])->name('therapists.update');
        Route::delete('therapists/{id}', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'destroyTherapist'])->name('therapists.destroy');
        Route::get('cabins', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'cabins'])->name('cabins');
        Route::post('cabins', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'storeCabin'])->name('cabins.store');
        Route::put('cabins/{id}', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'updateCabin'])->name('cabins.update');
        Route::delete('cabins/{id}', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'destroyCabin'])->name('cabins.destroy');
        Route::get('memberships', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'memberships'])->name('memberships');
        Route::post('memberships', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'storeMembership'])->name('memberships.store');
        Route::put('memberships/{id}', [\App\Http\Controllers\Panel\Spa\SpaController::class, 'updateMembership'])->name('memberships.update');
    });

    Route::prefix('hr')->name('hr.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Hr\HrController::class, 'index'])->name('index');
        Route::get('employees', [\App\Http\Controllers\Panel\Hr\HrController::class, 'employees'])->name('employees');
        Route::post('employees', [\App\Http\Controllers\Panel\Hr\HrController::class, 'storeEmployee'])->name('employees.store');
        Route::get('employees/{id}', [\App\Http\Controllers\Panel\Hr\HrController::class, 'showEmployee'])->name('employees.show');
        Route::get('employees/{id}/edit', [\App\Http\Controllers\Panel\Hr\HrController::class, 'editEmployee'])->name('employees.edit');
        Route::put('employees/{id}', [\App\Http\Controllers\Panel\Hr\HrController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('employees/{id}', [\App\Http\Controllers\Panel\Hr\HrController::class, 'destroyEmployee'])->name('employees.destroy');
        Route::get('attendance', [\App\Http\Controllers\Panel\Hr\HrController::class, 'attendance'])->name('attendance');
        Route::post('attendance/clock', [\App\Http\Controllers\Panel\Hr\HrController::class, 'clockIn'])->name('attendance.clock');
        Route::delete('attendance/{id}', [\App\Http\Controllers\Panel\Hr\HrController::class, 'destroyAttendance'])->name('attendance.destroy');
        Route::get('payroll', [\App\Http\Controllers\Panel\Hr\HrController::class, 'payroll'])->name('payroll');
        Route::post('payroll/generate', [\App\Http\Controllers\Panel\Hr\HrController::class, 'generatePayslips'])->name('payroll.generate');
        Route::get('payslips/{id}', [\App\Http\Controllers\Panel\Hr\HrController::class, 'showPayslip'])->name('payslips.show');
        Route::patch('payslips/{id}/approve', [\App\Http\Controllers\Panel\Hr\HrController::class, 'approvePayslip'])->name('payslips.approve');
        Route::patch('payslips/{id}/paid', [\App\Http\Controllers\Panel\Hr\HrController::class, 'markPayslipPaid'])->name('payslips.paid');
        Route::get('service-charge', [\App\Http\Controllers\Panel\Hr\HrController::class, 'serviceCharge'])->name('service-charge');

        // Leave Management
        Route::get('leave', [\App\Http\Controllers\Panel\Hr\LeaveController::class, 'index'])->name('leave.index');
        Route::get('leave/create', [\App\Http\Controllers\Panel\Hr\LeaveController::class, 'create'])->name('leave.create');
        Route::post('leave', [\App\Http\Controllers\Panel\Hr\LeaveController::class, 'store'])->name('leave.store');
        Route::post('leave/{id}/approve', [\App\Http\Controllers\Panel\Hr\LeaveController::class, 'approve'])->name('leave.approve');
        Route::post('leave/{id}/reject', [\App\Http\Controllers\Panel\Hr\LeaveController::class, 'reject'])->name('leave.reject');
        Route::delete('leave/{id}', [\App\Http\Controllers\Panel\Hr\LeaveController::class, 'destroy'])->name('leave.destroy');
        Route::get('leave/{employeeId}/balance', [\App\Http\Controllers\Panel\Hr\LeaveController::class, 'balance'])->name('leave.balance');

        // Gamification
        Route::get('gamification', [\App\Http\Controllers\Panel\Hr\GamificationController::class, 'index'])->name('gamification');
        Route::post('gamification/badges', [\App\Http\Controllers\Panel\Hr\GamificationController::class, 'store'])->name('gamification.badges.store');
        Route::delete('gamification/badges/{id}', [\App\Http\Controllers\Panel\Hr\GamificationController::class, 'destroyBadge'])->name('gamification.badges.destroy');
        Route::post('gamification/points', [\App\Http\Controllers\Panel\Hr\GamificationController::class, 'awardPoints'])->name('gamification.points.award');
        Route::get('gamification/leaderboard', [\App\Http\Controllers\Panel\Hr\GamificationController::class, 'leaderboard'])->name('gamification.leaderboard');
        Route::get('gamification/{id}/stats', [\App\Http\Controllers\Panel\Hr\GamificationController::class, 'employeeStats'])->name('gamification.employee-stats');

        // Shift Schedule
        Route::get('schedule', [\App\Http\Controllers\Panel\Hr\ScheduleController::class, 'calendar'])->name('schedule.calendar');
        Route::post('schedule/assign', [\App\Http\Controllers\Panel\Hr\ScheduleController::class, 'assign'])->name('schedule.assign');
        Route::post('schedule/swap', [\App\Http\Controllers\Panel\Hr\ScheduleController::class, 'swap'])->name('schedule.swap');

        // Performance Reviews
        Route::get('performance', [\App\Http\Controllers\Panel\Hr\PerformanceController::class, 'index'])->name('performance.index');
        Route::get('performance/create', [\App\Http\Controllers\Panel\Hr\PerformanceController::class, 'create'])->name('performance.create');
        Route::post('performance', [\App\Http\Controllers\Panel\Hr\PerformanceController::class, 'store'])->name('performance.store');
        Route::get('performance/{id}', [\App\Http\Controllers\Panel\Hr\PerformanceController::class, 'show'])->name('performance.show');
        Route::get('performance/{id}/edit', [\App\Http\Controllers\Panel\Hr\PerformanceController::class, 'edit'])->name('performance.edit');
        Route::put('performance/{id}', [\App\Http\Controllers\Panel\Hr\PerformanceController::class, 'update'])->name('performance.update');
        Route::delete('performance/{id}', [\App\Http\Controllers\Panel\Hr\PerformanceController::class, 'destroy'])->name('performance.destroy');
        Route::get('performance/generate/prefill', [\App\Http\Controllers\Panel\Hr\PerformanceController::class, 'generate'])->name('performance.generate');
        Route::post('performance/{id}/acknowledge', [\App\Http\Controllers\Panel\Hr\PerformanceController::class, 'acknowledge'])->name('performance.acknowledge');
    });

    Route::prefix('rms')->name('rms.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Rms\RmsController::class, 'dashboard'])->name('dashboard');
        Route::get('forecast', [\App\Http\Controllers\Panel\Rms\RmsController::class, 'forecast'])->name('forecast');
        Route::get('yield', [\App\Http\Controllers\Panel\Rms\RmsController::class, 'yield'])->name('yield');
        Route::get('rate-shopper', [\App\Http\Controllers\Panel\Rms\RmsController::class, 'rateShopper'])->name('rate-shopper');
        Route::post('rate-shopper/trigger', [\App\Http\Controllers\Panel\Rms\RmsController::class, 'triggerRateShopper'])->name('rate-shopper.trigger');
        Route::get('competitor-intelligence', [\App\Http\Controllers\Panel\Rms\RmsController::class, 'competitorIntelligence'])->name('competitor-intelligence');
        Route::get('pricing-log', [\App\Http\Controllers\Panel\Rms\RmsController::class, 'pricingLog'])->name('pricing-log');
        Route::get('forecast-accuracy', [\App\Http\Controllers\Panel\Rms\ForecastAccuracyController::class, 'index'])->name('forecast-accuracy');

        // Rate Scraper
        Route::get('scraper', [\App\Http\Controllers\Panel\RateScraperController::class, 'index'])->name('scraper.index');
        Route::post('scraper/targets', [\App\Http\Controllers\Panel\RateScraperController::class, 'storeTarget'])->name('scraper.targets.store');
        Route::put('scraper/targets/{id}', [\App\Http\Controllers\Panel\RateScraperController::class, 'updateTarget'])->name('scraper.targets.update');
        Route::delete('scraper/targets/{id}', [\App\Http\Controllers\Panel\RateScraperController::class, 'destroyTarget'])->name('scraper.targets.destroy');
        Route::post('scraper/targets/{id}/scrape', [\App\Http\Controllers\Panel\RateScraperController::class, 'scrapeTarget'])->name('scraper.scrape');
        Route::post('scraper/scrape-all', [\App\Http\Controllers\Panel\RateScraperController::class, 'scrapeAll'])->name('scraper.scrape-all');
        Route::get('scraper/alerts', [\App\Http\Controllers\Panel\RateScraperController::class, 'alerts'])->name('scraper.alerts');
        Route::post('scraper/alerts/{id}/read', [\App\Http\Controllers\Panel\RateScraperController::class, 'markAlertRead'])->name('scraper.alerts.read');
    });

    Route::prefix('asset')->name('asset.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Asset\AssetController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Panel\Asset\AssetController::class, 'store'])->name('store');
        Route::get('{id}', [\App\Http\Controllers\Panel\Asset\AssetController::class, 'show'])->name('show');
        Route::get('work-orders/list', [\App\Http\Controllers\Panel\Asset\AssetController::class, 'workOrders'])->name('work-orders');
        Route::post('work-orders/list', [\App\Http\Controllers\Panel\Asset\AssetController::class, 'storeWorkOrder'])->name('work-orders.store');
        Route::patch('work-orders/{id}', [\App\Http\Controllers\Panel\Asset\AssetController::class, 'updateWorkOrder'])->name('work-orders.update');
        Route::get('ppm/schedules', [\App\Http\Controllers\Panel\Asset\AssetController::class, 'ppm'])->name('ppm');

        // Preventive Maintenance Scheduler
        Route::get('pm', [\App\Http\Controllers\Panel\Asset\PmController::class, 'index'])->name('pm');
        Route::post('pm/schedule', [\App\Http\Controllers\Panel\Asset\PmController::class, 'schedule'])->name('pm.schedule');
        Route::post('pm/complete/{id}', [\App\Http\Controllers\Panel\Asset\PmController::class, 'complete'])->name('pm.complete');
        Route::post('pm/toggle/{id}', [\App\Http\Controllers\Panel\Asset\PmController::class, 'toggle'])->name('pm.toggle');
    });

    Route::prefix('comm')->name('comm.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Comm\CommController::class, 'inbox'])->name('inbox');
        Route::get('threads/{id}', [\App\Http\Controllers\Panel\Comm\CommController::class, 'thread'])->name('thread');
        Route::post('threads/{id}/reply', [\App\Http\Controllers\Panel\Comm\CommController::class, 'reply'])->name('reply');
        Route::get('templates', [\App\Http\Controllers\Panel\Comm\CommController::class, 'templates'])->name('templates');
        Route::post('templates', [\App\Http\Controllers\Panel\Comm\CommController::class, 'storeTemplate'])->name('templates.store');
        Route::get('campaigns', [\App\Http\Controllers\Panel\Comm\CampaignController::class, 'index'])->name('campaigns');
        Route::get('campaigns/create', [\App\Http\Controllers\Panel\Comm\CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('campaigns', [\App\Http\Controllers\Panel\Comm\CampaignController::class, 'store'])->name('campaigns.store');
        Route::get('campaigns/{id}', [\App\Http\Controllers\Panel\Comm\CampaignController::class, 'show'])->name('campaigns.show');
        Route::post('campaigns/{id}/send', [\App\Http\Controllers\Panel\Comm\CampaignController::class, 'send'])->name('campaigns.send');
        Route::post('campaigns/{id}/pause', [\App\Http\Controllers\Panel\Comm\CampaignController::class, 'pause'])->name('campaigns.pause');
        Route::get('campaigns/{id}/analytics', [\App\Http\Controllers\Panel\Comm\CampaignController::class, 'analytics'])->name('campaigns.analytics');
    });

    Route::prefix('loyalty')->name('loyalty.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Loyalty\LoyaltyController::class, 'members'])->name('members');
        Route::post('enroll', [\App\Http\Controllers\Panel\Loyalty\LoyaltyController::class, 'enroll'])->name('enroll');
        Route::get('tiers', [\App\Http\Controllers\Panel\Loyalty\LoyaltyController::class, 'tiers'])->name('tiers');
        Route::post('tiers', [\App\Http\Controllers\Panel\Loyalty\LoyaltyController::class, 'storeTier'])->name('tiers.store');
        Route::get('vouchers', [\App\Http\Controllers\Panel\Loyalty\LoyaltyController::class, 'vouchers'])->name('vouchers');
        Route::post('vouchers', [\App\Http\Controllers\Panel\Loyalty\LoyaltyController::class, 'issueVoucher'])->name('vouchers.issue');
    });

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('bank-accounts', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'bankAccounts'])->name('bank-accounts');
        Route::post('bank-accounts', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'storeBankAccount'])->name('bank-accounts.store');
        Route::get('bank-recon', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'bankRecon'])->name('bank-recon');
        Route::post('bank-recon/match', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'reconcileMatch'])->name('bank-recon.match');
        Route::post('bank-recon/auto-match', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'autoMatch'])->name('bank-recon.auto-match');
        Route::post('bank-recon/import', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'importStatement'])->name('bank-recon.import');
        Route::get('budget', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'budget'])->name('budget');
        Route::post('budget', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'storeBudgetLine'])->name('budget.store');
        Route::get('owner-statements', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'ownerStatements'])->name('owner-statements');
        Route::get('fx-rates', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'fxRates'])->name('fx-rates');
        Route::post('fx-rates', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'storeFxRate'])->name('fx-rates.store');
        Route::post('fx-rates/convert', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'convertFx'])->name('fx-rates.convert');
        Route::post('fx-rates/refresh', [\App\Http\Controllers\Panel\Finance\FinanceController::class, 'refreshFxRates'])->name('fx-rates.refresh');

        Route::prefix('owners')->name('owners.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Finance\OwnerManagementController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Panel\Finance\OwnerManagementController::class, 'store'])->name('store');
            Route::put('{id}', [\App\Http\Controllers\Panel\Finance\OwnerManagementController::class, 'update'])->name('update');
            Route::delete('{id}', [\App\Http\Controllers\Panel\Finance\OwnerManagementController::class, 'destroy'])->name('destroy');
            Route::post('distributions', [\App\Http\Controllers\Panel\Finance\OwnerManagementController::class, 'storeDistribution'])->name('distributions.store');
            Route::post('distributions/{id}/pay', [\App\Http\Controllers\Panel\Finance\OwnerManagementController::class, 'markDistributionPaid'])->name('distributions.pay');
            Route::post('documents', [\App\Http\Controllers\Panel\Finance\OwnerManagementController::class, 'uploadDocument'])->name('documents.store');
        });

        Route::prefix('deposits')->name('deposits.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Finance\DepositController::class, 'index'])->name('index');
            Route::post('receive', [\App\Http\Controllers\Panel\Finance\DepositController::class, 'receive'])->name('receive');
            Route::post('{id}/refund', [\App\Http\Controllers\Panel\Finance\DepositController::class, 'refund'])->name('refund');
            Route::post('{id}/forfeit', [\App\Http\Controllers\Panel\Finance\DepositController::class, 'forfeit'])->name('forfeit');
            Route::post('{id}/apply-to-folio', [\App\Http\Controllers\Panel\Finance\DepositController::class, 'applyToFolio'])->name('apply-to-folio');
        });

        Route::prefix('chargebacks')->name('chargebacks.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Finance\ChargebackController::class, 'index'])->name('index');
            Route::post('register', [\App\Http\Controllers\Panel\Finance\ChargebackController::class, 'register'])->name('register');
            Route::get('{id}', [\App\Http\Controllers\Panel\Finance\ChargebackController::class, 'show'])->name('show');
            Route::post('{id}/evidence', [\App\Http\Controllers\Panel\Finance\ChargebackController::class, 'addEvidence'])->name('evidence.store');
            Route::delete('{id}/evidence/{evidenceId}', [\App\Http\Controllers\Panel\Finance\ChargebackController::class, 'deleteEvidence'])->name('evidence.delete');
            Route::post('{id}/submit', [\App\Http\Controllers\Panel\Finance\ChargebackController::class, 'submit'])->name('submit');
            Route::post('{id}/outcome', [\App\Http\Controllers\Panel\Finance\ChargebackController::class, 'outcome'])->name('outcome');
        });
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Inventory\StockController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Panel\Inventory\StockController::class, 'store'])->name('store');
        Route::post('movements', [\App\Http\Controllers\Panel\Inventory\StockController::class, 'recordMovement'])->name('movements.store');

        // Vendor Management
        Route::get('vendors', [\App\Http\Controllers\Panel\Inventory\VendorController::class, 'index'])->name('vendors.index');
        Route::get('vendors/create', [\App\Http\Controllers\Panel\Inventory\VendorController::class, 'create'])->name('vendors.create');
        Route::post('vendors', [\App\Http\Controllers\Panel\Inventory\VendorController::class, 'store'])->name('vendors.store');
        Route::get('vendors/{id}', [\App\Http\Controllers\Panel\Inventory\VendorController::class, 'show'])->name('vendors.show');
        Route::get('vendors/{id}/edit', [\App\Http\Controllers\Panel\Inventory\VendorController::class, 'edit'])->name('vendors.edit');
        Route::put('vendors/{id}', [\App\Http\Controllers\Panel\Inventory\VendorController::class, 'update'])->name('vendors.update');
        Route::delete('vendors/{id}', [\App\Http\Controllers\Panel\Inventory\VendorController::class, 'destroy'])->name('vendors.destroy');
        Route::post('vendors/{id}/toggle', [\App\Http\Controllers\Panel\Inventory\VendorController::class, 'toggleActive'])->name('vendors.toggle');
        Route::get('vendors/{id}/contracts', [\App\Http\Controllers\Panel\Inventory\VendorController::class, 'contracts'])->name('vendors.contracts');
        Route::post('vendors/{id}/contracts', [\App\Http\Controllers\Panel\Inventory\VendorController::class, 'storeContract'])->name('vendors.contracts.store');

        // Purchase Requests
        Route::prefix('pr')->name('pr.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Inventory\PrController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Panel\Inventory\PrController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Panel\Inventory\PrController::class, 'store'])->name('store');
            Route::get('{id}', [\App\Http\Controllers\Panel\Inventory\PrController::class, 'show'])->name('show');
            Route::post('{id}/approve', [\App\Http\Controllers\Panel\Inventory\PrController::class, 'approve'])->name('approve');
            Route::post('{id}/reject', [\App\Http\Controllers\Panel\Inventory\PrController::class, 'reject'])->name('reject');
        });

        // Purchase Orders
        Route::prefix('po')->name('po.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Inventory\PoController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Panel\Inventory\PoController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Panel\Inventory\PoController::class, 'store'])->name('store');
            Route::get('{id}', [\App\Http\Controllers\Panel\Inventory\PoController::class, 'show'])->name('show');
            Route::post('{id}/send', [\App\Http\Controllers\Panel\Inventory\PoController::class, 'send'])->name('send');
        });

        // Goods Receipts
        Route::prefix('gr')->name('gr.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Inventory\GrController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Panel\Inventory\GrController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Panel\Inventory\GrController::class, 'store'])->name('store');
            Route::get('{id}', [\App\Http\Controllers\Panel\Inventory\GrController::class, 'show'])->name('show');
            Route::post('{id}/accept', [\App\Http\Controllers\Panel\Inventory\GrController::class, 'accept'])->name('accept');
        });
    });

    // Open Pricing + Dynamic Pricing + Channel Parity
    Route::prefix('pricing')->name('pricing.')->group(function () {
        Route::get('calendar', [\App\Http\Controllers\Panel\Pricing\PricingController::class, 'calendar'])->name('calendar');
        Route::get('calendar/data', [\App\Http\Controllers\Panel\Pricing\PricingController::class, 'calendarData'])->name('calendar.data');
        Route::post('calendar/save', [\App\Http\Controllers\Panel\Pricing\PricingController::class, 'bulkSave'])->name('calendar.save');
        Route::get('rules', [\App\Http\Controllers\Panel\Pricing\PricingController::class, 'rules'])->name('rules');
        Route::post('rules', [\App\Http\Controllers\Panel\Pricing\PricingController::class, 'storeRule'])->name('rules.store');
        Route::post('rules/apply', [\App\Http\Controllers\Panel\Pricing\PricingController::class, 'applyRulesNow'])->name('rules.apply');
        Route::get('parity', [\App\Http\Controllers\Panel\Pricing\PricingController::class, 'parity'])->name('parity');
        Route::post('parity/check', [\App\Http\Controllers\Panel\Pricing\PricingController::class, 'checkParityNow'])->name('parity.check');
        Route::post('parity/{id}/resolve', [\App\Http\Controllers\Panel\Pricing\PricingController::class, 'resolveAlert'])->name('parity.resolve');
        Route::get('logs', [\App\Http\Controllers\Panel\Pricing\PricingController::class, 'logs'])->name('logs');
    });

    // Guest 360 Profile
    Route::get('guests/{id}/profile', [\App\Http\Controllers\Panel\Guest\GuestProfileController::class, 'show'])->name('guests.profile');
    Route::post('guests/{id}/profile/rebuild', [\App\Http\Controllers\Panel\Guest\GuestProfileController::class, 'rebuild'])->name('guests.profile.rebuild');

    // Kids Club
    Route::prefix('kids-club')->name('kids-club.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Activities\KidsClubController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Panel\Activities\KidsClubController::class, 'store'])->name('store');
        Route::put('{id}', [\App\Http\Controllers\Panel\Activities\KidsClubController::class, 'update'])->name('update');
        Route::delete('{id}', [\App\Http\Controllers\Panel\Activities\KidsClubController::class, 'destroy'])->name('destroy');
        Route::get('bookings', [\App\Http\Controllers\Panel\Activities\KidsClubController::class, 'bookings'])->name('bookings');
        Route::post('bookings', [\App\Http\Controllers\Panel\Activities\KidsClubController::class, 'book'])->name('book');
        Route::post('bookings/{id}/cancel', [\App\Http\Controllers\Panel\Activities\KidsClubController::class, 'cancel'])->name('cancel');
    });

    // Keycard Inventory
    Route::get('fo/keycards', [\App\Http\Controllers\Panel\Fo\KeycardController::class, 'index'])->name('fo.keycards');
    Route::post('fo/keycards/issue', [\App\Http\Controllers\Panel\Fo\KeycardController::class, 'issue'])->name('fo.keycards.issue');
    Route::post('fo/keycards/{id}/return', [\App\Http\Controllers\Panel\Fo\KeycardController::class, 'return'])->name('fo.keycards.return');
    Route::post('fo/keycards/{id}/lost', [\App\Http\Controllers\Panel\Fo\KeycardController::class, 'lost'])->name('fo.keycards.lost');
    Route::match(['get', 'post', 'put', 'delete'], 'fo/keycard-types', [\App\Http\Controllers\Panel\Fo\KeycardController::class, 'types'])->name('fo.keycard-types');

    // Laundry & Linen Stock Tracking
    Route::get('hk/linen-stock', [\App\Http\Controllers\Panel\Hk\LaundryStockController::class, 'index'])->name('hk.linen');
    Route::post('hk/linen-stock/transaction', [\App\Http\Controllers\Panel\Hk\LaundryStockController::class, 'storeTransaction'])->name('hk.linen.transaction');
    Route::get('hk/linen-stock/uniforms', [\App\Http\Controllers\Panel\Hk\LaundryStockController::class, 'uniforms'])->name('hk.linen.uniforms');
    Route::post('hk/linen-stock/uniforms/assign', [\App\Http\Controllers\Panel\Hk\LaundryStockController::class, 'assignUniform'])->name('hk.linen.uniforms.assign');
    Route::post('hk/linen-stock/uniforms/{id}/return', [\App\Http\Controllers\Panel\Hk\LaundryStockController::class, 'returnUniform'])->name('hk.linen.uniforms.return');

    // IoT Smart Room
    Route::prefix('iot')->name('iot.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Iot\IotController::class, 'index'])->name('dashboard');
        Route::get('room/{room}', [\App\Http\Controllers\Panel\Iot\IotController::class, 'room'])->name('room');
        Route::post('command', [\App\Http\Controllers\Panel\Iot\IotController::class, 'command'])->name('command');
        Route::get('energy', [\App\Http\Controllers\Panel\Iot\IotController::class, 'energy'])->name('energy');
    });

    // Multi-Property HQ Dashboard
    Route::get('multi-property', [MultiPropertyController::class, 'dashboard'])->name('multi-property');

    // Dynamic Packaging
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\Revenue\PackageController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Panel\Revenue\PackageController::class, 'store'])->name('store');
        Route::put('{id}', [\App\Http\Controllers\Panel\Revenue\PackageController::class, 'update'])->name('update');
        Route::delete('{id}', [\App\Http\Controllers\Panel\Revenue\PackageController::class, 'destroy'])->name('destroy');
        Route::post('{package}/items', [\App\Http\Controllers\Panel\Revenue\PackageController::class, 'addItem'])->name('items.store');
        Route::delete('items/{id}', [\App\Http\Controllers\Panel\Revenue\PackageController::class, 'removeItem'])->name('items.destroy');
        Route::get('reservation/{reservation}', [\App\Http\Controllers\Panel\Revenue\PackageController::class, 'reservationPackages'])->name('reservation');
        Route::post('reservation/{reservation}/attach/{package}', [\App\Http\Controllers\Panel\Revenue\PackageController::class, 'attachToReservation'])->name('attach');
        Route::delete('reservation/{reservation}/detach/{rp}', [\App\Http\Controllers\Panel\Revenue\PackageController::class, 'detachFromReservation'])->name('detach');
    });

    // Security — Incident Reports
    Route::prefix('security')->name('security.')->group(function () {
        Route::prefix('incidents')->name('incidents.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Panel\Security\IncidentController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Panel\Security\IncidentController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Panel\Security\IncidentController::class, 'store'])->name('store');
            Route::get('{id}', [\App\Http\Controllers\Panel\Security\IncidentController::class, 'show'])->name('show');
            Route::get('{id}/edit', [\App\Http\Controllers\Panel\Security\IncidentController::class, 'edit'])->name('edit');
            Route::put('{id}', [\App\Http\Controllers\Panel\Security\IncidentController::class, 'update'])->name('update');
            Route::post('{id}/resolve', [\App\Http\Controllers\Panel\Security\IncidentController::class, 'resolve'])->name('resolve');
            Route::post('{id}/followups', [\App\Http\Controllers\Panel\Security\IncidentController::class, 'addFollowup'])->name('followups.store');
            Route::post('{id}/followups/{followupId}/complete', [\App\Http\Controllers\Panel\Security\IncidentController::class, 'completeFollowup'])->name('followups.complete');
        });
    });

    // Compliance — Licenses & Privacy
    Route::prefix('compliance')->name('compliance.')->group(function () {
        Route::get('licenses', [\App\Http\Controllers\Panel\Compliance\LicenseController::class, 'index'])->name('licenses.index');
        Route::post('licenses', [\App\Http\Controllers\Panel\Compliance\LicenseController::class, 'store'])->name('licenses.store');
        Route::put('licenses/{id}', [\App\Http\Controllers\Panel\Compliance\LicenseController::class, 'update'])->name('licenses.update');
        Route::delete('licenses/{id}', [\App\Http\Controllers\Panel\Compliance\LicenseController::class, 'destroy'])->name('licenses.destroy');
        Route::post('licenses/{id}/upload', [\App\Http\Controllers\Panel\Compliance\LicenseController::class, 'uploadDoc'])->name('licenses.upload');

        Route::get('privacy', [\App\Http\Controllers\Panel\Compliance\PrivacyController::class, 'index'])->name('privacy.index');
        Route::get('privacy/{id}/consent', [\App\Http\Controllers\Panel\Compliance\PrivacyController::class, 'consentLog'])->name('privacy.consent');
        Route::get('privacy/{id}/export', [\App\Http\Controllers\Panel\Compliance\PrivacyController::class, 'export'])->name('privacy.export');
        Route::post('privacy/{id}/anonymize', [\App\Http\Controllers\Panel\Compliance\PrivacyController::class, 'anonymize'])->name('privacy.anonymize');
    });

    // ════════════════ ENHANCEMENT: Micro-stay / Day-use ════════════════
    Route::prefix('microstay')->name('microstay.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\MicrostayController::class, 'index'])->name('index');
        Route::get('rates', [\App\Http\Controllers\Panel\MicrostayController::class, 'rates'])->name('rates');
        Route::post('rates', [\App\Http\Controllers\Panel\MicrostayController::class, 'storeRate'])->name('rates.store');
        Route::put('rates/{id}', [\App\Http\Controllers\Panel\MicrostayController::class, 'updateRate'])->name('rates.update');
        Route::delete('rates/{id}', [\App\Http\Controllers\Panel\MicrostayController::class, 'destroyRate'])->name('rates.destroy');
        Route::post('book', [\App\Http\Controllers\Panel\MicrostayController::class, 'book'])->name('book');
    });

    // ════════════════ ENHANCEMENT: Self Check-in Kiosk ════════════════
    Route::prefix('kiosk')->name('kiosk.')->group(function () {
        Route::get('sessions', [\App\Http\Controllers\Panel\KioskController::class, 'sessions'])->name('sessions');
        Route::get('sessions/{id}', [\App\Http\Controllers\Panel\KioskController::class, 'showSession'])->name('sessions.show');
    });

    // ════════════════ ENHANCEMENT: Guest Messaging ════════════════
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\MessagingController::class, 'index'])->name('index');
        Route::get('thread/{id}', [\App\Http\Controllers\Panel\MessagingController::class, 'thread'])->name('thread');
        Route::post('thread/{id}/send', [\App\Http\Controllers\Panel\MessagingController::class, 'send'])->name('send');
        Route::post('thread/{id}/read', [\App\Http\Controllers\Panel\MessagingController::class, 'markRead'])->name('read');
        Route::post('thread/{id}/close', [\App\Http\Controllers\Panel\MessagingController::class, 'closeThread'])->name('close');
        Route::get('thread/{id}/poll', [\App\Http\Controllers\Panel\MessagingController::class, 'poll'])->name('poll');
        Route::get('quick-replies', [\App\Http\Controllers\Panel\MessagingController::class, 'quickReplies'])->name('quick-replies');
        Route::post('quick-replies', [\App\Http\Controllers\Panel\MessagingController::class, 'storeQuickReply'])->name('quick-replies.store');
    });

    // ════════════════ ENHANCEMENT: Dynamic Packaging ════════════════
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\DynamicPackageController::class, 'index'])->name('index');
        Route::get('{id}/builder', [\App\Http\Controllers\Panel\DynamicPackageController::class, 'builder'])->name('builder');
        Route::post('{id}/customize', [\App\Http\Controllers\Panel\DynamicPackageController::class, 'customize'])->name('customize');
        Route::post('{id}/attach/{reservation}', [\App\Http\Controllers\Panel\DynamicPackageController::class, 'attach'])->name('attach');
    });

    // ════════════════ ENHANCEMENT: Upsell Pre-arrival Campaigns ════════════════
    Route::prefix('upsell')->name('upsell.')->group(function () {
        Route::get('campaigns', [\App\Http\Controllers\Panel\UpsellCampaignController::class, 'index'])->name('campaigns.index');
        Route::get('campaigns/create', [\App\Http\Controllers\Panel\UpsellCampaignController::class, 'create'])->name('campaigns.create');
        Route::post('campaigns', [\App\Http\Controllers\Panel\UpsellCampaignController::class, 'store'])->name('campaigns.store');
        Route::get('campaigns/{id}', [\App\Http\Controllers\Panel\UpsellCampaignController::class, 'show'])->name('campaigns.show');
        Route::post('campaigns/{id}/run', [\App\Http\Controllers\Panel\UpsellCampaignController::class, 'run'])->name('campaigns.run');
        Route::post('campaigns/{id}/toggle', [\App\Http\Controllers\Panel\UpsellCampaignController::class, 'toggle'])->name('campaigns.toggle');
        Route::post('presentations/{id}/accept', [\App\Http\Controllers\Panel\UpsellCampaignController::class, 'acceptPresentation'])->name('presentations.accept');
    });

    // ════════════════ ENHANCEMENT: RFM Segmentation Dashboard ════════════════
    Route::prefix('rfm')->name('rfm.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Panel\RfmController::class, 'index'])->name('index');
        Route::post('calculate', [\App\Http\Controllers\Panel\RfmController::class, 'calculate'])->name('calculate');
        Route::get('segments', [\App\Http\Controllers\Panel\RfmController::class, 'segments'])->name('segments');
        Route::post('segments', [\App\Http\Controllers\Panel\RfmController::class, 'storeSegment'])->name('segments.store');
        Route::put('segments/{id}', [\App\Http\Controllers\Panel\RfmController::class, 'updateSegment'])->name('segments.update');
        Route::get('guest/{id}', [\App\Http\Controllers\Panel\RfmController::class, 'guestDetail'])->name('guest');
    });

});
