# 19 — Pricing

> Tier pricing standalone & SaaS. Add-ons. Decision tree owner.

---

## 1. Standalone (license one-time + annual support)

| Tier | Kamar | License | Annual support | Cocok untuk |
|---|---|---|---|---|
| **Lite** | ≤ 30 | Rp 25 juta | Rp 7 juta | Boutique hotel, villa, guesthouse, hostel |
| **Pro** | 31 – 100 | Rp 50 juta | Rp 12 juta | Hotel bintang 2-3, serviced apartment kecil |
| **Enterprise** | > 100 atau multi-property | Rp 100 juta+ | Rp 20 juta+ | Hotel bintang 4-5 indie, chain kecil 2-5 properti |

**Yang termasuk:**
- Source code lifetime (git access ke release branch)
- Install di 1 domain produksi + 1 domain staging
- Update versi minor & patch selama support window
- Email support (response 1x24 jam business day untuk Lite/Pro, 4 jam untuk Enterprise)
- Onboarding session 2 jam (Pro+) atau 4 jam (Enterprise)

**Yang TIDAK termasuk:**
- Hosting / VPS (owner siapkan sendiri)
- Domain & SSL (owner siapkan sendiri, app support Let's Encrypt auto)
- BYOK provider biaya (Midtrans, OTA, AI, dll. dibayar owner langsung ke vendor)
- Custom development di luar scope produk
- Migration data dari sistem lama (lihat add-on di bawah)

**Renewal support:**
- Tahun ke-2 dst.: harga annual support sesuai tier
- Tidak renew? Software tetap jalan, cuma gak dapat update + support

---

## 2. SaaS (per kamar per bulan, all-included hosting)

| Tier | Per kamar/bulan | Yang termasuk |
|---|---|---|
| **Starter** | Rp 35.000 | PMS + CM (3 OTA) + Booking Engine + POS basic + Housekeeping + accounting basic + pSEO |
| **Pro** | Rp 60.000 | + Revenue Management AI, Multi-property, Loyalty, Banquet, unlimited OTA, full accounting |
| **Enterprise** | Custom (Rp 80.000+/kamar) | + Spa, HR/Payroll, GDS connectivity, dedicated SLA 99.9%, custom modules |

**Minimum billing:**
- Starter: 10 kamar = Rp 350.000/bulan
- Pro: 20 kamar = Rp 1.200.000/bulan

**Volume discount:**
- 100+ kamar: 10% off
- 500+ kamar: 20% off
- Annual prepay: 15% off

**Yang termasuk SaaS:**
- Hosting (region: Jakarta, AWS/GCP atau Biznet)
- Auto-update ke versi terbaru
- Daily backup, point-in-time recovery 7 hari
- 99.5% uptime SLA (Pro), 99.9% (Enterprise)
- Email support unlimited
- WhatsApp support business hours

**Yang TIDAK termasuk SaaS:**
- BYOK provider biaya (sama seperti standalone)
- Custom domain SSL (kalau pakai domain sendiri — included di Pro+)
- Data export pada saat keluar = free (bukan ditahan)

---

## 3. Add-ons (untuk standalone & SaaS)

| Add-on | Harga | Catatan |
|---|---|---|
| **White-Label Branding** | Rp 15 juta one-time atau Rp 1 juta/bulan | Logo, warna, domain custom, email sender, hilangkan attribution |
| **SaaS Reseller License** | Rp 200 juta one-time + 10% revenue share | Hak resell SaaS ke multiple client (white-label penuh) |
| **AI Credits Prepaid** | Markup 20% dari harga vendor | Kalau owner gak mau urus BYOK sendiri |
| **Custom OTA Integration** | Rp 5-15 juta per OTA | Untuk OTA niche di luar 7 OTA built-in |
| **Migration dari sistem lama** | Rp 10-30 juta | VHP, eZee, Realta, Power Pro, spreadsheet → import data |
| **Custom Module Development** | Rp 1.5-3 juta/hari | Minimum 5 hari |
| **On-site Training** | Rp 5 juta/hari + transport+akomodasi | Onsite di hotel (Jakarta/Bali populer) |
| **Pre-go-live Audit** | Rp 8 juta | Cek pajak setup, accounting setup, channel manager mapping |
| **24/7 Premium Support** | Rp 5 juta/bulan | WhatsApp + phone, 1 jam response |

---

## 4. BYOK provider — owner-paid (estimate biaya operasional)

Owner bayar **langsung ke vendor**, bukan ke kita. Estimasi untuk hotel 30 kamar:

| Provider | Estimasi/bulan |
|---|---|
| Midtrans / Xendit (payment) | 1.5 - 2.5% per transaksi |
| Booking.com / Agoda commission | 15-25% per booking (di luar scope kita, tapi context) |
| Channel Manager API fees | 0 (kita pakai direct integration) |
| AI provider (BYOK, optional) | Rp 50rb - 500rb tergantung volume + provider |
| WhatsApp Business API | Rp 200rb - 1 juta (tergantung volume) |
| SMS gateway | Rp 100rb - 500rb |
| Mail (Resend/Mailgun BYOK) | Rp 50rb - 200rb |
| **Total estimate operational** | **Rp 500rb - 3 juta/bulan + commission** |

---

## 5. Pricing decision tree (untuk owner)

```
Mau self-host (data di server sendiri)?
├── Ya → Standalone
│   ├── ≤30 kamar → Lite (Rp 25jt + 7jt/year)
│   ├── 30-100 → Pro (Rp 50jt + 12jt/year)
│   └── >100 → Enterprise (Rp 100jt+)
│
└── Tidak (mau cloud, gak mau urus IT)
    └── SaaS
        ├── Basic ops → Starter (Rp 35rb/kamar/bln)
        ├── Multi-property atau revenue mgmt → Pro (Rp 60rb)
        └── Resort heavy ops (banquet, spa, HR) → Enterprise
```

---

## 6. Comparison vs kompetitor (30 kamar, year 1)

| Vendor | Year 1 cost (estimate Rp) |
|---|---|
| **Kita Standalone Lite** | Rp 25jt license + Rp 7jt support = **Rp 32 juta** |
| **Kita SaaS Starter** | 30 × Rp 35rb × 12 = **Rp 12.6 juta/year** |
| Cloudbeds | $300/bln × 12 × 16rb = **Rp 57.6 juta/year** |
| Mews | €350/bln × 12 × 17rb = **Rp 71.4 juta/year** |
| eZee Absolute | $90/bln × 12 × 16rb = **Rp 17.3 juta/year** |
| VHP license | Rp 80 juta + Rp 12jt/year = **Rp 92 juta year 1** |
| Realta | Rp 60 juta + Rp 10jt/year = **Rp 70 juta year 1** |

Kita = **paling murah di kategori standalone**, dan **kompetitif di SaaS** (lebih murah dari eZee bila include feature parity).

---

## 7. Discount policy

| Promo | Diskon |
|---|---|
| Early adopter (3 bulan pertama launch) | 30% off license |
| Migration dari kompetitor (proof invoice) | 20% off license |
| Multi-property single owner | 15% off license per property kedua dst. |
| Annual prepay SaaS | 15% off |
| Reseller volume (5+ license) | 25% off license |
| NGO / pemerintah / pendidikan | 50% off (case-by-case) |

Diskon maksimum cumulative 40%.

---

## 8. Refund policy

- Standalone: 14 hari money-back guarantee, full refund kalau license belum di-pair ke domain. Setelah pair, no refund (source code sudah di-deliver).
- SaaS: pro-rata refund untuk sisa periode prepay. Bulanan billing → cancel kapan saja, pay-as-you-go.
- Add-ons custom dev: deposit non-refundable, sisa progress-based.

---

## 9. Pricing history

- **2026-04-28 (v1):** Initial pricing set as above.

Future adjustment akan di-grandfather untuk existing customer (selalu lock harga lama untuk renewal mereka).
