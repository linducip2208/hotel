#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."
echo "▶ Pulling latest..."
git pull origin main

echo "▶ composer install"
composer install --no-dev --optimize-autoloader

echo "▶ npm build"
npm ci --omit=dev
npm run build

echo "▶ php artisan down"
php artisan down --secret="${MAINTENANCE_SECRET:-deploy-token}"

echo "▶ migrate"
php artisan migrate --force

echo "▶ caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

if command -v supervisorctl >/dev/null 2>&1; then
    echo "▶ restart workers"
    sudo supervisorctl restart hotel-worker:* || true
fi

echo "▶ php artisan up"
php artisan up

echo "✓ Deploy done."
