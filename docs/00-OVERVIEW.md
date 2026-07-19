# 00 — Overview

> Visi produk, target market, positioning, value proposition.

---

## 1. Apa ini

Hotel Management System (HMS) full-stack untuk pasar Indonesia. Bukan sekadar PMS — ini adalah **all-in-one operating system hotel**: PMS + Channel Manager + Booking Engine + POS + Akuntansi + AI tools + pSEO marketing pages, dalam satu codebase Laravel.

Dijual sebagai **standalone product** (license one-time + maintenance) ke pemilik hotel yang ingin self-host, **dengan upgrade path mulus ke SaaS multi-tenant** tanpa rewrite kode.

---

## 2. Target Market

### Primary (MVP launch)

| Segmen | Ukuran tipikal | Pain point utama |
|---|---|---|
| **Boutique hotel & villa** | 5-30 kamar | Bosan VHP/Realta yang dated; gak mampu Cloudbeds ($300+/bln) |
| **Hotel bintang 2-3 lokal** | 30-100 kamar | Butuh akuntansi + pajak Indonesia + channel manager dalam 1 sistem |
| **Guesthouse & hostel modern** | 10-40 bed | Butuh online booking engine kuat + OTA Indonesia + pSEO |
| **Villa group / serviced apartment** | Multi-property kecil | Butuh central dashboard tanpa harus chain enterprise tools |

### Secondary (Phase 2+)

- Hotel bintang 4-5 independen (butuh banquet, spa, MICE)
- Resort destinasi (Bali, Lombok, Yogya, Bandung)
- Chain kecil 3-10 property
- Reseller / agency yang white-label untuk client mereka

### Bukan target

- Chain enterprise (Marriott, Accor, Hyatt) — Oracle OPERA Cloud territory, sales cycle 12+ bulan, kompleksitas berbeda
- Hotel ultra-budget (<5 kamar) yang cukup pakai spreadsheet
- Property management residential (bukan hospitality)

---

## 3. Positioning Statement

> "**Cloudbeds-grade UX dengan compliance VHP, harga eZee, dengan pSEO yang tidak dimiliki kompetitor manapun** — di-deploy di server kamu sendiri (atau cloud kami) dengan license fair."

### Three pillars yang membedakan

**A. Indonesia-first compliance**
- PB1 per kabupaten/kota (rate dinamis, configurable di admin)
- e-Faktur Coretax XML export
- Lapor WNA imigrasi (LAPOR PASPOR format)
- NPWP guest opsional, KTP/Paspor OCR
- Format laporan PSAK Indonesia
- Native OTA lokal: Traveloka, Tiket.com, Mister Aladin, Pegipegi
- Native payment lokal: Midtrans, Xendit, DOKU, QRIS, e-wallet, VA

Cloudbeds & Mews **tidak punya** ini built-in — harus custom integration mahal.

**B. BYOK semua integrasi**
- User input API key sendiri di admin UI
- Tidak ada lock-in ke "Cloudbeds Payments" atau "Mews Payments" yang markup 1-2%
- 11 preset AI provider termurah (DeepSeek, Gemini Flash, Mistral, Anthropic Haiku, dll.)
- Switch provider kapan saja tanpa downtime

**C. Programmatic SEO built-in**
- `/hotels-in-{city}`, `/best-hotels-in-{city}-{year}`
- `/hotels-near-{landmark}-{city}`, `/cheap-hotels-{city}-under-{price}`
- `/wedding-venues-{city}`, `/business-hotels-{city}`
- `/things-to-do-in-{city}`, `/compare/{a}-vs-{b}`
- Auto-generate dari data DB + JSON-LD + sitemap dinamis

Tidak ada kompetitor yang punya ini bawaan. Owner hotel kecil-menengah dapat traffic organik **gratis** tanpa harus paid Google Ads.

---

## 4. Competitive Landscape

| Vendor | Harga (~property kecil) | Plus | Minus | Posisi kita |
|---|---|---|---|---|
| **Cloudbeds** | $300-500/bln | UX modern, all-in-one | Mahal, payment lock-in, gak Indonesia-aware | Lebih murah 3-5x, Indonesia-native |
| **Mews** | €300-700/bln | API-first, modern | Lebih mahal, kurang SEA OTA | Lebih murah, Indonesia-native, BYOK |
| **Oracle OPERA Cloud** | $500-2000/bln | Enterprise-grade | Overkill SMB, mahal | Berbeda segmen |
| **eZee/Yanolja Cloud** | $60-150/bln | Affordable, SEA-aware | UX dated, akuntansi lemah | UX modern, akuntansi kuat |
| **VHP / Realta / Power Pro** | License Rp 50-200jt | Akuntansi & pajak Indonesia kuat, on-prem | Mobile lemah, cloud lemah, UI dated | UI modern, cloud-ready, channel manager kuat |
| **Hotelogix / DJUBO** | $2-5/kamar/bln | Affordable | Generik, gak Indonesia-aware | Indonesia-native, pSEO |

**Sweet spot kita:** harga eZee + UX Mews + compliance VHP + pSEO unique = posisi yang belum diisi siapapun.

---

## 5. Value Proposition per Stakeholder

### Owner hotel
- ROI cepat: license one-time + biaya bulanan minim (cuma BYOK provider yang kamu pakai)
- Source code di server sendiri = data sovereignty
- pSEO = traffic organik gratis tanpa Google Ads
- Compliance pajak Indonesia auto

### General Manager
- Single dashboard: occupancy, ADR, RevPAR, channel mix, daily revenue
- Forecast & rate management bantuan AI (BYOK LLM)
- Mobile real-time

### Front Office staff
- Reservation grid drag-drop
- KTP/Paspor scan via kamera HP
- Room assignment otomatis dari rules
- Check-in <60 detik

### Housekeeping
- Mobile interface, status real-time
- Task queue per maid
- Lost & found tracker

### Accounting
- Auto journal posting dari operasi
- AR/AP, city ledger
- e-Faktur Coretax export
- Neraca + laba-rugi standar PSAK

### Tamu
- Booking direct di website hotel (commission-free)
- Online check-in via mobile (skip antrian FO)
- AI concierge multi-bahasa 24/7
- e-receipt

### Owner reseller / agency (Phase 2)
- White-label penuh
- Commission tracking
- Multi-client dashboard

---

## 6. Business Model

### Standalone (license one-time + annual support)

| Tier | Kamar | License | Annual support |
|---|---|---|---|
| Lite | ≤30 | Rp 25 juta | Rp 7 juta/year |
| Pro | 31-100 | Rp 50 juta | Rp 12 juta/year |
| Enterprise | >100 atau multi-property | Rp 100 juta+ | Rp 20 juta/year |

### SaaS (per kamar per bulan, recurring)

| Tier | Harga | Fitur |
|---|---|---|
| Starter | Rp 35rb/kamar/bln | PMS + CM + BE + POS basic |
| Pro | Rp 60rb/kamar/bln | + Revenue Mgmt, Multi-property, Loyalty |
| Enterprise | Custom | + Banquet, Spa, HR, dedicated support |

### Add-ons (for both)

- White-label branding: Rp 15 juta one-time atau Rp 1 juta/bln
- SaaS Reseller License: Rp 200 juta one-time + revenue share
- AI provider credits prepaid (kalau owner gak mau urus BYOK sendiri): markup 20%
- Custom OTA integration: Rp 5-15 juta per OTA
- Migration dari sistem lama (VHP/eZee): Rp 10-30 juta jasa

Detail di [`19-PRICING.md`](19-PRICING.md).

---

## 7. Why now (2026)

- **OTA komisi naik** (Booking 18-25%, Agoda 15-20%) → owner lapar tools yang dorong direct booking
- **Coretax mandatory 2025** → semua hotel butuh sistem yang export e-Faktur otomatis
- **PB1 audit lebih ketat** di banyak Pemkab/Pemkot → butuh laporan yang clean
- **AI accessibility** → DeepSeek, Gemini Flash, Llama bikin AI feature affordable; hotel yang gak adopt akan tertinggal
- **Cloud adoption naik** di SMB hospitality Indonesia post-pandemic
- **Generation gap di vendor lokal** — VHP/Realta legacy, generasi pengguna baru menuntut UX modern

---

## 8. Success Metrics (12 bulan dari launch)

| Metrik | Target |
|---|---|
| Standalone licenses sold | 50 properties |
| SaaS active tenants | 20 properties |
| MRR (SaaS) | Rp 50 juta/bulan |
| pSEO impressions/month | 100,000 |
| pSEO conversion ke trial | 200/bulan |
| NPS owner | ≥ 50 |
| Support ticket avg resolution | < 24 jam |

---

## 9. Risk & Mitigation

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| OTA API change breaks channel sync | High | High | Versioned adapter, queue retry, monitoring + alert |
| Coretax format change | Medium | High | Versioned XML schema, monthly compliance check |
| Big competitor masuk Indonesia (Cloudbeds/Mews lokalisasi) | Medium | High | Lock owner via long-term license, white-label reseller |
| Self-host complexity scare off owner | High | Medium | One-click installer + managed-host partnership |
| AI provider deprecation | Medium | Low | BYOK = user pindah aja, gak ngejer kita |
| License key leak / piracy | High | Medium | Pairing v3 + heartbeat + revocation, watermark source |
| Pajak audit gagal karena bug | Low | Catastrophic | Test suite ekstensif, opsi pre-audit consulting |

---

## 10. Next Steps

1. ✅ Selesai dokumentasi (saat ini)
2. ⏭ Setup repo Laravel 11 + base architecture per [`03-ARCHITECTURE.md`](03-ARCHITECTURE.md)
3. ⏭ MVP Phase 1 (10 minggu): Front Office + Channel Manager (3 OTA) + Booking Engine + POS basic + akuntansi basic + pSEO
4. ⏭ Pilot 1-2 hotel kenalan untuk validation
5. ⏭ Iterasi based on pilot feedback
6. ⏭ Public launch standalone tier
7. ⏭ SaaS conversion (Phase 4)
