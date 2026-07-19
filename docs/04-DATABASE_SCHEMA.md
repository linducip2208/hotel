# 04 — Database Schema

> ±60 tabel utama. Konvensi: snake_case, plural for tables, `id` PK, `*_id` FK, `created_at` `updated_at` soft `deleted_at` di mana relevan, `created_by_id` `updated_by_id` for audit.

---

## 1. Schema Layout

### Central DB (SaaS only — `central_db`)
- `tenants`, `domains`, `subscriptions`, `subscription_plans`, `central_users`, `central_billing`, `central_invoices`

### Tenant DB / Standalone DB (semua tabel di bawah)
- Auth & RBAC
- Master data (property, room, rate)
- Operations (reservation, folio, payment, housekeeping, POS)
- Accounting (GL, AR, AP)
- Integrations (provider, ota_mapping, webhook_log)
- AI usage
- Compliance (e-faktur, lapor_wna)
- pSEO
- License
- Audit & system

---

## 2. Auth & RBAC

### `users`
```
id, name, email (unique), email_verified_at, password,
phone, avatar_path, locale, timezone,
two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at,
last_login_at, last_login_ip,
is_active, remember_token,
created_at, updated_at, deleted_at
```

### `roles` (spatie/laravel-permission)
```
id, name, guard_name, description, created_at, updated_at
```

### `permissions`
```
id, name, guard_name, description, group_label, created_at, updated_at
```

### `model_has_roles`, `model_has_permissions`, `role_has_permissions` (pivot from spatie)

### `personal_access_tokens` (Sanctum)

### `sessions` (database driver)

---

## 3. Property & Master Data

### `properties`
```
id, name, slug (unique), legal_name,
property_type ENUM('hotel','villa','guesthouse','hostel','resort','serviced_apartment','homestay'),
star_rating, address, city, province, postal_code, country_code,
latitude, longitude,
phone, whatsapp, email, website,
npwp, hotel_license_number, pb1_rate (default 10.00), service_charge_rate, ppn_mode ENUM('final','non_final','exempt'),
checkin_time TIME, checkout_time TIME, night_audit_time TIME,
default_currency, default_language,
brand_logo_path, cover_photo_path,
description_id TEXT, description_en TEXT,
is_active, created_at, updated_at, deleted_at
```

### `property_translations`
```
id, property_id, locale, field, value
```

### `room_types`
```
id, property_id, code, name, slug,
max_occupancy, max_adults, max_children, default_adults,
size_sqm, bed_config, view_type,
description_id TEXT, description_en TEXT,
amenities JSON, photos JSON,
base_price NUMERIC(15,2), is_active,
created_at, updated_at, deleted_at
```

### `rooms`
```
id, property_id, room_type_id,
room_number (unique per property), floor,
status ENUM('clean','dirty','inspected','occupied','out_of_order','out_of_service'),
notes,
last_status_change_at, last_status_change_by_id,
created_at, updated_at, deleted_at
```

### `rate_plans`
```
id, property_id, room_type_id, code, name,
plan_type ENUM('bar','corporate','member','promo','package','derived'),
parent_rate_plan_id (for derived), derive_formula (e.g. "+10%"),
includes_breakfast, includes_lunch, includes_dinner,
cancellation_policy_id, payment_policy_id,
min_los, max_los, cta_days JSON, ctd_days JSON,
is_refundable, is_active,
valid_from DATE, valid_until DATE,
created_at, updated_at, deleted_at
```

### `rate_calendar`
```
id, rate_plan_id, date,
price NUMERIC(15,2), available_inventory INT,
restrictions JSON,
unique(rate_plan_id, date)
```

---

## 4. Reservations

### `reservations`
```
id, property_id, reservation_number (unique, format: PRO-YYYYMMDD-NNNN),
booking_source ENUM('direct','walkin','booking_com','agoda','traveloka','tiket','expedia','airbnb','agent','corporate','phone','email'),
ota_reservation_id (external ID from OTA),
status ENUM('booked','in_house','checked_out','cancelled','no_show'),
guest_id (primary), holder_email, holder_phone,
checkin_date, checkout_date, nights,
adults, children, infants,
rate_plan_id, total_room_charges, total_taxes, total_service_charge, total_extras,
grand_total, deposit_paid, balance_due,
currency, exchange_rate,
group_master_id (reservasi induk untuk group), is_group_master,
company_id (corporate), agent_id, market_segment,
notes, special_requests, internal_notes,
arrived_at, departed_at, cancelled_at, cancellation_reason,
created_by_id, updated_by_id,
created_at, updated_at, deleted_at
```

### `reservation_rooms` (1 reservation bisa multi-room)
```
id, reservation_id, room_id (nullable, assigned later), room_type_id,
checkin_date, checkout_date, adults, children,
rate_plan_id, room_rate_per_night,
guest_id (kalau beda dengan holder), share_with_guest_id,
status ENUM('booked','assigned','in_house','checked_out','cancelled','no_show'),
created_at, updated_at
```

### `reservation_status_logs`
```
id, reservation_id, from_status, to_status, changed_by_id, reason, changed_at
```

---

## 5. Guest

### `guests`
```
id, salutation, first_name, last_name, full_name (generated/computed),
email, phone, whatsapp,
dob, gender,
nationality, passport_number, passport_expiry,
ktp_number, npwp, address, city, country_code,
preferences JSON, allergies, dietary_requirements,
is_vip, vip_tier, blacklist_reason (nullable, set = blacklist),
loyalty_member_id (FK to loyalty_members),
total_stays, total_nights, total_spend,
ktp_path, passport_path, signature_path,
notes,
created_at, updated_at, deleted_at,
INDEX(email), INDEX(phone), INDEX(passport_number), INDEX(ktp_number)
```

### `guest_stays`
```
id, guest_id, reservation_id, checkin_date, checkout_date, total_spend, satisfaction_rating, created_at
```

---

## 6. Folio & Charges

### `folios`
```
id, reservation_id, folio_number (unique),
type ENUM('master','split','permanent','company','agent'),
opened_at, closed_at, status ENUM('open','closed','transferred'),
holder_name, holder_address, holder_npwp,
subtotal, total_taxes, total_service_charge, grand_total, paid_amount, balance,
currency, created_by_id, closed_by_id,
created_at, updated_at
```

### `charges`
```
id, folio_id, reservation_id (denorm), reservation_room_id,
charge_type ENUM('room','breakfast','minibar','laundry','spa','restaurant','bar','transport','phone','damage','tax','service_charge','adjustment','misc'),
description, quantity, unit_price, amount,
posted_by_id, posted_at, source_module, source_id,
voided_at, voided_by_id, void_reason,
charge_date,
created_at, updated_at
```

### `payments`
```
id, folio_id, reservation_id (denorm),
payment_method ENUM('cash','credit_card','debit_card','bank_transfer','virtual_account','qris','gopay','ovo','dana','shopeepay','linkaja','tripay','xendit','midtrans','doku','other'),
provider_id (FK to providers, nullable),
amount, currency, exchange_rate,
reference_number, gateway_transaction_id,
status ENUM('pending','authorized','captured','failed','refunded','partially_refunded'),
paid_at, captured_by_id,
metadata JSON,
created_at, updated_at
```

### `payment_refunds`
```
id, payment_id, amount, reason, refunded_by_id, refunded_at, gateway_refund_id, created_at
```

---

## 7. Housekeeping

### `housekeeping_tasks`
```
id, property_id, room_id,
task_type ENUM('checkout_clean','stayover_clean','inspection','deep_clean','linen_change','maintenance'),
status ENUM('pending','in_progress','completed','blocked'),
assigned_to_id, supervisor_id,
priority ENUM('normal','rush','vip'),
guest_id (kalau ada tamu di room), notes,
scheduled_for DATETIME, started_at, completed_at, inspected_at, inspected_by_id,
created_at, updated_at
```

### `lost_and_found`
```
id, property_id, room_id, guest_id (nullable),
description, photo_path, found_at, found_by_id,
status ENUM('stored','returned','disposed'),
returned_at, returned_by_id, return_method, notes,
created_at, updated_at
```

### `maintenance_orders`
```
id, property_id, room_id (nullable, kalau room-specific),
category ENUM('plumbing','electrical','hvac','furniture','tv_internet','door_lock','other'),
priority, status,
description, photo_path, requested_by_id, assigned_to_id,
estimated_cost, actual_cost,
requested_at, started_at, completed_at,
created_at, updated_at
```

---

## 8. POS

### `pos_outlets`
```
id, property_id, name, type ENUM('restaurant','bar','spa','laundry','minibar','room_service','transport','other'),
default_tax_rate, default_service_charge_rate,
is_active, created_at, updated_at
```

### `pos_items`
```
id, outlet_id, code, name_id, name_en,
category, subcategory,
price, cost_price (for margin calc),
recipe_id (BOM, Phase 2), is_taxable, tax_rate,
photo_path, is_active,
created_at, updated_at, deleted_at
```

### `pos_orders`
```
id, outlet_id, order_number, table_number, server_id,
guest_id (kalau ada), reservation_id (kalau charge to room), folio_id (kalau already posted),
status ENUM('open','sent_to_kitchen','served','closed','voided'),
subtotal, total_tax, total_service_charge, grand_total,
payment_method, payment_id (kalau bayar langsung, bukan charge to room),
notes, opened_at, closed_at, created_at, updated_at
```

### `pos_order_items`
```
id, pos_order_id, pos_item_id, quantity, unit_price, subtotal,
modifiers JSON, special_request, voided_at, voided_by_id, void_reason
```

---

## 9. Channel Manager

### `channels`
```
id, code (booking_com, agoda, traveloka, ...), display_name,
api_format, base_url, is_active, default_commission_rate,
created_at, updated_at
```

### `channel_credentials`
```
id, property_id, channel_id, hotel_id_external,
api_key_encrypted, secret_encrypted, extra_config JSON,
is_active, last_sync_at, last_sync_status,
created_at, updated_at
```

### `channel_room_mappings`
```
id, property_id, channel_id, room_type_id,
external_room_id, external_room_name, external_rate_plan_id,
unique(channel_id, room_type_id, external_room_id)
```

### `channel_sync_logs`
```
id, property_id, channel_id, action ENUM('push_ari','pull_bookings','push_rate','push_inventory'),
direction ENUM('outbound','inbound'),
payload JSON, response JSON, status ENUM('success','failed'), error_message,
started_at, completed_at, duration_ms,
created_at
```

### `channel_bookings`
```
id, channel_id, channel_booking_id (external),
reservation_id (after ingest), payload_raw JSON,
ingested_at, status ENUM('received','processed','duplicate','error'),
created_at
```

---

## 10. Integrations & BYOK Providers

### `providers` (universal table for all BYOK providers)
```
id, integration_type ENUM('ai','payment','sms','whatsapp','mail','door_lock','rate_shopper','ota','storage','captcha','accounting_export','other'),
name, slug, api_format,
base_url, api_key_encrypted, secret_encrypted, extra_headers JSON,
default_model (for AI), capabilities JSON,
is_active, is_default,
display_order,
test_status, last_tested_at,
notes,
created_at, updated_at, deleted_at
```

### `provider_features` (per-feature mapping: which provider used for what)
```
id, feature_key (e.g. 'ai.concierge', 'ai.translation', 'payment.qris', 'sms.otp'),
primary_provider_id, fallback_provider_id,
extra_config JSON,
created_at, updated_at
```

### `provider_credentials_audit`
```
id, provider_id, action ENUM('create','update','rotate','delete','test'),
actor_id, ip, user_agent, meta JSON, created_at
```

---

## 11. AI Usage

### `ai_usage_logs`
```
id, provider_id, feature_key, model,
input_tokens, output_tokens, total_tokens,
cost_usd, cost_idr, exchange_rate,
request_id (idempotency), latency_ms, status, error_message,
input_preview (first 200 chars, for debug; PII-masked), output_preview,
created_at
```

### `ai_conversations` (concierge)
```
id, guest_id, channel ENUM('whatsapp','web','sms','email'),
status ENUM('active','closed'), opened_at, closed_at,
total_messages, total_cost_idr,
created_at, updated_at
```

### `ai_messages`
```
id, conversation_id, role ENUM('user','assistant','system','tool'),
content TEXT, tokens_in, tokens_out, model_used, tool_calls JSON,
created_at
```

---

## 12. Accounting

### `gl_accounts`
```
id, code (e.g. 1110, 4101), name, account_type ENUM('asset','liability','equity','revenue','cogs','expense'),
parent_id (hierarchy), is_postable, normal_balance ENUM('debit','credit'),
description, is_active, created_at, updated_at
```

### `journal_entries`
```
id, entry_number (unique), entry_date, posting_date,
description, source_module, source_id, source_reference,
total_debit, total_credit (must equal),
status ENUM('draft','posted','reversed'),
posted_at, posted_by_id, reversal_of_id,
created_at, updated_at
```

### `journal_entry_lines`
```
id, journal_entry_id, line_number,
gl_account_id, description, debit, credit,
cost_center, dimension JSON
```

### `ar_invoices` (city ledger)
```
id, invoice_number, customer_id (FK to companies/agents/guests),
issue_date, due_date,
subtotal, tax, total, paid_amount, balance,
status ENUM('draft','issued','partially_paid','paid','overdue','cancelled','written_off'),
folio_id (kalau dari folio transfer), created_at, updated_at
```

### `ar_invoice_lines`
```
id, invoice_id, description, quantity, unit_price, amount, gl_account_id, tax_rate
```

### `ar_payments`
```
id, invoice_id, amount, paid_at, method, reference, recorded_by_id, created_at
```

### `ap_bills`
```
id, vendor_id, bill_number, bill_date, due_date,
subtotal, tax, total, paid_amount, balance, status,
notes, created_at, updated_at
```

### `companies` (corporate accounts)
```
id, name, npwp, address, contact_person, email, phone,
credit_limit, payment_terms_days, is_active, created_at, updated_at
```

### `agents`
```
id, name, type ENUM('travel_agent','ota','online_travel_agent','corporate'),
commission_rate, contact, payment_terms_days, is_active,
created_at, updated_at
```

### `vendors`
```
id, name, npwp, contact, payment_terms_days, account_id (default expense),
is_active, created_at, updated_at
```

---

## 13. Indonesia Compliance

### `pb1_rates` (per kabupaten/kota)
```
id, region_code, region_name, rate (default 10.00), effective_from, effective_until, source_law, created_at
```

### `efaktur_records`
```
id, invoice_id (or reservation_id), faktur_number (NSFP), faktur_type,
seller_npwp, seller_name, buyer_npwp, buyer_name, buyer_address,
dpp, ppn, total,
xml_path, qr_code_path,
status ENUM('draft','generated','submitted','approved','rejected','cancelled'),
submitted_at, approved_at, rejection_reason, coretax_response JSON,
created_at, updated_at
```

### `lapor_wna_records`
```
id, guest_id, reservation_id,
passport_number, passport_country, full_name, dob,
checkin_at, checkout_at, room_number,
visa_type, visa_expiry,
report_payload JSON, report_status ENUM('pending','submitted','accepted','rejected'),
submitted_at, accepted_at,
created_at, updated_at
```

### `sipgar_reports` (Phase 2)
```
id, property_id, report_period (YYYY-MM),
total_arrivals, total_nights, by_nationality JSON, by_purpose JSON,
submitted_at, payload JSON, response JSON,
created_at
```

---

## 14. pSEO

### `pseo_pages`
```
id, slug (unique), template_key,
title, meta_description, h1,
city, region, landmark, category, year, price_range, comparison_a_id, comparison_b_id,
content_html LONGTEXT, jsonld JSON,
view_count, click_through_count,
last_generated_at, last_indexed_at,
is_active, created_at, updated_at
```

### `pseo_destinations` (master data city, landmark)
```
id, type ENUM('city','region','landmark','neighborhood'),
name, slug, parent_id, latitude, longitude,
description, photos JSON, faq JSON,
created_at, updated_at
```

---

## 15. License

### `license_lock_meta` (cache info dari .license.lock — file is canonical)
```
id, installation_uuid, activation_key_hash, domain,
product_name, product_version,
issued_at, expires_at (support window),
last_heartbeat_at, heartbeat_status,
created_at, updated_at
```

(File `.license.lock` di disk adalah otoritatif; tabel ini cuma denorm cache.)

---

## 16. Audit & System

### `audit_logs` (append-only, no soft delete)
```
id, actor_id, actor_type, actor_email,
action, target_type, target_id,
ip, user_agent, http_method, request_path,
old_values JSON, new_values JSON, meta JSON,
created_at,
INDEX(actor_id), INDEX(target_type, target_id), INDEX(action), INDEX(created_at)

DB-level: REVOKE UPDATE, DELETE on audit_logs from app_role
Trigger: BEFORE UPDATE → SIGNAL SQLSTATE
```

### `webhook_logs` (incoming webhooks: OTA, payment)
```
id, source ENUM('booking_com','agoda','traveloka','midtrans','xendit','doku','...'),
event_type, signature_valid, payload JSON, received_at,
processed_at, status ENUM('received','processed','duplicate','failed'), error,
INDEX(source, event_type, received_at)
```

### `outgoing_webhooks` (Phase 2 — push to integrator endpoints)
```
id, endpoint_url, event, payload JSON, signature,
attempts, status ENUM('pending','delivered','failed','dropped'),
last_attempted_at, delivered_at, response_status, response_body, created_at
```

### `notifications` (Laravel native)
```
id, type, notifiable_type, notifiable_id, data JSON, read_at, created_at, updated_at
```

### `failed_jobs` (Laravel native)

### `jobs` (Laravel native)

### `cache`, `cache_locks`, `sessions` (Laravel native if using DB driver — kita pakai Redis, jadi optional)

### `activity_log` (spatie)

---

## 17. Settings

### `settings` (key-value store, encrypted untuk sensitive)
```
id, key (unique), value TEXT, type ENUM('string','int','bool','json','encrypted'),
group_label, description, is_public,
updated_by_id, created_at, updated_at
```

---

## 18. Loyalty (Phase 2)

### `loyalty_members`
```
id, guest_id, member_number, tier ENUM('bronze','silver','gold','platinum'),
points_balance, lifetime_points, lifetime_spend,
joined_at, expires_at, is_active, created_at, updated_at
```

### `loyalty_transactions`
```
id, member_id, type ENUM('earn','redeem','expire','adjust'),
points, source_type, source_id, description,
created_at
```

---

## 19. Banquet (Phase 2)

### `banquet_events`
```
id, property_id, event_name, event_date, room_id (function hall),
contact_company_id, organizer_name, organizer_phone,
expected_pax, type ENUM('wedding','meeting','exhibition','seminar','party','other'),
package_id, total_revenue, deposit_paid, balance,
status, beo_path, created_at, updated_at
```

### `beo_setup` (banquet event order details)
```
id, banquet_event_id, section ENUM('layout','av','fnb','timeline','staffing','decoration'),
item_description, quantity, notes, supplier_id
```

---

## 20. Spa (Phase 2)

### `spa_services`, `spa_therapists`, `spa_appointments`, `spa_rooms`

---

## 21. HR & Payroll (Phase 2)

### `employees`, `attendance`, `payroll_periods`, `payroll_items`, `bpjs_contributions`, `pph21_calculations`, `payslips`

---

## 22. Inventory (Phase 2)

### `inventory_items`, `warehouses`, `stock_movements`, `purchase_requests`, `purchase_orders`, `goods_receipts`, `recipes`, `recipe_items`

---

## 23. Sample ERD (key relations)

```
properties (1) ── (M) room_types ── (M) rooms
                            └── (M) rate_plans ── (M) rate_calendar

properties (1) ── (M) reservations
                       ├── (M) reservation_rooms ── (1) room
                       ├── (1) folios ── (M) charges
                       │                   └── (M) payments
                       ├── (1) guest (primary)
                       └── (M) reservation_status_logs

guests (1) ── (M) guest_stays
       ── (1) loyalty_members

channels (1) ── (M) channel_credentials (per property)
              ├── (M) channel_room_mappings
              ├── (M) channel_sync_logs
              └── (M) channel_bookings

providers (1) ── (M) provider_features
provides (1) ── (M) ai_usage_logs

journal_entries (1) ── (M) journal_entry_lines ── (1) gl_accounts
```

---

## 24. Indexes & Performance

| Table | Critical indexes |
|---|---|
| `reservations` | (property_id, status, checkin_date), (ota_reservation_id), (booking_source) |
| `rate_calendar` | (rate_plan_id, date) UNIQUE |
| `rooms` | (property_id, status), (room_type_id) |
| `guests` | (email), (phone), (passport_number), (ktp_number) |
| `folios` | (reservation_id, status), (folio_number) UNIQUE |
| `charges` | (folio_id, charge_date), (charge_type) |
| `payments` | (folio_id), (gateway_transaction_id), (status) |
| `housekeeping_tasks` | (room_id, status), (assigned_to_id, status) |
| `pos_orders` | (outlet_id, status), (folio_id) |
| `audit_logs` | (actor_id), (target_type, target_id), (action), (created_at) |
| `ai_usage_logs` | (provider_id, created_at), (feature_key, created_at) |
| `pseo_pages` | (slug) UNIQUE, (template_key, city) |
| `channel_sync_logs` | (channel_id, created_at), (status) |

---

## 25. Migration Order (Phase 1 MVP)

1. users, roles, permissions (auth)
2. properties, settings
3. room_types, rooms, rate_plans, rate_calendar
4. guests
5. reservations, reservation_rooms, reservation_status_logs
6. folios, charges
7. payments, payment_refunds
8. providers, provider_features (BYOK config)
9. channels, channel_credentials, channel_room_mappings, channel_sync_logs, channel_bookings
10. housekeeping_tasks, lost_and_found, maintenance_orders
11. pos_outlets, pos_items, pos_orders, pos_order_items
12. gl_accounts, journal_entries, journal_entry_lines
13. ar_invoices, ar_invoice_lines, ar_payments, ap_bills, companies, agents, vendors
14. pb1_rates, efaktur_records, lapor_wna_records
15. ai_usage_logs, ai_conversations, ai_messages
16. pseo_pages, pseo_destinations
17. audit_logs (with DB-level append-only triggers)
18. webhook_logs
19. notifications, jobs, failed_jobs
20. license_lock_meta

Phase 2: loyalty, banquet, spa, HR, inventory, lock_keys.

---

## 26. Conventions

- All money: `NUMERIC(15,2)` — Rp dengan 2 decimal (untuk USD/lain juga aman)
- All percentages: `NUMERIC(5,2)` — e.g. `10.00`
- All dates without time: `DATE`
- All datetimes: `DATETIME` (UTC stored, Asia/Jakarta display)
- All enums via VARCHAR + Laravel cast (untuk fleksibilitas migration)
- All JSON fields: validated schema in app layer
- Soft delete (`deleted_at`) hanya pada master data, bukan pada transaksi
- `created_by_id`, `updated_by_id` di tabel transaksi penting (reservasi, folio, payment, journal)

---

## 27. Future Considerations

- Partitioning `audit_logs` by `created_at` month (>10M rows)
- Partitioning `ai_usage_logs` (volume tinggi)
- Read replica untuk reporting (Phase 3)
- Materialized views untuk dashboard hot queries
- Time-series store (TimescaleDB) untuk metrics historis (Phase 3)
