# 06 — Integrations

> Semua integrasi pihak ketiga BYOK (Bring-Your-Own-Key). Class adapter format-based, bukan vendor-named. Owner input semua sendiri di admin UI.

---

## 1. Categories

| Category | Examples (preset autofill) | Adapter strategy |
|---|---|---|
| Payment Gateway | Midtrans, Xendit, DOKU, iPaymu, Faspay, Stripe, custom | Format-based (redirect / embed / qris / direct) |
| OTA Channel | Booking.com, Agoda, Traveloka, Tiket.com, Mister Aladin, Pegipegi, Expedia, Airbnb, Trip.com | Vendor-specific (API spec sangat unik) |
| AI / LLM | DeepSeek, Mistral, Gemini, Anthropic, dll. | Format-based (openai_compat / anthropic / gemini) — lihat [05-AI_PROVIDERS.md](05-AI_PROVIDERS.md) |
| SMS Gateway | Twilio, Vonage, Zenziva, Reswara, Hubtic, custom | Format-based (rest / smpp) |
| WhatsApp Business | Meta Cloud API, Twilio, Wati, custom | Format-based (cloud / 360dialog / on-prem) |
| Mail SMTP/API | Mailgun, Resend, Postmark, SendGrid, SES, Brevo, Mailtrap, generic SMTP | Format-based (smtp / api) |
| Door Lock | Salto, Onity, Vingcard, Dormakaba, MIWA | Vendor-specific |
| Storage | S3, Cloudflare R2, Wasabi, Backblaze B2, MinIO | S3-compatible single adapter |
| Captcha | Cloudflare Turnstile, hCaptcha, reCAPTCHA | Format-based |
| Rate Shopper | OTA Insight, RateGain, custom | Format-based |
| Coretax (e-Faktur) | DJP Coretax | Single direct (compliance) |

---

## 2. Universal Provider Schema

```sql
providers
├── id
├── integration_type   ENUM('ai','payment','sms','whatsapp','mail',
│                            'door_lock','rate_shopper','ota','storage',
│                            'captcha','accounting_export','other')
├── name               (user input, e.g. "Midtrans Production")
├── slug               (auto-generated)
├── api_format         (e.g. "redirect_flow", "openai_compatible", "smtp")
├── base_url
├── api_key_encrypted  (Crypt::encryptString)
├── secret_encrypted
├── extra_headers      JSON
├── extra_config       JSON  (e.g. webhook_url, currency, region)
├── default_model      (untuk AI)
├── capabilities       JSON  (e.g. ['supports_qris', 'supports_recurring'])
├── is_active
├── is_default
├── display_order
├── test_status
├── last_tested_at
├── notes
└── timestamps
```

Per-feature mapping di tabel `provider_features`:
```
feature_key (e.g. 'payment.qris', 'ai.concierge') → primary_provider_id, fallback_provider_id
```

---

## 3. Payment Gateway

### Format groups (adapter purely by format, not vendor)

| Format | Behavior | Provider yang fit |
|---|---|---|
| `redirect_flow` | User di-redirect ke halaman PG, return ke callback URL | Midtrans Snap, Xendit Invoice, DOKU Checkout, iPaymu |
| `embed_flow` | Form card embedded di iframe / SDK JS | Midtrans Core API, Xendit Direct, Stripe Elements |
| `qris_dynamic` | Generate QR code dynamic, polling status | Midtrans QRIS, Xendit QRIS, DOKU QRIS, semua acquirer QRIS Indonesia |
| `va_static` | Generate Virtual Account number, polling | BCA VA, Mandiri VA, BNI VA, BRI VA, Permata VA |
| `direct_charge` | API charge langsung dengan token | Midtrans Tokenization, Stripe Charges |
| `e_wallet_redirect` | Deep-link to GoPay, OVO, Dana, ShopeePay, LinkAja apps | Midtrans/Xendit e-wallet endpoints |

### Adapter interface

```php
interface PaymentAdapterInterface
{
    public function createPayment(PaymentRequest $req): PaymentResult;
    public function pollStatus(string $referenceId): PaymentStatus;
    public function refund(string $paymentId, Money $amount): RefundResult;
    public function verifyWebhook(string $signature, string $body): bool;
    public function parseWebhook(string $body): WebhookPayload;
}
```

### Preset JSON example (Midtrans)

```json
{
  "preset_id": "midtrans-snap",
  "display_name": "Midtrans (Snap)",
  "api_format": "redirect_flow",
  "base_url_production": "https://app.midtrans.com/snap/v1",
  "base_url_sandbox": "https://app.sandbox.midtrans.com/snap/v1",
  "auth_method": "basic_auth",
  "auth_user_field": "server_key",
  "supported_methods": ["credit_card","va","gopay","qris","shopeepay","permata_va","echannel","akulaku","kredivo"],
  "webhook_signature_method": "sha512_concat",
  "default_currency": "IDR",
  "fee_structure_url": "https://midtrans.com/pricing",
  "docs_url": "https://docs.midtrans.com",
  "indonesia_friendly": true
}
```

### Webhook handler

```
POST /webhook/payment/{provider_slug}
    ↓
Signature verify (HMAC sesuai api_format)
    ↓
parseWebhook → PaymentEvent
    ↓
Match payment by gateway_transaction_id
    ↓
Update status, fire PaymentReceived event
    ↓
Listener: post folio, send receipt, audit log
```

---

## 4. OTA Channel Manager

OTA spec API per-vendor sangat unik; tidak fit format-based pure. Kecuali konsep umum (rate, inventory, restrictions, bookings).

Pattern: `ChannelAdapterInterface` umum, implementation per OTA.

```php
interface ChannelAdapterInterface
{
    public function pushAri(PropertyId $p, DateRange $r, AriPayload $payload): SyncResult;
    public function pullBookings(PropertyId $p, DateRange $r): array;
    public function ackBooking(string $externalId): bool;
    public function pushRoomMapping(array $mapping): bool;
    public function getAvailableExternalRooms(PropertyId $p): array;
    public function verifyWebhook(string $signature, string $body): bool;
}
```

Vendor-specific:
- `BookingComAdapter` — XML push/pull, daily reservation report
- `AgodaAdapter` — YCS (Yield Control System) API
- `TravelokaAdapter` — Traveloka Hotels API
- `TiketComAdapter` — Direct API (REST JSON)
- `MisterAladinAdapter`, `PegipegiAdapter`, `ExpediaAdapter`, `AirbnbAdapter`, `TripComAdapter`

Detail: [`07-CHANNEL_MANAGER.md`](07-CHANNEL_MANAGER.md).

---

## 5. SMS Gateway

### Format groups

| Format | Provider |
|---|---|
| `rest_simple` | Twilio, Vonage, Zenziva, Hubtic, Reswara |
| `smpp` | Telkomsel SMPP, custom carrier (Phase 3) |

### Adapter

```php
interface SmsAdapterInterface
{
    public function send(string $to, string $message, array $options = []): SmsResult;
    public function status(string $messageId): SmsStatus;
    public function balance(): ?float;
}
```

### Preset Zenziva
```json
{
  "preset_id": "zenziva",
  "display_name": "Zenziva",
  "api_format": "rest_simple",
  "base_url": "https://api.zenziva.net/v1",
  "auth_method": "basic",
  "send_endpoint": "/sms/send",
  "indonesia_friendly": true
}
```

---

## 6. WhatsApp Business

### Format groups

| Format | Provider |
|---|---|
| `meta_cloud_api` | Meta Cloud API (gratis tier, official) |
| `twilio_whatsapp` | Twilio |
| `360dialog` | 360dialog |
| `wati` | Wati (Indonesia partner) |
| `qontak` | Qontak |
| `unofficial_baileys` (Phase 3, hati-hati TOS) | self-host node baileys |

### Adapter

```php
interface WhatsAppAdapterInterface
{
    public function sendText(string $to, string $message): WhatsAppResult;
    public function sendTemplate(string $to, string $templateName, array $params, string $locale = 'id'): WhatsAppResult;
    public function sendMedia(string $to, string $mediaUrl, string $caption = '', string $type = 'image'): WhatsAppResult;
    public function setWebhookUrl(string $url): bool;
    public function verifyWebhook(string $signature, string $body): bool;
}
```

---

## 7. Mail (SMTP / API)

### Format groups

| Format | Provider |
|---|---|
| `smtp_generic` | Generic SMTP (Gmail, custom server) |
| `mailgun_api` | Mailgun |
| `resend_api` | Resend |
| `postmark_api` | Postmark |
| `sendgrid_api` | SendGrid |
| `ses_api` | Amazon SES |

Laravel native sudah support semua via `MAIL_MAILER` driver. Provider table jadi UI layer wrapper untuk env var management dynamic.

---

## 8. Door Lock (Phase 2)

Vendor-specific (API beda jauh):

| Vendor | Notes |
|---|---|
| **Salto** | RESTful API, popular global, Bali populer |
| **Onity** | Older RFID, biasanya batch sync |
| **Vingcard / Assa Abloy** | Hospitality-focused |
| **Dormakaba** | Mid-range hotels |
| **MIWA** | Jepang, Bali resort populer |

Adapter pattern:
```php
interface DoorLockAdapterInterface
{
    public function generateKey(string $reservationId, string $roomNumber, DateRange $validity): KeyResult;
    public function revokeKey(string $keyId): bool;
    public function listActiveKeys(string $roomNumber): array;
}
```

---

## 9. Storage (S3-compatible)

Single adapter `S3CompatibleAdapter`. Cover:
- Amazon S3
- Cloudflare R2 (cheapest egress: $0)
- Wasabi
- Backblaze B2
- MinIO (self-host)
- DigitalOcean Spaces

Config:
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=auto
AWS_BUCKET=hotel
AWS_ENDPOINT=https://...r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```

Owner pilih provider, paste credentials, app jalan tanpa code change.

---

## 10. Captcha

| Provider | Adapter format |
|---|---|
| Cloudflare Turnstile | `turnstile` |
| hCaptcha | `hcaptcha` |
| Google reCAPTCHA v3 | `recaptcha_v3` |

Booking engine guest-facing pakai Turnstile by default (privacy-friendly, gratis).

---

## 11. Rate Shopper (Phase 2)

```php
interface RateShopperAdapterInterface
{
    public function getCompetitorRates(PropertyId $p, DateRange $range, array $compSetIds): array;
}
```

Vendor: OTA Insight, RateGain, atau scraping internal (legal grey area, opsional Phase 3).

---

## 12. Coretax (e-Faktur DJP)

Single direct integration (gak BYOK karena vendor cuma satu: DJP Indonesia). Detail di [`08-INDONESIA_COMPLIANCE.md`](08-INDONESIA_COMPLIANCE.md).

```php
class CoretaxClient
{
    public function getAccessToken(): string;
    public function uploadFakturXml(string $xml): CoretaxResult;
    public function getFakturStatus(string $nsfp): array;
    public function cancelFaktur(string $nsfp, string $reason): bool;
}
```

Auth flow: OAuth2 dengan certificate-based signing.

---

## 13. Webhook Hub (incoming)

Universal route group `/webhook/{type}/{provider_slug}` → resolve ke handler.

```
POST /webhook/payment/midtrans-snap   → PaymentWebhookController
POST /webhook/payment/xendit          → PaymentWebhookController
POST /webhook/ota/booking-com         → OtaWebhookController
POST /webhook/ota/agoda               → OtaWebhookController
POST /webhook/whatsapp/meta-cloud     → WhatsAppWebhookController
POST /webhook/sms/twilio              → SmsWebhookController
POST /webhook/license/heartbeat       → LicenseWebhookController (server-side)
```

All webhook → log to `webhook_logs` BEFORE processing. Idempotency by `event_id` if provider provides.

---

## 14. Outgoing Webhooks (Phase 2)

Owner / integrator subscribe ke event hotel:

```
POST {customer_endpoint}
Headers:
  X-Hotel-Event: reservation.created
  X-Hotel-Signature: hmac-sha256(payload, secret)
  X-Hotel-Idempotency-Key: uuid
Body: { "event": "...", "data": {...}, "occurred_at": "..." }
```

Events: `reservation.created`, `reservation.cancelled`, `reservation.checked_in`, `reservation.checked_out`, `payment.received`, `folio.closed`, `room.status_changed`.

Retry: exponential backoff 5x (1s, 5s, 30s, 5min, 1h). Failed → dead-letter UI.

---

## 15. Connection Test Endpoint

Setiap provider punya **Test Connection** button di admin.

| Type | Test action |
|---|---|
| AI | `GET /models` (or equivalent) |
| Payment | Create Rp 1,000 sandbox transaction → cancel |
| OTA | Pull last 7 days bookings (read-only) |
| SMS | Get balance |
| WhatsApp | Send template "test" to admin's number |
| Mail | Send test email to admin |
| Storage | Upload + delete 1KB test file |
| Door Lock | List active keys |
| Captcha | Validate dummy token (expects fail) |

Hasil masuk `providers.test_status` + `providers.last_tested_at` + audit log.

---

## 16. BYOK Setup Wizard (admin UI)

```
Admin → Integrations
    ↓
[Payment Gateway] tab
    ↓
[ + Add Provider ]
    ↓
Step 1: Pick category & preset (or custom)
Step 2: Fill credentials (encrypted on save)
Step 3: Test Connection
Step 4: Map to features
        ┌────────────────────────────────────┐
        │ Payment Gateway: Midtrans (Snap) v │
        │                                    │
        │ Map to features:                   │
        │   ☑ Booking Engine - Direct        │
        │   ☑ Folio Payment                  │
        │   ☑ Banquet Deposit                │
        │   ☐ POS Walk-in (use cash only)    │
        │                                    │
        │ Webhook URL: (auto-generated)      │
        │ https://hotel.com/webhook/         │
        │   payment/midtrans-snap-1          │
        │ [ Copy → ]                         │
        └────────────────────────────────────┘
```

---

## 17. Failover & Resilience

- Per provider: circuit breaker after 3x consecutive failures (60s open)
- Multiple providers per category: primary + fallback
- Dashboard alert kalau provider failure rate > 5% in last hour
- Webhook delivery: retry 5x exponential
- All external calls: max timeout 30s (longer for AI streaming up to 120s)

---

## 18. Logs & Observability per Integration

Tabel `integration_logs`:
```
id, provider_id, integration_type, action, status, latency_ms, error_message,
request_summary, response_summary (PII-masked), created_at,
INDEX(provider_id, created_at)
```

Owner buka dashboard → Integration Health → grafik success rate per provider, P50/P95/P99 latency.

---

## 19. Encryption Key Rotation

Setiap kuartal owner bisa rotate `APP_KEY`:
```bash
php artisan provider:rotate-keys --new-key=base64:xxxxxx
```
Service:
1. Decrypt all `*_encrypted` columns dengan old key
2. Re-encrypt dengan new key
3. Atomic update transaction

Audit log: `provider.key_rotated`.

---

## 20. Compliance dengan Global Rule

Sesuai [`global preferences`](file:///C:/Users/lindu%20cipta/.claude/CLAUDE.md):

✅ Tidak ada hardcoded vendor name di code
✅ Tidak ada hardcoded API key
✅ Tidak ada hardcoded base URL
✅ Tidak ada hardcoded model ID
✅ Tidak ada hardcoded "default per fitur"
✅ Format-based adapter
✅ Encrypted at rest, never logged
✅ Auto-fetch convenience tetap ada (test connection, list models)
✅ Preset hanya di JSON file untuk autofill UI — code TIDAK reference saat runtime

Reference implementation: [foodscan/docs/10-AI-PROVIDERS.md](file:///D:/project%20laravel/foodscan/docs/10-AI-PROVIDERS.md).
