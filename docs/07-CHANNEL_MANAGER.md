# 07 — Channel Manager

> Arsitektur sync 2-arah ke OTA. ARI (Availability/Rate/Inventory) push outbound + booking ingest inbound. Conflict resolution + idempotency.

---

## 1. Goals

- **Tidak ada double booking** — saat OTA A book 1 kamar, OTA lain & PMS langsung tahu inventory turun
- **Rate parity (jika diaktifkan)** — same room, same date → same price across channels
- **2-way sync** — booking dari OTA masuk PMS otomatis, modifikasi PMS push ke OTA
- **Resilient** — OTA API down? Queue retry tanpa kehilangan event
- **Auditable** — setiap sync event tercatat di `channel_sync_logs`
- **OTA Indonesia first-class** — Traveloka, Tiket.com, Mister Aladin, Pegipegi setara prioritasnya dengan Booking.com & Agoda

---

## 2. Supported OTAs

### MVP Phase 1
- ✅ Booking.com (XML push/pull)
- ✅ Agoda (YCS API)
- ✅ Traveloka (TPI API)

### Phase 2
- 🟡 Tiket.com
- 🟡 Mister Aladin
- 🟡 Pegipegi
- 🟡 Expedia (EQC)
- 🟡 Airbnb
- 🟡 Trip.com

### Phase 3+
- 🔵 GDS (Sabre, Amadeus, Travelport) — corporate/MICE international
- 🔵 Direct hotel websites (white-label resell)

---

## 3. Adapter Interface

```php
interface ChannelAdapterInterface
{
    public function pushAri(PropertyId $p, DateRange $r, AriPayload $payload): SyncResult;
    public function pullBookings(PropertyId $p, DateRange $r): array;
    public function ackBooking(string $externalId): bool;
    public function listExternalRooms(PropertyId $p): array;
    public function listExternalRatePlans(PropertyId $p): array;
    public function verifyWebhook(string $signature, string $body): bool;
    public function parseWebhook(string $body): WebhookPayload;
}

final readonly class AriPayload
{
    public function __construct(
        public array $rates,         // [date => price]
        public array $inventory,     // [date => available_count]
        public array $restrictions,  // [date => ['cta'=>bool,'ctd'=>bool,'min_los'=>int,'max_los'=>int,'closed'=>bool]]
    ) {}
}
```

---

## 4. Database

### `channels`
- Master OTA list. Seeded di migration.

### `channel_credentials` (per property × per channel)
- Hotel ID di OTA, API key, secret, webhook URL, etc.

### `channel_room_mappings`
- Internal `room_type_id` ↔ external `external_room_id` + `external_rate_plan_id`
- Setup wajib sebelum sync aktif. UI drag-drop atau dropdown.

### `channel_sync_logs`
- Setiap push & pull event tercatat — request, response, status, duration

### `channel_bookings`
- Buffer table — booking inbound dari OTA sebelum di-ingest ke `reservations`
- Memungkinkan replay & debugging

---

## 5. Outbound: ARI Sync

Trigger:
- **Reservation created/updated/cancelled** → recalc availability + push to all OTAs
- **Rate plan updated** → push price to all OTAs
- **Manual rate calendar edit** → push immediately
- **Periodic full sync** every 15 minutes (catchup, idempotent)

```
Event: ReservationCreated (room_type_id=5, dates=2026-05-01..2026-05-03)
    ↓
Listener: SyncRoomToOtaListener
    ↓
For each active channel × room_type mapping:
    Dispatch job: PushAriJob(channel_id, room_type_id, date_range)
    ↓
[worker]
    Calculate inventory: total_rooms - reserved_rooms_for_dates
    Calculate rates: rate_calendar lookup
    Calculate restrictions: from rate_plan
    Build AriPayload
    ↓
    adapter::pushAri(payload)
    ↓
    Log to channel_sync_logs
    Update last_sync_at on credential
    ↓
    On error → retry 5x exponential (1s, 10s, 1min, 5min, 30min)
```

### Push throttling
- Max 1 push per channel per room_type per 5s (debounce, batch updates)
- Bulk push untuk full sync (jangan per-date individual)

---

## 6. Inbound: Booking Ingest

Two paths:
- **Webhook** (push from OTA) — preferred, low latency
- **Polling** (every 5 minutes pull recent bookings) — fallback if webhook missed

### Webhook flow

```
POST /webhook/ota/booking-com
    Headers: X-OTA-Signature: ...
    Body: <XML>...</XML>
    ↓
Middleware: verify signature (HMAC sesuai per-OTA)
    ↓
Log raw payload to webhook_logs (idempotent dedupe by event_id)
    ↓
Dispatch: ProcessOtaBookingJob → ota-ingest queue
    ↓
[worker]
    Parse via channel adapter → BookingDto
    Save to channel_bookings (buffer)
    ↓
    Match or create Guest:
        - Email match? → existing guest, update last contact
        - Phone match? → confirm
        - New → create
    ↓
    Map external_room_id → internal room_type_id (via channel_room_mappings)
    ↓
    Create Reservation:
        - source = 'booking_com'
        - ota_reservation_id = external ID
        - status = 'booked'
        - all dates, occupancy, rate from payload
    ↓
    Open folio, post charges (room rate × nights)
    ↓
    Fire ReservationCreated event
    ↓
    Mark channel_bookings.status = 'processed'
    Sync inventory back to other OTAs (block this date for other channels)
```

### Polling flow (fallback)
```
Schedule: every 5 minutes
    ↓
For each active channel × property:
    Dispatch PullBookingsJob(channel, property, last_pulled_at..now)
    ↓
[worker]
    adapter::pullBookings(date_range)
    For each booking:
        if (channel_bookings.where('channel_booking_id', $booking->external_id)->exists()) skip
        else process same as webhook flow
```

---

## 7. Conflict Resolution

### Conflict scenarios

**A. Race condition: 2 OTA book same room same time**
- Inventory: total = 5 rooms, reserved = 4. OTA A book → -1. OTA B book at same time → -1 (race, oversold)
- **Mitigation:** DB row lock pada `room_type` saat ingest. `SELECT ... FOR UPDATE`. Reject second booking → mark as `oversold`, alert manager → manual rebook to higher tier or refund.

**B. PMS update conflict with OTA inbound**
- FO edit reservation, OTA push update at same time
- **Mitigation:** last-write-wins for non-critical fields. Critical fields (dates, room_type, total) → flag for manual review.

**C. Rate edit while booking in progress**
- Manager update rate, guest sedang checkout di booking engine
- **Mitigation:** rate snapshot at booking creation; OTA price already locked at confirmation.

**D. Cancellation race**
- Guest cancel via OTA + via direct call to hotel simultaneous
- **Mitigation:** idempotent cancel — second event no-op, log notification.

---

## 8. Mapping UI

```
Admin → Channel Manager → Booking.com → Mappings
┌─────────────────────────────────────────────────────────┐
│  Internal Room Type    │  Booking.com Room              │
├────────────────────────┼────────────────────────────────┤
│  Deluxe Garden         │  [Deluxe Garden View    v]     │
│  Suite Pool            │  [Pool Suite             v]    │
│  Family Room           │  [Family Triple          v]    │
│  Standard              │  [Standard Double        v]    │
│  ...                   │                                 │
│  [+ Add Mapping]                                         │
└─────────────────────────────────────────────────────────┘
```

Dropdown isi populated via `adapter::listExternalRooms()`. Mapping per-property.

Saat reservation ingest dengan `external_room_id` belum ter-map → reservation status `pending_mapping` + alert manager.

---

## 9. Restrictions

Per rate plan + per date:

| Restriction | Behavior |
|---|---|
| `min_los` | Min N malam wajib book |
| `max_los` | Max N malam boleh book |
| `cta` (close to arrival) | Tamu tidak boleh check-in pada tanggal ini |
| `ctd` (close to departure) | Tamu tidak boleh check-out pada tanggal ini |
| `closed` | Inventory 0 (block channel) |
| `no_arrival` | Tidak ada arrival diizinkan tapi stayover OK |
| `no_departure` | Sama, untuk departure |

Push semua restrictions ke OTA via `pushAri`.

---

## 10. Derived Rates

Rate plan A = Rp 1,000,000 (BAR)
Rate plan B (Booking.com) = derived from A + 12% (commission gross-up)
Rate plan C (Agoda) = derived from A + 10%

Saat A diupdate, B & C auto-recalc & push.

```php
class RateDerivationService
{
    public function calculateDerived(RatePlan $plan, Carbon $date): float
    {
        if ($plan->plan_type !== 'derived') {
            return $plan->getRateForDate($date);
        }
        $parent = $plan->parentRatePlan;
        $base = $parent->getRateForDate($date);
        return match (true) {
            str_starts_with($plan->derive_formula, '+') && str_ends_with($plan->derive_formula, '%') =>
                $base * (1 + (float) trim($plan->derive_formula, '+%') / 100),
            str_starts_with($plan->derive_formula, '+') =>
                $base + (float) substr($plan->derive_formula, 1),
            // ...
        };
    }
}
```

---

## 11. Channel Mix Reporting

Dashboard shows:
- Bookings count per channel (last 7/30/90 day)
- Revenue per channel
- Lead time per channel
- Cancellation rate per channel
- Commission paid per channel (configurable rate or imported)
- Direct booking % vs OTA %

Goal: shift dari OTA-heavy → direct-heavy via pSEO + booking engine.

---

## 12. Error Handling Patterns

| Error | Behavior |
|---|---|
| 401 / auth fail | Mark credential as invalid, alert owner via email + UI banner. Stop sync until refreshed |
| 429 rate limit | Exponential backoff, respect `Retry-After` header |
| 5xx server error | Retry queue 5x, log all attempts. After exhaustion → DLQ + alert |
| Validation error from OTA (4xx) | Log + alert, don't retry (business logic issue, not transient) |
| Network timeout | Retry with longer timeout next attempt |
| Mapping not found | Reservation marked `pending_mapping`, alert manager, no auto-retry |
| Duplicate booking ID | Idempotent skip, log info |
| Oversold | Critical alert. Auto-rebook to higher tier if price match. Else manual escalation. |

---

## 13. Webhook Signature Verification

Per OTA berbeda. Implementation di `verifyWebhook` per adapter.

Booking.com: HMAC-SHA256 dengan secret di credential.
Agoda: shared secret + timestamp window 5 menit.
Traveloka: HMAC-SHA1 dengan format khusus.

Reject signature invalid → 401 + log security event.

---

## 14. Idempotency

- Inbound webhook: dedupe by `(channel_id, external_event_id)` di `webhook_logs`
- Inbound booking ingest: dedupe by `(channel_id, channel_booking_id)` di `channel_bookings`
- Outbound push: idempotency_key = `(property, room_type, date_range, payload_hash)` — kalau payload sama dengan last successful push, skip

---

## 15. Test Sandboxes

Setiap OTA punya sandbox/test environment. Owner toggle in UI: `Production` / `Sandbox`. Different `base_url` + `api_key` per env.

| OTA | Sandbox URL |
|---|---|
| Booking.com | https://supply-xml.booking.com (sandbox via partner request) |
| Agoda | https://ycs-stg.agoda.com |
| Traveloka | (per-partner sandbox URL) |
| Tiket.com | (per-partner sandbox) |

---

## 16. Implementation Plan (Sprint 4)

```
Day 1-2: Channel adapter interface + DTO + base service
Day 3-4: BookingComAdapter (XML push/pull)
Day 5-6: Booking ingest service + reservation creation flow
Day 7-8: AgodaAdapter
Day 9: TravelokaAdapter
Day 10: Mapping UI (Livewire)
Day 11: Channel mix reporting
Day 12: E2E test with sandbox
Day 13: Conflict resolution UI
Day 14: Documentation + runbook
```

---

## 17. Runbook (operasional)

### Sync stuck / lag
1. Check `channel_sync_logs` — apakah ada error pattern
2. Check queue health: `php artisan queue:monitor`
3. Restart worker: `sudo supervisorctl restart hotel-worker:*`
4. If specific OTA: temporarily disable credential to stop bleed, fix, re-enable

### Oversold detected
1. UI alert + email to manager
2. Manager option: rebook to higher room_type at same price (auto-suggested)
3. Or refund + apologize email
4. Audit log records resolution

### OTA migration / API version change
1. Update adapter class
2. Bump `channels.api_version`
3. Re-test sandbox
4. Roll out gradually (1 property pilot first)

---

## 18. Future Enhancements

- Real-time WebSocket dashboard untuk OTA sync status
- AI-powered rate suggestions per channel (revenue management)
- Automated rate parity monitoring + alert
- Channel-specific promo engine (e.g. flash sale Agoda only)
- B2B distribution (corporate travel agents direct API)
- GDS integration via Sabre / Amadeus connector
