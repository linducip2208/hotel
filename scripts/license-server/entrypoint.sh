#!/usr/bin/env bash
#
# HotelHub License Server — Docker Entrypoint
#
# Responsibilities:
#   1. Generate RSA keypair on first boot (if missing)
#   2. Wait for MariaDB to be ready
#   3. Run database migrations
#   4. Create default admin user if not exists
#   5. Write public key hash to .env
#   6. Cache Laravel config
#   7. Start supervisor

set -euo pipefail

echo "╔══════════════════════════════════════════╗"
echo "║  HotelHub License Server — Boot         ║"
echo "╚══════════════════════════════════════════╝"
echo ""

APP_DIR="/var/www"
PRIVATE_KEY="${APP_DIR}/storage/app/vendor-private.pem"
PUBLIC_KEY="${APP_DIR}/storage/app/vendor-public.pem"
ENV_FILE="${APP_DIR}/.env"

cd "$APP_DIR"

# ─────────────────────────────────────────────────────────────────
# 1. Generate RSA keypair on first boot
# ─────────────────────────────────────────────────────────────────
echo "▶ Checking RSA keypair..."

if [[ ! -f "$PRIVATE_KEY" ]] || [[ ! -f "$PUBLIC_KEY" ]]; then
    echo "  RSA keypair not found. Generating 2048-bit keypair..."
    mkdir -p "$(dirname "$PRIVATE_KEY")"

    openssl genrsa -out "$PRIVATE_KEY" 2048
    openssl rsa -in "$PRIVATE_KEY" -pubout -out "$PUBLIC_KEY"

    chmod 600 "$PRIVATE_KEY"
    chmod 644 "$PUBLIC_KEY"

    PUBKEY_HASH=$(openssl dgst -sha256 "$PUBLIC_KEY" | awk '{print $NF}')

    # Update .env with public key hash
    if grep -q "^LICENSE_PUBLIC_KEY_HASH=" "$ENV_FILE" 2>/dev/null; then
        sed -i "s/^LICENSE_PUBLIC_KEY_HASH=.*/LICENSE_PUBLIC_KEY_HASH=${PUBKEY_HASH}/" "$ENV_FILE"
    else
        echo "LICENSE_PUBLIC_KEY_HASH=${PUBKEY_HASH}" >> "$ENV_FILE"
    fi

    echo "  ✓ RSA keypair generated."
    echo "  Public key SHA256: ${PUBKEY_HASH}"
else
    echo "  ✓ RSA keypair exists."

    # Ensure hash is in .env even if keys exist
    if [[ -f "$PUBLIC_KEY" ]]; then
        PUBKEY_HASH=$(openssl dgst -sha256 "$PUBLIC_KEY" | awk '{print $NF}')
        if ! grep -q "^LICENSE_PUBLIC_KEY_HASH=" "$ENV_FILE" 2>/dev/null; then
            echo "LICENSE_PUBLIC_KEY_HASH=${PUBKEY_HASH}" >> "$ENV_FILE"
        fi
    fi
fi

# ─────────────────────────────────────────────────────────────────
# 2. Wait for MariaDB to be ready
# ─────────────────────────────────────────────────────────────────
echo "▶ Waiting for MariaDB..."

MAX_TRIES=30
TRIES=0
DB_HOST="${DB_HOST:-mariadb}"
DB_PORT="${DB_PORT:-3306}"
DB_PASSWORD="${DB_PASSWORD:-license_app_secret}"
DB_USERNAME="${DB_USERNAME:-hotel}"

while ! mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent 2>/dev/null; do
    TRIES=$((TRIES + 1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "  ✗ MariaDB not available after ${MAX_TRIES} attempts. Exiting."
        exit 1
    fi
    echo "  MariaDB not ready yet... (attempt ${TRIES}/${MAX_TRIES})"
    sleep 2
done

echo "  ✓ MariaDB is ready."

# ─────────────────────────────────────────────────────────────────
# 3. Run database migrations
# ─────────────────────────────────────────────────────────────────
echo "▶ Running database migrations..."
php artisan migrate --force --no-interaction
echo "  ✓ Migrations complete."

# ─────────────────────────────────────────────────────────────────
# 4. Create default admin user
# ─────────────────────────────────────────────────────────────────
echo "▶ Checking admin user..."

ADMIN_EMAIL="${ADMIN_EMAIL:-ops@hotelhub.id}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-}"

if [[ -z "$ADMIN_PASSWORD" ]]; then
    ADMIN_PASSWORD=$(openssl rand -base64 18 2>/dev/null || php -r 'echo bin2hex(random_bytes(12));')
    echo "  Auto-generated admin password: ${ADMIN_PASSWORD}"
fi

# Check if admin exists
php artisan tinker --execute="
\$exists = \App\Models\User::where('email', '${ADMIN_EMAIL}')->exists();
echo \$exists ? 'EXISTS' : 'NOT_EXISTS';
" 2>/dev/null | grep -q "EXISTS"
ADMIN_EXISTS=$?

if [ $ADMIN_EXISTS -ne 0 ]; then
    php artisan tinker --execute="
\$admin = \App\Models\User::create([
    'name' => 'HotelHub Admin',
    'email' => '${ADMIN_EMAIL}',
    'password' => bcrypt('${ADMIN_PASSWORD}'),
    'email_verified_at' => now(),
]);
if (method_exists(\$admin, 'assignRole')) {
    \$admin->assignRole('super-admin');
}
echo 'Admin created: ' . \$admin->email;
" 2>/dev/null

    echo "  ✓ Admin user created: ${ADMIN_EMAIL}"
else
    echo "  ✓ Admin user already exists: ${ADMIN_EMAIL}"
fi

# ─────────────────────────────────────────────────────────────────
# 5. Seed required data (idempotent)
# ─────────────────────────────────────────────────────────────────
echo "▶ Seeding required data..."
php artisan db:seed --class=RolesAndPermissionsSeeder --force --no-interaction 2>/dev/null || echo "  (Roles seeder skipped or already seeded)"
php artisan db:seed --class=PlansSeeder --force --no-interaction 2>/dev/null || echo "  (Plans seeder skipped or already seeded)"
echo "  ✓ Seeders complete."

# ─────────────────────────────────────────────────────────────────
# 6. Cache Laravel configuration
# ─────────────────────────────────────────────────────────────────
echo "▶ Optimizing Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
echo "  ✓ Caches warmed."

# ─────────────────────────────────────────────────────────────────
# 7. Set permissions
# ─────────────────────────────────────────────────────────────────
echo "▶ Setting file permissions..."
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod 600 "$APP_DIR/.env" 2>/dev/null || true
echo "  ✓ Permissions set."

# ─────────────────────────────────────────────────────────────────
# 8. Print boot summary
# ─────────────────────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════╗"
echo "║  License Server Ready                    ║"
echo "╠══════════════════════════════════════════╣"
echo "║  URL:       http://$(hostname -i 2>/dev/null || echo 'localhost')"
echo "║  Admin:     ${ADMIN_EMAIL}"
echo "║  DB Host:   ${DB_HOST}"
echo "╚══════════════════════════════════════════╝"
echo ""

# ─────────────────────────────────────────────────────────────────
# 9. Start supervisor
# ─────────────────────────────────────────────────────────────────
exec "$@"
