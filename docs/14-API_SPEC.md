# 14 — API Spec

> Public REST/JSON API + webhook untuk integrasi pihak ketiga: kasir POS terpisah, channel manager external (kalau owner mau pakai SiteMinder/Yieldplanet), accounting export tools, custom website, mobile app.

Base: `https://{hotel-domain}/api/v1/...`

---

## 1. Prinsip

- **REST + JSON** — body & response selalu JSON.
- **Versioned** prefix `/api/v1/` — breaking change buka v2.
- **OAuth 2 client credentials** untuk machine-to-machine, **personal access token (PAT)** untuk integrator manual.
- **Idempotency-Key** header pada POST critical (booking, payment).
- **Rate limit** per token + per IP.
- **Webhook** outbound untuk realtime push.
- **OpenAPI 3.1 spec** di `/api/v1/openapi.json` + Swagger UI di `/api/v1/docs`.
- Multi-tenant: token sudah scoped ke property/tenant — TIDAK ada cross-tenant access.

---

## 2. Auth

### Personal Access Token (PAT)
Owner generate di admin UI: name, scope, expiry. Token displayed once, hashed di DB.

```
Authorization: Bearer hms_pat_xxxxxxxxxxxxxxxx
```

### OAuth 2 Client Credentials (P2)
Untuk app marketplace integrasi resmi.

```
POST /oauth/token
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials&client_id=...&client_secret=...&scope=reservations.read+ari.write
```

Response: `access_token`, `expires_in`, `token_type=Bearer`.

### Scopes

| Scope | Description |
|---|---|
| `reservations.read` | Read reservation list & detail |
| `reservations.write` | Create / modify reservation |
| `availability.read` | Read availability + rates |
| `ari.write` | Push ARI update |
| `folios.read` | Read folio + charges |
| `folios.write` | Post charges, payments |
| `guests.read` | Read guest profile |
| `guests.write` | Create / update guest |
| `housekeeping.write` | Update room status |
| `pos.write` | POS ticket post |
| `accounting.read` | GL, AR, AP read |
| `webhooks.manage` | Manage webhook subscriptions |
| `*` | Full access (admin only) |

---

## 3. Headers konvensi

| Header | Wajib | Catatan |
|---|---|---|
| `Authorization: Bearer ...` | ✅ | |
| `Accept: application/json` | ✅ | |
| `Content-Type: application/json` | ✅ POST/PUT | |
| `Idempotency-Key: <uuid>` | Disarankan | Wajib untuk POST yg create resource. Replay aman dalam 24 jam. |
| `X-API-Version: 2026-04-28` | Optional | Pinning behavior date-based |
| `X-Property-Id: 12` | Optional | Override default property (multi-property token) |

Response headers:
- `X-Request-Id` — trace UUID
- `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`
- `Deprecation: true` + `Sunset: <date>` saat endpoint legacy

---

## 4. Pagination

Cursor-based untuk list endpoint:

```
GET /api/v1/reservations?limit=50&cursor=eyJpZCI6MTAwfQ
```

Response:
```json
{
  "data": [...],
  "meta": {
    "next_cursor": "eyJpZCI6MTUwfQ",
    "has_more": true,
    "limit": 50
  }
}
```

Default limit 25, max 200.

---

## 5. Error format (RFC 7807-ish)

```json
{
  "error": {
    "type": "https://docs.hotelhub.id/errors/validation",
    "code": "VALIDATION_FAILED",
    "title": "Validation failed",
    "status": 422,
    "detail": "The check-in date must be before the check-out date.",
    "request_id": "req_01HXYZ...",
    "fields": {
      "check_in": ["The check_in field must be before check_out."]
    }
  }
}
```

Status code:
- `200/201/204` success
- `400` bad request shape
- `401` unauthenticated
- `403` insufficient scope
- `404` not found
- `409` conflict (e.g. overbooking)
- `422` validation
- `429` rate limited
- `500` internal
- `503` service unavailable (e.g. integration provider down)

---

## 6. Endpoints utama (Phase 1 / MVP)

### Properties

```
GET    /properties
GET    /properties/{id}
PATCH  /properties/{id}
```

### Room Types & Rooms

```
GET    /room-types
POST   /room-types
GET    /room-types/{id}
PATCH  /room-types/{id}

GET    /rooms
GET    /rooms/{id}
PATCH  /rooms/{id}/status   { "status":"clean|dirty|inspected|ooo" }
```

### Availability & Rates

```
GET /availability?check_in=2026-05-01&check_out=2026-05-04&adults=2
→ list room types with availability + rate breakdown

GET /rates?room_type_id=5&from=2026-05-01&to=2026-05-31
PATCH /rates/bulk
  { "room_type_id":5, "rate_plan_id":1, "from":"2026-05-01","to":"2026-05-04","amount":850000,"min_los":2,"closed":false }
```

### Reservations

```
POST /reservations
  body:
  {
    "check_in":"2026-05-01","check_out":"2026-05-04",
    "rooms":[{"room_type_id":5,"rate_plan_id":1,"adults":2,"children":0}],
    "primary_guest":{"first_name":"...","last_name":"...","email":"...","phone":"..."},
    "source":"direct|ota:booking|api",
    "promo_code": null,
    "addons":[{"code":"breakfast","qty":2}],
    "deposit":{"amount":500000,"method":"manual"},
    "notes":"...",
    "idempotency_key":"..."
  }
  → 201, returns {"id","ref","total","balance","folio_id","status":"confirmed"}

GET    /reservations
GET    /reservations/{id}
PATCH  /reservations/{id}
POST   /reservations/{id}/cancel    { "reason":"...", "refund":true }
POST   /reservations/{id}/check-in
POST   /reservations/{id}/check-out
POST   /reservations/{id}/no-show
POST   /reservations/{id}/move-room { "to_room_id":12 }
```

### Folio & Charges

```
GET   /folios/{id}
POST  /folios/{id}/charges
   { "description":"Late checkout","amount":150000,"account_id":..., "tax_code":"PB1" }
POST  /folios/{id}/payments
   { "amount":1500000,"method":"cash|card|qris|transfer","reference":"...","idempotency_key":"..." }
POST  /folios/{id}/transfer  { "to_folio_id":456, "amount":300000 }
POST  /folios/{id}/discount  { "amount":100000,"reason":"..." }
GET   /folios/{id}/invoice.pdf
```

### Guests

```
GET    /guests?q=john
GET    /guests/{id}
POST   /guests
PATCH  /guests/{id}
GET    /guests/{id}/stays
GET    /guests/{id}/folios
```

### Housekeeping

```
GET    /hk/rooms
POST   /hk/tasks   { "room_id":..,"type":"cleaning","assignee_id":.. }
PATCH  /hk/tasks/{id}/status   { "status":"started|done" }
```

### POS

```
POST   /pos/orders
   { "outlet_id":1,"table_id":3,"items":[{"menu_id":12,"qty":2,"modifiers":[...]}] }
PATCH  /pos/orders/{id}
POST   /pos/orders/{id}/settle
   { "method":"cash","amount":...,"split":[...] }
```

### Channel Manager (ARI sync)

```
PATCH  /ari/availability    { updates:[ {room_type_id, date, count} ] }
PATCH  /ari/rates           { updates:[ {rate_plan_id, room_type_id, date, amount} ] }
PATCH  /ari/restrictions    { updates:[ {rate_plan_id, room_type_id, date, min_los, max_los, cta, ctd, closed} ] }
GET    /channel/bookings    (delta sync — bookings dari OTA via channel manager)
```

### Accounting

```
GET   /coa
GET   /journal-entries?from=...&to=...
POST  /journal-entries (manual)
GET   /reports/daily-revenue?date=2026-05-01
GET   /reports/trial-balance?period=2026-05
GET   /reports/profit-loss?period=2026-05
GET   /ar/invoices
GET   /ap/bills
```

### Reports / Exports

```
GET /reports/occupancy?from=...&to=...
GET /reports/channel-production?from=...&to=...
GET /reports/source-of-business?from=...&to=...
GET /export/coa.csv
GET /export/journal.csv?period=2026-05
```

### Webhooks management

```
GET    /webhooks
POST   /webhooks
   { "url":"https://...", "events":["reservation.created","reservation.cancelled"], "secret":"..." }
PATCH  /webhooks/{id}
DELETE /webhooks/{id}
GET    /webhooks/{id}/deliveries  (failed retries history)
POST   /webhooks/{id}/test
```

---

## 7. Webhook (outbound)

Subscribed events:
- `reservation.created` / `.updated` / `.cancelled`
- `reservation.checked_in` / `.checked_out`
- `folio.charged` / `.settled`
- `payment.received` / `.failed` / `.refunded`
- `room.status_changed`
- `housekeeping.task_completed`
- `inventory.low_stock` (POS)
- `ari.synced` (per channel)
- `night_audit.closed`
- `journal.posted` (accounting)
- `review.received`
- `error.integration_failure`

Payload format:
```json
{
  "id": "evt_01HXYZ...",
  "type": "reservation.created",
  "created_at": "2026-04-28T10:15:00Z",
  "property_id": 12,
  "data": { ... full resource snapshot ... }
}
```

Signature: `X-HotelHub-Signature: t=...,v1=hmac_sha256(secret, payload)`. Receiver verify.

Retry policy: exponential backoff 5 attempts (1m, 5m, 30m, 2h, 12h). Setelah itu mark dead. UI di admin "Webhook deliveries" untuk replay manual.

---

## 8. Rate limit

| Tier | Burst (per 10s) | Sustained (per min) |
|---|---|---|
| Free / trial | 30 | 100 |
| Standalone Standard | 60 | 600 |
| Standalone Pro | 120 | 1200 |
| SaaS (per plan) | scaled | scaled |

Returns `429 Too Many Requests` dengan `Retry-After` header.

---

## 9. Idempotency

POST yang create (reservasi, payment, charge, journal) wajib accept `Idempotency-Key`. Server cache request hash + response 24 jam. Replay dengan key sama → return cached response (200/201) tanpa side-effect.

Tabel `api_idempotency_keys (key, hash, response_json, expires_at)`.

---

## 10. Pagination, sorting, filter konvensi

```
GET /reservations?
  status=confirmed,checked-in&
  check_in_from=2026-05-01&
  check_in_to=2026-05-31&
  source=direct,ota:booking&
  q=john&
  sort=-check_in&
  fields=id,ref,check_in,check_out,total&
  limit=50&cursor=...
```

- `sort=-field` untuk DESC, `+field`/`field` untuk ASC
- `fields=` untuk sparse fieldset
- Filter style: `field=val1,val2` (OR), repeat key untuk AND

---

## 11. Bulk operations

```
POST /bulk
[
  { "method":"PATCH","path":"/rates/bulk","body":{...} },
  { "method":"POST","path":"/reservations","body":{...} }
]

→ array of responses parallel/atomic (option flag)
```

Phase 2.

---

## 12. Versioning

Breaking change → buka `/api/v2/...`. Old version supported minimum 12 bulan. `Deprecation` & `Sunset` header diset.

---

## 13. OpenAPI

`/api/v1/openapi.json` auto-generated via Scribe atau L5-Swagger. Swagger UI di `/api/v1/docs` (admin-protected di production, public di staging).

---

## 14. Testing & sandbox

- `/api/v1/...` di tenant sandbox = data dummy + reset endpoint `POST /sandbox/reset`
- Production keys ≠ sandbox keys (prefix `hms_pat_test_` vs `hms_pat_live_`)
- Webhook simulator UI di admin: trigger event manual untuk debugging integrator

---

## 15. Audit log per request

Untuk endpoint write (POST/PATCH/DELETE):
- request_id, token_id, timestamp, method, path, body hash, response status
- retained 90 days (longer untuk financial endpoints)

Owner bisa lihat di admin UI "API audit".

---

## 16. Open questions

1. **GraphQL vs REST**? Default REST untuk MVP — GraphQL P3 kalau ada permintaan kuat dari developer.
2. **WebSocket public API** untuk realtime availability streaming? P2.
3. **MCP server** untuk AI agent integration (let AI book on behalf of guest)? P2 — interesting, exploratory.
4. **Server-Sent Events** untuk dashboard live update tanpa websocket complexity? Considered.
