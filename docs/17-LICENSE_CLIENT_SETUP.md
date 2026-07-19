# 17 — License Client Setup (Wizard, Heartbeat, Errors)

> Sisi customer (deployment hotel) saat install, paste license key, dan jalankan pairing. Companion docs ke 16-LICENSE_PAIRING_DESIGN.md (server-side).

Target: zero-friction install untuk owner non-IT, tapi tetap secure.

---

## 1. Install timing flow

```
1. Owner unduh installer / clone repo
2. Konfigurasi .env dasar (DB, APP_URL, MAIL)
3. Run `php artisan migrate`
4. Run `php artisan license:bootstrap` (bootstrap kunci & install_id)
5. Akses /setup/wizard di browser
6. Wizard:
   a. Welcome
   b. Connection check (DB, Redis, Vendor License Server reachability)
   c. Input license key
   d. Pairing
   e. Owner profile (nama, email, telepon)
   f. Property info (nama hotel, alamat, region, jumlah kamar)
   g. Default tax (PB1 region, PPN if PKP)
   h. Initial admin user create
   i. Done — redirect ke /panel
```

Total waktu wizard ≈ 5 menit.

---

## 2. CLI bootstrap

`php artisan license:bootstrap`:
- Generate `install_id` (UUID v4, persist di `local_licenses.install_id`)
- Generate device fingerprint:
  ```
  fingerprint = sha256(
    machine_id (DBus / WMI) +
    primary_mac_address +
    OS_release +
    install_path_hash +
    install_id
  )
  ```
- Persist di local DB
- Print fingerprint ke console (untuk troubleshooting kalau wizard gagal — owner bisa kirim ke support)

Flag `--force` untuk regenerate (saat re-pair / move install).

---

## 3. Wizard step-by-step

### Step (a) Welcome

Card overview:
- Logo hotel app (sebelum custom branding)
- "Selamat datang. Wizard ini akan membantu setup license dan property pertama. ~5 menit."
- Button "Mulai"

### Step (b) Connection check

Auto-run pre-checks:
- ✅ DB connection
- ✅ Redis connection
- ✅ Storage writable
- ✅ Vendor license server reachable (ping `/api/license/health`)

Kalau gagal salah satu:
- Tampil error message + suggestion fix
- Button "Coba Lagi"
- Link "Saya butuh bantuan" → support email/WA

### Step (c) Input license key

Form:
- Field license key (auto-format `HMS-XXXXX-XXXXX-XXXXX-XXXXX`)
- Auto-fokus, paste-friendly
- Tooltip: "Cek email pembelian Anda. Format: HMS-...-...-...-..."

Validation client-side: regex format. Server-side: actual lookup.

### Step (d) Pairing

Submit license key → wizard call `POST /api/license/pair` (lihat 16-LICENSE_PAIRING_DESIGN.md).

Sukses:
- Tampil checkmark + "License aktif sampai 28 April 2027"
- Plan + feature list ringkas

Gagal scenarios:
- `license not found` → "Lisensi tidak dikenali. Periksa format atau hubungi sales."
- `license already paired` → "Lisensi sudah terpasang di device lain. Untuk pindah server, gunakan menu 'Pindah Instalasi' di portal vendor."
- `license expired` → "Lisensi sudah kedaluwarsa. Hubungi sales untuk perpanjangan."
- `network error` → "Tidak bisa menghubungi server lisensi. Cek internet & firewall."

Setiap error punya CTA + link bantuan.

### Step (e) Owner profile

Form:
- Nama lengkap
- Email
- WhatsApp
- Posisi (Owner / GM / Manager)

Disimpan di `tenant_profiles` (atau `properties.owner_*`) untuk billing & komunikasi.

### Step (f) Property info

- Nama hotel
- Brand (kalau ada)
- Alamat (street, city, province, postal, lat-lng auto via Maps)
- Region code (untuk PB1 — auto-resolve dari alamat, tapi bisa override)
- Jumlah kamar
- Bintang (1-5)
- Currency default (IDR)
- Timezone (Asia/Jakarta default)

### Step (g) Tax config

- PB1 rate (auto-suggest dari region master, editable)
- PKP status: ya/tidak
  - Kalau ya → input NPWP, NSFP series, plan e-Faktur Coretax
  - Kalau tidak → skip PPN config
- Service charge default (5-10%)

### Step (h) Initial admin user

Form:
- Email
- Password (dengan strength meter)
- Konfirmasi password
- Nama
- 2FA enrollment (TOTP QR + recovery codes) — wajib finish disini, tidak bisa skip

User created with role `super_owner`.

### Step (i) Done

- Confirmation card: "Setup selesai 🎉"
- Quick start checklist:
  - [ ] Tambah room types
  - [ ] Konfigurasi rate plans
  - [ ] Add payment provider (BYOK)
  - [ ] Connect channel manager / OTA
  - [ ] Customize booking engine theme
- Tombol "Masuk Panel"

---

## 4. Re-pair / Migrate flow

Trigger: owner pindah server, ganti hardware, atau restore from backup.

Akses: dari panel admin → "Settings → License → Pindah Instalasi"

1. Confirm: "Lisensi akan dipindah ke device ini. Device lama akan otomatis non-aktif. Lanjut?"
2. Reason input (audit)
3. Submit → call `POST /api/license/migrate`
4. Vendor server validate quota migrate (default 2/year free)
5. Sukses → token replaced, fingerprint updated
6. Audit log entry both sides

---

## 5. Heartbeat scheduler (klien)

Cron entry di app:

```php
// app/Console/Kernel.php
$schedule->command('license:heartbeat')->dailyAt('03:00');
$schedule->command('license:heartbeat-retry')->everyFourHours();
```

`license:heartbeat`:
- Kirim payload (lihat 16-LICENSE_PAIRING_DESIGN.md)
- Sukses → update token + `last_heartbeat_success_at`
- Gagal → log + schedule retry job

`license:heartbeat-retry`:
- Kalau `last_heartbeat_success_at < now() - 24h`, retry
- Backoff exponential
- Kalau >7d gagal → trigger banner + email

---

## 6. Banner & UI feedback

Status banner di top admin/user panel:

| Status | Banner |
|---|---|
| `paired` & heartbeat OK | (none) |
| Heartbeat overdue 7-14d | Yellow "Lisensi: heartbeat tertunda 8 hari. Periksa koneksi internet server." (dismissible 24h) |
| Heartbeat overdue 14-30d | Red "Lisensi belum sinkron 16 hari. Hubungi support jika berkelanjutan." (not dismissible) |
| Grace expired (>30d) | Modal full-screen "Mode terbatas: hanya read & report yang tersedia. Re-online untuk aktifkan kembali." |
| `revoked` | Login layar terganti "Lisensi tidak aktif. Hubungi admin." |
| `fingerprint_mismatch` | Modal "Perubahan hardware terdeteksi. Lakukan re-pair?" + wizard |

---

## 7. Logging

Semua event license-relevant logged ke `audit_logs`:
- `license.paired`, `license.heartbeat.success`, `license.heartbeat.failed`
- `license.degraded`, `license.locked`, `license.revoked`
- `license.migrated`
- `license.fingerprint_mismatch`

Plus dedicated log channel `storage/logs/license.log` (rolling 30 days) untuk debugging.

---

## 8. Local API (panel)

`GET /panel/license/status` (admin only):
```json
{
  "status":"paired",
  "plan":"standalone-pro",
  "valid_until":"2027-04-28",
  "last_heartbeat":"2026-04-28T03:00:01Z",
  "next_heartbeat":"2026-04-29T03:00:00Z",
  "grace_until":"2026-05-28",
  "features":{...},
  "usage":{
    "rooms":24,
    "max_rooms":100,
    "users":8,
    "max_users":30
  }
}
```

`POST /panel/license/refresh` — manually trigger heartbeat sekarang.
`POST /panel/license/migrate` — initiate migration wizard.

---

## 9. Backup-aware behavior

Kalau owner restore from backup ke server baru:
- `install_id` dari backup berbeda dengan fingerprint baru → mismatch detected
- Banner trigger re-pair flow
- Vendor server bisa opt-in policy "restore = auto-approve migrate" untuk kemudahan, atau require konfirm

---

## 10. CLI utilities

```
php artisan license:status        → print full local status JSON
php artisan license:heartbeat     → force heartbeat now
php artisan license:rotate-token  → request new token (without changing fingerprint)
php artisan license:unpair        → unpair current install (admin only)
php artisan license:diagnostic    → run all checks (network, fingerprint, token validity, server reachability)
```

`license:diagnostic` output:
```
[✓] Vendor server reachable
[✓] Public key intact
[✓] Token valid (exp 2026-05-28)
[✓] Fingerprint matches
[✓] Last heartbeat 4 hours ago
Plan: standalone-pro
Features: channel_manager, marketplace_addons:false, ai_demand_forecast:false
Usage: 24/100 rooms, 8/30 users
```

Hand off ke support saat user lapor masalah.

---

## 11. Edge cases & FAQ

**Q: Apa yang terjadi kalau internet hotel mati seharian?**
A: Tidak masalah — heartbeat retry otomatis, grace window 30 hari.

**Q: Bisa multiple instance load-balanced di belakang 1 license?**
A: Phase 1 — 1 license = 1 fingerprint = 1 server. Phase 2 multi-node fingerprint cluster (premium).

**Q: Kalau hardware mati total, license lost?**
A: Tidak. License key di-store di vendor side. Owner re-pair di server baru via wizard "Migrate".

**Q: Bisa cek di vendor portal status license sendiri?**
A: Ya, owner punya akun di `portal.hotelhub.id` untuk lihat status license, perpanjang, download invoice.

**Q: Apa yang ada di dalam `.env` setelah pair?**
A:
```
LICENSE_KEY_HASH=...     (hashed, not plaintext)
LICENSE_INSTALL_ID=uuid
LICENSE_FINGERPRINT=sha256:...
```
Token disimpan di DB row `local_licenses` (encrypted at rest), bukan di `.env`.

---

## 12. Security notes

- Public key (verify token) bundled in `config/license/vendor-public.pem`. Hash hardcoded.
- Wizard endpoint `/setup/wizard` hanya accessible kalau `local_licenses.status` IS NULL or `unpaired`. Setelah pair, route hidden.
- Heartbeat selalu via HTTPS, certificate pinning optional Phase 2.
- Token in transit: bearer header, never query string.
- Token at rest: encrypted via `Crypt::encryptString()`.

---

## 13. Owner-side observability

Setting → License panel:
- Real-time status, grace countdown, expiry countdown
- Recent heartbeat log (last 30)
- Telemetry preview (apa saja yang dikirim ke vendor — for transparency)
- Buttons: Refresh, Migrate, Diagnose, Unpair (with double-confirm)
- Link "Buka portal vendor untuk perpanjang" → `https://portal.hotelhub.id`

---

## 14. Open questions

1. Apakah wizard support resume (kalau owner close mid-flow)? Ya — disimpan di session + DB resumable.
2. Multi-property setup di wizard, atau cukup 1 property pertama lalu sisanya via panel? Default: 1 property di wizard, sisanya di panel.
3. Onboarding video di wizard step pertama (90 detik tour)? Nice-to-have Phase 2.
