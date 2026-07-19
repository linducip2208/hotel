<?php

use App\Http\Controllers\Api\V1\AvailabilityController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\FolioController;
use App\Http\Controllers\Api\V1\GuestController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\RateController;
use App\Http\Controllers\Api\V1\HousekeepingController;
use App\Http\Controllers\Api\V1\PosController;
use App\Http\Controllers\Api\V1\AriController;
use App\Http\Controllers\Api\V1\ChannelBookingController;
use App\Http\Controllers\Api\V1\AccountingController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\NightAuditController;
use App\Http\Controllers\Api\V1\ApprovalController;
use App\Http\Controllers\Api\V1\GuestRequestController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\PromoController;
use App\Http\Controllers\Api\V1\SurveyController;
use App\Http\Controllers\Api\V1\StockController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ArApController;
use App\Http\Controllers\Api\V1\KbController;
use App\Http\Controllers\Api\V1\OpenPricingController;
use App\Http\Controllers\Api\V1\DynamicPricingController;
use App\Http\Controllers\Api\V1\ParityController;
use App\Http\Controllers\Api\V1\CoreTaxController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['license', 'auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('properties', [PropertyController::class, 'index']);
    Route::get('properties/{id}', [PropertyController::class, 'show']);
    Route::patch('properties/{id}', [PropertyController::class, 'update']);

    Route::apiResource('room-types', RoomController::class);
    Route::get('rooms', [RoomController::class, 'rooms']);
    Route::patch('rooms/{id}/status', [RoomController::class, 'updateStatus']);

    Route::get('availability', [AvailabilityController::class, 'index']);

    Route::get('rates', [RateController::class, 'index']);
    Route::patch('rates/bulk', [RateController::class, 'bulkUpdate']);

    Route::apiResource('reservations', ReservationController::class);
    Route::post('reservations/{id}/cancel', [ReservationController::class, 'cancel']);
    Route::post('reservations/{id}/check-in', [ReservationController::class, 'checkIn']);
    Route::post('reservations/{id}/check-out', [ReservationController::class, 'checkOut']);
    Route::post('reservations/{id}/no-show', [ReservationController::class, 'noShow']);
    Route::post('reservations/{id}/move-room', [ReservationController::class, 'moveRoom']);

    Route::get('folios/{id}', [FolioController::class, 'show']);
    Route::post('folios/{id}/charges', [FolioController::class, 'addCharge']);
    Route::post('folios/{id}/payments', [FolioController::class, 'addPayment']);
    Route::post('folios/{id}/transfer', [FolioController::class, 'transfer']);
    Route::post('folios/{id}/discount', [FolioController::class, 'addDiscount']);
    Route::get('folios/{id}/invoice.pdf', [FolioController::class, 'invoicePdf']);

    Route::apiResource('guests', GuestController::class);
    Route::get('guests/{id}/stays', [GuestController::class, 'stays']);
    Route::get('guests/{id}/folios', [GuestController::class, 'folios']);

    Route::prefix('hk')->group(function () {
        Route::get('rooms', [HousekeepingController::class, 'rooms']);
        Route::post('tasks', [HousekeepingController::class, 'createTask']);
        Route::patch('tasks/{id}/status', [HousekeepingController::class, 'updateTaskStatus']);
    });

    Route::prefix('pos')->group(function () {
        Route::post('orders', [PosController::class, 'createOrder']);
        Route::patch('orders/{id}', [PosController::class, 'updateOrder']);
        Route::post('orders/{id}/settle', [PosController::class, 'settleOrder']);
    });

    Route::patch('ari/availability', [AriController::class, 'availability']);
    Route::patch('ari/rates', [AriController::class, 'rates']);
    Route::patch('ari/restrictions', [AriController::class, 'restrictions']);
    Route::get('channel/bookings', [ChannelBookingController::class, 'index']);

    Route::prefix('accounting')->group(function () {
        Route::get('coa', [AccountingController::class, 'coa']);
        Route::get('journal-entries', [AccountingController::class, 'journals']);
        Route::post('journal-entries', [AccountingController::class, 'storeJournal']);
        Route::get('reports/daily-revenue', [AccountingController::class, 'dailyRevenue']);
        Route::get('reports/trial-balance', [AccountingController::class, 'trialBalance']);
        Route::get('reports/profit-loss', [AccountingController::class, 'profitLoss']);
    });

    Route::apiResource('webhooks', WebhookController::class);
    Route::post('webhooks/{id}/test', [WebhookController::class, 'test']);
    Route::get('webhooks/{id}/deliveries', [WebhookController::class, 'deliveries']);

    // Phase 2 — Banquet
    Route::prefix('banquet')->group(function () {
        Route::get('function-rooms', [\App\Http\Controllers\Api\V1\BanquetController::class, 'functionRooms']);
        Route::get('events', [\App\Http\Controllers\Api\V1\BanquetController::class, 'events']);
        Route::post('events', [\App\Http\Controllers\Api\V1\BanquetController::class, 'storeEvent']);
        Route::get('events/{id}', [\App\Http\Controllers\Api\V1\BanquetController::class, 'showEvent']);
        Route::post('events/{id}/menu', [\App\Http\Controllers\Api\V1\BanquetController::class, 'addMenu']);
        Route::get('events/{id}/beo', [\App\Http\Controllers\Api\V1\BanquetController::class, 'beo']);
    });

    // Phase 2 — Spa
    Route::prefix('spa')->group(function () {
        Route::get('treatments', [\App\Http\Controllers\Api\V1\SpaController::class, 'treatments']);
        Route::get('appointments', [\App\Http\Controllers\Api\V1\SpaController::class, 'appointments']);
        Route::post('appointments', [\App\Http\Controllers\Api\V1\SpaController::class, 'book']);
        Route::patch('appointments/{id}/complete', [\App\Http\Controllers\Api\V1\SpaController::class, 'complete']);
    });

    // Phase 2 — HR
    Route::prefix('hr')->group(function () {
        Route::get('employees', [\App\Http\Controllers\Api\V1\HrController::class, 'employees']);
        Route::get('employees/{id}', [\App\Http\Controllers\Api\V1\HrController::class, 'showEmployee']);
        Route::post('attendance/clock', [\App\Http\Controllers\Api\V1\HrController::class, 'clockIn']);
        Route::get('payslips', [\App\Http\Controllers\Api\V1\HrController::class, 'payslips']);
        Route::post('payroll/generate', [\App\Http\Controllers\Api\V1\HrController::class, 'generatePayroll']);
    });

    // Phase 2 — Loyalty
    Route::prefix('loyalty')->group(function () {
        Route::get('members', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'members']);
        Route::get('members/{id}', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'show']);
        Route::post('enroll', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'enroll']);
        Route::post('members/{id}/redeem', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'redeem']);
        Route::get('vouchers', [\App\Http\Controllers\Api\V1\LoyaltyController::class, 'vouchers']);
    });

    // Phase 2 — Asset & Maintenance
    Route::prefix('asset')->group(function () {
        Route::get('assets', [\App\Http\Controllers\Api\V1\AssetController::class, 'assets']);
        Route::get('assets/{id}', [\App\Http\Controllers\Api\V1\AssetController::class, 'show']);
        Route::get('work-orders', [\App\Http\Controllers\Api\V1\AssetController::class, 'workOrders']);
        Route::post('work-orders', [\App\Http\Controllers\Api\V1\AssetController::class, 'createWorkOrder']);
        Route::patch('work-orders/{id}', [\App\Http\Controllers\Api\V1\AssetController::class, 'updateWorkOrder']);
    });

    // Phase 2 — Communication
    Route::prefix('comm')->group(function () {
        Route::get('threads', [\App\Http\Controllers\Api\V1\CommController::class, 'threads']);
        Route::get('threads/{id}', [\App\Http\Controllers\Api\V1\CommController::class, 'show']);
        Route::post('threads/{id}/reply', [\App\Http\Controllers\Api\V1\CommController::class, 'reply']);
        Route::post('inbound', [\App\Http\Controllers\Api\V1\CommController::class, 'inbound'])
            ->withoutMiddleware('auth:sanctum'); // public webhook endpoint, signature-verified
    });

    // Phase 2 — Finance
    Route::prefix('finance')->group(function () {
        Route::get('bank-accounts', [\App\Http\Controllers\Api\V1\FinanceController::class, 'bankAccounts']);
        Route::get('owner-statements', [\App\Http\Controllers\Api\V1\FinanceController::class, 'ownerStatements']);
        Route::get('fx-rates', [\App\Http\Controllers\Api\V1\FinanceController::class, 'fxRates']);
        Route::get('fx-rates/lookup', [\App\Http\Controllers\Api\V1\FinanceController::class, 'fxLookup']);
        Route::get('budgets', [\App\Http\Controllers\Api\V1\FinanceController::class, 'budgets']);
    });

    // Phase 2 — AI features
    Route::prefix('ai')->group(function () {
        Route::post('translate', [\App\Http\Controllers\Api\V1\AiController::class, 'translate']);
        Route::post('concierge', [\App\Http\Controllers\Api\V1\AiController::class, 'concierge']);
        Route::post('reviews/{id}/reply', [\App\Http\Controllers\Api\V1\AiController::class, 'reviewReply']);
        Route::get('demand-forecast', [\App\Http\Controllers\Api\V1\AiController::class, 'demandForecast']);
        Route::post('chatbot', [\App\Http\Controllers\Api\V1\AiController::class, 'chatbot']);
    });

    // Night Audit
    Route::prefix('night-audit')->group(function () {
        Route::get('/', [NightAuditController::class, 'index']);
        Route::post('/run', [NightAuditController::class, 'run']);
        Route::get('/{id}', [NightAuditController::class, 'show']);
    });

    // Approval Workflow
    Route::prefix('approvals')->group(function () {
        Route::get('/', [ApprovalController::class, 'index']);
        Route::post('/', [ApprovalController::class, 'store']);
        Route::post('/{id}/approve', [ApprovalController::class, 'approve']);
        Route::post('/{id}/reject', [ApprovalController::class, 'reject']);
    });

    // Guest Requests / Concierge Requests
    Route::prefix('guest-requests')->group(function () {
        Route::get('/', [GuestRequestController::class, 'index']);
        Route::post('/', [GuestRequestController::class, 'store']);
        Route::patch('/{id}', [GuestRequestController::class, 'update']);
        Route::post('/{id}/resolve', [GuestRequestController::class, 'resolve']);
    });

    // Reviews
    Route::prefix('reviews')->group(function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::post('/{id}/publish', [ReviewController::class, 'publish']);
        Route::post('/{id}/unpublish', [ReviewController::class, 'unpublish']);
        Route::get('/{id}/reply-draft', [ReviewController::class, 'replyDraft']);
    });

    // Promo Codes
    Route::prefix('promos')->group(function () {
        Route::get('/', [PromoController::class, 'index']);
        Route::post('/', [PromoController::class, 'store']);
        Route::patch('/{id}', [PromoController::class, 'update']);
        Route::delete('/{id}', [PromoController::class, 'destroy']);
        Route::post('/validate', [PromoController::class, 'validate']);
    });

    // Surveys
    Route::prefix('surveys')->group(function () {
        Route::get('/', [SurveyController::class, 'index']);
        Route::post('/', [SurveyController::class, 'store']);
        Route::get('/{id}', [SurveyController::class, 'show']);
        Route::patch('/{id}', [SurveyController::class, 'update']);
        Route::get('/{id}/responses', [SurveyController::class, 'responses']);
    });

    // Stock Management
    Route::prefix('stock')->group(function () {
        Route::get('/items', [StockController::class, 'items']);
        Route::post('/items', [StockController::class, 'storeItem']);
        Route::get('/items/{id}/movements', [StockController::class, 'movements']);
        Route::post('/items/{id}/movements', [StockController::class, 'addMovement']);
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/daily-flash', [ReportController::class, 'dailyFlash']);
        Route::get('/occupancy', [ReportController::class, 'occupancy']);
        Route::get('/revenue-by-source', [ReportController::class, 'revenueBySource']);
    });

    // AR / AP
    Route::prefix('ar')->group(function () {
        Route::get('/accounts', [ArApController::class, 'arAccounts']);
        Route::get('/invoices', [ArApController::class, 'arInvoices']);
        Route::get('/invoices/{id}', [ArApController::class, 'showArInvoice']);
        Route::post('/invoices/{id}/pay', [ArApController::class, 'payArInvoice']);
    });
    Route::prefix('ap')->group(function () {
        Route::get('/suppliers', [ArApController::class, 'suppliers']);
        Route::get('/bills', [ArApController::class, 'apBills']);
        Route::post('/bills/{id}/pay', [ArApController::class, 'payApBill']);
    });

    // Knowledge Base
    Route::prefix('kb')->group(function () {
        Route::get('/', [KbController::class, 'index']);
        Route::post('/', [KbController::class, 'store']);
        Route::get('/{id}', [KbController::class, 'show']);
        Route::patch('/{id}', [KbController::class, 'update']);
        Route::delete('/{id}', [KbController::class, 'destroy']);
    });

    // Open Pricing — per-date rate overrides and availability grid
    Route::prefix('pricing')->group(function () {
        Route::get('/effective', [OpenPricingController::class, 'effective']);
        Route::get('/grid', [OpenPricingController::class, 'grid']);
        Route::post('/overrides', [OpenPricingController::class, 'bulkUpsert']);
        Route::delete('/overrides/{id}', [OpenPricingController::class, 'destroy']);
    });

    // Dynamic Pricing Rules
    Route::prefix('dynamic-pricing')->group(function () {
        Route::get('/rules', [DynamicPricingController::class, 'rules']);
        Route::post('/rules', [DynamicPricingController::class, 'storeRule']);
        Route::patch('/rules/{id}', [DynamicPricingController::class, 'updateRule']);
        Route::delete('/rules/{id}', [DynamicPricingController::class, 'destroyRule']);
        Route::post('/apply', [DynamicPricingController::class, 'applyNow']);
        Route::get('/log', [DynamicPricingController::class, 'log']);
    });

    // Channel Parity Monitoring
    Route::prefix('parity')->group(function () {
        Route::get('/alerts', [ParityController::class, 'index']);
        Route::post('/check', [ParityController::class, 'checkNow']);
        Route::post('/alerts/{id}/acknowledge', [ParityController::class, 'acknowledge']);
        Route::post('/alerts/{id}/resolve', [ParityController::class, 'resolve']);
    });

    // Coretax DJP Integration
    Route::prefix('coretax')->group(function () {
        Route::post('faktur', [CoreTaxController::class, 'pushFaktur']);
        Route::get('faktur/{nomor}', [CoreTaxController::class, 'checkStatus']);
        Route::post('faktur/{nomor}/cancel', [CoreTaxController::class, 'cancelFaktur']);
        Route::get('nsfp/{year}', [CoreTaxController::class, 'getNsfp']);
    });

    // Guest Intelligence / 360 Profiles
    Route::prefix('guests')->group(function () {
        Route::get('/{id}/profile', [\App\Http\Controllers\Api\V1\GuestController::class, 'profile']);
    });

    // Lost & Found
    Route::prefix('lost-found')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\LostAndFoundController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\V1\LostAndFoundController::class, 'store']);
        Route::get('{id}', [\App\Http\Controllers\Api\V1\LostAndFoundController::class, 'show']);
        Route::patch('{id}', [\App\Http\Controllers\Api\V1\LostAndFoundController::class, 'update']);
        Route::post('{id}/claim', [\App\Http\Controllers\Api\V1\LostAndFoundController::class, 'claim']);
        Route::post('{id}/dispose', [\App\Http\Controllers\Api\V1\LostAndFoundController::class, 'dispose']);
    });
});

Route::get('v1/openapi.json', [\App\Http\Controllers\Api\OpenApiController::class, 'spec']);
