# HotelHub — Hotel Management System (PMS)

> All-in-one Hotel Operating System: PMS + Channel Manager + Booking Engine + POS + Accounting + AI + Marketing

---

## Fitur & Fungsi

### Front Office
| Fitur | Fungsi |
|---|---|
| Reservasi | Booking management, check-in/out, room assignment |
| Tape Chart | Visual room timeline drag-drop |
| Night Audit | EOD posting, rollover otomatis |
| E-Registration | Digital registration card + e-signature via public link |
| Digital Key | PIN-based room access, issue/revoke via LockService |
| AI Room Assignment | Auto-assign optimal room based on guest preferences, floor, view, group proximity |
| Cashier Shift | Open/close shift, cash tracking |
| Out of Order | OOO room management |
| Self Check-in Kiosk | Public kiosk with lookup + check-in |

### Housekeeping
| Fitur | Fungsi |
|---|---|
| HK Board | Task dashboard with real-time WebSocket updates |
| Auto-Assign | Smart task assignment based on workload, floor proximity, priority |
| Inspection Checklist | Room inspection with scoring |
| Minibar | Product CRUD, per-room stock tracking, auto-charge on checkout |
| Lost & Found | Item tracking, claim management, disposal reminder |
| Linen & Laundry | Stock tracking with PAR levels, uniform assignment, transaction log |
| Key Card Inventory | Card stock, issue/return/lost/damaged tracking |

### OTA & Channel Manager
| Fitur | Fungsi |
|---|---|
| Multi-OTA | 9 adapters: Booking.com, Agoda, Traveloka, Tiket.com, Expedia, Airbnb, Trip.com, Pegipegi, Mister Aladin |
| ARI Sync | 2-way availability/rates/inventory push + booking ingest |
| Rate Mapping | Room type & rate plan mapping per OTA |
| Channel Dashboard | Unified health monitoring: sync status, conflicts, alerts |
| Per-OTA Detail | Credentials, sync log, mapping, bookings, parity alerts per channel |
| Virtual Card (VCC) | Booking.com/Agoda virtual card tracking |
| GDS Booking | Sabre, Amadeus, Travelport booking management |
| Rate Parity Monitor | Auto-detect OTA selling cheaper than direct |
| Rate Override | Per-channel, per-date rate override + stop sell |
| Dynamic Pricing | Rule-based auto pricing per channel |
| Agency Travel | Travel agent CRUD with commission config |
| Allotment | Room block for agents/corporate with pickup tracking, auto-release scheduler |

### Revenue Management
| Fitur | Fungsi |
|---|---|
| AI Revenue Agent | AI-analyzed pricing recommendations, 7-day batch |
| Weather-Based Pricing | Auto-adjust rates based on weather forecast |
| Open Pricing Calendar | Bulk rate override, stop sell, CTA/CTD per date |
| Upsell Engine | Auto-suggest upgrades based on guest tier, offer/accept/decline workflow |
| Overbooking Optimization | Safe overbooking calculator based on no-show rate |
| Dynamic Packaging | Bundle room + spa + dinner + transfer |
| Rate Shopper | Competitor rate benchmarking |
| Competitor Intelligence | Position analysis, trend, alerts dashboard |
| RMS Dashboard | Revenue management system with forecast + yield |
| Forecast Accuracy | MAPE, bias, absolute error tracking |

### Direct Booking Engine
| Fitur | Fungsi |
|---|---|
| Public Website | Room search, availability, booking, payment |
| Abandoned Cart Recovery | Track + email recovery link |
| Multi-Currency | Real-time FX conversion (10 currencies via open.er-api.com) |

### POS & F&B
| Fitur | Fungsi |
|---|---|
| Multi-Outlet POS | Restaurant, bar, spa, laundry |
| KDS | Kitchen Display System |
| QR Menu | Guest scan QR → order from phone |
| Table Reservation | Visual floor plan with color-coded status |
| Menu Engineering | 4-quadrant matrix (Star/Plowhorse/Puzzle/Dog), recipe costing |
| Laundry POS | Guest laundry order tracking |

### Accounting & Finance
| Fitur | Fungsi |
|---|---|
| Chart of Accounts | Full COA management |
| Journal Entries | Double-entry bookkeeping |
| AR/AP | Accounts receivable & payable |
| Trial Balance | Automated TB report |
| Profit & Loss | Monthly P&L statement |
| Balance Sheet | Financial position report |
| Coretax / e-Faktur | DJP tax compliance, NSFP generation |
| PB1 Tax | Per-kabupaten/kota hotel tax |
| Bank Reconciliation | Auto-match + manual match |
| Budget | Plan vs actual tracking |
| Owner Portal | Investor dashboard: revenue, expense, NOI, distribution |
| Deposit Management | Booking guarantee, incidental, refund + forfeiture |
| Chargeback Handling | Dispute tracking, evidence management, deadline alerts |
| FX Rates | Live currency rates + converter |

### Guest Relationship
| Fitur | Fungsi |
|---|---|
| Guest 360 Profile | Lifetime value, behavioral metrics, preferences |
| LTV Dashboard | RFM segmentation (Champions, Loyal, Potential, New) |
| Loyalty Program | Tiers, points, vouchers |
| Guest Preference Learning | Auto-detect preferences from stay history |
| Cross-Property Profile | Unified guest view across property chain |
| Self-Service Portal | Mobile-friendly: booking, room service, requests, chat |
| WhatsApp Concierge | Send messages via chatgo.whitelabel.co.id |
| Survey | Guest feedback collection |

### Marketing
| Fitur | Fungsi |
|---|---|
| WhatsApp Blast | Segment-based blast via chatgo API |
| Drip Campaign | Automated WA/email sequence: pre-arrival → post-stay → review → promo |
| Referral Program | Referral codes, tracking, rewards |
| Review Aggregator | Google Places API review pulling + dashboard |
| Social Auto-Poster | Instagram auto-post room availability + promo |
| Google Hotel Ads | Price feed generation + performance tracking |
| Metasearch | Trivago, Kayak, TripAdvisor feed generator |
| Blog | Full blog system with categories, RSS, SEO tags |

### IoT & Smart Room
| Fitur | Fungsi |
|---|---|
| Smart Devices | Thermostat, lighting, blinds, TV, sensors |
| Device Control | Per-room device dashboard with command log |
| Energy Monitoring | Per-room kWh tracking + cost estimate |
| Energy Saving | Auto energy-saving mode on checkout |
| Auto Welcome | Guest welcome mode on check-in |
| Carbon Footprint | CO2 calculation, offset tracking |

### Asset & Maintenance
| Fitur | Fungsi |
|---|---|
| Asset Registry | Asset catalog with category, location |
| PM Scheduler | Preventive maintenance auto-schedule (AC, heater, elevator) |
| Work Orders | Maintenance request tracking |
| Vendor Management | Supplier CRUD, contracts, purchase history |

### Event & Facility
| Fitur | Fungsi |
|---|---|
| Wedding & Event Planning | Event booking, catering, services, BEO |
| Banquet | Venue management, setup requirements |
| Kids Club | Activities CRUD, children booking |
| Spa Management | Appointment booking, service catalog |
| Parking | Slot grid, check-in/out, valet, folio charge |
| Fleet Management | Vehicles, drivers, airport shuttle schedule, trip tracking |

### HR & Staff
| Fitur | Fungsi |
|---|---|
| Employee Directory | Staff profiles, departments |
| Attendance | Check-in/out tracking |
| Payroll | Salary calculation |
| Leave Management | Request, approve, balance |
| Performance Reviews | Review cycles with scoring |
| Shift Schedule | Calendar-based scheduling |
| Gamification | Leaderboard, points, badges per department |

### Security & Compliance
| Fitur | Fungsi |
|---|---|
| Incident Reports | Guest injury, theft, damage tracking with severity + followups |
| License & Permit | Expiry tracking with auto-reminder |
| Data Privacy | Consent management, data export, right-to-delete anonymization |
| Audit Log | Full activity trail |
| Lapor WNA | Immigration foreign guest reporting |

### Sustainability
| Fitur | Fungsi |
|---|---|
| Energy Dashboard | Monthly consumption, room breakdown, saving suggestions |
| Carbon Footprint | CO2 kg calculation + offset tracking |
| Food Waste | Category tracking, reduction targets, cost impact |

### Multi-Property
| Fitur | Fungsi |
|---|---|
| Property Switcher | Multi-property management in single install |
| HQ Dashboard | Cross-property occupancy + revenue overview |
| Cross-Property Guest | Unified guest search across all properties |

### Multi-Language
| Fitur | Fungsi |
|---|---|
| i18n (EN/ID) | Full Bahasa Indonesia + English via JSON translation files |
| Language Switcher | Cookie-based + user preference persistence |
| SetLocale Middleware | Auto-detect from cookie → user → browser → default |

### Custom Reporting
| Fitur | Fungsi |
|---|---|
| Report Builder | Drag-drop widget: charts, tables, stats cards |
| Guest Journey Funnel | 6-stage funnel: search → book → checkin → checkout → review → repeat |
| Channel Mix Report | Revenue breakdown by OTA source |
| SIPGAR | BPS hotel statistics report export |
| Flash Report | Daily operational summary |

### Programmatic SEO
| Fitur | Fungsi |
|---|---|
| PSEO Pages | 1M+ auto-generated SEO pages |
| Sitemap | Dynamic split sitemap (50K URLs per file) |
| IndexNow | Auto-submit to Bing, Yandex, Seznam, Naver |
| JSON-LD Schema | Auto Article, ItemList, FAQ, Product schema |
| Robots.txt | Dynamic with crawl rules |

### SaaS & License
| Fitur | Fungsi |
|---|---|
| Multi-Tenant | Tenant signup, provisioning, billing |
| Subscription | Plan management, MRR tracking |
| License v3 | RSA-signed + AES-256-GCM encrypted pairing |
| Telemetry | Heartbeat, error monitoring, health check |

### Integrations
| Kategori | Provider |
|---|---|
| Payment Gateway | Dynamic provider system (BYOK) |
| AI / LLM | OpenAI-compatible + Anthropic + Gemini (BYOK) |
| WhatsApp | Meta Cloud API + On-Prem + chatgo.whitelabel.co.id |
| Door Lock | Salto, Onity, Vingcard, Dormakaba, Miwa |
| Weather | OpenWeather API |
| Currency | open.er-api.com |
| Google | Places API (reviews), Hotel Ads |

---

## Tech Stack

- **Backend:** Laravel 11, PHP 8.3
- **Frontend:** Blade, Tailwind CSS, Alpine.js, Chart.js
- **Database:** MySQL 8
- **WhatsApp:** chatgo (Go binary via whatsmeow)
- **License:** RSA-4096 + AES-256-GCM

---

## Quick Start

```bash
git clone https://github.com/linducip2208/hotel
cd hotel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

---

## Demo Login

| Role | Email | Password |
|---|---|---|
| Admin | admin@hotel.test | password |
| Manager | manager@hotel.test | password |
| FO | fo@hotel.test | password |
| HK | hk@hotel.test | password |
| Kasir | kasir@hotel.test | password |

---

## Documentation

Full docs at `/docs` — includes tutorial 25+ langkah alur bisnis, demo accounts, struktur menu, screenshot fitur.

---

## License

Proprietary. Whitelabel source code available at [whitelabel.co.id](https://whitelabel.co.id).
