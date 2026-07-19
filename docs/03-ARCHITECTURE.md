# 03 — Architecture

> Modular monolith Laravel. Service layer + event-driven + repository pattern. Same codebase untuk standalone & SaaS via `APP_MODE` flag.

---

## 1. Architectural Principles

1. **Modular monolith** — semua modul (FO, CM, BE, POS, Acc, AI, pSEO) dalam 1 Laravel app, dipisah via `app/Modules/{Module}/` namespace.
2. **Service layer** — logic bisnis di `Services\*`, controller hanya orchestrate.
3. **Event-driven** — operasi penting fire event; listener tangani side-effect (queue, GL post, notification).
4. **Repository pattern** — abstract DB access di `Repositories\*` untuk testability.
5. **Format-based adapter** — integrasi pihak ketiga via adapter berdasarkan format API, bukan per vendor (sesuai global rule "no hardcoded providers").
6. **Standalone-first, SaaS-compatible** — codebase identik, mode dipilih runtime via `APP_MODE`.
7. **Append-only audit** — semua perubahan kritis tercatat di `audit_logs`.
8. **Idempotency by default** — semua webhook & external mutation pakai `idempotency_key`.

---

## 2. Folder Structure

```
app/
├── Console/
│   └── Commands/                 # artisan commands
│       ├── NightAuditCommand.php
│       ├── OtaSyncCommand.php
│       ├── PseoSitemapCommand.php
│       └── LicenseHeartbeatCommand.php
├── Events/                        # domain events
│   ├── ReservationCreated.php
│   ├── ReservationCheckedIn.php
│   ├── FolioCharged.php
│   ├── PaymentReceived.php
│   ├── OtaBookingIngested.php
│   └── ...
├── Listeners/                     # event handlers (queue-able)
│   ├── PostFolioToGl.php
│   ├── SendConfirmationEmail.php
│   ├── SyncRoomToOta.php
│   ├── NotifyHousekeeping.php
│   └── ...
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                # admin panel routes
│   │   ├── Owner/                # owner-only routes
│   │   ├── Staff/                # staff (FO/HK/POS/Acc) routes
│   │   ├── Guest/                # public guest portal
│   │   ├── Api/                  # REST API
│   │   └── Webhook/              # OTA webhooks, payment callbacks
│   ├── Middleware/
│   │   ├── RequirePair.php       # license pairing gate
│   │   ├── EnsureProperty.php    # multi-property scope
│   │   ├── TenantInitializer.php # SaaS only
│   │   └── AuditLogger.php
│   └── Requests/                 # FormRequest validators
├── Models/
│   ├── Property.php
│   ├── Room.php
│   ├── RoomType.php
│   ├── RatePlan.php
│   ├── Reservation.php
│   ├── Guest.php
│   ├── Folio.php
│   ├── Charge.php
│   ├── Payment.php
│   ├── HousekeepingTask.php
│   ├── PosOrder.php
│   ├── GlAccount.php
│   ├── JournalEntry.php
│   ├── Provider.php              # BYOK integration provider
│   ├── License.php
│   └── ...
├── Modules/                       # business logic per modul
│   ├── FrontOffice/
│   │   ├── Services/
│   │   │   ├── ReservationService.php
│   │   │   ├── CheckInService.php
│   │   │   ├── NightAuditService.php
│   │   │   └── RoomAssignmentService.php
│   │   ├── Repositories/
│   │   ├── DTOs/
│   │   └── Policies/
│   ├── ChannelManager/
│   │   ├── Services/
│   │   ├── Adapters/             # format-based adapters
│   │   │   ├── BookingComAdapter.php
│   │   │   ├── AgodaAdapter.php
│   │   │   ├── TravelokaAdapter.php
│   │   │   └── ChannelAdapterInterface.php
│   │   ├── Jobs/
│   │   │   ├── PushAvailabilityJob.php
│   │   │   ├── PullBookingsJob.php
│   │   │   └── ResolveConflictJob.php
│   │   └── DTOs/
│   ├── BookingEngine/
│   ├── Pos/
│   ├── Housekeeping/
│   ├── Accounting/
│   │   ├── Services/
│   │   │   ├── GlPostingService.php
│   │   │   ├── ReportService.php
│   │   │   └── EFakturService.php
│   │   └── Posting/
│   │       └── PostingRules.php
│   ├── Ai/
│   │   ├── Services/
│   │   │   ├── AiClient.php
│   │   │   └── ConciergeService.php
│   │   ├── Adapters/             # FORMAT-based, not vendor
│   │   │   ├── OpenAICompatibleAdapter.php
│   │   │   ├── AnthropicFormatAdapter.php
│   │   │   ├── GeminiFormatAdapter.php
│   │   │   └── AiAdapterInterface.php
│   │   └── DTOs/
│   ├── Payment/
│   │   ├── Adapters/             # FORMAT-based payment
│   │   │   ├── RedirectFlowAdapter.php
│   │   │   ├── EmbedFlowAdapter.php
│   │   │   ├── QrisFlowAdapter.php
│   │   │   └── PaymentAdapterInterface.php
│   │   └── Services/
│   ├── Pseo/
│   │   ├── Services/
│   │   │   ├── PseoRouteResolver.php
│   │   │   ├── ContentGenerator.php
│   │   │   └── SitemapGenerator.php
│   │   └── Templates/
│   ├── Compliance/                # Indonesia-specific
│   │   ├── Services/
│   │   │   ├── Pb1Service.php
│   │   │   ├── EFakturService.php
│   │   │   ├── LaporWnaService.php
│   │   │   └── KtpOcrService.php
│   ├── License/
│   │   ├── Services/
│   │   │   ├── LicenseClient.php       # adopt v3 from whitelabel
│   │   │   └── PairingService.php
│   │   └── Middleware/
│   ├── Tenancy/
│   │   ├── Bootstrappers/
│   │   └── Services/
│   └── ...
├── Providers/
│   ├── AppServiceProvider.php
│   ├── EventServiceProvider.php
│   ├── RouteServiceProvider.php
│   ├── ModuleServiceProvider.php  # auto-register modules
│   ├── TenancyServiceProvider.php # SaaS only
│   └── AdapterServiceProvider.php # bind format adapters
└── Support/
    ├── Money.php                  # value object Rp dengan presisi
    ├── DateRange.php
    └── ...

config/
├── app.php
├── hotel.php                      # global hotel config
├── modes.php                      # standalone / saas mode flags
├── tenancy.php                    # SaaS only
├── license.php                    # client kit v3 config
├── ai.php                         # default adapter config
├── ota.php                        # OTA defaults
├── pseo.php                       # pSEO routes & templates
└── ...

database/
├── migrations/
│   ├── shared/                    # jalan di standalone & SaaS tenant DB
│   ├── landlord/                  # SaaS central DB only
│   └── tenant/                    # SaaS tenant DB only (= shared sebenarnya)
├── seeders/
│   ├── ProductionSeeder.php
│   ├── DemoSeeder.php
│   └── ...
└── factories/

resources/
├── views/
│   ├── layouts/
│   ├── admin/
│   ├── owner/
│   ├── staff/
│   ├── guest/                     # public booking engine
│   ├── pseo/
│   └── license/
│       ├── pair-wizard.blade.php
│       └── pair-success.blade.php
├── js/
└── css/

routes/
├── web.php
├── admin.php
├── owner.php
├── staff.php
├── guest.php
├── api.php
├── webhook.php
├── pseo.php
├── pair-routes.php                # license v3 from kit
└── tenant.php                     # SaaS only

storage/
└── app/
    ├── llm-presets/               # JSON presets (autofill UI)
    ├── ota-presets/
    ├── payment-presets/
    └── lock-presets/
```

---

## 3. Layer Diagram

```
┌─────────────────────────────────────────────────────────────┐
│  HTTP Layer (Controllers)                                   │
│  • Validate request via FormRequest                         │
│  • Authorize via Policy                                     │
│  • Delegate to Service                                      │
│  • Format response (Resource / Blade view)                  │
└──────────────┬──────────────────────────────────────────────┘
               │
┌──────────────▼──────────────────────────────────────────────┐
│  Service Layer (app/Modules/*/Services/*)                   │
│  • Business logic                                           │
│  • Transaction management                                   │
│  • Event dispatching                                        │
│  • Coordinate Repositories + Adapters                       │
└──────────────┬─────────────────────┬────────────────────────┘
               │                     │
       ┌───────▼───────┐    ┌────────▼─────────┐
       │ Repositories  │    │ Adapters         │
       │ (DB access)   │    │ (External APIs)  │
       │ Eloquent +    │    │ Format-based,    │
       │ scopes        │    │ BYOK config      │
       └───────┬───────┘    └────────┬─────────┘
               │                     │
       ┌───────▼───────┐    ┌────────▼─────────┐
       │ MySQL/Postgres│    │ External: OTA,   │
       │ via Eloquent  │    │ Payment, AI, WA, │
       └───────────────┘    │ SMS, Lock        │
                            └──────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Event Bus (Laravel Events + Queue)                         │
│  • Async listeners → side effects                           │
│  • GL posting, OTA sync, email, audit log                   │
└─────────────────────────────────────────────────────────────┘
```

---

## 4. Event Flow Examples

### A. Guest checks in via FO

```
[FO staff] click "Check-in" on reservation
    ↓
ReservationController@checkIn
    ↓
CheckInService::execute(ReservationId)
    ├─ Update reservation.status = 'in_house'
    ├─ Update room.status = 'occupied'
    ├─ Open folio if not exists
    ├─ Auto-post room charge (1st night)
    ├─ Generate registration card PDF
    └─ Fire event: ReservationCheckedIn
        ↓
        Listeners (async via queue):
          ├─ SendCheckInEmailListener     → mail queue
          ├─ NotifyHousekeepingListener   → realtime ws + db
          ├─ AuditLogListener              → audit_logs append
          ├─ SyncRoomToOtaListener         → ota-sync queue (block other OTAs)
          ├─ PostFolioToGlListener         → GL journal entry
          ├─ TriggerLaporWnaListener       → if guest WNA, queue lapor
          └─ AwardLoyaltyPointsListener    → if member
```

### B. OTA booking ingest (Booking.com webhook)

```
POST /webhook/ota/booking-com (HMAC verified)
    ↓
WebhookController@handleBookingCom
    ↓
ProcessOtaBookingJob::dispatch($payload) → ota-ingest queue
    ↓
[worker] OtaBookingIngestService::ingest($payload)
    ├─ Map OTA room type → internal RoomType
    ├─ Find or create Guest (email match or create new)
    ├─ Create Reservation
    ├─ Open folio + post charges
    ├─ Mark inventory taken (sync back to other OTAs)
    └─ Fire: OtaBookingIngested
        ↓
        Listeners:
          ├─ SendConfirmationEmail         (if email present)
          ├─ SyncOtherOtasListener         → push reduced ARI to all other OTAs
          ├─ AuditLog
          └─ NotifyFrontOffice             → realtime
```

### C. AI Concierge guest message

```
[Guest WhatsApp] "Boleh extend checkout sampai 2 PM?"
    ↓
WhatsAppWebhookController@receive
    ↓
ConciergeService::handleMessage($from, $text)
    ├─ Lookup guest by phone
    ├─ Build context: reservation, room, current charges
    ├─ AiClient::ask(systemPrompt, userText, context)
    │     ↓
    │   AdapterFactory::resolve(activeProvider) → e.g. OpenAICompatibleAdapter
    │     ↓
    │   POST {base_url}/chat/completions (BYOK API key)
    │     ↓
    │   Return AI text + structured action (if any)
    ├─ If action = "request_late_checkout":
    │     LateCheckoutService::request(reservation, until=14:00)
    │     ↓ Fire ReservationModified event
    └─ Send AI reply via WhatsApp
```

---

## 5. Adapter Pattern (BYOK rule)

### Interface

```php
// app/Modules/Ai/Adapters/AiAdapterInterface.php
interface AiAdapterInterface
{
    public function chat(array $messages, array $options = []): AiChatResponse;
    public function listModels(): array;
    public function tokenCount(string $text): int;
}
```

### Format-based implementations

```php
// OpenAI-compatible (covers DeepSeek, Groq, Together, Fireworks,
// Mistral, DeepInfra, OpenRouter, Cerebras, OpenAI itself,
// Ollama, LM Studio, vLLM)
class OpenAICompatibleAdapter implements AiAdapterInterface { ... }

// Anthropic format
class AnthropicFormatAdapter implements AiAdapterInterface { ... }

// Gemini format
class GeminiFormatAdapter implements AiAdapterInterface { ... }
```

### Resolution

```php
// app/Modules/Ai/Services/AiClient.php
public function ask(string $prompt): AiChatResponse
{
    $provider = Provider::active('ai')->first();
    $adapter = AdapterFactory::for($provider->api_format)
        ->withConfig([
            'base_url'       => $provider->base_url,
            'api_key'        => decrypt($provider->api_key_encrypted),
            'extra_headers'  => $provider->extra_headers ?? [],
            'model'          => $provider->default_model,
        ]);

    return $adapter->chat([
        ['role' => 'system', 'content' => $this->systemPrompt()],
        ['role' => 'user',   'content' => $prompt],
    ]);
}
```

**Hard rule:** TIDAK ADA class bernama `OpenAIAdapter`, `MidtransAdapter`, `BookingComAdapter` dengan logic vendor-specific. Class adapter purely format-based.

Untuk OTA (yang spec API-nya per-vendor sangat unik) — pengecualian terbatas: `BookingComAdapter`, `AgodaAdapter`, `TravelokaAdapter` boleh ada karena API mereka bukan "format keluarga". Tapi tetap implement `ChannelAdapterInterface` shared. (Ini decision pragmatik — payment & AI bisa format-based, OTA tidak.)

---

## 6. Mode Selection

```php
// config/modes.php
return [
    'mode' => env('APP_MODE', 'standalone'), // standalone | saas
    'tenancy_enabled' => env('APP_MODE') === 'saas',
    'license_required' => env('APP_MODE') === 'standalone',
];

// app/Providers/AppServiceProvider.php
public function register()
{
    if (config('modes.tenancy_enabled')) {
        $this->app->register(TenancyServiceProvider::class);
    }
    if (config('modes.license_required')) {
        $this->app->register(LicenseServiceProvider::class);
    }
}

// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        // Standalone only
        \App\Modules\License\Middleware\RequirePair::class,
        // SaaS only
        \App\Modules\Tenancy\Middleware\TenantInitializer::class,
    ]);
})
```

Middleware self-skip jika mode tidak match.

---

## 7. Tenancy Strategy (SaaS)

`stancl/tenancy` v4. DB-per-tenant, subdomain identification.

```
Request → app.{tenant}.hotelxyz.com
    ↓
TenantInitializer middleware
    ├─ Resolve tenant by subdomain
    ├─ Switch DB connection: tenant_<uuid>
    ├─ Switch cache prefix: tenant:<uuid>:
    ├─ Switch storage path: tenants/<uuid>/
    └─ Continue request
```

Central DB tetap accessible via `central()` helper untuk billing, plan, etc.

Detail: [`18-SAAS_UPGRADE_PATH.md`](18-SAAS_UPGRADE_PATH.md).

---

## 8. RBAC

`spatie/laravel-permission`. Roles:

- **owner** — semua, tidak bisa di-revoke
- **manager** — semua kecuali billing & owner-level config
- **front_office** — reservation, guest, folio, payment
- **housekeeping** — room status, task
- **pos_cashier** — POS only + folio post
- **accounting** — GL, AR/AP, report, e-Faktur
- **revenue_manager** — rate plan, channel manager
- **maintenance** — work order
- **hr** — employee, payroll (Phase 2)
- **read_only_auditor** — view all, no edit

Permission granular: `reservation.create`, `reservation.cancel`, `folio.transfer`, `gl.post`, `ota.sync`, dll.

Detail matrix: [`15-ADMIN_SECURITY.md`](15-ADMIN_SECURITY.md).

---

## 9. Audit Log

`spatie/laravel-activitylog` + custom append-only constraint.

Tracked actions:
- All `Reservation`, `Folio`, `Charge`, `Payment`, `JournalEntry` CUD
- All login/logout, 2FA, password reset
- All RBAC role/permission changes
- All admin config changes (BYOK provider add/edit/delete — tanpa expose key)
- All license activate/heartbeat/revoke

Append-only enforcement: trigger DB-level + Eloquent observer prevent UPDATE/DELETE pada `audit_logs`.

---

## 10. Idempotency

Semua POST yang mutate state (booking create, payment, OTA push, etc.) accept `Idempotency-Key` header. Service cek di Redis (TTL 24h) — kalau sudah ada response cached, return tanpa eksekusi ulang.

```php
$key = $request->header('Idempotency-Key');
if ($cached = Cache::get("idem:{$key}")) {
    return $cached;
}
$response = $this->service->execute(...);
Cache::put("idem:{$key}", $response, 86400);
return $response;
```

---

## 11. Event Catalog (key events)

| Event | Fired oleh | Listener (sample) |
|---|---|---|
| `ReservationCreated` | ReservationService | SendConfirmationEmail, SyncRoomToOta, PostFolioToGl |
| `ReservationCheckedIn` | CheckInService | NotifyHousekeeping, TriggerLaporWna, AuditLog |
| `ReservationCheckedOut` | CheckOutService | FinalizeFolio, PostGlClose, AwardLoyaltyPoints |
| `ReservationCancelled` | ReservationService | RefundIfApplicable, ReleaseInventory, NotifyOta |
| `FolioCharged` | ChargeService | RecalculateFolio, PostGl |
| `PaymentReceived` | PaymentService | UpdateFolioBalance, AuditLog, IssueReceipt |
| `OtaBookingIngested` | OtaBookingService | SendConfirmationEmail, SyncOtherOtas, NotifyFo |
| `RoomStatusChanged` | HousekeepingService | NotifyFo, BroadcastWs |
| `PosOrderClosed` | PosService | PostToFolioOrPayment, KitchenComplete |
| `NightAuditCompleted` | NightAuditService | GenerateDailyReport, EmailManagement |
| `LicensePaired` | LicenseClient | EmitInstallation, AuditLog |
| `LicenseRevoked` | LicenseClient | LogOutSessions, NotifyOwner |

---

## 12. Naming Conventions

| Layer | Pattern |
|---|---|
| Controller | `XxxController` (singular) |
| Service | `XxxService` |
| Repository | `XxxRepository` |
| Adapter | `FormatAdapter` (e.g. `OpenAICompatibleAdapter`) |
| Job | `VerbXxxJob` (e.g. `PushAvailabilityJob`) |
| Event | `NounVerbedEvent` past-tense (e.g. `ReservationCheckedIn`) |
| Listener | `VerbNounListener` (e.g. `SendConfirmationEmailListener`) |
| Migration | `2026_04_28_create_reservations_table` |
| Route name | `dot.case` (e.g. `reservations.create`) |
| Permission | `dot.case` (e.g. `reservation.create`) |
| Test class | `XxxTest` mirrors structure |

---

## 13. Coding Standards

- **PSR-12** + Laravel Pint default
- **PHPStan level 6** minimum (level 8 target untuk new code)
- **Strict types** declared: `declare(strict_types=1);`
- **Eloquent** OK untuk queries simple; **DB query builder** untuk reporting (faster, less hydration)
- **DTOs** untuk data transfer cross-layer (`spatie/laravel-data` ringan)
- **No `mixed`** kecuali strict-typed downstream
- **No facades di service layer** — inject via constructor

---

## 14. Testing Strategy

- **Unit tests** — service methods, adapter contract conformance
- **Feature tests** — HTTP request → response flow
- **Integration tests** — OTA mock servers (Booking.com sandbox), payment sandbox
- **Browser tests** (Pest+Dusk) — wizard pairing, booking engine flow
- **Architecture tests** (Pest arch) — enforce adapter naming, no facades in services
- **Mutation tests** (Infection) — high-value modules: pricing, accounting, tax

Coverage target: **80% line coverage** modul accounting + tax + pricing (highest risk). 50% baseline lain.

Detail: [`21-QA_CHECKLIST.md`](21-QA_CHECKLIST.md).

---

## 15. Technical Debt Policy

- TODO → harus include issue link + due date
- "Temporary" workaround → tracked sebagai task di `22-PROGRESS.md`
- Deprecation: minimum 1 minor version warning sebelum removal
- No silent fallback yang masking bug — semua fallback log + telemetry
