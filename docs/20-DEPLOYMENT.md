# 20 — Deployment Guide

> Step-by-step untuk deploy HMS ke production. Cover: VPS standar (DigitalOcean, Vultr, Linode, Hetzner, Niagahoster, Biznet), Docker (recommended), local on-prem.

Audience: technical owner / sysadmin yang siapkan instalasi standalone, atau vendor team yang siapkan tenant SaaS.

---

## 1. Sizing

| Hotel size | Spec minimum | Spec recommended |
|---|---|---|
| < 30 kamar | 2 vCPU / 4 GB RAM / 60 GB SSD | 4 vCPU / 8 GB / 80 GB |
| 30-100 kamar | 4 vCPU / 8 GB / 120 GB | 8 vCPU / 16 GB / 200 GB |
| 100-300 kamar | 8 vCPU / 16 GB / 240 GB | 8 vCPU / 32 GB / 400 GB + DB replica |
| > 300 kamar / chain | 16 vCPU / 32 GB / 500 GB + DB cluster | Dedicated infra, lihat enterprise architecture |

Storage growth: estimate ~2 GB/tahun per 50 kamar (PII docs, photos, audit log).

---

## 2. Stack baseline

- Ubuntu 22.04 LTS atau Debian 12
- PHP 8.3 (FPM)
- Nginx (atau Caddy)
- PostgreSQL 16 (atau MySQL 8) — Postgres recommended
- Redis 7
- Meilisearch 1.x
- Node 20 (untuk asset build)
- Composer 2
- Supervisor (untuk queue worker)
- Cron (untuk scheduler)

Optional:
- Docker + Docker Compose
- Cloudflare Tunnel atau Tailscale (admin access)

---

## 3. Path A — Bare-metal VPS install

### 3.1 Server prep

```bash
# update
sudo apt update && sudo apt upgrade -y

# essentials
sudo apt install -y curl wget git unzip software-properties-common ufw fail2ban

# firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow OpenSSH
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable

# fail2ban (default config OK)
sudo systemctl enable --now fail2ban
```

### 3.2 PHP 8.3

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-pgsql php8.3-redis \
  php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath \
  php8.3-gd php8.3-imagick php8.3-intl php8.3-soap
```

`php.ini` tweaks (per `/etc/php/8.3/fpm/php.ini`):
```
memory_limit = 512M
post_max_size = 64M
upload_max_filesize = 32M
max_execution_time = 120
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
```

### 3.3 PostgreSQL

```bash
sudo apt install -y postgresql-16
sudo -u postgres psql -c "CREATE USER hms WITH PASSWORD 'STRONG_PW';"
sudo -u postgres psql -c "CREATE DATABASE hotel_main OWNER hms;"
```

`postgresql.conf` (`/etc/postgresql/16/main/postgresql.conf`):
```
shared_buffers = 1GB           # 25% of RAM
effective_cache_size = 3GB     # 75% of RAM
work_mem = 16MB
maintenance_work_mem = 256MB
max_connections = 200
```

### 3.4 Redis

```bash
sudo apt install -y redis-server
# bind 127.0.0.1, set requirepass di /etc/redis/redis.conf
sudo systemctl enable --now redis-server
```

### 3.5 Meilisearch

```bash
curl -L https://install.meilisearch.com | sh
sudo mv meilisearch /usr/local/bin/
# create systemd service
sudo tee /etc/systemd/system/meilisearch.service <<EOF
[Unit]
Description=Meilisearch
[Service]
ExecStart=/usr/local/bin/meilisearch --master-key=YOUR_MASTER_KEY --db-path /var/lib/meilisearch
Restart=always
User=meilisearch
[Install]
WantedBy=multi-user.target
EOF
sudo useradd -r meilisearch
sudo mkdir -p /var/lib/meilisearch && sudo chown meilisearch /var/lib/meilisearch
sudo systemctl enable --now meilisearch
```

### 3.6 Nginx

`/etc/nginx/sites-available/hotel`:
```nginx
server {
  listen 80;
  server_name hotelmandala.com www.hotelmandala.com;
  return 301 https://$host$request_uri;
}

server {
  listen 443 ssl http2;
  server_name hotelmandala.com www.hotelmandala.com;
  root /var/www/hotel/public;
  index index.php;

  ssl_certificate /etc/letsencrypt/live/hotelmandala.com/fullchain.pem;
  ssl_certificate_key /etc/letsencrypt/live/hotelmandala.com/privkey.pem;

  client_max_body_size 64M;

  add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
  add_header X-Frame-Options "SAMEORIGIN" always;
  add_header X-Content-Type-Options "nosniff" always;
  add_header Referrer-Policy "strict-origin-when-cross-origin" always;

  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }

  location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
  }

  location ~ /\.ht { deny all; }
  location ~ /\.env { deny all; }
}
```

`sudo ln -s /etc/nginx/sites-available/hotel /etc/nginx/sites-enabled/`

### 3.7 Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d hotelmandala.com -d www.hotelmandala.com
```

### 3.8 App deploy

```bash
sudo mkdir -p /var/www/hotel
sudo chown $USER:www-data /var/www/hotel
cd /var/www/hotel
git clone https://github.com/your-org/hotel.git .
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# edit .env (DB, Redis, Mail, APP_URL, MEILISEARCH_KEY, etc.)
php artisan migrate --force
php artisan storage:link
npm ci && npm run build
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 3.9 Supervisor (queue worker)

`/etc/supervisor/conf.d/hotel-worker.conf`:
```ini
[program:hotel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hotel/artisan queue:work redis --sleep=3 --tries=3 --timeout=120 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/hotel-worker.log
stopwaitsecs=180
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hotel-worker:*
```

### 3.10 Cron

```bash
sudo crontab -u www-data -e
```
```
* * * * * cd /var/www/hotel && php artisan schedule:run >> /dev/null 2>&1
```

### 3.11 License setup

```bash
cd /var/www/hotel
sudo -u www-data php artisan license:bootstrap
# kemudian akses https://hotelmandala.com/setup/wizard di browser
```

---

## 4. Path B — Docker Compose (recommended)

Repo include `docker-compose.yml`:

```yaml
services:
  app:
    image: hotelhub/hms:1.0
    env_file: .env
    volumes:
      - ./storage:/var/www/storage
      - ./public/uploads:/var/www/public/uploads
    depends_on: [db, redis, meilisearch]
    networks: [back, front]

  nginx:
    image: nginx:1.27
    ports: ["80:80", "443:443"]
    volumes:
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf:ro
      - ./docker/certs:/etc/nginx/certs:ro
      - ./public:/var/www/public:ro
    depends_on: [app]
    networks: [front]

  db:
    image: postgres:16
    environment:
      POSTGRES_USER: hms
      POSTGRES_PASSWORD: ${DB_PASSWORD}
      POSTGRES_DB: hotel_main
    volumes: [pgdata:/var/lib/postgresql/data]
    networks: [back]

  redis:
    image: redis:7
    command: redis-server --requirepass ${REDIS_PASSWORD}
    volumes: [redisdata:/data]
    networks: [back]

  meilisearch:
    image: getmeili/meilisearch:v1.10
    environment:
      MEILI_MASTER_KEY: ${MEILI_KEY}
    volumes: [meilidata:/meili_data]
    networks: [back]

  worker:
    image: hotelhub/hms:1.0
    env_file: .env
    command: php artisan queue:work redis --sleep=3 --tries=3
    depends_on: [app, redis]
    networks: [back]

  scheduler:
    image: hotelhub/hms:1.0
    env_file: .env
    command: |
      sh -c 'while true; do php artisan schedule:run; sleep 60; done'
    depends_on: [app]
    networks: [back]

volumes:
  pgdata: {}
  redisdata: {}
  meilidata: {}

networks:
  back:
  front:
```

Deploy:
```bash
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan license:bootstrap
# kemudian https://your-domain/setup/wizard
```

---

## 5. Path C — Niagahoster / Biznet shared / managed

Banyak owner hotel di Indonesia pilih **Niagahoster Cloud VPS** atau **Biznet GIO** untuk lokal latency.

Catatan:
- Niagahoster Cloud VPS support full root → bisa pakai Path A
- Hindari shared hosting (cPanel) — PHP version + queue worker biasanya tidak support
- Biznet GIO Cloud Compute: support container, plug-and-play
- Cloudways managed: also supported (DigitalOcean / Vultr underlying)

Vendor opsional sediakan **managed-host bundle**:
- Sediakan VPS Niagahoster di-pre-configure
- Owner cukup beli license + akses panel
- Vendor handle updates, backup, monitoring (optional add-on Rp 1.5jt/bln)

---

## 6. Deployment checklist (pre-go-live)

- [ ] HTTPS aktif, redirect 80→443
- [ ] HSTS preload
- [ ] License paired
- [ ] DB credentials di-rotate dari default
- [ ] Redis password set
- [ ] APP_KEY generated unique
- [ ] APP_DEBUG=false di production
- [ ] APP_ENV=production
- [ ] Mail SMTP configured & test send
- [ ] Backup cron configured (DB dump → S3/R2)
- [ ] Queue worker running (supervisorctl status)
- [ ] Scheduler running (test: `php artisan schedule:list`)
- [ ] Meilisearch indexed (`php artisan scout:import "App\Models\Guest"` dll)
- [ ] Property profile filled
- [ ] Tax config (PB1, PPN) set
- [ ] Initial admin user created
- [ ] 2FA enrolled untuk semua admin
- [ ] Payment provider added & tested (BYOK)
- [ ] OTA channel added & tested (BYOK)
- [ ] Booking engine theme customized
- [ ] DNS → server IP propagated
- [ ] Robots.txt + sitemap accessible
- [ ] Submit sitemap ke Google Search Console
- [ ] Smoke test: search → book → confirm → pay flow
- [ ] Smoke test: night audit run (testing date)
- [ ] Smoke test: housekeeping mobile login

---

## 7. Backup strategy

### Database
Cron daily 02:00:
```bash
pg_dump -Fc -U hms hotel_main | gzip | aws s3 cp - s3://hotel-backups/db/$(date +%Y%m%d).sql.gz \
  --sse AES256
```

Retention: 30 hari S3 standard, lifecycle policy → Glacier setelah 30 hari, delete setelah 365 hari.

### Storage / uploads
Cron daily 02:30: rsync `/var/www/hotel/storage/app/` ke S3-compatible bucket (R2, B2, Wasabi).

### Configuration
- `.env` → encrypted ke separate vault (1Password / Bitwarden).
- Public certs no need backup (regenerated via certbot).

### Restore drill
Quarterly: restore di staging server, verify integrity, document time-to-recover.

---

## 8. Monitoring

| Layer | Tool |
|---|---|
| Server health | Netdata / Glances / btop (manual SSH) |
| Application errors | Sentry (BYOK key) atau self-hosted GlitchTip |
| Uptime | UptimeRobot / BetterStack / Hetrix Tools |
| Logs | `storage/logs/*.log` rotated daily, optional ship ke Loki/Logtail |
| Metrics | Laravel Telescope (admin only, off in prod by default) |
| Slow queries | PostgreSQL `log_min_duration_statement = 1000` |
| Queue health | Horizon (kalau kita pilih horizon) atau cron alert kalau jobs failed > 50 |

---

## 9. Update procedure

```bash
cd /var/www/hotel
sudo -u www-data php artisan down --secret=your-token  # maintenance mode
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache
sudo supervisorctl restart hotel-worker:*
sudo -u www-data php artisan up
```

Or via deploy script `bin/deploy.sh` (provided di repo).

Auto-update (P2): vendor push update notification, owner click "Update now" → run script.

---

## 10. Performance tuning checklist

- OPcache enabled + preload
- Route/config/view cache
- Composer autoloader optimized
- DB indexes verified (`EXPLAIN ANALYZE` on slow queries)
- Redis sebagai cache + session + queue (not DB)
- Image CDN (Cloudflare / Bunny) untuk public assets
- Gzip / Brotli di Nginx
- HTTP/2 enabled
- Static asset versioned (Vite manifest hashing)

Lighthouse target booking engine: ≥90 mobile.

---

## 11. Network & ports

| Port | Service | Expose |
|---|---|---|
| 80 | HTTP (redirect) | Public |
| 443 | HTTPS | Public |
| 22 | SSH | IP whitelist atau Tailscale only |
| 5432 | PostgreSQL | localhost only |
| 6379 | Redis | localhost only |
| 7700 | Meilisearch | localhost only |

Admin panel di subdomain `admin.{domain}` dengan IP whitelist (kalau aktifkan vendor remote management).

---

## 12. Troubleshooting cepat

| Gejala | Cek dulu |
|---|---|
| 500 error | `storage/logs/laravel.log`, php-fpm log |
| Queue tidak jalan | `supervisorctl status`, Redis aktif? |
| Cron tidak run | `crontab -l`, `php artisan schedule:list` |
| Search blank | Meilisearch service, re-index |
| Asset 404 | `npm run build`, `php artisan storage:link` |
| Slow page | OPcache enable? config:cache? DB index? slow query log |
| License banner | `php artisan license:diagnostic` |
| OTA sync error | `storage/logs/channel-manager.log`, integration test endpoint |
| Email tidak terkirim | `mail.php` config, test via `php artisan tinker` `Mail::raw(...)` |

---

## 13. Open questions

1. **Bundle Docker image official ke registry public** vs private? Default: public minor version, private snapshot per license customer.
2. **Auto-update channel** stable vs beta — config flag `UPDATE_CHANNEL=stable|beta`?
3. **Multi-server cluster (LB + 2 app + DB replica)** untuk hotel besar — provide as separate guide?
4. **Edge cache (Cloudflare worker)** untuk pSEO halaman — Phase 2 kalau traffic tinggi.
