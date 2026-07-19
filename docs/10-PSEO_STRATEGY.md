# 10 — pSEO Strategy

> Programmatic SEO baked into every property's public website. Goal: organic acquisition tanpa ads, jutaan halaman terindex Google secara otomatis dari satu set template + data DB.

Mandatory per global preference. Reference baseline pattern: `D:\project laravel\whitelabel\whitelabel\app\Http\Controllers\ProgrammaticSeoController.php`. Adapt untuk konteks hotel.

---

## 1. Mengapa pSEO untuk hotel

- **Kompetitor utama (PMS lain) hampir tidak punya pSEO bawaan** — Cloudbeds/Mews fokus operations, bukan marketing surface. Realta/VHP zero web presence beyond brochure.
- **Hotel tunggal yang dipasarkan via OTA** kebal komisi 18-25%. Direct booking via SEO langsung = margin penuh.
- **Long-tail search** ("hotel murah dekat malioboro untuk keluarga", "villa private pool ubud dengan dapur") kurang terlayani — branded hotel page tidak bersaing di tail keyword ini.
- **Property owner langsung lihat dampak**: rank di Google = booking masuk via direct = ROI nyata.

---

## 2. Ruang lingkup (siapa yang punya pSEO?)

### a) Standalone deploy — single-property

Hotel A pasang HMS di domain `hotelmandala.com`. Public surface jadi:

- Homepage hotel
- Halaman tipe kamar
- pSEO category pages: lokasi spesifik, jenis akomodasi, theme, dll
- Booking engine

### b) SaaS multi-tenant (Phase 2+)

Tiap tenant punya subdomain `hotelmandala.hotelhub.id` (dan/atau custom domain). Setiap tenant generate pSEO sendiri dari datanya sendiri.

### c) Marketplace mode (opsional, P3)

Hotel Hub central directory `hotelhub.id` agregator — pSEO cross-tenant: "best hotels in Bali", listing semua hotel terdaftar. Ini opsional, hanya kalau owner aplikasi mau jadi OTA tipis.

---

## 3. URL Pattern (mandatory + extras)

### Wajib (sesuai global rule)

| Pattern | Contoh | Sumber data |
|---|---|---|
| `/best-{category}` | `/best-villa` | top-rated villa di property ini |
| `/best-{category}-{year}` | `/best-villa-2026` | snapshot rating + booking count tahun berjalan |
| `/alternatives-to-{slug}` | `/alternatives-to-deluxe-room` | room types serupa di property |
| `/compare/{a}-vs-{b}` | `/compare/superior-vs-deluxe` | head-to-head room comparison |

### Domain-specific hotel (extra)

| Pattern | Contoh | Catatan |
|---|---|---|
| `/hotels-in-{city}` | `/hotels-in-bali` | Marketplace mode atau properti chain |
| `/hotels-in-{city}-{neighborhood}` | `/hotels-in-jakarta-kemang` | |
| `/{city}-hotels-near-{landmark}` | `/yogyakarta-hotels-near-malioboro` | |
| `/best-hotels-{city}-{year}` | `/best-hotels-bali-2026` | top 10 listing |
| `/hotels-under-{price}-{city}` | `/hotels-under-500k-jakarta` | budget filter |
| `/villas-with-{feature}-{location}` | `/villas-with-private-pool-ubud` | feature filter |
| `/{occasion}-stay-{city}` | `/honeymoon-stay-bali`, `/family-stay-yogyakarta`, `/business-stay-jakarta` | use-case |
| `/things-to-do-near-{property-slug}` | `/things-to-do-near-hotel-mandala` | Local guide content |
| `/{room-type}-in-{city}` | `/suite-in-jakarta` | |
| `/best-time-to-visit-{city}` | `/best-time-to-visit-bali` | Long-tail informational |
| `/{landmark}-hotels` | `/borobudur-hotels` | Hotel near landmark |
| `/pet-friendly-hotels-{city}` | | Niche filter |
| `/wedding-venue-{city}` | | Phase 2 (banquet module) |

### Internal cross-link

Setiap halaman pSEO link ke 5-10 halaman pSEO terkait via "related" sections, plus link ke listing & individual property pages. Goal: silo topical.

---

## 4. Schema JSON-LD (mandatory)

Per halaman, embed schema sesuai konteks:

| Page type | Schema |
|---|---|
| Homepage hotel | `Hotel` + `LocalBusiness` |
| Room type page | `Product` + `Offer` + `AggregateRating` |
| Listing pSEO (top 10, etc) | `ItemList` + per item `Hotel` |
| Compare page | `ItemList` (2 items, comparison) |
| Things-to-do | `TouristAttraction` + nested `Place` |
| FAQ section | `FAQPage` |
| Review page | `Review` + `Rating` |
| Booking confirmation page | `Reservation` (private, but valid) |
| Best time / informational | `Article` |
| Breadcrumb (semua) | `BreadcrumbList` |

Helper service `App\Services\Seo\SchemaBuilder` per type. Contoh hasil di Hotel:

```json
{
  "@context": "https://schema.org",
  "@type": "Hotel",
  "name": "Hotel Mandala Yogyakarta",
  "image": ["..."],
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Jl. Malioboro 100",
    "addressLocality": "Yogyakarta",
    "addressRegion": "DIY",
    "postalCode": "55271",
    "addressCountry": "ID"
  },
  "geo": { "@type": "GeoCoordinates", "latitude": -7.79, "longitude": 110.36 },
  "priceRange": "Rp 350.000 - Rp 1.200.000",
  "starRating": { "@type": "Rating", "ratingValue": "3" },
  "amenityFeature": [...],
  "checkinTime": "14:00", "checkoutTime": "12:00",
  "aggregateRating": { "@type": "AggregateRating", "ratingValue": 4.5, "reviewCount": 287 }
}
```

---

## 5. Meta tags (mandatory)

Per page, helper `<x-seo-head ... />` blade component:

```html
<title>{{ $title }} — {{ $propertyName }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:type" content="website">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:url" content="{{ $canonical }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $twitterImage }}">

<link rel="alternate" hreflang="id" href="{{ $idUrl }}">
<link rel="alternate" hreflang="en" href="{{ $enUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $defaultUrl }}">
```

OG image generated dinamis via `/og/{type}/{slug}.png` route — Imagick/GD compose template + property photo + heading text.

---

## 6. Konten 300+ kata yang bermakna (anti thin-content)

Per halaman pSEO punya minimum 300 kata copy unik. Sumber konten:

| Bagian | Generator |
|---|---|
| Intro paragraph (60-80 kata) | Template + variabel: lokasi, jumlah listing, tahun, harga rata-rata, use-case |
| Listing items (10-30 items) | Card per hotel/room: nama, foto, harga, rating, review excerpt, key features |
| FAQ (5-7 Q&A, 50 kata each) | Generator FAQ dari template per pattern, e.g. `What's the average price of {category} in {city}?` |
| Review excerpts | Pull dari `reviews` table, filtered by relevance |
| Comparison tables | Side-by-side feature matrix |
| Outro paragraph (60-80 kata) | Booking CTA + secondary intent linking |
| Local guide section (informational pages) | LLM-generated saat content build, di-cache |

### LLM-generated content rules

- Dipakai untuk informational pages (`/best-time-to-visit-bali`, `/things-to-do-near-X`).
- Generator ada di service `App\Services\Seo\ContentGenerator`.
- BYOK LLM (per global rule). Owner punya AI provider sendiri, set di admin.
- Content disimpan di `seo_content` table dengan `regenerate_after` (e.g. setiap 90 hari) untuk freshness.
- Selalu tetap punya editor UI untuk owner override / approve sebelum publish.
- Watermark `data-source="ai-generated"` di template untuk transparency (ga ditampilkan ke user, internal flag saja).

---

## 7. Sitemap.xml dynamic

Route `GET /sitemap.xml` — index file referencing sub-sitemaps:

```
sitemap.xml
├── sitemap-pages.xml       (homepage, about, contact, policy)
├── sitemap-rooms.xml       (semua room types)
├── sitemap-pseo-best.xml   (best-X patterns)
├── sitemap-pseo-compare.xml
├── sitemap-pseo-location.xml (hotels-in-X)
├── sitemap-pseo-feature.xml
├── sitemap-blog.xml        (kalau ada blog)
└── sitemap-images.xml      (semua property photos)
```

- Cache 24 jam (rebuild via cron `seo:rebuild-sitemap` di malam hari).
- Each URL include `<lastmod>`, `<priority>`, `<changefreq>`.
- Maksimal 50.000 URL per file (split kalau lebih).

---

## 8. robots.txt

```
User-agent: *
Allow: /

# Allow pSEO
Allow: /best-*
Allow: /alternatives-to-*
Allow: /compare/*
Allow: /hotels-in-*
Allow: /things-to-do-near-*

# Disallow internal
Disallow: /admin
Disallow: /panel
Disallow: /portal
Disallow: /api
Disallow: /booking/*/payment-callback
Disallow: /*?utm_

Sitemap: https://{domain}/sitemap.xml
```

Generated dinamis via route `GET /robots.txt` — owner bisa override per-property (admin UI section "SEO → robots").

---

## 9. Tabel database

```
seo_pages
├── id
├── property_id
├── route_pattern (e.g. 'best-villa-{year}')
├── slug (e.g. 'best-villa-2026')
├── url (cached canonical)
├── title
├── description
├── h1
├── intro_html
├── outro_html
├── content_html (rendered listing block)
├── faq_json
├── meta_json (og, twitter, schema_jsonld)
├── locale (id | en)
├── status (draft | published)
├── last_generated_at
├── regenerate_after
├── views_total, views_30d
├── conversions_30d
└── timestamps

seo_redirects
├── id, property_id
├── source_path
├── target_path
├── http_status (301 | 302)
└── timestamps

seo_keywords (tracking)
├── id, property_id
├── keyword
├── target_url
├── current_rank, last_checked
└── timestamps
```

---

## 10. Generators & Job Queue

| Job | Trigger | Output |
|---|---|---|
| `GenerateBestListPSEOJob` | Daily cron + manual rebuild | `/best-{cat}` & `/best-{cat}-{year}` per category & current year |
| `GenerateCompareRoomsPSEOJob` | Saat room types berubah | All `/compare/{a}-vs-{b}` permutations (limit ke kombinasi yang masuk akal — same property) |
| `GenerateLocationPSEOJob` | Saat property data updated | `/hotels-in-{city}`, `/hotels-in-{city}-{neighborhood}`, dll |
| `GenerateLandmarkPSEOJob` | Manual + saat landmark master di-update | `/hotels-near-{landmark}` |
| `GenerateInformationalPSEOJob` | Monthly | LLM-driven informational pages |
| `RebuildSitemapJob` | Daily 03:00 + post-publish | Sitemap files |
| `RegenerateStaleContentJob` | Daily | Pages dengan `regenerate_after < now` |

Generator service base class: `App\Services\Seo\Generators\BasePseoGenerator`.

---

## 11. Performance & cache

- Output rendered HTML cached di Redis 1 jam (or longer) untuk pSEO routes — invalidate saat data dasar berubah.
- Use `Cache::tags(["pseo:property:{$id}"])` untuk targeted purge.
- Static asset (image) via CDN (Cloudflare / Bunny / R2).
- Lighthouse target: ≥90 mobile, ≥95 desktop.
- Core Web Vitals — LCP <2.5s, CLS <0.1, INP <200ms.
- Image: WebP/AVIF auto, lazy-load, responsive `srcset`.

---

## 12. Internationalization

- Default lokal **id** (Bahasa Indonesia).
- Auto duplicate `/en/...` untuk seluruh route pSEO. Konten translate via owner's BYOK LLM (auto-translate + manual edit allowed).
- `hreflang` linking antar locale.
- Pricing tetap Rupiah (kecuali user toggle USD untuk audience asing — Phase 2).

---

## 13. Analytics & monitoring

- GSC integration: owner connect via OAuth → search performance per pSEO page tampil di admin dashboard.
- Custom analytics table `seo_pageviews` untuk per-page conversion tracking (booking dari halaman ini).
- Per page: rank, impressions, clicks, CTR, bookings attributed, revenue.
- Bulanan auto-email "Top 20 SEO pages" ke owner.

---

## 14. Anti-spam guardrails

- Pages dengan 0 listing data dont publish (skip empty `/hotels-in-pekanbaru` kalau gak ada hotel di Pekanbaru). Mark as "draft" + di-exclude dari sitemap.
- Duplicate detection via shingles — kalau dua pSEO page > 80% similar, canonical-link salah satunya.
- Owner toggle "exclude from sitemap" per page.

---

## 15. Submit ke Google Search Console (README mention)

Wajib dokumented di `README.md` (sudah ada):

> Setelah deploy, tambahkan property di Google Search Console (`https://search.google.com/search-console`) dan submit sitemap `https://your-domain.com/sitemap.xml`. pSEO pages akan terindex bertahap dalam 1-4 minggu.

---

## 16. Marketplace mode (P3)

Kalau owner aplikasi pengen jadi tipis-OTA / agregator:

- Central pSEO digenerate cross-tenant
- Tenant A's hotel listed di `hotelhub.id/hotels-in-bali`
- Click → ke landing page di subdomain tenant
- Konversi tetap di tenant, owner aplikasi opsional ambil komisi (config per kontrak)
- Phase 3 only — bukan core MVP
