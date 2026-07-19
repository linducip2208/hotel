# 23 — Advanced Roadmap (Phase 2 & Phase 3)

> Fitur post-MVP yang akan diurutkan setelah produk stabil dan ada paying customers. Bukan janji deadline — urutan prioritas + dependency.

---

## Phase 2 — Post-MVP (Q3-Q4 setelah launch)

Fokus: deepen value untuk hotel yang sudah pakai. Convert standalone → SaaS. Add module yang sering diminta tapi bukan blocker MVP.

### 2.1 SaaS conversion (M16)

- stancl/tenancy adoption
- Central control plane (vendor admin)
- Tenant signup flow public
- Trial 14-day
- Per-kamar billing
- Migration tooling standalone → SaaS

Lihat [18-SAAS_UPGRADE_PATH.md](18-SAAS_UPGRADE_PATH.md).

### 2.2 Revenue Management System (RMS) Lite

Tujuan: dynamic pricing rule-based, bukan AI penuh.

- Demand forecast simple (last-year same-period + occupancy trend)
- BAR auto-suggest based on pickup pace
- Competitor rate ingest (BYOK rate shopper)
- Price elasticity nudge per segment
- Rules engine: "kalau occupancy > 80% by D-3, raise rate +15%"
- Yield report: revenue per available room (RevPAR), market share index (MPI), rate index (ARI)

AI demand forecast (P3) — Phase 3 saat ada cukup data historis tenant (≥6 bulan).

### 2.3 Banquet & Event Module

- Function room availability calendar
- Event reservation: wedding, meeting, gala
- BEO (Banquet Event Order) generator
- F&B menu per event
- AV equipment booking
- Wedding inquiry form (pSEO `/wedding-venue-{city}`)
- Quote → contract → deposit → final settlement
- Multi-day events
- Conjunction dengan room block (group)

### 2.4 Spa & Wellness Module

- Treatment menu
- Therapist scheduling
- Room (cabin) booking
- Membership / package
- POS spa terhubung ke folio kamar
- Inventory product (massage oil, dll)

### 2.5 HR & Payroll (Mekari/Talenta-style)

- Employee master
- Attendance: clock in/out (mobile, fingerprint integration via PSAM device)
- Schedule / shift planning
- Leave management
- Payroll: gaji pokok, tunjangan, lembur
- BPJS Kesehatan + Ketenagakerjaan auto-calc
- PPh 21 calc + slip
- Service charge distribution module
- Performance review (yearly)

Integrasi: payslip print, transfer batch ke bank (BCA/Mandiri/BRI/BNI auto-format).

### 2.6 Asset & Inventory

- Fixed asset register: pembelian → pencatatan → maintenance → depresiasi → disposal
- General inventory beyond F&B (linen, amenity, cleaning supply)
- Stock opname workflow
- Reorder point alert

### 2.7 Loyalty Program

- Tier (Silver / Gold / Platinum)
- Point earn rule (per Rp spent atau per night)
- Redeem: room, F&B, voucher, upgrade
- Member rate visible in booking engine
- Birthday auto-promo
- Referral mechanism

### 2.8 White-label & Reseller portal

- Reseller account (sub-admin di vendor panel)
- Per-reseller branding (logo, color, domain)
- Commission ledger
- Sub-admin panel
- White-label license (rebrand HMS as own product)

### 2.9 Advanced reporting

- Custom report builder (drag-drop columns + filter)
- Schedule reports auto-email weekly / monthly
- Cross-property consolidated P&L (chain owner)
- Owner statement per villa (titip-kelola model)
- Forecast cash-flow

### 2.10 In-stay companion (full)

- Tamu app (PWA): info, request, room service, folio
- Concierge AI chatbot (BYOK LLM)
- Local guide content
- TV casting / IPTV integration

### 2.11 Door lock + IoT

- Salto / Onity / Vingcard direct integration
- Mobile key (Bluetooth / NFC)
- Energy management: room key card → AC/lights cut on checkout
- IoT minibar (auto-detect open + auto-charge)

### 2.12 Communication center

- Unified inbox: WA + email + SMS + chat dari OTA
- Auto-response template
- Sentiment AI tag (BYOK)
- Bulk campaign tool

### 2.13 Maintenance & PPM

- Preventive Maintenance schedule (per asset)
- Work order full lifecycle
- Vendor / contractor master
- Cost tracking per asset

### 2.14 Multi-currency & FX

- Booking engine support USD, SGD, AUD, JPY, EUR
- FX rate daily ingest (BYOK rate provider)
- Folio in IDR, display in foreign as estimate
- Settlement reconciliation cross-currency

### 2.15 GDS Connect (paid integration)

- Sabre, Amadeus, Travelport
- Berbayar ke vendor GDS (passthrough fee + setup)
- Untuk hotel yang serve corporate travel agent

---

## Phase 3 — Strategic / Heavy

Fokus: differentiator unik + monetization extra. Setelah ada ≥50 paying SaaS tenants.

### 3.1 AI Concierge per hotel (BYOK)

- Chatbot personalized per property
- Intent: pre-stay (FAQ, recommendation), in-stay (request), post-stay (review)
- Multilingual (ID, EN, ZH, JA, KO, AR)
- Train on hotel-specific knowledge base (room facilities, local guide)
- Voice mode (P3+ + ASR/TTS BYOK)

### 3.2 AI Demand Forecast & Dynamic Pricing

- Forecast based on hotel + market data (events, weather, school holidays)
- Auto-suggest BAR per day per room type
- Confidence score
- Owner approve / auto-apply
- Backtest mode

### 3.3 AI Review Reply Generator

- Saat review masuk (Google, OTA, internal) → draft reply auto-generated
- Owner edit + send
- Tone customization (formal / friendly / per-locale)

### 3.4 AI Photo enhancement

- Auto-enhance hotel photos (color, exposure, watermark)
- AI-generated hero image variants per locale

### 3.5 AI Lead scoring (corporate sales)

- Scoring leads (company inquiry) by likelihood-to-close
- Recommend follow-up cadence

### 3.6 Marketplace add-ons

- Third-party developer publish add-on (e.g. specialized integrations)
- Vendor curate, security review
- Tenant subscribe via marketplace
- Revenue share (70/30 dev/vendor)

### 3.7 Embedded fintech

- Working capital loan untuk hotel (partner: Akulaku / KoinWorks / Fund Indonesia)
- Owner cash flow analytics → pre-qualify
- Invoice factoring untuk OTA payout
- Co-branded card (P3+)

### 3.8 Group & Chain management

- Multi-property chain dashboard
- Cross-property booking (transfer, group quote span properties)
- Brand standards enforcement
- Mystery shopper feedback module
- Brand-level marketing campaign

### 3.9 PMS-as-a-Platform (API marketplace)

- Public API marketplace docs
- Partner integrations: Expedia (more depth), Booking 2-way modify, Airbnb API direct
- Developer portal `developers.hotelhub.id`

### 3.10 Big-data analytics & data warehouse

- ETL ke data warehouse (BigQuery / ClickHouse)
- Cross-tenant aggregated insight (anonimized): "best practice benchmark"
- Owner dashboard "your hotel vs market in city"
- Sell anonimised market data report (P3+)

### 3.11 Marketplace `hotelhub.id` (agregator)

- Cross-tenant directory
- pSEO landing pages (lihat 10-PSEO_STRATEGY.md section 16)
- Direct booking via marketplace, redirect ke tenant booking engine
- Optional 3-5% commission from marketplace bookings (vendor-owned business)

### 3.12 Mobile native apps

- iOS + Android dengan Flutter / React Native
- Untuk staff (HK, FO, manager dashboard)
- Untuk guest (booking + in-stay companion)

### 3.13 OTAdirect (eat OTA's lunch)

- Bypass OTA: direct partnership dengan corporate travel manager
- Hotel inventory aggregator untuk B2B agen
- Wholesaler API (rate net + markup)

### 3.14 Sustainability tracking

- Carbon footprint per stay
- Energy / water consumption tracking
- Sustainability badge → marketing differentiator
- Compliance laporan ke ASEAN Green Hotel Standard

### 3.15 Voice AI staff assistant

- Front desk staff hands-free: "Cek kamar 305 status", "Buat reservasi atas nama Budi 3 malam"
- Wake-word: "Halo Hotel"
- Privacy-aware (data tetap di tenant DB, BYOK ASR endpoint)

---

## Capacity & resourcing notes

Phase 2 & 3 tidak akan dikerjakan paralel semua. Urutan didorong oleh:
- Paying customer requests
- Competitive pressure
- Estimated revenue impact per fitur
- Engineer capacity

Setiap fitur Phase 2-3 punya RFC (request-for-comments) dokumen sendiri sebelum build dimulai — tidak commit ke spek di sini lebih dari high-level outline.

---

## Open questions

1. Phase 2 priority order — RMS atau Banquet duluan? Tergantung 5 customer pertama.
2. AI features (Phase 3) butuh data lake — bangun lebih dulu di Phase 2?
3. Marketplace add-on Phase 3 — apakah butuh review board atau auto-approve dengan rate-limit?
4. Mobile native: iOS / Android atau Flutter / React Native?
5. Embedded fintech butuh license OJK — kerjasama partner atau bangun BU sendiri?
