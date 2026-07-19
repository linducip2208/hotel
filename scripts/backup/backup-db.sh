#!/usr/bin/env bash
#
# HotelHub HMS — Database Backup Script
#
# Usage:
#   ./backup-db.sh [--retention-daily=7] [--retention-weekly=4] [--retention-monthly=12]
#
# Performs:
#   1. mysqldump of the application database
#   2. Compress with gzip
#   3. Upload to S3 (or S3-compatible)
#   4. Retention: daily 7 days, weekly 4 weeks, monthly 12 months
#   5. Log result
#   6. Alert on failure (email/webhook)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${APP_DIR:-/var/www/hotelhub}"
BACKUP_DIR="${BACKUP_DIR:-${APP_DIR}/storage/backups}"
LOG_FILE="${BACKUP_DIR}/backup-db.log"

# ── Retention settings ───────────────────────────────────────────────
RETENTION_DAILY=7
RETENTION_WEEKLY=4
RETENTION_MONTHLY=12

# ── Parse arguments ──────────────────────────────────────────────────
while [[ $# -gt 0 ]]; do
    case "$1" in
        --retention-daily=*)  RETENTION_DAILY="${1#*=}"; shift ;;
        --retention-weekly=*) RETENTION_WEEKLY="${1#*=}"; shift ;;
        --retention-monthly=*) RETENTION_MONTHLY="${1#*=}"; shift ;;
        --help|-h)
            echo "Usage: ./backup-db.sh [--retention-daily=7] [--retention-weekly=4] [--retention-monthly=12]"
            exit 0
            ;;
        *) echo "Unknown option: $1"; exit 1 ;;
    esac
done

# ── Ensure directories ───────────────────────────────────────────────
mkdir -p "$BACKUP_DIR"
mkdir -p "$BACKUP_DIR/daily"
mkdir -p "$BACKUP_DIR/weekly"
mkdir -p "$BACKUP_DIR/monthly"

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

# ── Colors ───────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
log()   { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" | tee -a "$LOG_FILE"; }
err()   { log "ERROR: $*"; }
ok()    { log "OK:    $*"; }

# ── Notification helpers ─────────────────────────────────────────────
notify_failure() {
    local message="$1"
    err "$message"

    # Send webhook alert if configured
    if [ -n "${BACKUP_ALERT_WEBHOOK:-}" ]; then
        curl -s -X POST "$BACKUP_ALERT_WEBHOOK" \
            -H "Content-Type: application/json" \
            -d "{\"text\":\"[DB Backup FAILED] ${message}\"}" \
            > /dev/null 2>&1 || true
    fi

    # Send email alert if mail is configured
    if [ -n "${BACKUP_ALERT_EMAIL:-}" ] && command -v mail &> /dev/null; then
        echo "$message" | mail -s "[DB Backup FAILED] $(hostname)" "$BACKUP_ALERT_EMAIL" 2>/dev/null || true
    fi
}

# ── Timestamps ───────────────────────────────────────────────────────
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DATE_STAMP=$(date +"%Y-%m-%d")
DAY_OF_WEEK=$(date +"%u")   # 1=Monday, 7=Sunday
DAY_OF_MONTH=$(date +"%d")

# ── Backup file name ─────────────────────────────────────────────────
BACKUP_FILE="${BACKUP_DIR}/daily/hotelhub_db_${TIMESTAMP}.sql.gz"

log "══════════════════════════════════════════════"
log "Starting database backup — ${DATE_STAMP}"
log "══════════════════════════════════════════════"

# ── 1. Create database dump ──────────────────────────────────────────
log "Dumping database: ${DB_DATABASE}"

MYSQLDUMP_OPTS="--single-transaction --quick --lock-tables=false"
MYSQLDUMP_OPTS="${MYSQLDUMP_OPTS} --routines --triggers --events"
MYSQLDUMP_OPTS="${MYSQLDUMP_OPTS} --set-gtid-purged=OFF"
MYSQLDUMP_OPTS="${MYSQLDUMP_OPTS} --no-tablespaces"

if mysqldump \
    -h "$DB_HOST" -P "$DB_PORT" \
    -u "$DB_USERNAME" -p"$DB_PASSWORD" \
    $MYSQLDUMP_OPTS \
    "$DB_DATABASE" 2>> "$LOG_FILE" | gzip > "$BACKUP_FILE"; then

    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    ok "Dump created: ${BACKUP_FILE} (${BACKUP_SIZE})"
else
    notify_failure "mysqldump failed for ${DB_DATABASE}"
    exit 1
fi

# ── 2. Upload to S3 ─────────────────────────────────────────────────
if [ -n "${AWS_BUCKET:-}" ]; then
    log "Uploading to S3: ${AWS_BUCKET}"

    S3_PATH="s3://${AWS_BUCKET}/backups/database/${DATE_STAMP}/"

    AWS_OPTS=""
    [ -n "${AWS_ENDPOINT:-}" ] && AWS_OPTS="${AWS_OPTS} --endpoint-url=${AWS_ENDPOINT}"
    [ -n "${AWS_REGION:-}" ] && AWS_OPTS="${AWS_OPTS} --region=${AWS_REGION}"

    if aws s3 cp "$BACKUP_FILE" "${S3_PATH}" $AWS_OPTS 2>> "$LOG_FILE"; then
        ok "Uploaded to S3: ${S3_PATH}"
    else
        notify_failure "S3 upload failed for ${BACKUP_FILE}"
        # Continue — don't exit, we still have local copy
    fi
else
    log "S3 not configured (AWS_BUCKET empty). Skipping remote upload."
fi

# ── 3. Retention cleanup ─────────────────────────────────────────────
log "Cleaning up old backups..."

# Daily: keep last N days
find "$BACKUP_DIR/daily" -name "hotelhub_db_*.sql.gz" -mtime +"$RETENTION_DAILY" -delete 2>/dev/null || true
ok "Daily retention: keep last ${RETENTION_DAILY} days"

# Weekly: keep Sunday backup, rotate after N weeks
if [ "$DAY_OF_WEEK" -eq 7 ]; then
    cp "$BACKUP_FILE" "${BACKUP_DIR}/weekly/hotelhub_db_${TIMESTAMP}.sql.gz"
    find "$BACKUP_DIR/weekly" -name "hotelhub_db_*.sql.gz" -mtime +$((RETENTION_WEEKLY * 7)) -delete 2>/dev/null || true
    ok "Weekly backup stored (Sunday). Retention: ${RETENTION_WEEKLY} weeks"
fi

# Monthly: keep 1st-of-month backup, rotate after N months
if [ "$DAY_OF_MONTH" -eq 01 ]; then
    cp "$BACKUP_FILE" "${BACKUP_DIR}/monthly/hotelhub_db_${TIMESTAMP}.sql.gz"
    find "$BACKUP_DIR/monthly" -name "hotelhub_db_*.sql.gz" -mtime +$((RETENTION_MONTHLY * 30)) -delete 2>/dev/null || true
    ok "Monthly backup stored (1st). Retention: ${RETENTION_MONTHLY} months"
fi

# ── 4. Summary ───────────────────────────────────────────────────────
DAILY_COUNT=$(find "$BACKUP_DIR/daily" -name "hotelhub_db_*.sql.gz" 2>/dev/null | wc -l)
WEEKLY_COUNT=$(find "$BACKUP_DIR/weekly" -name "hotelhub_db_*.sql.gz" 2>/dev/null | wc -l)
MONTHLY_COUNT=$(find "$BACKUP_DIR/monthly" -name "hotelhub_db_*.sql.gz" 2>/dev/null | wc -l)

log "──────────────────────────────────────────────"
log "Backup complete."
log "  File:     ${BACKUP_FILE} (${BACKUP_SIZE})"
log "  Daily:    ${DAILY_COUNT} files"
log "  Weekly:   ${WEEKLY_COUNT} files"
log "  Monthly:  ${MONTHLY_COUNT} files"
log "══════════════════════════════════════════════"
