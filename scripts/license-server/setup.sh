#!/usr/bin/env bash
#
# HotelHub HMS — License Server Setup Script
# Target: Ubuntu 22.04+ / Debian 12+ VPS
#
# Usage:
#   chmod +x setup.sh
#   sudo ./setup.sh
#
# This script will:
#   1. Update system packages
#   2. Install PHP 8.3, MariaDB, Nginx, Composer, Redis
#   3. Clone the hotel app repo
#   4. Configure .env for license server
#   5. Run composer install + migrations
#   6. Generate RSA keypair for license signing
#   7. Set correct file permissions
#   8. Configure Nginx virtual host + SSL via Let's Encrypt
#   9. Setup supervisor for queue worker + heartbeat listener
#   10. Setup cron jobs
#   11. Create initial admin user
#   12. Print all credentials

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="/var/www/hotelhub-license"
REPO_URL="${REPO_URL:-https://github.com/hotelhub/hms.git}"
REPO_BRANCH="${REPO_BRANCH:-main}"

# ── Colors ──────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; NC='\033[0m'; BOLD='\033[1m'
info()  { echo -e "${GREEN}[INFO]${NC}  $*"; }
warn()  { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error() { echo -e "${RED}[ERROR]${NC} $*"; }
step()  { echo -e "\n${BLUE}${BOLD}▶ $*${NC}\n"; }

# ── Pre-flight checks ───────────────────────────────────────────────
if [[ "$EUID" -ne 0 ]]; then
    error "This script must be run as root (sudo)."
    exit 1
fi

ARCH="$(dpkg --print-architecture)"
OS_ID="$(. /etc/os-release && echo "$ID")"
OS_VERSION="$(. /etc/os-release && echo "$VERSION_ID")"
info "Detected: $OS_ID $OS_VERSION ($ARCH)"

# ── Interactive config ──────────────────────────────────────────────
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  HotelHub License Server — Setup"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

read -rp "License server domain (e.g. license.hotelhub.id): " LICENSE_DOMAIN
LICENSE_DOMAIN="${LICENSE_DOMAIN:-license.hotelhub.id}"

read -rp "Admin email: " ADMIN_EMAIL
ADMIN_EMAIL="${ADMIN_EMAIL:-ops@hotelhub.id}"

read -rsp "Admin password: " ADMIN_PASSWORD
echo ""
ADMIN_PASSWORD="${ADMIN_PASSWORD:-$(openssl rand -base64 24)}"

read -rsp "MySQL root password [auto-generate if blank]: " MYSQL_ROOT_PASS
echo ""
MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-$(openssl rand -base64 24)}"

read -rsp "MySQL app password [auto-generate if blank]: " MYSQL_APP_PASS
echo ""
MYSQL_APP_PASS="${MYSQL_APP_PASS:-$(openssl rand -base64 24)}"

DB_DATABASE="${DB_DATABASE:-hotel_license}"
DB_USERNAME="${DB_USERNAME:-hotel}"

read -rp "Git repo URL [${REPO_URL}]: " INPUT_REPO
REPO_URL="${INPUT_REPO:-$REPO_URL}"

read -rp "Git branch [${REPO_BRANCH}]: " INPUT_BRANCH
REPO_BRANCH="${INPUT_BRANCH:-$REPO_BRANCH}"

echo ""
info "Configuration collected. Starting installation..."

# ═══════════════════════════════════════════════════════════════════
# 1. Update system packages
# ═══════════════════════════════════════════════════════════════════
step "1/12  Updating system packages"
apt-get update -y && apt-get upgrade -y
apt-get install -y software-properties-common curl wget unzip git \
    acl nginx mariadb-server mariadb-client redis-server supervisor \
    certbot python3-certbot-nginx logrotate

# ═══════════════════════════════════════════════════════════════════
# 2. Install PHP 8.3 + extensions
# ═══════════════════════════════════════════════════════════════════
step "2/12  Installing PHP 8.3"

if ! command -v php8.3 &> /dev/null; then
    add-apt-repository -y ppa:ondrej/php
    apt-get update -y
fi

apt-get install -y \
    php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl \
    php8.3-bcmath php8.3-gd php8.3-zip php8.3-intl \
    php8.3-redis php8.3-opcache php8.3-gmp php8.3-imagick

# Configure PHP-FPM for production
PHP_FPM_POOL="/etc/php/8.3/fpm/pool.d/www.conf"
if [[ -f "$PHP_FPM_POOL" ]]; then
    sed -i 's/^pm\.max_children = .*/pm.max_children = 40/' "$PHP_FPM_POOL"
    sed -i 's/^pm\.start_servers = .*/pm.start_servers = 8/' "$PHP_FPM_POOL"
    sed -i 's/^pm\.min_spare_servers = .*/pm.min_spare_servers = 4/' "$PHP_FPM_POOL"
    sed -i 's/^pm\.max_spare_servers = .*/pm.max_spare_servers = 10/' "$PHP_FPM_POOL"
    sed -i 's/^;pm\.max_requests = .*/pm.max_requests = 500/' "$PHP_FPM_POOL"
fi

# PHP ini production tuning
PHP_INI="/etc/php/8.3/fpm/php.ini"
if [[ -f "$PHP_INI" ]]; then
    sed -i 's/^memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
    sed -i 's/^max_execution_time = .*/max_execution_time = 300/' "$PHP_INI"
    sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 64M/' "$PHP_INI"
    sed -i 's/^post_max_size = .*/post_max_size = 64M/' "$PHP_INI"
fi

systemctl enable php8.3-fpm
systemctl restart php8.3-fpm
info "PHP 8.3 installed and configured."

# ═══════════════════════════════════════════════════════════════════
# 3. Install Composer
# ═══════════════════════════════════════════════════════════════════
step "3/12  Installing Composer"
if ! command -v composer &> /dev/null; then
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
        warn "Composer installer checksum mismatch — downloading directly."
        curl -sS https://getcomposer.org/composer.phar -o /usr/local/bin/composer
    else
        php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    fi
    rm -f composer-setup.php
    chmod +x /usr/local/bin/composer
fi
composer --version
info "Composer installed."

# ═══════════════════════════════════════════════════════════════════
# 4. Configure MariaDB
# ═══════════════════════════════════════════════════════════════════
step "4/12  Configuring MariaDB"
systemctl enable mariadb
systemctl start mariadb

# Secure MariaDB and create database
mysql -u root <<SQL
ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASS}';
CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${MYSQL_APP_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost';
FLUSH PRIVILEGES;
SQL

info "MariaDB configured. Database '${DB_DATABASE}' created."

# ═══════════════════════════════════════════════════════════════════
# 5. Configure Redis
# ═══════════════════════════════════════════════════════════════════
step "5/12  Configuring Redis"
REDIS_CONF="/etc/redis/redis.conf"
if [[ -f "$REDIS_CONF" ]]; then
    sed -i 's/^# maxmemory-policy .*/maxmemory-policy allkeys-lru/' "$REDIS_CONF"
    sed -i 's/^# maxmemory .*/maxmemory 256mb/' "$REDIS_CONF"
    # Require password if set
    if [[ -n "${REDIS_PASSWORD:-}" ]]; then
        sed -i "s/^# requirepass .*/requirepass ${REDIS_PASSWORD}/" "$REDIS_CONF"
    fi
fi
systemctl enable redis-server
systemctl restart redis-server
info "Redis configured."

# ═══════════════════════════════════════════════════════════════════
# 6. Clone repository
# ═══════════════════════════════════════════════════════════════════
step "6/12  Cloning application repository"
if [[ -d "$APP_DIR" ]]; then
    warn "Directory ${APP_DIR} already exists."
    read -rp "Remove and re-clone? [y/N]: " RECLONE
    if [[ "$RECLONE" =~ ^[Yy]$ ]]; then
        rm -rf "$APP_DIR"
        git clone --branch "$REPO_BRANCH" --depth 1 "$REPO_URL" "$APP_DIR"
    fi
else
    git clone --branch "$REPO_BRANCH" --depth 1 "$REPO_URL" "$APP_DIR"
fi
info "Repository cloned to ${APP_DIR}."

# ═══════════════════════════════════════════════════════════════════
# 7. Configure .env
# ═══════════════════════════════════════════════════════════════════
step "7/12  Configuring environment"
cd "$APP_DIR"

# Copy .env from our template
cp "${SCRIPT_DIR}/.env.example" .env

APP_KEY=$(php artisan key:generate --show --no-interaction 2>/dev/null || php artisan key:generate --show)

# Update .env with collected values
cat > .env <<ENVEOF
APP_MODE=vendor
APP_ENV=production
APP_KEY=${APP_KEY}
APP_URL=https://${LICENSE_DOMAIN}
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=en
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${MYSQL_APP_PASS}

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=database

LICENSE_SERVER_ROLE=issuer
LICENSE_TOKEN_TTL=31536000
LICENSE_VENDOR_BASE_URL=https://${LICENSE_DOMAIN}
LICENSE_PUBLIC_KEY_PATH=storage/app/vendor-public.pem
LICENSE_PUBLIC_KEY_HASH=

LICENSE_MAX_PROPERTIES_PER_PLAN=premium:10,enterprise:999
LICENSE_RATE_LIMIT_PER_HEARTBEAT=100

ADMIN_NAME="HotelHub Admin"
ADMIN_EMAIL=${ADMIN_EMAIL}
ADMIN_PASSWORD=${ADMIN_PASSWORD}

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@hotelhub.id"
MAIL_FROM_NAME="HotelHub License"
ENVEOF

info ".env configured."

# ═══════════════════════════════════════════════════════════════════
# 8. Composer install + migrations
# ═══════════════════════════════════════════════════════════════════
step "8/12  Installing PHP dependencies + running migrations"
composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Run migrations
php artisan migrate --force --no-interaction

# Run seeders if needed
php artisan db:seed --class=RolesAndPermissionsSeeder --force --no-interaction 2>/dev/null || true
php artisan db:seed --class=PlansSeeder --force --no-interaction 2>/dev/null || true

info "Dependencies installed and database migrated."

# ═══════════════════════════════════════════════════════════════════
# 9. Generate RSA keypair
# ═══════════════════════════════════════════════════════════════════
step "9/12  Generating RSA keypair for license signing"
PRIVATE_KEY_PATH="${APP_DIR}/storage/app/vendor-private.pem"
PUBLIC_KEY_PATH="${APP_DIR}/storage/app/vendor-public.pem"

mkdir -p "$(dirname "$PRIVATE_KEY_PATH")"

if [[ -f "$PRIVATE_KEY_PATH" ]]; then
    warn "Keypair already exists. Backing up old keys..."
    mv "$PRIVATE_KEY_PATH" "${PRIVATE_KEY_PATH}.bak.$(date +%s)"
    mv "$PUBLIC_KEY_PATH" "${PUBLIC_KEY_PATH}.bak.$(date +%s)" 2>/dev/null || true
fi

openssl genrsa -out "$PRIVATE_KEY_PATH" 2048
openssl rsa -in "$PRIVATE_KEY_PATH" -pubout -out "$PUBLIC_KEY_PATH"

# Set permissions
chmod 600 "$PRIVATE_KEY_PATH"
chmod 644 "$PUBLIC_KEY_PATH"

# Compute SHA256 hash of public key for .env
PUBKEY_HASH=$(openssl dgst -sha256 "$PUBLIC_KEY_PATH" | awk '{print $NF}')
echo "LICENSE_PUBLIC_KEY_HASH=${PUBKEY_HASH}" >> .env

info "RSA keypair generated."
info "Public key hash: ${PUBKEY_HASH}"

# ═══════════════════════════════════════════════════════════════════
# 10. Set file permissions
# ═══════════════════════════════════════════════════════════════════
step "10/12  Setting file permissions"
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod 600 "$APP_DIR/.env"
chmod 600 "$PRIVATE_KEY_PATH"

# Ensure storage/app is secure
chmod 700 "$APP_DIR/storage/app"
chmod 600 "$APP_DIR/storage/app/vendor-private.pem"

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

info "Permissions set and caches warmed."

# ═══════════════════════════════════════════════════════════════════
# 11. Setup Nginx
# ═══════════════════════════════════════════════════════════════════
step "11/12  Configuring Nginx virtual host"

# Copy nginx config
cp "${SCRIPT_DIR}/nginx.conf" "/etc/nginx/sites-available/${LICENSE_DOMAIN}.conf"
sed -i "s/license\.hotelhub\.id/${LICENSE_DOMAIN}/g" "/etc/nginx/sites-available/${LICENSE_DOMAIN}.conf"
sed -i "s|/var/www/license-server|${APP_DIR}|g" "/etc/nginx/sites-available/${LICENSE_DOMAIN}.conf"

ln -sf "/etc/nginx/sites-available/${LICENSE_DOMAIN}.conf" "/etc/nginx/sites-enabled/${LICENSE_DOMAIN}.conf"

# Remove default
rm -f /etc/nginx/sites-enabled/default

# Test nginx config
nginx -t

# Start with HTTP first (need HTTP for certbot)
systemctl enable nginx
systemctl restart nginx
info "Nginx configured with HTTP."

# ═══════════════════════════════════════════════════════════════════
# 12. SSL via Let's Encrypt
# ═══════════════════════════════════════════════════════════════════
step "12/12  Requesting SSL certificate"

# Check if domain resolves to this server
DOMAIN_IP=$(dig +short "$LICENSE_DOMAIN" 2>/dev/null || true)
SERVER_IP=$(curl -s ifconfig.me 2>/dev/null || true)

if [[ -n "$DOMAIN_IP" && "$DOMAIN_IP" == "$SERVER_IP" ]]; then
    certbot --nginx -d "$LICENSE_DOMAIN" --non-interactive --agree-tos \
        -m "$ADMIN_EMAIL" --redirect --hsts || {
        warn "Certbot failed. You can run it manually:"
        warn "  certbot --nginx -d ${LICENSE_DOMAIN}"
    }
else
    warn "Domain ${LICENSE_DOMAIN} does not point to this server (${SERVER_IP:-unknown})."
    warn "Skipping SSL. Run certbot manually after DNS is configured:"
    warn "  certbot --nginx -d ${LICENSE_DOMAIN}"
fi

# Reload nginx with final config
systemctl reload nginx 2>/dev/null || systemctl restart nginx
info "Nginx reloaded."

# ═══════════════════════════════════════════════════════════════════
# 13. Setup Supervisor for queue workers
# ═══════════════════════════════════════════════════════════════════
step "13/12  Configuring Supervisor"

cp "${SCRIPT_DIR}/supervisord.conf" /etc/supervisor/conf.d/hotelhub-license.conf

# Update paths in supervisor config
sed -i "s|/var/www/license-server|${APP_DIR}|g" /etc/supervisor/conf.d/hotelhub-license.conf

supervisorctl reread
supervisorctl update
supervisorctl start all 2>/dev/null || true

systemctl enable supervisor
info "Supervisor configured. Queue workers are running."

# ═══════════════════════════════════════════════════════════════════
# 14. Setup Cron jobs
# ═══════════════════════════════════════════════════════════════════
step "14/12  Configuring Cron jobs"

CRON_FILE="/etc/cron.d/hotelhub-license"
cat > "$CRON_FILE" <<CRONEOF
# HotelHub License Server — Scheduled Tasks
# Runs every minute via Laravel scheduler
* * * * * www-data php ${APP_DIR}/artisan schedule:run >> /dev/null 2>&1
CRONEOF

chmod 644 "$CRON_FILE"
info "Cron jobs configured."

# ═══════════════════════════════════════════════════════════════════
# 15. Create initial admin user
# ═══════════════════════════════════════════════════════════════════
step "15/12  Creating initial admin user"

php artisan tinker --execute="
\$admin = \App\Models\User::updateOrCreate(
    ['email' => '${ADMIN_EMAIL}'],
    [
        'name' => 'HotelHub Admin',
        'password' => bcrypt('${ADMIN_PASSWORD}'),
        'email_verified_at' => now(),
    ]
);
\$admin->assignRole('super-admin');
echo 'Admin user created/updated: ' . \$admin->email . PHP_EOL;
" 2>/dev/null || {
    warn "Could not create admin via tinker. Run manually:"
    warn "  php artisan license:create-admin --email=${ADMIN_EMAIL} --password=..."
}

# ═══════════════════════════════════════════════════════════════════
# Firewall
# ═══════════════════════════════════════════════════════════════════
step "16/12  Configuring firewall"
if command -v ufw &> /dev/null; then
    ufw allow 22/tcp   # SSH
    ufw allow 80/tcp   # HTTP
    ufw allow 443/tcp  # HTTPS
    ufw --force enable 2>/dev/null || true
    info "UFW firewall enabled (22, 80, 443)."
fi

# ═══════════════════════════════════════════════════════════════════
# Print credentials summary
# ═══════════════════════════════════════════════════════════════════
cat <<BANNER

╔════════════════════════════════════════════════════════════╗
║     HotelHub License Server — Setup Complete!              ║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║  URL:       https://${LICENSE_DOMAIN}
║  App Path:  ${APP_DIR}
║                                                            ║
║  Admin Panel: https://${LICENSE_DOMAIN}/admin
║  Admin Email: ${ADMIN_EMAIL}
║  Admin Pass:  ${ADMIN_PASSWORD}
║                                                            ║
║  MySQL Root:  ${MYSQL_ROOT_PASS}
║  MySQL App:   ${MYSQL_APP_PASS}
║  Database:    ${DB_DATABASE}
║                                                            ║
║  RSA Public Key Hash:                                      ║
║  ${PUBKEY_HASH}
║                                                            ║
║  IMPORTANT — Save these credentials securely!              ║
║  Store them in a password manager or encrypted vault.      ║
║                                                            ║
║  Next steps:                                               ║
║  1. Visit https://${LICENSE_DOMAIN}/admin                   ║
║  2. Issue licenses: php artisan license:issue              ║
║  3. Configure backups: scripts/backup/backup-db.sh         ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝

BANNER

# Save credentials to a secure file
CREDS_FILE="${APP_DIR}/storage/app/.credentials.txt"
cat > "$CREDS_FILE" <<CREDS
HotelHub License Server Credentials
====================================
Date: $(date -u +"%Y-%m-%dT%H:%M:%SZ")
Domain: https://${LICENSE_DOMAIN}
Admin Email: ${ADMIN_EMAIL}
Admin Password: ${ADMIN_PASSWORD}
MySQL Root: ${MYSQL_ROOT_PASS}
MySQL App: ${MYSQL_APP_PASS}
Database: ${DB_DATABASE}
RSA Public Key Hash: ${PUBKEY_HASH}
CREDS
chmod 600 "$CREDS_FILE"
info "Credentials saved to ${CREDS_FILE}"

info "Setup complete. Server is ready!"
