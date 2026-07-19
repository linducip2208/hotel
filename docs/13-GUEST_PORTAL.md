# 13 — Guest Portal

> Public-facing surface untuk tamu: booking engine (search → book → pay), self check-in, in-stay companion, post-stay (review, loyalty). Mobile-first.

URL: pakai domain hotel sendiri (`/`, `/rooms`, `/booking`, `/portal/...`). Tidak ada sub-path /panel atau /admin.

---

## 1. Komponen

| Komponen | URL pattern | Phase |
|---|---|---|
| Homepage hotel | `/` | 🟢 |
| About / contact / policy | `/about`, `/contact`, `/privacy`, `/terms` | 🟢 |
| Room types listing | `/rooms` | 🟢 |
| Room type detail | `/rooms/{slug}` | 🟢 |
| Booking engine search | `/booking` | 🟢 |
| Booking engine results | `/booking/results` | 🟢 |
| Booking engine checkout | `/booking/checkout` | 🟢 |
| Booking confirmation | `/booking/confirmation/{ref}` | 🟢 |
| pSEO pages | (lihat 10-PSEO_STRATEGY.md) | 🟢 |
| Guest portal — Manage booking | `/portal/booking/{ref}?token=...` | 🟢 |
| Guest portal — Pre check-in | `/portal/pre-checkin/{ref}` | 🟢 |
| Guest portal — Self check-in (kiosk/mobile) | `/portal/checkin/{ref}` | 🟡 |
| Guest portal — In-stay (request, info) | `/portal/stay/{token}` | 🟡 |
| Guest portal — Folio view | `/portal/folio/{ref}` | 🟡 |
| Post-stay review | `/portal/review/{ref}` | 🟡 |
| Loyalty member portal | `/portal/loyalty` | 🟡 |
| Gift voucher buy / redeem | `/voucher`, `/voucher/{code}` | 🟡 |
| Wedding inquiry (banquet P2) | `/wedding`, `/wedding/inquiry` | 🟡 |

---

## 2. Booking engine flow (booking utama)

### a) Search

Form di homepage / sticky widget:
- Check-in date, check-out date
- Adults, children (with age tiers)
- Promo code (optional)
- Room count (default 1)

Validation: max stay 90 days, check-in >= today, check-out > check-in, dewasa >= 1.

### b) Results

- Available room types listed dengan: foto, nama, max occupancy, fasilitas key, price (BAR + service + tax breakdown), CTA "Book"
- Filter: price range, view (sea/garden/city), feature (smoking/non-smoking, breakfast included)
- Sort: price asc/desc, popularity
- Sticky summary di mobile: dates + nights + guests
- Multi-room: tab "Add another room" untuk booking >1 unit di transaksi yang sama

### c) Checkout

Single-page, vertical accordion sections:

1. **Booking summary** — non-editable, link "edit" balik ke search
2. **Add-ons** — breakfast, transfer, late checkout, extra bed (optional)
3. **Guest details** — primary guest: nama, email, telepon, KTP/Paspor (opsional pre-check-in), country, special requests free-text
4. **Special requests** — checkbox common (extra towel, rocking chair) + free-text
5. **Promo / voucher** — apply
6. **Payment** — BYOK PG redirect / embed / QRIS

Checkout = ATOMIC: reservation + folio created saat success payment callback, atau saat "pay later" untuk PG yang support deferred.

### d) Confirmation

- Confirmation number, QR
- Itinerary print-friendly
- Add-to-calendar links
- Email + WhatsApp (BYOK) auto-sent
- Link ke "Manage booking"

---

## 3. Manage booking

URL `/portal/booking/{ref}?token={one-time-secure-token}` — token di-email/WA, valid 30 hari.

Aksi:
- Lihat detail (tanggal, kamar, total, payment status)
- Edit guest detail / add request
- Pre-check-in form (upload KTP/Paspor, signature digital)
- Cancel (kalau dalam policy) — auto-calc penalty + initiate refund
- Modify dates (kalau policy memungkinkan + ada availability + kalau gak ada penalty issue)
- Pay outstanding balance
- Download invoice PDF

Tabel `booking_access_tokens` (booking_id, token_hashed, expires_at, used_at).

---

## 4. Pre check-in

Tujuan: kurangi antrean di front desk saat kedatangan.

Form online (sebelum tiba):
- Konfirmasi guest list (nama lengkap semua tamu)
- KTP/Paspor scan (per tamu) — camera HP, OCR auto-fill nama, tanggal lahir, no dokumen, kewarganegaraan
- Estimated arrival time
- Transport preference (need pickup? flight no, plate no kalau pakai mobil sendiri)
- Signature digital (e-registration card)
- Setuju syarat & kondisi hotel

Status `reservation.pre_checkin_complete = true` → di FO, kartu reservasi tampil "✅ pre-checkin done", staff tinggal verify dokumen + serahkan kunci.

---

## 5. Self check-in (kiosk / mobile)

Kalau hotel pakai door lock integration:

- Tamu scan QR di lobby kiosk atau buka link mobile saat tiba
- Verifikasi dokumen (yang sudah di-upload pre check-in)
- Pilih kamar (kalau auto-assign offered alternative)
- Generate kode pintu (PIN, mobile key, or NFC)
- Selesai — masuk kamar

Phase 2, butuh integrasi lock (Salto/Onity/Vingcard).

---

## 6. In-stay companion (Phase 2)

Setelah check-in, tamu dapat link `/portal/stay/{token}` (token unique per stay):

- Daily info hotel (jam breakfast, jam pool, jam shuttle)
- Request: extra towel, late checkout, room service, housekeeping
- Folio live view + add charges (room service from menu)
- Local guide (things to do, restaurants nearby — pSEO content)
- WhatsApp link to concierge
- Loyalty points earning (kalau member)
- Tip / service rating (saat departure)

---

## 7. Folio view

URL `/portal/folio/{ref}` — tamu lihat tagihan running:

- Detail charges (room rate, service, PB1, F&B, laundry, dll)
- Payment status (paid, partial, outstanding)
- Pay outstanding via PG
- Download interim invoice
- "Settle now" untuk early checkout

---

## 8. Post-stay

### Review request
Email + WA blast 24 jam setelah check-out:
- Link ke `/portal/review/{ref}`
- Rating (5-star), feedback per kategori (kebersihan, staff, lokasi, fasilitas, value)
- Public review (tampil di hotel page) atau private (hanya owner lihat)
- Insentif: diskon 10% next stay kalau review submit

### Auto-share ke Google / OTA (P2)
Kalau positif (>4 stars), prompt tamu auto-link ke Google review / TripAdvisor.

### Loyalty enrollment / point credit
Otomatis credit point untuk member, atau prompt enroll non-member.

---

## 9. Loyalty portal (P2)

- Sign up / login (email atau OAuth Google/Apple/Facebook)
- Tier display (Silver / Gold / Platinum)
- Point balance
- Transaction history
- Member-only rate visible
- Redeem: free night, upgrade, F&B voucher

---

## 10. Gift voucher

- Buy: pilih nominal (Rp 500k, 1jt, 2jt, custom) atau package (1-night stay, dinner-for-2, spa)
- Pay via PG
- Otomatis kirim e-voucher ke recipient via email/WA dengan kode unik
- Redeem: tamu booking + apply kode di checkout, atau staff scan QR di check-in

---

## 11. Multi-bahasa

Default bahasa: detect dari browser `Accept-Language`, fallback `id`. User toggle di header `[ID][EN]`.

Translatable string: di file `lang/id.json`, `lang/en.json`. Property-specific copy (homepage, room desc) editable di admin UI dengan kolom per locale.

---

## 12. Theme & branding

Owner config di admin:
- Logo (header, footer, favicon)
- Color palette (primary, secondary, accent)
- Hero image / video
- About copy
- Custom CSS (advanced)
- Custom Footer (sosmed, alamat)
- Custom domain + SSL

Template baseline: 3 design preset ("Boutique", "Resort", "Modern") yang bisa di-pick + override.

---

## 13. Tracking / analytics

Embed (BYOK):
- Google Analytics / GA4 ID
- Google Tag Manager
- Meta Pixel
- TikTok Pixel
- Custom script box (untuk Hotjar dll)

E-commerce events fired di booking flow: `view_item`, `add_to_cart`, `begin_checkout`, `purchase`.

---

## 14. Performance & SEO

- Booking engine SSR (server-rendered) untuk SEO + LCP
- Critical CSS inline
- Image WebP/AVIF + lazy + responsive
- Preconnect/DNS prefetch ke CDN, payment provider, GA
- Core Web Vitals monitored
- Booking CTA above the fold

---

## 15. Security

- Booking token hashed di DB, sekali pakai untuk action sensitif (cancel, refund)
- Rate limit: 10 booking attempt per menit per IP
- Captcha (BYOK Turnstile/hCaptcha) di checkout untuk anti-fraud
- PCI: card data tidak pernah hit server kita; via PG redirect/iframe/tokenization
- HTTPS enforced; HSTS preload-ready

---

## 16. PWA

`/portal/*` route boleh di-install sebagai PWA — tamu tap "Add to Home" → app icon hotel di phone, akses cepat selama stay.

Phase 2.

---

## 17. Open questions

1. Booking engine — single page (modern, app-like) atau multi-step (familiar)? Default: multi-step accordion di MVP.
2. Apakah pre-check-in mandatory atau optional? Beberapa hotel pengen mandatory untuk efisiensi.
3. Custom domain handling per tenant (SaaS) — pakai Cloudflare for SaaS atau setup manual ACME?
4. Apakah guest harus daftar akun untuk booking, atau guest checkout (no account) cukup? Default: guest checkout, optional account creation.
