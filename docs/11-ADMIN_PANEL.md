# 11 — Admin Panel (Super-Admin / Vendor-Side)

> Panel untuk **vendor** (kita — penjual aplikasi) memantau license, tenants (SaaS), telemetry, billing, support. Ini BUKAN panel hotel staff. Hotel staff pakai User Panel (lihat 12-USER_PANEL.md).

URL convention: `/admin` di host vendor (e.g. `admin.hotelhub.id` atau host khusus untuk standalone).

---

## 1. Akses & isolasi

- **Route group `admin.*`** dengan middleware `auth:admin` + `role:super_admin|support|sales`.
- **Tabel `admin_users`** terpisah dari `users` (hotel staff) — tidak overlap.
- 2FA wajib (TOTP) untuk semua admin user.
- Login attempt logged + alert untuk gagal berturut-turut.
- Standalone deploy biasa tidak expose admin panel ke internet umum — guarded oleh IP whitelist + bind ke port internal.
- SaaS deploy: admin panel di subdomain khusus, di belakang VPN / Cloudflare Access (P2).

---

## 2. Layout

```
[Sidebar]                        [Topbar: search, notifications, profile]
─────────────────────────────────────────────────────────────────────────
🏠 Dashboard
🔑 Licenses
   ├── Active
   ├── Expired
   ├── Revoked
   └── Pairing logs
🏢 Tenants (SaaS only)
   ├── All tenants
   ├── New signups
   ├── Trials
   └── Churned
💳 Billing
   ├── Subscriptions
   ├── Invoices
   ├── Coupons
   └── Failed payments
📊 Telemetry & Health
   ├── System health (per tenant)
   ├── API call usage
   ├── Errors / alerts
   └── Database size
🤝 Support
   ├── Tickets
   ├── Knowledge base
   └── Live chat queue (P2)
🛍 Marketplace (P3)
   ├── Add-ons
   └── Affiliates
👥 Admin Users
   ├── Roles
   └── Activity log
🔧 System
   ├── Feature flags
   ├── Pricing config
   ├── Email templates
   ├── Update notifications
   └── Audit log
```

---

## 3. Module: Licenses

### List view
- Search by license key, hotel name, owner email
- Filter: active / expired / revoked / unpaired
- Bulk action: extend, revoke, send reminder

### Detail view
Per license:
- Key (masked, copy-on-click)
- Owner contact
- Plan & feature set
- Issued / expires / last heartbeat
- Paired devices (with revoke per device)
- Usage telemetry (rooms count, bookings/month)
- Pairing log (history of attach/detach)
- Action: extend, regenerate token, revoke, send to owner

### Create license (manual sale flow)
Form: company name, contact, plan (standalone vs SaaS), feature flags, max rooms, max users, validity period, payment status. Generate key + send activation email.

### Bulk operations
- Import from CSV (mass migration from old activation system)
- Export usage report

Lihat detail desain di 16-LICENSE_PAIRING_DESIGN.md.

---

## 4. Module: Tenants (SaaS)

Hanya muncul kalau `APP_MODE=saas`.

### List
- Tenant id, slug, custom domain, plan, status (active / trial / suspended / churned)
- Created at, last active at
- Rooms count, bookings/month, MRR

### Detail per tenant
Tabs:
- **Overview** — KPI: rooms, occupancy avg, monthly bookings, MRR
- **Subscription** — current plan, next renewal, payment history, upgrade/downgrade button
- **Usage** — API calls, storage, queue jobs, AI tokens consumed (per BYOK provider)
- **Database** — size, last backup, restore point
- **Logs** — recent errors, slow queries, login activity
- **Domain** — primary, custom, SSL status
- **Impersonate** — login-as button (audit-logged) untuk troubleshooting

### Tenant lifecycle
- Trial 14 days → notify D-7, D-3, D-0
- Auto-suspend setelah lewat grace 7 days unpaid
- Suspend = read-only mode, data retained 90 days
- Hard-delete setelah 90 days (with multiple notifications) — atau owner export & cancel

---

## 5. Module: Billing

- **Subscriptions** view per tenant
- **Invoices** (PDF) — generated otomatis monthly
- **Failed payments** queue — retry + dunning
- **Coupons / promo codes** — create, expire, usage cap
- **Tax handling** — kalau owner aplikasi PKP, generate faktur pajak per invoice (via Coretax)
- **Payment methods** — owner aplikasi BYOK juga (Midtrans/Xendit/Stripe untuk billing tenants)

---

## 6. Module: Telemetry & Health

Polling endpoint dari setiap deployment (standalone & tenant) push ke central:

```
POST /admin/telemetry/heartbeat
{
  "license_key": "...",
  "deployment_id": "...",
  "version": "1.4.2",
  "rooms_count": 24,
  "active_bookings": 18,
  "queue_jobs_pending": 3,
  "queue_jobs_failed_24h": 0,
  "errors_24h": 12,
  "db_size_mb": 380,
  "uptime_pct_24h": 99.9
}
```

Heartbeat tiap 1 jam. Stored di tabel `deployment_heartbeats` (rolling 30 days).

Alert rules:
- No heartbeat 24 jam → "Deployment offline"
- queue_jobs_failed_24h > 50 → notify support
- errors_24h spike (3x baseline) → notify dev team
- DB size > plan limit → notify sales team (upsell)

Dashboard chart: deployment health overview, top-error tenants, version distribution.

---

## 7. Module: Support

### Tickets
- Created by tenant via in-app help button or email
- Priority: low / medium / high / urgent
- SLA target by plan tier
- Assign to support agent
- Internal notes vs customer-facing reply
- Linked to tenant/license context

### Knowledge base
- Markdown articles
- Versioned per app version
- Public URL (slug-based)

### Live chat (P2)
- Queue + agent assignment

---

## 8. Module: Admin Users & Roles

```
admin_users
├── id, email, password (bcrypt)
├── name, phone
├── role_id
├── two_factor_secret_encrypted
├── is_active
├── last_login_at, last_login_ip
└── timestamps

admin_roles
├── id, name (super_admin|sales|support|finance|dev_ops|read_only)
├── permissions (JSON array of permission keys)
└── timestamps
```

Permission contoh: `licenses.create`, `licenses.revoke`, `tenants.impersonate`, `billing.refund`, `system.feature_flags`, `audit.read`, dll.

Activity log per admin user: every action (especially impersonation, refund, license revoke) logged.

---

## 9. Module: System Settings

### Feature flags
Global toggle features yang bisa dipatok per tenant atau global:
- `enable_ai_demand_forecast` (default off)
- `enable_marketplace_addons` (P3)
- `force_2fa_for_owners`
- dll

UI: list flag + tenant override matrix.

### Pricing config
Edit plan: name, monthly/yearly price, feature set, rooms cap, user cap, AI quota baseline. Activate / deprecate plan.

### Email templates
Master template untuk:
- Trial expiring
- Payment failed
- Pairing success
- License expired
- Welcome email

WYSIWYG + variable insert.

### Update notifications
Push announcement to all tenants:
- "v1.5 released — new feature X"
- "Maintenance window Sunday 02:00 WIB"

Tampil di user panel banner. Tenant-targeted (by plan, by region).

### Audit log
Read-only, searchable, exportable. Retention 7 tahun.

---

## 10. Module: Marketplace (P3)

- **Add-ons** — paid features tenant bisa subscribe (e.g. "Spa module", "Banquet module", "Revenue Management AI")
- **Affiliates** — referrer tracking + commission ledger
- Owner aplikasi atau third-party developer bisa publish add-on

Phase 3, opsional.

---

## 11. Branding & White-label

Kalau jualnya sebagai white-label (reseller boleh re-brand):
- Per-reseller theme: logo, color, domain, invoice template
- Reseller ID di license — semua tenant under reseller masuk ke MRR-nya
- Reseller punya sub-admin panel `/reseller` (limited subset of admin) — Phase 2

---

## 12. Reports yang tersedia

| Report | Periode | Format |
|---|---|---|
| MRR / ARR breakdown | Monthly | Dashboard + Excel |
| Churn rate | Monthly | Dashboard |
| New signups | Daily / Weekly | Dashboard |
| Top tenants by revenue | All-time | Excel |
| License sale (standalone) | Monthly | Excel |
| Support ticket volume | Weekly | Dashboard |
| Avg resolution time | Weekly | Dashboard |
| Deployment health summary | Real-time | Dashboard |
| AI BYOK usage (aggregated) | Monthly | Excel — informational only, ga bill ke tenant |
| Renewal pipeline (next 30d) | Daily | Dashboard |

---

## 13. Stack

- Filament admin panel (atau custom-built Livewire) — quick to build
- Charts: Apache ECharts atau Chart.js
- Real-time: Laravel Echo + Reverb untuk live notifications
- Auth: Laravel Fortify + custom 2FA + IP whitelist guard

---

## 14. Open questions

1. Filament vs custom Livewire untuk admin? Filament cepat tapi kurang lentur untuk custom flow license.
2. Apakah perlu mobile admin app, atau cukup responsive web?
3. Multi-region deployment (Asia + Europe) untuk reduce latency tenant push? Phase 3.
4. Reseller portal di Phase 2 atau Phase 3?
