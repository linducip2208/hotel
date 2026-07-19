<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class DocsController extends Controller
{
    protected string $docsPath;

    public function __construct()
    {
        $this->docsPath = base_path('docs');
    }

    public function index()
    {
        $title = 'Dokumentasi Lengkap';
        $metaDescription = 'Dokumentasi lengkap sistem manajemen hotel — Front Office, POS, Accounting, Channel Manager, Revenue Management, dan 17+ modul operasional hotel berbasis Laravel 11. Panduan langkah demi langkah, demo accounts, dan daftar fitur lengkap.';

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'TechArticle',
            'headline' => $title . ' — ' . config('app.name'),
            'description' => $metaDescription,
            'author' => ['@type' => 'Organization', 'name' => config('app.name')],
            'publisher' => ['@type' => 'Organization', 'name' => config('app.name')],
            'about' => [
                '@type' => 'SoftwareApplication',
                'name' => config('app.name'),
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'Web',
            ],
            'proficiencyLevel' => 'Beginner',
            'datePublished' => '2026-01-01',
            'dateModified' => '2026-06-05',
        ];

        return view('pseo.docs-index', [
            'title' => $title,
            'meta_description' => $metaDescription,
            'schema' => $schema,
            'demoAccounts' => $this->demoAccounts(),
            'menuStructure' => $this->menuStructure(),
            'tutorial' => $this->tutorial(),
            'features' => $this->features(),
            'featureModules' => $this->parseFeaturesMd(),
            'competition' => $this->competitiveComparison(),
        ]);
    }

    public function show(string $slug)
    {
        $files = $this->listFiles();
        $file = collect($files)->firstWhere('slug', $slug);

        if (! $file || ! is_file($file['path'])) {
            abort(404, 'Doc not found');
        }

        $raw = file_get_contents($file['path']);
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);
        $html = (string) $converter->convert($raw);

        $html = preg_replace_callback('/<(h[1-6])>(.*?)<\/\1>/s', function ($m) {
            $id = \Illuminate\Support\Str::slug(strip_tags($m[2]));
            return "<{$m[1]} id=\"{$id}\">{$m[2]} <a href=\"#{$id}\" class=\"anchor\">#</a></{$m[1]}>";
        }, $html);

        $headings = [];
        if (preg_match_all('/<(h[23])\s+id="([^"]+)">([^<]+?)\s*<a/u', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $headings[] = ['level' => (int) substr($m[1], 1), 'id' => $m[2], 'text' => trim($m[3])];
            }
        }

        return view('docs.show', [
            'files' => $files,
            'currentSlug' => $slug,
            'currentFile' => $file,
            'html' => $html,
            'headings' => $headings,
        ]);
    }

    public function raw(string $slug)
    {
        $files = $this->listFiles();
        $file = collect($files)->firstWhere('slug', $slug);
        abort_unless($file && is_file($file['path']), 404);
        return response(file_get_contents($file['path']), 200, ['Content-Type' => 'text/markdown; charset=utf-8']);
    }

    protected function listFiles(): array
    {
        $items = [];
        foreach (glob($this->docsPath.'/*.md') as $path) {
            $base = pathinfo($path, PATHINFO_FILENAME);
            $title = $this->extractTitle($path) ?: $base;
            $items[] = [
                'slug' => $base,
                'path' => $path,
                'title' => $title,
                'order' => (int) (preg_match('/^(\d+)/', $base, $m) ? $m[1] : 99),
            ];
        }
        usort($items, fn ($a, $b) => $a['order'] <=> $b['order'] ?: strcmp($a['slug'], $b['slug']));
        return $items;
    }

    protected function extractTitle(string $path): ?string
    {
        $h = fopen($path, 'r');
        if (! $h) return null;
        for ($i = 0; $i < 5; $i++) {
            $line = fgets($h);
            if ($line === false) break;
            if (preg_match('/^#\s+(.+)$/', trim($line), $m)) {
                fclose($h);
                return $m[1];
            }
        }
        fclose($h);
        return null;
    }

    // ──────────────────────────────────────────────
    //  Data providers for the new docs index page
    // ──────────────────────────────────────────────

    public function demoAccounts(): array
    {
        return [
            [
                'role' => 'Administrator',
                'icon' => '🛡️',
                'email' => 'admin@demohotel.id',
                'password' => 'password',
                'scope' => 'Akses penuh semua modul & konfigurasi sistem',
                'color' => 'indigo',
            ],
            [
                'role' => 'Front Office',
                'icon' => '🏨',
                'email' => 'fo@demohotel.id',
                'password' => 'password',
                'scope' => 'Reservasi, check-in/out, folio, night audit',
                'color' => 'blue',
            ],
            [
                'role' => 'Housekeeping',
                'icon' => '🧹',
                'email' => 'hk@demohotel.id',
                'password' => 'password',
                'scope' => 'Room status board, task assignment, linen',
                'color' => 'emerald',
            ],
            [
                'role' => 'Accounting',
                'icon' => '💳',
                'email' => 'acc@demohotel.id',
                'password' => 'password',
                'scope' => 'COA, jurnal, AR/AP, laporan keuangan',
                'color' => 'amber',
            ],
            [
                'role' => 'Manager',
                'icon' => '👔',
                'email' => 'manager@demohotel.id',
                'password' => 'password',
                'scope' => 'Semua modul operasional kecuali sistem',
                'color' => 'violet',
            ],
            [
                'role' => 'Kasir POS',
                'icon' => '🍽️',
                'email' => 'kasir@demohotel.id',
                'password' => 'password',
                'scope' => 'F&B order, pembayaran restoran',
                'color' => 'orange',
            ],
            [
                'role' => 'Channel Manager',
                'icon' => '🌐',
                'email' => 'channel@demohotel.id',
                'password' => 'password',
                'scope' => 'OTA mapping, rate sync, channel parity',
                'color' => 'cyan',
            ],
            [
                'role' => 'Owner',
                'icon' => '👑',
                'email' => 'owner@demohotel.id',
                'password' => 'password',
                'scope' => 'Dashboard eksekutif, laporan, audit trail',
                'color' => 'rose',
            ],
        ];
    }

    public function menuStructure(): array
    {
        return [
            [
                'group' => 'Dashboard',
                'icon' => '📊',
                'sections' => [
                    [
                        'label' => 'Dashboard',
                        'items' => [
                            'Statistik Overview',
                            'Occupancy Widget',
                            'Revenue Widget',
                            'Housekeeping Status',
                            'Daily Flash Report',
                        ],
                    ],
                ],
            ],
            [
                'group' => 'Operasional',
                'icon' => '🏨',
                'sections' => [
                    [
                        'label' => 'Front Office',
                        'items' => [
                            'Reservasi Calendar',
                            'Walk-in Booking',
                            'Check-in Tamu',
                            'Check-out Tamu',
                            'Registration Card',
                            'Folio Management',
                            'Deposit Handling',
                            'Night Audit',
                            'Room Move / Merge',
                        ],
                    ],
                    [
                        'label' => 'Housekeeping',
                        'items' => [
                            'Room Status Board',
                            'Task Assignment',
                            'Linen Management',
                            'Inspection Checklist',
                            'Minibar Tracking',
                            'Lost & Found',
                            'Maintenance Request',
                            'HK Report',
                        ],
                    ],
                    [
                        'label' => 'F&B / POS',
                        'items' => [
                            'Table Management',
                            'Order Taking (KDS)',
                            'Cashier / Payment',
                        ],
                    ],
                ],
            ],
            [
                'group' => 'Revenue',
                'icon' => '💰',
                'sections' => [
                    [
                        'label' => 'Pricing',
                        'items' => [
                            'Rate Plan Management',
                            'Seasonal Pricing',
                            'Promo & Discount',
                        ],
                    ],
                    [
                        'label' => 'Revenue Management',
                        'items' => [
                            'Open Pricing Calendar',
                            'Dynamic Pricing Rules',
                            'Rate Shopper',
                            'Demand Forecast',
                        ],
                    ],
                    [
                        'label' => 'OTA / Channel Manager',
                        'items' => [
                            'OTA Mapping',
                            'Rate Sync (ARI)',
                            'Reservation Fetch',
                            'Channel Parity Monitor',
                            'Booking.com XML',
                            'Agoda YCS JSON',
                            'Traveloka v2 HMAC',
                        ],
                    ],
                ],
            ],
            [
                'group' => 'AI Tools',
                'icon' => '🤖',
                'sections' => [
                    [
                        'label' => 'AI Tools',
                        'items' => [
                            'AI Translate',
                            'AI Concierge',
                            'AI Review Reply',
                            'AI Demand Forecasting',
                            'AI Provider Setup (BYOK)',
                        ],
                    ],
                ],
            ],
            [
                'group' => 'Guests & CRM',
                'icon' => '👥',
                'sections' => [
                    [
                        'label' => 'Guests',
                        'items' => [
                            'Guest Database',
                            'Guest 360° Profile',
                            'Guest Preferences',
                            'Stay History',
                        ],
                    ],
                    [
                        'label' => 'Loyalty & Marketing',
                        'items' => [
                            'Loyalty Program',
                            'Member Tiers',
                            'Points Redemption',
                            'Email Campaign',
                        ],
                    ],
                    [
                        'label' => 'Communications',
                        'items' => [
                            'Email Templates',
                            'SMS / WA Broadcast',
                            'Scheduled Messages',
                        ],
                    ],
                    [
                        'label' => 'Concierge & Survey',
                        'items' => [
                            'Guest Requests',
                            'Survey Forms',
                            'Feedback Analysis',
                        ],
                    ],
                ],
            ],
            [
                'group' => 'Finance',
                'icon' => '💳',
                'sections' => [
                    [
                        'label' => 'Accounting',
                        'items' => [
                            'Chart of Accounts',
                            'Journal Entry',
                            'AR Invoice',
                            'AP Bill',
                            'Bank Reconciliation',
                            'Trial Balance',
                            'Period Close',
                            'Tax Settlement',
                        ],
                    ],
                    [
                        'label' => 'Finance',
                        'items' => [
                            'Daily Revenue Report',
                            'P&L Statement',
                            'Balance Sheet',
                            'Cash Flow',
                            'Department P&L',
                        ],
                    ],
                ],
            ],
            [
                'group' => 'Inventory & People',
                'icon' => '📦',
                'sections' => [
                    [
                        'label' => 'Inventory',
                        'items' => [
                            'Stock Items',
                            'Purchase Orders',
                            'Stock Receipts',
                            'Stock Adjustments',
                        ],
                    ],
                    [
                        'label' => 'Asset Management',
                        'items' => [
                            'Asset Register',
                            'Depreciation Schedule',
                            'Maintenance Calendar',
                        ],
                    ],
                    [
                        'label' => 'HR & Payroll',
                        'items' => [
                            'Employee Master',
                            'Attendance',
                            'Leave Management',
                            'Payroll Processing',
                            'BPJS / Tax',
                            'Performance Review',
                            'Training Records',
                            'Schedule / Roster',
                        ],
                    ],
                    [
                        'label' => 'Spa & Banquet',
                        'items' => [
                            'Event Booking',
                            'BEO Generation',
                            'Spa Appointments',
                            'Membership / Treatment',
                            'Therapist Schedule',
                            'Room Setup Plan',
                            'Menu Packages',
                            'Banquet Order',
                        ],
                    ],
                ],
            ],
            [
                'group' => 'Insights & Settings',
                'icon' => '📈',
                'sections' => [
                    [
                        'label' => 'Reports',
                        'items' => [
                            'Occupancy Report',
                            'Revenue Report',
                            'Channel Production',
                            'Cashier Shift Report',
                            'SIPGAR Compliance',
                            'Daily Flash Report',
                        ],
                    ],
                    [
                        'label' => 'Sustainability',
                        'items' => [
                            'Energy Tracking',
                            'Waste Management',
                            'Green Certifications',
                        ],
                    ],
                    [
                        'label' => 'Knowledge Base',
                        'items' => [
                            'SOP Documents',
                            'Training Materials',
                            'FAQ Admin',
                        ],
                    ],
                    [
                        'label' => 'Audit Log',
                        'items' => [
                            'Activity Log',
                            'Login History',
                            'Data Changes',
                        ],
                    ],
                    [
                        'label' => 'Settings',
                        'items' => [
                            'Property Configuration',
                            'Tax Rates',
                            'User Management',
                            'Role & Permission',
                            'License Management',
                            'Integration Providers',
                            'Email Templates',
                            'System Parameters',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function tutorial(): array
    {
        return [
            [
                'phase' => 'Fase 1: Setup Awal',
                'icon' => '⚙️',
                'steps' => [
                    'Buka halaman Setup Wizard di <code>/setup/wizard</code> — isi nama properti, alamat, bintang, dan kota.',
                    'Buat akun admin pertama melalui wizard — email, password, dan nama lengkap.',
                    'Login ke admin panel di <code>/admin</code> menggunakan kredensial yang baru dibuat.',
                    'Buka <strong>Settings → Property Configuration</strong> — lengkapi detail properti: NPWP, telepon, email, kebijakan hotel.',
                    'Buka <strong>Settings → User Management</strong> — buat user untuk FO, HK, Accounting, Kasir, dan role lainnya.',
                    'Buka <strong>Settings → Tax Rates</strong> — tambahkan PPN 11%, PB1 10%, dan service charge jika berlaku.',
                    'Buka <strong>Settings → Integration Providers</strong> — tambahkan payment gateway (Midtrans/Xendit), SMS gateway, dan AI provider.',
                ],
            ],
            [
                'phase' => 'Fase 2: Master Data',
                'icon' => '📋',
                'steps' => [
                    'Buka <strong>Room Type</strong> — buat tipe kamar: Deluxe, Superior, Suite, Family Room, dll. Tentukan max guest, bed type, dan fasilitas.',
                    'Buka <strong>Room / Kamar</strong> — generate nomor kamar per lantai. Mapping setiap kamar ke tipe yang sudah dibuat.',
                    'Buka <strong>Rate Plan</strong> — buat rate plan: Best Available Rate, Non-Refundable, Breakfast Included, Corporate, Government.',
                    'Buka <strong>Seasonal Pricing</strong> — atur high season, low season, dan peak surcharge (Nataru, Lebaran, MotoGP).',
                    'Buka <strong>Guest Database</strong> — import atau input manual data tamu existing (opsional, bisa diisi saat check-in).',
                ],
            ],
            [
                'phase' => 'Fase 3: Integrasi',
                'icon' => '🔌',
                'steps' => [
                    'Buka <strong>Integration → Payment Gateway</strong> — konfigurasi Midtrans/Xendit: masukkan server key, client key, dan merchant ID.',
                    'Buka <strong>Channel Manager → OTA Mapping</strong> — mapping room type hotel ke room ID di Booking.com, Agoda, Traveloka.',
                    'Buka <strong>Channel Manager → Rate Sync</strong> — enable ARI push, atur interval sync (rekomendasi: setiap 5 menit).',
                    'Buka <strong>AI Provider Setup</strong> — masukkan API key provider AI (OpenAI, DeepSeek, atau Ollama self-host). Pilih model untuk masing-masing task.',
                    'Buka <strong>Integration → SMS/WA Gateway</strong> — konfigurasi Twilio/Vonage/Whacenter untuk notifikasi tamu otomatis.',
                ],
            ],
            [
                'phase' => 'Fase 4: Transaksi Harian',
                'icon' => '🔄',
                'steps' => [
                    'Buka <strong>Front Office → Reservasi Calendar</strong> — klik tanggal dan slot untuk buat reservasi baru. Isi nama tamu, tipe kamar, rate plan, durasi.',
                    'Saat tamu tiba, buka <strong>Front Office → Check-in</strong> — verifikasi identitas, upload KTP/WNA, assign kamar, cetak registration card.',
                    'Selama menginap, buka <strong>Folio Management</strong> — tambahkan charge: restoran, laundry, spa, telepon, minibar. Semua tercatat real-time.',
                    'Buka <strong>Front Office → Payment</strong> — terima pembayaran: deposit, partial payment, atau full settlement. Pilih metode bayar.',
                    'Saat tamu check-out, buka <strong>Front Office → Check-out</strong> — review folio, settle outstanding balance, cetak invoice final, update room status ke dirty.',
                ],
            ],
            [
                'phase' => 'Fase 5: Operasional',
                'icon' => '🧹',
                'steps' => [
                    'Buka <strong>Housekeeping → Room Status Board</strong> — lihat status real-time semua kamar: dirty, clean, inspected, out-of-order.',
                    'Assign task ke room attendant via <strong>Task Assignment</strong> — tentukan prioritas berdasarkan check-in hari ini.',
                    'Update <strong>Inspection Checklist</strong> setelah kamar selesai dibersihkan — supervisor verifikasi dan ubah status ke clean.',
                    'Untuk tamu restoran, buka <strong>POS → Order Taking</strong> — pilih meja, input pesanan, kirim ke kitchen display.',
                    'Kelola <strong>Linen Management</strong> — tracking stok linen bersih/kotor, kirim ke laundry, terima kembali.',
                    'Buat <strong>Banquet Event Order (BEO)</strong> untuk event/wedding — setup ruangan, menu, dan jadwal.',
                    'Buka <strong>Spa → Appointments</strong> — jadwalkan treatment tamu, assign therapist.',
                ],
            ],
            [
                'phase' => 'Fase 6: Finance',
                'icon' => '📒',
                'steps' => [
                    'Buka <strong>Accounting → Chart of Accounts</strong> — review dan sesuaikan COA standar perhotelan (default sudah tersedia).',
                    'Buka <strong>Journal Entry</strong> — input jurnal manual untuk transaksi non-operasional (penyusutan, accrual).',
                    'Generate <strong>AR Invoice</strong> untuk corporate client / travel agent yang menggunakan credit facility.',
                    'Input <strong>AP Bill</strong> dari supplier: bahan makanan, amenities, linen, maintenance.',
                    'Buka <strong>Bank Reconciliation</strong> — cocokkan transaksi bank statement dengan GL, tandai cleared items.',
                    'Akhir bulan: buka <strong>Period Close</strong> — lock periode, auto-generate closing entries, preview trial balance sebelum final.',
                ],
            ],
            [
                'phase' => 'Fase 7: Laporan',
                'icon' => '📊',
                'steps' => [
                    'Buka <strong>Reports → Occupancy Report</strong> — analisis okupansi per tipe kamar, per bulan, per channel.',
                    'Buka <strong>Reports → Revenue Report</strong> — breakdown revenue: room, F&B, spa, banquet, other.',
                    'Buka <strong>Reports → Channel Production</strong> — lihat kontribusi OTA vs direct booking, commission analysis.',
                    'Buka <strong>Reports → Cashier Shift Report</strong> — rekap transaksi per shift, per kasir, cash float reconciliation.',
                    'Buka <strong>Reports → SIPGAR Compliance</strong> — generate laporan SIPGAR (Sistem Informasi Pajak) untuk Dirjen Pajak.',
                    'Buka <strong>Reports → Daily Flash</strong> — ringkasan CEO: occupancy %, RevPAR, ADR, revenue today, outstanding AR.',
                ],
            ],
            [
                'phase' => 'Fase 8: Advanced',
                'icon' => '🚀',
                'steps' => [
                    'Buka <strong>Revenue → Dynamic Pricing Rules</strong> — atur auto-adjust rate berdasarkan okupansi (contoh: occupancy >80% → naikkan 15%).',
                    'Buka <strong>Channel Manager → Channel Parity</strong> — monitor selisih harga antar OTA, auto-alert jika selisih >5%.',
                    'Buka <strong>Guests → Guest 360° Profile</strong> — lihat riwayat lengkap tamu: total stay, total spend, preferensi, komplain.',
                    'Jadwalkan <strong>Night Audit</strong> — auto-run setiap jam 02:00, posting room charge harian + tutup hari bisnis.',
                    'Buka <strong>Loyalty Program</strong> — buat tier membership (Silver/Gold/Platinum), atur earn rate dan redemption rules.',
                    'Buka <strong>Marketing → PSEO</strong> — review halaman SEO terprogram: best hotels, alternatives, compare, city guides.',
                ],
            ],
        ];
    }

    public function parseFeaturesMd(): array
    {
        $path = base_path('docs/01-FEATURES.md');
        if (!file_exists($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $modules = [];
        $currentModule = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^## Modul \d+ — (.+)$/', $line, $m)) {
                if ($currentModule) {
                    $modules[] = $currentModule;
                }
                $currentModule = [
                    'name' => $m[1],
                    'features' => [],
                ];
                continue;
            }
            if (preg_match('/^\| (\d+\.\d+) \| (.+?) \| (.+?) \| (.+?) \|$/', $line, $m)) {
                $phaseRaw = trim($m[3]);
                $phase = match (true) {
                    str_contains($phaseRaw, '🟢') => 'mvp',
                    str_contains($phaseRaw, '🟡') => 'phase2',
                    str_contains($phaseRaw, '🔵') => 'phase3',
                    default => 'mvp',
                };
                $currentModule['features'][] = [
                    'number' => $m[1],
                    'name' => trim($m[2]),
                    'phase' => $phase,
                    'phaseLabel' => $phaseRaw,
                    'notes' => trim($m[4]),
                ];
            }
        }

        if ($currentModule) {
            $modules[] = $currentModule;
        }

        return $modules;
    }

    public function competitiveComparison(): array
    {
        return [
            'competitors' => [
                ['name' => 'HotelHub HMS', 'flag' => 'Kita', 'highlight' => true],
                ['name' => 'QloApps', 'flag' => 'Open Source'],
                ['name' => 'HotelDruid', 'flag' => 'Open Source'],
                ['name' => 'FewohBee', 'flag' => 'Berbayar'],
                ['name' => 'ERPNext Hospitality', 'flag' => 'Open Source'],
            ],
            'categories' => [
                [
                    'category' => 'Core PMS',
                    'features' => [
                        ['label' => 'Reservasi + Check-in/out', 'hotelhms' => true, 'qlo' => true,    'hdruid' => true,  'fewoh' => true,    'erp' => true],
                        ['label' => 'Folio & Billing',       'hotelhms' => true, 'qlo' => true,    'hdruid' => true,  'fewoh' => true,    'erp' => true],
                        ['label' => 'Housekeeping',          'hotelhms' => true, 'qlo' => false,   'hdruid' => true,  'fewoh' => false,   'erp' => false],
                        ['label' => 'Night Audit',           'hotelhms' => true, 'qlo' => false,   'hdruid' => true,  'fewoh' => false,   'erp' => false],
                        ['label' => 'Group Booking',         'hotelhms' => true, 'qlo' => false,   'hdruid' => false, 'fewoh' => true,    'erp' => false],
                        ['label' => 'Tape Chart / Calendar', 'hotelhms' => true, 'qlo' => true,    'hdruid' => true,  'fewoh' => true,    'erp' => false],
                    ],
                ],
                [
                    'category' => 'Booking Engine',
                    'features' => [
                        ['label' => 'Direct Booking Website', 'hotelhms' => true, 'qlo' => true, 'hdruid' => true, 'fewoh' => true, 'erp' => false],
                        ['label' => 'Real-time Availability', 'hotelhms' => true, 'qlo' => true, 'hdruid' => true, 'fewoh' => true, 'erp' => false],
                        ['label' => 'Payment Gateway (13+)',  'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'Promo Code Engine',      'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'Abandoned Cart Recovery','hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                    ],
                ],
                [
                    'category' => 'Channel Manager',
                    'features' => [
                        ['label' => 'Booking.com (XML)',     'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => true,  'erp' => false],
                        ['label' => 'Agoda (YCS)',           'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'Traveloka (HMAC)',      'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => '9+ OTA Total',          'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => true,  'erp' => false],
                        ['label' => 'ARI Sync 2-Way',        'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'Rate Parity Monitor',   'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                    ],
                ],
                [
                    'category' => 'F&B / POS',
                    'features' => [
                        ['label' => 'Restaurant POS',        'hotelhms' => true, 'qlo' => false, 'hdruid' => true,  'fewoh' => false, 'erp' => false],
                        ['label' => 'Kitchen Display (KDS)', 'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'QR Menu Guest',         'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'Charge to Room',        'hotelhms' => true, 'qlo' => false, 'hdruid' => true,  'fewoh' => false, 'erp' => false],
                        ['label' => 'Laundry POS',           'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                    ],
                ],
                [
                    'category' => 'Accounting & Finance',
                    'features' => [
                        ['label' => 'Double-Entry GL',       'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                        ['label' => 'AR / AP',               'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                        ['label' => 'Bank Reconciliation',   'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                        ['label' => 'Trial Balance',         'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                        ['label' => 'Profit & Loss',         'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                        ['label' => 'Balance Sheet',         'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                    ],
                ],
                [
                    'category' => 'Indonesia Compliance',
                    'features' => [
                        ['label' => 'PB1 Hotel Daerah',      'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'PPN 11%',               'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                        ['label' => 'e-Faktur Coretax',      'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'Lapor WNA Imigrasi',    'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'SIPGAR Kemenparekraf',  'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'KTP/Paspor OCR',        'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                    ],
                ],
                [
                    'category' => 'Revenue Management',
                    'features' => [
                        ['label' => 'Dynamic Pricing Rules', 'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'Rate Shopper',          'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'Demand Forecast AI',    'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'Open Pricing Calendar', 'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                    ],
                ],
                [
                    'category' => 'HR & Operations',
                    'features' => [
                        ['label' => 'Employee + Attendance', 'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                        ['label' => 'Payroll + BPJS/PPh21',  'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                        ['label' => 'Asset Maintenance',     'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                        ['label' => 'Inventory (PR/PO/GR)',  'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                    ],
                ],
                [
                    'category' => 'AI & Marketing',
                    'features' => [
                        ['label' => 'AI Translate (BYOK)',   'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'AI Concierge Chatbot',  'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'AI Review Reply',       'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'Programmatic SEO',      'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                        ['label' => 'Email Campaign',        'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                    ],
                ],
                [
                    'category' => 'Teknis & Deployment',
                    'features' => [
                        ['label' => 'Standalone Install',    'hotelhms' => true, 'qlo' => true,  'hdruid' => true,  'fewoh' => false, 'erp' => false],
                        ['label' => 'White-Label Ready',     'hotelhms' => true, 'qlo' => true,  'hdruid' => true,  'fewoh' => false, 'erp' => false],
                        ['label' => 'Mobile Responsive',     'hotelhms' => true, 'qlo' => true,  'hdruid' => false, 'fewoh' => true,  'erp' => true],
                        ['label' => 'PWA Support',           'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => true],
                        ['label' => 'REST API',              'hotelhms' => true, 'qlo' => true,  'hdruid' => false, 'fewoh' => true,  'erp' => true],
                        ['label' => 'License Protection',    'hotelhms' => true, 'qlo' => false, 'hdruid' => false, 'fewoh' => false, 'erp' => false],
                    ],
                ],
            ],
            'summary' => [
                'hotelhms_score' => '51/54',
                'qlo_score'      => '11/54',
                'hdruid_score'   => '13/54',
                'fewoh_score'    => '10/54',
                'erp_score'      => '18/54',
            ],
        ];
    }

    public function features(): array
    {
        return [
            [
                'group' => 'Front Office',
                'icon' => '🏨',
                'description' => 'Reservasi calendar drag-and-drop, check-in/out flow, folio posting ganda (debit/kredit), night audit otomatis, dan e-registration card untuk tamu.',
                'bullets' => [
                    'Reservasi calendar interaktif dengan drag-and-drop dan color-coded status',
                    'Check-in flow lengkap: verifikasi ID, upload KTP/WNA, deposit capture',
                    'Folio double-entry system — setiap charge punya offset GL account',
                    'Night audit auto-posting room charge per pukul 02:00 + tutup hari bisnis',
                    'E-Registration card generate otomatis, compliance PB1 & NSFP',
                    'Room move/merge/sharing tanpa kehilangan data folio',
                ],
            ],
            [
                'group' => 'Housekeeping',
                'icon' => '🧹',
                'description' => 'Room status board real-time, task assignment ke room attendant, linen inventory tracking, inspeksi checklist, dan lost & found register.',
                'bullets' => [
                    'Room status board: dirty → cleaned → inspected → ready, real-time update',
                    'Task assignment per attendant dengan prioritas berbasis check-in hari ini',
                    'Linen management: tracking stok, kirim laundry, terima, discard',
                    'Inspection checklist digital per tipe kamar — supervisor sign-off',
                    'Lost & found register dengan foto, guest matching, dan claim resolution',
                    'Auto-create maintenance work order dari temuan HK inspection',
                ],
            ],
            [
                'group' => 'POS & F&B',
                'icon' => '🍽️',
                'description' => 'Table management visual, order taking dengan kitchen display system, bill splitting, dan integrasi laundry order untuk tamu in-house.',
                'bullets' => [
                    'Visual table map — tap meja untuk buka pesanan, indikator warna status',
                    'Kitchen Display System (KDS) — pesanan langsung tampil di layar dapur',
                    'Bill splitting: tamu bisa split bill per item atau 50/50',
                    'Post F&B charge langsung ke folio kamar tamu in-house',
                    'QR menu public — tamu scan & order mandiri via HP',
                ],
            ],
            [
                'group' => 'Channel Manager',
                'icon' => '🌐',
                'description' => 'Koneksi real-time ke Booking.com (XML/SOAP), Agoda (YCS JSON), Traveloka (v2 HMAC). ARI sync otomatis, rate parity monitoring, dan reservation fetch.',
                'bullets' => [
                    'Booking.com via XML/SOAP — real ARI push + reservation pull',
                    'Agoda via YCS JSON REST — rate & availability sync dua arah',
                    'Traveloka v2 HMAC — signed API calls dengan replay protection',
                    'Channel parity auto-alert — notifikasi jika rate selisih >5% antar OTA',
                    'Bulk ARI update — update banyak tanggal sekaligus via CSV import',
                    'Sync log per channel — audit trail lengkap setiap API call',
                ],
            ],
            [
                'group' => 'Accounting',
                'icon' => '💳',
                'description' => 'Double-entry GL dengan chart of accounts standar perhotelan (USALI-based). AR/AP, bank reconciliation, trial balance, P&L, dan daily revenue report.',
                'bullets' => [
                    'Chart of Accounts berbasis USALI — standar akuntansi perhotelan global',
                    'Journal poster otomatis dari transaksi: check-in, payment, folio, refund',
                    'AR aging + reminder otomatis ke corporate client / travel agent',
                    'Bank reconciliation — match statement dengan GL, cleared items tracking',
                    'Trial balance & P&L real-time, drill-down ke source transaction',
                    'Period lock — prevent back-dating setelah GL periode ditutup',
                ],
            ],
            [
                'group' => 'Revenue Management',
                'icon' => '💰',
                'description' => 'Open pricing calendar, dynamic pricing rules engine, rate shopper kompetitor, dan demand forecasting berbasis historical data.',
                'bullets' => [
                    'Open pricing calendar — lihat & edit rate untuk 365 hari ke depan',
                    'Dynamic pricing rules: auto-adjust rate berdasarkan occupancy threshold',
                    'Rate shopper — monitor harga kompetitor (manual input atau auto-scrape)',
                    'Demand forecast AI — prediksi okupansi 30 hari ke depan',
                    'Promo & discount management — early bird, last minute, long stay',
                ],
            ],
            [
                'group' => 'HR & Payroll',
                'icon' => '👷',
                'description' => 'Employee master data, attendance tracking, leave management, payroll processing dengan BPJS & PPh 21, performance review, dan training records.',
                'bullets' => [
                    'Employee database lengkap: kontrak, jabatan, department, grade',
                    'Attendance tracking — integrasi fingerprint atau manual log',
                    'Leave management: cuti tahunan, sakit, melahirkan, unpaid',
                    'Payroll engine: gaji pokok + tunjangan + lembur - potongan = net pay',
                    'BPJS Ketenagakerjaan & Kesehatan auto-calculation',
                    'Performance review form dengan KPI per departemen',
                ],
            ],
            [
                'group' => 'Banquet & Spa',
                'icon' => '💆',
                'description' => 'Event booking untuk wedding, meeting, conference. BEO generation, spa appointments, membership treatment packages, dan therapist scheduling.',
                'bullets' => [
                    'Banquet event booking — wedding, meeting, conference, birthday',
                    'BEO (Banquet Event Order) auto-generate dengan detail setup + menu',
                    'Spa appointment calendar — booking treatment per therapist',
                    'Membership packages: 5x massage, 10x facial dengan expiry tracking',
                    'Therapist schedule & commission calculation otomatis',
                    'Room setup plan visual — layout meja, kursi, panggung',
                ],
            ],
            [
                'group' => 'AI Tools',
                'icon' => '🤖',
                'description' => 'Bring-your-own-key AI: terjemahan otomatis, AI concierge untuk tamu, auto-reply review, dan demand forecasting. Support OpenAI, DeepSeek, Ollama, dan 20+ provider.',
                'bullets' => [
                    'AI Translate — terjemahkan konten ke 20+ bahasa untuk tamu internasional',
                    'AI Concierge — chatbot rekomendasi wisata, restoran, aktivitas lokal',
                    'AI Review Reply — generate draft balasan review Google/OTA otomatis',
                    'AI Demand Forecast — prediksi okupansi + rekomendasi harga',
                    'BYOK (Bring Your Own Key) — support 20+ provider, user input sendiri',
                ],
            ],
            [
                'group' => 'Reports',
                'icon' => '📈',
                'description' => 'Occupancy analysis, revenue breakdown, channel production comparison, cashier shift report, SIPGAR compliance, dan daily executive flash.',
                'bullets' => [
                    'Occupancy report: per tipe kamar, per channel, MoM/YoY comparison',
                    'Revenue breakdown: room, F&B, spa, banquet, other departments',
                    'Channel production: direct vs OTA contribution, commission analysis',
                    'Cashier shift report: per kasir, per shift, cash float reconciliation',
                    'SIPGAR compliance: laporan pajak siap kirim ke Dirjen Pajak',
                    'Daily flash report — 1 halaman: Occ%, RevPAR, ADR, revenue, AR',
                ],
            ],
            [
                'group' => 'Settings',
                'icon' => '⚙️',
                'description' => 'Konfigurasi properti, tax rates, user & role management, license pairing, integration providers, email templates, dan system parameters.',
                'bullets' => [
                    'Property configuration: nama, alamat, bintang, NPWP, kontak',
                    'Tax engine: PPN 11%, PB1 10%, service charge, foreign tax',
                    'RBAC dengan 11 role + Spatie Permission — granular per module',
                    'License v3 pairing — RSA-signed, AES-256-GCM encrypted lock',
                    'BYOK integration hub: payment, SMS, AI, channel — user input provider sendiri',
                    'Email + SMS template editor dengan variable substitution',
                ],
            ],
        ];
    }
}
