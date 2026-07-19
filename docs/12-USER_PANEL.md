# 12 — User Panel (Hotel Staff Side)

> Panel utama untuk pengguna sehari-hari: front office, housekeeping, kasir, F&B server, manager, accountant, owner. Modular, role-based, mobile-first untuk role yang lapangan.

URL convention: `/panel` (atau root `/` setelah login untuk single-property standalone). SaaS: `{tenant-slug}.hotelhub.id/panel` atau custom domain.

---

## 1. Roles default

| Role | Panel section | Mobile-first? | 2FA wajib? |
|---|---|---|---|
| Super Owner | Full + admin tools | No | Yes |
| Manager | Full read, edit op | No | Yes |
| Front Office | FO, reservasi, folio | Yes (tablet) | No |
| Cashier | Folio + payment posting | Yes | No |
| Housekeeping | Room status, task | Yes (mobile) | No |
| Engineer / Maintenance | Work order | Yes | No |
| F&B Server | POS | Yes (tablet) | No |
| F&B Cashier | POS settle | Yes | No |
| Accountant | GL, AR, AP, report | No | Yes |
| Auditor (read-only) | Reports | No | No |
| HR (P2) | Payroll, attendance | No | Yes |
| Sales | Company, contract, group block | No | No |
| Revenue Manager (P2) | Rate, restriction, demand | No | No |
| Marketing | pSEO, promo, email | No | No |
| IT Admin | Settings, integrations, user mgmt | No | Yes |

User punya 1 atau lebih role. Permission resolved via union.

Custom role bisa dibuat owner di "Settings → Roles".

---

## 2. Layout dasar

```
[Top Bar]
  [Logo] [Property switcher (kalau owner punya >1 prop)] [Date toggle (sistem tanggal operasi)]
  [Search box] [Notification bell] [User profile menu]

[Sidebar (collapsible)]
  Modul list — disembunyikan kalau role gak punya akses

[Main Content]
  Breadcrumb
  Page header dengan action buttons
  Konten halaman
```

Color coding cepat (sticker per modul):
- 🔵 FO — biru
- 🟢 Housekeeping — hijau
- 🟠 POS — oranye
- 🟣 Accounting — ungu
- 🔴 Engineering — merah
- ⚫ Setting / Admin — abu

---

## 3. Sidebar / navigation full (super owner view)

```
🏠 Dashboard
📅 Front Office
   ├── Reservation calendar
   ├── New reservation
   ├── Walk-in
   ├── Arrivals today
   ├── Departures today
   ├── In-house guests
   ├── Folio
   ├── Group block
   ├── Waitlist
   ├── Room move
   └── Night audit

🛏 Housekeeping
   ├── Room status board
   ├── Task assignment
   ├── Lost & found
   ├── Linen inventory
   └── Inspection log

🔧 Engineering
   ├── Work orders
   ├── Preventive maintenance
   ├── Asset register (P3)
   └── Out of order rooms

🍽 POS
   ├── Outlets
   ├── Open tables
   ├── Menu
   ├── Inventory
   ├── Daily sales
   └── Recipe & cost (P2)

💼 Sales & Marketing
   ├── Companies
   ├── Travel agents
   ├── Contracts
   ├── Promo codes
   ├── Email campaigns
   ├── pSEO pages
   └── Reviews

🌐 Channel Manager
   ├── Connected OTAs
   ├── Mapping
   ├── Rate plans
   ├── Restrictions calendar
   ├── ARI sync log
   └── Conflict resolution

💰 Accounting
   ├── Chart of Accounts
   ├── Journal entries
   ├── AR (city ledger, OTA)
   ├── AP (suppliers)
   ├── Reports (P&L, TB, etc)
   ├── Tax (PB1, PPN, PPh)
   └── Period close

📊 Reports
   ├── Daily revenue
   ├── Forecast (P2 — RMS)
   ├── Channel production
   ├── Source of business
   ├── Guest demographics
   ├── Cashier shift
   └── Custom report builder (P3)

🤝 Guest Experience
   ├── Reviews & feedback
   ├── Loyalty program (P2)
   ├── Communications log
   └── Service request

👥 HR (P2)
   ├── Employees
   ├── Attendance
   ├── Schedule
   ├── Payroll
   └── Service charge distribution

⚙️ Settings
   ├── Property profile
   ├── Room types & rates
   ├── Tax config (PB1, region, PPN)
   ├── Users & roles
   ├── Integrations (BYOK config)
   ├── Email templates
   ├── Theme & branding (booking engine)
   ├── Document templates (folio, invoice)
   ├── Currency & locale
   └── Audit log
```

---

## 4. Dashboard (default landing)

Widget per role:

### Owner / Manager
- Today's KPI: occupancy, ADR, RevPAR, revenue
- Arrivals / departures count
- Pending payment / outstanding balance
- Channel mix (pie)
- 7/14/30-day trend
- Quick alert list (overbooking, OOO, payment failed)

### Front Office
- Arrivals list (with action: pre-check-in)
- In-house list (request flag)
- Departures list (with action: settle)
- Pending requests (group, waitlist, room move)
- Cashier shift status

### Housekeeping
- Rooms board: clean / dirty / inspected / OOO
- Today's checkout list
- Today's checkin list
- My assigned tasks (mobile)
- Lost & found alerts

### Cashier
- Open shift status + cash drawer balance
- Pending settlements
- Today's payment summary by method

### Accountant
- Period status (open / locked)
- Pending journal review
- AR aging summary
- AP due this week

---

## 5. Mobile-first sub-panels

### Housekeeping mobile (PWA)

- Login → list room di-assign hari ini
- Tap room → status update flow: arrive → start cleaning → finish → inspect
- Photo capture untuk damage report
- Voice note untuk lost & found
- Offline-first: queue sync saat online

### POS tablet

- Outlet pick → table grid
- Order entry: menu category → item → modifier → send to kitchen (printer)
- Bill split, transfer, void (permission-gated)
- Room charge: search guest by name/room → post to folio
- Settle: cash, card, QRIS, voucher

### Engineering mobile

- Work order list assigned to me
- Status: pending → in-progress → done → verified
- Photo before/after
- Material used (auto-deduct from inventory)

---

## 6. Search

Global search bar (top bar):
- "200" → ambiguous: room 200, reservation 200, invoice 200 → result tabs
- "John" → guest match
- "INV/2026/0001" → direct to invoice
- "/setting room types" → quick navigation to module

Powered by Meilisearch indexed: guests, reservations, rooms, invoices, users.

---

## 7. Notification system

In-app bell:
- New reservation (live)
- Cancellation
- Payment failed
- OOO alert
- Overbooking warning
- Maintenance work order assigned to me
- Cashier shift mismatch
- Approval request (e.g. discount > threshold)

Channel: in-app + email + WhatsApp (BYOK) + SMS (BYOK).

User config notification preference per type.

---

## 8. Permission matrix (sample)

```
permissions
├── id, key (e.g. 'fo.reservation.create')
├── group (fo|hk|pos|acc|sett)
├── label
└── description

role_permission (M:M)
```

Sample keys:
- `fo.reservation.create`, `fo.reservation.cancel`, `fo.folio.discount_above_threshold`
- `hk.room_status.update`, `hk.lost_found.read`
- `acc.journal.post`, `acc.period.unlock`, `acc.coa.edit`
- `sett.user.create`, `sett.integration.edit_secret`
- `report.financial.read`, `report.export`

Owner UI: matrix view role × permission with toggle.

---

## 9. Property switcher (multi-property)

Untuk owner yang punya beberapa hotel:

- Pull-down di topbar
- Switch context = scope semua data ke property terpilih
- "All properties" view di dashboard owner — aggregate KPI
- Cross-property sales: report consolidated

Implementasi: `app('current_property')->id` injected via middleware berdasarkan session-selected property.

---

## 10. Localization & accessibility

- Default `id` (Indonesia), available `en`. Phase 2: `zh-CN`, `ja`, `ko` untuk pasar inbound.
- Date format: `dd-MM-yyyy` (default) atau ISO toggle.
- Currency: IDR default, ada formatter helper untuk display tanpa desimal.
- Accessibility: keyboard-first nav, semantic HTML, contrast WCAG AA, screen-reader friendly key flows.

---

## 11. Performance target

- TTFB < 300ms (di server lokal)
- LCP < 2s (login dashboard)
- Calendar grid (reservation tape chart) handle 200 rooms × 90 days < 1s render
- Real-time room status update via WebSocket (Reverb / Pusher) — < 1s propagation antar staff

---

## 12. Open questions

1. **Filament vs full custom Livewire** untuk staff panel? Filament fast, tapi reservation calendar drag-drop perlu custom heavy.
2. **Mobile native app vs PWA** untuk housekeeping? PWA cukup untuk MVP, native (Flutter / RN) di Phase 2 kalau request banyak.
3. **Single-page vs multi-page**? Cenderung Livewire-driven multi-page dengan partial reload (Hotwire-style).
4. **Voice command** untuk HK / front office? P3 nice-to-have.
