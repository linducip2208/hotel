# 18 — SaaS Upgrade Path

> Bagaimana same codebase berpindah dari mode **standalone** (single-tenant, self-hosted, license one-time) ke mode **SaaS** (multi-tenant, managed by vendor, subscription per kamar).

Goal: bukan rewrite. Toggle `APP_MODE=saas`, sediakan central control plane, deploy ke shared infrastructure. Phase 2 milestone (M16).

Reference stack: **stancl/tenancy** v3 — proven library Laravel multi-tenant dengan db-per-tenant.

---

## 1. Mode comparison

| Aspect | Standalone | SaaS |
|---|---|---|
| `APP_MODE` env | `standalone` | `saas` |
| Database | 1 DB | Central + N tenant DB (db-per-tenant) |
| Domain | Custom domain hotel | `{slug}.hotelhub.id` + opsi custom domain |
| License | Pairing v3, server-validated | Subscription, central control |
| Updates | Manual / cron self-update | Vendor-controlled rolling |
| Backup | Owner responsibility | Vendor managed (S3 versioned) |
| Pricing | One-time + maintenance | Per kamar / month |
| Host | Owner's VPS / on-prem | Vendor cloud (DigitalOcean / Hetzner / AWS Asia) |
| Support tier | Email / WA | In-app + ticketing + chat |
| Scaling | Per deployment | Horizontal, vendor managed |

---

## 2. Architecture

```
                 ┌────────────────────────┐
                 │  Central Control Plane │
                 │  - tenants table       │
                 │  - billing             │
                 │  - vendor admin panel  │
                 │  - license issuance    │
                 │  - telemetry receive   │
                 │  - tenant routing      │
                 └─────────┬──────────────┘
                           │
           ┌───────────────┼───────────────┐
           │               │               │
   ┌───────▼──────┐ ┌──────▼──────┐ ┌──────▼──────┐
   │ Tenant DB A  │ │ Tenant DB B │ │ Tenant DB C │
   │ (hotel_a)    │ │ (hotel_b)   │ │ (hotel_c)   │
   └──────────────┘ └─────────────┘ └─────────────┘

   ┌──────────────────────────────────────────────┐
   │         Shared Application Servers           │
   │  Resolve tenant from domain → switch DB conn │
   └──────────────────────────────────────────────┘
```

Pakai **db-per-tenant** (bukan single DB shared schema) — alasan utama:
- Audit pajak Indonesia: setiap badan usaha hotel butuh data terpisah jelas
- Performance isolation: hotel besar tidak ganggu hotel kecil
- Restore individual tenant tanpa risiko
- Compliance lebih mudah (UU PDP — data tenant terisolasi fisik)

Trade-off: more DB to manage. Mitigated dengan automation + connection pooling (PgBouncer / ProxySQL).

---

## 3. Central database (control plane)

```
tenants
├── id (UUID)
├── slug (unique, used in subdomain)
├── domain (custom domain, nullable)
├── company_name, owner_name, owner_email, owner_phone
├── status (trial | active | suspended | churned)
├── plan_id
├── trial_ends_at, current_period_ends_at
├── max_rooms, max_users (from plan)
├── db_name, db_host (per tenant — bisa shared host atau dedicated)
├── storage_disk_path (per tenant directory)
├── created_at, last_active_at
└── timestamps

domains
├── id, tenant_id
├── domain (e.g. hotel-mandala.hotelhub.id atau hotelmandala.com)
├── is_primary, is_verified
├── ssl_status (none | provisioning | active | failed)
└── timestamps

plans
├── id, name, slug
├── monthly_price_idr, yearly_price_idr
├── per_room_price_idr (optional model: Rp 50k / kamar / bulan)
├── max_rooms, max_users, max_properties
├── features (json)
├── is_active, is_default_signup
└── timestamps

subscriptions
├── id, tenant_id, plan_id
├── status (trialing | active | past_due | cancelled)
├── current_period_start, current_period_end
├── trial_end
├── billing_cycle (monthly | yearly)
├── price_paid_idr, billing_currency
└── timestamps

invoices, payments, coupons, usage_metrics, ...
```

---

## 4. Tenant routing & resolution

Middleware `InitializeTenancyByDomain`:
- Parse `Host:` header
- Lookup `domains` → resolve `tenants.id`
- Switch default DB connection ke tenant DB
- Bind `app('current_tenant')`
- Continue request

Tenant tidak ditemukan → fallback page atau marketing site.

Subdomain regex: `[a-z0-9-]{3,30}\.hotelhub\.id` reserved untuk tenants. Plus reserved words (admin, www, api, blog, status, dll).

---

## 5. Migration tenant

Saat tenant signup:

1. Insert ke `tenants`, `domains`, `subscriptions` (central DB)
2. `php artisan tenants:migrate --tenant={id}`:
   - Create DB `tenant_<slug>` di tenant DB host
   - Run all tenant migrations (semua table HMS — properties, reservations, dll)
   - Seed default data (COA, region tax, role, permission)
3. Issue trial license (auto-pair, no wizard)
4. Send welcome email + login link
5. Schedule onboarding emails (D+1, D+3, D+7, D+13)

Time target: tenant ready < 30 detik post-signup.

---

## 6. Code modifications dari standalone → SaaS

### Minimal changes

Codebase didesain dari awal supaya tenant-aware. Tabel tidak punya `tenant_id` column (karena per-tenant DB). Service tidak hardcode global state. Caching pakai prefix `cache::store('redis')->prefix("tenant:{$tid}:")` (auto-prefixed via stancl/tenancy).

Bagian yang berbeda di SaaS mode:
- **Auth**: bukan hanya login per-tenant tapi central login portal `accounts.hotelhub.id` opsional (P3 — single sign-on cross-tenant untuk owner multi-property).
- **Mail / WA**: di standalone tenant punya konfig sendiri. Di SaaS shared infra fallback (dengan owner BYOK overrides) — owner bisa pakai email vendor default (rate-limited) atau bring own.
- **AI BYOK**: tetap per-tenant
- **License**: tidak ada wizard di tenant — central-managed, JWT issued otomatis saat tenant active subscription

### Boot logic per mode

`config/app.php`:
```php
'mode' => env('APP_MODE', 'standalone'),
```

`AppServiceProvider`:
```php
if (config('app.mode') === 'saas') {
    $this->app->register(SaasServiceProvider::class);
} else {
    $this->app->register(StandaloneServiceProvider::class);
}
```

Mode-specific provider register:
- Tenant routing middleware
- Central DB connection
- Cron scheduler differences (tenant-aware iterating)
- Different login page layout

---

## 7. Cron & queue (SaaS-aware)

Cron schedules running per tenant dimaintain via `tenants:run` wrapper:
```bash
* * * * * php artisan tenants:run schedule:run
0 3 * * * php artisan tenants:run night-audit:close
*/15 * * * * php artisan tenants:run channel:sync-ari
```

Wrapper iterate active tenants, switch context, run command. Skip suspended.

Queue worker dengan tenancy-aware job: setiap job carry `tenant_id`, di-resolve di middleware sebelum handle.

---

## 8. Deployment & infra (SaaS)

### Server topology

```
[Cloudflare] → [Load Balancer] → [App servers x N] → [DB cluster]
                                       ↓
                                   [Redis cluster]
                                       ↓
                                 [Object storage S3/R2]
                                       ↓
                                 [Queue workers x M]
```

### Phase milestones

- **MVP SaaS launch (M16):** 1 app server, 1 DB host, 1 Redis, S3-compatible storage
- **Growth (50+ tenant):** dedicated DB host per cluster, replica read-only, queue workers separate
- **Scale (500+ tenant):** sharded DB clusters, multi-region edge, autoscaling

### Backup

- Per-tenant DB dump daily ke S3 (encrypted), retained 30 days
- WAL streaming untuk PITR (Phase 2)
- Restore drill quarterly

---

## 9. Pricing model SaaS

Lihat `19-PRICING.md` untuk detail. Singkat:

| Plan | Price | Includes |
|---|---|---|
| Starter | Rp 35rb / kamar / bln | < 20 kamar, 5 user, OTA basic |
| Growth | Rp 50rb / kamar / bln | < 50 kamar, 15 user, full OTA, AI quota basic |
| Pro | Rp 70rb / kamar / bln | < 150 kamar, unlimited user, premium AI, channel mgr full |
| Enterprise | Custom | > 150 kamar, dedicated support, custom integrations |

Add-ons: white-label, marketplace add-on, AI premium, custom domain SSL.

Trial: 14 days no card.

---

## 10. Migration dari standalone ke SaaS (existing customer)

Skenario: hotel pakai standalone Phase 1, kemudian mau migrate ke SaaS managed.

1. Vendor side create tenant slot
2. Run `php artisan license:export-data` di standalone → ZIP backup full
3. Vendor `php artisan tenants:import {zip}` → restore ke tenant DB baru
4. Domain DNS pointed ke vendor
5. License standalone revoked (vendor side)
6. Tenant aktif di SaaS

Phase 2 — finalize migration tooling.

---

## 11. Reverse: SaaS → standalone (data export)

Hak portabilitas data (UU PDP & business courtesy):
- Owner di vendor portal → "Export & Quit"
- ZIP berisi: DB dump + storage files + config snapshot
- Owner deploy sendiri di VPS, beli license standalone, restore
- Tenant SaaS dibekukan 90 days kemudian dihapus

---

## 12. Custom domain handling

- Owner setup CNAME ke `cname.hotelhub.id`
- `domains.is_verified` = true setelah ACME challenge sukses
- SSL via Let's Encrypt automation, atau Cloudflare for SaaS (premium tier)
- Tenant routing tetap berfungsi (resolve `Host: hotelmandala.com` → tenant via `domains` table)

---

## 13. Per-tenant feature flag

Beberapa tenant subscribe add-on, beberapa tidak:
- `tenant.features` JSON column override `plan.features`
- Resolve precedence: `tenant.features` > `plan.features` > default
- Used by `feature(...)` helper di code

Contoh:
- Tenant A subscribe add-on "Banquet Module" → `tenant.features.banquet = true`
- Tenant B tidak subscribe → menu Banquet hidden

---

## 14. Telemetry & observability (SaaS-spesifik)

- Centralize metrics (Prometheus + Grafana)
- Per-tenant dashboard di vendor admin panel
- Per-tenant slow-query log (sampled)
- Error tracking: Sentry per environment, tenant tag injected
- Uptime page public (Statuspage / Cachet)

---

## 15. Operational concerns

- **Update rolling**: schema migration backward-compatible; rolling deploy app servers; tenant DB migrate runs setelah app deployed (with feature flag gate)
- **DB schema drift**: prevent via mandatory `tenants:migrate-all` step di deployment pipeline
- **Disaster recovery**: RTO < 4 jam, RPO < 1 jam (Phase 2)
- **On-call**: rotation setelah ≥3 paying tenant
- **Compliance documentation**: ISO 27001 path Phase 3

---

## 16. Open questions

1. **Single-DB shared (row-level multi-tenancy) vs DB-per-tenant** — sudah keputusan: db-per-tenant. Konfirmasi dengan stress-test 1000 tenants.
2. **Stripe-style in-app billing** vs **manual quoting** untuk Indonesia? Default: BYOK PG (Midtrans/Xendit) untuk billing.
3. **Multi-region**: Indonesia + Singapore data center? Phase 3.
4. **Fixed monthly vs per-kamar pricing** — start with per-kamar, evaluate fixed-tier fallback for stability.
5. **Free tier (forever-free)?** Tidak — trial 14 days only. Free tier cenderung tarik low-quality user.
