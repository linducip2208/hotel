# 01 — Features

> Daftar lengkap fitur per modul. Skala MVP (Phase 1) vs advanced (Phase 2+).

Legend: 🟢 = MVP Phase 1 · 🟡 = Phase 2 · 🔵 = Phase 3+

---

## Modul 1 — Front Office (FO)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 1.1 | Reservation create / edit / cancel | 🟢 | Multi-room, multi-night |
| 1.2 | Walk-in registration | 🟢 | Quick form |
| 1.3 | Group booking | 🟢 | Block N rooms 1 master folio |
| 1.4 | Room blocking (allotment) | 🟢 | Per company / agent |
| 1.5 | Reservation grid (drag-drop calendar) | 🟢 | Tape chart by room number |
| 1.6 | Availability search | 🟢 | Filter by date, room type, occupancy |
| 1.7 | Auto-assign room (rule-based) | 🟢 | First available, prefer floor, dll |
| 1.8 | Manual room move / share-with | 🟢 | |
| 1.9 | Extend stay / early checkout | 🟢 | Auto pro-rate folio |
| 1.10 | No-show & cancel penalty rules | 🟢 | Configurable per rate plan |
| 1.11 | Waitlist | 🟡 | Auto-notify saat available |
| 1.12 | Reservation confirmation email/WA | 🟢 | Template-based, multi-bahasa |
| 1.13 | KTP/Paspor scan + OCR | 🟢 | Camera HP, auto-fill registration |
| 1.14 | E-registration card (digital) | 🟢 | Tamu sign di tablet |
| 1.15 | Guest history search | 🟢 | Repeat guest detection |
| 1.16 | Folio (charges + payments) | 🟢 | Split folio, transfer charges |
| 1.17 | Cashier shift open/close | 🟢 | Cash tray reconciliation |
| 1.18 | Night audit | 🟢 | Auto post room charge, close day |
| 1.19 | Daily flash report | 🟢 | Occupancy, revenue, sumber, segmen |
| 1.20 | Voucher / package / add-on | 🟢 | Breakfast, transfer, late checkout |

---

## Modul 2 — Channel Manager

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 2.1 | Booking.com integration | 🟢 | XML push/pull |
| 2.2 | Agoda integration | 🟢 | YCS API |
| 2.3 | Traveloka integration | 🟢 | TPI/Traveloka Hotels API |
| 2.4 | Tiket.com integration | 🟡 | Direct API |
| 2.5 | Mister Aladin integration | 🟡 | |
| 2.6 | Pegipegi integration | 🟡 | |
| 2.7 | Expedia integration | 🟡 | EQC |
| 2.8 | Airbnb integration | 🟡 | |
| 2.9 | Trip.com integration | 🟡 | |
| 2.10 | ARI sync (Availability/Rate/Inventory) | 🟢 | 2-way, queue-backed |
| 2.11 | Booking ingest dari OTA | 🟢 | Webhook + polling fallback |
| 2.12 | Restrictions: CTA, CTD, MinLOS, MaxLOS | 🟢 | Per-OTA |
| 2.13 | Derived rates | 🟢 | "BAR + 10%" untuk OTA tertentu |
| 2.14 | Rate parity check | 🟡 | Alert kalau ada divergensi |
| 2.15 | Mapping room type ↔ OTA | 🟢 | UI drag-drop |
| 2.16 | Conflict resolution UI | 🟢 | Saat OTA & PMS bertabrakan |
| 2.17 | Channel mix report | 🟢 | Source production |

---

## Modul 3 — Booking Engine (Direct Web)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 3.1 | Public website hotel (template) | 🟢 | Multi-bahasa ID/EN |
| 3.2 | Room search widget | 🟢 | Embed di iframe atau native |
| 3.3 | Real-time availability + price | 🟢 | Best Available Rate (BAR) |
| 3.4 | Multi-room booking flow | 🟢 | |
| 3.5 | Promo code | 🟢 | Configurable rules |
| 3.6 | Package (room + breakfast + spa, dll) | 🟢 | |
| 3.7 | Add-on (transfer, late checkout, extra bed) | 🟢 | |
| 3.8 | Payment gateway (BYOK Indonesia) | 🟢 | Midtrans/Xendit/DOKU/QRIS |
| 3.9 | Confirmation email + WhatsApp | 🟢 | |
| 3.10 | Abandon cart recovery | 🟡 | Email reminder + diskon |
| 3.11 | Guest review post-stay | 🟡 | |
| 3.12 | Loyalty member rate | 🟡 | |
| 3.13 | Direct booking widget di Instagram/FB | 🔵 | Meta Catalog integration |

---

## Modul 4 — Housekeeping

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 4.1 | Status real-time (clean/dirty/inspected/OOO/OOS) | 🟢 | |
| 4.2 | Mobile interface untuk housekeeper | 🟢 | PWA |
| 4.3 | Task assignment per maid | 🟢 | |
| 4.4 | Daily forecast workload | 🟡 | |
| 4.5 | Lost & found | 🟢 | Photo + description |
| 4.6 | Maintenance work order | 🟡 | Linked to room status |
| 4.7 | Linen & supply tracker | 🟡 | |
| 4.8 | Inspection checklist | 🟡 | Photo evidence |

---

## Modul 5 — POS (Multi-outlet)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 5.1 | Restaurant POS (touch-friendly) | 🟢 | Tablet/iPad mode |
| 5.2 | Bar POS | 🟢 | |
| 5.3 | Spa / activity POS | 🟡 | Therapist scheduling |
| 5.4 | Laundry POS | 🟡 | |
| 5.5 | Minibar posting | 🟢 | Cepat input dari HK mobile |
| 5.6 | Charge to room (auto folio post) | 🟢 | |
| 5.7 | Split bill | 🟢 | |
| 5.8 | Discount, void, refund | 🟢 | Audit log |
| 5.9 | Table management (denah) | 🟢 | |
| 5.10 | Kitchen Display System (KDS) | 🟡 | |
| 5.11 | Recipe / BOM costing | 🟡 | F&B margin |
| 5.12 | Receipt thermal printer | 🟢 | ESC/POS |
| 5.13 | QR menu (guest scan from table) | 🟡 | |

---

## Modul 6 — Guest CRM

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 6.1 | Guest profile (preferences, allergies, notes) | 🟢 | |
| 6.2 | Stay history | 🟢 | |
| 6.3 | Birthday & anniversary tracker | 🟢 | Auto-greeting |
| 6.4 | Segmentation (VIP, corporate, OTA, walk-in) | 🟢 | |
| 6.5 | Blacklist | 🟢 | Cross-property warning |
| 6.6 | Marketing email campaign | 🟡 | Template + scheduling |
| 6.7 | WhatsApp broadcast | 🟡 | BYOK Twilio/Wati/Meta |
| 6.8 | Guest portal (self-service) | 🟡 | View bookings, request services |

---

## Modul 7 — Reporting & Analytics

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 7.1 | Daily flash report | 🟢 | |
| 7.2 | Occupancy / ADR / RevPAR / GOPPAR | 🟢 | |
| 7.3 | Source production | 🟢 | OTA, direct, walk-in, agent |
| 7.4 | Market segment | 🟢 | Corporate, leisure, group, MICE |
| 7.5 | Forecast (occupancy + revenue) | 🟡 | Rolling 30/60/90 day |
| 7.6 | Competitor rate shopping | 🟡 | BYOK rate shopper API |
| 7.7 | Guest demographics | 🟢 | |
| 7.8 | Custom report builder | 🔵 | SQL + visualization |
| 7.9 | Export Excel/PDF | 🟢 | Semua report |
| 7.10 | Scheduled email digest (weekly/monthly) | 🟡 | |

---

## Modul 8 — Payments & Folio

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 8.1 | Payment BYOK Indonesia | 🟢 | Midtrans, Xendit, DOKU, iPaymu, Faspay, etc. |
| 8.2 | QRIS dynamic | 🟢 | |
| 8.3 | E-wallet (GoPay, OVO, Dana, ShopeePay, LinkAja) | 🟢 | |
| 8.4 | Virtual Account (BCA, Mandiri, BNI, BRI, Permata) | 🟢 | |
| 8.5 | Credit card (Visa/Master/JCB/Amex) | 🟢 | |
| 8.6 | Cash | 🟢 | |
| 8.7 | Bank transfer manual + auto-match | 🟢 | |
| 8.8 | Pre-authorization (hold) | 🟡 | |
| 8.9 | Refund | 🟢 | Audit + approval flow |
| 8.10 | Deposit / down payment scheme | 🟢 | Configurable per rate plan |
| 8.11 | Foreign currency display + conversion | 🟡 | |
| 8.12 | Tokenization (re-charge tanpa input ulang) | 🟡 | |

---

## Modul 9 — Indonesia Compliance

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 9.1 | PB1 (pajak hotel daerah) per kabupaten | 🟢 | Rate configurable, default 10% |
| 9.2 | Service charge configurable | 🟢 | Default 5-10% |
| 9.3 | PPN (final 0% atau non-final 11%) | 🟢 | Per kebijakan hotel |
| 9.4 | NPWP guest opsional | 🟢 | |
| 9.5 | e-Faktur Coretax XML export | 🟢 | Per format DJP terbaru |
| 9.6 | Lapor WNA imigrasi (LAPOR PASPOR) | 🟢 | Auto generate dari check-in WNA |
| 9.7 | KTP/Paspor scan + OCR | 🟢 | |
| 9.8 | SIPGAR Kemenparekraf reporting | 🟡 | Visitor stats |
| 9.9 | Kop surat invoice resmi (sesuai DJP) | 🟢 | |
| 9.10 | Format laporan PSAK Indonesia | 🟢 | |

---

## Modul 10 — Akuntansi

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 10.1 | Chart of Accounts (USALI-inspired + PSAK) | 🟢 | |
| 10.2 | General Ledger | 🟢 | |
| 10.3 | AR (Accounts Receivable) — city ledger | 🟢 | Corporate, agent, OTA |
| 10.4 | AP (Accounts Payable) | 🟢 | Vendor billing |
| 10.5 | Journal entry (auto + manual) | 🟢 | |
| 10.6 | Trial balance | 🟢 | |
| 10.7 | Balance sheet (neraca) | 🟢 | |
| 10.8 | Income statement (laba-rugi) | 🟢 | |
| 10.9 | Cash flow | 🟡 | |
| 10.10 | Aging report (AR/AP) | 🟢 | |
| 10.11 | Bank reconciliation | 🟡 | |
| 10.12 | Multi-currency support | 🟡 | |
| 10.13 | Daily revenue report (DRR) | 🟢 | |
| 10.14 | Auto-posting dari operasi (FO, POS) | 🟢 | Event-driven |

---

## Modul 11 — Revenue Management

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 11.1 | Rate plan management | 🟢 | BAR, weekday/weekend, seasonal |
| 11.2 | Yield rules (occupancy-based pricing) | 🟡 | |
| 11.3 | Demand forecasting | 🟡 | AI BYOK |
| 11.4 | Competitor rate shopping integration | 🟡 | BYOK API |
| 11.5 | Auto-suggest rate adjustment | 🟡 | AI |
| 11.6 | Promo & discount engine | 🟢 | |
| 11.7 | Rate calendar (12-month view) | 🟢 | |

---

## Modul 12 — Loyalty & Marketing

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 12.1 | Loyalty points engine | 🟡 | |
| 12.2 | Tier (Bronze/Silver/Gold/Platinum) | 🟡 | |
| 12.3 | Member-only rate | 🟡 | |
| 12.4 | Referral program | 🟡 | |
| 12.5 | Email campaign | 🟡 | |
| 12.6 | WhatsApp broadcast | 🟡 | |
| 12.7 | Guest review aggregation (Booking, Agoda, Google) | 🔵 | Sentiment AI |
| 12.8 | AI auto-reply review | 🔵 | BYOK LLM |

---

## Modul 13 — AI Tools (BYOK)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 13.1 | AI Concierge chatbot tamu (multi-bahasa) | 🟡 | |
| 13.2 | Auto-translate room descriptions | 🟢 | |
| 13.3 | Auto-generate pSEO content (FAQ, descriptions) | 🟢 | |
| 13.4 | Smart email/WA reply suggestion | 🟡 | |
| 13.5 | Sentiment analysis review | 🔵 | |
| 13.6 | Demand forecasting | 🟡 | |
| 13.7 | KTP/Paspor OCR (vision LLM) | 🟢 | Fallback ke Tesseract local |
| 13.8 | Voice-to-reservation (call center) | 🔵 | Whisper + LLM |

Lihat [`05-AI_PROVIDERS.md`](05-AI_PROVIDERS.md) untuk 11 preset BYOK.

---

## Modul 14 — Programmatic SEO

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 14.1 | `/hotels-in-{city}` template | 🟢 | |
| 14.2 | `/best-hotels-in-{city}-{year}` | 🟢 | |
| 14.3 | `/hotels-near-{landmark}-{city}` | 🟢 | |
| 14.4 | `/cheap-hotels-{city}-under-{price}` | 🟢 | |
| 14.5 | `/wedding-venues-{city}` | 🟡 | |
| 14.6 | `/business-hotels-{city}` | 🟡 | |
| 14.7 | `/things-to-do-in-{city}` | 🟡 | |
| 14.8 | `/compare/{a}-vs-{b}` | 🟢 | |
| 14.9 | JSON-LD schema (Hotel, LodgingBusiness, FAQPage, ItemList) | 🟢 | |
| 14.10 | Dynamic sitemap.xml | 🟢 | |
| 14.11 | robots.txt config | 🟢 | |
| 14.12 | Auto submit ke IndexNow / Bing | 🟡 | |

Detail di [`10-PSEO_STRATEGY.md`](10-PSEO_STRATEGY.md).

---

## Modul 15 — Banquet & MICE (Phase 2)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 15.1 | Function diary | 🟡 | Calendar event hall |
| 15.2 | BEO (Banquet Event Order) | 🟡 | Full setup spec |
| 15.3 | F&B forecast per event | 🟡 | |
| 15.4 | AV equipment tracking | 🟡 | |
| 15.5 | Wedding package builder | 🟡 | |

---

## Modul 16 — Spa & Activity (Phase 2)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 16.1 | Therapist schedule | 🟡 | |
| 16.2 | Treatment room availability | 🟡 | |
| 16.3 | Service package | 🟡 | |
| 16.4 | Online booking spa | 🟡 | |

---

## Modul 17 — HR & Payroll (Phase 2)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 17.1 | Employee master | 🟡 | |
| 17.2 | Schedule (shift management) | 🟡 | |
| 17.3 | Attendance (mobile clock-in/out) | 🟡 | |
| 17.4 | Payroll (gaji, tunjangan, lembur) | 🟡 | |
| 17.5 | BPJS Kesehatan & Ketenagakerjaan | 🟡 | |
| 17.6 | PPh21 calculation | 🟡 | |
| 17.7 | Slip gaji digital | 🟡 | |

---

## Modul 18 — Inventory & Purchasing (Phase 2)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 18.1 | Stock card per item | 🟡 | |
| 18.2 | Purchase Request (PR) | 🟡 | |
| 18.3 | Purchase Order (PO) | 🟡 | |
| 18.4 | Goods Receipt (GR) | 🟡 | |
| 18.5 | Vendor master | 🟡 | |
| 18.6 | Stock opname | 🟡 | |
| 18.7 | Recipe/BOM linkage ke POS | 🟡 | F&B costing |
| 18.8 | Multi-warehouse | 🟡 | Per outlet |

---

## Modul 19 — Door Lock Integration (Phase 2)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 19.1 | Salto integration | 🟡 | |
| 19.2 | Onity integration | 🟡 | |
| 19.3 | Vingcard / Assa Abloy integration | 🟡 | |
| 19.4 | Dormakaba | 🟡 | |
| 19.5 | MIWA (Jepang, popular di Bali) | 🟡 | |
| 19.6 | Mobile key (BLE) | 🔵 | |
| 19.7 | QR code key | 🟡 | |

---

## Modul 20 — Online Check-in / Kiosk (Phase 2)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 20.1 | Pre-check-in via mobile (24-48h sebelum arrival) | 🟡 | |
| 20.2 | E-signature registration card | 🟡 | |
| 20.3 | KTP/Paspor upload | 🟡 | |
| 20.4 | Payment / pre-auth balance | 🟡 | |
| 20.5 | Kiosk mode (iPad lobby) | 🟡 | |
| 20.6 | QR code → digital key | 🔵 | |

---

## Modul 21 — Multi-Property (Phase 2)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 21.1 | Group HQ dashboard | 🟡 | |
| 21.2 | Cross-property reporting | 🟡 | |
| 21.3 | Central rate management | 🟡 | |
| 21.4 | Inter-property guest profile | 🟡 | |
| 21.5 | Central guest blacklist | 🟡 | |

---

## Modul 22 — White-Label (Add-on)

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 22.1 | Custom logo + warna | 🟢 | |
| 22.2 | Custom domain | 🟢 | |
| 22.3 | Custom email sender | 🟢 | |
| 22.4 | Hilangkan attribution | 🟢 | Add-on berbayar |
| 22.5 | Reseller portal (Phase 3) | 🔵 | Multi-client management |

---

## Modul 23 — Mobile Apps

| # | Fitur | Phase | Catatan |
|---|---|---|---|
| 23.1 | Staff PWA (housekeeping, FO supervisor) | 🟢 | |
| 23.2 | Guest PWA (booking, online check-in) | 🟡 | |
| 23.3 | Native app iOS/Android | 🔵 | Capacitor wrap PWA |

---

## Total Fitur

| Phase | Jumlah fitur |
|---|---|
| 🟢 MVP Phase 1 | ±90 |
| 🟡 Phase 2 | ±70 |
| 🔵 Phase 3+ | ±20 |
| **Total** | **±180 fitur** |

---

## Comparison cepat dengan kompetitor

| Fitur | Kita 🟢 | Cloudbeds | Mews | Oracle OPERA | eZee | VHP |
|---|---|---|---|---|---|---|
| Channel Manager OTA Indonesia | ✅ Native | Limited | Limited | ❌ | ⚠️ | ⚠️ |
| BYOK Payment Indonesia | ✅ | ❌ Lock-in | ❌ Lock-in | ⚠️ | ⚠️ | ⚠️ |
| PB1 + e-Faktur Coretax | ✅ Native | ❌ | ❌ | ❌ | ⚠️ | ✅ |
| Lapor WNA imigrasi | ✅ | ❌ | ❌ | ❌ | ⚠️ | ✅ |
| Akuntansi GL/AR/AP | ✅ | ❌ Integration | ❌ Integration | ✅ | ⚠️ | ✅ |
| Programmatic SEO | ✅ Built-in | ❌ | ❌ | ❌ | ❌ | ❌ |
| AI BYOK 11 providers | ✅ | ⚠️ Some | ⚠️ Some | ⚠️ | ❌ | ❌ |
| Modern UX (mobile-first) | ✅ | ✅ | ✅ | ⚠️ | ❌ | ❌ |
| Standalone install | ✅ | ❌ Cloud only | ❌ Cloud only | ⚠️ Hybrid | ⚠️ | ✅ |
| White-label | ✅ Add-on | ❌ | Limited | ⚠️ | ❌ | ⚠️ |

✅ = full · ⚠️ = partial / via integration · ❌ = none / very limited
