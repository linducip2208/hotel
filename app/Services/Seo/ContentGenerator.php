<?php

namespace App\Services\Seo;

use App\Support\SeoData;
use Illuminate\Support\Str;

class ContentGenerator
{
    /**
     * 300+ word intro for best-category pages: /best-{category} & /best-{category}-{year}
     */
    public function bestCategoryIntro(string $category, ?string $year = null): string
    {
        $yearText = $year ? " untuk tahun {$year}" : '';
        $cat = $this->humanize($category);
        $yearRef = $year ?: date('Y');

        return implode("\n\n", [
            "Daftar terbaik {$cat}{$yearText}, dipilih berdasarkan rating tamu, kelengkapan fasilitas, dan nilai value-for-money. Kami mengevaluasi setiap akomodasi dari berbagai sudut — mulai dari kualitas tempat tidur, kebersihan kamar mandi, kecepatan Wi-Fi, hingga keramahan staf front office — agar Anda mendapatkan gambaran menyeluruh sebelum memutuskan booking.",

            "Proses kurasi kami tidak sekadar mengambil daftar dari hasil pencarian umum. Setiap properti yang masuk dalam daftar ini telah melalui serangkaian penilaian objektif: skor rating tamu minimal 4.0 dari 5.0, responsivitas manajemen terhadap keluhan tamu dalam 24 jam, konsistensi harga antar platform booking, dan keberagaman tipe kamar yang ditawarkan. Untuk kategori {$cat}, kami memberikan bobot ekstra pada aspek yang paling relevan — misalnya ketersediaan meja kerja dan stop kontak dekat tempat tidur untuk business hotel, atau kualitas soundproofing dan privasi untuk romantic getaway.",

            "Satu hal yang membedakan daftar {$cat}{$yearText} kami dari platform lain adalah transparansi harga. Semua tarif yang ditampilkan sudah termasuk pajak dan service charge — tidak ada biaya tersembunyi yang muncul di halaman checkout. Kami juga secara berkala memonitor fluktuasi tarif musiman dan event lokal di sekitar properti sehingga Anda bisa memilih tanggal menginap dengan value terbaik. Misalnya, weekday di luar musim liburan sekolah sering kali menawarkan diskon 15–30% dibanding weekend.",

            "Fasilitas standar yang wajib ada di setiap properti rekomendasi kami meliputi: Wi-Fi berkecepatan minimal 20 Mbps, AC dengan kontrol suhu individual, kamar mandi dalam dengan water heater, smart TV 32 inci ke atas, serta akses 24 jam ke resepsionis. Untuk kategori {$cat}, beberapa tambahan yang sering kami temukan di properti unggulan antara lain layanan kamar 24 jam, concierge yang bisa membantu reservasi restoran dan transportasi, serta loyalty program dengan poin yang bisa ditukar untuk menginap gratis di kunjungan berikutnya.",

            "Dari sisi keamanan dan kenyamanan, semua properti dalam daftar ini telah kami verifikasi memiliki sistem keamanan 24 jam — baik berupa CCTV di area publik, satpam berjaga, maupun akses kartu kunci ke lantai kamar. Properti juga telah lolos inspeksi dasar: tidak ada laporan serius terkait kebersihan dalam 6 bulan terakhir, tidak ada keluhan berulang tentang kebisingan, dan rasio staf terhadap tamu cukup untuk memastikan layanan personal tetap terjaga. Untuk wisatawan dengan kebutuhan khusus, kami menandai properti yang menyediakan kamar aksesibel, ramp kursi roda, dan menu diet khusus.",

            "Tips memilih {$cat} terbaik: perhatikan lokasi relatif terhadap tujuan utama perjalanan Anda — menginap di pusat kota memang praktis tetapi sering kali lebih bising dan mahal, sementara properti di area pinggiran biasanya lebih tenang dengan harga 20–40% lebih rendah. Cek juga kebijakan anak dan hewan peliharaan sebelum booking — tidak semua {$cat} mengizinkan anak di bawah 2 tahun atau hewan peliharaan. Terakhir, manfaatkan fitur chat dengan properti untuk mengkonfirmasi detail spesifik seperti ketersediaan connecting room, view kamar, atau permintaan late check-out sebelum Anda menyelesaikan pembayaran. Booking langsung via platform kami menjamin harga terbaik {$yearRef} dengan free cancellation H-1 — tanpa biaya perantara.",
        ]);
    }

    /**
     * 300+ word intro for compare pages: /compare/{a}-vs-{b}
     */
    public function compareIntro(string $a, string $b): string
    {
        return implode("\n\n", [
            "Perbandingan langsung antara {$a} dan {$b} — semua aspek penting kami sajikan side-by-side agar Anda bisa memutuskan dengan informasi lengkap. Memilih di antara dua tipe kamar yang sekilas mirip bisa menjadi keputusan yang sulit, terutama ketika selisih harga tidak terlalu signifikan. Di sinilah perbandingan terstruktur membantu: kami memecah setiap dimensi — kapasitas, konfigurasi tempat tidur, ukuran ruangan, tarif dasar, dan fasilitas tambahan — menjadi baris-baris yang mudah dibandingkan.",

            "{$a} dan {$b} masing-masing memiliki keunggulan yang mungkin cocok untuk profil tamu berbeda. {$a} unggul dalam aspek tertentu yang membuatnya ideal untuk kategori wisatawan spesifik — mungkin keluarga dengan anak kecil, pasangan yang mencari privasi, atau pebisnis yang membutuhkan ruang kerja. Di sisi lain, {$b} menonjol dalam dimensi yang berbeda, dan memahami perbedaan ini akan membantu Anda memaksimalkan kepuasan menginap tanpa membayar lebih untuk fitur yang sebenarnya tidak Anda butuhkan.",

            "Beberapa pertimbangan praktis yang sering terlewat saat membandingkan kamar: posisi kamar di dalam bangunan (lantai dasar vs lantai atas, menghadap jalan vs menghadap taman), tingkat kebisingan dari koridor atau lift, dan jarak dari lobi atau restoran. Kami sarankan Anda membaca ulasan tamu sebelumnya — terutama yang menyebut kata kunci spesifik seperti 'kebisingan', 'bau', 'AC tidak dingin', atau 'air panas tidak stabil' — karena ini adalah indikator paling jujur dari kondisi aktual kamar yang tidak bisa ditangkap oleh foto promosi.",

            "Dari sisi value-for-money, bandingkan tidak hanya tarif dasar per malam tetapi juga biaya tambahan yang mungkin timbul: apakah sarapan sudah termasuk, berapa biaya extra bed jika Anda membawa anak, apakah parkir dikenakan biaya terpisah, dan bagaimana kebijakan perubahan tanggal. {$a} dan {$b} mungkin memiliki struktur biaya tambahan yang berbeda — satu kamar bisa terlihat lebih murah di permukaan tetapi menjadi lebih mahal setelah memperhitungkan semua tambahan yang Anda butuhkan. Gunakan tabel perbandingan di bawah ini sebagai titik awal, lalu lanjutkan riset Anda dengan membaca detail fasilitas per kamar di halaman masing-masing.",

            "Rekomendasi kami: pilih {$a} jika prioritas Anda adalah [kenyamanan dan fitur unggulan yang dimiliki {$a}], dan pilih {$b} jika Anda lebih mementingkan [keunggulan spesifik {$b}]. Namun pada akhirnya, keputusan terbaik adalah yang sesuai dengan konteks perjalanan Anda — durasi menginap, jumlah tamu, anggaran total, dan ekspektasi kenyamanan. Untuk kemudahan, kami telah merangkum perbandingan dalam tabel di bawah ini. Anda juga bisa langsung booking salah satu kamar tanpa perlu kembali ke halaman pencarian — sistem kami otomatis menyimpan preferensi Anda.",
        ]);
    }

    /**
     * Context-aware FAQ questions. Pass $type to get relevant questions per page context.
     * $type: 'compare' | 'city' | 'best-category' | 'things-to-do' | 'best-time' | 'near-landmark' | 'occasion' | 'villa-feature'
     *        | 'star-hotel' | 'cheap' | 'near-landmark-short' | 'near-transport' | 'amenity' | 'alt-accommodation'
     *        | 'tips' | 'travel-guide' | 'weather' | 'events' | 'recommendation' | 'neighborhood-area'
     */
    public function defaultFaqs(string $context, string $type = 'generic'): array
    {
        $ctx = $this->humanize($context);

        return match ($type) {
            'compare' => [
                ['q' => "Mana yang lebih baik, {$ctx}?", 'a' => "Tergantung kebutuhan Anda. {$ctx} — masing-masing memiliki kelebihan. Lihat tabel perbandingan di atas untuk detail fitur, kapasitas, dan harga. Pilih berdasarkan prioritas: budget, jumlah tamu, atau fasilitas spesifik yang Anda butuhkan."],
                ['q' => "Apakah harga lebih murah di salah satu dari {$ctx}?", 'a' => "Harga kamar bergantung pada musim, okupansi, dan durasi menginap. Secara umum, kedua kamar memiliki struktur tarif dasar yang berbeda — cek detail tarif di tabel perbandingan. Booking di hari biasa (weekday) biasanya lebih murah 10–25% dibanding weekend."],
                ['q' => "Fasilitas apa yang membedakan {$ctx}?", 'a' => "Perbedaan utama terletak pada kapasitas tamu, konfigurasi tempat tidur, dan ukuran ruangan. Beberapa kamar juga memiliki fasilitas tambahan seperti bathtub, balkon pribadi, atau akses lounge. Detail lengkap bisa Anda lihat di tabel perbandingan di atas."],
                ['q' => "Lokasi mana yang lebih strategis di antara {$ctx}?", 'a' => "Kedua kamar berada dalam properti yang sama sehingga akses ke pusat kota, tempat wisata, dan transportasi publik relatif setara. Perbedaan mungkin ada pada posisi kamar di dalam bangunan — misalnya menghadap taman vs menghadap jalan."],
                ['q' => "Mana yang cocok untuk keluarga — {$ctx}?", 'a' => "Kamar dengan kapasitas lebih besar dan konfigurasi tempat tidur fleksibel (twin bed atau extra bed) lebih cocok untuk keluarga. Cek kolom Max Occupancy dan Bed Config di tabel perbandingan untuk menentukan mana yang sesuai dengan jumlah anggota keluarga Anda."],
            ],
            'city' => [
                ['q' => "Hotel terbaik di {$ctx}?", 'a' => "Daftar hotel terbaik di {$ctx} bergantung pada preferensi Anda — lokasi, budget, dan tipe perjalanan. Kami telah mengkurasi pilihan akomodasi berdasarkan rating tamu, fasilitas, dan nilai value-for-money. Telusuri daftar di halaman ini untuk membandingkan langsung."],
                ['q' => "Kapan waktu terbaik mengunjungi {$ctx}?", 'a' => "Waktu terbaik ke {$ctx} adalah musim kemarau (Mei–September) untuk cuaca optimal, namun tarif hotel cenderung 20–35% lebih tinggi. Musim hujan (November–Maret) menawarkan harga lebih murah. Hindari peak season seperti liburan Lebaran dan Tahun Baru jika Anda ingin suasana lebih tenang."],
                ['q' => "Berapa harga hotel di {$ctx}?", 'a' => "Harga hotel di {$ctx} bervariasi mulai dari Rp 200.000 untuk budget hotel hingga Rp 3.000.000+ untuk resort premium per malam. Tarif dipengaruhi oleh lokasi (pusat kota vs pinggiran), musim, dan tipe kamar. Booking 2–4 minggu sebelumnya biasanya memberikan harga terbaik."],
                ['q' => "Apakah {$ctx} aman untuk wisatawan?", 'a' => "{$ctx} umumnya aman untuk wisatawan. Seperti kota wisata pada umumnya, tetap waspada terhadap pencopetan di area ramai dan simpan barang berharga di safe deposit box hotel. Sebagian besar hotel di {$ctx} memiliki keamanan 24 jam dan CCTV di area publik."],
                ['q' => "Transportasi di {$ctx} bagaimana?", 'a' => "{$ctx} memiliki berbagai opsi transportasi: taksi online (GoCar/Grab), taksi konvensional, bus umum, dan ojek. Beberapa hotel menyediakan shuttle gratis ke area tertentu. Untuk fleksibilitas maksimal, sewa motor atau mobil banyak tersedia dengan tarif Rp 75.000–300.000 per hari."],
            ],
            'best-category' => [
                ['q' => "Apa itu hotel {$ctx}?", 'a' => "Hotel {$ctx} adalah kategori akomodasi yang memiliki karakteristik dan fasilitas spesifik sesuai tipenya. Kategori ini dipilih berdasarkan konfigurasi kamar, target tamu, dan value proposition unik yang membedakannya dari hotel standar. Setiap {$ctx} dikurasi dengan kriteria ketat untuk memastikan kualitas."],
                ['q' => "Hotel {$ctx} terbaik di Indonesia?", 'a' => "Indonesia memiliki banyak pilihan hotel {$ctx} berkualitas — dari Bali, Yogyakarta, Bandung, hingga Lombok. Daftar terbaik di halaman ini telah dikurasi berdasarkan rating tamu, konsistensi layanan, dan value-for-money. Kami memperbarui daftar setiap triwulan."],
                ['q' => "Berapa budget untuk hotel {$ctx}?", 'a' => "Budget untuk {$ctx} bervariasi: mulai dari Rp 300.000/malam untuk opsi budget, Rp 800.000–1.500.000 untuk mid-range, dan Rp 2.000.000+ untuk premium. Harga sangat dipengaruhi oleh lokasi, musim, dan fasilitas tambahan yang disertakan."],
                ['q' => "Tips memilih hotel {$ctx}", 'a' => "Tips memilih {$ctx}: (1) Cek rating tamu terbaru — minimal 4.0 dari 5.0, (2) Baca review yang menyebut fasilitas spesifik kategori ini, (3) Bandingkan harga di 2–3 platform sebelum booking, (4) Pastikan kebijakan pembatalan fleksibel, (5) Konfirmasi ketersediaan fasilitas kunci sebelum check-in."],
                ['q' => "Fasilitas standar hotel {$ctx}", 'a' => "Fasilitas standar {$ctx} meliputi Wi-Fi cepat, AC, kamar mandi dalam dengan air panas, TV layar datar, dan layanan kamar. Tergantung kategorinya, tambahan seperti kolam renang, restoran in-house, pusat bisnis, atau spa mungkin tersedia. Cek detail masing-masing properti untuk fasilitas lengkapnya."],
            ],
            'things-to-do' => [
                ['q' => "Apa saja yang bisa dilakukan di dekat {$ctx}?", 'a' => "Di sekitar {$ctx}, Anda bisa menikmati berbagai aktivitas: eksplorasi landmark lokal, wisata kuliner, fotografi, workshop budaya, dan tur berpemandu. Banyak pengunjung merekomendasikan mengalokasikan minimal setengah hari penuh untuk benar-benar menikmati area ini tanpa terburu-buru."],
                ['q' => "Berapa lama waktu yang dibutuhkan di {$ctx}?", 'a' => "Waktu ideal untuk mengeksplorasi area {$ctx} adalah 3–5 jam untuk tur dasar, atau sehari penuh jika Anda ingin mencakup semua atraksi utama dan mencicipi kuliner lokal. Jika Anda berencana melakukan aktivitas spesifik seperti trekking atau workshop, alokasikan 1–2 hari."],
                ['q' => "Apakah {$ctx} cocok untuk anak-anak?", 'a' => "Beberapa atraksi di sekitar {$ctx} ramah anak, namun ada juga yang lebih cocok untuk dewasa. Sebelum berkunjung, cek apakah tersedia fasilitas seperti toilet anak, jalur stroller, atau restoran dengan menu kids. Banyak hotel di area ini juga menyediakan kids club dan aktivitas keluarga."],
                ['q' => "Berapa biaya masuk {$ctx}?", 'a' => "Biaya masuk ke {$ctx} bervariasi — umumnya Rp 25.000–150.000 per orang untuk wisatawan domestik, dan Rp 50.000–300.000 untuk internasional. Beberapa atraksi menawarkan paket bundling yang lebih hemat. Harga bisa berubah saat peak season dan hari libur nasional."],
                ['q' => "Bagaimana cara ke {$ctx}?", 'a' => "{$ctx} dapat diakses dengan kendaraan pribadi, taksi online, atau transportasi umum tergantung lokasi. Dari pusat kota terdekat, perjalanan biasanya memakan waktu 30–90 menit. Beberapa hotel di sekitar {$ctx} menyediakan shuttle gratis atau tur terorganisir dengan transportasi termasuk dalam paket."],
            ],
            'best-time' => [
                ['q' => "Kapan peak season di {$ctx}?", 'a' => "Peak season di {$ctx} umumnya jatuh pada musim kemarau (Mei–September) dan periode liburan besar seperti Lebaran, Natal, dan Tahun Baru. Saat peak season, tarif hotel bisa naik 30–50% dan okupansi sangat tinggi — disarankan booking 6–8 minggu sebelumnya."],
                ['q' => "Musim hujan di {$ctx} bulan apa?", 'a' => "Musim hujan di {$ctx} umumnya berlangsung November hingga Maret, dengan curah hujan tertinggi di Desember–Februari. Meskipun hujan, ini adalah low season dengan harga hotel 20–40% lebih murah — cocok untuk wisatawan budget yang tidak keberatan dengan cuaca sesekali hujan."],
                ['q' => "Event tahunan apa saja di {$ctx}?", 'a' => "{$ctx} memiliki beberapa event tahunan yang menjadi daya tarik wisatawan — mulai dari festival budaya, pertunjukan seni, lomba kuliner, hingga perayaan keagamaan. Tanggal pasti bervariasi setiap tahun, jadi cek kalender event lokal sebelum merencanakan perjalanan Anda."],
                ['q' => "Kapan harga hotel paling murah di {$ctx}?", 'a' => "Harga hotel termurah di {$ctx} biasanya tersedia pada low season (November–Maret, di luar liburan Natal/Tahun Baru) dan weekday di luar musim liburan sekolah. Diskon bisa mencapai 30–50% dibanding peak season. Pantau juga promo flash sale dari platform booking."],
                ['q' => "Berapa suhu rata-rata di {$ctx}?", 'a' => "Suhu rata-rata di {$ctx} berkisar antara 22–32°C sepanjang tahun, dengan kelembaban 70–85%. Musim kemarau cenderung lebih sejuk di malam hari, sementara musim hujan terasa lebih lembab. Bawa jaket tipis untuk malam hari jika Anda berkunjung ke dataran tinggi sekitar {$ctx}."],
            ],
            'near-landmark' => [
                ['q' => "Hotel mana yang paling dekat dengan {$ctx}?", 'a' => "Beberapa hotel berada dalam jarak berjalan kaki (≤500 meter) dari {$ctx}. Kami telah mengelompokkan pilihan akomodasi berdasarkan jarak dan aksesibilitas. Properti yang paling dekat biasanya memiliki permintaan tinggi — booking lebih awal sangat disarankan, terutama saat musim liburan."],
                ['q' => "Apakah ada hotel murah dekat {$ctx}?", 'a' => "Ya, tersedia opsi budget hotel mulai dari Rp 200.000–500.000 per malam dalam radius 2–3 km dari {$ctx}. Hotel budget umumnya menawarkan kamar standar dengan fasilitas dasar. Untuk kenyamanan lebih, mid-range hotel di kisaran Rp 500.000–1.000.000 memberikan value lebih baik."],
                ['q' => "Apakah hotel dekat {$ctx} menyediakan parkir?", 'a' => "Sebagian besar hotel di sekitar {$ctx} menyediakan parkir gratis untuk tamu menginap. Beberapa hotel butik di area padat mungkin memiliki keterbatasan slot parkir — konfirmasi dengan properti sebelum kedatangan jika Anda membawa kendaraan pribadi."],
                ['q' => "Berapa jarak dari {$ctx} ke pusat kota?", 'a' => "Jarak dari {$ctx} ke pusat kota bervariasi — umumnya 5–30 menit berkendara tergantung lokasi. Transportasi online (GoCar/Grab) tersedia luas dan tarif berkisar Rp 30.000–80.000. Beberapa hotel menyediakan shuttle gratis ke area pusat kota pada jam tertentu."],
                ['q' => "Apakah {$ctx} ramai di akhir pekan?", 'a' => "{$ctx} cenderung lebih ramai di akhir pekan dan hari libur nasional karena banyak dikunjungi wisatawan domestik. Jika Anda mencari pengalaman yang lebih tenang, kunjungi pada hari Selasa–Kamis pagi. Hotel di sekitar {$ctx} juga lebih cepat penuh di weekend — booking H-3 minimal."],
            ],
            'occasion' => [
                ['q' => "Paket apa yang termasuk dalam stay {$ctx}?", 'a' => "Paket stay {$ctx} bervariasi per properti — umumnya mencakup kamar, sarapan, dan fasilitas tambahan yang relevan dengan occasion. Beberapa paket premium termasuk welcome drink, dekorasi khusus, late check-out, dan layanan tambahan. Cek detail per kamar untuk informasi lengkap."],
                ['q' => "Apakah bisa request dekorasi khusus untuk {$ctx}?", 'a' => "Ya, sebagian besar properti menerima permintaan dekorasi khusus untuk stay {$ctx} — seperti kelopak bunga, balon, atau lilin aromaterapi. Request sebaiknya diajukan minimal 3 hari sebelum check-in. Biaya tambahan mungkin berlaku tergantung kompleksitas dekorasi."],
                ['q' => "Kapan sebaiknya booking untuk stay {$ctx}?", 'a' => "Booking untuk stay {$ctx} sebaiknya dilakukan 2–4 minggu sebelum tanggal rencana, terutama jika bertepatan dengan peak season atau long weekend. Semakin awal booking, semakin banyak pilihan kamar dan semakin besar peluang mendapatkan upgrade gratis."],
                ['q' => "Apakah stay {$ctx} cocok untuk grup?", 'a' => "Banyak properti menyediakan opsi untuk grup dalam stay {$ctx} — termasuk connecting room, family suite, atau villa multi-bedroom. Kapasitas bervariasi dari 2–10 tamu tergantung tipe akomodasi. Hubungi reservasi untuk pengaturan grup di atas 5 kamar."],
                ['q' => "Bagaimana kebijakan pembatalan untuk stay {$ctx}?", 'a' => "Kebijakan pembatalan untuk stay {$ctx} mengikuti syarat tarif yang dipilih saat booking. Umumnya tersedia free cancellation H-1 hingga H-3. Beberapa paket spesial (promo, last-minute) mungkin non-refundable — baca detail sebelum konfirmasi pembayaran."],
            ],
            'villa-feature' => [
                ['q' => "Apa kelebihan villa dibanding hotel biasa di {$ctx}?", 'a' => "Villa di {$ctx} menawarkan lebih banyak privasi, ruang yang lebih lega, dan fleksibilitas untuk grup. Anda mendapatkan dapur pribadi, ruang tamu terpisah, dan sering kali kolam renang pribadi — pengalaman yang sulit didapat di kamar hotel standar."],
                ['q' => "Apakah villa di {$ctx} aman?", 'a' => "Ya, sebagian besar villa di {$ctx} dilengkapi sistem keamanan 24 jam, CCTV, dan staf keamanan. Beberapa villa dalam kompleks gated community dengan akses terbatas. Tetap gunakan safe deposit box dan pastikan pintu terkunci saat meninggalkan villa."],
                ['q' => "Berapa minimum menginap di villa {$ctx}?", 'a' => "Minimum menginap di villa {$ctx} umumnya 1–2 malam untuk hari biasa, dan 2–3 malam untuk peak season atau long weekend. Beberapa villa premium menerapkan minimum 3–5 malam saat high season. Cek kebijakan masing-masing villa sebelum booking."],
                ['q' => "Apakah villa di {$ctx} menyediakan chef pribadi?", 'a' => "Beberapa villa premium di {$ctx} menyediakan chef pribadi — baik termasuk dalam tarif maupun sebagai add-on dengan biaya tambahan Rp 300.000–750.000/hari (belum termasuk bahan makanan). Chef dapat menyiapkan menu Indonesia, Western, atau sesuai permintaan diet khusus."],
                ['q' => "Bagaimana kebersihan villa di {$ctx} dijamin?", 'a' => "Villa di {$ctx} umumnya memiliki housekeeping harian (daily cleaning), penggantian linen setiap 2–3 hari, dan pembersihan mendalam (deep cleaning) antara tamu. Beberapa villa juga menyediakan housekeeping on-demand di luar jadwal reguler. Cek kebijakan housekeeping saat check-in."],
            ],
            'star-hotel' => [
                ['q' => "Apa beda hotel bintang {$ctx} dengan bintang lainnya?", 'a' => "Semakin tinggi bintang, semakin lengkap fasilitas dan personal layanan. Bintang {$ctx} berarti level kenyamanan dan fasilitas di atas standar — ekspektasi kualitas lebih tinggi untuk kebersihan, ukuran kamar, dining options, dan layanan tambahan seperti concierge dan room service."],
                ['q' => "Apakah harga hotel bintang {$ctx} lebih mahal?", 'a' => "Ya, hotel bintang {$ctx} memiliki tarif yang lebih tinggi karena investasi fasilitas dan layanan yang lebih besar. Namun value-for-money seringkali lebih baik karena Anda mendapatkan pengalaman yang lebih lengkap."],
                ['q' => "Fasilitas apa yang wajib ada di hotel bintang {$ctx}?", 'a' => "Hotel bintang {$ctx} wajib memenuhi standar minimal yang ditetapkan oleh asosiasi hotel — termasuk ukuran kamar, jenis restoran, dan layanan 24 jam. Detail lengkap bisa dicek di halaman ini."],
                ['q' => "Apakah hotel bintang {$ctx} selalu lebih baik?", 'a' => "Tidak selalu — beberapa hotel butik bintang 3 bisa memberikan pengalaman yang lebih memorable dibanding hotel bintang 5 yang impersonal. Rating tamu dan review seringkali lebih akurat sebagai indikator kualitas daripada jumlah bintang."],
                ['q' => "Berapa budget untuk hotel bintang {$ctx} di {$ctx}?", 'a' => "Budget untuk hotel bintang {$ctx} bervariasi tergantung kota, musim, dan tipe kamar. Di Indonesia, kisaran harga bintang {$ctx} umumnya Rp 500.000–3.000.000+ per malam."],
            ],
            'cheap' => [
                ['q' => "Hotel termurah di {$ctx} berapa harganya?", 'a' => "Hotel termurah di {$ctx} bisa ditemukan mulai Rp 100.000–200.000 per malam untuk kamar dasar dengan kipas angin. Untuk hotel dengan AC dan air panas, budget minimal Rp 200.000–350.000 per malam."],
                ['q' => "Apakah hotel murah di {$ctx} bersih?", 'a' => "Banyak hotel murah di {$ctx} yang bersih dan terawat. Kuncinya adalah memilih hotel dengan rating tamu minimal 3.8/5.0 untuk kebersihan. Baca review spesifik tentang kebersihan sebelum booking."],
                ['q' => "Hotel murah di {$ctx} dekat mana?", 'a' => "Hotel murah di {$ctx} umumnya terkonsentrasi di area transit — dekat stasiun, terminal, atau area backpacker populer. Lokasi ini strategis untuk akses transportasi publik dan kuliner malam."],
                ['q' => "Apakah hotel murah di {$ctx} aman?", 'a' => "Ya, sebagian besar hotel di {$ctx} aman. Pilih hotel yang memiliki resepsionis (minimal 12 jam), kunci kamar ganda, dan penerangan yang baik di area sekitar. Baca review yang menyebut 'keamanan' atau 'aman'."],
                ['q' => "Tips booking hotel murah di {$ctx}", 'a' => "Tips: (1) pesan weekday untuk harga lebih murah, (2) cek apakah sarapan sudah termasuk, (3) bawa perlengkapan mandi sendiri, (4) booking langsung via website hotel untuk menghindari komisi platform, (5) tanyakan diskon untuk menginap panjang (weekly/monthly rate)."],
            ],
            'near-landmark-short' => [
                ['q' => "Hotel terdekat dengan {$ctx} di mana?", 'a' => "Hotel terdekat dengan {$ctx} berada dalam radius 500 meter hingga 1 km. Kami telah mengelompokkan pilihan akomodasi berdasarkan jarak dan aksesibilitas ke {$ctx}."],
                ['q' => "Apakah ada hotel murah dekat {$ctx}?", 'a' => "Ya, tersedia hotel budget mulai Rp 200.000–500.000 per malam dalam radius 2–3 km dari {$ctx}. Hotel budget ini cocok untuk wisatawan yang memprioritaskan efisiensi anggaran."],
                ['q' => "Berapa jarak hotel ke {$ctx}?", 'a' => "Jarak hotel ke {$ctx} bervariasi dari 100 meter (pintu depan) hingga 3 km. Jalan kaki 5–25 menit adalah opsi paling umum untuk hotel di sekitar {$ctx}."],
                ['q' => "Apakah hotel dekat {$ctx} menyediakan parkir?", 'a' => "Sebagian besar hotel di sekitar {$ctx} menyediakan parkir gratis untuk tamu menginap. Namun beberapa hotel di area wisata padat mungkin memiliki slot parkir terbatas."],
                ['q' => "Kapan waktu terbaik ke {$ctx}?", 'a' => "Waktu terbaik ke {$ctx} adalah pagi hari (07:00–09:00) untuk menghindari panas dan keramaian. Untuk fotografer, sore hari (15:30–17:30) menawarkan golden hour yang spektakuler."],
            ],
            'near-transport' => [
                ['q' => "Hotel dekat {$ctx} di mana yang terbaik?", 'a' => "Hotel terbaik dekat {$ctx} adalah yang menawarkan shuttle gratis, kedap suara baik, dan fleksibilitas check-in/check-out. Pilihan direkomendasikan ada di halaman ini."],
                ['q' => "Apakah hotel dekat {$ctx} menyediakan antar-jemput?", 'a' => "Banyak hotel dekat {$ctx} menyediakan shuttle gratis pada jam operasional tertentu. Konfirmasi jadwal dan rute dengan hotel sebelum booking."],
                ['q' => "Berapa harga hotel dekat {$ctx}?", 'a' => "Harga hotel dekat {$ctx} bervariasi dari Rp 250.000 untuk budget hingga Rp 1.500.000+ untuk hotel premium dengan fasilitas lengkap. Tarif biasanya 10–20% lebih tinggi dari hotel sekelas di pusat kota."],
                ['q' => "Apakah bising menginap dekat {$ctx}?", 'a' => "Kebisingan bervariasi tergantung jarak dan kualitas bangunan hotel. Hotel yang lebih mahal biasanya memiliki jendela double-glazed dan insulasi suara yang lebih baik. Minta kamar di sisi yang berlawanan dengan runway/rel."],
                ['q' => "Ada hotel transit dekat {$ctx}?", 'a' => "Beberapa hotel menawarkan paket day-use (tanpa menginap penuh) untuk transit 6–10 jam. Tanyakan langsung ke hotel karena tidak semua mengiklankan layanan ini secara online."],
            ],
            'amenity' => [
                ['q' => "Hotel {$ctx} di mana yang paling bagus?", 'a' => "Hotel dengan {$ctx} terbaik dikurasi berdasarkan rating tamu dan kelengkapan fasilitas. Lihat daftar rekomendasi kami di halaman ini untuk perbandingan langsung."],
                ['q' => "Apakah semua hotel {$ctx} menyediakan sarapan?", 'a' => "Tidak semua — sarapan biasanya bergantung pada paket yang dipilih. Hotel dengan klaim {$ctx} mungkin include breakfast atau tidak. Cek detail sebelum booking."],
                ['q' => "Apakah {$ctx} aman untuk anak-anak?", 'a' => "Sebagian besar hotel {$ctx} aman untuk anak-anak, namun pastikan Anda mengecek fasilitas keamanan spesifik seperti pagar pengaman, kedalaman kolam anak, atau ketersediaan babysitting."],
                ['q' => "Berapa budget ideal untuk hotel {$ctx}?", 'a' => "Budget untuk hotel {$ctx} bervariasi dari Rp 300.000 untuk opsi budget hingga Rp 2.000.000+ untuk hotel premium. Harga dipengaruhi oleh lokasi, musim, dan kelengkapan fasilitas."],
                ['q' => "Tips memilih hotel {$ctx}", 'a' => "Tips memilih hotel {$ctx}: (1) lihat foto yang diupload tamu, bukan foto promosi; (2) baca review spesifik tentang fasilitas tersebut; (3) cek jam operasional; (4) tanyakan biaya tambahan; (5) booking 2–3 minggu sebelumnya untuk peak season."],
            ],
            'alt-accommodation' => [
                ['q' => "Apa beda {$ctx} dengan hotel biasa?", 'a' => "{$ctx} menawarkan lebih banyak ruang, privasi, dan fleksibilitas dibanding hotel — seringkali dengan dapur pribadi dan area outdoor. Namun layanan 24 jam dan daily housekeeping tidak selalu tersedia."],
                ['q' => "Berapa harga {$ctx} di {$ctx}?", 'a' => "Harga {$ctx} bervariasi dari Rp 100.000 untuk opsi budget hingga Rp 8.000.000+ untuk villa mewah per malam. Harga sangat dipengaruhi lokasi, ukuran, dan fasilitas."],
                ['q' => "Apakah {$ctx} aman?", 'a' => "Sebagian besar {$ctx} aman, terutama yang memiliki rating tamu tinggi dan banyak review. Pilih yang memiliki sistem keamanan 24 jam, CCTV, dan lokasi di area yang ramai."],
                ['q' => "Tips booking {$ctx}", 'a' => "Tips booking {$ctx}: (1) baca semua review terbaru, (2) konfirmasi fasilitas yang termasuk via chat, (3) minta foto kondisi terkini, (4) pahami kebijakan deposit dan pembatalan, (5) simpan kontak darurat pengelola."],
                ['q' => "Apakah {$ctx} cocok untuk keluarga?", 'a' => "Banyak {$ctx} yang cocok untuk keluarga — terutama yang memiliki multiple bedroom, dapur lengkap, dan area outdoor. Kapasitas 4–10 tamu adalah umum untuk {$ctx} tipe keluarga."],
            ],
            'tips' => [
                ['q' => "Hotel terbaik di {$ctx} berdasarkan apa?", 'a' => "Hotel terbaik di {$ctx} ditentukan berdasarkan kombinasi rating tamu, lokasi, fasilitas, dan value-for-money. Tidak ada satu hotel yang 'terbaik' untuk semua orang — kami membantu Anda memilih berdasarkan prioritas perjalanan Anda."],
                ['q' => "Kapan sebaiknya booking hotel di {$ctx}?", 'a' => "Booking hotel di {$ctx} sebaiknya 2–4 minggu sebelum check-in untuk peak season (liburan sekolah, Lebaran, akhir tahun) dan 1–7 hari sebelumnya untuk low season. Last-minute deals seringkali menawarkan diskon menarik di luar peak season."],
                ['q' => "Apa saja biaya tersembunyi hotel di {$ctx}?", 'a' => "Biaya tersembunyi hotel di {$ctx} yang sering muncul: extra bed, parkir, resort fee, pajak dan service charge. Selalu tanya 'total harga all-in' sebelum booking untuk menghindari kejutan."],
                ['q' => "Aplikasi apa yang berguna di {$ctx}?", 'a' => "Aplikasi yang berguna untuk wisata di {$ctx}: Gojek/Grab untuk transportasi dan pesan makanan, Google Maps untuk navigasi, dan aplikasi booking hotel favorit Anda. Download sebelum tiba untuk menghemat data roaming."],
                ['q' => "Bagaimana cara dapat upgrade gratis di {$ctx}?", 'a' => "Cara meningkatkan peluang upgrade gratis di {$ctx}: (1) bergabung dengan loyalty program hotel, (2) booking langsung via website hotel, (3) check-in lebih awal atau lebih lambat, (4) sebutkan jika Anda merayakan momen spesial, (5) jadi tamu yang ramah dan sopan — ini benar-benar berfungsi."],
            ],
            'travel-guide' => [
                ['q' => "Berapa hari ideal di {$ctx}?", 'a' => "Durasi ideal di {$ctx} adalah 3–5 hari untuk first-timer yang ingin mencakup atraksi utama, dan 7–10 hari untuk eksplorasi yang lebih dalam dengan day trip ke area sekitar."],
                ['q' => "Apa makanan khas {$ctx} yang wajib dicoba?", 'a' => "Setiap {$ctx} memiliki kuliner khas yang wajib dicoba — dari street food harga Rp 10.000 hingga fine dining Rp 500.000+. Jangan lewatkan pasar tradisional dan warung lokal untuk pengalaman kuliner paling otentik."],
                ['q' => "Apakah {$ctx} ramah untuk wisatawan asing?", 'a' => "{$ctx} umumnya ramah untuk wisatawan asing — banyak staf hotel dan restoran yang bisa berbahasa Inggris dasar. Namun belajar beberapa kata bahasa Indonesia sederhana akan sangat dihargai dan membuka interaksi yang lebih hangat."],
                ['q' => "Bagaimana transportasi di {$ctx}?", 'a' => "Transportasi di {$ctx} meliputi ride-hailing (GoCar/Grab), taksi konvensional, bus umum, dan rental motor/mobil. Untuk fleksibilitas, rental motor adalah opsi paling populer di kalangan wisatawan."],
                ['q' => "Apa oleh-oleh khas dari {$ctx}?", 'a' => "Oleh-oleh khas {$ctx} bervariasi — dari kerajinan tangan, makanan kemasan, kain tradisional, hingga produk artisanal. Pasar tradisional dan pusat oleh-oleh adalah tempat terbaik untuk berburu dengan harga yang bisa dinegosiasi."],
            ],
            'weather' => [
                ['q' => "Bagaimana cuaca di {$ctx}?", 'a' => "Cuaca di {$ctx} bervariasi tergantung musim. Lihat detail bulanan di halaman ini untuk informasi temperatur, curah hujan, dan rekomendasi aktivitas sesuai kondisi cuaca."],
                ['q' => "Kapan musim hujan di {$ctx}?", 'a' => "Musim hujan di {$ctx} umumnya berlangsung November–Maret dengan curah hujan tertinggi di Desember–Februari. Meskipun hujan, ini adalah low season dengan harga hotel 20–40% lebih murah."],
                ['q' => "Apa yang harus dibawa ke {$ctx}?", 'a' => "Packing list untuk {$ctx} tergantung musim kunjungan: musim kemarau (pakaian ringan, sunscreen, topi), musim hujan (payung, raincoat, pakaian quick-dry). Bawa juga obat nyamuk dan adaptor colokan."],
                ['q' => "Apakah {$ctx} sering banjir?", 'a' => "Risiko banjir di {$ctx} bervariasi tergantung area dan musim. Sebagian besar area wisata aman dari banjir. Cek dengan hotel tentang kondisi area sekitar saat musim hujan."],
                ['q' => "Kapan waktu terbaik untuk foto di {$ctx}?", 'a' => "Waktu terbaik untuk fotografi di {$ctx} adalah pagi (06:00–08:00) untuk cahaya soft dan langit biru, serta sore (16:00–18:00) untuk golden hour. Hindari tengah hari (11:00–14:00) saat cahaya terlalu keras."],
            ],
            'events' => [
                ['q' => "Event apa saja di {$ctx} bulan ini?", 'a' => "{$ctx} memiliki kalender event sepanjang tahun. Cek detail event bulan ini di halaman ini atau kunjungi website resmi pariwisata {$ctx} untuk jadwal terbaru."],
                ['q' => "Apakah event di {$ctx} gratis?", 'a' => "Banyak event di {$ctx} yang gratis atau dengan biaya masuk minimal (Rp 25.000–100.000). Event besar seperti festival budaya seringkali gratis untuk ditonton, sementara konser internasional memerlukan tiket."],
                ['q' => "Kapan event terbesar di {$ctx}?", 'a' => "Event terbesar di {$ctx} umumnya berlangsung saat musim kemarau (Mei–September) atau bertepatan dengan hari libur besar. Cek kalender event di halaman ini untuk tanggal spesifik."],
                ['q' => "Bagaimana cara booking hotel saat event {$ctx}?", 'a' => "Booking hotel saat event {$ctx}: pesan 3–4 minggu sebelumnya, pilih hotel dengan jarak ≤3 km dari venue, dan cek apakah hotel menyediakan shuttle ke lokasi event. Ekspektasikan harga 20–40% lebih tinggi."],
                ['q' => "Apakah {$ctx} ramai saat event?", 'a' => "Ya, {$ctx} bisa sangat ramai saat event besar — antrian di restoran, tempat wisata, dan transportasi publik lebih panjang dari biasanya. Rencanakan itinerary dengan buffer waktu ekstra 30–60 menit untuk setiap perpindahan."],
            ],
            'recommendation' => [
                ['q' => "Hotel {$ctx} terbaik di {$ctx}?", 'a' => "Rekomendasi hotel {$ctx} terbaik di {$ctx} sudah dikurasi di halaman ini berdasarkan rating tamu, fasilitas relevan, dan konsistensi layanan. Pilih sesuai budget dan preferensi Anda."],
                ['q' => "Berapa budget untuk hotel {$ctx} di {$ctx}?", 'a' => "Budget untuk hotel {$ctx} di {$ctx} bervariasi: ekonomi Rp 200.000–500.000/malam, menengah Rp 500.000–1.500.000/malam, premium Rp 1.500.000+/malam. Harga dipengaruhi musim, lokasi, dan tipe kamar."],
                ['q' => "Apakah perlu booking jauh hari untuk {$ctx}?", 'a' => "Untuk hotel {$ctx} di {$ctx}, booking 2–4 minggu sebelumnya disarankan — terutama jika perjalanan Anda bertepatan dengan peak season atau long weekend."],
                ['q' => "Fasilitas apa yang penting untuk hotel {$ctx}?", 'a' => "Fasilitas penting untuk hotel {$ctx} di {$ctx} bergantung pada tipe perjalanan — misalnya kolam renang dan kids club untuk keluarga, meja kerja dan Wi-Fi cepat untuk bisnis, atau bathtub dan pemandangan untuk honeymoon."],
                ['q' => "Lokasi mana yang terbaik untuk hotel {$ctx} di {$ctx}?", 'a' => "Lokasi terbaik untuk hotel {$ctx} di {$ctx} tergantung pada itinerary Anda. Konsultasikan dengan tim kami untuk rekomendasi personal — kami akan menyesuaikan dengan rencana perjalanan dan preferensi Anda."],
            ],
            'neighborhood-area' => [
                ['q' => "Apa kelebihan menginap di {$ctx}?", 'a' => "Menginap di {$ctx} memberi Anda ketenangan tanpa mengorbankan akses ke atraksi utama. Area ini menawarkan karakter lokal yang otentik dengan harga hotel 15–25% lebih rendah dari pusat kota."],
                ['q' => "Apakah {$ctx} dekat pusat kota?", 'a' => "{$ctx} berjarak 5–30 menit dari pusat kota tergantung lokasi spesifik dan kondisi lalu lintas. Transportasi online tersedia 24 jam dengan tarif terjangkau."],
                ['q' => "Apa yang bisa dilakukan di {$ctx}?", 'a' => "Di {$ctx}, Anda bisa eksplorasi hidden gem lokal — kafe butik, galeri seni, pasar tradisional, dan spot foto yang belum ramai. Area ini sempurna untuk slow travel dan eksplorasi dengan berjalan kaki."],
                ['q' => "Apakah {$ctx} aman di malam hari?", 'a' => "{$ctx} umumnya aman di malam hari, namun seperti area manapun, tetap waspada. Pilih rute yang terang, hindari gang sepi sendirian, dan gunakan transportasi online untuk perjalanan malam."],
                ['q' => "Hotel di {$ctx} untuk keluarga?", 'a' => "{$ctx} memiliki beberapa hotel yang ramah keluarga dengan taman, area bermain, dan kamar connecting. Area ini menawarkan suasana yang lebih tenang untuk anak-anak dibanding pusat kota yang bising."],
            ],
            'granular-price' => [
                ['q' => "Hotel di {$ctx} yang harganya di bawah budget?", 'a' => "Kami telah mengkurasi hotel-hotel budget terbaik sesuai batas anggaran — semua di bawah harga yang Anda tentukan. Setiap hotel dalam daftar ini sudah diverifikasi untuk kebersihan, keamanan, dan kenyamanan."],
                ['q' => "Apakah hotel budget di {$ctx} bersih?", 'a' => "Ya, meskipun budget, hotel-hotel dalam daftar ini memenuhi standar kebersihan minimal dengan rating tamu di atas 3.5/5 untuk kebersihan. Kami hanya merekomendasikan hotel yang lulus inspeksi kebersihan."],
                ['q' => "Kapan harga hotel paling murah di {$ctx}?", 'a' => "Harga hotel termurah di {$ctx} biasanya di weekday (Senin–Kamis) di luar peak season (liburan sekolah, Lebaran, Natal). Musim hujan (November–Maret) juga menawarkan tarif 20–40% lebih rendah."],
                ['q' => "Apa fasilitas yang bisa diharapkan dengan budget ini di {$ctx}?", 'a' => "Dengan budget ini di {$ctx}, Anda bisa mendapat kamar bersih dengan kipas angin atau AC, kamar mandi dalam, dan Wi-Fi gratis. Fasilitas tambahan seperti sarapan atau kolam renang mungkin tidak termasuk."],
                ['q' => "Tips booking hotel budget di {$ctx}?", 'a' => "Tips: (1) pesan weekday untuk harga lebih murah; (2) booking langsung via website kami untuk harga terbaik; (3) bawa perlengkapan mandi sendiri; (4) cek apakah ada biaya tambahan; (5) manfaatkan promo last-minute jika jadwal fleksibel."],
            ],
            'price-range' => [
                ['q' => "Hotel di {$ctx} dalam rentang harga ini?", 'a' => "Kami telah mengkurasi hotel-hotel terbaik di {$ctx} dalam rentang harga yang Anda tentukan. Setiap properti dinilai dari value-for-money — bukan sekadar harga, tetapi kualitas yang Anda dapatkan untuk uang yang dikeluarkan."],
                ['q' => "Apakah harga hotel di {$ctx} sudah termasuk pajak?", 'a' => "Harga yang ditampilkan di daftar ini sudah termasuk pajak dan service charge (all-in). Tidak ada biaya tersembunyi yang muncul di halaman checkout. Namun tetap konfirmasi saat booking untuk kepastian."],
                ['q' => "Apa yang membedakan hotel di range harga ini di {$ctx}?", 'a' => "Perbedaan utama dalam rentang harga yang sama di {$ctx} adalah: lokasi, ukuran kamar, kelengkapan fasilitas, dan brand amenities. Dua hotel dengan harga yang sama bisa memberikan pengalaman yang sangat berbeda."],
                ['q' => "Kapan waktu terbaik booking di range harga ini di {$ctx}?", 'a' => "Untuk rentang harga ini di {$ctx}, booking 2-4 minggu sebelumnya memberikan pilihan terbaik. Saat peak season, rentang harga yang sama mungkin hanya mendapat kamar yang lebih kecil atau lokasi yang kurang strategis."],
                ['q' => "Apakah bisa nego harga hotel di {$ctx}?", 'a' => "Untuk hotel dalam rentang ini, negosiasi langsung jarang berhasil. Strategi yang lebih efektif: booking di weekday, pilih paket bundle, atau tanyakan corporate/long-stay rate jika applicable."],
            ],
            'room-type' => [
                ['q' => "Apa kelebihan kamar {$ctx} dibanding tipe lain?", 'a' => "Kamar {$ctx} umumnya menawarkan ruang yang lebih lega, konfigurasi tempat tidur yang berbeda, dan fasilitas tambahan tertentu yang tidak tersedia di tipe kamar di bawahnya. Lihat detail spesifik per hotel untuk perbandingan akurat."],
                ['q' => "Apakah kamar {$ctx} cocok untuk keluarga?", 'a' => "Tergantung konfigurasi spesifik kamar {$ctx} di hotel yang Anda pilih. Beberapa kamar {$ctx} memiliki kapasitas 3-4 orang dengan extra bed, sementara yang lain didesain untuk 2 orang. Cek detail kapasitas sebelum booking."],
                ['q' => "Berapa harga kamar {$ctx} di {$ctx}?", 'a' => "Harga kamar {$ctx} bervariasi dari Rp 200.000 untuk opsi budget hingga Rp 3.000.000+ untuk resort premium. Harga dipengaruhi oleh lokasi hotel, musim, view, dan fasilitas tambahan."],
                ['q' => "Bagaimana cara upgrade ke kamar {$ctx}?", 'a' => "Anda bisa upgrade ke kamar {$ctx} saat booking (pilih tipe kamar yang diinginkan) atau saat check-in (tergantung ketersediaan). Upgrade saat check-in seringkali lebih murah — tanyakan ke front desk."],
                ['q' => "Apa saja fasilitas standar kamar {$ctx} di {$ctx}?", 'a' => "Fasilitas standar kamar {$ctx}: AC, TV layar datar, kamar mandi dalam dengan air panas, Wi-Fi, dan amenities dasar. Tambahan seperti bathub, balkon, atau minibar bergantung pada kelas hotel."],
            ],
            'room-type-price' => [
                ['q' => "Berapa harga terbaru kamar {$ctx}?", 'a' => "Harga kamar {$ctx} di {$ctx} berkisar dari Rp 200.000–3.000.000+ per malam tergantung kelas hotel, musim, dan lokasi. Cek daftar di halaman ini untuk harga real-time."],
                ['q' => "Kapan harga kamar {$ctx} paling murah?", 'a' => "Harga kamar {$ctx} paling murah di {$ctx} saat weekday di luar peak season (Januari–Maret dan Oktober–November). Diskon bisa mencapai 30% dibanding peak season."],
                ['q' => "Apakah harga kamar {$ctx} sudah termasuk sarapan?", 'a' => "Tergantung hotel dan paket yang dipilih. Hotel mid-range ke atas umumnya menyertakan sarapan dalam tarif kamar {$ctx}, sementara hotel budget mungkin mengenakan biaya tambahan."],
                ['q' => "Apa yang memengaruhi harga kamar {$ctx} di {$ctx}?", 'a' => "Faktor yang memengaruhi: musim, lokasi hotel, view (sea view vs garden view), fasilitas dalam kamar, branding hotel, dan event lokal. Peak season bisa menaikkan harga 30–50%."],
                ['q' => "Tips dapat harga terbaik untuk kamar {$ctx}?", 'a' => "Tips: (1) booking 2-4 minggu sebelumnya; (2) pilih weekday; (3) manfaatkan loyalty program; (4) cek paket bundling; (5) booking langsung via website kami tanpa komisi perantara."],
            ],
            'guest-type' => [
                ['q' => "Hotel terbaik di {$ctx} untuk {$ctx}?", 'a' => "Kami telah mengkurasi hotel-hotel di {$ctx} yang spesifik cocok untuk profil tamu ini — berdasarkan lokasi, fasilitas, dan review dari tamu dengan profil serupa."],
                ['q' => "Apa fasilitas penting untuk {$ctx} di hotel?", 'a' => "Fasilitas penting bergantung pada tipe tamu — bisa meliputi connecting room, kids club, meja kerja, Wi-Fi cepat, atau layanan khusus. Cek detail masing-masing hotel untuk fasilitas yang relevan."],
                ['q' => "Apakah hotel di {$ctx} ramah untuk {$ctx}?", 'a' => "Sebagian besar hotel di {$ctx} melayani berbagai tipe tamu, namun beberapa hotel lebih terspesialisasi untuk segmen tertentu. Pilih hotel yang memiliki review positif dari tamu dengan profil serupa."],
                ['q' => "Berapa budget ideal untuk hotel {$ctx} di {$ctx}?", 'a' => "Budget bervariasi tergantung tipe tamu — mulai dari Rp 150.000 untuk opsi budget hingga Rp 5.000.000+ untuk pengalaman premium. Pilih sesuai kebutuhan dan prioritas Anda."],
                ['q' => "Tips memilih hotel untuk {$ctx} di {$ctx}?", 'a' => "Tips: (1) identifikasi prioritas utama; (2) baca review tamu dengan profil serupa; (3) komunikasikan kebutuhan spesifik saat booking; (4) pilih lokasi yang strategis untuk aktivitas Anda."],
            ],
            'season' => [
                ['q' => "Bagaimana kondisi hotel {$ctx} saat musim {$ctx}?", 'a' => "Hotel di {$ctx} saat musim ini memiliki karakteristik berbeda — dari harga, ketersediaan, hingga fasilitas yang relevan. Lihat detail di halaman ini untuk panduan lengkap."],
                ['q' => "Apakah harga hotel {$ctx} lebih murah saat musim {$ctx}?", 'a' => "Harga hotel sangat dipengaruhi musim — bisa 20–40% lebih murah atau 30–50% lebih mahal tergantung apakah ini peak season atau low season di {$ctx}."],
                ['q' => "Aktivitas apa yang cocok saat musim {$ctx} di {$ctx}?", 'a' => "Aktivitas yang cocok bergantung pada musim: musim kemarau ideal untuk outdoor (trekking, diving, city tour), musim hujan lebih cocok untuk indoor (museum, spa, kuliner, workshop)."],
                ['q' => "Apakah hotel di {$ctx} penuh saat musim {$ctx}?", 'a' => "Tingkat okupansi hotel di {$ctx} sangat fluktuatif antar musim. Peak season bisa 90–100% penuh, low season bisa 40–60%. Booking lebih awal untuk peak season sangat disarankan."],
                ['q' => "Tips memilih hotel {$ctx} sesuai musim {$ctx}?", 'a' => "Tips: (1) pilih hotel dengan fasilitas yang relevan dengan musim; (2) booking lebih awal untuk peak season; (3) manfaatkan diskon low season; (4) cek kebijakan pembatalan fleksibel."],
            ],
            'holiday' => [
                ['q' => "Hotel {$ctx} untuk liburan {$ctx} — kapan harus booking?", 'a' => "Untuk liburan {$ctx} di {$ctx}, booking 6-8 minggu sebelumnya sangat disarankan. Hotel-hotel terbaik biasanya sudah fully booked 4-6 minggu sebelum {$ctx}."],
                ['q' => "Apakah harga hotel {$ctx} naik saat liburan {$ctx}?", 'a' => "Ya, harga hotel di {$ctx} bisa naik 30–50% saat liburan {$ctx}. Ini adalah peak season dengan permintaan sangat tinggi. Booking early adalah satu-satunya cara mengamankan harga terbaik."],
                ['q' => "Hotel mana yang ada program spesial {$ctx} di {$ctx}?", 'a' => "Banyak hotel di {$ctx} menyelenggarakan program spesial {$ctx}: dinner spesial, aktivitas anak, dekorasi tematik, dan paket menginap dengan tema {$ctx}. Cek detail per hotel di halaman ini."],
                ['q' => "Apakah hotel di {$ctx} menyediakan extra bed saat {$ctx}?", 'a' => "Sebagian besar hotel menyediakan extra bed (Rp 100.000–300.000) namun ketersediaan terbatas saat {$ctx} karena banyak keluarga yang membutuhkan. Request extra bed saat booking, jangan saat check-in."],
                ['q' => "Tips booking hotel {$ctx} untuk {$ctx}?", 'a' => "Tips: (1) pesan 6-8 minggu sebelumnya; (2) siapkan budget 30–50% lebih tinggi; (3) pilih free cancellation; (4) pertimbangkan hotel di pinggiran untuk harga lebih masuk akal; (5) tanyakan paket spesial {$ctx}."],
            ],
            'distance-city' => [
                ['q' => "Hotel di {$ctx} yang paling dekat pusat kota?", 'a' => "Hotel dalam radius yang Anda tentukan dari pusat {$ctx} telah kami kurasi di halaman ini. Jarak aktual dan estimasi waktu tempuh tercantum untuk setiap properti."],
                ['q' => "Bagaimana transportasi dari hotel di {$ctx} ke pusat kota?", 'a' => "Dari hotel dalam radius ini, transportasi online (GoCar/Grab) tersedia 24 jam dengan tarif Rp 15.000–50.000. Beberapa hotel juga menyediakan shuttle gratis ke pusat kota pada jam tertentu."],
                ['q' => "Apakah hotel di {$ctx} lebih murah jika jauh dari pusat?", 'a' => "Ya, hotel di luar pusat kota {$ctx} umumnya 15–30% lebih murah untuk kualitas yang sama. Penghematan ini bisa dialokasikan untuk transportasi dan atraksi."],
                ['q' => "Apa keuntungan menginap di {$ctx} radius ini?", 'a' => "Keuntungan: harga lebih rendah, suasana lebih tenang, parkir lebih luas, dan pengalaman lokal yang lebih autentik. Kompromi: perlu transportasi tambahan ke pusat kota."],
                ['q' => "Apakah aman berjalan kaki dari hotel ke pusat {$ctx}?", 'a' => "Tergantung jarak spesifik dan infrastruktur pejalan kaki di {$ctx}. Untuk radius dekat (≤1 km), jalan kaki nyaman. Untuk radius lebih jauh, gunakan transportasi — terutama di malam hari."],
            ],
            'distance-landmark' => [
                ['q' => "Hotel terdekat dengan {$ctx}?", 'a' => "Kami telah mengelompokkan hotel berdasarkan jarak dari {$ctx}. Beberapa hotel berada dalam jarak berjalan kaki (≤500m), yang lain bisa dijangkau dengan transportasi singkat."],
                ['q' => "Apakah hotel dekat {$ctx} menyediakan view langsung?", 'a' => "Beberapa hotel di radius ini menawarkan kamar dengan view langsung ke {$ctx} — pengalaman yang sangat dicari. Cek tipe kamar 'dengan view' saat booking."],
                ['q' => "Berapa biaya transportasi dari hotel radius ini ke {$ctx}?", 'a' => "Dari hotel radius ini ke {$ctx}, jalan kaki gratis (≤500m), ojek online Rp 10.000–25.000, atau shuttle hotel (gratis jika tersedia). Sangat terjangkau."],
                ['q' => "Apakah area sekitar {$ctx} aman di malam hari?", 'a' => "Area wisata sekitar {$ctx} umumnya aman hingga malam karena banyak wisatawan dan pencahayaan yang baik. Namun tetap waspada dan gunakan transportasi untuk jarak jauh."],
                ['q' => "Kapan waktu terbaik menginap dekat {$ctx}?", 'a' => "Waktu terbaik: weekday di luar peak season untuk harga lebih rendah dan {$ctx} yang lebih sepi. Hindari long weekend dan liburan nasional jika Anda tidak suka keramaian."],
            ],
            'question' => [
                ['q' => "Informasi apa yang paling penting untuk {$ctx}?", 'a' => "Informasi paling penting tentang {$ctx} telah kami rangkum di halaman ini — berdasarkan data terkini, feedback wisatawan, dan pengalaman langsung tim kami di lapangan."],
                ['q' => "Apakah informasi tentang {$ctx} ini up-to-date?", 'a' => "Ya, kami memperbarui halaman ini secara berkala — setiap 1-3 bulan atau lebih cepat jika ada perubahan signifikan. Tanggal update terakhir tercantum di bagian bawah halaman."],
                ['q' => "Bagaimana cara verifikasi info tentang {$ctx}?", 'a' => "Semua informasi di halaman ini telah diverifikasi melalui kombinasi: situs resmi pemerintah, review wisatawan terkini, konfirmasi langsung ke pengelola atraksi, dan laporan tim lapangan kami."],
                ['q' => "Ada tips tambahan tentang {$ctx}?", 'a' => "Tips tambahan selalu kami update di halaman ini. Anda juga bisa menghubungi tim reservasi kami untuk rekomendasi personal — gratis, tanpa kewajiban booking."],
                ['q' => "Bagaimana jika informasi tentang {$ctx} berubah?", 'a' => "Jika ada perubahan signifikan, kami segera update halaman ini. Namun untuk kepastian, selalu konfirmasi langsung ke hotel atau atraksi terkait sebelum keberangkatan."],
            ],
            'compare-cities' => [
                ['q' => "Mana yang lebih baik, {$ctx}?", 'a' => "Tidak ada yang secara mutlak 'lebih baik' — {$ctx} masing-masing memiliki keunggulan untuk tipe wisatawan yang berbeda. Lihat tabel perbandingan di atas untuk memutuskan berdasarkan prioritas Anda."],
                ['q' => "Mana yang lebih murah, {$ctx}?", 'a' => "Biaya akomodasi dan hidup di {$ctx} bisa berbeda. Secara umum, salah satu kota cenderung lebih terjangkau untuk hotel dan makan, sementara yang lain mungkin lebih murah untuk transportasi dan atraksi."],
                ['q' => "Mana yang lebih cocok untuk keluarga, {$ctx}?", 'a' => "Kedua destinasi ini memiliki atraksi keluarga, namun salah satu mungkin lebih unggul dalam hal fasilitas ramah anak, keamanan, dan variasi aktivitas yang cocok untuk segala usia."],
                ['q' => "Berapa lama waktu yang dibutuhkan di masing-masing {$ctx}?", 'a' => "Durasi ideal: 3-5 hari per kota untuk eksplorasi dasar, atau 7-10 hari jika ingin lebih mendalam. Jika waktu terbatas, pilih salah satu dan simpan yang lain untuk kunjungan berikutnya."],
                ['q' => "Apakah mudah berpindah antara {$ctx}?", 'a' => "Konektivitas antar {$ctx} umumnya baik — penerbangan langsung tersedia (1-2 jam) atau transportasi darat untuk kota di pulau yang sama. Perjalanan antar kota bisa memakan waktu setengah hingga satu hari penuh."],
            ],
            'compare-neighborhoods' => [
                ['q' => "Mana yang lebih cocok untuk menginap, {$ctx}?", 'a' => "{$ctx} — pilihan tergantung pada prioritas: suasana, budget, akses ke atraksi, atau fasilitas sekitar. Lihat tabel perbandingan untuk membantu keputusan Anda."],
                ['q' => "Apakah {$ctx} aman untuk wisatawan?", 'a' => "Kedua area {$ctx} aman untuk wisatawan. Perbedaan utama adalah pada tingkat keramaian di malam hari — area yang lebih ramai cenderung lebih terang dan lebih banyak orang, area yang lebih tenang mungkin lebih sepi."],
                ['q' => "Berapa selisih harga hotel antara {$ctx}?", 'a' => "Selisih harga hotel antara {$ctx} bisa 10-30% untuk kualitas yang setara. Perbedaan ini dipengaruhi oleh popularitas area, proximity ke atraksi, dan ketersediaan lahan."],
                ['q' => "Bagaimana akses transportasi dari {$ctx}?", 'a' => "Kedua area {$ctx} terhubung dengan transportasi online. Namun salah satu mungkin lebih dekat ke halte transportasi publik atau memiliki akses jalan yang lebih baik."],
                ['q' => "Area mana yang lebih dekat ke atraksi {$ctx}?", 'a' => "Dari {$ctx}, jarak ke atraksi utama berbeda. Salah satu area mungkin lebih dekat ke pusat kota atau spot wisata, sementara area lain menawarkan akses lebih mudah ke atraksi spesifik."],
            ],
            'source-code' => [
                ['q' => "Apa yang termasuk dalam source code {$ctx}?", 'a' => "Source code HotelHub HMS mencakup seluruh sistem: Front Office, POS, Accounting, Channel Manager, Revenue Management, Housekeeping, HR & Payroll — total 23+ modul Laravel 11 dengan 122 automated tests dan dokumentasi lengkap. Anda mendapat full ownership, self-host di server sendiri tanpa biaya berlangganan bulanan."],
                ['q' => "Berapa harga source code {$ctx}?", 'a' => "Harga source code HotelHub HMS bervariasi sesuai paket: Basic (single property), Growth (multi-property), dan Enterprise (whitelabel + full customization). Hubungi WhatsApp 081296052010 untuk quotation detail sesuai kebutuhan Anda. Tidak ada biaya bulanan tersembunyi — one-time purchase, lifetime ownership."],
                ['q' => "Apakah {$ctx} bisa dicoba dulu?", 'a' => "Ya! Anda bisa langsung mencoba demo di /docs atau melalui link demo di halaman ini. Kami juga menyediakan trial 7 hari untuk calon buyer serius — chat WA 081296052010 untuk akses. Demo mencakup semua modul dengan data dummy siap uji."],
                ['q' => "Apa kelebihan {$ctx} dibanding SaaS hotel?", 'a' => "Keuntungan utama: (1) Full source code ownership — Anda tidak bergantung pada vendor; (2) Self-host — data tamu tetap di server Anda; (3) No monthly fees — one-time investment; (4) Customizable — ubah sesuka hati; (5) White-label — branding sendiri. Bandingkan dengan SaaS yang Anda bayar bulanan selamanya tanpa memiliki apa-apa."],
                ['q' => "Teknologi apa yang digunakan {$ctx}?", 'a' => "HotelHub HMS dibangun dengan Laravel 11 (PHP 8.3+), MySQL, Livewire/Filament admin panel, Tailwind CSS responsive. Mendukung PWA, webhook, API REST. BYOK payment gateway (13+ provider), AI adapter (20+ LLM provider), dan OTA channel manager (10 channel). Full stack dengan deployment Nginx + PHP-FPM standar."],
            ],
            'feature-city' => [
                ['q' => "Hotel {$ctx} dengan fasilitas terbaik?", 'a' => "Kami sudah mengkurasi hotel-hotel {$ctx} dengan fasilitas lengkap — dari kamar AC dan Wi-Fi cepat hingga kolam renang, spa, dan restoran in-house. Lihat daftar rekomendasi di halaman ini untuk bandingkan langsung."],
                ['q' => "Apakah hotel {$ctx} cocok untuk keluarga?", 'a' => "Hotel {$ctx} banyak yang menyediakan fasilitas keluarga: connecting room, kids club, kolam renang anak, dan menu khusus. Ideal untuk liburan keluarga dengan budget fleksibel."],
                ['q' => "Berapa harga hotel {$ctx}?", 'a' => "Harga hotel {$ctx} bervariasi dari Rp 200.000 untuk budget hingga Rp 3.000.000+ untuk resort lengkap. Harga dipengaruhi lokasi, musim, dan fasilitas spesifik yang dipilih."],
                ['q' => "Kapan waktu terbaik booking hotel {$ctx}?", 'a' => "Booking 2-4 minggu sebelumnya untuk peak season, 1-7 hari untuk low season. Weekday umumnya 10-25% lebih murah. Cek kalender event lokal untuk menghindari tanggal ramai."],
                ['q' => "Tips memilih hotel {$ctx}?", 'a' => "Tips: (1) Baca review tamu 3 bulan terakhir; (2) Konfirmasi fasilitas yang diiklankan benar-benar tersedia; (3) Bandingkan harga all-in (termasuk pajak); (4) Tanyakan kebijakan anak dan hewan peliharaan; (5) Booking via website ini untuk harga terbaik tanpa biaya perantara."],
            ],
            default => [
                ['q' => "Apa yang termasuk dalam tarif {$ctx}?", 'a' => "Tarif standar mencakup kamar, fasilitas dasar, dan akses area umum. Sarapan dan tambahan lain bergantung paket yang dipilih saat booking."],
                ['q' => "Bagaimana cara membatalkan booking {$ctx}?", 'a' => "Pembatalan tersedia melalui link 'Manage Booking' yang dikirim via email. Kebijakan refund mengikuti syarat tarif yang dipilih."],
                ['q' => "Apakah {$ctx} cocok untuk keluarga?", 'a' => "Banyak pilihan {$ctx} memiliki kamar dengan kapasitas keluarga (extra bed, kamar interconnecting, area bermain). Cek detail fasilitas per kamar."],
                ['q' => "Apakah ada parkir gratis di {$ctx}?", 'a' => "Sebagian besar properti menyediakan parkir gratis untuk tamu menginap. Konfirmasi dengan pihak hotel saat melakukan reservasi."],
                ['q' => "Bagaimana check-in di {$ctx}?", 'a' => "Check-in standar pukul 14:00 dan check-out pukul 12:00. Early check-in / late check-out tersedia berdasarkan ketersediaan kamar."],
            ],
        };
    }

    /**
     * Long-form intro (≥300 kata) untuk listing kota — dengan detail spesifik per kota.
     */
    public function cityListingIntro(string $citySlug, ?string $neighborhood = null, ?string $year = null, ?string $price = null): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);
        $neighborhoodText = $neighborhood ? " kawasan {$this->humanize($neighborhood)}" : '';
        $yearText = $year ? " yang masih relevan tahun {$year}" : '';
        $priceText = $price ? " dengan tarif di bawah Rp {$this->humanize($price)}" : '';

        $cityDetail = $this->cityDetail($citySlug);

        return implode("\n\n", [
            "Mencari hotel di {$cityName}{$neighborhoodText}{$priceText}{$yearText}? Kami menyusun daftar pilihan akomodasi terbaik berdasarkan rating tamu, lokasi strategis, dan rasio harga vs fasilitas. {$cityName} adalah salah satu destinasi utama di Indonesia — {$cityDetail} Dengan keragaman pilihan akomodasi mulai dari losmen budget Rp 150.000/malam hingga resort bintang lima di atas Rp 5.000.000/malam, {$cityName} melayani semua segmen wisatawan dengan standar hospitality yang terus meningkat setiap tahun.",

            "Karakteristik hotel di {$cityName} sangat dipengaruhi oleh geografi dan demografi pengunjung kota ini. {$this->cityHotelCharacteristic($citySlug)} Kami telah mengkurasi pilihan akomodasi yang mencerminkan karakter lokal sambil tetap memenuhi standar internasional — Wi-Fi cepat, AC, air panas, kebersihan prima, dan layanan pelanggan yang responsif.",

            "Dari sisi harga, {$cityName} menawarkan spektrum yang luas. Hotel budget di kisaran Rp 150.000–400.000/malam umumnya menyediakan kamar standar dengan fasilitas dasar, cocok untuk backpacker dan solo traveler. Hotel mid-range di Rp 400.000–1.200.000 sudah mencakup sarapan, kolam renang, dan ruang pertemuan — segmen paling populer untuk keluarga Indonesia. Hotel premium di atas Rp 1.500.000 menawarkan pengalaman lengkap: spa, fine dining, lounge eksekutif, dan layanan concierge yang dapat mengatur tur pribadi ke destinasi sekitar {$cityName}. {$priceText}",

            "Booking langsung melalui platform kami memberikan Anda akses ke harga terbaik {$cityName} tanpa biaya perantara. Kami tidak membebankan komisi tambahan — harga yang Anda lihat adalah harga yang Anda bayar. Sistem kami juga mendukung free cancellation H-1 untuk sebagian besar tipe kamar, sehingga Anda bisa merencanakan perjalanan dengan fleksibilitas maksimal. Jika rencana berubah, Anda tinggal membatalkan via link di email konfirmasi tanpa penalti.",

            "Untuk wisatawan yang baru pertama kali ke {$cityName}, berikut tips praktis: (1) pilih hotel dalam radius 3 km dari pusat kota atau area yang ingin Anda eksplorasi untuk menghemat waktu transportasi; (2) download aplikasi transportasi online sebelum tiba karena ini adalah moda paling praktis dan transparan di {$cityName}; (3) cek kalender event lokal — konser, festival, atau konferensi besar bisa membuat hotel penuh dan harga melonjak; (4) jika Anda sensitif terhadap kebisingan, minta kamar di lantai atas dan bukan menghadap jalan utama; (5) bawa adaptor colokan jika Anda datang dari luar Indonesia karena sebagian besar hotel menggunakan stop kontak tipe C dan G.",

            "Bagi tamu bisnis, hotel di pusat kota {$cityName} umumnya menyediakan business center, ruang meeting dengan kapasitas hingga 100 orang, dan akses transportasi ke area perkantoran. Banyak hotel juga menawarkan paket corporate rate untuk pemesanan rutin. Hubungi tim reservasi kami untuk pengaturan khusus — kami dapat membantu negosiasi tarif korporat jangka panjang.",
        ]);
    }

    /**
     * 300+ word intro untuk landmark / hotels near landmark.
     */
    public function landmarkIntro(string $landmarkSlug, ?string $landmarkName, ?string $cityName): string
    {
        $name = $landmarkName ?: $this->humanize($landmarkSlug);
        $city = $cityName ?: 'kota tersebut';
        $landmarkDesc = $this->landmarkDescription($landmarkSlug, $name);

        return implode("\n\n", [
            "Berkunjung ke {$name}? Kami merangkum hotel-hotel terbaik di sekitar {$name} dengan akses cepat, harga kompetitif, dan rating tamu yang konsisten. {$landmarkDesc} Dengan memilih akomodasi di sekitar {$name}, Anda tidak hanya menghemat waktu perjalanan tetapi juga mendapatkan kesempatan untuk menikmati suasana sekitar di pagi hari sebelum keramaian datang, atau di sore hari setelah sebagian besar wisatawan pulang.",

            "Lokasi {$name} di {$city} merupakan salah satu titik populer baik untuk wisatawan lokal maupun internasional. Memilih hotel dengan jarak ≤2 km dari {$name} membantu menghemat waktu transportasi dan memungkinkan Anda kembali ke kamar untuk istirahat siang sebelum melanjutkan eksplorasi sore. Beberapa hotel di radius 500 meter bahkan menawarkan 'view langsung' ke {$name} — pengalaman yang sangat dicari oleh fotografer dan pasangan yang merayakan momen spesial. Saat memilih hotel, perhatikan juga akses pejalan kaki: trotoar yang baik dan penerangan jalan yang memadai akan membuat jalan kaki pagi atau malam ke {$name} terasa aman dan menyenangkan.",

            "Fasilitas hotel di area {$name} sangat beragam. Hotel budget di kisaran Rp 200.000–500.000/malam menawarkan kamar bersih dengan fasilitas standar, cocok untuk backpacker dan solo traveler yang menghabiskan sebagian besar waktu di luar hotel. Hotel mid-range di Rp 500.000–1.500.000 umumnya menyertakan sarapan, kolam renang, dan parkir gratis — ideal untuk keluarga dengan anak-anak. Butik hotel dan resort di atas Rp 1.500.000 menawarkan pengalaman yang lebih curated: arsitektur yang menyatu dengan lingkungan, menu restoran yang menggabungkan bahan lokal dengan teknik internasional, dan layanan concierge yang dapat menyusun itinerary personal dari pagi hingga malam.",

            "Tim kami juga mengkurasi paket bundle tertentu — misalnya menginap 2 malam + tiket masuk {$name} + sarapan — yang sering kali lebih hemat 15–25% dibanding membeli terpisah. Beberapa hotel juga bekerja sama dengan operator tur lokal untuk memberikan diskon eksklusif bagi tamu yang memesan via mereka. Cek detail per kamar untuk melihat paket terbaru yang tersedia. Untuk pengalaman yang lebih seamless, beberapa hotel menyediakan shuttle service gratis ke {$name} pada jam tertentu — konfirmasi jadwal dan rute dengan front desk sebelum mengatur rencana perjalanan Anda.",

            "Untuk wisatawan yang membawa keluarga, pilih hotel dengan connecting room atau family suite yang memberikan ruang lebih untuk anak-anak beraktivitas. Hotel dengan kolam renang anak dan kids menu di restoran akan sangat membantu orang tua. Untuk solo traveler atau perjalanan bisnis, kamar boutique compact dengan meja kerja dan Wi-Fi 20+ Mbps adalah pilihan paling efisien. Beberapa hotel di sekitar {$name} juga menawarkan coworking space yang bisa digunakan tamu non-menginap — praktis jika Anda perlu menyelesaikan pekerjaan sebelum atau sesudah mengunjungi {$name}. Terakhir, selalu cek kebijakan parkir jika Anda membawa kendaraan sendiri karena beberapa hotel di area wisata populer memiliki slot parkir terbatas.",
        ]);
    }

    /**
     * 300+ word intro untuk best-time-to-visit.
     */
    public function bestTimeIntro(string $citySlug): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);
        $climateDetail = $this->climateDetail($citySlug);

        return implode("\n\n", [
            "Kapan waktu terbaik berkunjung ke {$cityName}? Iklim, event lokal, harga kamar, dan padat tidaknya destinasi sangat memengaruhi kualitas perjalanan Anda. {$climateDetail} Memahami pola cuaca dan kalender wisata adalah kunci untuk mendapatkan pengalaman terbaik di {$cityName} — baik dari sisi kenyamanan, budget, maupun akses ke atraksi.",

            "Secara umum, musim kemarau (Mei–September) adalah peak season di {$cityName} — cuaca cerah, kelembaban lebih rendah, dan hampir semua atraksi beroperasi penuh. Namun tarif hotel umumnya 20–35% lebih tinggi, tempat wisata lebih ramai, dan Anda perlu antre lebih lama di spot-spot populer. Musim hujan (November–Maret) membawa curah hujan yang cukup tinggi — beberapa atraksi outdoor mungkin terbatas atau ditutup — tetapi harga akomodasi bisa turun 30–50% dan Anda akan menikmati {$cityName} yang lebih sepi dan intim. Periode pancaroba (April dan Oktober) sering menjadi sweet spot: cuaca cukup baik, keramaian belum mencapai puncak, dan harga masih rasional.",

            "{$cityName} memiliki sejumlah event tahunan yang patut dipertimbangkan dalam perencanaan. Festival budaya, lomba kuliner, pertunjukan seni, atau perayaan keagamaan lokal sering kali menarik ribuan wisatawan dan membuat akomodasi cepat penuh. Jika Anda ingin datang saat event-event ini, booking 6–8 minggu sebelumnya sangat disarankan — beberapa hotel bahkan sudah fully booked 3 bulan sebelum event besar. Sebaliknya, jika Anda menghindari keramaian, cukup periksa kalender event dan pilih tanggal di luar periode tersebut.",

            "Untuk perjalanan bisnis, weekday di luar minggu hari raya biasanya menawarkan tarif lebih murah dan ketersediaan kamar lebih baik. Hotel-hotel bisnis di pusat kota {$cityName} bahkan sering memberikan corporate rate untuk pemesanan berulang. Sebaliknya, untuk staycation keluarga, weekend dan masa liburan sekolah cenderung ramai — strategi terbaik adalah memilih long weekend di awal kuartal (Januari–Maret) saat permintaan lebih rendah. Pensiunan dan digital nomad sering memilih {$cityName} untuk long stay 1–3 bulan di luar peak season — banyak hotel dan guesthouse yang menawarkan tarif bulanan dengan diskon signifikan.",

            "Tips tambahan: selalu pantau prakiraan cuaca 7 hari sebelum keberangkatan karena kondisi aktual bisa berbeda dari pola musiman. Bawa perlengkapan yang sesuai — jaket ringan untuk malam di dataran tinggi, payung lipat untuk antisipasi hujan mendadak, dan alas kaki yang nyaman karena Anda mungkin akan banyak berjalan. Tim reservasi kami juga dapat membantu Anda menyusun itinerary berdasarkan musim kunjungan — termasuk rekomendasi indoor activities untuk hari hujan di {$cityName}, atau spot sunset/sunrise terbaik yang hanya accessible di musim tertentu. Hubungi kami untuk konsultasi gratis perencanaan perjalanan Anda ke {$cityName}.",
        ]);
    }

    /**
     * 300+ word intro untuk occasion stay.
     */
    public function occasionIntro(string $occasion, string $citySlug): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);
        $occ = $this->humanize($occasion);

        $blurb = match ($occasion) {
            'honeymoon' => "Paket honeymoon di {$cityName} biasanya mencakup welcome drink, dekorasi kamar romantis, candle light dinner, dan late check-out — cocok untuk pasangan baru yang ingin pengalaman tak terlupakan. Beberapa hotel bahkan menyediakan paket foto pre-wedding dengan latar pemandangan ikonik {$cityName}, private dining di tepi kolam, atau couple spa treatment dengan bahan alami lokal.",
            'family'    => "Pilihan kamar family di {$cityName} menyediakan twin bed, extra bed, atau connecting room. Banyak hotel keluarga juga menyediakan kids meal, area bermain anak, kolam renang anak, dan aktivitas harian terprogram — dari kelas memasak, melukis, hingga treasure hunt di taman hotel. Keamanan adalah prioritas: lifeguard di kolam renang, gate kolam yang terkunci, dan staf yang terlatih P3K.",
            'business'  => "Perjalanan bisnis ke {$cityName}? Kami pilih hotel dengan Wi-Fi berkecepatan 30+ Mbps, business center yang beroperasi 24 jam, ruang meeting dengan kapasitas variatif (10–100 orang), dan akses transportasi mudah ke area perkantoran dan convention center. Banyak hotel bisnis juga menawarkan executive lounge dengan sarapan, afternoon tea, dan evening cocktail — included dalam tarif kamar tertentu.",
            'romantic'  => "Untuk getaway romantis di {$cityName}, kami seleksi kamar dengan bathtub, balkon private, jacuzzi, atau private pool. Beberapa properti menawarkan paket 'romance turndown' — kelopak mawar di tempat tidur, lilin aromaterapi, dan sparkling wine lokal. Restoran hotel sering kali punya menu degustasi khusus pasangan yang bisa dipesan in-room untuk privasi maksimal.",
            'wedding'   => "Mengadakan wedding di {$cityName}? Beberapa properti menyediakan paket lengkap: akad + resepsi + akomodasi tamu, termasuk wedding planner pendamping, dekorasi tema, catering dengan menu kustom, sound system, dan dokumentasi. Kapasitas bervariasi dari intimate wedding 30 tamu hingga grand ballroom 1.000 tamu. Beberapa hotel juga menyediakan bridal suite dengan akses eksklusif ke area foto outdoor.",
            default     => "Paket {$occ} di {$cityName} disesuaikan dengan kebutuhan Anda — mulai dari kamar hingga layanan tambahan yang relevan dengan occasion ini. Kami akan mengarahkan Anda ke properti yang memiliki pengalaman dan fasilitas spesifik untuk kebutuhan {$occ}.",
        };

        return implode("\n\n", [
            "Mencari akomodasi {$occ} di {$cityName}? Kami menyusun pilihan kamar yang paling cocok untuk konteks {$occ} — dengan fasilitas, layanan, dan suasana yang relevan. Perencanaan stay {$occ} membutuhkan perhatian pada detail yang sering terlewat: posisi kamar (jauh dari lift dan area bising untuk honeymoon, dekat kolam anak untuk family), konfigurasi tempat tidur (king bed vs twin, ketersediaan baby cot), dan fleksibilitas jadwal (early check-in untuk penerbangan pagi, late check-out untuk acara malam).",

            $blurb,

            "Booking langsung di sini memberikan akses ke harga terbaik tanpa perantara, plus free cancellation hingga H-1. Tim reservasi kami akan menghubungi Anda untuk konfirmasi detail tambahan setelah booking dibuat — termasuk preferensi kamar, waktu kedatangan, dan permintaan khusus. Untuk {$occ} group di atas 5 kamar, kami juga bisa membantu negosiasi group rate yang biasanya 10–20% di bawah published rate. Proses booking group memerlukan waktu 2–3 hari kerja untuk konfirmasi ketersediaan.",

            "Layanan tambahan yang bisa di-request untuk stay {$occ} di {$cityName} meliputi: airport transfer (private car/minibus), tur setengah hari atau sehari penuh ke destinasi sekitar {$cityName}, fotografer profesional untuk sesi foto, jasa babysitting untuk tamu family yang ingin dinner romantis, dan layanan laundry express. Semua layanan ini bisa dipesan melalui concierge setelah check-in, namun kami sarankan booking lebih awal untuk ketersediaan optimal — terutama saat peak season.",

            "Tips memaksimalkan stay {$occ} di {$cityName}: (1) komunikasikan ekspektasi Anda dengan jelas ke tim reservasi — semakin detail, semakin personal layanan yang bisa disiapkan; (2) cek paket bundle yang mungkin sudah mencakup fasilitas yang Anda rencanakan, sering kali lebih hemat dibanding add-on terpisah; (3) bawa dokumen pendukung jika diperlukan — misalnya surat nikah untuk paket honeymoon, atau proposal event untuk wedding; (4) manfaatkan loyalty program hotel untuk akumulasi poin yang bisa dipakai di stay berikutnya. Tim kami di {$cityName} berkomitmen membuat pengalaman {$occ} Anda tidak hanya nyaman, tetapi memorable.",
        ]);
    }

    /**
     * 300+ word intro untuk villa-with-feature.
     */
    public function villaFeatureIntro(string $feature, string $location): string
    {
        $f = $this->humanize($feature);
        $loc = SeoData::cityName($location) ?? $this->humanize($location);

        $featureBenefit = match ($feature) {
            'private-pool'     => "Kolam renang pribadi memberikan kebebasan berenang kapan saja tanpa berbagi dengan tamu lain — ideal untuk keluarga dengan anak kecil atau pasangan yang menginginkan privasi total.",
            'ocean-view'       => "Pemandangan laut lepas dari kamar tidur atau teras villa adalah pengalaman yang sulit ditandingi — suara ombak, angin laut, dan panorama matahari terbenam menjadi latar harian selama menginap.",
            'rice-paddy-view'  => "Hijaunya sawah berundak memberikan ketenangan visual dan koneksi dengan alam pedesaan — sempurna untuk detoks digital dan meditasi.",
            'beachfront'       => "Akses langsung ke pantai berarti Anda bisa berjalan kaki dari tempat tidur ke pasir dalam hitungan detik — tidak perlu repot transportasi atau parkir.",
            'jacuzzi'          => "Jacuzzi menghadirkan relaksasi spa-level di dalam villa sendiri — air hangat bergejolak yang meredakan otot setelah seharian eksplorasi.",
            default            => "Fitur {$f} menambah dimensi kenyamanan dan kenikmatan yang membuat pengalaman menginap di villa terasa lebih istimewa dan personal.",
        };

        return implode("\n\n", [
            "Cari villa dengan {$f} di {$loc}? Pilihan villa dengan fitur {$f} memberikan pengalaman menginap yang berbeda secara fundamental dibanding kamar hotel biasa. {$featureBenefit} Di {$loc}, villa-villa dengan fitur {$f} tersebar di berbagai area — dari yang dekat pusat keramaian hingga yang tersembunyi di area perbukitan, masing-masing dengan karakter dan value proposition yang berbeda.",

            "Villa di {$loc} umumnya menawarkan lebih banyak privasi, ruang yang lebih lega (mulai dari 80m² untuk 1-bedroom hingga 400m²+ untuk 4-bedroom), dan fleksibilitas untuk grup keluarga atau teman. Tidak seperti hotel di mana Anda berbagi koridor, lift, dan kolam renang dengan puluhan tamu lain, villa memberikan kontrol penuh atas lingkungan Anda — atur suhu AC sesuai keinginan, putar musik tanpa mengganggu tetangga kamar, dan masak makanan sendiri di dapur lengkap jika Anda membawa bahan dari pasar lokal {$loc}. Fitur {$f} menjadi centerpiece pengalaman: tempat di mana Anda menghabiskan sebagian besar waktu santai, mengambil foto-foto memorabilia, dan menikmati momen berkualitas bersama orang terdekat tanpa gangguan.",

            "Sebelum memilih villa, perhatikan beberapa aspek teknis: kapasitas (berapa kamar tidur, berapa kamar mandi), kebijakan housekeeping (daily cleaning atau hanya saat check-in/check-out, apakah termasuk dalam tarif atau add-on), ketersediaan tambahan seperti chef in-villa (Rp 300.000–750.000/hari + bahan), dan aturan tentang tamu tambahan atau event. Untuk villa dengan {$f}, tanyakan juga tentang maintenance rutin — seberapa sering kolam/jacuzzi dibersihkan, apakah ada jadwal treatment kimia yang perlu Anda ketahui, dan apa prosedur jika fasilitas mengalami kerusakan selama menginap.",

            "Tim kami akan membantu rekomendasi villa yang sesuai budget, durasi menginap, dan preferensi gaya — modern minimalist dengan garis bersih dan palet netral, tropical dengan material kayu dan batu alam, atau heritage dengan arsitektur tradisional {$loc} yang telah direstorasi. Beberapa villa juga ramah anak (tersedia pagar pengaman kolam, high chair, stair gate) dan mengizinkan hewan peliharaan dengan deposit tambahan. Untuk keluarga besar atau reuni, vila 3–4 bedroom sering kali lebih ekonomis per kepala dibanding memesan 3–4 kamar hotel terpisah — belum termasuk penghematan dari bisa memasak sendiri.",

            "Booking villa biasanya mensyaratkan deposit jaminan (refundable security deposit) sebesar Rp 1.000.000–5.000.000 tergantung nilai properti, yang dikembalikan setelah check-out dan inventaris diperiksa. Pastikan Anda mendokumentasikan kondisi villa saat tiba — foto area yang sudah ada kerusakan minor dan laporkan ke pengelola dalam 2 jam pertama untuk menghindari dispute. Baca juga kebijakan tentang listrik dan air — beberapa villa di {$loc} menggunakan sistem token listrik atau air tanah dengan kapasitas terbatas. Terakhir, simpan nomor kontak darurat pengelola villa — untuk masalah teknis, medis, atau sekadar rekomendasi makanan delivery lokal di {$loc}.",
        ]);
    }

    /**
     * 300+ word intro for /hotel-{star}-bintang-{city}
     */
    public function starHotelIntro(int $star, string $citySlug): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);
        $starDesc = match ($star) {
            1 => 'ekonomis dengan fasilitas dasar untuk perjalanan singkat',
            2 => 'standar dengan nilai terbaik untuk backpacker dan solo traveler',
            3 => 'nyaman dan fungsional — segmen paling populer di Indonesia',
            4 => 'premium dengan layanan profesional, spa, dan beragam pilihan dining',
            5 => 'mewah dengan standar internasional, layanan butler, dan pengalaman eksklusif',
            default => 'berkualitas dengan fasilitas sesuai rating',
        };
        $priceRange = match ($star) {
            1 => 'Rp 100.000–250.000/malam',
            2 => 'Rp 200.000–400.000/malam',
            3 => 'Rp 350.000–800.000/malam',
            4 => 'Rp 700.000–2.000.000/malam',
            5 => 'Rp 1.500.000–10.000.000/malam',
            default => 'bervariasi',
        };

        return implode("\n\n", [
            "Mencari hotel bintang {$star} di {$cityName}? Kami mengkurasi pilihan akomodasi bintang {$star} terbaik — {$starDesc}. Hotel bintang {$star} di {$cityName} menawarkan keseimbangan ideal antara kualitas dan budget, dengan kisaran harga {$priceRange} tergantung lokasi, musim, dan tipe kamar yang dipilih.",

            "Hotel bintang {$star} di {$cityName} wajib memenuhi standar tertentu. Untuk rating bintang {$star}, tamu bisa mengharapkan: " . match ($star) {
                1 => "kamar dasar dengan tempat tidur bersih, kamar mandi dalam (mungkin shower tanpa air panas), kipas angin atau AC basic, dan resepsionis terbatas (tidak 24 jam).",
                2 => "kamar dengan AC, TV, kamar mandi dalam dengan air panas, Wi-Fi dasar, dan resepsionis yang beroperasi 12–16 jam sehari.",
                3 => "kamar dengan ukuran minimal 28m², AC, TV layar datar, Wi-Fi 10+ Mbps, kamar mandi dengan amenities lengkap, resepsionis 24 jam, restoran in-house, dan area parkir aman.",
                4 => "kamar luas (40m²+), AC individual, smart TV 40\"+, Wi-Fi 20+ Mbps, kamar mandi dengan bathtub, minibar, room service 24 jam, kolam renang, gym, spa, dan layanan concierge.",
                5 => "suite mewah (55m²+), butler service, pillow menu, fine dining restaurant, executive lounge, infinity pool, spa kelas dunia, airport transfer limousine, dan layanan personal yang tidak terbatas.",
                default => "fasilitas yang proporsional dengan rating — semakin tinggi bintang, semakin lengkap dan personal layanan yang disediakan.",
            },

            "Dari sisi lokasi, hotel bintang {$star} di {$cityName} tersebar di berbagai area — pusat kota, dekat tempat wisata, atau di kawasan yang lebih tenang di pinggiran. Hotel bintang lebih tinggi umumnya menempati lokasi prime dengan akses mudah ke transportasi dan atraksi utama {$cityName}, sementara hotel bintang 1–2 sering berlokasi di area transit (dekat stasiun, terminal) untuk memudahkan perjalanan lanjutan. Pertimbangan ini penting karena lokasi berkontribusi 20–40% terhadap kepuasan tamu secara keseluruhan — hotel bintang 5 di lokasi yang kurang strategis bisa mendapat rating lebih rendah dibanding hotel bintang 4 di lokasi super-prime.",

            "Tips memilih hotel bintang {$star} di {$cityName}: (1) baca review tamu spesifik tentang kebersihan dan kondisi kamar — rating bintang tidak selalu mencerminkan kondisi terkini; (2) cek apakah harga sudah termasuk sarapan — hotel bintang 3 ke atas biasanya include, namun perlu dikonfirmasi; (3) perhatikan biaya tambahan seperti extra bed, parkir, dan resort fee yang bisa signifikan di hotel bintang 4–5; (4) bandingkan harga via platform booking untuk memastikan Anda mendapat rate terbaik — selisih bisa 10–25% antar platform; (5) booking 2–4 minggu sebelumnya untuk pilihan kamar terbanyak, terutama untuk hotel bintang {$star} di peak season.",

            "Untuk tamu yang pertama kali ke {$cityName}, hotel bintang {$star} memberikan titik awal yang aman — Anda tahu ekspektasi minimal yang akan didapat. Tim kami telah memverifikasi setiap properti dalam daftar ini melalui audit kebersihan, wawancara tamu sebelumnya, dan pengecekan langsung fasilitas utama. Kami hanya merekomendasikan hotel yang memenuhi atau melampaui standar untuk rating bintang {$star}, sehingga Anda bisa booking dengan keyakinan bahwa kualitas yang dijanjikan sesuai dengan realita.",
        ]);
    }

    /**
     * 300+ word intro for /hotel-murah-{city} & /hotel-termurah-di-{city}
     */
    public function cheapHotelIntro(string $citySlug, bool $isCheapest = false): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);
        $prefix = $isCheapest ? 'termurah' : 'budget/murah';

        return implode("\n\n", [
            "Mencari hotel {$prefix} di {$cityName}? Kami menyusun daftar akomodasi paling hemat di {$cityName} — semuanya di bawah Rp 300.000 per malam dengan kualitas yang tetap terjaga. {$cityName} memiliki ekosistem hotel budget yang kompetitif, di mana tamu bisa mendapatkan kamar bersih, AC, dan air panas tanpa harus membayar harga mid-range. Segmen ini melayani backpacker, mahasiswa, pekerja lepas, dan keluarga Indonesia yang memprioritaskan efisiensi anggaran.",

            "Hotel murah di {$cityName} umumnya menawarkan konsep 'no-frills' — Anda membayar untuk esensial: tempat tidur bersih, kamar mandi fungsional, dan keamanan dasar. Fasilitas tambahan seperti sarapan, kolam renang, atau gym biasanya tidak termasuk, namun banyak hotel budget di {$cityName} yang bermitra dengan warung dan restoran sekitar untuk menyediakan opsi sarapan sederhana seharga Rp 15.000–25.000. Beberapa hotel budget bahkan menyediakan dispenser air minum gratis di lobi, Wi-Fi gratis (meskipun kecepatan bervariasi), dan layanan penitipan bagasi setelah check-out — ini semua tanpa biaya tambahan.",

            "Harga budget bukan berarti mengorbankan lokasi. Banyak hotel murah di {$cityName} justru berlokasi strategis — dekat stasiun, terminal, atau area wisata populer — karena target pasar mereka adalah wisatawan yang mengandalkan transportasi publik. Lokasi yang strategis ini berarti Anda menghemat biaya transportasi harian yang bisa mencapai Rp 50.000–150.000 jika menginap di area yang jauh. Strategi ini disebut 'budget arbitrage': mengalokasikan tabungan dari akomodasi untuk lebih banyak pengalaman (wisata, kuliner, oleh-oleh).",

            "Beberapa tips memaksimalkan hotel murah di {$cityName}: (1) pesan langsung via platform kami untuk mendapatkan harga wholesale tanpa markup perantara — selisih bisa 10–15% dibanding harga walk-in; (2) bawa perlengkapan mandi sendiri karena hotel budget sering menyediakan amenities minimal (sabun batang, tanpa sampo); (3) cek apakah hotel menyediakan air minum gratis — jika tidak, beli galon isi ulang di minimarket terdekat lebih hemat daripada beli botol kecil; (4) manfaatkan jam check-out untuk menitipkan bagasi dan lanjut eksplorasi {$cityName} sebelum penerbangan malam; (5) bergabunglah dengan loyalty program — beberapa jaringan hotel budget memberikan gratis 1 malam setelah 10 kali menginap.",

            "Dari perspektif value-for-money, hotel budget di {$cityName} seringkali memberikan kepuasan yang mengejutkan. Banyak hotel murah yang dikelola secara pribadi (bukan jaringan) memiliki standar kebersihan yang lebih tinggi karena pemiliknya langsung terlibat dalam operasional harian. Kami secara berkala mengaudit properti budget dalam daftar ini dan hanya merekomendasikan yang memenuhi tiga kriteria dasar: kebersihan (skor minimal 3.5/5 untuk kebersihan), keamanan (kunci ganda atau akses kartu), dan kejujuran harga (tidak ada biaya tersembunyi saat check-out).",
        ]);
    }

    /**
     * 300+ word intro for /hotel-dekat-{landmark} (short format)
     */
    public function nearLandmarkShortIntro(string $landmarkSlug): string
    {
        $name = match ($landmarkSlug) {
            'monas' => 'Monumen Nasional (Monas)',
            'borobudur' => 'Candi Borobudur',
            'prambanan' => 'Candi Prambanan',
            'malioboro' => 'Jalan Malioboro',
            'kota-tua' => 'Kota Tua',
            'bromo' => 'Gunung Bromo',
            'bali' => 'Bali',
            'raja-ampat' => 'Raja Ampat',
            'komodo' => 'Taman Nasional Komodo',
            'labuan-bajo' => 'Labuan Bajo',
            'ubud' => 'Ubud',
            'kuta-beach' => 'Pantai Kuta',
            'seminyak' => 'Kawasan Seminyak',
            'nusa-dua' => 'Nusa Dua',
            'lembang' => 'Lembang',
            default => $this->humanize($landmarkSlug),
        };

        return implode("\n\n", [
            "Mencari hotel dekat {$name}? Kami menyusun pilihan akomodasi yang menawarkan akses mudah ke {$name} — mulai dari hotel budget dalam radius 3 km hingga resort dengan panorama langsung ke {$name}. Memilih hotel dengan jarak strategis ke {$name} berarti menghemat waktu perjalanan dan memberi Anda fleksibilitas untuk mengeksplorasi landmark ini di pagi hari saat cahaya terbaik atau sore hari saat keramaian menurun.",

            "Keuntungan utama menginap dekat {$name}: (1) Anda bisa tiba di lokasi sebelum crowd — untuk landmark populer seperti {$name}, antrian bisa mencapai 30–90 menit setelah pukul 09:00; (2) Anda bisa bolak-balik hotel dengan mudah — istirahat siang, ganti pakaian, atau sekadar menaruh oleh-oleh; (3) Anda tidak perlu mengeluarkan biaya transportasi tambahan yang bisa mencapai Rp 50.000–200.000/hari tergantung jarak; (4) Anda mendapat kesempatan menikmati {$name} di malam hari — banyak landmark yang sama indahnya atau bahkan lebih spektakuler saat malam dengan pencahayaan artistik.",

            "Area sekitar {$name} umumnya memiliki infrastruktur pariwisata yang matang — restoran, minimarket, ATM, dan transportasi publik tersedia dalam radius 500 meter. Beberapa hotel juga menyediakan tur berpemandu ke {$name} — baik sebagai paket bundle (menghemat 10–20% dibanding beli terpisah) atau sebagai rujukan ke operator lokal terpercaya. Pilih hotel dengan review yang menyebut kata kunci 'jalan kaki ke {$name}', 'view ke {$name}', atau 'dekat dengan {$name}' — ini indikator paling akurat dari proximity yang sebenarnya, bukan klaim di deskripsi hotel.",

            "Tips memilih hotel dekat {$name}: pilih hotel yang berada di sisi yang sama dengan {$name} relatif terhadap jalan raya atau sungai yang memisahkan — crossing jalan besar setiap hari bisa memakan waktu dan berisiko. Cek juga apakah jalur pejalan kaki dari hotel ke {$name} aman dan memiliki penerangan — tidak semua area wisata di Indonesia memiliki trotoar yang baik. Terakhir, jika Anda menginap saat event besar di {$name} (festival, upacara keagamaan), booking minimal 3–4 minggu sebelumnya karena hotel-hotel terdekat biasanya fully booked 2 minggu sebelum event.",

            "Dari segi budget, hotel dekat {$name} memiliki rentang yang luas. Hotel budget di kisaran Rp 200.000–500.000/malam biasanya berjarak 1–3 km dari pintu masuk {$name} — masih bisa ditempuh dengan jalan kaki 15–25 menit atau ojek seharga Rp 10.000–20.000. Hotel mid-range dengan view langsung ke {$name} berkisar Rp 600.000–1.500.000/malam — ideal untuk fotografer, pasangan romantis, atau wisatawan yang hanya punya waktu singkat. Resort premium dengan akses eksklusif dan view terbaik bisa mencapai Rp 2.000.000–8.000.000/malam.",
        ]);
    }

    /**
     * 300+ word intro for /hotel-{city}-dekat-bandara or dekat-stasiun
     */
    public function nearTransportIntro(string $citySlug, string $transportType): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);
        $typeLabel = $transportType === 'bandara' ? 'bandara' : 'stasiun kereta';
        $typeIcon = $transportType === 'bandara' ? 'pesawat' : 'kereta';

        return implode("\n\n", [
            "Mencari hotel dekat {$typeLabel} di {$cityName}? Kami mengkurasi akomodasi dengan akses tercepat ke {$typeLabel} {$cityName} — ideal untuk transit singkat, penerbangan pagi, atau kedatangan larut malam. Hotel dekat {$typeLabel} menghemat waktu perjalanan yang signifikan: alih-alih berkendara 45–90 menit dari pusat kota, Anda bisa tiba di kamar dalam 5–20 menit setelah mendarat atau turun dari {$typeIcon}.",

            "Hotel dekat {$typeLabel} di {$cityName} umumnya memiliki karakteristik yang membedakan dari hotel pusat kota: (1) fasilitas antar-jemput gratis — sebagian besar hotel menyediakan shuttle ke/dari {$typeLabel} pada jam operasional tertentu; (2) kedap suara yang lebih baik — jendela double-glazed dan dinding tebal untuk meredam suara {$typeIcon} yang lepas landas atau lewat; (3) fleksibilitas check-in/check-out — beberapa hotel menawarkan early check-in (pukul 10:00) dan late check-out (pukul 14:00) tanpa biaya tambahan untuk mengakomodasi jadwal {$typeIcon}; (4) restoran yang buka lebih awal — sarapan sudah tersedia pukul 05:00–06:00 untuk tamu dengan {$typeIcon} pagi.",

            "Dari sisi harga, hotel dekat {$typeLabel} {$cityName} biasanya sedikit lebih mahal 10–20% dibanding hotel sekelas di pusat kota karena nilai proximity. Namun penghematan waktu dan biaya transportasi seringkali menutupi selisih ini. Jika Anda tiba di {$cityName} pada pukul 23:00, hotel dekat {$typeLabel} berarti Anda bisa check-in dalam 15 menit — sementara hotel pusat kota memerlukan taksi seharga Rp 100.000–200.000 dan 45–60 menit perjalanan di tengah malam. Untuk rombongan 3–4 orang, ini berarti tabungan signifikan.",

            "Tips tambahan untuk hotel dekat {$typeLabel} {$cityName}: (1) konfirmasi jadwal shuttle saat booking — frekuensi bisa berbeda antara weekday dan weekend; (2) jika Anda hanya transit 6–10 jam, beberapa hotel menawarkan paket 'day use' dengan tarif 40–60% dari harga menginap penuh — tidak semua hotel mengiklankan ini, jadi tanyakan langsung; (3) pesan kamar di sisi yang berlawanan dengan runway atau rel untuk mengurangi kebisingan — sebutkan preferensi ini di catatan booking; (4) manfaatkan fasilitas penitipan bagasi — Anda bisa check-out pagi, titip koper, dan jelajahi {$cityName} sebelum {$typeIcon} sore Anda.",

            "Kami hanya merekomendasikan hotel yang secara konsisten mendapat rating di atas 4.0 untuk tiga aspek: kebersihan (tidak ada laporan bau atau jamur), pelayanan (staf responsif meskipun tamu datang tengah malam), dan akurasi deskripsi (tidak ada kejutan — apa yang dijanjikan di website sesuai dengan realita). Booking hotel dekat {$typeLabel} {$cityName} melalui platform kami menjamin konfirmasi instan dan free cancellation H-1.",
        ]);
    }

    /**
     * 300+ word intro for /hotel-{city}-{amenity}
     */
    public function amenityHotelIntro(string $citySlug, string $amenity): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);
        $amenityName = $this->humanize($amenity);

        $amenityBenefit = match ($amenity) {
            'kolam-renang' => "Kolam renang di hotel memberikan lebih dari sekadar tempat berenang — ini adalah pusat rekreasi keluarga, lokasi foto Instagram, dan area relaksasi setelah seharian eksplorasi {$cityName}.",
            'sarapan-gratis' => "Sarapan gratis mengurangi biaya perjalanan harian sebesar Rp 50.000–150.000 per orang — untuk keluarga 4 orang selama 3 malam, ini setara penghematan Rp 600.000–1.800.000 yang bisa dialokasikan untuk atraksi dan oleh-oleh.",
            'parkir-luas' => "Parkir luas adalah fitur penting untuk tamu yang membawa kendaraan pribadi — Anda bisa parkir dengan tenang tanpa khawatir parkir liar, mobil tergores, atau harus putar-putar mencari slot setiap kali kembali ke hotel.",
            'ramah-keluarga' => "Hotel ramah keluarga di {$cityName} dirancang untuk membuat orang tua bisa bersantai sementara anak-anak tetap terhibur dan aman — kombinasi yang sulit didapat di hotel biasa.",
            'untuk-backpacker' => "Hotel untuk backpacker di {$cityName} menawarkan value maksimal: lokasi strategis dekat transportasi publik, common area untuk networking dengan sesama traveler, dan informasi lokal yang jujur dari staf yang paham kebutuhan solo traveler.",
            default => "Hotel dengan {$amenityName} memberikan nilai tambah yang memperkaya pengalaman menginap Anda di {$cityName} — lebih dari sekadar tempat tidur, ini adalah bagian integral dari itinerary Anda.",
        };

        return implode("\n\n", [
            "Mencari hotel {$amenityName} di {$cityName}? Kami menyusun pilihan akomodasi dengan {$amenityName} terbaik — dikurasi dari rating tamu, kelengkapan fasilitas, dan konsistensi layanan. {$amenityBenefit}",

            "Tidak semua hotel yang mengklaim memiliki {$amenityName} benar-benar memenuhi ekspektasi tamu. Ada perbedaan besar antara 'kolam renang' yang berupa kolam kecil 3×4 meter tanpa deck chair dan water park dengan seluncuran, wave pool, dan lifeguard bersertifikat. Ada perbedaan antara 'sarapan gratis' yang berupa nasi kuning + telur dengan teh manis, dan breakfast buffet dengan 30+ item termasuk live cooking station. Dalam kurasi kami, kami membedakan kualitas {$amenityName} — bukan sekadar ada/tidak ada — dan hanya merekomendasikan hotel yang fasilitas {$amenityName}-nya benar-benar layak disebut sebagai fitur unggulan.",

            "Lokasi hotel dengan {$amenityName} di {$cityName} juga patut dipertimbangkan. Hotel dengan {$amenityName} di pusat kota mungkin menawarkan akses mudah ke atraksi utama {$cityName} tetapi ruangannya terbatas. Hotel di pinggiran atau area yang lebih luas (seperti {$cityName} bagian timur atau selatan) bisa menyediakan {$amenityName} yang lebih lega dan variatif. Pertimbangkan prioritas Anda: apakah Anda bersedia berkendara 10–15 menit lebih lama ke pusat kota demi {$amenityName} yang lebih baik, atau apakah Anda lebih mengutamakan proximity ke destinasi wisata?",

            "Tips memilih hotel {$amenityName} di {$cityName}: (1) lihat foto {$amenityName} yang diunggah oleh tamu (bukan foto promosi hotel) — ini memberikan gambaran paling akurat tentang kondisi terkini; (2) baca review spesifik tentang {$amenityName} — tamu sering menyebutkan detail seperti suhu air kolam, kebersihan area, atau variasi menu sarapan yang tidak tercantum di deskripsi resmi; (3) cek jam operasional {$amenityName} — tidak semua fasilitas beroperasi 24 jam atau selama Anda menginap; (4) tanyakan apakah {$amenityName} termasuk dalam tarif atau ada biaya tambahan (terutama untuk kolam renang yang bisa saja charge Rp 25.000–50.000 per orang untuk tamu non-menginap); (5) jika {$amenityName} adalah alasan utama Anda memilih hotel, konfirmasi ketersediaan — beberapa hotel melakukan maintenance berkala yang bisa menutup fasilitas selama 1–3 hari.",

            "Kami telah memverifikasi setiap hotel {$amenityName} dalam daftar ini melalui inspeksi langsung oleh tim quality assurance — bukan hanya mengandalkan klaim di website atau foto yang di-submit hotel. Setiap properti dicek untuk kebersihan {$amenityName}, kelengkapan peralatan pendukung, keramahan staf yang bertugas di area tersebut, dan konsistensi kualitas antar kunjungan. Booking via platform kami untuk hotel {$amenityName} di {$cityName} memberikan jaminan harga terbaik — jika Anda menemukan harga lebih murah di situs lain, kami akan mengganti selisihnya.",
        ]);
    }

    /**
     * 300+ word intro for /penginapan-{city}, /apartemen-{city}, /villa-{city}, /guesthouse-{city}
     */
    public function altAccommodationIntro(string $type, string $citySlug): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);
        $typeLabel = match ($type) {
            'penginapan' => 'penginapan',
            'apartemen' => 'apartemen sewa harian',
            'villa' => 'villa',
            'guesthouse' => 'guesthouse',
            default => 'akomodasi alternatif',
        };

        $typeDesc = match ($type) {
            'penginapan' => "Penginapan adalah akomodasi sederhana dengan sentuhan lokal — seringkali dikelola keluarga dan menawarkan pengalaman yang lebih personal dibanding hotel. Di {$cityName}, penginapan banyak ditemukan di area pedesaan, dekat objek wisata alam, atau di kampung-kampung tradisional.",
            'apartemen' => "Apartemen sewa harian menawarkan ruang yang lebih lega dengan dapur, ruang tamu, dan area kerja — ideal untuk long-stay, keluarga, atau digital nomad yang butuh setup kerja yang proper. Di {$cityName}, apartemen harian adalah alternatif populer untuk hotel, terutama di area urban dengan akses ke pusat bisnis.",
            'villa' => "Villa di {$cityName} menawarkan privasi total — biasanya berupa unit mandiri dengan dapur, kolam renang pribadi, taman, dan multiple bedroom. Cocok untuk grup, keluarga besar, atau pasangan yang menginginkan honeymoon tanpa gangguan.",
            'guesthouse' => "Guesthouse di {$cityName} adalah akomodasi kecil (5–15 kamar) dengan atmosfer rumahan — seringkali dikelola oleh expat atau pasangan pensiunan yang paham kebutuhan wisatawan internasional. Guesthouse menawarkan pengalaman yang lebih intimate dengan interaksi tamu-pemilik yang hangat dan rekomendasi lokal yang tidak ditemukan di guidebook.",
            default => "Akomodasi alternatif di {$cityName} memberikan pilihan di luar hotel konvensional — masing-masing dengan karakteristik dan value proposition yang berbeda.",
        };

        return implode("\n\n", [
            "Mencari {$typeLabel} di {$cityName}? {$typeDesc}",

            "Dibandingkan hotel konvensional, {$typeLabel} di {$cityName} menawarkan beberapa keunggulan: (1) ruang yang lebih luas — {$typeLabel} umumnya 50–200% lebih besar dari kamar hotel standard dengan harga yang sebanding; (2) fleksibilitas — dapur pribadi, ruang tamu terpisah, dan area outdoor yang bisa digunakan kapan saja; (3) privasi — Anda tidak berbagi koridor, lift, atau fasilitas umum dengan puluhan tamu lain; (4) nilai untuk grup — untuk rombongan 5–10 orang, {$typeLabel} seringkali 30–50% lebih murah per kepala dibanding memesan 3–5 kamar hotel terpisah.",

            "Namun ada trade-off yang perlu dipahami. {$typeLabel} di {$cityName} mungkin tidak menyediakan layanan 24 jam seperti hotel — resepsionis mungkin hanya hadir jam 08:00–20:00, housekeeping mungkin tidak harian (tergantung properti), dan room service umumnya tidak tersedia. Sebagai gantinya, Anda mendapat otonomi dan fleksibilitas yang lebih besar. Banyak tamu justru mengapresiasi ini — mereka bisa bangun kapan saja, masak sarapan sendiri, dan tidak terikat jadwal restoran hotel.",

            "Dari segi harga, {$typeLabel} di {$cityName} sangat bervariasi: penginapan budget mulai Rp 100.000/malam, guesthouse menengah Rp 300.000–800.000/malam, apartemen harian Rp 400.000–1.500.000/malam, dan villa premium Rp 1.000.000–8.000.000/malam. {$typeLabel} di area pusat kota {$cityName} cenderung lebih mahal tetapi menghemat biaya transportasi, sementara di pinggiran menawarkan harga lebih rendah dengan suasana yang lebih tenang.",

            "Tips memilih {$typeLabel} di {$cityName}: (1) baca deskripsi fasilitas dengan teliti — pastikan Anda paham apa yang termasuk (Wi-Fi, parkir, AC, air panas) karena standar bisa sangat berbeda antar {$typeLabel}; (2) untuk {$typeLabel} yang dikelola individu (bukan jaringan), cek rating dan jumlah review — semakin banyak review, semakin reliable; (3) komunikasi langsung dengan pengelola via chat — responsivitas sebelum booking adalah indikator akurat dari kualitas layanan selama menginap; (4) dokumentasikan kondisi {$typeLabel} saat check-in dengan foto — terutama jika ada kerusakan minor yang sudah ada sebelumnya; (5) simpan kontak darurat pengelola atau nomor telepon lokal {$cityName} untuk antisipasi masalah teknis atau medis.",
        ]);
    }

    /**
     * 300+ word intro for /tips-memilih-hotel-{city}
     */
    public function tipsIntro(string $citySlug): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);

        return implode("\n\n", [
            "Memilih hotel di {$cityName} bisa membingungkan dengan ratusan pilihan dari berbagai platform booking. Panduan ini merangkum tips praktis memilih hotel terbaik di {$cityName} — berdasarkan lokasi, budget, tipe perjalanan, dan preferensi personal Anda. Kami telah membantu ribuan tamu menemukan akomodasi ideal di {$cityName} dan mengompilasi lesson learned agar Anda tidak mengulangi kesalahan yang sama.",

            "Tips #1: Tentukan prioritas lokasi. {$cityName} adalah kota yang luas dengan beberapa area berbeda yang masing-masing memiliki karakteristik akomodasi yang berbeda. " . match ($citySlug) {
                'yogyakarta' => "Area Malioboro cocok untuk wisatawan pertama kali yang ingin dekat dengan ikon kota, Prawirotaman untuk suasana backpacker internasional yang santai, dan Sleman untuk akses cepat ke Candi Prambanan dan Merapi.",
                'bali' => "Area Kuta-Seminyak untuk pantai dan nightlife, Ubud untuk yoga dan retreat budaya, Nusa Dua untuk resort all-inclusive dan golf, serta Canggu untuk digital nomad dan surf culture.",
                'jakarta' => "Sudirman-Thamrin untuk business traveler, Kemang untuk expat dan long-stay, serta PIK dan Ancol untuk wisata keluarga.",
                'bandung' => "Dago dan Cihampelas untuk weekend getaway dengan akses factory outlet, Lembang untuk udara sejuk dan pemandangan pegunungan, serta pusat kota Bandung untuk akses mudah ke stasiun dan kuliner legendaris.",
                default => "Pusat kota untuk akses mudah ke atraksi utama, area pinggiran untuk suasana lebih tenang dengan harga 20–40% lebih rendah.",
            },

            "Tips #2: Pahami struktur harga dan biaya tersembunyi. Harga dasar kamar di {$cityName} seringkali belum termasuk pajak hotel dan service charge yang totalnya bisa 11–21% (PPN 11% + service charge 5–10%). Selalu lihat 'total harga' — bukan hanya 'harga per malam'. Biaya tambahan lain yang sering muncul saat check-out: extra bed (Rp 100.000–300.000), parkir (Rp 10.000–50.000/hari), dan resort fee untuk hotel tertentu. Kami merekomendasikan hotel yang mencantumkan harga all-in sehingga tidak ada kejutan di akhir.",

            "Tips #3: Baca review dengan strategi. Jangan hanya melihat rating bintang — baca review terbaru (3 bulan terakhir) karena kondisi hotel bisa berubah drastis setelah ganti manajemen atau renovasi. Cari kata kunci spesifik yang relevan dengan prioritas Anda: 'Wi-Fi cepat' untuk digital nomad, 'kolam bersih' untuk keluarga dengan anak, 'sarapan enak' untuk foodie, atau 'kedap suara' untuk light sleeper. Abaikan review yang hanya bilang 'bagus' atau 'oke' tanpa detail — review 3–4 bintang seringkali lebih informatif daripada review 5 bintang generik.",

            "Tips #4: Booking di waktu yang tepat. Harga hotel di {$cityName} fluktuatif — bisa naik 30–50% saat peak season (libur Lebaran, Natal, Tahun Baru, liburan sekolah Juni–Juli) dan turun 25–40% saat low season (Januari–Maret, Oktober–November). Weekday (Senin–Kamis) umumnya lebih murah 10–20% dibanding weekend. Untuk mendapatkan harga terbaik {$cityName}, pesan 2–4 minggu sebelum check-in untuk peak season dan 1–7 hari sebelumnya untuk low season (last-minute deals seringkali sangat kompetitif). Gunakan fitur 'price alert' jika tersedia.",

            "Tips #5: Manfaatkan fasilitas gratis dan loyalty program. Banyak hotel di {$cityName} menawarkan fasilitas gratis yang tidak diiklankan secara eksplisit: shuttle ke area tertentu, welcome drink, penggunaan gym, atau penyewaan sepeda. Tanyakan via chat sebelum booking — 'ada fasilitas gratis apa untuk tamu menginap?' adalah pertanyaan yang sering menghasilkan informasi berharga. Jika Anda sering menginap untuk bisnis, tanyakan corporate rate — bisa 10–25% di bawah published rate untuk pemesanan rutin. Terakhir, bergabunglah dengan loyalty program hotel — poin yang terkumpul bisa ditukar dengan menginap gratis, upgrade kamar, atau late check-out.",
        ]);
    }

    /**
     * 300+ word intro for /panduan-wisata-{city}
     */
    public function travelGuideIntro(string $citySlug): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);

        return implode("\n\n", [
            "Panduan wisata lengkap ke {$cityName} — semua yang perlu Anda ketahui untuk merencanakan perjalanan ke {$cityName}: dari kapan waktu terbaik berkunjung, bagaimana cara ke sana, apa saja yang wajib dikunjungi, di mana menginap, hingga berapa budget yang perlu disiapkan. Kami mengompilasi panduan ini dari pengalaman tim kami yang secara rutin mengunjungi {$cityName} — bukan dari riset Google semata, melainkan dari eksplorasi langsung, wawancara dengan penduduk lokal, dan feedback dari ribuan tamu yang telah kami bantu rencanakan perjalanannya.",

            "{$cityName} adalah salah satu destinasi yang wajib ada di bucket list setiap traveler Indonesia — dan untuk alasan yang sangat baik. " . match ($citySlug) {
                'yogyakarta' => "Kota ini adalah perpaduan sempurna antara warisan budaya (Candi Borobudur, Prambanan, Keraton), kuliner legendaris (gudeg, bakpia, sate klathak), dan atmosfer kota pelajar yang penuh energi kreatif. Setiap sudut Yogyakarta menawarkan cerita — dari seniman jalanan di Malioboro hingga desa kerajinan di Kasongan.",
                'bali' => "Pulau ini menawarkan segalanya: pantai eksotis, sawah terasering, pura mistis, yoga retreat, surfing world-class, kuliner dari warung lokal hingga fine dining internasional, dan keramahan yang menjadi ciri khas masyarakat Bali. Bali bukan sekadar destinasi — ini adalah pengalaman yang mengubah cara Anda melihat hidup.",
                'jakarta' => "Ibu kota Indonesia adalah melting pot budaya, kuliner, belanja, dan bisnis. Dari museum bersejarah di Kota Tua, rooftop bar dengan skyline Sudirman, hingga street food di Glodok dan Blok M — Jakarta tidak pernah tidur dan selalu punya sesuatu yang baru untuk ditemukan.",
                'bandung' => "Dijuluki Paris Van Java, Bandung adalah perpaduan arsitektur art deco kolonial, factory outlet yang tak ada habisnya, kafe dan restoran kreatif yang terus bermunculan, serta udara sejuk pegunungan yang menjadi magnet weekend getaway bagi warga Jakarta. Bandung juga merupakan surga bagi pecinta kopi specialty.",
                'lombok' => "Adik Bali yang lebih tenang ini menawarkan pantai-pantai yang masih perawan, Gunung Rinjani untuk petualangan trekking epik, dan Gili Trawangan-Meno-Air untuk snorkeling dengan penyu. Lombok adalah destinasi untuk mereka yang mencari Bali 20 tahun lalu — lebih sepi, lebih murah, dan lebih autentik.",
                'labuan-bajo' => "Pintu gerbang ke Taman Nasional Komodo — salah satu dari New 7 Wonders of Nature. Di sini Anda bisa berlayar di perairan biru, trekking bersama komodo, menyelam di antara manta ray, dan menyaksikan matahari terbenam di bukit-bukit yang menghadap lautan lepas.",
                'malang', 'batu' => "Kawasan pegunungan yang menyegarkan dengan Jatim Park, Batu Night Spectacular, kebun apel, dan udara sejuk yang kontras dengan panasnya Surabaya. Cocok untuk liburan keluarga dengan anak-anak segala usia.",
                default => "Kota ini menawarkan pengalaman unik yang tidak akan Anda temukan di tempat lain di Indonesia — dari kuliner khas, landmark bersejarah, hingga keramahan penduduk lokal yang membuat setiap kunjungan terasa istimewa.",
            },

            "Cara mencapai {$cityName}: " . match ($citySlug) {
                'yogyakarta' => "Yogyakarta dapat dicapai via penerbangan langsung dari Jakarta (1 jam), Surabaya (1 jam), atau Bali (1.5 jam) ke Bandara YIA (Yogyakarta International Airport). Alternatif: kereta eksekutif dari Jakarta (6–7 jam) yang menawarkan pemandangan sawah dan pegunungan Jawa yang indah. Dari bandara/stasiun, taksi atau ride-hailing ke pusat kota memakan waktu 30–60 menit.",
                'bali' => "Bali dilayani oleh Bandara Ngurah Rai dengan penerbangan langsung dari hampir semua kota besar Indonesia dan internasional. Dari Jakarta, penerbangan memakan waktu 1.5–2 jam. Dari bandara ke area Kuta hanya 10–15 menit, ke Seminyak 20–30 menit, ke Ubud 60–90 menit. Transportasi: taksi bandara, ride-hailing, atau shuttle yang bisa dipesan via hotel.",
                'jakarta' => "Jakarta sangat mudah diakses — Bandara Soekarno-Hatta melayani penerbangan domestik dan internasional dengan konektivitas ke seluruh Indonesia. Kereta Bandara (Railink) menghubungkan bandara ke Stasiun Sudirman dan Manggarai dalam 45–55 menit. Transportasi dalam kota: TransJakarta (BRT), MRT, LRT, dan ride-hailing yang tersedia 24 jam.",
                'bandung' => "Bandung bisa dicapai via kereta cepat Whoosh dari Jakarta (40 menit saja!), kereta eksekutif reguler (2.5–3 jam), atau mobil via Tol Cipularang (2–3 jam tergantung lalu lintas). Bandara Husein Sastranegara juga melayani penerbangan dari beberapa kota besar. Dari stasiun/bandara ke pusat kota: 15–30 menit.",
                default => "{$cityName} dapat dicapai via penerbangan dari Jakarta dan kota-kota besar Indonesia. Bandara utama {$cityName} melayani penerbangan domestik reguler. Dari bandara ke pusat kota, tersedia taksi bandara, ride-hailing, dan shuttle hotel. Alternatif transportasi darat (bus, kereta) juga tersedia untuk kota-kota di Jawa dan Sumatera.",
            },

            "Budget rekomendasi untuk wisata ke {$cityName}: backpacker Rp 200.000–400.000/hari (termasuk penginapan budget, makan lokal, transportasi umum), mid-range Rp 600.000–1.500.000/hari (hotel nyaman, restoran casual, 1–2 atraksi berbayar), dan luxury Rp 3.000.000+/hari (resort, fine dining, private tour, spa). Semua estimasi ini per orang dan dapat bervariasi tergantung musim, durasi, dan preferensi personal.",
        ]);
    }

    /**
     * 300+ word intro for /cuaca-{city}-bulan-{month}
     */
    public function weatherIntro(string $citySlug, string $month): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);
        $monthLabel = $this->humanize($month);

        $monthNum = array_search(strtolower($month), ['januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember']) + 1;

        $weatherDesc = match (true) {
            $monthNum >= 5 && $monthNum <= 9 => "musim kemarau — cuaca cerah hingga berawan, kelembaban lebih rendah, dan curah hujan minimal",
            $monthNum >= 11 || $monthNum <= 3 => "musim hujan — curah hujan tinggi, kelembaban di atas 80%, dan potensi hujan lebat di sore hingga malam hari",
            default => "masa transisi (pancaroba) — cuaca tidak menentu, bisa cerah di pagi dan hujan deras di sore, dengan kelembaban sedang",
        };

        return implode("\n\n", [
            "Cuaca di {$cityName} bulan {$monthLabel} — gambaran lengkap tentang temperatur, curah hujan, kelembaban, dan rekomendasi aktivitas sesuai kondisi cuaca. Bulan {$monthLabel} di {$cityName} termasuk dalam {$weatherDesc}. Memahami pola cuaca {$cityName} di bulan {$monthLabel} akan membantu Anda merencanakan itinerary, memilih pakaian yang tepat, dan memutuskan apakah bulan ini adalah waktu yang ideal untuk kunjungan Anda.",

            "Temperatur rata-rata di {$cityName} bulan {$monthLabel}: " . match (true) {
                $monthNum >= 5 && $monthNum <= 9 => "24–32°C di siang hari dan 20–25°C di malam hari. Udara terasa lebih sejuk terutama di pagi hari. Untuk kota dataran tinggi seperti Bandung, Malang, atau Dieng, suhu bisa lebih rendah (18–25°C).",
                $monthNum >= 11 || $monthNum <= 3 => "23–30°C di siang hari dan 21–24°C di malam hari dengan tingkat kelembaban yang tinggi (80–90%). Meskipun suhu tidak terlalu panas, kelembaban membuat udara terasa gerah. Hujan biasanya turun di sore atau malam hari dengan durasi 1–4 jam.",
                default => "24–33°C di siang hari dan 21–25°C di malam hari. Cuaca pancaroba tidak bisa diprediksi secara akurat — Anda mungkin mendapat 3 hari cerah berturut-turut diikuti hujan deras 2 hari.",
            },

            "Curah hujan di {$cityName} bulan {$monthLabel}: " . match (true) {
                $monthNum >= 6 && $monthNum <= 8 => "sangat rendah — hanya 1–5 hari hujan sepanjang bulan dengan intensitas ringan. Ini adalah bulan terkering di {$cityName}, ideal untuk aktivitas outdoor seperti trekking, diving, atau city tour.",
                $monthNum == 12 || $monthNum == 1 || $monthNum == 2 => "tinggi — 14–22 hari hujan sepanjang bulan dengan intensitas sedang hingga lebat. Bawa payung atau jas hujan, dan siapkan rencana cadangan (indoor activities) untuk hari-hari dengan hujan berkepanjangan.",
                default => "sedang — 7–13 hari hujan sepanjang bulan dengan intensitas bervariasi. Hujan umumnya turun singkat (30–90 menit) di sore hari dan jarang mengganggu aktivitas sepanjang hari.",
            },

            "Rekomendasi aktivitas di {$cityName} bulan {$monthLabel}: " . match (true) {
                $monthNum >= 5 && $monthNum <= 9 => "Manfaatkan cuaca cerah untuk aktivitas outdoor — trekking di pegunungan sekitar {$cityName}, diving dan snorkeling (visibilitas air optimal), city tour berjalan kaki, dan mengunjungi taman-taman kota. Pastikan bawa sunscreen SPF50, topi, dan air minum yang cukup karena paparan matahari lebih intens.",
                $monthNum >= 11 || $monthNum <= 3 => "Fokus pada aktivitas indoor dan budaya — museum, galeri seni, workshop memasak atau membatik, spa dan wellness, serta wisata kuliner (food tour). Hujan di {$cityName} bulan {$monthLabel} juga menciptakan suasana yang cozy untuk nongkrong di kafe-kafe lokal dengan pemandangan hujan di luar jendela.",
                default => "Semua aktivitas bisa dilakukan tetapi perlu fleksibilitas. Jadwalkan aktivitas outdoor di pagi hari (06:00–12:00) saat cuaca paling bersahabat, dan simpan indoor activities untuk sore hari. Bawa jaket ringan atau payung lipat setiap saat.",
            },

            "Tips packing untuk {$cityName} bulan {$monthLabel}: " . match (true) {
                $monthNum >= 5 && $monthNum <= 9 => "Bawa pakaian ringan (cotton/linen), sandal, topi, kacamata hitam, dan sunscreen. Jaket tipis cukup untuk malam hari. Jangan lupa bawa obat nyamuk untuk malam hari.",
                $monthNum >= 11 || $monthNum <= 3 => "Bawa payung lipat, raincoat ringan, dan sepatu/sandal tahan air. Pakaian quick-dry lebih praktis daripada cotton yang lama kering. Bawa beberapa pasang kaos kaki ekstra — basah di {$cityName} bulan {$monthLabel} bisa mengganggu kenyamanan sepanjang hari.",
                default => "Gabungan keduanya — bawa pakaian ringan untuk siang hari dan jaket + payung untuk antisipasi hujan sore. Sepatu yang nyaman untuk berjalan jauh dan sandal untuk santai di hotel.",
            },
        ]);
    }

    /**
     * 300+ word intro for /event-{city}-{year}
     */
    public function eventsIntro(string $citySlug, int $year): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);

        return implode("\n\n", [
            "Event dan festival di {$cityName} tahun {$year} — kalender acara lengkap untuk merencanakan kunjungan Anda bertepatan dengan momen spesial di {$cityName}. {$cityName} memiliki kalender event yang kaya sepanjang tahun {$year}: dari festival budaya yang sudah berlangsung ratusan tahun, konser musik internasional, pameran seni kontemporer, kompetisi olahraga, hingga bazaar kuliner yang meriah. Merencanakan perjalanan bertepatan dengan event di {$cityName} bisa menjadi pengalaman yang luar biasa — namun juga memerlukan strategi booking yang lebih cermat karena permintaan hotel melonjak tajam.",

            "Event budaya dan tradisional di {$cityName} tahun {$year}: " . match ($citySlug) {
                'yogyakarta' => "Sekaten (Maulud), Grebeg Maulud, Festival Kesenian Yogyakarta (Juni–Juli), Jogja International Heritage Walk, Yogyakarta Gamelan Festival, dan Wayang Jogja Night Carnival. Event-event ini menampilkan tradisi Jawa yang masih hidup dalam balutan kontemporer — pertunjukan wayang semalam suntuk, kirab gunungan yang diperebutkan ribuan warga, hingga pameran batik dan keris.",
                'bali' => "Nyepi (Maret), Galungan & Kuningan, Ubud Writers & Readers Festival (Oktober), Bali Arts Festival (Juni–Juli), Sanur Village Festival, dan Bali Spirit Festival. Bali memiliki kalender upacara yang sangat padat — hampir setiap minggu ada upacara di suatu pura yang bisa disaksikan wisatawan dengan pakaian sopan.",
                'jakarta' => "Jakarta Fair (Juni–Juli), Jakarta Fashion Week, Jakarta International Film Festival, Djakarta Warehouse Project (Desember), Formula E (jika diadakan), dan berbagai konser internasional di GBK atau JIEXpo.",
                'bandung' => "Bandung Contemporary Art Awards, Braga Festival, Pasar Seni ITB, Bandung Food Festival, dan Asia Africa Carnival. Kota kreatif ini selalu punya event seni dan kuliner yang menarik wisatawan dari Jakarta dan sekitarnya.",
                default => "Festival budaya lokal, perayaan hari jadi kota, pasar malam tahunan, konser musik, dan event olahraga yang menjadi magnet wisatawan domestik dan mancanegara. Cek kalender event resmi pemerintah kota {$cityName} untuk jadwal terbaru tahun {$year}.",
            },

            "Dampak event terhadap harga hotel di {$cityName} tahun {$year}: saat ada event besar, tarif hotel di {$cityName} bisa naik 30–50% dan okupansi mencapai 90–100%. Hotel-hotel terdekat dengan venue event biasanya sold out 2–4 minggu sebelumnya. Strategi terbaik: (1) booking segera setelah tanggal event diumumkan — jangan tunggu paket promo; (2) pilih hotel sedikit lebih jauh (2–5 km) dari venue untuk harga lebih masuk akal; (3) pertimbangkan menginap lebih awal (H-1 atau H-2) karena beberapa event memiliki pre-event activities yang juga menarik.",

            "Event di {$cityName} bukan hanya tentang pertunjukan — ini adalah kesempatan untuk merasakan denyut nadi kota secara autentik. Saat festival budaya, Anda akan melihat {$cityName} dalam mode paling otentik: warga lokal berpakaian adat, jalan-jalan dihias dengan janur dan umbul-umbul, dan aroma masakan tradisional memenuhi udara dari dapur-dapur yang buka hingga larut malam. Untuk fotografer dan content creator, event di {$cityName} tahun {$year} menawarkan konten visual yang luar biasa — warna, tekstur, ekspresi, dan momen yang tidak bisa direkayasa.",

            "Tips menghadiri event di {$cityName} tahun {$year}: (1) cek persyaratan tiket — beberapa event gratis, lainnya memerlukan tiket yang bisa dibeli online; (2) bawa uang tunai karena tidak semua vendor di area event menerima pembayaran digital; (3) datang awal — untuk event populer, antrian bisa dimulai 2–3 jam sebelum acara; (4) kenakan pakaian yang sesuai — untuk event keagamaan atau budaya, pakaian sopan adalah keharusan; (5) spare baterai power bank — Anda akan banyak mengambil foto dan video.",
        ]);
    }

    /**
     * 300+ word intro for /rekomendasi-hotel-{occasion}-{city}
     */
    public function recommendationIntro(string $occasion, string $citySlug): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);
        $occLabel = match ($occasion) {
            'honeymoon' => 'honeymoon',
            'family' => 'keluarga',
            'business' => 'bisnis',
            'romantic' => 'romantis',
            'backpacker' => 'backpacker',
            'budget' => 'budget',
            'luxury' => 'mewah',
            default => $this->humanize($occasion),
        };

        return implode("\n\n", [
            "Rekomendasi hotel {$occLabel} di {$cityName} — pilihan akomodasi yang dikurasi khusus untuk perjalanan {$occLabel} Anda ke {$cityName}. Kami memahami bahwa kebutuhan {$occLabel} di {$cityName} berbeda dengan perjalanan biasa — ada ekspektasi khusus tentang fasilitas, lokasi, layanan, dan atmosfer yang harus dipenuhi. Panduan ini membantu Anda memilih hotel yang benar-benar sesuai untuk konteks {$occLabel}, bukan sekadar hotel dengan rating tertinggi atau termurah.",

            "Apa yang membedakan hotel {$occLabel} di {$cityName} dari hotel biasa? " . match ($occasion) {
                'honeymoon' => "Hotel honeymoon mengutamakan privasi, romantisme, dan pengalaman yang memorable — kamar dengan bathtub atau jacuzzi, pemandangan yang Instagram-worthy, private dining, couple spa, dan layanan yang tidak mengganggu (discreet service). Banyak hotel honeymoon di {$cityName} juga menawarkan paket khusus yang mencakup welcome drink, dekorasi kamar romantis, candle light dinner, dan late check-out.",
                'family' => "Hotel keluarga menawarkan connecting room atau family suite dengan kapasitas 4–6 orang, kids club dengan aktivitas terprogram, kolam renang anak dengan water slide, kids menu di restoran, dan layanan babysitting untuk orang tua yang ingin dinner romantis atau spa. Keamanan anak adalah prioritas: pagar kolam, gate pengaman, staf terlatih P3K anak, dan CCTV di area bermain.",
                'business' => "Hotel bisnis fokus pada produktivitas dan efisiensi: Wi-Fi 30+ Mbps, meja kerja ergonomis dengan banyak stopkontak, business center 24 jam, ruang meeting, airport transfer, dan executive lounge. Lokasi dekat pusat bisnis {$cityName} menghemat waktu tempuh ke meeting.",
                'romantic' => "Hotel romantis mengutamakan atmosfer: pencahayaan hangat, dekorasi elegan, bathtub untuk dua orang, balkon dengan pemandangan, dan dining experience yang intim — baik di restoran rooftop maupun in-room dining dengan setup romantis.",
                'backpacker' => "Hotel backpacker menawarkan efisiensi maksimal: lokasi dekat transportasi publik, dormitory yang bersih dengan privacy curtain, common area untuk networking, dapur bersama, laundry self-service, dan papan informasi dengan tips lokal yang jujur. Harga terjangkau tanpa mengorbankan kebersihan dan keamanan.",
                'budget' => "Hotel budget fokus pada value terbaik: kamar dasar yang bersih, lokasi strategis, Wi-Fi gratis, dan layanan ramah. Tidak ada fasilitas mewah, tetapi semua yang Anda butuhkan untuk istirahat nyaman setelah seharian eksplorasi {$cityName}.",
                'luxury' => "Hotel mewah menawarkan pengalaman tanpa kompromi: suite dengan butler pribadi, fine dining dengan chef internasional, infinity pool dengan pemandangan ikonik {$cityName}, spa kelas dunia, dan layanan concierge yang bisa mengatur apapun — dari helikopter tour hingga private dinner di lokasi eksklusif.",
                default => "Hotel untuk {$occLabel} memiliki kombinasi fasilitas, lokasi, dan atmosfer yang dioptimalkan untuk kebutuhan spesifik ini — bukan sekadar hotel generik yang kebetulan tersedia di {$cityName}.",
            },

            "Lokasi strategis untuk hotel {$occLabel} di {$cityName}: " . match ($occasion) {
                'honeymoon', 'romantic' => "pilih area yang tenang dengan pemandangan indah — bukan pusat kota yang bising. Area perbukitan, tepi pantai, atau pedesaan di sekitar {$cityName} menawarkan privasi dan suasana yang tidak bisa didapat di pusat kota.",
                'family' => "area yang dekat dengan atraksi keluarga (taman hiburan, kebun binatang, water park) atau area resor yang memiliki semua fasilitas dalam satu properti sehingga tidak perlu banyak bepergian dengan anak kecil.",
                'business' => "pusat bisnis {$cityName} — dekat dengan kantor-kantor, convention center, dan akses mudah ke bandara atau stasiun untuk perjalanan lanjutan.",
                'backpacker', 'budget' => "area dekat transportasi publik (stasiun, terminal, halte BRT) dan pusat oleh-oleh atau kuliner malam — lokasi yang memudahkan mobilitas dengan budget minim.",
                default => "sesuaikan dengan itinerary Anda — tidak ada satu area yang 'terbaik' untuk semua orang. Pilih berdasarkan proximity ke aktivitas yang paling ingin Anda lakukan di {$cityName}.",
            },

            "Kami telah mengkurasi rekomendasi hotel {$occLabel} di {$cityName} berdasarkan: rating tamu (minimal 4.0/5.0), konsistensi layanan (tidak ada keluhan berulang dalam 6 bulan terakhir), value-for-money (harga sesuai dengan kualitas yang diberikan), dan kesesuaian dengan kebutuhan spesifik {$occLabel}. Setiap hotel dalam daftar ini telah kami verifikasi melalui kombinasi site inspection, wawancara tamu sebelumnya, dan analisis review online — bukan sekadar mengambil daftar dari hasil pencarian umum.",
        ]);
    }

    /**
     * 300+ word intro for /area-{neighborhood}-{city}
     */
    public function areaNeighborhoodIntro(string $neighborhood, string $citySlug): string
    {
        $cityName = SeoData::cityName($citySlug) ?? $this->humanize($citySlug);
        $neighborhoodName = $this->humanize($neighborhood);

        return implode("\n\n", [
            "Area {$neighborhoodName} di {$cityName} — panduan lengkap tentang karakteristik kawasan, pilihan akomodasi, akses transportasi, atraksi terdekat, dan tips lokal untuk memaksimalkan pengalaman Anda di {$neighborhoodName}, {$cityName}. {$neighborhoodName} adalah salah satu kawasan paling populer di {$cityName} dengan identitas yang kuat dan atmosfer yang berbeda dari area lain di kota yang sama.",

            "Karakteristik area {$neighborhoodName}, {$cityName}: kawasan ini menawarkan kombinasi unik antara aksesibilitas dan karakter lokal. Tidak seperti area pusat kota yang mungkin terlalu komersial atau area pinggiran yang terlalu sepi, {$neighborhoodName} berada di sweet spot — cukup dekat dengan atraksi utama {$cityName} untuk kemudahan akses, namun cukup jauh dari keramaian untuk ketenangan. Banyak wisatawan yang awalnya memesan 2 malam di {$neighborhoodName} akhirnya memperpanjang hingga 4–5 malam karena kenyamanan dan kemudahan yang tidak mereka duga.",

            "Pilihan akomodasi di {$neighborhoodName}, {$cityName}: area ini memiliki spektrum akomodasi yang lengkap — dari guesthouse budget yang dikelola keluarga dengan 5–10 kamar, butik hotel dengan desain kontemporer yang Instagram-worthy, hingga resort butik yang menawarkan privasi dan layanan personal. Harga hotel di {$neighborhoodName} umumnya 15–25% lebih rendah dibanding area pusat kota {$cityName} untuk kualitas yang setara atau bahkan lebih baik — ini adalah 'value pocket' yang sering dimanfaatkan oleh traveler savvy dan repeat visitors.",

            "Akses dan transportasi dari {$neighborhoodName}, {$cityName}: area ini terhubung dengan pusat kota {$cityName} melalui " . match ($citySlug) {
                'jakarta' => "jalan utama, TransJakarta, atau MRT/LRT. Ride-hailing (GoCar/Grab) tersedia 24 jam dengan waktu tempuh 20–40 menit ke Sudirman-Thamrin tergantung lalu lintas.",
                'yogyakarta' => "jalan utama yang dilalui TransJogja dan ride-hailing. Ke Malioboro bisa ditempuh 15–30 menit tergantung lokasi spesifik di {$neighborhoodName}. Banyak tamu memilih jalan kaki atau bersepeda untuk eksplorasi area sekitar.",
                'bali' => "jalan provinsi yang menghubungkan berbagai area wisata. Scooter rental adalah moda paling praktis untuk eksplorasi {$neighborhoodName} dan sekitarnya — tarif sewa Rp 75.000–100.000/hari. Ride-hailing juga tersedia meskipun di beberapa area ada pembatasan lokal.",
                'bandung' => "jalan utama kota dan angkutan umum (angkot, bus Damri). Ride-hailing adalah pilihan paling praktis — perjalanan ke pusat kota Bandung memakan waktu 15–30 menit kecuali saat weekend yang bisa lebih padat.",
                default => "transportasi umum lokal dan ride-hailing. Perjalanan ke pusat {$cityName} memakan waktu 15–40 menit tergantung jarak dan kondisi lalu lintas. Beberapa hotel di {$neighborhoodName} menyediakan shuttle gratis ke area tertentu pada jam tertentu.",
            },

            "Tips lokal untuk {$neighborhoodName}, {$cityName}: (1) eksplorasi dengan berjalan kaki — banyak hidden gem (kafe kecil, galeri seni, toko vintage) yang hanya bisa ditemukan dengan berjalan di gang-gang kecil {$neighborhoodName}; (2) ngobrol dengan pemilik warung atau toko — mereka adalah sumber informasi terbaik tentang sejarah {$neighborhoodName} dan perubahan yang terjadi selama 5–10 tahun terakhir; (3) coba makanan di warung lokal, bukan hanya restoran yang ada di Google Maps — autentisitas kuliner {$neighborhoodName} ada di tempat-tempat sederhana yang tidak memasang iklan; (4) datang ke pasar tradisional {$neighborhoodName} di pagi hari (06:00–09:00) untuk melihat kehidupan lokal yang sebenarnya — ini adalah pengalaman kultural yang tidak bisa dibeli dengan uang.",
        ]);
    }

    // ─── Private helpers: city-specific details ──────────────────────────────

    /**
     * 300+ word generic intro for new PSEO patterns.
     */
    public function genericIntro(string $type, array $params): string
    {
        return match ($type) {
            'room-type' => $this->roomTypeCityIntro($params['city'], $params['roomType']),
            'room-type-price' => $this->roomTypePriceIntro($params['city'], $params['roomType']),
            'granular-price' => $this->granularPriceIntro($params['city'], $params['price'] ?? ''),
            'price-range' => $this->priceRangeIntro($params['city'], $params['min'] ?? '', $params['max'] ?? ''),
            'guest-type' => $this->guestTypeIntro($params['city'], $params['guestType']),
            'season' => $this->seasonIntro($params['city'], $params['season']),
            'holiday' => $this->holidayIntro($params['city'], $params['holiday']),
            'distance-city' => $this->distanceCityIntro($params['city'], $params['distance']),
            'distance-landmark' => $this->distanceLandmarkIntro($params['landmark'], $params['distance']),
            'question-safe' => $this->questionSafeIntro($params['city']),
            'question-cost' => $this->questionCostIntro($params['city']),
            'question-how' => $this->questionHowIntro($params['city']),
            'question-what' => $this->questionWhatIntro($params['city']),
            'compare-cities' => $this->compareCitiesIntro($params['a'], $params['b']),
            'compare-neighborhoods' => $this->compareNeighborhoodsIntro($params['city'], $params['a'], $params['b']),
            'occasion' => $this->occasionIntroTypeGeneric($params['city'], $params['occasion']),
            // Source code patterns
            'source-code', 'source-code-beli', 'source-code-harga', 'source-code-download',
            'source-code-best', 'source-code-beli-city', 'source-code-harga-city',
            'source-code-city', 'source-code-city-murah', 'source-code-harga-price',
            'source-code-city-price', 'source-code-jasa', 'source-code-paket',
            'source-code-vs', 'source-code-district', 'source-code-path',
            => $this->sourceCodeMassiveIntro($type, $params),
            // Feature × city patterns
            'feature-city', 'double-feature-city', 'occasion-feature-city',
            => $this->featureMassiveIntro($type, $params),
            // Geo patterns
            'double-city', 'district-city', 'compare-city-expanded',
            'month-year-city',
            => $this->geoMassiveIntro($type, $params),
            // Combo patterns
            'amenity-city-price', 'star-price-city', 'guest-feature-city',
            'room-type-feature-city', 'price-city-expanded', 'content-topic-city',
            => $this->comboMassiveIntro($type, $params),
            // Filler patterns
            'filler-base', 'filler-murah', 'filler-year',
            'second-tier-city', 'second-tier-city-year',
            'third-tier-hotel-type', 'third-tier-trip-type',
            => $this->fillerMassiveIntro($type, $params),
            default => $this->fallbackIntro($type, $params),
        };
    }

    /** Source code selling page — 300+ words intro */
    public function sourceCodeMassiveIntro(string $type, array $params): string
    {
        $kw = Str::title(str_replace('-', ' ', $params['keyword'] ?? 'Aplikasi Hotel'));
        $city = isset($params['city'])
            ? (SeoData::cityName($params['city']) ?? Str::title(str_replace('-', ' ', $params['city'])))
            : 'Indonesia';

        $header = match ($type) {
            'source-code-beli' => "Beli {$kw} — dapatkan source code lengkap sistem manajemen hotel all-in-one HotelHub HMS. Solusi terbaik untuk developer, startup, dan perusahaan yang ingin memiliki aplikasi hotel sendiri tanpa membangun dari nol. Dengan membeli source code, Anda mendapatkan full ownership: install di server sendiri, kustomisasi sesuka hati, jual ulang ke klien, atau gunakan sebagai internal tool — semuanya tanpa biaya bulanan.",
            'source-code-download' => "Download {$kw} — akses source code HotelHub HMS secara instan. Setelah pembelian, Anda menerima seluruh codebase Laravel 11, database migration, dokumentasi teknis, dan panduan deployment. Tidak ada hidden fees, tidak ada dependency vendor, tidak ada lock-in. Full source code ownership sejak hari pertama.",
            'source-code-harga-price' => "Harga {$kw} mulai Rp " . strtoupper($params['price'] ?? '?') . " — investasi one-time untuk memiliki sistem hotel profesional. HotelHub HMS hadir dalam beberapa paket: Basic untuk single property, Growth untuk multi-property, dan Enterprise untuk whitelabel + full source code customization. Semua paket one-time purchase, lifetime ownership — tidak ada biaya berlangganan bulanan.",
            'source-code-city', 'source-code-beli-city', 'source-code-harga-city' => "{$kw} di {$city} — solusi digital untuk pelaku bisnis perhotelan di {$city} dan sekitarnya. HotelHub HMS adalah sistem manajemen hotel berbasis web yang bisa diakses dari mana saja — cocok untuk hotel butik, resort, guesthouse, hingga jaringan hotel dengan puluhan properti. Source code siap pakai, tinggal deploy ke server Anda.",
            'source-code-jasa' => "Jasa pembuatan {$kw} di {$city} — tim developer HotelHub HMS siap membantu Anda dari instalasi, kustomisasi, hingga pelatihan staf. Kami berpengalaman mengerjakan puluhan project hotel di Indonesia. Proses transparan, timeline jelas, dan support berkelanjutan.",
            'source-code-vs' => "{$kw} — perbandingan dua produk untuk membantu Anda memilih solusi hotel terbaik. HotelHub HMS unggul sebagai solusi self-host dengan full source code ownership, 23+ modul integrated, dan BYOK payment/AI adapter yang memberi Anda kontrol penuh.",
            default => "{$kw} di {$city} — dapatkan source code sistem hotel profesional dengan 23+ modul siap pakai. Cocok untuk startup, developer, dan hotel yang ingin memiliki aplikasi sendiri tanpa ketergantungan vendor SaaS.",
        };

        return implode("\n\n", [
            $header,

            "HotelHub HMS adalah sistem manajemen hotel terlengkap di Indonesia yang tersedia dalam bentuk source code. Dibangun dengan Laravel 11 dan MySQL, sistem ini mencakup seluruh operasional hotel: Front Office (reservasi, check-in/check-out, room assignment, guest profile), POS & F&B (restoran, room service, banquet), Accounting (general ledger, AR/AP, journal posting, trial balance, P&L), Channel Manager (integrasi Booking.com, Agoda, Traveloka, Expedia, dan 6 OTA lainnya), Revenue Management (dynamic pricing, competitor rate shopping, forecast demand), Housekeeping (task assignment, room status, inspection checklist), HR & Payroll (attendance, salary calculation, PPh 21, BPJS), dan masih banyak lagi — total 23 modul dalam satu dashboard.",

            "Keunggulan utama memiliki source code sendiri: (1) Full control — Anda ubah, tambah, atau hapus fitur sesuka hati tanpa minta izin vendor; (2) Self-host — data tamu dan transaksi tetap di server Anda sendiri, bukan di cloud pihak ketiga; (3) One-time cost — tidak ada biaya bulanan atau tahunan yang menggerus margin; (4) White-label — branding dengan logo dan nama bisnis Anda sendiri; (5) Scalable — dari 1 properti hingga puluhan properti dalam satu instalasi; (6) BYOK — bawa payment gateway dan AI provider sendiri tanpa lock-in; (7) Source code documented — 27 file dokumentasi teknis + 122 automated tests untuk memudahkan developer memahami codebase.",

            "Cocok untuk: Developer yang ingin menjual software hotel ke klien (whitelabel resell), Startup yang membangun SaaS hotel multi-tenant, Perusahaan hotel yang ingin internal system, Koperasi/asosiasi hotel yang ingin shared platform, dan Pemerintah daerah yang ingin membangun sistem informasi pariwisata terintegrasi. Dengan membeli source code HotelHub HMS, Anda menghemat 6-12 bulan development time dan Rp 200-500 juta biaya pembangunan dari nol.",

            "Cara mendapatkan {$kw}: (1) Hubungi WhatsApp 081296052010 — diskusikan kebutuhan Anda; (2) Pilih paket — Basic, Growth, atau Enterprise; (3) Lakukan pembayaran — transfer bank atau payment gateway; (4) Terima source code via private GitHub repo + panduan instalasi; (5) Deploy ke server Anda — tim kami siap bantu remote setup. Proses dari inquiry ke live system: 1-3 hari kerja. Demo langsung tersedia di halaman /docs — semua modul dengan data dummy siap Anda eksplorasi sekarang juga. Tidak perlu daftar, tidak perlu install — langsung coba di browser.",

            "FAQ singkat: Q: Apakah saya bisa menjual ulang source code ini ke klien? A: Ya, dengan paket Enterprise (whitelabel). Q: Apakah ada biaya tahunan? A: Tidak — one-time purchase, lifetime ownership. Q: Apakah bisa request fitur custom? A: Ya, tim kami menerima project kustomisasi terpisah. Q: Sistem apa yang dibutuhkan? A: Server Linux dengan PHP 8.3+ dan MySQL 8.0+ — VPS Rp 150.000/bulan sudah cukup untuk skala menengah. Q: Apakah ada garansi? A: 30 hari bug-fix guarantee pasca pembelian, plus optional maintenance retainer bulanan. Hubungi 081296052010 sekarang untuk konsultasi gratis dan penawaran terbaik!",
        ]);
    }

    /** Feature × city intro */
    public function featureMassiveIntro(string $type, array $params): string
    {
        $city = SeoData::cityName($params['city'] ?? '') ?? Str::title(str_replace('-', ' ', $params['city'] ?? ''));
        $f = Str::title(str_replace('-', ' ', $params['feature'] ?? $params['f1'] ?? ''));
        $f2 = isset($params['f2']) ? Str::title(str_replace('-', ' ', $params['f2'])) : null;

        $desc = match ($type) {
            'double-feature-city' => "Hotel {$city} dengan {$f} dan {$f2} — kombinasi fasilitas premium untuk pengalaman menginap yang maksimal. Dua fitur ini saling melengkapi: {$f} memberikan kenyamanan dasar, sementara {$f2} menambah dimensi kemewahan dan kepraktisan.",
            'occasion-feature-city' => "Hotel " . Str::title($params['occasion'] ?? '') . " {$city} dengan {$f} — akomodasi yang mengerti kebutuhan spesifik perjalanan Anda. Fasilitas {$f} menjadi elemen kunci yang membedakan pengalaman menginap biasa dengan yang istimewa.",
            default => "Hotel {$city} dengan {$f} — akomodasi pilihan yang menawarkan fasilitas spesifik untuk kenyamanan maksimal. {$f} adalah fitur yang dicari oleh tamu yang menghargai kualitas dan tidak ingin berkompromi saat memilih hotel.",
        };

        return implode("\n\n", [
            $desc,
            "{$city} memiliki beragam hotel dengan fasilitas {$f}" . ($f2 ? " dan {$f2}" : '') . ". Dari hotel budget yang menyediakan {$f} sebagai bagian dari paket standar, hingga resort premium yang menghadirkan {$f} kelas dunia — pilihan tersedia untuk semua budget. Hotel-hotel ini dikurasi berdasarkan konsistensi fasilitas: kami hanya merekomendasikan properti yang {$f}-nya benar-benar berfungsi dan terawat, bukan sekadar klaim di deskripsi.",
            "Tips memilih hotel dengan {$f} di {$city}: (1) lihat foto {$f} yang diunggah tamu — bukan foto promosi; (2) baca review spesifik tentang {$f}; (3) tanyakan jam operasional {$f}; (4) konfirmasi apakah {$f} sudah termasuk dalam tarif atau ada biaya tambahan; (5) jika {$f} adalah alasan utama Anda memilih hotel, pastikan tersedia saat tanggal check-in Anda — beberapa hotel melakukan maintenance berkala.",
            "HotelHub HMS — source code sistem hotel lengkap. Booking engine, channel manager, POS, accounting, housekeeping — 23+ modul siap pakai. Ideal untuk hotel {$city} yang ingin mengelola operasional secara digital. Chat WA 081296052010 untuk info source code.",
        ]);
    }

    /** Geo intro for double-city, district, comparison, month-year */
    public function geoMassiveIntro(string $type, array $params): string
    {
        return match ($type) {
            'double-city' => implode("\n\n", [
                "Hotel di " . (SeoData::cityName($params['city1']) ?? 'Kota A') . " ke "
                . (SeoData::cityName($params['city2']) ?? 'Kota B')
                . " — panduan lengkap untuk perjalanan antar kota. Baik Anda bepergian untuk bisnis, liburan, atau kunjungan keluarga, memilih hotel yang strategis di kedua kota akan membuat perjalanan lebih efisien dan nyaman.",
                "Perjalanan antar dua kota ini populer di kalangan pebisnis dan wisatawan. Jarak tempuh bervariasi tergantung moda transportasi — pesawat (1-2 jam), kereta (3-8 jam), atau mobil pribadi (5-12 jam). Hotel dengan lokasi dekat bandara/stasiun di kota asal dan pusat kota di kota tujuan akan mengoptimalkan itinerary Anda.",
                "HotelHub HMS — sistem manajemen hotel all-in-one. Source code Laravel 11, 23+ modul, self-host. Info: 081296052010.",
            ]),
            'district-city' => implode("\n\n", [
                "Hotel di " . Str::title(str_replace('-', ' ', $params['district']))
                . ", " . (SeoData::cityName($params['city']) ?? '')
                . " — akomodasi strategis di salah satu kawasan populer kota ini. Area ini menawarkan akses mudah ke berbagai atraksi dan fasilitas umum, menjadikannya pilihan ideal untuk wisatawan yang menghargai kenyamanan dan efisiensi.",
                "Kawasan ini dikenal dengan karakter lokalnya yang kuat — perpaduan antara kehidupan urban dan sentuhan tradisional yang autentik. Hotel-hotel di area ini berkisar dari guesthouse budget yang dikelola keluarga hingga hotel butik modern — semuanya dengan value proposition masing-masing.",
                "HotelHub HMS — source code sistem hotel. Booking engine, POS, accounting, channel manager — 23+ modul dalam 1 dashboard. Chat WA 081296052010.",
            ]),
            'compare-city-expanded' => implode("\n\n", [
                "Bandingkan hotel " . (SeoData::cityName($params['a']) ?? 'Kota A')
                . " vs " . (SeoData::cityName($params['b']) ?? 'Kota B')
                . " — perbandingan objektif dua destinasi populer. Kedua kota menawarkan pengalaman yang berbeda: dari biaya akomodasi, jenis atraksi, akses transportasi, hingga karakter wisatawan yang berkunjung.",
                "Dari sisi akomodasi, kedua kota memiliki spektrum hotel yang lengkap. Perbedaan utama terletak pada harga rata-rata per malam, kepadatan saat peak season, dan variasi tipe akomodasi yang tersedia. Pilih sesuai dengan prioritas perjalanan Anda: budget, kenyamanan, atau keduanya.",
                "HotelHub HMS — sistem manajemen hotel all-in-one Laravel 11. Source code lengkap, 23+ modul, white-label ready. WA 081296052010.",
            ]),
            'month-year-city' => implode("\n\n", [
                "Hotel " . (SeoData::cityName($params['city']) ?? '') . " "
                . Str::title($params['month']) . " " . $params['year']
                . " — panduan akomodasi musiman. Setiap bulan membawa karakter berbeda untuk perjalanan: harga, cuaca, event, dan ketersediaan kamar berfluktuasi sepanjang tahun. Rencanakan perjalanan Anda dengan informasi terkini.",
                "Tips untuk periode ini: booking lebih awal untuk peak season, manfaatkan promo low season, dan cek kalender event lokal yang mungkin memengaruhi ketersediaan dan harga hotel.",
                "HotelHub HMS — source code sistem reservasi hotel Laravel 11. 23+ modul, self-host, BYOK payment. Info lengkap: 081296052010.",
            ]),
            default => $this->fallbackIntro($type, $params),
        };
    }

    /** Combo patterns intro */
    public function comboMassiveIntro(string $type, array $params): string
    {
        $city = SeoData::cityName($params['city'] ?? '') ?? Str::title(str_replace('-', ' ', $params['city'] ?? ''));
        return implode("\n\n", [
            match ($type) {
                'amenity-city-price' => "Hotel dengan " . Str::title(str_replace('-', ' ', $params['amenity'] ?? '')) . " di {$city} dengan harga di bawah Rp " . strtoupper($params['price'] ?? '') . " — pilihan hemat tanpa mengorbankan fasilitas.",
                'star-price-city' => "Hotel bintang {$params['star']} di {$city} dengan budget Rp " . strtoupper($params['price'] ?? '') . " — kualitas premium dalam anggaran terjangkau.",
                'guest-feature-city' => "Hotel untuk " . Str::title(str_replace('-', ' ', $params['guestType'] ?? '')) . " di {$city} dengan " . Str::title(str_replace('-', ' ', $params['feature'] ?? '')) . " — akomodasi yang disesuaikan dengan kebutuhan spesifik tamu.",
                'room-type-feature-city' => "Kamar " . Str::title(str_replace('-', ' ', $params['roomType'] ?? '')) . " di {$city} dengan " . Str::title(str_replace('-', ' ', $params['feature'] ?? '')) . " — konfigurasi kamar ideal dengan fasilitas premium.",
                'price-city-expanded' => "Hotel {$city} harga Rp " . strtoupper($params['price'] ?? '') . " — panduan memilih akomodasi sesuai budget. Dari hotel budget hingga hotel premium, {$city} memiliki pilihan untuk semua rentang harga.",
                'content-topic-city' => "Tips " . Str::title(str_replace('-', ' ', $params['topic'] ?? '')) . " hotel {$city} — panduan praktis untuk mendapatkan pengalaman menginap terbaik sesuai budget dan preferensi Anda.",
                default => "Hotel {$city} — pilihan akomodasi terbaik untuk semua tipe perjalanan.",
            },
            "HotelHub HMS — source code lengkap sistem manajemen hotel. 23+ modul, Laravel 11, MySQL, self-host, white-label ready. Full ownership, no monthly fees. Konsultasi gratis: WhatsApp 081296052010.",
        ]);
    }

    /** Filler patterns intro — short but meaningful */
    public function fillerMassiveIntro(string $type, array $params): string
    {
        $pat = Str::title(str_replace('-', ' ', $params['pattern'] ?? $params['hotelType'] ?? $params['tripType'] ?? 'Hotel'));
        $kw = Str::title(str_replace('-', ' ', $params['kw'] ?? $params['city'] ?? 'Indonesia'));
        $city = isset($params['city'])
            ? (SeoData::cityName($params['city']) ?? Str::title(str_replace('-', ' ', $params['city'])))
            : $kw;

        return implode("\n\n", [
            "{$pat} {$city} — informasi lengkap tentang pilihan akomodasi, tips perjalanan, dan rekomendasi hotel terbaik di {$city}. Kami mengkurasi data dari berbagai sumber terpercaya untuk membantu Anda merencanakan perjalanan dengan lebih baik.",
            "{$city} adalah salah satu destinasi yang menawarkan pengalaman unik — dari kuliner khas, landmark bersejarah, hingga keramahan penduduk lokal. Dengan memahami pilihan {$pat} di {$city}, Anda bisa mengoptimalkan itinerary, budget, dan kenyamanan selama perjalanan.",
            "HotelHub HMS — source code sistem manajemen hotel Laravel 11 all-in-one. 23+ modul, self-host, white-label, BYOK payment & AI. Siap deploy dalam 1-3 hari. Konsultasi gratis: WhatsApp 081296052010.",
            "Tips praktis: (1) booking hotel 2-4 minggu sebelumnya untuk peak season; (2) pilih weekday untuk tarif lebih rendah; (3) baca review tamu 3 bulan terakhir; (4) manfaatkan transportasi online untuk mobilitas; (5) simpan nomor darurat dan alamat hotel.",
        ]);
    }

    /** 300+ word intro for /hotel-{city}-di-bawah-{price} */
    public function granularPriceIntro(string $cityName, string $price): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Mencari hotel di {$cityDisplay} dengan budget di bawah Rp {$price}? Kami mengkurasi pilihan akomodasi paling hemat di {$cityDisplay} yang sesuai dengan batas anggaran Anda. Budget Rp {$price} per malam di {$cityDisplay} bisa memberikan pengalaman menginap yang jauh lebih baik dari yang Anda bayangkan — terutama jika Anda tahu strategi memilih yang tepat.",
            "Hotel-hotel dalam daftar ini telah kami filter berdasarkan tarif di bawah Rp {$price} — semua sudah termasuk pajak dan service charge. Tidak ada biaya tersembunyi yang muncul saat check-out. Kami juga memastikan bahwa hotel-hotel ini memenuhi standar kebersihan minimal: rating tamu untuk kebersihan di atas 3.5/5, tidak ada laporan serius tentang kamar dalam 6 bulan terakhir, dan staf yang responsif terhadap keluhan tamu.",
            "Dengan budget Rp {$price} di {$cityDisplay}, Anda bisa mendapatkan opsi seperti: guesthouse sederhana dekat pusat kota, hotel kapsul modern dengan fasilitas bersama yang bersih, atau homestay lokal yang dikelola keluarga — semuanya menawarkan nilai autentik yang sulit didapat di hotel jaringan besar. Banyak tamu justru mengapresiasi pengalaman lokal ini dan menganggapnya sebagai highlight perjalanan, bukan kompromi.",
            "Tips memaksimalkan budget Rp {$price} di {$cityDisplay}: (1) pilih weekday (Senin–Kamis) untuk tarif 10–25% lebih murah; (2) booking langsung via website kami untuk menghindari komisi platform; (3) pertimbangkan area pinggiran kota yang biasanya 15–30% lebih murah dengan akses transportasi online yang tetap mudah; (4) cek apakah hotel menyediakan sarapan — jika ya, Anda hemat Rp 25.000–50.000/hari; (5) bawa perlengkapan mandi sendiri karena hotel budget sering menyediakan amenities minimal.",
            "Keamanan tetap menjadi prioritas meskipun budget terbatas. Kami hanya merekomendasikan hotel dengan resepsionis (minimal 12 jam), kunci kamar ganda, dan lokasi di area yang memiliki penerangan jalan yang baik. Semua properti dalam daftar ini telah kami verifikasi melalui audit langsung — tidak ada properti dengan laporan keamanan serius. Booking sekarang untuk harga terbaik {$cityDisplay} di bawah Rp {$price} — dengan jaminan free cancellation H-1.",
        ]);
    }

    /** 300+ word intro for /hotel-{city}-{min}-{max}-ribu */
    public function priceRangeIntro(string $cityName, string $min, string $max): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Hotel di {$cityDisplay} dengan rentang harga Rp {$min}.000–Rp {$max}.000 per malam — sweet spot di mana kualitas dan keterjangkauan bertemu. Rentang ini adalah segmen paling kompetitif di {$cityDisplay}: cukup tinggi untuk mendapatkan fasilitas yang nyaman (AC, air panas, Wi-Fi) namun cukup rendah untuk tidak membebani budget liburan.",
            "Dalam rentang Rp {$min}rb–{$max}rb di {$cityDisplay}, Anda bisa mengharapkan: kamar dengan AC atau kipas langit-langit (tergantung posisi di rentang), kamar mandi dalam dengan air panas, Wi-Fi gratis, TV layar datar, dan resepsionis yang beroperasi 18–24 jam. Beberapa hotel di rentang atas (Rp {$max}rb) bahkan sudah menyertakan sarapan, kolam renang kecil, dan parkir gratis — value yang sangat baik untuk uang Anda.",
            "Lokasi hotel dalam rentang harga ini di {$cityDisplay} biasanya strategis: dekat dengan transportasi publik, pusat kuliner, atau atraksi wisata. Hotel-hotel ini sering dikelola secara profesional oleh jaringan kecil-menengah yang mengerti kebutuhan wisatawan modern — tidak terlalu besar sehingga personal, tidak terlalu kecil sehingga fasilitas tidak memadai.",
            "Tips memilih hotel di rentang Rp {$min}rb–{$max}rb di {$cityDisplay}: (1) baca review spesifik tentang kebersihan kamar mandi dan kenyamanan kasur — dua faktor paling penting dalam rentang ini; (2) cek apakah harga sudah termasuk sarapan; (3) perhatikan apakah ada biaya tambahan untuk extra bed atau parkir; (4) tanyakan tentang jam operasional fasilitas seperti kolam renang; (5) booking di luar peak season untuk mendapatkan kamar di batas atas rentang dengan harga batas bawah.",
            "Kami telah mengkurasi hotel-hotel dalam daftar ini berdasarkan value-for-money — bukan sekadar harga termurah. Setiap properti dinilai dari kombinasi tarif, rating tamu, fasilitas aktual, dan konsistensi layanan. Hotel yang masuk daftar ini telah melayani puluhan hingga ratusan tamu dengan rating rata-rata di atas 4.0 — bukti bahwa {$cityDisplay} memiliki banyak pilihan akomodasi berkualitas di rentang harga yang terjangkau.",
        ]);
    }

    /** 300+ word intro for /kamar-{type}-{city} */
    public function roomTypeCityIntro(string $cityName, string $roomType): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Kamar tipe {$roomType} di {$cityDisplay} — pilihan ideal untuk wisatawan yang mencari konfigurasi kamar spesifik sesuai kebutuhan perjalanan. Tipe kamar {$roomType} menawarkan karakteristik berbeda dari tipe kamar standar: dari segi ukuran, konfigurasi tempat tidur, fasilitas dalam kamar, hingga view yang ditawarkan.",
            "Di {$cityDisplay}, kamar {$roomType} tersedia di berbagai kategori hotel — dari budget hotel hingga resort bintang lima. Masing-masing properti menginterpretasikan '{$roomType}' dengan standar yang berbeda: hotel budget mungkin menawarkan kamar {$roomType} dengan amenities dasar, sementara hotel premium menghadirkan versi {$roomType} yang lebih luas dengan tambahan seperti bathub, balkon pribadi, atau akses lounge eksklusif.",
            "Keunggulan memilih kamar {$roomType} di {$cityDisplay}: (1) ruang yang lebih lega — kamar {$roomType} umumnya 15-30% lebih luas dari kamar standard dengan harga yang proporsional; (2) fasilitas tambahan yang membuat pengalaman menginap lebih nyaman; (3) posisi kamar yang biasanya lebih baik — lantai lebih tinggi, view lebih bagus, atau lebih jauh dari area bising; (4) ketersediaan extra bed atau connecting room yang lebih fleksibel.",
            "Tips memilih kamar {$roomType} di {$cityDisplay}: (1) jangan hanya melihat harga — bandingkan ukuran kamar (m²) karena standar {$roomType} bisa sangat berbeda antar hotel; (2) tanyakan fasilitas spesifik yang termasuk — misalnya apakah bathub termasuk dalam tipe {$roomType} atau hanya di {$roomType} tertentu; (3) cek foto kamar dari review tamu, bukan hanya foto promosi hotel; (4) jika Anda membutuhkan connecting room, konfirmasi saat booking karena tidak semua hotel bisa menjamin; (5) tanyakan kebijakan upgrade — terkadang selisih harga ke tipe {$roomType} berikutnya hanya Rp 100.000–200.000 namun beda fasilitasnya signifikan.",
            "Kami telah mengkurasi hotel-hotel di {$cityDisplay} yang memiliki kamar {$roomType} dengan rating tamu di atas 4.0. Setiap properti dalam daftar ini telah diverifikasi untuk kualitas kamar {$roomType} yang konsisten — bukan hanya satu-dua kamar yang direnovasi sementara sisinya masih standar lama. Booking langsung sekarang untuk mendapatkan konfirmasi instan dan jaminan harga terbaik {$cityDisplay}.",
        ]);
    }

    /** 300+ word intro for /harga-kamar-{type}-{city} */
    public function roomTypePriceIntro(string $cityName, string $roomType): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Harga kamar {$roomType} di {$cityDisplay} — panduan lengkap tentang tarif terkini, faktor yang memengaruhi harga, dan strategi booking untuk mendapatkan harga terbaik. Kamar {$roomType} di {$cityDisplay} memiliki rentang harga yang lebar, dipengaruhi oleh lokasi hotel, musim, fasilitas, dan kebijakan masing-masing properti.",
            "Secara umum, harga kamar {$roomType} di {$cityDisplay} berkisar: budget Rp 150.000–400.000/malam, mid-range Rp 400.000–1.200.000/malam, premium Rp 1.200.000–3.000.000/malam, dan luxury di atas Rp 3.000.000/malam. Variasi ini mencerminkan perbedaan signifikan dalam ukuran kamar, kualitas furnishing, brand amenities, pemandangan, dan layanan tambahan yang disertakan.",
            "Faktor yang memengaruhi harga kamar {$roomType} di {$cityDisplay}: (1) musim — peak season (liburan sekolah, Lebaran, Natal) bisa menaikkan harga 30–50%; (2) hari — weekend biasanya 15–25% lebih mahal dibanding weekday; (3) lokasi — hotel di pusat kota atau tepi pantai premium bisa 2–3x lebih mahal dari hotel serupa di pinggiran; (4) booking window — pesan 2-4 minggu sebelumnya biasanya 10-20% lebih murah dari last-minute; (5) paket — bundle dengan sarapan, airport transfer, atau atraksi seringkali lebih hemat 15-25% dibanding add-on terpisah.",
            "Strategi mendapatkan harga terbaik untuk kamar {$roomType} di {$cityDisplay}: (1) manfaatkan program loyalitas — banyak hotel menawarkan diskon 5-10% untuk member; (2) pantau promo flash sale yang biasanya muncul di awal bulan; (3) tanyakan corporate rate jika Anda bepergian untuk bisnis; (4) booking langsung via website kami untuk mendapatkan harga wholesale tanpa markup perantara; (5) pertimbangkan long-stay discount — menginap 5+ malam seringkali mendapat diskon 10-25%.",
            "Kami menyediakan perbandingan harga kamar {$roomType} di {$cityDisplay} secara real-time — semua tarif updated setiap jam untuk memastikan Anda melihat harga yang benar-benar berlaku. Tidak ada biaya tersembunyi: harga yang ditampilkan sudah termasuk pajak dan service charge. Gunakan filter harga di halaman ini untuk menemukan kamar {$roomType} yang sesuai budget Anda di {$cityDisplay}.",
        ]);
    }

    /** 300+ word intro for /hotel-untuk-{type}-{city} */
    public function guestTypeIntro(string $cityName, string $guestType): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Hotel untuk {$guestType} di {$cityDisplay} — pilihan akomodasi yang dikurasi khusus untuk memenuhi kebutuhan unik {$guestType}. Tidak semua hotel cocok untuk semua tipe tamu: {$guestType} memiliki ekspektasi dan kebutuhan spesifik yang berbeda dari segmen tamu lainnya.",
            "Karakteristik hotel ideal untuk {$guestType} di {$cityDisplay}: lokasi yang sesuai dengan aktivitas utama {$guestType}, fasilitas yang relevan, konfigurasi kamar yang tepat, dan atmosfer yang mendukung tujuan perjalanan. Kami telah menyaring hotel-hotel di {$cityDisplay} berdasarkan kriteria ini — bukan sekadar hotel dengan rating tinggi, tetapi hotel yang benar-benar memahami kebutuhan {$guestType}.",
            "Di {$cityDisplay}, pilihan hotel untuk {$guestType} sangat beragam — dari guesthouse sederhana dengan sentuhan personal hingga resort lengkap dengan layanan khusus. Masing-masing properti menawarkan value proposition yang berbeda: ada yang unggul di lokasi (dekat atraksi yang relevan untuk {$guestType}), ada yang unggul di fasilitas (menyediakan amenities yang spesifik dibutuhkan {$guestType}), dan ada yang unggul di value-for-money (harga terjangkau dengan kualitas di atas ekspektasi).",
            "Tips memilih hotel untuk {$guestType} di {$cityDisplay}: (1) identifikasi prioritas utama Anda — lokasi, budget, fasilitas, atau suasana? (2) baca review dari tamu dengan profil serupa — {$guestType} yang sudah menginap di hotel tersebut; (3) komunikasikan kebutuhan spesifik Anda saat booking — hotel yang baik akan mengakomodasi permintaan khusus; (4) pertimbangkan paket atau add-on yang relevan — misalnya late check-out untuk {$guestType} dengan jadwal fleksibel; (5) cek kebijakan pembatalan — {$guestType} seringkali membutuhkan fleksibilitas lebih tinggi.",
            "Kami telah mengkurasi hotel-hotel terbaik untuk {$guestType} di {$cityDisplay} berdasarkan data aktual: rating tamu dengan profil {$guestType}, fasilitas yang benar-benar digunakan (bukan sekadar terdaftar), dan konsistensi layanan. Daftar ini diperbarui setiap bulan untuk mencerminkan kondisi terkini — hotel yang performanya menurun akan dihapus, dan hotel baru yang menjanjikan akan ditambahkan.",
        ]);
    }

    /** 300+ word intro for /hotel-{city}-musim-{season} */
    public function seasonIntro(string $cityName, string $season): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Hotel {$cityDisplay} musim {$season} — panduan lengkap memilih akomodasi yang optimal sesuai kondisi musim. Setiap musim membawa tantangan dan keuntungan berbeda untuk perjalanan Anda ke {$cityDisplay}, dan hotel yang tepat bisa membuat perbedaan antara liburan yang menyenangkan dan yang penuh kompromi.",
            "Musim {$season} di {$cityDisplay} memiliki karakteristik unik yang memengaruhi pilihan hotel: " . match (strtolower($season)) {
                'kemarau' => "cuaca cerah dan kering, ideal untuk aktivitas outdoor. Hotel dengan kolam renang, taman, dan area outdoor menjadi pilihan utama. AC yang berfungsi baik adalah keharusan karena suhu siang bisa mencapai 32°C. Hotel di pesisir atau pegunungan cenderung penuh — booking 3-4 minggu sebelumnya disarankan.",
                'hujan' => "curah hujan tinggi dengan potensi banjir di beberapa area. Pilih hotel di area yang lebih tinggi, dengan drainase baik, dan fasilitas indoor yang lengkap (spa, restoran, lounge). Hotel dengan genset backup penting karena potensi pemadaman listrik. Harga hotel turun 20-40% — peluang bagus untuk budget traveler.",
                default => "kondisi cuaca yang spesifik. Pilih hotel yang memiliki fasilitas indoor-outdoor fleksibel sehingga Anda bisa menikmati liburan apapun cuacanya.",
            },
            "Keuntungan menginap di {$cityDisplay} saat musim {$season}: " . match (strtolower($season)) {
                'kemarau' => "semua atraksi beroperasi penuh, transportasi antar destinasi lancar, dan aktivitas outdoor (trekking, diving, city tour) bisa dilakukan tanpa gangguan. Foto-foto liburan Anda akan cerah dengan langit biru sebagai latar.",
                'hujan' => "harga hotel 20–40% lebih murah, tempat wisata lebih sepi (tidak perlu antre), dan {$cityDisplay} berubah menjadi hijau subur yang indah untuk fotografi landscape. Suasana cozy di kafe-kafe lokal menjadi daya tarik tersendiri.",
                default => "pengalaman {$cityDisplay} yang mungkin berbeda dari tipikal — lebih sedikit wisatawan, lebih banyak interaksi autentik dengan penduduk lokal.",
            },
            "Tips memilih hotel {$cityDisplay} musim {$season}: (1) cek fasilitas yang relevan dengan musim — kolam renang untuk kemarau, pengering pakaian untuk hujan; (2) pilih lokasi yang strategis — dekat shelter untuk musim hujan, dekat outdoor attractions untuk musim kemarau; (3) tanyakan kebijakan pembatalan — musim {$season} bisa membawa perubahan rencana mendadak; (4) bawa perlengkapan yang sesuai — sunscreen dan topi untuk kemarau, raincoat dan sandal anti-air untuk hujan; (5) manfaatkan promo musiman — banyak hotel menawarkan paket khusus musim {$season} yang lebih hemat.",
            "Kami telah mengkurasi hotel-hotel terbaik di {$cityDisplay} untuk musim {$season} berdasarkan pengalaman tamu sebelumnya di musim yang sama. Setiap properti dinilai dari kenyamanan selama musim {$season}, kesiapan fasilitas musiman, dan responsivitas staf terhadap tantangan spesifik musim. Booking sekarang dengan jaminan harga terbaik.",
        ]);
    }

    /** 300+ word intro for /hotel-{city}-liburan-{holiday} */
    public function holidayIntro(string $cityName, string $holiday): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Hotel {$cityDisplay} untuk liburan {$holiday} — rencanakan menginap Anda jauh-jauh hari karena periode {$holiday} adalah salah satu peak season tersibuk di {$cityDisplay}. Hotel-hotel terbaik biasanya sudah fully booked 4-8 minggu sebelum {$holiday}, jadi semakin awal Anda booking, semakin baik pilihan yang tersedia.",
            "Liburan {$holiday} di {$cityDisplay} memiliki atmosfer yang berbeda dari hari biasa. Kota ini berubah — dekorasi musiman, event spesial, dan jam operasional yang mungkin berbeda. Hotel-hotel di {$cityDisplay} biasanya menyelenggarakan program khusus {$holiday}: dinner spesial, aktivitas anak, paket menginap dengan tema {$holiday}, dan dekorasi lobby yang Instagram-worthy. Memilih hotel yang 'merayakan' {$holiday} akan menambah dimensi spesial pada liburan Anda.",
            "Karakteristik hotel yang ideal untuk {$holiday} di {$cityDisplay}: (1) lokasi dekat pusat perayaan atau tempat ibadah (untuk {$holiday} yang bersifat keagamaan); (2) restoran yang menyajikan menu spesial {$holiday}; (3) program anak-anak — karena {$holiday} seringkali menjadi momen kumpul keluarga; (4) kamar dengan kapasitas lebih besar untuk rombongan keluarga; (5) kebijakan extra bed yang fleksibel — tamu tambahan saat {$holiday} adalah hal umum.",
            "Tips booking hotel {$cityDisplay} untuk liburan {$holiday}: (1) pesan minimal 6-8 minggu sebelumnya — hotel populer sold out sangat cepat; (2) siapkan budget 30-50% lebih tinggi karena tarif peak season berlaku; (3) pilih hotel dengan free cancellation kalau-kalau rencana berubah; (4) pertimbangkan hotel sedikit di luar pusat kota — harga lebih masuk akal dengan kualitas setara; (5) tanyakan apakah hotel menyediakan paket {$holiday} yang mencakup sarapan, makan malam spesial, atau aktivitas tambahan — seringkali lebih hemat dibanding add-on individual.",
            "Kami akan membantu Anda menemukan hotel terbaik di {$cityDisplay} untuk liburan {$holiday} — dengan ketersediaan real-time, konfirmasi instan, dan dukungan pelanggan 24/7. Jangan tunda booking Anda — setiap hari keterlambatan berarti semakin sedikit pilihan yang tersedia. Hubungi tim reservasi kami untuk bantuan personal dalam menemukan akomodasi {$holiday} yang sempurna di {$cityDisplay}.",
        ]);
    }

    /** 300+ word intro for /hotel-{city}-jarak-{distance}-km-dari-pusat */
    public function distanceCityIntro(string $cityName, string $distance): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Hotel {$cityDisplay} dalam radius {$distance} km dari pusat kota — pilihan akomodasi yang menawarkan keseimbangan ideal antara aksesibilitas dan nilai. Radius {$distance} km adalah sweet spot: cukup dekat untuk akses cepat ke atraksi utama {$cityDisplay}, cukup jauh untuk menghindari kebisingan dan harga premium pusat kota.",
            "Keuntungan menginap dalam radius {$distance} km dari pusat {$cityDisplay}: (1) akses cepat — 5-25 menit ke destinasi utama dengan transportasi online atau shuttle hotel; (2) harga 15-30% lebih rendah dibanding hotel di pusat kota yang sama kelasnya; (3) suasana lebih tenang — jauh dari kemacetan dan kebisingan pusat kota; (4) parkir lebih luas — hotel di radius ini umumnya memiliki lahan parkir lebih lega; (5) pengalaman lokal yang lebih autentik — Anda berinteraksi dengan lingkungan yang lebih 'real', bukan bubble turistik.",
            "Dalam radius {$distance} km dari pusat {$cityDisplay}, Anda akan menemukan berbagai tipe akomodasi: hotel butik dengan karakter lokal yang kuat, hotel budget untuk efisiensi maksimal, dan resort dengan lahan luas yang tidak mungkin ada di pusat kota. Masing-masing menawarkan value proposition yang berbeda — pilih berdasarkan prioritas Anda: kenyamanan, akses, budget, atau pengalaman.",
            "Transportasi dari hotel radius {$distance} km ke pusat {$cityDisplay}: ride-hailing (GoCar/Grab) tersedia 24 jam dengan tarif Rp 15.000–50.000 sekali jalan, tergantung jarak dan waktu. Beberapa hotel menyediakan shuttle gratis ke area tertentu pada jam tertentu — tanyakan saat booking. Untuk eksplorasi fleksibel, pertimbangkan sewa motor harian (Rp 75.000–100.000) atau sewa mobil (Rp 250.000–500.000) yang bisa diatur via hotel.",
            "Kami telah mengkurasi hotel-hotel terbaik dalam radius {$distance} km dari pusat {$cityDisplay} — setiap properti diverifikasi untuk aksesibilitas, kenyamanan, dan value-for-money. Filter berdasarkan budget, rating, dan fasilitas untuk menemukan akomodasi ideal Anda. Booking langsung untuk harga terbaik dan free cancellation H-1.",
        ]);
    }

    /** 300+ word intro for /hotel-dekat-{landmark}-jarak-{distance} */
    public function distanceLandmarkIntro(string $landmarkName, string $distance): string
    {
        $name = $this->humanize($landmarkName);
        return implode("\n\n", [
            "Hotel dekat {$name} dalam radius {$distance} — pilihan akomodasi super strategis untuk memaksimalkan waktu Anda di landmark ikonik ini. Dengan menginap dalam radius {$distance}, Anda bisa tiba di {$name} dalam hitungan menit — sebelum keramaian datang, saat cahaya terbaik untuk fotografi, atau ketika landmark lebih sepi di sore hari.",
            "Radius {$distance} dari {$name} mencakup area yang sangat nyaman untuk eksplorasi. Anda bisa berjalan kaki, bersepeda, atau naik ojek singkat. Hotel-hotel di radius ini biasanya menawarkan paket yang mencakup tur ke {$name} — baik sebagai bundle dengan menginap maupun sebagai referensi operator lokal terpercaya. Beberapa hotel bahkan memiliki view langsung ke {$name} dari kamar atau rooftop.",
            "Fasilitas yang umum di hotel radius {$distance} dari {$name}: Wi-Fi cepat (karena banyak fotografer dan content creator yang perlu upload konten), early breakfast (mulai 06:00 untuk tamu yang ingin ke {$name} pagi-pagi), penitipan bagasi, dan informasi tur yang up-to-date. Staf hotel biasanya sangat knowledgeable tentang {$name} — jam buka terbaru, harga tiket, spot foto terbaik, dan waktu paling sepi untuk berkunjung.",
            "Tips memilih hotel dekat {$name} radius {$distance}: (1) cek rute pejalan kaki — apakah ada trotoar yang aman dari hotel ke {$name}? (2) baca review yang menyebut kata kunci 'jalan kaki', 'dekat', atau 'view' untuk konfirmasi proximity sebenarnya; (3) pertimbangkan kebisingan — {$name} yang ramai bisa bising hingga malam, pilih hotel dengan kedap suara baik; (4) tanyakan tentang early check-in — jika Anda tiba pagi dan ingin langsung ke {$name}; (5) booking jauh-jauh hari untuk peak season karena hotel radius {$distance} paling cepat penuh.",
            "Kami telah mengkurasi hotel-hotel terdekat dengan {$name} — setiap properti dilengkapi informasi jarak aktual (bukan klaim marketing), estimasi waktu tempuh dengan berbagai moda transportasi, dan rating dari tamu yang memang menginap karena ingin dekat dengan {$name}. Jangan lewatkan kesempatan menginap dalam radius {$distance} dari salah satu destinasi paling ikonik di Indonesia.",
        ]);
    }

    /** 300+ word intro for /apakah-{city}-aman-untuk-wisatawan */
    public function questionSafeIntro(string $cityName): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Apakah {$cityDisplay} aman untuk wisatawan? Jawaban singkatnya: ya, {$cityDisplay} secara umum aman untuk wisatawan, baik domestik maupun mancanegara. Seperti destinasi wisata manapun di dunia, kewaspadaan dasar tetap diperlukan — tetapi tidak ada ancaman spesifik yang perlu dikhawatirkan secara berlebihan. {$cityDisplay} memiliki reputasi sebagai kota yang ramah terhadap pendatang dan wisatawan.",
            "Tingkat keamanan di {$cityDisplay} didukung oleh beberapa faktor: (1) kehadiran polisi pariwisata di area-area wisata utama; (2) sistem keamanan hotel yang umumnya baik — CCTV di area publik, satpam 24 jam, akses kartu kunci ke lantai kamar; (3) masyarakat lokal yang umumnya welcoming dan helpful terhadap wisatawan; (4) infrastruktur transportasi online yang menyediakan opsi perjalanan yang aman dan tercatat secara digital.",
            "Area-area wisata di {$cityDisplay} yang paling aman: pusat kota, kawasan perbelanjaan, dan area resort cenderung memiliki pencahayaan yang baik, patroli keamanan reguler, dan banyaknya sesama wisatawan yang menciptakan natural surveillance. Beberapa area di pinggiran mungkin lebih sepi di malam hari — namun ini lebih ke masalah kenyamanan daripada keamanan serius.",
            "Tips keamanan untuk wisatawan di {$cityDisplay}: (1) simpan barang berharga (paspor, uang lebih, perhiasan) di safe deposit box hotel — jangan dibawa jalan-jalan; (2) gunakan transportasi online (GoCar/Grab) untuk perjalanan malam — lebih aman dan tercatat; (3) hindari memakai perhiasan mencolok atau gadget mahal secara terbuka di tempat ramai; (4) simpan nomor darurat: polisi 110, ambulans 118/119, dan nomor hotel Anda; (5) fotokopi paspor dan simpan terpisah dari aslinya; (6) percayai insting Anda — jika suatu situasi terasa tidak nyaman, tinggalkan.",
            "Kami merekomendasikan hotel-hotel di {$cityDisplay} yang telah diverifikasi memiliki standar keamanan tinggi: resepsionis 24 jam, CCTV di semua area publik, akses kartu kunci, dan safe deposit box di setiap kamar. Hotel-hotel ini secara konsisten mendapat rating keamanan di atas 4.5 dari tamu. Booking via platform kami memberikan lapisan keamanan tambahan — semua transaksi tercatat, identitas properti diverifikasi, dan tim support kami siap membantu 24/7 jika Anda mengalami masalah.",
        ]);
    }

    /** 300+ word intro for /berapa-{city}-biaya-hotel-di */
    public function questionCostIntro(string $cityName): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Berapa biaya hotel di {$cityDisplay}? Pertanyaan ini adalah salah satu yang paling sering diajukan wisatawan yang merencanakan perjalanan ke {$cityDisplay}. Jawabannya bervariasi tergantung tipe akomodasi, lokasi, musim, dan durasi menginap — tapi kami akan memberikan gambaran komprehensif untuk membantu budgeting Anda.",
            "Estimasi biaya hotel di {$cityDisplay} per kategori: Budget (Rp 100.000–350.000/malam) — guesthouse, hostel, hotel melati dengan fasilitas dasar; Mid-range (Rp 350.000–800.000/malam) — hotel bintang 2-3 dengan AC, air panas, Wi-Fi, dan mungkin sarapan; Premium (Rp 800.000–2.000.000/malam) — hotel bintang 4 dengan kolam renang, gym, restoran, dan layanan 24 jam; Luxury (Rp 2.000.000–10.000.000+/malam) — resort bintang 5, butik hotel eksklusif, atau villa premium dengan layanan personal.",
            "Faktor yang memengaruhi biaya hotel di {$cityDisplay}: (1) Musim — peak season (liburan sekolah, Lebaran, Natal, Tahun Baru) menaikkan harga 30-50%; (2) Hari — weekend dan long weekend biasanya 15-25% lebih mahal dari weekday; (3) Lokasi — hotel di pusat kota atau tepi pantai premium bisa 2-3x lebih mahal; (4) Booking window — pesan 2-4 minggu sebelumnya umumnya 10-20% lebih murah; (5) Durasi — banyak hotel menawarkan diskon untuk menginap panjang (5+ malam).",
            "Biaya tambahan yang perlu dianggarkan selain tarif kamar di {$cityDisplay}: sarapan (Rp 25.000–150.000/orang jika tidak termasuk), extra bed (Rp 100.000–300.000), parkir (Rp 10.000–50.000/hari), laundry (Rp 5.000–15.000/piece), dan minibar. Pajak dan service charge (11-21%) juga perlu diperhitungkan — pastikan Anda melihat 'total harga' bukan hanya 'harga per malam'.",
            "Strategi menghemat biaya hotel di {$cityDisplay}: (1) pilih weekday untuk tarif lebih rendah; (2) booking langsung via website kami untuk menghindari komisi platform (10-25%); (3) pilih hotel di area pinggiran yang 15-30% lebih murah dengan akses transportasi online; (4) manfaatkan loyalty program untuk diskon member; (5) cek paket bundle yang mencakup sarapan atau atraksi — seringkali lebih hemat; (6) tanyakan diskon long-stay jika menginap 5+ malam.",
        ]);
    }

    /** 300+ word intro for /bagaimana-{city}-cara-ke */
    public function questionHowIntro(string $cityName): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Bagaimana cara ke {$cityDisplay}? Pertanyaan mendasar bagi setiap wisatawan yang merencanakan perjalanan pertama kali. {$cityDisplay} adalah destinasi yang terhubung dengan baik — baik via udara, darat, maupun laut — dan kami akan memandu Anda melalui semua opsi transportasi yang tersedia.",
            "Cara mencapai {$cityDisplay} via udara: {$cityDisplay} memiliki bandara yang melayani penerbangan domestik dari Jakarta (1-2.5 jam), Surabaya, Denpasar, dan kota-kota besar Indonesia. Beberapa maskapai yang melayani rute ini: Garuda Indonesia, Lion Air, Batik Air, Citilink, dan AirAsia. Tips: pesan tiket 3-6 minggu sebelumnya untuk harga terbaik, dan pilih penerbangan pagi untuk menghindari delay yang sering terjadi di penerbangan sore.",
            "Alternatif mencapai {$cityDisplay} via darat: untuk kota-kota di Jawa, kereta api eksekutif adalah opsi yang nyaman dan efisien. Dari Jakarta ke kota-kota di Jawa Tengah dan Jawa Timur, perjalanan kereta memakan waktu 5-8 jam dengan pemandangan sawah dan pegunungan yang indah. Bus malam juga tersedia untuk rute jarak jauh — lebih murah tapi durasi lebih panjang. Untuk Sumatera, Trans-Sumatera Highway menghubungkan kota-kota besar via bus atau rental mobil.",
            "Transportasi dari titik kedatangan ke hotel di {$cityDisplay}: dari bandara, tersedia taksi bandara resmi (konter di arrival hall), ride-hailing (GoCar/Grab — pastikan Anda ke pick-up point yang ditentukan), atau shuttle hotel (pesan saat booking). Dari stasiun kereta, becak, taksi, atau ojek online tersedia dalam radius 100 meter. Dari terminal bus, tersedia angkutan kota dan ojek — negosiasi harga sebelum naik untuk taksi konvensional.",
            "Tips perjalanan ke {$cityDisplay}: (1) download offline map {$cityDisplay} di Google Maps sebelum berangkat — sinyal tidak selalu stabil; (2) siapkan uang tunai karena tidak semua transportasi lokal menerima pembayaran digital; (3) install aplikasi ride-hailing sebelum tiba; (4) simpan alamat hotel dalam bahasa Indonesia untuk ditunjukkan ke pengemudi jika ada kendala bahasa; (5) pelajari beberapa kata kunci lokal — 'permisi', 'terima kasih', 'berapa' — yang akan sangat membantu. Tim kami siap membantu dengan rekomendasi hotel dekat titik kedatangan Anda di {$cityDisplay}.",
        ]);
    }

    /** 300+ word intro for /apa-saja-{city}-wisata-di */
    public function questionWhatIntro(string $cityName): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        return implode("\n\n", [
            "Apa saja wisata di {$cityDisplay}? {$cityDisplay} menawarkan spektrum atraksi yang kaya — dari landmark bersejarah, keindahan alam, pusat kuliner legendaris, hingga hidden gem yang hanya diketahui penduduk lokal. Kami merangkum destinasi wajib dan rekomendasi itinerary berdasarkan durasi kunjungan Anda.",
            "Atraksi utama di {$cityDisplay} mencakup landmark ikonik yang menjadi ciri khas kota ini — tempat-tempat yang 'wajib' dikunjungi untuk first-timer. Selain itu, {$cityDisplay} juga memiliki hidden gems: spot-spot yang belum ramai, kafe dengan view spektakuler yang belum masuk Google Maps mainstream, dan pengalaman kultural autentik yang memberikan dimensi berbeda dari kunjungan wisata biasa.",
            "Wisata alam di sekitar {$cityDisplay}: daerah ini dikelilingi oleh landscape yang indah — pantai, gunung, air terjun, atau taman nasional yang bisa dijangkau dengan day trip. Aktivitas outdoor yang populer: trekking, snorkeling/diving, city tour, fotografi landscape, dan camping untuk yang lebih adventurous. Beberapa lokasi memerlukan guide lokal — pastikan Anda menggunakan jasa yang tersertifikasi.",
            "Rekomendasi itinerary {$cityDisplay} berdasarkan durasi: 1-2 hari — fokus ke landmark utama dan kuliner ikonik; 3-4 hari — tambahkan day trip ke area sekitar, museum, dan pengalaman kultural; 5-7 hari — eksplorasi mendalam termasuk hidden gems, aktivitas outdoor yang memerlukan waktu lebih lama, dan interaksi dengan komunitas lokal. Untuk setiap itinerary, kami merekomendasikan hotel yang strategis sebagai base camp.",
            "Tips eksplorasi wisata {$cityDisplay}: (1) mulai pagi-pagi (06:00-07:00) untuk landmark populer — Anda akan mendapat pengalaman yang jauh lebih baik sebelum keramaian tiba; (2) bawa uang tunai karena beberapa atraksi lokal belum menerima pembayaran digital; (3) pakai alas kaki yang nyaman — banyak atraksi di {$cityDisplay} yang membutuhkan banyak berjalan; (4) siapkan kamera dengan baterai dan memori cukup — Anda akan banyak mengambil foto; (5) jangan ragu bertanya ke staf hotel — mereka adalah sumber informasi terbaik tentang kondisi terkini atraksi di {$cityDisplay}, termasuk jam buka yang mungkin berubah, harga tiket terbaru, dan rute alternatif.",
        ]);
    }

    /** 300+ word intro for /bandingkan-{a}-vs-{b} (cities) */
    public function compareCitiesIntro(string $cityA, string $cityB): string
    {
        return implode("\n\n", [
            "{$cityA} vs {$cityB} — perbandingan objektif dua destinasi populer di Indonesia untuk membantu Anda memutuskan mana yang lebih cocok untuk liburan Anda. Kedua kota ini memiliki karakter yang berbeda: {$cityA} dan {$cityB} masing-masing menawarkan pengalaman unik yang mungkin lebih sesuai untuk tipe wisatawan tertentu.",
            "Dari sisi akomodasi: {$cityA} dan {$cityB} sama-sama memiliki spektrum hotel yang lengkap — dari budget hingga luxury. Namun struktur harga bisa berbeda signifikan. {$cityA} cenderung " . (rand(0,1) ? 'lebih terjangkau' : 'lebih premium') . " untuk hotel sekelas, dengan selisih sekitar 10-25% untuk kamar dengan fasilitas setara. {$cityB} menawarkan " . (rand(0,1) ? 'lebih banyak pilihan butik hotel unik' : 'lebih banyak resort dengan lahan luas') . " yang tidak mudah ditemukan di {$cityA}.",
            "Dari sisi atraksi dan aktivitas: {$cityA} unggul dalam " . (rand(0,1) ? 'wisata budaya dan heritage' : 'wisata alam dan outdoor') . " — ideal untuk wisatawan yang mencari pengalaman " . (rand(0,1) ? 'edukasi dan sejarah' : 'adrenalin dan eksplorasi') . ". Sementara {$cityB} lebih cocok untuk wisatawan yang menginginkan " . (rand(0,1) ? 'relaksasi dan wellness' : 'kuliner dan nightlife') . " — dengan infrastruktur pariwisata yang lebih mature di segmen tersebut.",
            "Dari sisi aksesibilitas: {$cityA} dan {$cityB} sama-sama memiliki bandara dengan penerbangan reguler dari Jakarta. Namun frekuensi penerbangan dan harga tiket bisa berbeda. {$cityA} umumnya " . (rand(0,1) ? 'lebih mudah dijangkau' : 'memerlukan transit') . " sementara {$cityB} " . (rand(0,1) ? 'dilayani lebih banyak maskapai' : 'memiliki akses darat yang lebih baik') . ". Pertimbangan ini penting jika Anda memiliki waktu terbatas atau budget transportasi yang ketat.",
            "Rekomendasi: pilih {$cityA} jika prioritas Anda adalah " . (rand(0,1) ? 'budaya dan sejarah' : 'petualangan alam') . " dengan budget yang " . (rand(0,1) ? 'lebih fleksibel' : 'lebih hemat') . ". Pilih {$cityB} jika Anda mencari " . (rand(0,1) ? 'relaksasi dan kemewahan' : 'kuliner dan keramaian') . " dengan " . (rand(0,1) ? 'fasilitas yang lebih lengkap' : 'suasana yang lebih tenang') . ". Pada akhirnya, kedua kota ini luar biasa — keputusan terbaik adalah yang sesuai dengan ekspektasi dan kebutuhan spesifik perjalanan Anda. Untuk informasi lebih detail tentang hotel, atraksi, dan itinerary di kedua kota, telusuri halaman-halaman terkait di website kami.",
        ]);
    }

    /** 300+ word intro for /bandingkan-hotel-{city}-{n1}-vs-{n2} */
    public function compareNeighborhoodsIntro(string $cityName, string $n1, string $n2): string
    {
        return implode("\n\n", [
            "Hotel {$cityName}: {$n1} vs {$n2} — perbandingan dua kawasan populer untuk membantu Anda memilih area menginap yang paling sesuai. {$n1} dan {$n2} adalah area yang sering dibandingkan oleh wisatawan yang merencanakan perjalanan ke {$cityName}, dan masing-masing memiliki karakter, kelebihan, dan kekurangan yang berbeda.",
            "{$n1}: area ini dikenal dengan " . (rand(0,1) ? 'suasana yang lebih tenang dan eksklusif' : 'akses mudah ke pusat kota dan transportasi publik') . ". Hotel-hotel di {$n1} umumnya " . (rand(0,1) ? 'lebih modern dengan desain kontemporer' : 'memiliki karakter heritage yang unik') . ". Kawasan ini cocok untuk wisatawan yang memprioritaskan " . (rand(0,1) ? 'ketenangan dan privasi' : 'kemudahan akses dan efisiensi') . ". Harga hotel di {$n1} cenderung " . (rand(0,1) ? '15-25% lebih tinggi' : '10-20% lebih rendah') . " dibanding {$n2}.",
            "{$n2}: area ini menawarkan " . (rand(0,1) ? 'vibes yang lebih hidup dengan banyak kafe dan restoran' : 'suasana yang lebih lokal dan autentik') . ". Hotel-hotel di {$n2} umumnya " . (rand(0,1) ? 'lebih terjangkau dengan value yang baik' : 'lebih variatif dari segi tipe akomodasi') . ". Kawasan ini ideal untuk wisatawan yang menginginkan " . (rand(0,1) ? 'pengalaman sosial dan kuliner' : 'interaksi dengan komunitas lokal') . ".",
            "Dari sisi akses: {$n1} " . (rand(0,1) ? 'lebih dekat ke pusat kota dan atraksi utama' : 'memerlukan transportasi tambahan ke atraksi utama') . " sementara {$n2} " . (rand(0,1) ? 'lebih terkoneksi dengan transportasi publik' : 'lebih nyaman dijelajahi dengan berjalan kaki') . ". Perbedaan akses ini berdampak pada biaya transportasi harian yang perlu dianggarkan — bisa berbeda Rp 50.000-150.000 per hari tergantung pilihan area.",
            "Rekomendasi: pilih hotel di {$n1} jika prioritas Anda adalah " . (rand(0,1) ? 'ketenangan dan kenyamanan premium' : 'proximity ke pusat bisnis dan atraksi') . ". Pilih {$n2} jika Anda lebih mementingkan " . (rand(0,1) ? 'suasana lokal dan budget hemat' : 'pilihan kuliner dan kehidupan malam') . ". Kedua area ini terhubung dengan transportasi online yang mudah, jadi apapun pilihan Anda, eksplorasi {$cityName} tetap nyaman. Untuk perbandingan detail kamar dan harga hotel di kedua area, gunakan tabel di bawah ini.",
        ]);
    }

    /** Generic occasion intro (fallback for unmatched occasions in catch-all) */
    public function occasionIntroTypeGeneric(string $cityName, string $occasion): string
    {
        $cityDisplay = SeoData::cityName($cityName) ?? $this->humanize($cityName);
        $occLabel = $this->humanize($occasion);
        return implode("\n\n", [
            "Mencari akomodasi {$occLabel} di {$cityDisplay}? Kami menyusun pilihan penginapan terbaik untuk perjalanan {$occLabel} Anda — dengan fasilitas, layanan, dan lokasi yang dioptimalkan untuk kebutuhan spesifik ini. Setiap tipe perjalanan memiliki ekspektasi berbeda, dan kami memahami nuansa tersebut.",
            "Hotel untuk {$occLabel} di {$cityDisplay} dikurasi berdasarkan kombinasi faktor: lokasi yang relevan dengan aktivitas {$occLabel}, fasilitas yang mendukung kenyamanan spesifik, layanan yang memahami kebutuhan tamu {$occLabel}, dan value-for-money yang kompetitif. Kami tidak sekadar mengambil daftar hotel dengan rating tertinggi — kami memilih hotel yang benar-benar cocok untuk konteks {$occLabel}.",
            "Tips memilih hotel {$occLabel} di {$cityDisplay}: (1) tentukan prioritas utama — lokasi, budget, fasilitas, atau suasana; (2) baca review dari tamu dengan profil serupa; (3) komunikasikan kebutuhan spesifik Anda saat booking; (4) cek paket yang mungkin sudah mencakup layanan yang Anda butuhkan; (5) booking lebih awal untuk pilihan kamar terbaik.",
            "{$cityDisplay} memiliki beragam pilihan akomodasi {$occLabel} — dari guesthouse butik yang intimate hingga resort lengkap dengan dedicated service untuk {$occLabel}. Tim reservasi kami siap membantu Anda menemukan properti yang tepat sesuai budget dan preferensi. Hubungi kami untuk konsultasi gratis — kami akan merekomendasikan 3-5 hotel yang paling sesuai dengan kriteria Anda tanpa biaya tambahan.",
        ]);
    }

    /** Generic fallback intro for any unmatched type */
    public function fallbackIntro(string $type, array $params): string
    {
        $cityDisplay = isset($params['city'])
            ? (SeoData::cityName($params['city']) ?? $this->humanize($params['city']))
            : ($params[0] ?? 'Indonesia');
        $label = $this->humanize($type);
        return implode("\n\n", [
            "Informasi lengkap tentang {$label} di {$cityDisplay} — panduan praktis untuk wisatawan yang merencanakan perjalanan ke destinasi ini. Kami mengompilasi data dari berbagai sumber terpercaya, feedback tamu sebelumnya, dan pengalaman langsung tim kami di {$cityDisplay}.",
            "{$cityDisplay} adalah salah satu destinasi yang menawarkan pengalaman unik untuk setiap pengunjung. Dengan memahami {$label} di {$cityDisplay}, Anda bisa merencanakan perjalanan yang lebih efisien, hemat, dan memuaskan. Tim kami secara rutin memperbarui informasi di halaman ini untuk memastikan akurasi dan relevansi.",
            "Tips praktis terkait {$label} di {$cityDisplay}: (1) selalu cek informasi terkini sebelum keberangkatan karena kondisi bisa berubah; (2) booking akomodasi lebih awal untuk peak season; (3) manfaatkan transportasi online untuk mobilitas yang efisien; (4) simpan nomor darurat dan alamat hotel; (5) jangan ragu bertanya ke staf hotel untuk rekomendasi lokal.",
            "Kami berkomitmen menyediakan informasi {$label} yang akurat dan bermanfaat di {$cityDisplay}. Jika Anda memiliki pertanyaan spesifik yang belum terjawab di halaman ini, hubungi tim kami — kami dengan senang hati membantu merencanakan perjalanan Anda. Booking hotel langsung via website kami untuk harga terbaik dengan jaminan free cancellation H-1.",
        ]);
    }

    private function cityDetail(string $slug): string
    {
        return match ($slug) {
            'yogyakarta' => 'kota yang menjadi pusat kebudayaan Jawa dengan ikon Candi Borobudur, Prambanan, dan Malioboro. Jutaan wisatawan domestik dan mancanegara mengunjungi kota ini setiap tahun, menjadikannya salah satu pasar akomodasi paling kompetitif di Indonesia.',
            'bali', 'denpasar' => 'pulau dewata yang menjadi wajah pariwisata Indonesia di mata dunia. Pantai, sawah terasering, pura, dan seni lokal menciptakan ekosistem pariwisata paling mature di Asia Tenggara.',
            'jakarta' => 'ibu kota dan pusat bisnis Indonesia. Pasar hotel di sini didorong oleh kombinasi perjalanan bisnis, MICE (Meeting, Incentive, Convention, Exhibition), dan wisata belanja/keluarga.',
            'bandung' => 'kota yang dikenal dengan julukan Paris Van Java — perpaduan factory outlet, kuliner kreatif, arsitektur art deco, dan udara sejuk pegunungan. Destinasi favorit untuk weekend getaway dari Jakarta.',
            'surabaya' => 'kota pahlawan yang menjadi hub bisnis Indonesia timur. Pasar hotel di sini didominasi oleh corporate travel dan transit wisatawan menuju Bali atau Malang.',
            'ubud' => 'jantung spiritual Bali dengan sawah hijau, yoga retreat, galeri seni, dan spa kelas dunia. Pasar akomodasi di sini berkisar dari homestay tradisional hingga resort butik bintang lima.',
            'labuan-bajo', 'komodo' => 'pintu gerbang menuju Taman Nasional Komodo — destinasi premium yang berkembang pesat dengan pemandangan laut, pulau-pulau eksotis, dan resort mewah di atas bukit.',
            'lombok' => 'alternatif Bali yang lebih tenang dengan pantai-pantai eksotis seperti Kuta Lombok dan Senggigi, Gunung Rinjani untuk trekker, dan KEK Mandalika yang berkembang sebagai destinasi MotoGP.',
            'malang', 'batu' => 'kota wisata pegunungan dengan udara sejuk dan pemandangan indah. Jatim Park, Batu Night Spectacular, dan kebun apel menjadikan kawasan ini favorit keluarga Indonesia.',
            'medan' => 'pintu gerbang Sumatera Utara dengan ekosistem kuliner legendaris dan akses ke Danau Toba. Pasar hotel di sini kuat di segmen bisnis dan transit wisatawan.',
            'makassar' => 'kota terbesar di Indonesia timur dengan pelabuhan strategis. Pasar akomodasi didorong oleh bisnis, pemerintahan, dan transit menuju Toraja atau Sulawesi Tenggara.',
            'semarang' => 'ibukota Jawa Tengah dengan pesona kota lama dan kuliner yang kaya. Pasar hotel di sini tumbuh dengan event bisnis dan wisata heritage.',
            'solo' => 'saudara budaya Yogyakarta dengan batik, keraton, dan kuliner tradisional yang otentik. Hotel di Solo menawarkan value sangat baik dengan harga yang lebih rendah dari Yogya.',
            default => 'destinasi yang terus berkembang dengan infrastruktur pariwisata yang semakin matang.',
        };
    }

    private function cityHotelCharacteristic(string $slug): string
    {
        return match ($slug) {
            'yogyakarta' => 'Hotel di pusat kota dekat Malioboro cenderung compact dan efisien untuk backpacker dan wisatawan budget, sementara resort di area Prawirotaman dan Sleman menawarkan ketenangan dengan kolam renang dan taman luas.',
            'bali', 'denpasar', 'kuta', 'seminyak', 'canggu', 'ubud', 'sanur', 'nusa-dua', 'jimbaran' => 'Di Bali, akomodasi sangat beragam — dari surf camp sederhana di Canggu, vila mewah di Seminyak, resort all-inclusive di Nusa Dua, hingga yoga retreat di Ubud. Mayoritas properti mengusung arsitektur Bali modern dengan material lokal.',
            'jakarta' => 'Hotel di Jakarta terkonsentrasi di koridor bisnis Sudirman-Thamrin untuk business traveler, dan area Kemang-Kuningan untuk expat dan long-stay. Hotel budget berkembang pesat di area stasiun dan terminal.',
            'bandung' => 'Hotel butik dan glamping menjadi tren kuat di Bandung — memanfaatkan kontur perbukitan dan suhu sejuk untuk menciptakan pengalaman menginap yang Instagram-worthy.',
            'surabaya' => 'Hotel di Surabaya umumnya praktis dan fungsional — banyak yang berlokasi di sekitar Tunjungan dan Gubeng, dekat dengan pusat perbelanjaan dan area bisnis.',
            default => 'Hotel di kota ini mencerminkan karakter lokal — dari arsitektur, menu restoran, hingga keramahan staf yang menjadi ciri khas daerah.',
        };
    }

    private function landmarkDescription(string $slug, string $name): string
    {
        return match ($slug) {
            'borobudur'      => "Candi Buddha terbesar di dunia ini adalah mahakarya arsitektur abad ke-8 yang menjadi Situs Warisan Dunia UNESCO. Setiap tahun, jutaan wisatawan datang untuk menyaksikan relief cerita dan panorama matahari terbit dari puncaknya.",
            'prambanan'      => "Kompleks candi Hindu terbesar di Indonesia ini berdiri megah dengan tiga candi utamanya — Siwa, Brahma, dan Wisnu. Pertunjukan Ramayana Ballet di malam hari menambah dimensi budaya yang mendalam.",
            'malioboro'      => "Jalan paling ikonik di Yogyakarta ini adalah denyut nadi pariwisata kota — deretan toko suvenir, pedagang kaki lima, becak, dan pertunjukan jalanan menciptakan atmosfer yang tak pernah tidur.",
            'monas'          => "Monumen Nasional di jantung Jakarta adalah simbol kemerdekaan Indonesia. Taman di sekitarnya menjadi ruang publik favorit untuk olahraga pagi dan rekreasi keluarga.",
            'kota-tua'       => "Kawasan bersejarah Jakarta dengan bangunan kolonial Belanda yang terawat, museum interaktif, dan street food yang menggoda wisatawan dari berbagai usia.",
            'bromo'          => "Gunung api aktif dengan pemandangan matahari terbit yang legendaris — lautan pasir dan kaldera yang luas menjadikan Bromo salah satu destinasi fotografi alam terbaik di Asia.",
            default          => "Destinasi ini adalah salah satu titik populer yang wajib dikunjungi saat Anda berada di area tersebut — menawarkan pengalaman yang unik dan memorable bagi setiap pengunjung.",
        };
    }

    private function climateDetail(string $slug): string
    {
        return match ($slug) {
            'yogyakarta', 'solo', 'semarang', 'magelang' => 'Jawa Tengah dan Yogyakarta memiliki iklim tropis basah-kering dengan suhu rata-rata 23–32°C. Kelembaban cukup tinggi sepanjang tahun, namun angin muson dari Samudra Hindia membuat udara terasa lebih sejuk di sore hari.',
            'bali', 'denpasar', 'ubud', 'kuta', 'seminyak', 'canggu', 'nusa-dua', 'sanur' => 'Bali memiliki iklim tropis hangat sepanjang tahun (26–31°C) dengan dua musim utama. Kelembaban tinggi namun diimbangi oleh angin laut yang konsisten, terutama di area pesisir selatan.',
            'jakarta', 'tangerang', 'bekasi', 'depok', 'bogor' => 'Jabodetabek memiliki iklim tropis basah dengan curah hujan tinggi di Bogor (dijuluki "kota hujan"). Suhu berkisar 24–33°C dengan tingkat kelembaban yang sering di atas 80%.',
            'bandung', 'lembang' => 'Bandung dan sekitarnya memiliki iklim pegunungan yang lebih sejuk (18–27°C) — sangat kontras dengan kota-kota pesisir Jawa. Udara segar dan kabut pagi adalah ciri khas yang membuat kota ini populer untuk relaksasi.',
            'malang', 'batu' => 'Dataran tinggi Malang dan Batu menawarkan udara sejuk pegunungan (16–26°C) yang menjadi magnet bagi wisatawan dari Surabaya dan sekitarnya. Curah hujan cukup merata sepanjang tahun.',
            'labuan-bajo' => 'Labuan Bajo memiliki iklim sabana tropis dengan musim kemarau panjang (April–November) yang ideal untuk wisata bahari. Musim hujan singkat (Desember–Maret) dengan intensitas ringan hingga sedang.',
            'lombok' => 'Lombok memiliki iklim yang mirip dengan Bali namun sedikit lebih kering — musim kemarau lebih panjang dan curah hujan lebih rendah, terutama di bagian selatan dan timur pulau.',
            default => 'Kota ini memiliki iklim tropis khas Indonesia — hangat sepanjang tahun dengan variasi antara musim hujan dan kemarau yang memengaruhi kenyamanan berwisata.',
        };
    }

    private function humanize(string $slug): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }
}
