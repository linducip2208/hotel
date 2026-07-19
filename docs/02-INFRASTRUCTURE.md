# 02 — Infrastructure

> Stack, deployment topology, scaling, monitoring, backup. Berlaku untuk standalone & SaaS.

---

## 1. Tech Stack

### Backend
- **Laravel 11** — main framework
- **PHP 8.3** — minimum 8.2, rekomendasi 8.3
- **MySQL 8.0** atau **PostgreSQL 16** — DB pilihan
- **Redis 7** — cache + session + queue + lock
- **Meilisearch 1.x** — full-text search (room, guest, OTA inventory)
- **Supervisor** — queue worker process management
- **Cron** — scheduler

### Frontend
- **Blade** + **Livewire 3** — interactive admin panel
- **Alpine.js 3** — micro-interaction
- **Tailwind CSS 3** — styling
- **Vite 5** — bundler
- **PWA** (service worker + manifest) — staff mobile

### Library penting
- `stancl/tenancy` — multi-tenant SaaS mode
- `spatie/laravel-permission` — RBAC
- `spatie/laravel-activitylog` — audit log
- `spatie/laravel-medialibrary` — file attachments
- `pragmarx/google2fa-laravel` — 2FA TOTP
- `dompdf/dompdf` atau `barryvdh/laravel-dompdf` — invoice PDF
- `simplesoftwareio/simple-qrcode` — QR generation
- `intervention/image` — image processing
- `barryvdh/laravel-snappy` — alternatif PDF (wkhtmltopdf)
- `laravel/scout` + `meilisearch/meilisearch-php` — search
- `predis/predis` atau `phpredis` — Redis client
- `laravel/horizon` — queue monitoring (production)
- `sentry/sentry-laravel` — error tracking
- `laravel/pulse` — performance monitoring
- `phpoffice/phpspreadsheet` — Excel export
- `tecnickcom/tcpdf` — fallback PDF (kompleks)

### Optional
- `pusher/pusher-php-server` — realtime broadcast (housekeeping live status)
- `php-http/curl-client` — HTTP client untuk OTA
- `aws/aws-sdk-php` — S3 (atau Cloudflare R2)
- `league/flysystem-aws-s3-v3` — disk driver

---

## 2. Deployment Topology

### A. Standalone (single-tenant) — owner self-host

```
┌────────────────────────────────────────────────┐
│ VPS (Niagahoster, Biznet, AWS Lightsail, GCP)  │
│                                                │
│  ┌──────────────┐                              │
│  │   Nginx      │  port 80, 443                │
│  └──────┬───────┘                              │
│         │                                      │
│         ▼                                      │
│  ┌──────────────┐                              │
│  │  PHP-FPM 8.3 │  Laravel 11                  │
│  └──┬───┬───────┘                              │
│     │   │                                      │
│  ┌──▼─┐ │  ┌──────────────┐                    │
│  │MySQL│ └──▶ Redis (queue+cache+session)     │
│  │  8  │    │  + Horizon worker (Supervisor)  │
│  └─────┘    └──────────────┘                   │
│                                                │
│  ┌──────────────┐                              │
│  │ Meilisearch  │  port 7700 (localhost)       │
│  └──────────────┘                              │
│                                                │
│  ┌──────────────┐                              │
│  │  Local FS    │  storage/app/                │
│  │  atau S3-compat (MinIO / R2 / Wasabi)       │
│  └──────────────┘                              │
└────────────────────────────────────────────────┘
```

**Sizing rekomendasi:**

| Tier | Kamar | vCPU | RAM | Disk | Bandwidth |
|---|---|---|---|---|---|
| Lite | ≤ 30 | 2 | 4 GB | 60 GB SSD | 2 TB/bln |
| Pro | 30-100 | 4 | 8 GB | 120 GB SSD | 4 TB/bln |
| Enterprise | > 100 | 8 | 16 GB | 250 GB SSD | 8 TB/bln |

### B. SaaS (multi-tenant) — kita host

```
┌─────────────────────────────────────────────────────────┐
│ Cloudflare (CDN + DDoS + SSL + WAF)                     │
└─────────────┬───────────────────────────────────────────┘
              │
┌─────────────▼─────────────────────────────────────────┐
│  Load Balancer (Nginx + ALB)                          │
└──┬───────────┬───────────┬────────────────────────────┘
   │           │           │
┌──▼───┐   ┌──▼───┐   ┌──▼───┐
│ App1 │   │ App2 │   │ App3 │  (Stateless Laravel)
└──┬───┘   └──┬───┘   └──┬───┘
   │          │          │
   ├──────────┼──────────┘
   │          │
┌──▼──────────▼────────┐    ┌─────────────┐
│ MySQL primary         │    │ Redis Cluster│
│ + read replica        │    │ (cache, queue,│
│ + DB-per-tenant       │    │  session)    │
└──────────────────────┘    └─────────────┘
   │
┌──▼────────────────┐    ┌─────────────────┐
│ S3 / R2 (storage) │    │ Meilisearch HA  │
└──────────────────┘    └─────────────────┘

┌────────────────────────────┐
│ Horizon worker pool x N    │
│ (queue: ota-sync, mail, ai)│
└────────────────────────────┘
```

**Region:** Jakarta (AWS ap-southeast-3) atau Singapore (ap-southeast-1) untuk latency Indonesia <50ms.

---

## 3. Database Strategy

### Standalone
- 1 database tunggal per install
- `DB_DATABASE=hotel_<owner>` di `.env`
- Migration jalan via `php artisan migrate`

### SaaS
- **DB-per-tenant** (rekomendasi karena audit pajak Indonesia + isolation)
- Central DB: `central_db` — tabel `tenants`, `subscriptions`, `domains`, `billing`
- Tenant DB: `tenant_<uuid>` — semua data hotel (reservations, folios, guests, dll)
- `stancl/tenancy` resolve tenant by subdomain → switch connection runtime

**Alternatif (kalau >1000 tenants):**
- Schema-per-tenant di Postgres
- Tetap fallback ke DB-per-tenant untuk Enterprise tier (data isolation kuat)

### Backup
- **Standalone:** `spatie/laravel-backup` daily ke local + S3 (configurable)
- **SaaS:** automated snapshot RDS daily + point-in-time recovery 7 hari

---

## 4. Queue Strategy

Semua I/O berat masuk queue, bukan synchronous.

| Job | Queue | Retry | Timeout |
|---|---|---|---|
| OTA ARI sync (push) | `ota-sync` | 5x exponential | 30s |
| OTA booking ingest | `ota-ingest` | 3x | 10s |
| Send confirmation email | `mail` | 3x | 30s |
| Send WhatsApp / SMS | `messaging` | 3x | 15s |
| AI request (LLM) | `ai` | 2x | 120s |
| pSEO content generation | `pseo` | 2x | 120s |
| PDF invoice generation | `pdf` | 2x | 30s |
| e-Faktur Coretax submit | `tax` | 5x | 60s |
| Heartbeat license check | `default` | 1x | 10s |
| Daily report email | `default` | 2x | 60s |

**Worker:**
- Standalone: Supervisor dengan 2-4 worker
- SaaS: Horizon dengan auto-scale 4-32 worker

---

## 5. Scheduler (cron)

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Night audit per property
    $schedule->command('hotel:night-audit')
        ->hourly()  // tiap property punya night audit time-nya sendiri, command cek
        ->withoutOverlapping();

    // OTA sync periodic (selain webhook)
    $schedule->command('ota:sync-availability')
        ->everyFifteenMinutes();

    // pSEO sitemap regenerate
    $schedule->command('pseo:generate-sitemap')
        ->dailyAt('03:00');

    // Daily flash report email
    $schedule->command('reports:daily-flash')
        ->dailyAt('08:00');

    // License heartbeat (standalone)
    $schedule->command('license:heartbeat')
        ->daily();

    // Backup
    $schedule->command('backup:run')
        ->dailyAt('02:00');

    // Clear old activity logs
    $schedule->command('activitylog:clean')
        ->weekly();

    // Refresh Coretax token
    $schedule->command('coretax:refresh-token')
        ->everyThirtyMinutes();

    // SaaS billing tick
    $schedule->command('billing:process')
        ->dailyAt('01:00')
        ->when(fn() => config('app.mode') === 'saas');

    // Index search engine
    $schedule->command('scout:reindex')
        ->dailyAt('04:00');
}
```

---

## 6. Caching Strategy

| Layer | TTL | Cache key pattern |
|---|---|---|
| Room availability search | 60s | `avail:{property}:{start}:{end}:{adults}` |
| Rate plan rules | 5 min | `rate:{property}:{plan}:{date}` |
| OTA inventory state | 30s | `ota:{ota}:{property}:{date}` |
| Guest profile (frequent lookup) | 1 hour | `guest:{id}` |
| Settings / config | 1 day | `config:{property}` |
| pSEO page render | 6 hours | `pseo:{route_slug}` |
| Sitemap chunk | 24 hours | `sitemap:chunk:{n}` |
| Audit log query | (no cache) | — append-only |
| License lock check | 5 min | `license:lock` (file mtime invalidated) |

---

## 7. Storage

### Disk layout

```
storage/app/
├── public/
│   ├── property/{id}/photos/
│   ├── room/{id}/photos/
│   ├── guest/{id}/{ktp,passport,signature}.jpg
│   └── pseo-cache/
├── private/
│   ├── invoices/{year}/{month}/
│   ├── e-faktur/{year}/{month}/
│   ├── tax-reports/
│   ├── lapor-imigrasi/
│   ├── backups/
│   └── audit-trails/
├── llm-presets/
│   ├── deepseek.json
│   ├── gemini-flash.json
│   ├── ... (lihat 05-AI_PROVIDERS)
└── ota-presets/
    ├── booking-com.json
    ├── agoda.json
    └── ...
```

### Standalone disk
- Default: local FS (`storage/app/private`, `storage/app/public`)
- Public symlink: `php artisan storage:link`

### SaaS disk
- Default: S3 / Cloudflare R2 / Wasabi (cheapest: R2 — $0/egress)
- Per-tenant prefix: `s3://hotel-saas/tenants/{tenant_uuid}/`

---

## 8. SSL / Domain

### Standalone
- Let's Encrypt via certbot (auto-renew via cron)
- Single domain bound to license

### SaaS
- Wildcard cert untuk `*.app.{namaproduct}.com`
- Custom domain per tenant (Pro+): Cloudflare for SaaS / certbot per-tenant

---

## 9. Email

- Default: SMTP BYOK (owner setup sendiri)
- Preset providers (autofill admin UI): Mailgun, Postmark, Resend, SendGrid, Amazon SES, Gmail SMTP, Mailtrap
- Bounce handling: webhook handler optional
- DKIM/SPF/DMARC: docs di [`20-DEPLOYMENT.md`](20-DEPLOYMENT.md)

---

## 10. Monitoring & Observability

### Standalone (lightweight)
- **Laravel Pulse** — performance dashboard (built-in route `/pulse`, RBAC-gated)
- **Sentry** (optional, BYOK DSN) — error tracking
- **Health check endpoint** `/health` — return DB, Redis, queue, storage status JSON
- **Uptime check** — owner pakai UptimeRobot/Better Uptime free tier

### SaaS (full)
- Sentry full
- Datadog APM atau Grafana + Prometheus
- Loki untuk log aggregation
- Status page publik (cstate self-hosted atau statuspage.io)
- PagerDuty / OpsGenie integration
- Cloudflare Logs

### Health check JSON contract

```json
GET /health → 200
{
  "status": "ok",
  "version": "1.0.0",
  "uptime_seconds": 38291,
  "checks": {
    "database": "ok",
    "redis": "ok",
    "storage": "ok",
    "queue_pending": 12,
    "queue_failed": 0,
    "license": "active"
  },
  "timestamp": "2026-04-28T10:00:00+07:00"
}
```

---

## 11. Security Hardening (infra layer)

- HTTPS only (HSTS preload)
- Cloudflare (SaaS) atau Cloudflare Tunnel (Standalone optional)
- Firewall: ufw allow 22, 80, 443 only
- Fail2ban untuk SSH brute-force
- SSH key only, password disabled
- Database tidak expose port public
- Redis bind 127.0.0.1, password set
- App secrets di `.env` (never committed)
- BYOK API keys encrypted at rest (`Crypt::encryptString`)
- Audit log append-only (lihat [`15-ADMIN_SECURITY.md`](15-ADMIN_SECURITY.md))

---

## 12. Backup & Disaster Recovery

### Standalone
- `spatie/laravel-backup` daily 02:00
- Retain: 7 daily + 4 weekly + 12 monthly
- Destination: local + 1 offsite (S3/R2/Wasabi BYOK)
- Encryption: at-rest (provider) + optional gpg pre-upload
- Restore drill: docs di [`20-DEPLOYMENT.md`](20-DEPLOYMENT.md)

### SaaS
- RDS automated daily snapshot
- Point-in-time recovery 7 hari
- Cross-region replica untuk disaster recovery
- File storage: S3 versioning + cross-region replication
- RPO: 24 jam · RTO: 4 jam (Pro), 1 jam (Enterprise)

---

## 13. Deployment Pipeline

### Standalone (manual atau CD ringan)

```bash
# Owner / kita push:
git pull origin release
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan down --retry=60
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache
php artisan up
sudo supervisorctl restart hotel-worker:*
```

Future: `php artisan app:update` self-update command.

### SaaS (full CI/CD)

```yaml
# .github/workflows/deploy.yml (sketch)
on:
  push:
    branches: [main]

jobs:
  deploy:
    steps:
      - checkout
      - php-test (PHPUnit + Pest)
      - phpstan analyse (level 6+)
      - laravel-pint (code style)
      - build-frontend (npm run build)
      - docker-build & push
      - deploy-blue-green (Kubernetes / ECS)
      - migrate-tenant-batched (rolling)
      - smoke-test
      - notify-slack
```

---

## 14. Development Environment

```bash
# Local dev (Laragon Windows / Valet macOS / Sail Docker)
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan tinker  # data poking

# Frontend
npm install
npm run dev  # vite dev server with HMR

# Queue worker (terminal kedua)
php artisan queue:work --queue=ota-sync,mail,ai,default

# Scheduler test (terminal ketiga)
php artisan schedule:work
```

`.env` important keys:
```env
APP_MODE=standalone              # standalone | saas
APP_LICENSE_DEV_BYPASS=true      # skip pairing wizard di local
DB_CONNECTION=mysql
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
BROADCAST_CONNECTION=null        # atau pusher di production realtime
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
```

---

## 15. Performance Budget

| Metric | Target |
|---|---|
| TTFB pSEO page | < 500ms |
| TTFB admin dashboard | < 800ms |
| Reservation create transaction | < 1s |
| OTA sync push | < 2s per OTA |
| AI concierge response | < 3s |
| PDF invoice generation | < 2s |
| Search room availability | < 300ms |
| Booking engine search → results | < 1.5s |

---

## 16. Capacity Planning

| Workload | Per property estimate | SaaS scaling |
|---|---|---|
| Reservations / day | 5-50 | Linear |
| OTA sync events / day | 100-500 | Queue-bound |
| POS transactions / day | 50-500 | Linear |
| Folio postings / day | 100-1000 | Linear |
| Active guests in DB | 1k-50k | Per-tenant DB scales independently |
| pSEO page hits / day | 100-10k | Heavily cached |
| AI requests / day | 50-500 | BYOK = no central bottleneck |

---

## 17. Reference Specs (vendor)

| Vendor | Product | Note |
|---|---|---|
| Niagahoster | Cloud VPS X1 (Rp 159rb/bln, 2 vCPU 2GB) | Lite tier |
| Biznet Gio | NEO Cloud VM | Pro tier — region Indonesia |
| AWS Lightsail | $40-80/bln | Pro tier — region SG/Jakarta |
| GCP e2-standard-2 | Sekitar $50/bln | Pro tier |
| Cloudflare R2 | $15/TB storage, $0 egress | Storage SaaS — tercheap |
| Cloudflare Pages | Static fallback | Optional CDN booking widget |
| Mailgun / Resend | $20-50/bln | Email BYOK preset |
