# 09 — Accounting

> Modul akuntansi built-in: General Ledger, AR/AP, daily revenue posting, COA per-property, integrasi pajak (PB1, PPN, PPh), export ke software lokal (Coretax, Accurate, Jurnal, Mekari Talenta/Jurnal).

Tujuan: hotel bisa close-the-day & close-the-month tanpa software akuntansi terpisah, tetap kompatibel kalau owner sudah punya tools lain.

---

## 1. Scope

| Sub-modul | Phase | Catatan |
|---|---|---|
| Chart of Accounts (COA) per property | 🟢 MVP | Template SAK ETAP / SAK EMKM |
| General Ledger (jurnal otomatis) | 🟢 MVP | Posting real-time dari folio, FO, POS, payroll |
| Accounts Receivable (AR) | 🟢 MVP | City ledger, company billing, OTA receivable |
| Accounts Payable (AP) | 🟢 MVP | Supplier invoice, vendor payment |
| Daily revenue report | 🟢 MVP | Auto generate at night audit |
| Trial balance & Profit-Loss | 🟢 MVP | On-demand & monthly |
| Balance sheet | 🟡 Phase 2 | Required full accountant role |
| Bank reconciliation | 🟡 Phase 2 | CSV import, auto-match |
| Multi-currency | 🔵 Phase 3 | Foreign currency property |
| Fixed asset & depreciation | 🔵 Phase 3 | Tied ke HR/Asset module |
| Budget vs Actual | 🟡 Phase 2 | Variance report |
| Owner statement (untuk villa management) | 🟡 Phase 2 | Per-villa P&L untuk titip-kelola |

---

## 2. Chart of Accounts (COA)

### Prinsip

- **Per-property COA**, tapi seed dari template default (SAK ETAP) saat property dibuat.
- 5 digit code: `1-1010` (asset-cash-bank BCA, dst).
- Multi-level: `parent_id` self-reference.
- Owner bisa edit / tambah account, bukan kunci.

### Struktur default (seed)

| Code | Name | Type | Catatan |
|---|---|---|---|
| 1 | Aset | Header | |
| 1-1 | Aset Lancar | Header | |
| 1-1010 | Kas | Asset | Cash on hand |
| 1-1020 | Bank — BCA | Asset | Konfigurable |
| 1-1030 | Bank — Mandiri | Asset | |
| 1-1100 | Piutang Tamu (City Ledger) | Asset | Linked ke AR module |
| 1-1110 | Piutang OTA — Booking.com | Asset | |
| 1-1120 | Piutang OTA — Agoda | Asset | |
| 1-1200 | Persediaan F&B | Asset | Linked ke POS |
| 1-1210 | Persediaan Minibar | Asset | |
| 1-1300 | Pajak Dibayar Dimuka (PPN Masukan) | Asset | |
| 2 | Liabilitas | Header | |
| 2-1 | Liabilitas Lancar | Header | |
| 2-1010 | Hutang Usaha | Liability | |
| 2-1100 | Pajak PB1 Terhutang | Liability | Auto-credit per folio |
| 2-1110 | PPN Keluaran | Liability | |
| 2-1120 | PPh 21 Terhutang | Liability | Payroll |
| 2-1130 | PPh 23 Terhutang | Liability | Service charge to vendors |
| 2-1200 | Service Charge Terhutang ke Karyawan | Liability | Distribusi service |
| 2-1300 | Deposit Tamu | Liability | Refundable |
| 2-1400 | Pendapatan Diterima Dimuka | Liability | Advance booking |
| 3 | Ekuitas | Header | |
| 3-1010 | Modal Disetor | Equity | |
| 3-1020 | Laba Ditahan | Equity | |
| 4 | Pendapatan | Header | |
| 4-1010 | Pendapatan Kamar | Revenue | Auto-post night audit |
| 4-1020 | Pendapatan F&B | Revenue | From POS |
| 4-1030 | Pendapatan Minibar | Revenue | |
| 4-1040 | Pendapatan Laundry | Revenue | |
| 4-1050 | Pendapatan Spa | Revenue | Phase 2 |
| 4-1060 | Pendapatan Banquet | Revenue | Phase 2 |
| 4-1070 | Pendapatan Lain (transfer, tour, dll) | Revenue | |
| 4-2000 | Service Charge | Revenue | Pass-through |
| 5 | HPP / Cost of Sales | Header | |
| 5-1010 | HPP F&B | Expense | |
| 5-1020 | HPP Minibar | Expense | |
| 6 | Beban Operasional | Header | |
| 6-1010 | Gaji & Upah | Expense | Payroll |
| 6-1020 | Listrik | Expense | |
| 6-1030 | Air | Expense | |
| 6-1040 | Internet & Telp | Expense | |
| 6-1050 | Komisi OTA | Expense | Auto-post saat reservasi OTA |
| 6-1060 | Biaya Channel Manager | Expense | |
| 6-1070 | Biaya Payment Gateway | Expense | |
| 6-1080 | Pemeliharaan & Perbaikan | Expense | |
| 6-1090 | Marketing & Iklan | Expense | |
| 6-1100 | Perlengkapan Tamu (amenities) | Expense | |
| 6-1110 | Laundry Supplies | Expense | |
| 6-1200 | Penyusutan | Expense | Phase 3 |
| 7 | Beban Lain | Header | |
| 7-1010 | Pajak & Perizinan | Expense | |
| 7-1020 | Asuransi | Expense | |
| 7-1030 | Beban Bank | Expense | |

### Tabel

```
chart_of_accounts
├── id
├── property_id
├── code (5-digit, unique per property)
├── parent_id (nullable)
├── name
├── type ENUM('asset','liability','equity','revenue','expense','header')
├── normal_balance ENUM('debit','credit')
├── is_active
├── is_system (boolean — true = jangan di-edit/hapus, dipakai auto-posting)
├── description
└── timestamps
```

---

## 3. General Ledger (Auto Posting)

### Sumber jurnal otomatis

| Trigger | Source modul | Jurnal |
|---|---|---|
| Night audit | FO | DR: Piutang Tamu / Kas — CR: Pendapatan Kamar, Service Charge, PB1 Terhutang |
| Check-in deposit | FO | DR: Kas / Bank — CR: Deposit Tamu |
| Folio settlement (cash) | FO | DR: Kas — CR: Piutang Tamu |
| Folio settlement (card/QRIS) | FO | DR: Bank (less MDR) — CR: Piutang Tamu (+ Beban PG) |
| OTA reservation in | Channel Mgr | DR: Piutang OTA — CR: Pendapatan Kamar (+ Komisi OTA expense) |
| OTA payout received | AR | DR: Bank — CR: Piutang OTA |
| POS sale (F&B) | POS | DR: Kas / Folio — CR: Pendapatan F&B, PPN Keluaran |
| POS COGS | POS | DR: HPP F&B — CR: Persediaan F&B |
| Supplier invoice | AP | DR: Persediaan / Beban — CR: Hutang Usaha (+ PPN Masukan) |
| Supplier payment | AP | DR: Hutang Usaha — CR: Bank |
| Payroll | HR (P2) | DR: Gaji & Upah — CR: Bank, PPh 21, BPJS |
| Refund tamu | FO | DR: Deposit Tamu — CR: Kas |

Setiap auto-posting wajib di-tag dengan `source_type` + `source_id` untuk traceability.

### Tabel

```
journal_entries
├── id
├── property_id
├── entry_no (sequential YYYYMM-NNNN)
├── posted_at (datetime — actual posting time)
├── period_year, period_month
├── description
├── source_type (e.g. 'folio_settle','night_audit','ota_booking','manual','recurring')
├── source_id (polymorphic FK)
├── total_debit, total_credit (must be equal)
├── status ENUM('draft','posted','void')
├── created_by, posted_by, voided_by
└── timestamps

journal_lines
├── id
├── journal_entry_id
├── account_id (FK → chart_of_accounts)
├── description
├── debit, credit (one is 0)
├── tax_code (nullable — 'PPN_OUT', 'PB1', 'PPH23')
├── cost_center_id (nullable — Phase 2)
└── line_no
```

Validasi: `sum(debit) === sum(credit)` per entry, enforced di service.

---

## 4. Accounts Receivable

### Tipe AR

1. **Guest in-house** — folio belum settle (residen aktif)
2. **City ledger** — guest sudah check-out tapi billing ke company / agent
3. **OTA receivable** — payout dari Booking.com / Agoda / Traveloka (paid-by-OTA model)
4. **Voucher / gift card outstanding**

### Aging report

`Current | 1-30 | 31-60 | 61-90 | >90 days`

### Workflow city ledger

```
Check-out → folio belum bayar → transfer ke city ledger account (per company)
→ generate invoice → kirim ke company → payment received → close
```

### Tabel

```
ar_accounts
├── id, property_id
├── account_type (city_ledger | ota | guest)
├── company_id / ota_id / guest_id
├── credit_limit
├── balance_cached (denormalized untuk speed)
├── payment_terms_days
└── timestamps

ar_invoices
├── id, ar_account_id
├── invoice_no (per format property: HTL/2026/0001)
├── issued_at, due_at
├── subtotal, tax_total, grand_total
├── paid_total, balance
├── status (open | partial | paid | overdue | void)
└── timestamps

ar_invoice_lines (description, qty, unit_price, tax)
ar_payments (FK invoice, amount, method, ref_no, journal_entry_id)
```

---

## 5. Accounts Payable

Mirror dari AR untuk supplier:

```
ap_suppliers (master vendor)
ap_bills (invoice masuk dari supplier)
ap_bill_lines
ap_payments
```

Workflow: bill received → approval (jika nominal > threshold) → schedule payment → bayar → posting jurnal.

PPh 23 atas jasa supplier (final 2% / non-final) auto-calculated saat tipe expense = service.

---

## 6. Daily Revenue Report (Night Audit Output)

Generated otomatis saat night audit closes. Field utama:

```
property_id, date
─────────────────────────────────
ROOMS
  rooms_available
  rooms_sold
  rooms_complimentary
  rooms_house_use
  rooms_out_of_order
  occupancy_pct
  adr (avg daily rate)
  revpar (revenue per available room)
  room_revenue_gross
  service_charge
  pb1_amount
  room_revenue_net

F&B
  fnb_revenue (resto + minibar + room service)
  fnb_covers (jumlah pelanggan)
  apc (avg per cover)

OTHER
  laundry_revenue
  spa_revenue (P2)
  other_revenue

TOTAL DAILY REVENUE
TOTAL TAX (PB1 + PPN + Service)
NET COLLECTED CASH / CARD / OTA / CITY-LEDGER
```

Format: dashboard view + PDF + auto-email ke owner.

---

## 7. Tax Integration

### PB1 / PHR

Auto-credit ke akun `2-1100 PB1 Terhutang` saat night audit. Tarif sesuai region (lihat 08-INDONESIA_COMPLIANCE.md).

### PPN

Auto-credit ke `2-1110 PPN Keluaran` untuk transaksi yang kena PPN (umumnya F&B di hotel besar yang sudah PKP, dan jasa katering banquet). Hotel kecil non-PKP tinggal disable di property setting.

PPN Masukan dari supplier bills auto-debit ke `1-1300`.

### PPh 21

Phase 2 saat HR module aktif. Per slip gaji: DR Gaji — CR Bank, PPh 21 Terhutang, BPJS Kesehatan, BPJS TK.

### PPh 23

Atas service charge ke vendor (tour guide, freelance, jasa) — auto-withhold saat AP bill dibayar.

### e-Faktur Coretax

Lihat 08-INDONESIA_COMPLIANCE.md. Service `CoretaxClient` push faktur via API.

---

## 8. Closing & Reporting

### Daily close (night audit)

- Lock semua transaksi tanggal berjalan
- Generate daily revenue report
- Roll over occupancy, room status
- Post recurring jurnal (rent if rented, etc.)

### Monthly close

- Reconcile AR aging
- Reconcile AP
- Bank reconciliation (semi-manual)
- Post adjusting entries (penyusutan saat Phase 3)
- Generate trial balance, P&L
- Lock period — `accounting_periods.status = 'locked'`

Posting ke periode locked harus permission `accounting.unlock_period`.

### Year-end close

- Close revenue/expense ke laba ditahan
- Generate annual P&L, balance sheet
- Export ke akuntan untuk SPT Tahunan badan

---

## 9. Reports (Built-in)

| Report | Frequency | Format |
|---|---|---|
| Daily Revenue Report | Daily (night audit) | PDF, Excel, dashboard |
| Cashier Shift Report | Per shift | PDF |
| Trial Balance | On-demand / monthly | PDF, Excel |
| General Ledger Detail | On-demand | Excel |
| AR Aging | Weekly / on-demand | PDF, Excel |
| AP Aging | Weekly / on-demand | PDF, Excel |
| P&L Statement | Monthly / on-demand | PDF, Excel |
| Balance Sheet (P2) | Monthly | PDF |
| Tax Summary (PB1, PPN, PPh) | Monthly | Excel + format SPTPD/SPT-PPN |
| OTA Reconciliation | Per payout | Excel |
| Channel Production | Monthly | PDF |
| Owner Statement (P2) | Monthly | PDF per villa/owner |

---

## 10. Export ke Software Eksternal

User bebas pilih (BYOK style — semua format). Adapter format-based:

| Target | Format | Catatan |
|---|---|---|
| **Accurate Online** | CSV mapping akun + import endpoint | Banyak hotel kecil pakai |
| **Mekari Jurnal** | CSV / API public | Modern, populer SMB |
| **Zahir** | CSV | Legacy, tetap dipakai |
| **Coretax DJP** | API (PPN, PPh) | Compliance |
| **Excel template generic** | XLSX | Fallback universal |
| **CSV generic** | CSV | Fallback universal |

User config mapping akun lokal ↔ akun di software target di admin UI.

```
external_accounting_mappings
├── id, property_id
├── target_software (accurate | jurnal | zahir | coretax | csv)
├── local_account_id (FK chart_of_accounts)
├── external_account_code
├── external_account_name
└── timestamps
```

Cron `accounting:export-daily` push ke target sesuai schedule owner (real-time / hourly / daily / manual).

**No hardcoded vendor adapter** — semua via universal CSV + per-target mapping. Untuk Coretax tetap direct API karena format DJP fixed.

---

## 11. Multi-property handling (SaaS mode)

- Setiap tenant (property) punya COA sendiri di db tenant.
- Tidak ada konsolidasi cross-property di Phase 1 (owner yang punya 3 hotel cetak P&L per property terpisah).
- **Phase 3:** consolidated P&L untuk grup hotel (owner statement multi-property) — opsional add-on.

---

## 12. Audit Trail

Setiap journal entry, AR/AP transaksi, COA edit terekam di `audit_logs`:

- who, when, what action
- before / after JSON snapshot
- IP, user agent

Retention: 10 tahun (UU Pajak Indonesia minimum 10 tahun untuk dokumen pembukuan).

---

## 13. Permissions

| Role | Capability |
|---|---|
| Cashier | Read folio, post payments |
| Front Office | Post folio charges, run night audit |
| Accountant | Full GL, AR, AP, COA edit, monthly close, period unlock |
| Manager | Read all reports, approve AP > threshold |
| Auditor (read-only) | Read GL, reports, audit log |
| Owner | Read P&L, balance, dashboards |

---

## 14. Open Questions

1. **Revenue recognition**: tagih saat reservasi dibuat atau saat tamu check-out? Default: cash basis (saat settle); akrual basis sebagai opsi.
2. **Accrual untuk advance booking**: butuh rekognisi pendapatan diterima dimuka saat booking confirmed-prepaid?
3. **Service charge distribution scheduler**: bulanan (umum) atau per-shift?
4. **Cost center per outlet** (resto, bar, spa) — Phase 2 atau Phase 3?
