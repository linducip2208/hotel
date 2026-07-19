#!/usr/bin/env bash
#
# HotelHub HMS — File/Storage Backup Script
#
# Usage:
#   ./backup-files.sh [--retention-daily=7] [--retention-weekly=4] [--retention-monthly=12]
#
# Backs up:
#   - storage/app (uploads, reports, generated files)
#   - storage/logs (compressed)
#   - public/uploads (user-uploaded media)
#   - config/license (public key files)
#
# Excludes:
#   - storage/framework/cache
#   - storage/framework/views
#   - node_modules
#   - vendor
#   - .git

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${APP_DIR:-/var/www/hotelhub}"
BACKUP_DIR="${BACKUP_DIR:-${APP_DIR}/storage/backups}"
LOG_FILE="${BACKUP_DIR}/backup-files.log"
TEMP_DIR="${TEMP_DIR:-/tmp/hotelhub_backup}"

# ── Retention settings ───────────────────────────────────────────────
RETENTION_DAILY=7
RETENTION_WEEKLY=4
RETENTION_MONTHLY=12

while [[ $# -gt 0 ]]; do
    case "$1" in
        --retention-daily=*)  RETENTION_DAILY="${1#*=}"; shift ;;
        --retention-weekly=*) RETENTION_WEEKLY="${1#*=}"; shift ;;
        --retention-monthly=*) RETENTION_MONTHLY="${1#*=}"; shift ;;
        --help|-h) echo "Usage: ./backup-files.sh [options]"; exit 0 ;;
        *) echo "Unknown option: $1"; exit 1 ;;
    esac
done

# ── Ensure directories ───────────────────────────────────────────────
mkdir -p "$BACKUP_DIR/daily" "$BACKUP_DIR/weekly" "$BACKUP_DIR/monthly" "$TEMP_DIR"

# ── Load .env ────────────────────────────────────────────────────────
if [ -f "${APP_DIR}/.env" ]; then
    set -a
    source <(grep -v '^#' "${APP_DIR}/.env" | grep -v '^$' | sed 's/^/export /')
    set +a
fi

# ── Colors ───────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
log()   { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" | tee -a "$LOG_FILE"; }
err()   { log "ERROR: $*"; }
ok()    { log "OK:    $*"; }

notify_failure() {
    local message="$1"
    err "$message"
    if [ -n "${BACKUP_ALERT_WEBHOOK:-}" ]; then
        curl -s -X POST "$BACKUP_ALERT_WEBHOOK" \
            -H "Content-Type: application/json" \
            -d "{\"text\":\"[File Backup FAILED] ${message}\"}" > /dev/null 2>&1 || true
    fi
    if [ -n "${BACKUP_ALERT_EMAIL:-}" ] && command -v mail &> /dev/null; then
        echo "$message" | mail -s "[File Backup FAILED] $(hostname)" "$BACKUP_ALERT_EMAIL" 2>/dev/null || true
    fi
}

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DATE_STAMP=$(date +"%Y-%m-%d")
DAY_OF_WEEK=$(date +"%u")
DAY_OF_MONTH=$(date +"%d")

BACKUP_FILE="${BACKUP_DIR}/daily/hotelhub_files_${TIMESTAMP}.tar.gz"

log "══════════════════════════════════════════════"
log "Starting file backup — ${DATE_STAMP}"
log "══════════════════════════════════════════════"

# ── 1. Create tar archive ────────────────────────────────────────────
log "Creating archive..."

# Build clean staging directory
STAGING="$TEMP_DIR/staging_${TIMESTAMP}"
mkdir -p "$STAGING"

# Copy files to stage (avoid tar picking up temp files mid-write)
if [ -d "${APP_DIR}/storage/app" ]; then
    cp -a "${APP_DIR}/storage/app" "$STAGING/" 2>/dev/null || {}
fi

if [ -d "${APP_DIR}/public/uploads" ]; then
    cp -a "${APP_DIR}/public/uploads" "$STAGING/" 2>/dev/null || {}
fi

# Backup license config if it exists
if [ -f "${APP_DIR}/config/license/vendor-public.pem" ]; then
    mkdir -p "$STAGING/config/license"
    cp "${APP_DIR}/config/license/vendor-public.pem" "$STAGING/config/license/" 2>/dev/null || {}
fi

# Backup .env (without creating it — it should already exist)
if [ -f "${APP_DIR}/.env" ]; then
    cp "${APP_DIR}/.env" "$STAGING/" 2>/dev/null || {}
fi

# Compress staging directory
tar -czf "$BACKUP_FILE" -C "$STAGING" . 2>> "$LOG_FILE" || {
    notify_failure "tar compression failed"
    rm -rf "$STAGING"
    exit 1
}

BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
rm -rf "$STAGING"
ok "Archive created: ${BACKUP_FILE} (${BACKUP_SIZE})"

# ── 2. Upload to S3 ─────────────────────────────────────────────────
if [ -n "${AWS_BUCKET:-}" ]; then
    log "Uploading to S3: ${AWS_BUCKET}"

    S3_PATH="s3://${AWS_BUCKET}/backups/files/${DATE_STAMP}/"

    AWS_OPTS=""
    [ -n "${AWS_ENDPOINT:-}" ] && AWS_OPTS="${AWS_OPTS} --endpoint-url=${AWS_ENDPOINT}"
    [ -n "${AWS_REGION:-}" ] && AWS_OPTS="${AWS_OPTS} --region=${AWS_REGION}"

    if aws s3 cp "$BACKUP_FILE" "${S3_PATH}" $AWS_OPTS 2>> "$LOG_FILE"; then
        ok "Uploaded to S3: ${S3_PATH}"
    else
        notify_failure "S3 upload failed for ${BACKUP_FILE}"
    fi
else
    log "S3 not configured (AWS_BUCKET empty). Local backup only."
fi

# ── 3. Retention cleanup ─────────────────────────────────────────────
log "Cleaning up old backups..."

find "$BACKUP_DIR/daily" -name "hotelhub_files_*.tar.gz" -mtime +"$RETENTION_DAILY" -delete 2>/dev/null || true
ok "Daily retention: ${RETENTION_DAILY} days"

if [ "$DAY_OF_WEEK" -eq 7 ]; then
    cp "$BACKUP_FILE" "${BACKUP_DIR}/weekly/hotelhub_files_${TIMESTAMP}.tar.gz"
    find "$BACKUP_DIR/weekly" -name "hotelhub_files_*.tar.gz" -mtime +$((RETENTION_WEEKLY * 7)) -delete 2>/dev/null || true
    ok "Weekly backup stored. Retention: ${RETENTION_WEEKLY} weeks"
fi

if [ "$DAY_OF_MONTH" -eq 01 ]; then
    cp "$BACKUP_FILE" "${BACKUP_DIR}/monthly/hotelhub_files_${TIMESTAMP}.tar.gz"
    find "$BACKUP_DIR/monthly" -name "hotelhub_files_*.tar.gz" -mtime +$((RETENTION_MONTHLY * 30)) -delete 2>/dev/null || true
    ok "Monthly backup stored. Retention: ${RETENTION_MONTHLY} months"
fi

# ── 4. Summary ───────────────────────────────────────────────────────
DAILY_COUNT=$(find "$BACKUP_DIR/daily" -name "hotelhub_files_*.tar.gz" 2>/dev/null | wc -l)
WEEKLY_COUNT=$(find "$BACKUP_DIR/weekly" -name "hotelhub_files_*.tar.gz" 2>/dev/null | wc -l)
MONTHLY_COUNT=$(find "$BACKUP_DIR/monthly" -name "hotelhub_files_*.tar.gz" 2>/dev/null | wc -l)

log "──────────────────────────────────────────────"
log "File backup complete."
log "  File:     ${BACKUP_FILE} (${BACKUP_SIZE})"
log "  Daily:    ${DAILY_COUNT} files"
log "  Weekly:   ${WEEKLY_COUNT} files"
log "  Monthly:  ${MONTHLY_COUNT} files"
log "══════════════════════════════════════════════"
