# 24 — Unicorn Roadmap (Strategic / Long-Term)

> Skenario "kalau semua jalan lurus" — bagaimana HMS ini bisa scale jadi infra hospitality regional besar (atau exit lewat akuisisi). Bukan plan, tapi vision document yang membentuk keputusan arsitektur sekarang.

Audience: founder / strategist. Lain dengan Phase 2/3 di [23-ADVANCED_ROADMAP.md](23-ADVANCED_ROADMAP.md) yang masih jelas terikat ke produk yang ada.

---

## 1. Posisi awal (Year 1)

- 50-200 paying customers Indonesia (mix standalone + SaaS early)
- ARR Rp 2-8 milyar (ARR target Year 1 conservative)
- Tim: 3-7 orang (founder + 2-3 dev + sales/CS partime)
- Geo: Jakarta, Bali, Yogyakarta dominasi
- Differentiator: pSEO bawaan, BYOK semua, harga friendly, kepatuhan Indonesia kuat

---

## 2. Year 2-3: Indonesia dominasi SMB segment

### Goal
- 1.000-3.000 hotel kecil-menengah Indonesia pakai platform
- ARR Rp 30-80 milyar
- 10-25 employees
- Recognized as "alternative to VHP / eZee for boutique hotel"

### Levers
- Heavy pSEO long-tail (every city, every neighborhood)
- Reseller network (1 reseller per provinsi)
- Free migration tooling (eZee → HotelHub, VHP → HotelHub)
- Local user community: "HotelierHub Meetup" Bali / Jakarta / Yogyakarta
- Partnership: PHRI (Perhimpunan Hotel & Restoran Indonesia) member discount
- Partnership: Niagahoster / Biznet bundle deals

### Risks
- Cloudbeds / Mews bidik Indonesia → kompetisi harga
- Big Indonesia SaaS (Mekari) bisa launch hotel module
- Funding tight: bootstrapped vs need to raise

### Funding decision point
Year 2-end: raise seed (USD 1-3M) untuk akselerasi sales + Phase 3 build, atau bootstrap stay lean.

---

## 3. Year 3-5: ASEAN expansion

### Goal
- Expand ke Vietnam, Filipina, Thailand (similar SMB hotel landscape)
- Multi-language: ID, EN, VI, TH, TL
- 10.000+ properties di-platform
- ARR USD 8-25M
- 60-100 employees

### Localization per country
- Tax: PB1 → equivalent (VAT/local tourism tax)
- Compliance reporting: per local immigration / tourism authority
- Payment: BYOK adapter cover local PG (VNPay, MoMo, GCash, PayMaya, TrueMoney)
- Channel: Agoda, Booking, Traveloka, Airbnb (universal), plus regional (Cleartrip, Klook)
- Pricing: per-country tier (PPP-adjusted)

### Strategic moves
- M&A small competitors (acquihire local PMS)
- Partnership with regional travel ecosystem (Traveloka, Klook, Tiket)
- Open API marketplace untuk lokal devs

---

## 4. Year 5-8: Vertical platform play

### Goal
- Beyond PMS: become "operating system" untuk hospitality SMB
- Embedded fintech, marketplace, GDS, supply chain
- 30.000+ properties
- ARR USD 100-300M
- 200-500 employees

### Modules expanded
- **HotelHub Capital** — working capital loan, invoice factoring, co-branded card (lihat 23 section 3.7)
- **HotelHub Marketplace** — supplies, F&B sourcing, linen, amenities (B2B)
- **HotelHub Distribution** — wholesaler API, B2B channel direct
- **HotelHub Insights** — anonymized market intelligence, sold as data product
- **HotelHub Academy** — training, certification untuk staff hotel SMB
- **HotelHub Insurance** — partnership hospitality insurance

### Revenue mix shift
- Year 1: 100% SaaS subscription
- Year 5: 60% SaaS + 25% fintech + 10% marketplace + 5% data
- Year 8: 40% SaaS + 35% fintech + 15% marketplace + 10% data

---

## 5. Year 8-10: Scale-up / IPO / Exit

### Possible paths

**Path A — IPO Indonesia (IDX) atau dual-list (NASDAQ)**
- Requires ARR > USD 200M, growth > 40% YoY, profitable EBITDA
- Use proceeds: international expansion, M&A
- Reference: Bukalapak, GoTo, Sea (regional comparable)

**Path B — Strategic acquisition**
- Acquirer candidates:
  - **Oracle** (Hospitality OPERA — but they target enterprise, mungkin underperformed di SMB)
  - **Sabre** (HMS gap di SMB)
  - **Booking Holdings** (vertical integration, less likely karena conflict)
  - **Amadeus**
  - **Mews / Cloudbeds** (consolidation play)
  - **GoTo / Traveloka** (regional vertical lock-in)
  - **Mekari Group** (Indonesia SMB SaaS giant)

**Path C — PE Roll-up**
- PE firm aggregate hospitality tech assets
- Reference: Vista Equity, Thoma Bravo

**Path D — Stay private profit-machine**
- Bootstrapped from Year 5 onward, distribute profits
- Reference: Calendly (kept private long), Atlassian (delayed IPO)

### Defensibility
- Switching cost: PMS deeply integrated ke ops hotel — once on-platform sticky
- Data moat: anonymized market data unique
- Distribution moat: pSEO long-tail compounding
- Brand: "HotelHub" recognized dari boutique sampai chain

---

## 6. Macro tailwinds & headwinds

### Tailwinds
- Hotel SMB digitalization at early stage Indonesia (~30% pakai PMS modern)
- Tourism recovery post-pandemic strong (2024-2026)
- Government push: Indonesia 2045 target double international visitor
- Cloud adoption mainstream
- BYOK + AI shift in SaaS landscape (us already there)
- Compliance burden increasing (good for built-in compliance vendor)

### Headwinds
- OTA dominance (Booking, Agoda) keep margin pressure
- Cloud cost rising
- Talent competition with mega tech
- Regulation (UU PDP, Coretax migration) requires constant update
- Currency volatility (USD pricing exposure)

---

## 7. Strategic decisions to lock NOW

These decisions di awal compound effect besar di Year 5-10. Disepakati di tahap docs ini:

| Decision | Locked | Why matters Year 5-10 |
|---|---|---|
| Modular monolith (not microservice now) | ✅ | Cepat shipped, refactor di Year 3 saat butuh |
| DB-per-tenant SaaS | ✅ | Compliance + isolation = entry barrier untuk sell ke enterprise |
| BYOK for everything | ✅ | Future-proof + future fintech monetization (we don't lock our infra to providers) |
| pSEO mandatory | ✅ | Compounding distribution moat |
| Indonesia compliance built-in | ✅ | Hard for global player to replicate quickly |
| Open API & webhook from day 1 | ✅ | Marketplace play possible Year 5 |
| Audit log tamper-evident | ✅ | Enterprise + IPO-ready due diligence |
| Bilingual ID/EN from day 1 | ✅ | Reduces localization debt for ASEAN expand |
| License pairing v3 robust | ✅ | Standalone biz line surviv lifelong; revenue stream alongside SaaS |

---

## 8. Anti-patterns yang sudah dihindari sejak design phase

- ❌ Hardcoded providers — would require rip-replace later
- ❌ Single-DB shared schema — would block enterprise sales
- ❌ Subdomain-only routing — custom domain dari awal supported
- ❌ Cloud-only architecture — standalone path keeps dual revenue stream
- ❌ Vendor-locked storage — S3-compatible from day 1
- ❌ English-only — hambat domestic adoption
- ❌ Mockup screen tanpa modularity — would force rewrite at SaaS conversion

---

## 9. KPI dashboard (founder-level)

Tracking di year 1+:
- ARR + ARR growth
- Net Revenue Retention (NRR) — target ≥110%
- Gross margin SaaS — target 75%+
- CAC payback — target < 12 months
- Logo count (paying)
- Logo churn — target < 1.5% monthly
- Magic Number (sales efficiency)
- Geographic distribution
- Module attach rate (% pakai HK / POS / Accounting / Channel)

Reported monthly to founders + investors (kalau raised).

---

## 10. Decision triggers (hard gates)

Hanya escalate ke fase berikut kalau:
- **Ke Phase 2 (post-MVP):** ≥10 paying customer, NPS ≥30, churn < 3%
- **Raise seed:** ARR ≥ USD 500k, growth ≥ 15% MoM 6 months, conviction tim 5+
- **ASEAN expand:** Indonesia rank ≥ top 3 in SMB segment, ≥1.000 customer
- **Phase 3 fintech:** ≥5.000 customer, capital partner LOI'd
- **IPO prep:** ARR ≥ USD 200M, EBITDA positive 4 quarters

Below threshold: stay focused, don't expand prematurely.

---

## 11. The "kept simple" outcome

Kalau market berubah / strategi shift / founder lelah:

**Profitable lifestyle business** path:
- ARR Rp 30-50M sustainable
- Tim 5-10 orang
- Distribute profit ke founder
- Focus single market (Indonesia)
- No IPO, no exit, just steady cashflow

Architecture decisions di docs ini compatible dengan path ini juga — kita gak overbuild untuk skenario unicorn doang.

---

## 12. Closing note

Docs ini bukan komitmen apapun. Tujuannya: ketika di Year 3 muncul keputusan "do we expand to Vietnam?", referensi balik ke decision triggers di sini supaya tidak kebawa hype atau over-extend. Reality check tool, bukan rencana eksekusi.

> "The best decisions made early compound the longest. Pick architecture and pricing wisely; everything else is recoverable." — note untuk diri sendiri saat baca ulang Year 3.

---

## 13. Open questions

1. **Funding stance**: bootstrap (slower, more autonomy) vs raise (faster, dilution)? Default: bootstrap Year 1-2, evaluate Year 3.
2. **Geo first ASEAN**: Vietnam first (similar SMB landscape) atau Filipina (English-friendly)?
3. **Embedded fintech build vs partner**: build sendiri (need OJK license, slow) atau partner (faster, share margin)? Default: partner.
4. **B2C marketplace `hotelhub.id`** at what tenant count? Probably 5.000+ to have meaningful inventory.
5. **Open-source core, paid features**: hybrid model possible? Worth exploring Year 3-4 untuk distribution.
