# 21 — QA Checklist

> Comprehensive QA + smoke test list pre-launch dan post-deploy. Pakai sebagai acceptance gate sebelum aktivasi license customer atau push update version.

Format: per modul ada **smoke test** (basic flow) + **regression** (edge case yang sering kepukul).

---

## 1. Test environments

| Env | Purpose | Data | License |
|---|---|---|---|
| Local dev | Per developer | Faker seed | Test license |
| CI | PR validation | Faker seed in pipeline | Skipped |
| Staging | Pre-release smoke | Daily reset, near-prod data | Test license, separate vendor server |
| Production | Live | Real | Live license |
| Sandbox tenant (SaaS) | Customer evaluation | Reset on demand | Trial license |

---

## 2. Acceptance criteria umum

- ✅ Tidak ada error 500 / unhandled exception di sentry
- ✅ Lighthouse mobile ≥ 80 booking engine
- ✅ DB migration runs clean dari empty + dari latest prod backup
- ✅ Tests: PHPUnit / Pest pass 100%, coverage ≥ 60% (priority modules ≥ 80%)
- ✅ No critical / high vuln di `composer audit` & `npm audit`
- ✅ License pairing wizard end-to-end OK
- ✅ All BYOK provider config form roundtrip (save → reload → still encrypted+masked)
- ✅ Security review: no secret in repo, no hardcoded provider, no debug=true in prod
- ✅ Backup script sukses, restore drill verified

---

## 3. Front Office (FO)

### Smoke
- [ ] Create reservation: future date, 1 room, 1 adult → confirmed status
- [ ] Walk-in: present arrival → check-in flow → folio created
- [ ] Add charge to folio: room service, breakfast, late checkout
- [ ] Settle folio: cash → printed receipt → folio status closed
- [ ] Settle folio: card via PG redirect → callback handled → posted to GL
- [ ] Check-out flow: late checkout, generate invoice PDF, send via email
- [ ] Night audit: close day, room charge auto-posted, occupancy rolled

### Regression
- [ ] Reservation di tanggal masa lalu → blocked
- [ ] Overbooking: book past availability → conflict error
- [ ] Group block: 5 rooms, master folio split per room → individual settle
- [ ] Move room mid-stay: charge transfer, no-overlap
- [ ] Cancel within penalty window: penalty auto-calc + refund initiated
- [ ] No-show after 23:59 audit: status updated, charge per policy
- [ ] Foreign currency reservation (P3) — display IDR + foreign
- [ ] OTA reservation arrival: VCC payment auth → captured at check-in

---

## 4. Booking Engine (Public)

### Smoke
- [ ] Search: 2 adults, 3 nights → results shown
- [ ] Select room → checkout → fill guest info → pay (PG) → success page
- [ ] Email + WA confirmation received
- [ ] Manage booking: open via token link → can view/cancel
- [ ] Multi-room booking: 2 rooms 1 transaksi → both reservation created

### Regression
- [ ] Search invalid: same-day check-in/out → blocked
- [ ] Promo code: apply → discount reflected in price breakdown
- [ ] Promo code expired → rejected with message
- [ ] Inventory race: 2 user book last room → 1 success 1 fail gracefully
- [ ] Payment fail / abandon → reservation auto-cancelled after timeout (15 min)
- [ ] Payment success but webhook lost → reconciliation cron re-confirm
- [ ] Locale switch ID/EN → persists across pages
- [ ] Currency display IDR formatted correctly (no decimals, thousand separator)
- [ ] Booking engine Core Web Vitals: LCP < 2.5s, CLS < 0.1, INP < 200ms

---

## 5. Channel Manager

### Smoke
- [ ] Connect Booking.com test endpoint → success
- [ ] Push availability update → ARI sync log entry "ok"
- [ ] Push rate update → reflected at OTA test channel
- [ ] Receive booking from OTA via webhook → reservation created locally with `source:ota:booking`

### Regression
- [ ] OTA cancellation → local reservation auto-cancel + refund posted
- [ ] OTA booking after local block → conflict resolution UI prompt
- [ ] Rate parity check warns if direct < OTA × threshold
- [ ] ARI queue backed up: drain test → all updates eventually pushed
- [ ] OTA API down: retry exponential backoff, alert after 3 failures
- [ ] Mapping change mid-day → next sync uses new mapping
- [ ] CTA / CTD restriction respected

---

## 6. Housekeeping

### Smoke
- [ ] Login as HK staff on mobile → see assigned rooms
- [ ] Update status: dirty → in-progress → clean → inspected
- [ ] Photo upload for damage report
- [ ] Lost & found: log item with photo
- [ ] Today's checkout list shows dirty rooms after departure

### Regression
- [ ] Offline mode: queue actions, sync when back online
- [ ] Concurrent staff: 2 HK update same room simultaneously → last-write-wins with conflict warning
- [ ] OOO room: blocked from FO assignment
- [ ] Long photo upload: chunked, no timeout
- [ ] Linen inventory deduct on cleaning → low-stock alert at threshold

---

## 7. POS

### Smoke
- [ ] Open table → add menu item → modifier (extra cheese)
- [ ] Send to kitchen → printer receipt (or simulated)
- [ ] Bill split: 4 ways → calc accurate
- [ ] Settle: cash → drawer balance updated
- [ ] Charge to room: search guest → posted to folio

### Regression
- [ ] Void item with reason + manager pin
- [ ] Discount > threshold → approval required
- [ ] Inventory deduct: recipe-based COGS posted
- [ ] Tax calculation: PPN 11% on F&B applied
- [ ] Outlet close-of-day: shift report printed
- [ ] Refund processed: jurnal reverse posted

---

## 8. Accounting

### Smoke
- [ ] COA list loads, edit account, add new account
- [ ] Manual journal: balanced (DR=CR), post → status updated
- [ ] AR: city ledger invoice generated → payment posted
- [ ] AP: bill received → schedule payment → bayar via cash
- [ ] Daily revenue report: opens, accurate vs night audit

### Regression
- [ ] Unbalanced journal entry → blocked save
- [ ] Period locked: post journal → blocked
- [ ] Period unlock: requires permission, audit logged
- [ ] PB1 auto-credit: 10% of room+service revenue
- [ ] PPN auto-calc on F&B: 11%
- [ ] OTA commission expense auto-posted on OTA booking
- [ ] Trial balance reconciles (sum DR = sum CR)
- [ ] Export to Coretax: file format valid, push API success on test
- [ ] Year-end close: revenue/expense closed to retained earnings

---

## 9. Indonesia Compliance

### Smoke
- [ ] PB1 region resolver: select Jakarta property → 10% rate
- [ ] PB1 region resolver: select Bali (Badung) property → applicable rate
- [ ] e-Faktur Coretax: generate test faktur → API push success
- [ ] Lapor WNA imigrasi: foreign guest check-in → flag set
- [ ] WNA report monthly: generated correct format → upload to imigrasi portal manual OK

### Regression
- [ ] PB1 effective_until past → fallback to next active rate
- [ ] NSFP serial: increments correctly, no skip
- [ ] PPh 23 withholding on supplier service invoice
- [ ] KTP OCR validation: 16-digit, NIK valid format
- [ ] Paspor expired warning at check-in
- [ ] WNA without paspor: blocked check-in (unless override permission)

---

## 10. License & Pairing

### Smoke
- [ ] Wizard: paste valid key → pair OK → token stored
- [ ] Heartbeat manual `php artisan license:heartbeat` → success
- [ ] License diagnostic CLI → all green

### Regression
- [ ] Invalid license key → wizard error with friendly message
- [ ] Already-paired key on different fingerprint → conflict
- [ ] Expired license → wizard rejects
- [ ] Heartbeat 8 days fail → yellow banner appears
- [ ] Heartbeat 30 days fail → degraded mode, edit blocked, read OK
- [ ] Re-pair / migrate flow: old fingerprint invalidated, new active
- [ ] Revoke from vendor side → next heartbeat → hard lock client
- [ ] Public key tampered (test by replacing file) → app refuses boot
- [ ] Token JWT signature invalid → app refuses boot
- [ ] Feature flag check: try access feature not in plan → 403 + upgrade prompt
- [ ] Max rooms exceeded → soft warn + read-only

---

## 11. Security

### Smoke
- [ ] 2FA enrollment flow → QR + recovery codes generated
- [ ] Login with 2FA → success
- [ ] Login with wrong 2FA → blocked, attempt counter ↑
- [ ] Password reset email → token valid 1 hour
- [ ] Audit log: edit folio entry → log row created with before/after

### Regression
- [ ] Lockout: 5 fail attempts → account locked 15 min
- [ ] Concurrent session: enforce single (if owner toggled)
- [ ] CSRF: form without token → 419
- [ ] XSS: try inject `<script>` in guest name → output escaped
- [ ] SQL injection: try in search query → ORM safe
- [ ] Rate limit: API 1000 req/min → 429 returned
- [ ] Encrypted secret: API config form → save → DB stores encrypted (verify by querying raw)
- [ ] Mask in API response: secret never returned plain
- [ ] Session hijack: change User-Agent → session invalidated
- [ ] Privileged action without permission → 403
- [ ] Privileged action requires approval → goes to approval queue

---

## 12. Integrations (BYOK)

### Smoke
- [ ] Add AI provider (OpenAI-compatible): URL + key → test connection → success
- [ ] Add payment gateway: Midtrans test creds → test charge → success
- [ ] Add SMS provider: Twilio test creds → test send → success
- [ ] Add WA provider: Meta Cloud → test send → success
- [ ] Add mail provider: Resend test API key → test send → success
- [ ] Storage S3: bucket connection → upload test file
- [ ] Captcha Turnstile: site key → render in form → submit verified

### Regression
- [ ] Provider key invalid → connection test fails with clear message
- [ ] Pricing per model user-input → tracking correct
- [ ] Switch active AI provider mid-task → next task uses new provider
- [ ] Webhook secret rotation → next event signed with new
- [ ] Failed webhook delivery: retry queue, after 5 attempts mark dead
- [ ] Disable provider → fitur fallback or graceful degradation

---

## 13. pSEO

### Smoke
- [ ] `/best-villa` route renders, has H1, meta, schema, ≥300 words
- [ ] `/compare/superior-vs-deluxe` renders comparison table
- [ ] `/sitemap.xml` returns valid XML, includes all pSEO routes
- [ ] `/robots.txt` allows pSEO routes

### Regression
- [ ] Page with 0 listing data → unpublished
- [ ] Schema JSON-LD validates via Google rich result test
- [ ] hreflang ID/EN cross-links correct
- [ ] LLM content regen cron picks stale pages
- [ ] OG image render: title overlays correctly
- [ ] Performance: pSEO page LCP < 2s

---

## 14. Multi-property & SaaS

### Smoke (SaaS only)
- [ ] Tenant signup → tenant DB created → ready < 30s
- [ ] Custom domain: CNAME setup → SSL provisioned → tenant accessible
- [ ] Property switcher dropdown switches context correctly
- [ ] Tenant suspend → read-only mode
- [ ] Tenant resume → full access

### Regression
- [ ] Cross-tenant isolation: user A cannot access tenant B's data
- [ ] Job queued for tenant A → executes in correct tenant context
- [ ] Cache key prefix per tenant — no leakage
- [ ] Storage path per tenant — no leakage

---

## 15. Performance & load

### Targets
- TTFB < 300ms (lokal LAN)
- Reservation calendar 200 rooms × 90 days renders < 1s
- Booking engine LCP < 2.5s
- API p95 < 500ms
- Concurrent: 50 staff + 200 guest concurrent → no error

### Tools
- k6 / Artillery untuk load test
- New Relic / Inspector untuk APM (opsional)
- Lighthouse CI di pipeline

---

## 16. Accessibility

- [ ] Booking engine keyboard-navigable end-to-end
- [ ] Form labels semantic
- [ ] Alt text on hotel photos
- [ ] Contrast ratio AA on primary CTA
- [ ] Screen reader test (NVDA / VoiceOver) on booking flow

---

## 17. Localization

- [ ] All static strings translated ID/EN
- [ ] Date format: dd-MM-yyyy default
- [ ] Currency IDR formatting consistent
- [ ] Property-specific copy editable per locale
- [ ] Email templates per locale rendered correctly

---

## 18. Browser & device matrix

- Chrome (latest) ✅
- Safari (latest, macOS + iOS) ✅
- Firefox (latest) ✅
- Edge (latest) ✅
- Samsung Internet (mobile) ✅
- iPhone SE (small viewport) ✅
- iPad (tablet HK) ✅

---

## 19. Smoke after deploy (post-release)

Runtime in 10 menit setelah `php artisan up`:
- [ ] Health endpoint `/health` returns 200
- [ ] Login admin → dashboard loads
- [ ] Create test reservation (sandbox) → success
- [ ] Settle test folio → posted to GL
- [ ] Run night audit (testing flag) → no error
- [ ] Channel sync test → ok
- [ ] Webhook delivery test → ok
- [ ] No 500 in last 10 min Sentry

Rollback procedure tested:
- [ ] `git checkout previous-tag && composer install ... && migrate:rollback ...`
- [ ] Documented runbook tersedia

---

## 20. Sign-off

Sebelum activate license customer / public launch:

| Owner | Sign-off |
|---|---|
| Tech lead | code review + tests pass |
| QA lead | full checklist run, bug minor only |
| Compliance | tax & PDP verified |
| Owner / sales | accepted UAT walkthrough |
| Ops | backup + monitoring active |

Document sign-off di Linear ticket / Notion page release.

---

## 21. Open questions

1. Automated test coverage target — push to 80% or stay 60% pragmatic?
2. Visual regression (Percy / BackstopJS) — Phase 2 nice-to-have?
3. Synthetic monitoring (Checkly) post-launch — Phase 2.
4. Real device cloud testing (BrowserStack) for matrix — opsional.
