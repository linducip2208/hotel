# 15 — Admin & Application Security

> Defense-in-depth across auth, authorization, secrets, audit, integrity. Both admin (vendor) panel dan user (hotel) panel covered.

---

## 1. Authentication

### Password policy

- Minimum 10 karakter
- Mix: minimal 1 huruf, 1 angka, 1 simbol
- Disallow last 5 password (history)
- Force change setelah 90 days (admin role; opsional untuk user role — owner toggle)
- Lockout: 5 gagal dalam 15 menit → kunci akun 15 menit + alert email + notify admin

### 2FA / MFA

- TOTP (Google Authenticator, Authy, dll) — wajib untuk admin & owner & accountant role
- Optional: WebAuthn / passkey (Phase 2)
- Recovery codes 10 buah, single-use, di-encrypt at rest
- Force enrollment saat first login untuk role wajib

### Session

- Stateful session (database driver) untuk panel
- Idle timeout: 30 menit (config per property)
- Absolute timeout: 12 jam
- Single session per user opsional (owner toggle "kick previous session")
- Session fingerprint: bind ke User-Agent + IP class — kalau berubah drastis, force re-auth

### Login surface protection

- Rate limit 10 attempt per IP per menit di endpoint `/login`
- Captcha (BYOK Turnstile/hCaptcha/reCAPTCHA) setelah 3 gagal
- Honeypot field
- Generic error message ("Invalid credentials") — tidak ungkap apakah email exist
- Login activity log: timestamp, IP, UA, lokasi (GeoIP), success/fail — visible di profil user

---

## 2. Authorization (RBAC)

### Layered

1. **Authentication** — siapa kamu? (login)
2. **Tenancy guard** — kamu ngakses tenant yang benar?
3. **Property scope** — kamu ngakses property yang punya akses?
4. **Role permission** — kamu boleh lakukan action ini?
5. **Resource ownership** — kamu owner of resource (kalau applicable, e.g. cashier hanya bisa edit folio shift-nya sendiri)?

### Implementation

- Laravel **Gate** + **Policy** per resource (`ReservationPolicy`, `FolioPolicy`, dll)
- Middleware `permission:fo.reservation.create`
- Resource ownership check di policy method (`$user->id === $folio->cashier_id`)

### Threshold-gated actions

Beberapa action butuh approval (di atas threshold):
- Discount > 20% folio
- Refund > Rp 5jt
- AP payment > Rp 50jt
- Period unlock (accounting)
- Comp room (free of charge)

Workflow: requester submit → manager approve / reject → action executed. Logged di `approval_requests`.

---

## 3. Secret management

### App-level secrets (di `.env`)

- `APP_KEY` (Laravel encryption key)
- DB credentials
- Redis password
- Mail SMTP
- SaaS-mode central JWT signing key
- Telemetry endpoint key

### Tenant-managed secrets (BYOK)

- Payment gateway keys
- AI provider keys
- SMS / WA / Mail provider keys
- OTA channel manager keys
- Coretax PKP credentials

**Storage:**
- Disimpan di kolom `*_encrypted` via `Crypt::encryptString()` (AES-256-CBC w/ HMAC).
- Decrypt hanya at usage point (di service yang execute call).
- **Never logged** — masking middleware di logger: redact key/secret/token.
- **Never returned via API** — response selalu masked `sk_****abcd`.
- Rotasi: UI "regenerate" untuk per-secret.

### Code-level secret hygiene

- Pre-commit hook: `gitleaks` / `trufflehog` scan
- CI scan: secret detection
- Dependabot / Renovate untuk vuln deps

---

## 4. Audit log

### Model

```
audit_logs
├── id, property_id (nullable for global events)
├── user_id, user_type (admin|staff|api)
├── action (string e.g. 'reservation.created', 'user.login', 'secret.rotated')
├── auditable_type, auditable_id (polymorphic)
├── before (json, nullable)
├── after (json, nullable)
├── ip, user_agent, request_id
├── metadata (json — extra context)
└── created_at
```

### What's logged (mandatory)

- All CRUD on financial entities (folio, journal, payment, AR/AP, COA edit, refund)
- Permission changes, role create/edit
- Secret rotate / view-attempt / decrypt event
- Period close / unlock
- User login / logout / failed login / password reset / 2FA enroll-disable
- Data export (PII downloads)
- Impersonation by admin (vendor side)
- License pairing / revoke
- Setting changes (tax config, integration toggle)

### Retention

- Financial: minimum 10 tahun (UU Pajak)
- Auth / access: 2 tahun
- Other: 1 tahun
- Archive ke cold storage setelah 90 hari (S3 Glacier / R2 archive)

### Tamper-evidence

- Append-only table (no DELETE permission)
- Optional hash chain: `entry.hash = sha256(prev_hash + entry_payload)` — detect tampering (P2)
- Daily checksum exported ke external storage (S3 versioned bucket)

---

## 5. Data protection (UU PDP)

Lihat juga `08-INDONESIA_COMPLIANCE.md` untuk full PDP coverage.

### Tagging PII

Per kolom DB di-tag PII level via comment / migration metadata:
- L1: nama, email, telp (basic)
- L2: KTP, paspor, alamat (sensitive)
- L3: foto identitas, signature digital (highly sensitive)

### Access control

- Role-based: hanya FO + Manager + Owner bisa lihat L2; Manager + Owner bisa export
- Audit: setiap akses L2/L3 logged
- Mask di list view (e.g. KTP `3201****1234`), full view butuh klik "show" + reason input

### Right to erasure

Endpoint admin "Forget guest":
- Anonimisasi (replace name/email/phone with hash) di semua tabel
- Tetap simpan reservation history (untuk pajak compliance) tapi dengan PII anonimised
- Catat di `dsr_requests` (data subject rights — request ID, type, completed_at, performed_by)

### Data export (right to portability)

Endpoint "Download my data" → ZIP berisi JSON profile + reservation history + folio.

### Breach notification

- Internal incident response runbook
- Notify Komdigi (≤ 3 × 24 jam) sesuai UU PDP
- Notify affected guests
- Logged di `security_incidents` (with severity, scope, mitigation, post-mortem)

---

## 6. Network / infra security

- HTTPS only — HSTS preload (max-age=63072000; includeSubDomains; preload)
- TLS 1.2 minimum, prefer 1.3
- WAF: Cloudflare (recommended) atau ModSecurity self-host
- DDoS: Cloudflare proxy
- Admin panel di subdomain terpisah + IP whitelist guard
- DB tidak expose ke public
- Backup encrypted at rest
- VPN / Bastion untuk SSH admin (jangan password auth — key only)
- Auto-update OS (unattended-upgrades) di VPS yang kita kelola untuk SaaS

---

## 7. CSRF, XSS, SQLi, SSRF

- CSRF: Laravel built-in (`@csrf` di form, middleware verify)
- XSS: Blade auto-escape; `@unsafe` rare, code review wajib
- CSP header: `default-src 'self'; img-src 'self' data: cdn.example.com; ...`
- SQLi: Eloquent / Query Builder — no raw concat user input
- SSRF: outbound HTTP via wrapper class yang validate destination (no `localhost`, no `169.254.*`, no internal CIDR) untuk fitur webhook / image fetch
- File upload: MIME sniff, virus scan (ClamAV optional Phase 2), randomized filename, store outside webroot
- Image processing: `intervention/image` — guard dimensi max + pixel bomb protection

---

## 8. Dependency security

- Composer audit di CI (`composer audit`)
- npm audit di CI
- Dependabot / Renovate
- Lock pnpm/composer files committed
- Major bump butuh PR review + smoke test

---

## 9. Encryption at rest

- DB-level: enable encrypted volume (LUKS / cloud provider native)
- Field-level encryption untuk PII L2/L3 (via Laravel Crypt or Eloquent caster)
- Backup files encrypted before push ke S3/R2

---

## 10. License & integrity check

Aplikasi self-check:
- License token verify di tiap request (license middleware)
- File integrity check (hash major core files) — kalau modified manual, log warning + degrade mode
- Tampering detection: `LICENSE.txt` integrity hashed, check di boot

Detail di 16-LICENSE_PAIRING_DESIGN.md.

---

## 11. Penetration testing

Sebelum public launch:
- Internal pentest checklist OWASP Top 10
- External vendor pentest (third-party) sebelum SaaS general availability
- Bug bounty program post-launch (Phase 2): private invite-only via HackerOne / local researcher

---

## 12. Compliance certifications (target jangka panjang)

- ISO/IEC 27001 (P3) — for enterprise SaaS sales
- SOC 2 Type 1 (P3)
- PCI-DSS — kita tidak handle card data langsung (offload ke PG), tapi compliance tetap dengan SAQ-A
- Bukti UU PDP self-attestation + DPIA dokumen

---

## 13. Incident response

Runbook urutan saat insiden:

1. **Detect** — alert from monitoring / user report
2. **Classify** — severity 1-4
3. **Contain** — isolate affected system, rotate compromised secrets
4. **Eradicate** — patch root cause
5. **Recover** — restore service, verify integrity
6. **Postmortem** — blameless writeup, action items, timeline
7. **Notify** — affected parties, regulator (kalau PDP scope)

On-call rotation untuk SaaS mode (P2 saat ada ≥3 paying tenants).

---

## 14. Threat model summary

| Threat | Mitigation |
|---|---|
| Stolen staff credential | 2FA + lockout + alert |
| Insider exfil financial data | Audit log + tamper detection + export approval |
| OTA channel injection (fake booking) | Webhook signature verify, manual review high-value bookings |
| Card fraud via direct booking | PG fraud module + Captcha + velocity checks |
| Ransomware on hotel server | Encrypted backup + offsite (S3) + restore drill quarterly |
| API key leak by integrator | Per-token scope + rate limit + revoke on detect |
| BYOK provider compromise (e.g. AI key leaked from staff) | Notification + 1-click rotate from admin |

---

## 15. Open questions

1. WebAuthn passkey adoption tier — Phase 2 atau lebih awal?
2. Hardware security key support (YubiKey) untuk owner — niche, P2?
3. SOC 2 timeline — mungkin Phase 3 saat ada >50 paying SaaS tenants?
4. SIEM integration (Splunk/Datadog/Elastic) untuk owner enterprise — opt-in feature?
