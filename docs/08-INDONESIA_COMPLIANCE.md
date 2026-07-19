# 08 — Indonesia Compliance

> Pajak (PB1, PPN, e-Faktur Coretax), lapor WNA imigrasi, NPWP, KTP/Paspor handling, SIPGAR Kemenparekraf. Built-in, bukan add-on.

---

## 1. Pajak Hotel (PB1)

### Dasar hukum
- UU No. 1 Tahun 2022 (HKPD — Hubungan Keuangan antara Pemerintah Pusat dan Pemerintahan Daerah)
- Perda masing-masing kabupaten/kota
- Tarif default 10% — beberapa daerah berbeda (max 75% untuk hiburan)

### Implementasi

Tabel `pb1_rates`:
```
region_code (e.g. 'ID-JK', 'ID-BA-BD' Badung)
region_name
rate (default 10.00)
effective_from, effective_until
source_law (referensi Perda)
```

Setiap `properties` punya `region_code` → resolve PB1 rate dari tabel.

### Kalkulasi

```
Subtotal (room rate + extras kena pajak) × PB1 rate = PB1 amount
```

PB1 ditagih ke tamu, bukan biaya hotel. Display di folio:
```
Room charge          1,000,000
Service charge 10%      100,000
PB1 (tax) 10%           100,000
─────────────────────────────────
Total              Rp 1,200,000
```

### Reporting

Bulanan, hotel lapor PB1 ke Pemkab/Pemkot via SPTPD (Surat Pemberitahuan Pajak Daerah).

App generate:
- Daily PB1 report (untuk reconciliation)
- Monthly SPTPD format Excel/PDF (per format Pemda — banyak format, configurable template per region)
- e-SPTPD upload ke portal Pemda (kalau region support — Bali sudah ada Sistem Informasi Manajemen Pajak Daerah)

---

## 2. Service Charge

- **Bukan pajak**, melainkan biaya pelayanan yang ditagih ke tamu lalu didistribusikan ke karyawan
- Tarif konvensional 5-10% (per kebijakan hotel)
- Konfigurable per property
- Distribusi: traditional 80% ke karyawan, 20% ops; modernnya 100% ke karyawan
- App track total terkumpul + report distribusi ke karyawan (Phase 2 saat HR module aktif)

---

## 3. PPN (Pajak Pertambahan Nilai)

### Dua mode untuk hotel:

**A. PPN Final 0%** (Pajak Hotel)
- Hotel kena PB1 saja, **tidak kena PPN** karena hotel termasuk objek Pajak Daerah
- Mode ini default untuk MVP
- Field `properties.ppn_mode = 'final'` (interpret as: tidak charge PPN ke tamu)

**B. PPN Non-Final 11%** (untuk service di luar room: spa, restaurant tertentu)
- Restaurant/F&B yang **terpisah** dari penginapan bisa kena PPN 11% (kalau pengusaha kena pajak/PKP)
- Field `pos_outlets.ppn_mode = 'non_final'`

**C. PPN Exempt**
- UMKM dengan omzet < 4.8 milyar/tahun: tidak wajib PPN
- Field `ppn_mode = 'exempt'`

App handle multi-mode per outlet.

---

## 4. NPWP

### Wajib

- Hotel sebagai badan: NPWP badan hukum (input di `properties.npwp`)
- Karyawan: NPWP individu untuk PPh21 (Phase 2 HR)

### Optional di guest

- Tamu boleh request invoice atas nama corporate dengan NPWP
- Field `guests.npwp` + `companies.npwp`
- Saat invoice issued, kalau ada NPWP buyer, append ke invoice

---

## 5. e-Faktur Coretax (PPN penjualan)

### Sejak Januari 2025: Coretax DJP

Sebelumnya: e-Faktur desktop app DJP (`efaktur.exe`).
Sekarang: **Coretax** — web platform integrated, ada API.

### Use case di hotel

- Untuk hotel yang charge **PPN non-final** pada outlet F&B atau service
- Untuk corporate guests yang minta Faktur Pajak (mereka kreditkan)
- Untuk banquet/MICE event B2B

### Workflow

```
Reservation/POS order issued → 
    PPN non-final calculated → 
    Generate e-Faktur record (status='draft') → 
    Build XML per format DJP → 
    Submit ke Coretax API (with NSFP — Nomor Seri Faktur Pajak) → 
    Get response (approved with QR code) → 
    Status='approved', save QR + PDF → 
    Email PDF ke buyer
```

### Coretax API integration

- **Auth**: OAuth2 + certificate-based (DJP issue certificate per WP)
- **Base URL**: `https://coretaxdjp.pajak.go.id` (production), staging tersedia
- **Endpoints**:
  - `POST /api/efaktur/upload` — submit XML
  - `GET /api/efaktur/{nsfp}` — query status
  - `POST /api/efaktur/{nsfp}/cancel` — cancel
  - `GET /api/nsfp/range` — get NSFP range (jatah faktur)

### XML format

Per spesifikasi DJP. Field utama:
- Header: faktur number (NSFP), tanggal, NPWP penjual, nama penjual, alamat penjual
- Buyer: NPWP pembeli, nama, alamat, NIK (kalau bukan PKP)
- Detail items: nama barang, jumlah, harga, DPP, PPN, PPnBM (kalau ada)
- Total: total DPP, total PPN, total bayar

App generate XML, validate, submit, save response.

### Tabel `efaktur_records`

Sudah dideskripsikan di [`04-DATABASE_SCHEMA.md`](04-DATABASE_SCHEMA.md) §13.

### NSFP management

- Hotel request NSFP range dari DJP (Coretax) per bulan/kuartal
- App track NSFP yang sudah dipakai vs belum
- Alert kalau stok NSFP < 20%

---

## 6. Lapor Tamu Asing (WNA) ke Imigrasi

### Dasar hukum
- UU No. 6 Tahun 2011 tentang Keimigrasian
- PP No. 31 Tahun 2013 — kewajiban pemilik akomodasi melapor tamu asing dalam 24 jam

### Format lapor

Sistem **APOA (Aplikasi Pelaporan Orang Asing)** atau **APIPRO** (di beberapa daerah Bali). Format CSV/JSON.

Field wajib:
- Nama lengkap
- Tanggal lahir
- Jenis kelamin
- Kewarganegaraan
- Nomor paspor
- Tanggal terbit + tanggal expired paspor
- Jenis visa
- Tanggal masuk Indonesia
- Tujuan kunjungan
- Tanggal check-in + check-out
- Alamat akomodasi
- Nomor kamar

### Implementation

```
ReservationCheckedIn event (untuk WNA guest)
    ↓
Listener: TriggerLaporWnaListener
    ↓
Build payload from guest + reservation
Save to lapor_wna_records (status='pending')
    ↓
Submit to APOA API (atau email PDF kalau region tidak online)
    ↓
Status='submitted'
    ↓
Receive ack → status='accepted'
```

### Dashboard

- Daftar lapor pending / sukses / gagal
- Resubmit button
- Compliance KPI (target: 100% lapor dalam 24 jam check-in WNA)

---

## 7. KTP / Paspor Scan + OCR

### Saat check-in

```
FO clicks "Scan KTP" → camera HP / webcam open
    ↓
Capture image
    ↓
OCR pipeline:
  Primary: Tesseract local (gratis, offline)
  Fallback: Vision LLM BYOK (Gemini Flash, OpenAI Vision via OpenRouter)
    ↓
Parse fields: NIK, name, dob, address, gender
    ↓
Auto-fill registration form
    ↓
FO verify, edit kalau ada error, save
    ↓
Image stored: storage/app/private/guest/{id}/ktp.jpg (encrypted at rest, RBAC)
```

### Validasi NIK

- Length 16 digit
- Checksum berdasarkan provinsi/kabupaten/kecamatan code
- App tampilkan derived data (provinsi, kabupaten, kecamatan dari NIK) sebagai sanity check

### Paspor

- OCR MRZ (Machine Readable Zone bagian bawah)
- Parse: nationality, dokumen number, name, dob, expiry
- Validasi expiry — alert kalau expired atau < 6 bulan dari sekarang (banyak negara require 6-month rule)

---

## 8. SIPGAR Kemenparekraf (Phase 2)

Sistem Informasi Pariwisata dan Geospasial — Kemenparekraf collect statistik pengunjung akomodasi.

### Reporting bulanan
- Total tamu (domestik + asing)
- Total kamar terjual
- Average length of stay
- Pemecahan per nationality (asing)
- Pemecahan per purpose (leisure/business/MICE/health/edu)

### Implementation

- Aggregate dari `reservations` + `guest_stays`
- Generate format SIPGAR (XML atau XLS sesuai yang diminta)
- Submit via portal Kemenparekraf (manual upload by admin atau auto kalau API tersedia)

---

## 9. Pajak Restoran / Pajak Hiburan

Kalau hotel punya restoran terbuka untuk umum (bukan in-house guest only):
- **Pajak Restoran (PB1)**: 10% (mirip pajak hotel)
- Lapor terpisah ke Pemda

Kalau hotel punya spa/karaoke/diskotik (entertainment):
- **Pajak Hiburan**: tarif 25-75% tergantung jenis & daerah
- Configurable di `pos_outlets.tax_rate` + `tax_type`

App track terpisah, report SPTPD per kategori.

---

## 10. PPh 23 (Withholding Tax)

Saat hotel bayar agen / OTA / vendor:
- **PPh 23 jasa**: 2% × DPP (tergantung jenis jasa)
- **PPh 21 karyawan**: per pasal 21 — Phase 2 HR module

App support:
- AP bills mark as "subject to PPh 23"
- Auto-deduct saat pembayaran
- Generate Bukti Pemotongan (BP) format DJP
- Lapor masa via Coretax

---

## 11. Audit Trail untuk Pajak

Semua transaksi yang impact pajak harus auditable:
- Charge posted at __ by __ user
- PB1 calculated as __ from base __ at rate __
- e-Faktur issued / cancelled with reason __
- Refund processed (yang affect tax basis)

Audit log append-only, tidak bisa di-edit/delete (DB-level constraint).

Saat audit pajak (Pemkot, DJP), tampilkan trail untuk tiap transaksi.

---

## 12. Format Laporan PSAK

### Wajib (Phase 1)
- **Neraca** (Statement of Financial Position) — sesuai PSAK 1
- **Laba Rugi** (Income Statement) — multi-step
- **Trial Balance**
- **Daily Revenue Report (DRR)**
- **Aging AR/AP**

### Tambahan (Phase 2)
- **Arus Kas** (Cash Flow Statement) — direct & indirect method
- **Perubahan Ekuitas**
- **Catatan atas Laporan Keuangan** (notes)

### Chart of Accounts (USALI + PSAK adaptasi)

USALI (Uniform System of Accounts for Lodging Industry) basis internasional. Adaptasi:
- Asset: Cash, Bank, AR, Inventory, Prepaid, PPE
- Liability: AP, Tax payable (PB1, PPN), Loans, Deposit guests
- Equity: Capital, Retained earnings
- Revenue: Room, F&B (split outlet), MOD (Minor Operated Departments — spa, laundry, transport), Rental
- Expense: Cost of Sales (food, beverage), Operating expenses (per department), Salaries, Utilities, Marketing, Admin, Depreciation

Detail di [`09-ACCOUNTING.md`](09-ACCOUNTING.md).

---

## 13. Privacy & Data Protection (UU PDP)

UU 27/2022 — Pelindungan Data Pribadi. Wajib:
- **Informed consent** — saat tamu input data, tampilkan privacy notice
- **Right to access** — tamu boleh request data (via support channel)
- **Right to delete** — tamu boleh request hapus (selain yang wajib retain hukum, mis. tax records 10 tahun)
- **Breach notification** — kalau ada data breach, notif tamu + Kemenkominfo dalam 72 jam
- **Data Officer** — appointed by hotel (kalau >1000 record), self-declare

App provide:
- Privacy policy template (multi-bahasa)
- Consent checkbox di booking engine
- Guest data export (JSON/CSV) — sesuai request
- Guest data delete (anonymize tax records, hard-delete personal info elsewhere)
- Audit log untuk semua akses data tamu (siapa baca data siapa)
- Encryption at rest (KTP image, paspor image, NPWP)

---

## 14. Hotel License & Permits

App track expiry dokumen perijinan hotel:
- TDUP (Tanda Daftar Usaha Pariwisata)
- IUMK / NIB (Nomor Induk Berusaha)
- Sertifikat keselamatan kebakaran
- Sertifikat hygiene F&B
- HO (Izin Gangguan) — di beberapa daerah masih ada
- Pajak reklame (kalau ada papan nama)

Field di `properties` + alert 30/60/90 hari sebelum expired.

---

## 15. Compliance Dashboard

Single page admin → **Compliance**:

```
┌──────────────────────────────────────────────────────┐
│  Compliance Status — Hotel ABC                       │
├──────────────────────────────────────────────────────┤
│  ✅ PB1 SPTPD bulan ini        — submitted 2026-04-10│
│  ⚠️  Lapor WNA pending         — 3 records (>24 jam) │
│  ✅ e-Faktur stock              — 89 of 100 NSFP     │
│  ✅ KTP scan rate               — 98% last 30 days   │
│  ⚠️  TDUP expiry                — 45 days remaining  │
│  ✅ Privacy consent rate        — 100% bookings      │
└──────────────────────────────────────────────────────┘
```

Click each → drill-down + action.

---

## 16. Test Cases Critical

| # | Scenario | Expected |
|---|---|---|
| C1 | Reservation di Bali (Badung) | PB1 10% applied |
| C2 | Reservation WNA, check-in done | Auto-create lapor_wna record |
| C3 | F&B order (PKP) | PPN 11% on subtotal |
| C4 | Guest request invoice with NPWP | Invoice header includes NPWP |
| C5 | e-Faktur submit ke Coretax | XML validate + accept |
| C6 | NSFP exhausted | Alert + block new e-Faktur until refill |
| C7 | Night audit | DRR + tax summary generated |
| C8 | Refund partial | Tax basis recalc, e-Faktur cancellation if applicable |
| C9 | Guest data export request | ZIP with all guest data within 7 days |
| C10 | Privacy breach detected | Audit log + alert mechanism trigger |

Detail: [`21-QA_CHECKLIST.md`](21-QA_CHECKLIST.md).

---

## 17. References

- DJP Coretax: https://coretaxdjp.pajak.go.id
- UU PDP: https://peraturan.bpk.go.id/Details/229798/uu-no-27-tahun-2022
- Pajak Hotel (UU HKPD): https://peraturan.bpk.go.id/Details/195696/uu-no-1-tahun-2022
- APOA Imigrasi: https://apoa.imigrasi.go.id (region-dependent)
- SIPGAR Kemenparekraf: https://sipgar.kemenparekraf.go.id
- USALI Standards: https://www.ahla.com/usali
