#!/usr/bin/env bash
#
# HotelHub HMS — Production Deployment Script (Client-side)
#
# Usage:
#   ./deploy.sh [--rollback] [--branch=main]
#
# Performs zero-downtime deployment:
#   1. Pull latest code
#   2. Install dependencies
#   3. Build frontend assets
#   4. Run migrations
#   5. Optimize caches
#   6. Restart queue workers
#   7. Health check
#   8. Rollback on failure

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/hotelhub}"
BRANCH="main"
HEALTH_URL="http://localhost/api/health"
ROLLBACK_ON_FAILURE=true
DEPLOY_LOG="${APP_DIR}/storage/logs/deploy.log"

# ── Colors ──────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; NC='\033[0m'
info()  { echo -e "${GREEN}[INFO]${NC}  $(date '+%H:%M:%S') $*" | tee -a "$DEPLOY_LOG"; }
warn()  { echo -e "${YELLOW}[WARN]${NC}  $(date '+%H:%M:%S') $*" | tee -a "$DEPLOY_LOG"; }
error() { echo -e "${RED}[ERROR]${NC} $(date '+%H:%M:%S') $*" | tee -a "$DEPLOY_LOG"; }
step()  { echo -e "\n${BLUE}▶ $*${NC}" | tee -a "$DEPLOY_LOG"; }

# ── Parse arguments ─────────────────────────────────────────────────
while [[ $# -gt 0 ]]; do
    case "$1" in
        --branch=*) BRANCH="${1#*=}"; shift ;;
        --no-rollback) ROLLBACK_ON_FAILURE=false; shift ;;
        --rollback) ROLLBACK=true; shift ;;
        --help|-h)
            echo "Usage: ./deploy.sh [--branch=<branch>] [--no-rollback] [--rollback]"
            exit 0
            ;;
        *) error "Unknown option: $1"; exit 1 ;;
    esac
done

# ── Ensure we are in app directory ───────────────────────────────────
cd "$APP_DIR" || { error "App directory ${APP_DIR} not found."; exit 1; }

# ── Create log directory ─────────────────────────────────────────────
mkdir -p "$(dirname "$DEPLOY_LOG")"

info "Starting deployment — branch: ${BRANCH}"
info "Log: ${DEPLOY_LOG}"

# ── Pre-deployment snapshot for rollback ─────────────────────────────
if [ "${ROLLBACK:-false}" = true ]; then
    step "Rolling back to previous commit"
    git log -1 --oneline
    git reset --hard HEAD~1
    info "Rolled back to $(git log -1 --oneline)"
    # Clear caches after rollback
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    # Restart queue workers
    supervisorctl restart queue-default: 2>/dev/null || true
    info "Rollback complete."
    exit 0
fi

# ── Save pre-deployment state ────────────────────────────────────────
PREV_COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
PREV_MIGRATION_BATCH=$(php artisan migrate:status 2>/dev/null | tail -1 | awk '{print $2}' || echo "unknown")

info "Pre-deployment state:"
info "  Commit:  ${PREV_COMMIT}"
info "  Batch:   ${PREV_MIGRATION_BATCH}"

# ── 1. Pull latest code ──────────────────────────────────────────────
step "1/7  Pulling latest code from origin/${BRANCH}"
git fetch origin "$BRANCH" 2>&1 | tee -a "$DEPLOY_LOG"
git checkout "$BRANCH" 2>&1 | tee -a "$DEPLOY_LOG"
git reset --hard "origin/${BRANCH}" 2>&1 | tee -a "$DEPLOY_LOG"

NEW_COMMIT=$(git rev-parse --short HEAD)
if [ "$PREV_COMMIT" = "$NEW_COMMIT" ]; then
    info "Already at latest commit (${NEW_COMMIT}). Nothing to deploy."
    exit 0
fi
info "Deploying: ${PREV_COMMIT} → ${NEW_COMMIT}"

# ── 2. Install PHP dependencies ──────────────────────────────────────
step "2/7  Installing Composer dependencies (--no-dev)"
composer install --no-dev --optimize-autoloader --no-interaction --no-progress 2>&1 | tee -a "$DEPLOY_LOG"

# ── 3. Build frontend assets ─────────────────────────────────────────
step "3/7  Building frontend assets"
if [ -f "package.json" ]; then
    npm ci --no-audit --no-fund 2>&1 | tee -a "$DEPLOY_LOG" || {
        warn "npm ci failed. Trying npm install..."
        npm install --no-audit --no-fund 2>&1 | tee -a "$DEPLOY_LOG"
    }
    npm run build 2>&1 | tee -a "$DEPLOY_LOG"
    info "Frontend assets built."
else
    info "No package.json — skipping frontend build."
fi

# ── 4. Run database migrations ───────────────────────────────────────
step "4/7  Running database migrations"
php artisan migrate --force --no-interaction 2>&1 | tee -a "$DEPLOY_LOG" || {
    error "Migration failed!"
    if [ "$ROLLBACK_ON_FAILURE" = true ]; then
        error "Rolling back to ${PREV_COMMIT}..."
        git reset --hard "$PREV_COMMIT"
        php artisan migrate:rollback --step=1 --force --no-interaction 2>/dev/null || true
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        exit 1
    fi
    exit 1
}
info "Migrations complete."

# ── 5. Optimize caches ───────────────────────────────────────────────
step "5/7  Optimizing caches"
php artisan optimize:clear 2>/dev/null || {
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
}

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
info "Caches optimized."

# ── 6. Restart queue workers ─────────────────────────────────────────
step "6/7  Restarting queue workers"

# Signal running workers to restart after current job
php artisan queue:restart 2>&1 | tee -a "$DEPLOY_LOG"

# If using supervisor, restart the worker groups
if command -v supervisorctl &> /dev/null; then
    supervisorctl restart queue-default: 2>/dev/null || true
    supervisorctl restart queue-notifications: 2>/dev/null || true
    supervisorctl restart queue-beat: 2>/dev/null || true
    info "Queue workers restarted via supervisor."
else
    info "Supervisor not found. Workers will restart via queue:restart signal."
fi

# ── 7. Health check ──────────────────────────────────────────────────
step "7/7  Running health check"

# Wait briefly for services to stabilize
sleep 3

# Perform health check
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -m 10 "$HEALTH_URL" 2>/dev/null || echo "000")

if [ "$HTTP_CODE" = "200" ]; then
    info "Health check passed: HTTP ${HTTP_CODE} from ${HEALTH_URL}"
else
    error "Health check failed: HTTP ${HTTP_CODE} from ${HEALTH_URL}"

    if [ "$ROLLBACK_ON_FAILURE" = true ]; then
        error "Rolling back to ${PREV_COMMIT}..."
        git reset --hard "$PREV_COMMIT"

        # Rollback migrations if needed
        # php artisan migrate:rollback --step=1 --force --no-interaction 2>/dev/null || true

        php artisan optimize:clear
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache

        supervisorctl restart queue-default: 2>/dev/null || true

        error "Rollback complete. Check logs for details."
        exit 1
    fi

    warn "Rollback disabled. Manual intervention required."
    exit 1
fi

# ── Deployment summary ───────────────────────────────────────────────
info "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
info "Deployment successful!"
info "  Branch:  ${BRANCH}"
info "  From:    ${PREV_COMMIT}"
info "  To:      ${NEW_COMMIT}"
info "  Health:  HTTP ${HTTP_CODE}"
info "  Time:    $(date '+%Y-%m-%d %H:%M:%S')"
info "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
