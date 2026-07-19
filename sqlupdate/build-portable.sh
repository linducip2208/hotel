#!/usr/bin/env bash
# Build maximally-portable SQL dump dari hotel_main.
# Output: sqlupdate/hotel_main_portable.sql
#
# Compatible target: MySQL 5.7+, MySQL 8+, MariaDB 10.3+
# Tidak butuh database pre-created — file include CREATE DATABASE IF NOT EXISTS.
# Tolerant terhadap strict sql_mode di server tujuan.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUT="$ROOT/sqlupdate/hotel_main_portable.sql"
DB="hotel_main"
HOST="${DB_HOST:-127.0.0.1}"
PORT="${DB_PORT:-3306}"
USER="${DB_USER:-root}"
PASS="${DB_PASS:-}"

PASS_FLAG=""
[ -n "$PASS" ] && PASS_FLAG="-p$PASS"

echo "→ Dumping $DB from $HOST:$PORT..."

# Header: explicit overrides supaya import jalan di server config apapun
cat > "$OUT" <<'HEADER'
-- ════════════════════════════════════════════════════════════════════
-- Hotel HMS — Portable SQL Dump (schema + data)
-- Compatible: MySQL 5.7+, MySQL 8+, MariaDB 10.3+
-- ════════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
SET AUTOCOMMIT = 0;
SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET @OLD_TIME_ZONE = @@TIME_ZONE;
SET TIME_ZONE = '+00:00';

CREATE DATABASE IF NOT EXISTS `hotel_main`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE `hotel_main`;

-- ════════════════════════════════════════════════════════════════════
HEADER

# Body: dump schema + data dengan flag portable
mysqldump \
    -h "$HOST" -P "$PORT" -u "$USER" $PASS_FLAG \
    --single-transaction \
    --no-tablespaces \
    --column-statistics=0 \
    --hex-blob \
    --default-character-set=utf8mb4 \
    --skip-set-charset \
    --skip-add-locks \
    --no-create-db \
    --routines --triggers --events \
    --set-gtid-purged=OFF \
    "$DB" \
    | sed -E '
        # Hapus baris yang set MySQL-8-only character set
        /^\/\*!40101 SET @OLD_CHARACTER_SET/d
        /^\/\*!40101 SET @OLD_COLLATION_CONNECTION/d
        /^\/\*!40101 SET CHARACTER_SET_CLIENT/d
        /^\/\*!40103 SET @OLD_TIME_ZONE/d
        /^\/\*!40103 SET TIME_ZONE/d
        /^\/\*!40014 SET @OLD_/d
        /^\/\*!40014 SET FOREIGN_KEY_CHECKS/d
        /^\/\*!40014 SET UNIQUE_CHECKS/d
        /^\/\*!40101 SET @OLD_SQL_MODE/d
        /^\/\*!40101 SET SQL_MODE/d
        /^\/\*!40111 SET @OLD_SQL_NOTES/d
        /^\/\*!40111 SET SQL_NOTES/d
        /^\/\*!50503 SET NAMES/d
        # Drop DEFINER clauses (jika ada)
        s/DEFINER=`[^`]+`@`[^`]+` //g
        s/DEFINER=[^ ]+ //g
        # Pastikan collation aman
        s/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g
        s/utf8mb4_0900_as_cs/utf8mb4_unicode_ci/g
        s/utf8mb4_uca1400_ai_ci/utf8mb4_unicode_ci/g
        # MariaDB tidak punya beberapa storage engine MySQL 8 — fallback ke InnoDB
        s/ENGINE=MyISAM/ENGINE=InnoDB/g
        # Hapus ROW_FORMAT yang mungkin tidak didukung
        s/ ROW_FORMAT=DYNAMIC//g
        s/ ROW_FORMAT=COMPACT//g
    ' \
    >> "$OUT"

# Footer: restore session state
cat >> "$OUT" <<'FOOTER'

-- ════════════════════════════════════════════════════════════════════
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
SET TIME_ZONE = @OLD_TIME_ZONE;
SET SQL_MODE = @OLD_SQL_MODE;
COMMIT;

-- DONE: hotel_main portable dump
FOOTER

# Verify output
SIZE=$(stat -c %s "$OUT" 2>/dev/null || stat -f %z "$OUT" 2>/dev/null)
LINES=$(wc -l < "$OUT")
echo "✓ Built: $OUT"
echo "  Size:  $((SIZE / 1024)) KB"
echo "  Lines: $LINES"
