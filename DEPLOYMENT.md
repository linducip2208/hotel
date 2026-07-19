# Deployment Guide — HotelHub HMS

> Panduan deployment production Hotel Management System. Cover: VPS bare-metal, Docker Compose, dan managed hosting (Niagahoster, Biznet GIO).

---

## Prasyarat Server

| Komponen | Versi Minimum | Rekomendasi |
|---|---|---|
| PHP | 8.2 | 8.3 |
| MySQL | 8.0 | 8.0 |
| PostgreSQL (opsional) | 16 | 16 |
| Composer | 2.x | 2.x |
| Node.js | 18 | 20 |
| Nginx | 1.24 | 1.27 |
| Redis | 7.x | 7.x |
| Meilisearch | 1.10 | 1.10 |
| Supervisor | 4.x | 4.x |

---

## Sizing

| Hotel size | Spec minimum | Spec rekomendasi |
|---|---|---|
| < 30 kamar | 2 vCPU / 4 GB RAM / 60 GB SSD | 4 vCPU / 8 GB / 80 GB SSD |
| 30-100 kamar | 4 vCPU / 8 GB / 120 GB SSD | 8 vCPU / 16 GB / 200 GB SSD |
| 100-300 kamar | 8 vCPU / 16 GB / 240 GB SSD | 8 vCPU / 32 GB / 400 GB + DB replica |
| > 300 kamar / chain | 16 vCPU / 32 GB / 500 GB + DB cluster | Dedicated infra |

Storage growth: ~2 GB/tahun per 50 kamar (PII docs, photos, audit log).

---

## Quick Start — Local Development

```bash
git clone https://github.com/your-org/hotel.git hotel
cd hotel

composer install
cp .env.example .env
php artisan key:generate

# Edit .env — minimal:
#   DB_DATABASE, DB_USERNAME, DB_PASSWORD
#   LICENSE_DEV_BYPASS=true

php artisan migrate --seed
php artisan storage:link

npm install && npm run build

php artisan serve
# Buka http://localhost:8000
```

---

## Production Deployment — Ubuntu 22.04 / 24.04

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Essential packages
sudo apt install -y curl wget git unzip software-properties-common ufw fail2ban

# Firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow OpenSSH
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Fail2Ban
sudo systemctl enable --now fail2ban
```

### 2. PHP 8.3 Installation

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis \
  php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath \
  php8.3-gd php8.3-imagick php8.3-intl php8.3-soap
```

`/etc/php/8.3/fpm/php.ini` tweaks:
```ini
memory_limit = 512M
post_max_size = 64M
upload_max_filesize = 32M
max_execution_time = 120
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
```

### 3. MySQL 8.0

```bash
sudo apt install -y mysql-server-8.0
sudo mysql_secure_installation

sudo mysql -e "CREATE DATABASE hotel_main CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'hms'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';"
sudo mysql -e "GRANT ALL PRIVILEGES ON hotel_main.* TO 'hms'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

### 4. Redis 7

```bash
sudo apt install -y redis-server

# /etc/redis/redis.conf — set:
#   bind 127.0.0.1
#   requirepass YOUR_REDIS_PASSWORD

sudo systemctl enable --now redis-server
```

### 5. Meilisearch

```bash
curl -L https://install.meilisearch.com | sh
sudo mv meilisearch /usr/local/bin/

sudo useradd -r meilisearch
sudo mkdir -p /var/lib/meilisearch && sudo chown meilisearch:meilisearch /var/lib/meilisearch

sudo tee /etc/systemd/system/meilisearch.service <<'EOF'
[Unit]
Description=Meilisearch
After=network.target

[Service]
ExecStart=/usr/local/bin/meilisearch --master-key=YOUR_MASTER_KEY --db-path /var/lib/meilisearch --env=production
Restart=always
User=meilisearch
Group=meilisearch

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
sudo systemctl enable --now meilisearch
```

### 6. Application Deploy

```bash
sudo mkdir -p /var/www/hotel
sudo chown $USER:www-data /var/www/hotel

cd /var/www/hotel
git clone https://github.com/your-org/hotel.git .

composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

# Edit .env — lihat section "Environment Variables" di bawah
# Pastikan APP_ENV=production, APP_DEBUG=false

php artisan migrate --force
php artisan storage:link

npm ci && npm run build

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 7. Nginx Setup

```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/hotel

# Edit server_name di file tersebut
sudo sed -i 's/your-hotel-domain.com/YOUR_DOMAIN/g' /etc/nginx/sites-available/hotel

sudo ln -s /etc/nginx/sites-available/hotel /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 8. SSL — Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renew cron
echo "0 3 * * * certbot renew --quiet" | sudo crontab -
```

### 9. Supervisor (Queue Worker)

```bash
sudo apt install -y supervisor
sudo cp deploy/supervisor.conf /etc/supervisor/conf.d/hotel.conf

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hotel-scheduler: hotel-queue:*
```

### 10. Scheduler (Cron)

```bash
sudo crontab -u www-data -e
```

```
* * * * * cd /var/www/hotel && php artisan schedule:run >> /dev/null 2>&1
```

### 11. License Setup

```bash
cd /var/www/hotel

# Pastikan LICENSE_SERVER_URL dan LICENSE_DEV_BYPASS=false di .env
php artisan license:bootstrap

# Buka browser: https://your-domain.com/__pair
# Ikuti wizard pairing
```

---

## Docker Compose Deployment

Repo sudah include `docker-compose.yml`. Quick start:

```bash
# Clone dan setup
git clone https://github.com/your-org/hotel.git hotel && cd hotel

# Copy dan edit environment
cp .env.example .env
# Edit DB_PASSWORD, REDIS_PASSWORD, MEILISEARCH_KEY

# Jalankan container
docker compose up -d

# Migrasi dan seeding
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link

# Build frontend
docker compose exec app npm ci && docker compose exec app npm run build

# License pairing
docker compose exec app php artisan license:bootstrap
# Buka http://localhost:8080/__pair
```

---

## Environment Variables

### Application
| Variable | Deskripsi | Default |
|---|---|---|
| `APP_NAME` | Nama aplikasi | `HotelHub HMS` |
| `APP_ENV` | Environment (`local`, `production`) | `production` |
| `APP_DEBUG` | Debug mode (wajib `false` di production) | `false` |
| `APP_URL` | URL aplikasi | `https://hotel-domain.com` |
| `APP_TIMEZONE` | Timezone | `Asia/Jakarta` |
| `APP_LOCALE` | Bahasa default | `id` |
| `APP_MODE` | `standalone` (single-tenant) atau `saas` | `standalone` |

### Database
| Variable | Deskripsi | Default |
|---|---|---|
| `DB_CONNECTION` | Database driver (`mysql` / `pgsql`) | `mysql` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `3306` |
| `DB_DATABASE` | Nama database | `hotel_main` |
| `DB_USERNAME` | Database user | `hms` |
| `DB_PASSWORD` | Database password | (wajib diisi) |

### Redis (Cache, Session, Queue)
| Variable | Deskripsi | Default |
|---|---|---|
| `CACHE_STORE` | Cache driver (`redis` / `database`) | `redis` |
| `SESSION_DRIVER` | Session driver (`redis` / `database`) | `redis` |
| `QUEUE_CONNECTION` | Queue driver (`redis` / `database`) | `redis` |
| `REDIS_CLIENT` | Redis client (`predis` / `phpredis`) | `predis` |
| `REDIS_HOST` | Redis host | `127.0.0.1` |
| `REDIS_PORT` | Redis port | `6379` |
| `REDIS_PASSWORD` | Redis password | (wajib diisi) |

### Session & Security
| Variable | Deskripsi | Default |
|---|---|---|
| `SESSION_ENCRYPT` | Encrypt session data | `true` |
| `SESSION_SECURE_COOKIE` | Secure cookie (wajib `true` saat HTTPS) | `true` |
| `SESSION_SAME_SITE` | SameSite policy | `lax` |

### Mail (BYOK — user input via admin UI)
| Variable | Deskripsi | Default |
|---|---|---|
| `MAIL_MAILER` | Mail driver | `smtp` |
| `MAIL_HOST` | SMTP host | (dari admin UI) |
| `MAIL_PORT` | SMTP port | `587` |
| `MAIL_USERNAME` | SMTP username | (dari admin UI) |
| `MAIL_PASSWORD` | SMTP password | (dari admin UI) |
| `MAIL_ENCRYPTION` | Encryption (`tls` / `ssl`) | `tls` |
| `MAIL_FROM_ADDRESS` | From email | `noreply@hotel-domain.com` |
| `MAIL_FROM_NAME` | From name | `${APP_NAME}` |

### Storage (S3-compatible BYOK)
| Variable | Deskripsi | Default |
|---|---|---|
| `FILESYSTEM_DISK` | Default disk | `local` |
| `AWS_ACCESS_KEY_ID` | S3 access key | (dari admin UI) |
| `AWS_SECRET_ACCESS_KEY` | S3 secret key | (dari admin UI) |
| `AWS_DEFAULT_REGION` | S3 region | `ap-southeast-3` |
| `AWS_BUCKET` | S3 bucket name | (dari admin UI) |
| `AWS_ENDPOINT` | S3 endpoint (R2, MinIO, dll) | (dari admin UI) |
| `AWS_USE_PATH_STYLE_ENDPOINT` | Path-style endpoint | `false` |

### Search
| Variable | Deskripsi | Default |
|---|---|---|
| `SCOUT_DRIVER` | Search driver (`database` / `meilisearch`) | `database` |
| `MEILISEARCH_HOST` | Meilisearch host | `http://localhost:7700` |
| `MEILISEARCH_KEY` | Meilisearch master key | (wajib diisi) |

### License (whitelabel.co.id pairing v3)
| Variable | Deskripsi | Default |
|---|---|---|
| `LICENSE_SERVER_URL` | License server URL | `https://whitelabel.co.id` |
| `LICENSE_DEV_BYPASS` | Skip pairing di development | `false` (production) |
| `LICENSE_HEARTBEAT_INTERVAL` | Heartbeat interval (detik) | `86400` |
| `LICENSE_HEARTBEAT_GRACE` | Grace period (detik) | `604800` |
| `LICENSE_PUBLIC_KEY_PATH` | Path ke public key vendor | `config/license/vendor-public.pem` |
| `LICENSE_GRACE_DAYS` | Grace days | `30` |

### Coretax (e-Faktur DJP)
| Variable | Deskripsi | Default |
|---|---|---|
| `CORETAX_BASE_URL` | Coretax API base URL | `https://api.coretaxdjp.pajak.go.id` |
| `CORETAX_CERT_PATH` | Path ke certificate | `storage/app/certificates/` |
| `CORETAX_CERT_PASSWORD` | Certificate password | (wajib diisi) |
| `CORETAX_NPWP` | NPWP penjual | (wajib diisi) |
| `CORETAX_TIMEOUT` | Request timeout (detik) | `30` |
| `CORETAX_RETRY` | Retry count | `3` |
| `CORETAX_ENVIRONMENT` | `development` / `production` | `production` |

### pSEO
| Variable | Deskripsi | Default |
|---|---|---|
| `PSEO_ENABLED` | Aktifkan pSEO | `true` |
| `PSEO_DEFAULT_LOCALE` | Default locale pSEO | `id` |

### Sentry (Error Tracking — optional)
| Variable | Deskripsi |
|---|---|
| `SENTRY_LARAVEL_DSN` | Sentry DSN |
| `SENTRY_TRACES_SAMPLE_RATE` | Sampling rate (0.0 - 1.0) |
| `SENTRY_ENVIRONMENT` | Environment label |

---

## Supervisor Configuration

File: `deploy/supervisor.conf`

Dua program:
1. **hotel-scheduler** — menjalankan `php artisan schedule:work` (pengganti cron jika tidak pakai crontab)
2. **hotel-queue** — 2 worker untuk queue processing

Koneksi queue: `redis` (sesuaikan dengan `QUEUE_CONNECTION` di `.env`).

---

## Backup Strategy

### Database
Cron daily 02:00:
```bash
mysqldump -u hms -p'PASSWORD' --single-transaction --routines --triggers hotel_main \
  | gzip > /var/backups/hotel/db-$(date +%Y%m%d).sql.gz
```

Retention: 7 daily + 4 weekly + 12 monthly. Offsite sync ke S3/R2 jika dikonfigurasi.

### Storage / Uploads
Cron daily 02:30:
```bash
rsync -avz /var/www/hotel/storage/app/ /var/backups/hotel/storage/
```

### Restore
```bash
gunzip < /var/backups/hotel/db-YYYYMMDD.sql.gz | mysql -u hms -p hotel_main
rsync -avz /var/backups/hotel/storage/ /var/www/hotel/storage/app/
```

---

## Update Procedure

```bash
cd /var/www/hotel

# Maintenance mode (bypass token opsional)
sudo -u www-data php artisan down --secret=update-token-2026

# Pull & build
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Migrations
sudo -u www-data php artisan migrate --force

# Cache
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

# Restart workers
sudo supervisorctl restart hotel-queue:*

# Go live
sudo -u www-data php artisan up
```

---

## Pre-Go-Live Checklist

- [ ] HTTPS aktif, redirect 80 → 443
- [ ] HSTS header aktif
- [ ] License paired
- [ ] DB credentials di-rotate dari default
- [ ] Redis password diset
- [ ] `APP_KEY` generated unik
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Mail SMTP dikonfigurasi & test send
- [ ] Backup cron dikonfigurasi
- [ ] Queue worker running (`supervisorctl status`)
- [ ] Scheduler running (`php artisan schedule:list`)
- [ ] Meilisearch indexed (`php artisan scout:import "App\Models\Guest"`, dsb.)
- [ ] Property profile diisi lengkap
- [ ] Tax config (PB1, PPN) diset
- [ ] Initial admin user dibuat
- [ ] 2FA di-enroll untuk semua admin
- [ ] Payment provider di-add & di-test (BYOK)
- [ ] OTA channel di-add & di-test (BYOK)
- [ ] Booking engine theme dikustomisasi
- [ ] DNS → server IP propagated
- [ ] `robots.txt` + sitemap accessible
- [ ] Submit sitemap ke Google Search Console
- [ ] Smoke test: search → book → confirm → pay flow
- [ ] Smoke test: night audit run
- [ ] Smoke test: housekeeping mobile login

---

## Troubleshooting

| Gejala | Cek dulu |
|---|---|
| 500 error | `storage/logs/laravel.log`, php-fpm error log |
| Queue tidak jalan | `supervisorctl status hotel-queue:*`, Redis connect? |
| Cron tidak run | `crontab -l -u www-data`, `php artisan schedule:list` |
| Search blank | Meilisearch service running? Re-index: `php artisan scout:import` |
| Asset 404 | `npm run build`, `php artisan storage:link` |
| Slow page | OPcache enable? `config:cache`? DB index? |
| License banner | `php artisan license:diagnostic` |
| OTA sync error | `storage/logs/laravel.log`, integration test endpoint |
| Email tidak terkirim | Test via `php artisan tinker`: `Mail::raw('test', fn($m) => $m->to('admin@test.com'))` |
| License pairing gagal | `LICENSE_DEV_BYPASS=true` untuk sementara; cek `LICENSE_SERVER_URL` reachable |

---

## Network & Ports

| Port | Service | Expose |
|---|---|---|
| 80 | HTTP (redirect) | Public |
| 443 | HTTPS | Public |
| 22 | SSH | IP whitelist atau Tailscale only |
| 3306 | MySQL | localhost only |
| 6379 | Redis | localhost only |
| 7700 | Meilisearch | localhost only |

---

## Monitoring

| Layer | Tool |
|---|---|
| Server health | Netdata / Glances / btop (manual SSH) |
| Application errors | Sentry (BYOK DSN) |
| Uptime | UptimeRobot / Better Uptime / Hetrix Tools |
| Logs | `storage/logs/laravel.log` (rotated daily) |
| Performance | Laravel Pulse (RBAC-gated, `/pulse`) |
| Queue health | Laravel Horizon (`/horizon`, RBAC-gated) |

---

## Performance Tuning

- OPcache enabled + preload di production
- Route/config/view cache (`php artisan optimize`)
- Composer autoloader optimized (`--optimize-autoloader`)
- Redis sebagai cache + session + queue (bukan database)
- Gzip + HTTP/2 di Nginx
- Static assets versioned via Vite manifest
- DB indexes on foreign keys + frequently queried columns
- Lighthouse target booking engine: ≥ 90 mobile

---

## Log Rotation

`/etc/logrotate.d/hotel`:
```
/var/www/hotel/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 664 www-data www-data
    sharedscripts
    postrotate
        /bin/kill -USR1 $(cat /var/run/php/php8.3-fpm.pid) 2>/dev/null || true
    endscript
}
```

---

## Security Hardening

- SSH key-only authentication, disable password
- UFW: allow 22, 80, 443 only
- Fail2ban untuk SSH brute-force
- MySQL bind `127.0.0.1` only
- Redis bind `127.0.0.1` + `requirepass`
- `APP_DEBUG=false` di production
- Semua API keys encrypted at rest (`Crypt::encryptString`)
- `SESSION_SECURE_COOKIE=true` (HTTPS only)
- HSTS preload enabled
- Rate limiting di route API dan login
- CSP headers di Nginx
- Audit log append-only
