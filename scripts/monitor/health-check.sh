#!/usr/bin/env bash
#
# HotelHub HMS — Health Check Script
#
# Usage:
#   ./health-check.sh [--json]
#
# Check targets:
#   1. HTTP 200 from /api/health
#   2. Database connectivity
#   3. Redis connectivity
#   4. Queue worker running
#   5. Disk space > 10%
#   6. License not expired
#
# Output: JSON with all check results (suitable for UptimeRobot/Pingdom)

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/hotelhub}"
APP_URL="${APP_URL:-http://localhost}"
HEALTH_URL="${APP_URL}/api/health"
TIMEOUT=10
OUTPUT_JSON=false

# ── Parse arguments ──────────────────────────────────────────────────
if [[ "${1:-}" == "--json" ]]; then
    OUTPUT_JSON=true
fi

# ── Load .env ────────────────────────────────────────────────────────
if [ -f "${APP_DIR}/.env" ]; then
    set -a
    source <(grep -v '^#' "${APP_DIR}/.env" | grep -v '^$' | sed 's/^/export /')
    set +a
fi

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-hotel_main}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

REDIS_HOST="${REDIS_HOST:-127.0.0.1}"
REDIS_PORT="${REDIS_PORT:-6379}"
REDIS_PASSWORD="${REDIS_PASSWORD:-null}"

# ── Initialize results ───────────────────────────────────────────────
declare -A RESULTS
declare -A TIMINGS
OVERALL_OK=true
TOTAL_START=$(date +%s%N)

check() {
    local name="$1"
    local description="$2"
    shift 2

    local start=$(date +%s%N)
    local output
    local exit_code=0

    output=$("$@" 2>&1) || exit_code=$?

    local end=$(date +%s%N)
    local elapsed_ms=$(( (end - start) / 1000000 ))

    RESULTS["${name}"]="$exit_code"
    TIMINGS["${name}"]="$elapsed_ms"

    if [ $exit_code -ne 0 ]; then
        OVERALL_OK=false
    fi
}

# ── 1. HTTP health check ─────────────────────────────────────────────
check "http_health" "HTTP 200 from /api/health" \
    sh -c "curl -s -o /dev/null -w '%{http_code}' -m ${TIMEOUT} '${HEALTH_URL}' | grep -q '200'"

# ── 2. Database connectivity ─────────────────────────────────────────
check "database" "Database connectivity" \
    sh -c "mysqladmin ping -h '${DB_HOST}' -P '${DB_PORT}' -u '${DB_USERNAME}' -p'${DB_PASSWORD}' --silent 2>/dev/null"

# ── 3. Redis connectivity ───────────────────────────────────────────
check "redis" "Redis connectivity" \
    sh -c "
        if [ '${REDIS_PASSWORD}' != 'null' ] && [ -n '${REDIS_PASSWORD}' ]; then
            redis-cli -h '${REDIS_HOST}' -p '${REDIS_PORT}' -a '${REDIS_PASSWORD}' ping 2>/dev/null | grep -q 'PONG'
        else
            redis-cli -h '${REDIS_HOST}' -p '${REDIS_PORT}' ping 2>/dev/null | grep -q 'PONG'
        fi
    "

# ── 4. Queue worker running ─────────────────────────────────────────
check "queue_worker" "Queue worker process running" \
    sh -c "
        if command -v supervisorctl &> /dev/null; then
            supervisorctl status queue-default: 2>/dev/null | grep -q 'RUNNING'
        else
            pgrep -f 'artisan queue:work' > /dev/null
        fi
    "

# ── 5. Disk space > 10% ─────────────────────────────────────────────
check "disk_space" "Disk space > 10%" \
    sh -c "df -h / | awk 'NR==2 { gsub(/%/, \"\", \$5); print \$5 }' | awk '{ if (\$1 < 90) exit 0; else exit 1 }'"

# ── 6. License check (optional vendor server check) ──────────────────
LICENSE_OK=true
if [ -n "${LICENSE_VENDOR_BASE_URL:-}" ]; then
    LICENSE_STATUS=$(curl -s -o /dev/null -w '%{http_code}' -m 5 "${LICENSE_VENDOR_BASE_URL}/api/license/status" 2>/dev/null || echo "000")
    if [ "$LICENSE_STATUS" = "200" ]; then
        RESULTS["license_server"]="0"
        TIMINGS["license_server"]="0"
    else
        RESULTS["license_server"]="1"
        TIMINGS["license_server"]="0"
        OVERALL_OK=false
    fi
else
    # Check local license status via artisan
    check "license_local" "Local license valid" \
        sh -c "php ${APP_DIR}/artisan license:status --json 2>/dev/null | python3 -c \"import sys,json; d=json.load(sys.stdin); sys.exit(0 if d.get('valid') else 1)\" 2>/dev/null || php ${APP_DIR}/artisan license:status --json 2>/dev/null | php -r '\$d=json_decode(file_get_contents(\"php://stdin\"),true); exit(isset(\$d[\"valid\"]) && \$d[\"valid\"] ? 0 : 1);'"
fi

# ── 7. PHP-FPM running ──────────────────────────────────────────────
check "php_fpm" "PHP-FPM process running" \
    sh -c "pgrep -f 'php-fpm' > /dev/null || pgrep -f 'php8.3-fpm' > /dev/null"

# ── 8. Nginx running ────────────────────────────────────────────────
check "nginx" "Nginx process running" \
    sh -c "pgrep -x nginx > /dev/null"

# ── Compute total time ───────────────────────────────────────────────
TOTAL_END=$(date +%s%N)
TOTAL_MS=$(( (TOTAL_END - TOTAL_START) / 1000000 ))

# ── Output ───────────────────────────────────────────────────────────
if [ "$OUTPUT_JSON" = true ]; then
    # Build JSON output
    JSON_CHECKS=""
    FIRST=true
    for name in "${!RESULTS[@]}"; do
        if [ "$FIRST" = true ]; then FIRST=false; else JSON_CHECKS+=","; fi
        status=$([ "${RESULTS[$name]}" -eq 0 ] && echo "true" || echo "false")
        elapsed=${TIMINGS[$name]:-0}
        JSON_CHECKS+="{\"name\":\"$name\",\"ok\":$status,\"elapsed_ms\":$elapsed}"
    done

    cat <<JSON
{
  "status": $([ "$OVERALL_OK" = true ] && echo '"ok"' || echo '"degraded"'),
  "timestamp": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")",
  "hostname": "$(hostname)",
  "total_elapsed_ms": $TOTAL_MS,
  "checks": [$JSON_CHECKS]
}
JSON
else
    # Pretty-print terminal output
    echo "══════════════════════════════════════════════"
    echo "  HotelHub HMS — Health Check"
    echo "  $(date '+%Y-%m-%d %H:%M:%S') — $(hostname)"
    echo "══════════════════════════════════════════════"

    for name in "${!RESULTS[@]}"; do
        if [ "${RESULTS[$name]}" -eq 0 ]; then
            printf "  [✓] %-20s (%s ms)\n" "$name" "${TIMINGS[$name]}"
        else
            printf "  [✗] %-20s (%s ms)\n" "$name" "${TIMINGS[$name]}"
        fi
    done

    echo "──────────────────────────────────────────────"
    if [ "$OVERALL_OK" = true ]; then
        echo "  OVERALL: ✓ HEALTHY (${TOTAL_MS} ms)"
    else
        echo "  OVERALL: ✗ DEGRADED (${TOTAL_MS} ms)"
    fi
    echo "══════════════════════════════════════════════"
fi

# ── Exit code ────────────────────────────────────────────────────────
if [ "$OVERALL_OK" = true ]; then
    exit 0
else
    exit 1
fi
