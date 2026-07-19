<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * Explicit mappings ensure critical listeners are always registered.
     * Auto-discovery handles the rest.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // ── Reservation Events ──
        \App\Events\ReservationCreated::class => [
            \App\Listeners\CreateFolioForReservation::class,
            \App\Listeners\SendBookingConfirmation::class,
            \App\Listeners\LogReservationAudit::class,
        ],

        \App\Events\ReservationCancelled::class => [
            \App\Listeners\CancelFoliosForReservation::class,
            \App\Listeners\ReleaseRoomInventory::class,
            \App\Listeners\SendCancellationEmail::class,
            \App\Listeners\LogCancellationAudit::class,
        ],

        \App\Events\ReservationCheckedIn::class => [
            \App\Listeners\ActivateRoomKeys::class,
            \App\Listeners\UpdateGuestProfileVisit::class,
            \App\Listeners\LogCheckInAudit::class,
        ],

        \App\Events\ReservationCheckedOut::class => [
            \App\Listeners\MarkRoomDirty::class,
            \App\Listeners\CloseGuestFolio::class,
            \App\Listeners\SendPostStaySurvey::class,
            \App\Listeners\LogCheckOutAudit::class,
        ],

        \App\Events\ReservationModified::class => [],
        \App\Events\ReservationNoShow::class => [],

        // ── Folio / Financial Events ──
        \App\Events\FolioCharged::class => [
            \App\Listeners\PostToNightAuditJournal::class,
            \App\Listeners\NotifyGuestOfCharge::class,
        ],

        \App\Events\FolioPaymentReceived::class => [
            \App\Listeners\PostPaymentToJournal::class,
            \App\Listeners\UpdateGuestLoyaltyPoints::class,
            \App\Listeners\SendPaymentReceipt::class,
        ],

        \App\Events\FolioSettled::class => [],

        // ── Night Audit Events ──
        \App\Events\NightAuditStarted::class => [],
        \App\Events\NightAuditCompleted::class => [
            \App\Listeners\GenerateNightAuditReport::class,
            \App\Listeners\SyncRoomCountToChannels::class,
        ],

        // ── Housekeeping Events ──
        \App\Events\RoomStatusChanged::class => [
            \App\Listeners\UpdateHousekeepingBoard::class,
            \App\Listeners\LogRoomStatusHistory::class,
        ],

        \App\Events\HousekeepingTaskAssigned::class => [],
        \App\Events\HousekeepingTaskCompleted::class => [],

        // ── Channel Manager Events ──
        \App\Events\AriSyncCompleted::class => [],
        \App\Events\ChannelBookingReceived::class => [
            \App\Listeners\CreateReservationFromChannel::class,
            \App\Listeners\SyncInventoryAfterBooking::class,
        ],

        // ── Guest Events ──
        \App\Events\GuestRegistered::class => [
            \App\Listeners\CreateOrMergeGuestProfile::class,
            \App\Listeners\SendWelcomeMessage::class,
        ],

        \App\Events\GuestProfileUpdated::class => [],

        // ── Accounting Events ──
        \App\Events\JournalEntryPosted::class => [
            \App\Listeners\VerifyAccountingBalance::class,
            \App\Listeners\PushToExternalAccounting::class,
        ],

        \App\Events\InvoiceIssued::class => [],

        // ── Payment Gateway Events ──
        \App\Events\PaymentGatewayCallbackReceived::class => [],
        \App\Events\PaymentFailed::class => [],

        // ── SaaS / Multi-tenant Events ──
        \App\Events\TenantCreated::class => [],
        \App\Events\TenantSubscriptionChanged::class => [],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array<int, string>
     */
    protected function discoverEventsWithin(): array
    {
        return [
            $this->app->path('Listeners'),
        ];
    }
}
