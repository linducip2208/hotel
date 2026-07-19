<?php

namespace Database\Seeders;

use App\Models\KbArticle;
use Illuminate\Database\Seeder;

class KbArticlesSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            ['slug' => 'check-in-procedure', 'category' => 'front_office', 'title' => 'Prosedur Check-in Standar',
                'body' => "## Langkah Check-in\n\n1. Verifikasi identitas (KTP/Paspor)\n2. Cek reservasi di sistem\n3. Konfirmasi metode pembayaran\n4. Cetak kartu registrasi (Indonesia: wajib hukum)\n5. Berikan kunci/keycard\n6. Briefing fasilitas hotel\n\n### WNA\nWajib lapor imigrasi via SIMKIM dalam 24 jam. Tabel `wna_logs` di sistem auto-populate."],
            ['slug' => 'night-audit-explained', 'category' => 'accounting', 'title' => 'Apa itu Night Audit?',
                'body' => "Night audit adalah proses end-of-day untuk menutup transaksi hari berjalan dan memulai hari baru.\n\n**Proses otomatis:**\n- Roll over occupancy + room status\n- Auto-post room charge per malam ke folio in-house\n- Compute KPI: ADR, RevPAR, occupancy\n- Mark no-show untuk arrival yg gak datang\n- Post journal entry agregat\n\nDijalankan otomatis 23:55 WIB via cron."],
            ['slug' => 'pb1-vs-ppn', 'category' => 'tax', 'title' => 'PB1 vs PPN — Apa bedanya?',
                'body' => "**PB1 (Pajak Hotel/PHR)**: pajak daerah, default 10%, dipungut hotel dari tamu, disetor ke Pemda bulanan via SPTPD.\n\n**PPN**: pajak pusat 11%, hanya berlaku untuk hotel yang sudah PKP, biasanya untuk service charge dan F&B di hotel besar.\n\nSistem auto-resolve PB1 rate dari `region_code` property ke tabel `pb1_rates`."],
            ['slug' => 'byok-integrations', 'category' => 'integrations', 'title' => 'BYOK — Bring Your Own Key',
                'body' => "Semua integrasi pihak ketiga di HotelHub adalah **BYOK** — owner masukkan API key sendiri di Settings → Integrations.\n\n**Mengapa?** Future-proof, no vendor lock-in, transparent pricing, owner kontrol penuh.\n\n**Yang BYOK:**\n- Payment Gateway (Midtrans, Xendit, ...)\n- AI / LLM (OpenAI, Anthropic, Gemini, ...)\n- SMS / WhatsApp / Email\n- Storage S3-compatible\n- Captcha"],
            ['slug' => 'channel-manager-overview', 'category' => 'channel', 'title' => 'Channel Manager — Overview',
                'body' => "Channel Manager menyinkronkan inventory + rate dari PMS ke OTA (Booking.com, Agoda, Traveloka).\n\n**Sync 2 arah:**\n- Push: ARI (Availability/Rate/Inventory) ke OTA\n- Pull: Booking masuk dari OTA → reservation otomatis\n\n**Conflict resolution UI** untuk handle overbooking saat OTA + PMS bertabrakan."],
            ['slug' => 'pseo-strategy', 'category' => 'marketing', 'title' => 'pSEO — Programmatic SEO',
                'body' => "HotelHub auto-generate halaman SEO dari template + data DB:\n\n- `/best-villa-2026`\n- `/compare/superior-vs-deluxe`\n- `/hotels-in-yogyakarta`\n- `/honeymoon-stay-bali`\n\nSemua otomatis include: schema JSON-LD, meta tags lengkap, 300+ kata konten unik, sitemap dinamis.\n\nSubmit `/sitemap.xml` ke Google Search Console untuk indexing."],
            ['slug' => 'license-pairing', 'category' => 'licensing', 'title' => 'License Pairing v3',
                'body' => "License HotelHub pakai pairing v3:\n\n1. **Pair**: License key + device fingerprint → vendor server → JWT token\n2. **Heartbeat**: Daily check ke vendor, refresh token\n3. **Grace mode**: Max 30 hari offline tanpa heartbeat\n\nKalau heartbeat lewat 30 hari → degraded mode (read-only). Kontak support untuk migrate license ke server baru."],
            ['slug' => 'cashier-shift', 'category' => 'front_office', 'title' => 'Cashier Shift Open/Close',
                'body' => "**Open shift** sebelum mulai jaga:\n- Input opening float (uang awal di laci)\n- System tracks semua payment dengan `shift_id`\n\n**Close shift** akhir jaga:\n- Input actual cash on drawer\n- System hitung expected vs actual → variance\n- Variance < 0 = kurang, perlu investigasi\n- Variance > 0 = lebih, kemungkinan typo input"],
            ['slug' => 'loyalty-points', 'category' => 'loyalty', 'title' => 'Cara Earn Points Loyalty',
                'body' => "Default: 1 point per Rp 10.000 dari `total_room` (tidak include service charge & tax).\n\n**Tier upgrade** otomatis saat lifetime points cross threshold:\n- Silver: 0+\n- Gold: 5,000+\n- Platinum: 25,000+\n\nRedeem via Settings → Loyalty → Members → Redeem."],
            ['slug' => 'group-block-howto', 'category' => 'sales', 'title' => 'Cara Buat Group Block',
                'body' => "Group block = blok N kamar untuk event/grup, ada master folio terpisah.\n\n**Langkah:**\n1. Sales → Allotments (atau langsung Group Block)\n2. Pilih tanggal, room type, jumlah\n3. Set release date (kalau gak terisi sampai tanggal X, balik ke umum)\n4. Master folio otomatis dibuat\n5. Cabang folio per kamar saat tamu individu confirmed"],
        ];

        foreach ($articles as $a) {
            KbArticle::updateOrCreate(['slug' => $a['slug']], $a + [
                'locale' => 'id',
                'is_published' => true,
                'is_public' => false,
            ]);
        }
    }
}
