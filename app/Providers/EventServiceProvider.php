<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\AriSyncCompleted;
use App\Events\ChannelBookingReceived;
use App\Events\FolioCharged;
use App\Events\FolioPaymentReceived;
use App\Events\FolioSettled;
use App\Events\GuestProfileUpdated;
use App\Events\GuestRegistered;
use App\Events\HousekeepingTaskAssigned;
use App\Events\HousekeepingTaskCompleted;
use App\Events\InvoiceIssued;
use App\Events\JournalEntryPosted;
use App\Events\NightAuditCompleted;
use App\Events\NightAuditStarted;
use App\Events\PaymentFailed;
use App\Events\PaymentGatewayCallbackReceived;
use App\Events\ReservationCancelled;
use App\Events\ReservationCheckedIn;
use App\Events\ReservationCheckedOut;
use App\Events\ReservationCreated;
use App\Events\ReservationModified;
use App\Events\ReservationNoShow;
use App\Events\RoomStatusChanged;
use App\Events\TenantCreated;
use App\Events\TenantSubscriptionChanged;
use App\Listeners\ActivateRoomKeys;
use App\Listeners\CancelFoliosForReservation;
use App\Listeners\CloseGuestFolio;
use App\Listeners\CreateFolioForReservation;
use App\Listeners\CreateOrMergeGuestProfile;
use App\Listeners\CreateReservationFromChannel;
use App\Listeners\GenerateNightAuditReport;
use App\Listeners\LogCancellationAudit;
use App\Listeners\LogCheckInAudit;
use App\Listeners\LogCheckOutAudit;
use App\Listeners\LogReservationAudit;
use App\Listeners\LogRoomStatusHistory;
use App\Listeners\MarkRoomDirty;
use App\Listeners\NotifyGuestOfCharge;
use App\Listeners\PostPaymentToJournal;
use App\Listeners\PostToNightAuditJournal;
use App\Listeners\PushToExternalAccounting;
use App\Listeners\ReleaseRoomInventory;
use App\Listeners\SendBookingConfirmation;
use App\Listeners\SendCancellationEmail;
use App\Listeners\SendPaymentReceipt;
use App\Listeners\SendPostStaySurvey;
use App\Listeners\SendWelcomeMessage;
use App\Listeners\SyncInventoryAfterBooking;
use App\Listeners\SyncRoomCountToChannels;
use App\Listeners\UpdateGuestLoyaltyPoints;
use App\Listeners\UpdateGuestProfileVisit;
use App\Listeners\UpdateHousekeepingBoard;
use App\Listeners\VerifyAccountingBalance;
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
        ReservationCreated::class => [
            CreateFolioForReservation::class,
            SendBookingConfirmation::class,
            LogReservationAudit::class,
        ],

        ReservationCancelled::class => [
            CancelFoliosForReservation::class,
            ReleaseRoomInventory::class,
            SendCancellationEmail::class,
            LogCancellationAudit::class,
        ],

        ReservationCheckedIn::class => [
            ActivateRoomKeys::class,
            UpdateGuestProfileVisit::class,
            LogCheckInAudit::class,
        ],

        ReservationCheckedOut::class => [
            MarkRoomDirty::class,
            CloseGuestFolio::class,
            SendPostStaySurvey::class,
            LogCheckOutAudit::class,
        ],

        ReservationModified::class => [],
        ReservationNoShow::class => [],

        // ── Folio / Financial Events ──
        FolioCharged::class => [
            PostToNightAuditJournal::class,
            NotifyGuestOfCharge::class,
        ],

        FolioPaymentReceived::class => [
            PostPaymentToJournal::class,
            UpdateGuestLoyaltyPoints::class,
            SendPaymentReceipt::class,
        ],

        FolioSettled::class => [],

        // ── Night Audit Events ──
        NightAuditStarted::class => [],
        NightAuditCompleted::class => [
            GenerateNightAuditReport::class,
            SyncRoomCountToChannels::class,
        ],

        // ── Housekeeping Events ──
        RoomStatusChanged::class => [
            UpdateHousekeepingBoard::class,
            LogRoomStatusHistory::class,
        ],

        HousekeepingTaskAssigned::class => [],
        HousekeepingTaskCompleted::class => [],

        // ── Channel Manager Events ──
        AriSyncCompleted::class => [],
        ChannelBookingReceived::class => [
            CreateReservationFromChannel::class,
            SyncInventoryAfterBooking::class,
        ],

        // ── Guest Events ──
        GuestRegistered::class => [
            CreateOrMergeGuestProfile::class,
            SendWelcomeMessage::class,
        ],

        GuestProfileUpdated::class => [],

        // ── Accounting Events ──
        JournalEntryPosted::class => [
            VerifyAccountingBalance::class,
            PushToExternalAccounting::class,
        ],

        InvoiceIssued::class => [],

        // ── Payment Gateway Events ──
        PaymentGatewayCallbackReceived::class => [],
        PaymentFailed::class => [],

        // ── SaaS / Multi-tenant Events ──
        TenantCreated::class => [],
        TenantSubscriptionChanged::class => [],
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
