# 16 — License Pairing v3 (Server-Side Design)

> Mekanisme proteksi & aktivasi license. Adopt dari project `whitelabel` v3 (proven). Mendukung dua mode: **standalone** (self-hosted, license one-time + maintenance) dan **SaaS** (license = subscription per tenant).

Goals:
- Hard-to-bypass aktivasi
- Survive offline (grace period)
- Easy revoke from vendor side
- Hardware/software fingerprint
- Tidak mengganggu UX hotel staff
- Audit trail full

---

## 1. Konsep dasar

| Term | Arti |
|---|---|
| **License key** | String yang dikasih ke owner saat beli. Format: `HMS-XXXXX-XXXXX-XXXXX-XXXXX` |
| **Pairing** | Proses pertama kali install: license key + device fingerprint → server verify → return signed token |
| **License token** | JWT signed by vendor private key, contains entitlement + expiry. Stored di `.env` / DB. |
| **Heartbeat** | Polling periodic ke vendor server untuk validate token + update last-seen |
| **Fingerprint** | Hash device-specific (machine UUID + OS + MAC + install path hash) |
| **Grace period** | Berapa hari aplikasi tetap jalan tanpa heartbeat sukses |

---

## 2. Architecture

```
┌─────────────────────────────────────┐
│   Vendor License Server             │
│   (admin panel + license API)       │
│   - License DB                      │
│   - Pairing endpoint                │
│   - Token signing (RSA private)     │
│   - Telemetry receive               │
│   - Revocation list                 │
└──────────────┬──────────────────────┘
               │ HTTPS, signed JWT
               │
┌──────────────▼──────────────────────┐
│   Customer Deployment (HMS)         │
│   - License middleware              │
│   - Token verify (RSA public)       │
│   - Pairing wizard (first-run)      │
│   - Heartbeat scheduler             │
│   - Grace mode logic                │
└─────────────────────────────────────┘
```

---

## 3. License lifecycle

### 3.1 Issuance (vendor side)

Sales generate license di admin:
- License key auto-generate (random, prefix `HMS-`)
- Plan, feature flags, max_rooms, max_users, valid_from, valid_until
- Status `unpaired`
- Send key + activation guide ke owner via email

### 3.2 Pairing (first install)

Customer install HMS, akses `/setup/wizard`:

1. Wizard step 1: input license key
2. Wizard step 2: app generate fingerprint (hash machine UUID + OS + MAC + install path)
3. Wizard call `POST {vendor}/api/license/pair`:
   ```json
   {
     "license_key":"HMS-XXXXX-...",
     "fingerprint":"sha256_hash...",
     "machine_info":{"hostname":"...","os":"Ubuntu 22.04","app_version":"1.0.0"},
     "install_id":"uuid"
   }
   ```
4. Server side:
   - Lookup license by key
   - Check status (must be `unpaired` atau `paired_pending_revoke`)
   - Check valid period
   - Bind license ↔ fingerprint, set status `paired`
   - Generate JWT token signed by RSA-2048 private:
     ```json
     {
       "sub": "license_id",
       "iss": "hotelhub-vendor",
       "iat": ...,
       "exp": +30days,
       "license_key_hash": "...",
       "fingerprint": "...",
       "plan": "standalone-pro",
       "features": {...},
       "max_rooms": 100,
       "max_users": 30,
       "valid_until": "2027-04-28"
     }
     ```
   - Return token to client
5. Wizard store token di `licenses` tabel local + write `.env`:
   - `LICENSE_TOKEN=...`
   - `LICENSE_FINGERPRINT=...`
   - `LICENSE_PAIRED_AT=...`

### 3.3 Runtime (every request)

Middleware `EnsureLicenseValid`:
- Decode JWT, verify signature dengan RSA public key (dibundel di app)
- Check `exp` not passed
- Check `fingerprint` cocok dengan current device
- Check feature flag yang akses route — kalau tidak punya, 403 with upgrade prompt
- Check max_rooms / max_users usage — kalau over, soft warn + read-only mode
- Cache result 5 menit (in-memory) untuk perf

### 3.4 Heartbeat (daily)

Cron `license:heartbeat` runs every 24 jam:

```
POST {vendor}/api/license/heartbeat
{
  "license_token": "...",
  "fingerprint": "...",
  "telemetry": {"rooms":..., "users":..., "uptime":..., "version":...}
}
```

Server:
- Verify token
- Check revocation list
- Update `licenses.last_heartbeat_at`
- Return `{ "valid":true, "renewed_token": "..." }` (server reissue JWT dengan exp +30 days)

Client:
- Replace local token kalau renewed
- Reset `last_heartbeat_success_at`

### 3.5 Grace mode (offline tolerance)

Kalau heartbeat fail (network, vendor server down):
- 0-7 days: silent retry
- 7-14 days: banner warning "License heartbeat overdue"
- 14-30 days: banner urgent + email owner
- >30 days: enter degraded mode — read-only (booking engine + reports work, edit blocked)
- >60 days (configurable): hard lock, require re-pair

Konfigurable per-license via `grace_days` field (default 30).

### 3.6 Revocation

Vendor admin di-trigger:
- Set `licenses.status = 'revoked'`, `revoked_at = now`
- Add to revocation list (cached at edge, signed)
- Next heartbeat client receives `{ "valid":false, "reason":"revoked", "message":"..." }`
- Client enters hard lock immediately
- Optional: untuk fraud / abuse, vendor push instan invalidation via webhook ke client (kalau client expose `/license/revoke-callback` endpoint)

### 3.7 Re-pairing (device migration)

Owner moving server (e.g. VPS migration, hardware replacement):
- Wizard "Move installation" — input license key + reason
- Vendor admin approve OR auto-approve kalau within policy (e.g. 2 re-pair per year free, more = paid support)
- Old fingerprint marked superseded
- New fingerprint paired
- Old token invalidated

### 3.8 Renewal

Sebelum `valid_until`:
- Vendor send invoice email D-30, D-14, D-7
- Owner bayar → vendor extend `valid_until`
- Next heartbeat → renewed token dengan exp baru
- Kalau lewat `valid_until` tanpa bayar: enter grace 30 days → hard lock

---

## 4. Schema

### Vendor side (license server DB)

```
licenses
├── id
├── license_key (unique, indexed)
├── owner_email, owner_name, company
├── plan (e.g. standalone-pro, saas-tier-2)
├── features (json — feature flags)
├── max_rooms, max_users, max_properties
├── valid_from, valid_until
├── status ENUM('unpaired','paired','grace','suspended','revoked','expired')
├── grace_days
├── current_fingerprint
├── current_install_id
├── paired_at
├── last_heartbeat_at
├── revoked_at, revoked_reason
├── created_by (admin user)
├── price_paid, currency
└── timestamps

license_pairings (history)
├── id, license_id
├── fingerprint, install_id, machine_info_json
├── paired_at, unpaired_at
├── unpaired_reason
└── timestamps

license_heartbeats (rolling — auto-prune > 90d)
├── id, license_id
├── received_at, telemetry_json
├── source_ip
└── timestamps

license_revocation_list
├── id, license_id
├── revoked_at
├── reason
├── propagated_at (saat client confirm receive)
└── timestamps
```

### Customer side (local DB)

```
local_licenses (singleton — only 1 row)
├── id (=1)
├── license_key (masked display, full hashed)
├── token (current valid JWT)
├── fingerprint
├── install_id (UUID generated saat pertama)
├── paired_at
├── last_heartbeat_success_at
├── last_heartbeat_attempt_at
├── grace_until (computed)
├── status (paired|grace|degraded|locked)
├── degrade_reason
└── timestamps
```

---

## 5. Token format (JWT)

Algorithm: **RS256** (RSA-2048).

Public key bundled di app source: `config/license/vendor-public.pem`.
Private key di vendor server only, encrypted at rest.

Claims:
```json
{
  "iss": "hotelhub-vendor",
  "sub": "license:1234",
  "aud": "hotelhub-app",
  "iat": 1714281600,
  "exp": 1716873600,
  "nbf": 1714281600,
  "jti": "uuid-token-id",
  "license": {
    "key_hash": "sha256:...",
    "plan": "standalone-pro",
    "features": {
      "channel_manager": true,
      "ai_demand_forecast": false,
      "marketplace_addons": false,
      "white_label": false,
      "max_concurrent_users": 30,
      "max_rooms": 100,
      "max_properties": 1
    },
    "valid_until": "2027-04-28T00:00:00Z",
    "grace_days": 30
  },
  "fingerprint": "sha256:..."
}
```

Token rotation: setiap heartbeat sukses → server reissue dengan `exp = now + 30d`. Client always carry fresh-ish token. Stale token (no heartbeat 30d) → grace logic kicks in.

---

## 6. Anti-tampering

### Public key integrity
- Public key file hash di-check at boot vs hardcoded constant
- Kalau hash mismatch → app refuse to start, log critical
- Hardcoded constant di-obfuscate sedikit (split string + concat di runtime)

### License middleware bypass
- License middleware terdaftar di kernel, kalau dihilangkan via edit code, sanity check di service provider boot detect missing middleware → fail
- Multiple sanity check di route layer + service layer + scheduler

### Database swap
- Token bound ke `install_id` — swap DB ke license lain → fingerprint mismatch detect

### Reverse engineering / re-package
- Code base bukan obfuscated (open enough untuk owner audit), tapi license check berlapis
- Kontrak EULA + watermark per-license: setiap PDF (folio, invoice) berisi watermark license_id (terbaca di metadata) untuk forensik kalau ada redistribusi

### Network bypass
- Heartbeat fail mode default = grace, tapi attempt count tracked
- Kalau client suddenly stop heartbeat (last seen 7 days, kemudian come back without explanation) → server flag review
- Optional: hardware token (Yubikey-style USB dongle) for enterprise tier — Phase 3

---

## 7. Pairing API endpoints (vendor server)

```
POST /api/license/pair
  body: license_key, fingerprint, machine_info, install_id
  → 200 { token, license_id, paired_at }
  → 409 { error: "license already paired to different fingerprint" }
  → 410 { error: "license expired" }
  → 403 { error: "license revoked" }

POST /api/license/heartbeat
  body: token, fingerprint, telemetry
  → 200 { valid:true, renewed_token, valid_until }
  → 403 { valid:false, reason:"revoked|expired|fingerprint_mismatch" }

POST /api/license/migrate
  body: license_key, old_fingerprint, new_fingerprint, machine_info, reason
  → 200 { token, migrated_at }
  → 429 { error: "migration limit reached, contact support" }

POST /api/license/unpair
  body: token, reason
  → 200 { unpaired:true }

GET /api/license/status?key=...  (auth: vendor admin)
  → 200 { license info + history }
```

---

## 8. Failure modes & UX

| Scenario | UX |
|---|---|
| Pairing wizard, key invalid | Inline error "License key not recognized." |
| Pairing wizard, network fail | "Cannot reach license server. Check connection." + retry button |
| Pairing wizard, key expired | "This license is expired. Contact sales to renew." |
| First heartbeat fails after pair | Silent retry, banner "Last heartbeat: ..." |
| Heartbeat 7+ days fail | Yellow banner "License heartbeat overdue. Check internet." |
| Heartbeat 14+ days fail | Red banner + email owner |
| Grace passed → degraded | Modal block edit ops, allow read + reports |
| Hard lock | Login screen replaced with "License inactive — contact admin" + revival button |
| Revoked | Same as hard lock + reason displayed |
| Fingerprint mismatch | Modal "Hardware change detected. Re-pair?" wizard |

---

## 9. SaaS mode adaptation

Di SaaS mode (Phase 2):
- License = central tenant subscription
- Per-tenant DB punya `local_licenses` row tapi server-validated otomatis
- Revoke license = suspend tenant
- Grace logic identik
- Tidak ada wizard pairing di tenant — vendor side auto-pair saat tenant create

---

## 10. Vendor admin views (singkat)

- License list: filter status, owner, plan, expiry
- Detail: histroy pairing, heartbeat trend, telemetry
- Action: extend, revoke, migrate, regenerate token, send reminder
- Bulk: import (CSV), export, mass-extend (e.g. promo)

Lihat 11-ADMIN_PANEL.md untuk lebih lengkap.

---

## 11. Privacy of telemetry

- Telemetry hanya kirim aggregate counts (rooms, users, version, uptime, error count)
- TIDAK kirim PII tamu, content reservation, financial detail
- Owner aplikasi declared di privacy notice + EULA bahwa telemetry dikirim
- Owner bisa opt-out (di Phase 2): paying customer dapat opsi minimal-telemetry, tapi heartbeat tetap wajib

---

## 12. Open questions

1. **JWT vs PASETO** — JWT mature ecosystem; PASETO lebih aman default. Default JWT-RS256.
2. **Bagaimana kalau vendor server down lama** — grace 30d cover most cases; multi-region failover server di Phase 2.
3. **Floating license** (1 key, 5 device random) — niche, P3.
4. **Offline-only deployment** (hotel di pulau remote tanpa internet kontinu) — license bisa pair online sekali, kemudian fully offline dengan extended grace (e.g. 365 days), opsional add-on premium.
