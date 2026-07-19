# SQL Update — Hotel HMS Database Snapshot (Portable)

Folder ini berisi dump SQL **portable** dari database `hotel_main` per **2026-05-05**.

Tested: **MySQL 5.7+, MySQL 8+, MariaDB 10.3+** — di server config strict mode pun masih jalan.

## Files

| File | Ukuran | Kapan dipakai |
|---|---|---|
| **`hotel_main_nodb.sql`** | 1.8 MB | **Pakai ini kalau nama database tujuan bukan `hotel_main`** (misalnya cPanel: `username_dbname`, atau `sql_hotel`). Tidak ada `CREATE DATABASE`/`USE` — semua tabel langsung dibuat di DB yang kamu pilih saat import. |
| `hotel_main_portable.sql` | 1.8 MB | Self-contained — auto `CREATE DATABASE IF NOT EXISTS hotel_main`. Pakai kalau nama DB-mu memang `hotel_main`. |
| `hotel_main_schema.sql` | 231 KB | Schema saja (167 tabel, indexes, FK, routines). |
| `hotel_main_data.sql` | 1.6 MB | Data saja (INSERT). Pakai bersama schema kalau load step-by-step. |
| `build-portable.sh` | 4 KB | Script build ulang dump dari local DB. |

### Mana yang dipakai?

**Database tujuan namanya `hotel_main`** → `hotel_main_portable.sql`
**Database tujuan namanya BEDA** (misal `sql_hotel`, `u123_hotel`, dll) → `hotel_main_nodb.sql`

## Cara Restore

### A. Database namanya `hotel_main` (default)

Tidak perlu create database dulu — file sudah include `CREATE DATABASE IF NOT EXISTS`:

```bash
mysql -u root -p < sqlupdate/hotel_main_portable.sql
```

### B. Database namanya BERBEDA (misal `sql_hotel`, `u123_hotel`)

**WAJIB pakai `hotel_main_nodb.sql`** dan specify nama DB di command line:

```bash
# Buat database dulu (sekali saja)
mysql -u root -p -e "CREATE DATABASE sql_hotel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import ke database itu
mysql -u root -p sql_hotel < sqlupdate/hotel_main_nodb.sql
```

Setelah import, **wajib update `.env`** Laravel-mu:
```
DB_DATABASE=sql_hotel
```
Lalu `php artisan config:clear`.

### C. Pakai cPanel / phpMyAdmin

1. Buat dulu database via cPanel → MySQL Databases (catat nama lengkapnya, biasanya `username_dbname`)
2. Login phpMyAdmin → pilih database itu
3. Klik tab **Import**
4. Upload **`hotel_main_nodb.sql`** (yang `nodb`, bukan yang `portable`)
5. Klik **Go**
6. Edit `.env` Laravel: `DB_DATABASE=username_dbname` sesuai yang dibuat di cPanel

## Kenapa "Portable"?

File ini sudah dibersihkan dari semua MySQL-8-only / vendor-specific syntax:

| Hal | Status |
|---|---|
| `DEFINER=` clauses | ✓ Stripped (tidak butuh user spesifik) |
| `utf8mb4_0900_*` collation (MySQL 8 only) | ✓ Replace ke `utf8mb4_unicode_ci` |
| `utf8mb4_uca1400_*` (MariaDB 10.10+) | ✓ Replace ke `utf8mb4_unicode_ci` |
| `ROW_FORMAT=DYNAMIC` | ✓ Removed (engine pilih default) |
| `SET FOREIGN_KEY_CHECKS=0` | ✓ Aktif saat import (avoid FK order issues) |
| `SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'` | ✓ Permissive — toleran terhadap strict mode |
| `SET NAMES utf8mb4` | ✓ Eksplisit, hindari encoding mismatch |
| `--column-statistics=0` | ✓ MariaDB-friendly |
| GTID purged | ✓ Disabled |
| `CREATE DATABASE IF NOT EXISTS` | ✓ Self-contained — tidak perlu create dulu |
| Encoding | ✓ utf8mb4 throughout |

## Stats

- **167** tables
- **100** rooms (60 Superior, 30 Deluxe, 10 Junior Suite, 10 lantai)
- **3** room types
- **2.838** rate calendar rows (~470 days × 3 types × 2 rate plans)
- **1.419** inventory rows
- **1.000** reservations (May 2025 – Aug 2026)
- **770** unique guests
- **673** folios + 2.285 folio charges + 673 payments

## Login Demo

| Role | Email | Password |
|---|---|---|
| Superadmin | `superadmin@demohotel.id` | `password123` |
| Manager | `manager@demohotel.id` | `password123` |
| Front Office | `fo@demohotel.id` | `password123` |
| Cashier | `cashier@demohotel.id` | `password123` |
| Housekeeping | `housekeeping@demohotel.id` | `password123` |
| POS Cashier | `pos_cashier@demohotel.id` | `password123` |
| Accountant | `accountant@demohotel.id` | `password123` |
| Auditor | `auditor@demohotel.id` | `password123` |
| Sales/Marketing | `sales_marketing@demohotel.id` | `password123` |
| IT Admin | `it_admin@demohotel.id` | `password123` |

## Generate Ulang Dump

Jalankan dari folder root project:

```bash
bash sqlupdate/build-portable.sh
```

Override koneksi via env var:
```bash
DB_HOST=db.example.com DB_USER=admin DB_PASS=secret bash sqlupdate/build-portable.sh
```

## Troubleshooting

**Error `Specified key was too long; max key length is 767 bytes`** (MySQL < 5.7 / MariaDB < 10.2):
```sql
SET GLOBAL innodb_large_prefix = ON;
SET GLOBAL innodb_file_format = Barracuda;
SET GLOBAL innodb_default_row_format = DYNAMIC;
```
Lalu re-import.

**Error `Unknown collation`**: server target terlalu lama (MySQL < 5.5). Upgrade dulu — utf8mb4 wajib untuk emoji & Unicode 7.0+.

**Error `Access denied for user`**: pastikan user MySQL punya privilege `CREATE`, `INSERT`, `ALTER`, dan `CREATE DATABASE` (atau database sudah pre-created dan user punya `ALL PRIVILEGES` di situ).
