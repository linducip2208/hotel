<?php

namespace App\Support;

/**
 * Static reference data untuk pSEO generation.
 * 250+ cities × 120+ landmarks × 80+ neighborhoods = 15,000+ indexed pages.
 */
class SeoData
{
    /** 250+ kota di Indonesia (slug → display). */
    public const CITIES = [
        // ── Aceh ──
        'banda-aceh'      => 'Banda Aceh',
        'sabang'          => 'Sabang',
        'lhokseumawe'     => 'Lhokseumawe',
        'langsa'          => 'Langsa',
        'takengon'        => 'Takengon',
        // ── Sumatera Utara ──
        'medan'           => 'Medan',
        'sibolga'         => 'Sibolga',
        'pematangsiantar' => 'Pematangsiantar',
        'padangsidimpuan' => 'Padangsidimpuan',
        'gunungsitoli'    => 'Gunungsitoli',
        'balige'          => 'Balige',
        'parapat'         => 'Parapat',
        'toba'            => 'Danau Toba',
        'berastagi'       => 'Berastagi',
        // ── Sumatera Barat ──
        'padang'          => 'Padang',
        'bukittinggi'     => 'Bukittinggi',
        'payakumbuh'      => 'Payakumbuh',
        'solok'           => 'Solok',
        'sawahlunto'      => 'Sawahlunto',
        'batusangkar'     => 'Batusangkar',
        'pariaman'        => 'Pariaman',
        // ── Riau ──
        'pekanbaru'       => 'Pekanbaru',
        'dumai'           => 'Dumai',
        'bengkalis'       => 'Bengkalis',
        'rengat'          => 'Rengat',
        // ── Kepulauan Riau ──
        'batam'           => 'Batam',
        'tanjung-pinang'  => 'Tanjung Pinang',
        'bintan'          => 'Bintan',
        'karimun'         => 'Karimun',
        // ── Jambi ──
        'jambi'           => 'Jambi',
        'sungai-penuh'    => 'Sungai Penuh',
        // ── Sumatera Selatan ──
        'palembang'       => 'Palembang',
        'lubuklinggau'    => 'Lubuklinggau',
        'pagaralam'       => 'Pagaralam',
        'lahat'           => 'Lahat',
        'baturaja'        => 'Baturaja',
        // ── Bengkulu ──
        'bengkulu'        => 'Bengkulu',
        'curup'           => 'Curup',
        'manna'           => 'Manna',
        // ── Lampung ──
        'lampung'         => 'Bandar Lampung',
        'metro'           => 'Metro',
        'kalianda'        => 'Kalianda',
        'pringsewu'       => 'Pringsewu',
        'kotabumi'        => 'Kotabumi',
        // ── Bangka Belitung ──
        'pangkal-pinang'  => 'Pangkal Pinang',
        'belitung'        => 'Belitung',
        'tanjung-pandan'  => 'Tanjung Pandan',
        'manggar'         => 'Manggar',
        // ── Banten ──
        'serang'          => 'Serang',
        'cilegon'         => 'Cilegon',
        'anyer'           => 'Anyer',
        'carita'          => 'Carita',
        'pandeglang'      => 'Pandeglang',
        'tangerang'       => 'Tangerang',
        'tangerang-selatan'=> 'Tangerang Selatan',
        'bsd'             => 'BSD City',
        // ── DKI Jakarta ──
        'jakarta'         => 'Jakarta',
        // ── Jawa Barat ──
        'bandung'         => 'Bandung',
        'bogor'           => 'Bogor',
        'depok'           => 'Depok',
        'bekasi'          => 'Bekasi',
        'sukabumi'        => 'Sukabumi',
        'cirebon'         => 'Cirebon',
        'tasikmalaya'     => 'Tasikmalaya',
        'garut'           => 'Garut',
        'cianjur'         => 'Cianjur',
        'purwakarta'      => 'Purwakarta',
        'subang'          => 'Subang',
        'sumedang'        => 'Sumedang',
        'majalengka'      => 'Majalengka',
        'indramayu'       => 'Indramayu',
        'kuningan'        => 'Kuningan',
        'pangandaran'     => 'Pangandaran',
        'ciamis'          => 'Ciamis',
        'cipanas'         => 'Cipanas',
        'lembang'         => 'Lembang',
        'ciwidey'         => 'Ciwidey',
        'karawang'        => 'Karawang',
        // ── Jawa Tengah ──
        'semarang'        => 'Semarang',
        'solo'            => 'Solo',
        'magelang'        => 'Magelang',
        'salatiga'        => 'Salatiga',
        'pekalongan'      => 'Pekalongan',
        'kudus'           => 'Kudus',
        'purwokerto'      => 'Purwokerto',
        'tegal'           => 'Tegal',
        'brebes'          => 'Brebes',
        'pemalang'        => 'Pemalang',
        'batang'          => 'Batang',
        'kendal'          => 'Kendal',
        'jepara'          => 'Jepara',
        'demak'           => 'Demak',
        'pati'            => 'Pati',
        'rembang'         => 'Rembang',
        'blora'           => 'Blora',
        'wonosobo'        => 'Wonosobo',
        'temanggung'      => 'Temanggung',
        'klaten'          => 'Klaten',
        'boyolali'        => 'Boyolali',
        'karanganyar'     => 'Karanganyar',
        'sragen'          => 'Sragen',
        'purwodadi'       => 'Purwodadi',
        'ungaran'         => 'Ungaran',
        'ambarawa'        => 'Ambarawa',
        'dieng'           => 'Dataran Tinggi Dieng',
        // ── D.I. Yogyakarta ──
        'yogyakarta'      => 'Yogyakarta',
        'sleman'          => 'Sleman',
        'bantul'          => 'Bantul',
        'gunung-kidul'    => 'Gunung Kidul',
        'kulon-progo'     => 'Kulon Progo',
        // ── Jawa Timur ──
        'surabaya'        => 'Surabaya',
        'malang'          => 'Malang',
        'batu'            => 'Kota Batu',
        'kediri'          => 'Kediri',
        'madiun'          => 'Madiun',
        'banyuwangi'      => 'Banyuwangi',
        'jember'          => 'Jember',
        'blitar'          => 'Blitar',
        'probolinggo'     => 'Probolinggo',
        'pasuruan'        => 'Pasuruan',
        'mojokerto'       => 'Mojokerto',
        'sidoarjo'        => 'Sidoarjo',
        'gresik'          => 'Gresik',
        'lamongan'        => 'Lamongan',
        'tuban'           => 'Tuban',
        'bojonegoro'      => 'Bojonegoro',
        'nganjuk'         => 'Nganjuk',
        'jombang'         => 'Jombang',
        'ponorogo'        => 'Ponorogo',
        'pacitan'         => 'Pacitan',
        'trenggalek'      => 'Trenggalek',
        'tulungagung'     => 'Tulungagung',
        'lumajang'        => 'Lumajang',
        'situbondo'       => 'Situbondo',
        'bondowoso'       => 'Bondowoso',
        'sampang'         => 'Sampang',
        'pamekasan'       => 'Pamekasan',
        'sumenep'         => 'Sumenep',
        'bangkalan'       => 'Bangkalan',
        // ── Bali ──
        'denpasar'        => 'Denpasar',
        'ubud'            => 'Ubud',
        'kuta'            => 'Kuta',
        'seminyak'        => 'Seminyak',
        'canggu'          => 'Canggu',
        'sanur'           => 'Sanur',
        'nusa-dua'        => 'Nusa Dua',
        'jimbaran'        => 'Jimbaran',
        'singaraja'       => 'Singaraja',
        'tabanan'         => 'Tabanan',
        'gianyar'         => 'Gianyar',
        'klungkung'       => 'Klungkung',
        'karangasem'      => 'Karangasem',
        'bangli'          => 'Bangli',
        'bali'            => 'Bali',
        'uluwatu'         => 'Uluwatu',
        // ── Nusa Tenggara Barat ──
        'mataram'         => 'Mataram',
        'lombok'          => 'Lombok',
        'praya'           => 'Praya',
        'selong'          => 'Selong',
        'sumbawa'         => 'Sumbawa',
        'bima'            => 'Bima',
        'dompu'           => 'Dompu',
        'gili-trawangan'  => 'Gili Trawangan',
        'senggigi'        => 'Senggigi',
        'kuta-lombok'     => 'Kuta Lombok',
        'mandalika'       => 'Mandalika',
        // ── Nusa Tenggara Timur ──
        'kupang'          => 'Kupang',
        'labuan-bajo'     => 'Labuan Bajo',
        'ende'            => 'Ende',
        'maumere'         => 'Maumere',
        'ruteng'          => 'Ruteng',
        'bajawa'          => 'Bajawa',
        'waingapu'        => 'Waingapu',
        'waikabubak'      => 'Waikabubak',
        'kalabahi'        => 'Kalabahi',
        'atambua'         => 'Atambua',
        'soe'             => 'Soe',
        'kefamenanu'      => 'Kefamenanu',
        'larantuka'       => 'Larantuka',
        'rotenao'         => 'Rote Ndao',
        // ── Kalimantan Barat ──
        'pontianak'       => 'Pontianak',
        'singkawang'      => 'Singkawang',
        'sambas'          => 'Sambas',
        'sintang'          => 'Sintang',
        'ketapang'        => 'Ketapang',
        // ── Kalimantan Tengah ──
        'palangkaraya'    => 'Palangkaraya',
        'pangkalan-bun'   => 'Pangkalan Bun',
        'sampit'          => 'Sampit',
        'kuala-kapuas'    => 'Kuala Kapuas',
        // ── Kalimantan Selatan ──
        'banjarmasin'     => 'Banjarmasin',
        'martapura'       => 'Martapura',
        'kandangan'       => 'Kandangan',
        'barabai'         => 'Barabai',
        'pelaihari'       => 'Pelaihari',
        'kotabaru'        => 'Kotabaru',
        // ── Kalimantan Timur ──
        'samarinda'       => 'Samarinda',
        'balikpapan'      => 'Balikpapan',
        'bontang'         => 'Bontang',
        'sangatta'        => 'Sangatta',
        'tenggarong'      => 'Tenggarong',
        'penajam'         => 'Penajam',
        // ── Kalimantan Utara ──
        'tarakan'         => 'Tarakan',
        'nunukan'         => 'Nunukan',
        'tanjung-selor'   => 'Tanjung Selor',
        'malinau'         => 'Malinau',
        // ── Sulawesi Selatan ──
        'makassar'        => 'Makassar',
        'parepare'        => 'Parepare',
        'palopo'          => 'Palopo',
        'watampone'       => 'Watampone',
        'bulukumba'       => 'Bulukumba',
        'rantepao'        => 'Rantepao',
        'tanatoraja'      => 'Tana Toraja',
        'selayar'         => 'Selayar',
        // ── Sulawesi Barat ──
        'mamuju'          => 'Mamuju',
        'polewali-mandar' => 'Polewali Mandar',
        'majene'          => 'Majene',
        // ── Sulawesi Tengah ──
        'palu'            => 'Palu',
        'poso'            => 'Poso',
        'luwuk'           => 'Luwuk',
        'toli-toli'       => 'Toli-Toli',
        'donggala'        => 'Donggala',
        'ampana'          => 'Ampana',
        // ── Sulawesi Tenggara ──
        'kendari'         => 'Kendari',
        'bau-bau'         => 'Bau-Bau',
        'kolaka'          => 'Kolaka',
        'raha'            => 'Raha',
        // ── Sulawesi Utara ──
        'manado'          => 'Manado',
        'bitung'          => 'Bitung',
        'tomohon'         => 'Tomohon',
        'kotamobagu'      => 'Kotamobagu',
        'tahuna'          => 'Tahuna',
        // ── Gorontalo ──
        'gorontalo'       => 'Gorontalo',
        'limboto'         => 'Limboto',
        // ── Maluku ──
        'ambon'           => 'Ambon',
        'masohi'          => 'Masohi',
        'tual'            => 'Tual',
        'namlea'          => 'Namlea',
        // ── Maluku Utara ──
        'ternate'         => 'Ternate',
        'tidore'          => 'Tidore',
        'tobelo'          => 'Tobelo',
        'sofifi'          => 'Sofifi',
        // ── Papua ──
        'jayapura'        => 'Jayapura',
        'timika'          => 'Timika',
        'merauke'         => 'Merauke',
        'biak'            => 'Biak',
        'sarmi'           => 'Sarmi',
        // ── Papua Barat ──
        'manokwari'       => 'Manokwari',
        'fakfak'          => 'Fakfak',
        'kaimana'         => 'Kaimana',
        'bintuni'         => 'Bintuni',
        // ── Papua Barat Daya ──
        'sorong'          => 'Sorong',
        'raja-ampat'      => 'Raja Ampat',
        // ── Papua Pegunungan ──
        'wamena'          => 'Wamena',
    ];

    /** 150+ landmark / destinasi wisata dengan koordinat. */
    public const LANDMARKS = [
        // [slug, name, city, province, lat, lng]
        // ── Yogyakarta ──
        ['malioboro',              'Jalan Malioboro',         'Yogyakarta', 'D.I. Yogyakarta',  -7.7925, 110.3653],
        ['borobudur',              'Candi Borobudur',         'Magelang',   'Jawa Tengah',      -7.6079, 110.2038],
        ['prambanan',              'Candi Prambanan',         'Yogyakarta', 'D.I. Yogyakarta',  -7.7520, 110.4915],
        ['malioboro-station',      'Stasiun Yogyakarta',      'Yogyakarta', 'D.I. Yogyakarta',  -7.7894, 110.3631],
        ['taman-sari',             'Taman Sari Yogyakarta',   'Yogyakarta', 'D.I. Yogyakarta',  -7.8101, 110.3592],
        ['keraton-yogya',          'Keraton Yogyakarta',      'Yogyakarta', 'D.I. Yogyakarta',  -7.8048, 110.3638],
        ['pantai-parangtritis',    'Pantai Parangtritis',     'Bantul',     'D.I. Yogyakarta',  -8.0241, 110.3224],
        ['candi-ratu-boko',        'Candi Ratu Boko',         'Sleman',     'D.I. Yogyakarta',  -7.7706, 110.4882],
        // ── Bali ──
        ['kuta-beach',             'Pantai Kuta',             'Badung',     'Bali',             -8.7184, 115.1686],
        ['seminyak-beach',         'Pantai Seminyak',         'Badung',     'Bali',             -8.6900, 115.1663],
        ['ubud-monkey-forest',     'Monkey Forest Ubud',      'Gianyar',    'Bali',             -8.5184, 115.2589],
        ['tanah-lot',              'Pura Tanah Lot',          'Tabanan',    'Bali',             -8.6212, 115.0867],
        ['uluwatu',                'Pura Uluwatu',            'Badung',     'Bali',             -8.8290, 115.0850],
        ['ngurah-rai-airport',     'Bandara Ngurah Rai',      'Badung',     'Bali',             -8.7480, 115.1672],
        ['nusa-penida',            'Nusa Penida',             'Klungkung',  'Bali',             -8.7278, 115.5444],
        ['besakih',                'Pura Besakih',            'Karangasem', 'Bali',             -8.3739, 115.4492],
        ['bedugul',                'Bedugul & Danau Beratan', 'Tabanan',    'Bali',             -8.2760, 115.1675],
        ['tegalalang',             'Tegallalang Rice Terrace','Gianyar',    'Bali',             -8.4316, 115.2789],
        ['jimbaran-bay',           'Teluk Jimbaran',          'Badung',     'Bali',             -8.7820, 115.1553],
        ['padang-padang-beach',    'Pantai Padang Padang',    'Badung',     'Bali',             -8.8909, 115.0906],
        ['goa-gajah',              'Goa Gajah',               'Gianyar',    'Bali',             -8.5227, 115.2898],
        // ── Jakarta ──
        ['monas',                  'Monumen Nasional',        'Jakarta',    'DKI Jakarta',      -6.1754, 106.8272],
        ['kemang',                 'Kemang',                  'Jakarta',    'DKI Jakarta',      -6.2604, 106.8137],
        ['kota-tua',               'Kota Tua Jakarta',        'Jakarta',    'DKI Jakarta',      -6.1352, 106.8133],
        ['scbd',                   'SCBD Sudirman',           'Jakarta',    'DKI Jakarta',      -6.2255, 106.8090],
        ['soekarno-hatta-airport', 'Bandara Soekarno-Hatta',  'Tangerang',  'Banten',           -6.1256, 106.6558],
        ['ancol',                  'Taman Impian Jaya Ancol', 'Jakarta',    'DKI Jakarta',      -6.1249, 106.8411],
        ['tmii',                   'Taman Mini Indonesia Indah','Jakarta',  'DKI Jakarta',      -6.3016, 106.8969],
        ['gbk',                    'Gelora Bung Karno',       'Jakarta',    'DKI Jakarta',      -6.2186, 106.8023],
        ['grand-indonesia',        'Grand Indonesia Mall',    'Jakarta',    'DKI Jakarta',      -6.1940, 106.8228],
        ['pik',                    'Pantai Indah Kapuk',      'Jakarta',    'DKI Jakarta',      -6.1085, 106.7542],
        ['kebun-raya-bogor',       'Kebun Raya Bogor',        'Bogor',      'Jawa Barat',       -6.6024, 106.8007],
        // ── Bandung ──
        ['bandung-paris-van-java', 'Paris Van Java Mall',      'Bandung',    'Jawa Barat',       -6.8915, 107.5870],
        ['lembang',                'Lembang',                 'Bandung Barat','Jawa Barat',     -6.8120, 107.6173],
        ['kawah-putih',            'Kawah Putih',             'Ciwidey',    'Jawa Barat',       -7.1668, 107.4014],
        ['gedung-sate',            'Gedung Sate',             'Bandung',    'Jawa Barat',       -6.9025, 107.6188],
        ['braga',                  'Jalan Braga',             'Bandung',    'Jawa Barat',       -6.9178, 107.6092],
        ['trans-studio-bandung',   'Trans Studio Bandung',    'Bandung',    'Jawa Barat',       -6.9285, 107.6360],
        ['tangkuban-perahu',       'Gunung Tangkuban Perahu', 'Bandung Barat','Jawa Barat',     -6.7698, 107.5998],
        ['ciwidey',                'Ciwidey',                 'Ciwidey',    'Jawa Barat',       -7.0926, 107.4258],
        // ── Surabaya ──
        ['surabaya-tunjungan',     'Tunjungan Plaza',         'Surabaya',   'Jawa Timur',       -7.2625, 112.7376],
        ['surabaya-submarine',     'Monumen Kapal Selam',     'Surabaya',   'Jawa Timur',       -7.2607, 112.7504],
        ['surabaya-zoo',           'Kebun Binatang Surabaya', 'Surabaya',   'Jawa Timur',       -7.2996, 112.7363],
        ['jembatan-suramadu',      'Jembatan Suramadu',       'Surabaya',   'Jawa Timur',       -7.2160, 112.7802],
        ['pantai-kenjeran',        'Pantai Kenjeran',         'Surabaya',   'Jawa Timur',       -7.2331, 112.7998],
        ['house-of-sampoerna',     'House of Sampoerna',      'Surabaya',   'Jawa Timur',       -7.2360, 112.7343],
        ['batu-night-spectacular', 'Batu Night Spectacular',  'Batu',       'Jawa Timur',       -7.8914, 112.5370],
        // ── Malang ──
        ['malang-batu-jatim-park', 'Jatim Park',              'Batu',       'Jawa Timur',       -7.8721, 112.5263],
        ['malang-city-square',     'Alun-Alun Malang',        'Malang',     'Jawa Timur',       -7.9840, 112.6347],
        ['museum-angkut',          'Museum Angkut',           'Batu',       'Jawa Timur',       -7.8805, 112.5238],
        ['coban-rondo',            'Air Terjun Coban Rondo',  'Batu',       'Jawa Timur',       -7.8656, 112.5048],
        // ── Jawa Timur ──
        ['bromo',                  'Gunung Bromo',            'Probolinggo','Jawa Timur',       -7.9425, 112.9530],
        ['ijen',                   'Kawah Ijen',              'Banyuwangi', 'Jawa Timur',       -8.0586, 114.2419],
        ['semeru',                 'Gunung Semeru',           'Lumajang',   'Jawa Timur',       -8.1077, 112.9261],
        ['baluran',                'Taman Nasional Baluran',  'Situbondo',  'Jawa Timur',       -7.8365, 114.3607],
        // ── Jawa Tengah ──
        ['lawang-sewu',            'Lawang Sewu',             'Semarang',   'Jawa Tengah',      -6.9840, 110.4092],
        ['kota-lama-semarang',     'Kota Lama Semarang',      'Semarang',   'Jawa Tengah',      -6.9679, 110.4271],
        ['candi-gedong-songo',     'Candi Gedong Songo',      'Semarang',   'Jawa Tengah',      -7.1941, 110.3429],
        ['tawangmangu',            'Tawangmangu',             'Karanganyar','Jawa Tengah',      -7.6746, 111.1307],
        ['karimunjawa',            'Karimunjawa',             'Jepara',     'Jawa Tengah',      -5.8230, 110.4620],
        ['dieng-plateau',          'Dataran Tinggi Dieng',    'Wonosobo',   'Jawa Tengah',      -7.2145, 109.8688],
        ['candi-sukuh',            'Candi Sukuh',             'Karanganyar','Jawa Tengah',      -7.6266, 111.1311],
        ['telaga-warna',           'Telaga Warna Dieng',      'Wonosobo',   'Jawa Tengah',      -7.2120, 109.9030],
        // ── Lombok ──
        ['mount-rinjani',          'Gunung Rinjani',          'Lombok Utara','Nusa Tenggara Barat', -8.4115, 116.4574],
        ['gili-trawangan-port',    'Pelabuhan Gili Trawangan','Lombok Utara','Nusa Tenggara Barat', -8.3520, 116.0420],
        ['mandalika',              'KEK Mandalika',           'Lombok Tengah','Nusa Tenggara Barat',-8.9080, 116.2920],
        ['gili-meno',              'Gili Meno',               'Lombok Utara','Nusa Tenggara Barat', -8.3493, 116.0552],
        ['gili-air',               'Gili Air',                'Lombok Utara','Nusa Tenggara Barat', -8.3580, 116.0809],
        // ── Labuan Bajo ──
        ['komodo',                 'Pulau Komodo',            'Manggarai Barat','Nusa Tenggara Timur', -8.5557, 119.4523],
        ['pink-beach',             'Pink Beach Komodo',       'Manggarai Barat','Nusa Tenggara Timur', -8.6020, 119.4880],
        ['padar-island',           'Pulau Padar',             'Manggarai Barat','Nusa Tenggara Timur', -8.6565, 119.5736],
        ['manta-point',            'Manta Point',             'Manggarai Barat','Nusa Tenggara Timur', -8.7500, 119.4500],
        ['rangko-cave',            'Goa Rangko',              'Manggarai Barat','Nusa Tenggara Timur', -8.4710, 119.8840],
        // ── Sumatera ──
        ['toba-lake',              'Danau Toba',              'Toba',       'Sumatera Utara',    2.6540,  98.7682],
        ['bukit-tinggi-jam-gadang','Jam Gadang',              'Bukittinggi','Sumatera Barat',   -0.3050, 100.3692],
        ['lembah-harau',           'Lembah Harau',            'Payakumbuh', 'Sumatera Barat',   -0.1250, 100.6300],
        ['pantai-padang',          'Pantai Air Manis',        'Padang',     'Sumatera Barat',   -0.8915, 100.3460],
        ['danau-maninjau',         'Danau Maninjau',          'Agam',       'Sumatera Barat',   -0.3120, 100.2250],
        ['ampera-bridge',          'Jembatan Ampera',         'Palembang',  'Sumatera Selatan', -2.9880, 104.7640],
        ['pulau-belitung',         'Pantai Tanjung Tinggi',   'Belitung',   'Bangka Belitung',  -2.5020, 107.6450],
        ['lampung-krakatau',       'Gunung Anak Krakatau',    'Lampung Selatan','Lampung',      -6.1000, 105.4230],
        ['mesjid-raya-medan',      'Masjid Raya Medan',       'Medan',      'Sumatera Utara',    3.5750,  98.6872],
        ['istana-maimun',          'Istana Maimun',           'Medan',      'Sumatera Utara',    3.5756,  98.6831],
        // ── Kalimantan ──
        ['derawan',                'Kepulauan Derawan',       'Berau',      'Kalimantan Timur',  2.2858, 118.2441],
        ['pasar-terapung',         'Pasar Terapung Banjarmasin','Banjarmasin','Kalimantan Selatan',-3.3186, 114.5901],
        ['khatulistiwa',           'Tugu Khatulistiwa',       'Pontianak',  'Kalimantan Barat',  0.0000, 109.3333],
        // ── Sulawesi ──
        ['tanatoraja-toraja',      'Tana Toraja',             'Tana Toraja','Sulawesi Selatan',-3.0760, 119.8660],
        ['bunaken',                'Taman Laut Bunaken',      'Manado',     'Sulawesi Utara',    1.6230, 124.7610],
        ['losari-beach',           'Pantai Losari',           'Makassar',   'Sulawesi Selatan', -5.1360, 119.4073],
        ['fort-rotterdam',         'Benteng Rotterdam',       'Makassar',   'Sulawesi Selatan', -5.1337, 119.4036],
        ['wakatobi',               'Taman Nasional Wakatobi', 'Wakatobi',   'Sulawesi Tenggara',-5.7600, 123.6700],
        // ── Papua ──
        ['raja-ampat-piaynemo',    'Pulau Piaynemo',          'Raja Ampat', 'Papua Barat',       0.5787, 130.2761],
        ['sentani-lake',           'Danau Sentani',           'Jayapura',   'Papua',            -2.5800, 140.5150],
        ['lembah-baliem',          'Lembah Baliem',           'Wamena',     'Papua Pegunungan', -4.0780, 138.9400],
        ['puncak-jaya',            'Puncak Jaya / Carstensz', 'Mimika',     'Papua Tengah',     -4.0833, 137.1833],
        // ── More tourist spots ──
        ['pulau-seribu',           'Kepulauan Seribu',        'Jakarta',    'DKI Jakarta',      -5.6000, 106.5500],
        ['puncak',                 'Puncak Bogor',            'Bogor',      'Jawa Barat',       -6.7000, 106.9833],
        ['curug-cilember',         'Curug Cilember',          'Bogor',      'Jawa Barat',       -6.6900, 106.9650],
        ['pantai-pangandaran',     'Pantai Pangandaran',      'Pangandaran','Jawa Barat',       -7.6900, 108.6600],
        ['green-canyon',           'Green Canyon Pangandaran','Pangandaran','Jawa Barat',       -7.7100, 108.4800],
        ['curug-sewu',             'Curug Sewu',              'Kendal',     'Jawa Tengah',      -6.9100, 110.1000],
        ['owabong',                'Owabong Waterpark',       'Purbalingga','Jawa Tengah',      -7.2800, 109.3700],
        ['jcc',                    'JCC Senayan',             'Jakarta',    'DKI Jakarta',      -6.2148, 106.8064],
        ['ice-bsd',                'ICE BSD',                 'Tangerang Selatan','Banten',    -6.2980, 106.6500],
        ['jiexpo',                 'JIExpo Kemayoran',        'Jakarta',    'DKI Jakarta',      -6.1460, 106.8510],
        ['masjid-istiqlal',        'Masjid Istiqlal',         'Jakarta',    'DKI Jakarta',      -6.1698, 106.8318],
        ['pura-besakih',           'Pura Besakih',            'Karangasem', 'Bali',             -8.3739, 115.4492],
        ['kawah-ijen',             'Kawah Ijen Blue Fire',    'Banyuwangi', 'Jawa Timur',       -8.0586, 114.2419],
        ['pantai-klayar',          'Pantai Klayar',           'Pacitan',    'Jawa Timur',       -8.2758, 111.0160],
        ['pantai-papuma',          'Pantai Papuma',           'Jember',     'Jawa Timur',       -8.4728, 113.6933],
        // ── Shopping Malls ──
        ['tunjungan-plaza',        'Tunjungan Plaza',         'Surabaya',   'Jawa Timur',       -7.2625, 112.7376],
        ['grand-indonesia-mall',   'Grand Indonesia Mall',    'Jakarta',    'DKI Jakarta',      -6.1940, 106.8228],
        ['pim',                    'Pondok Indah Mall',       'Jakarta',    'DKI Jakarta',      -6.2640, 106.7833],
        ['paskal-23',              'Paskal 23 Mall',          'Bandung',    'Jawa Barat',       -6.9160, 107.5920],
        ['ciputra-world',          'Ciputra World',           'Surabaya',   'Jawa Timur',       -7.2910, 112.7170],
        ['pakuwon-mall',           'Pakuwon Mall',            'Surabaya',   'Jawa Timur',       -7.2890, 112.6650],
        ['beachwalk',              'Beachwalk Mall',          'Badung',     'Bali',             -8.7180, 115.1689],
        ['bali-galleria',          'Bali Galleria Mall',      'Badung',     'Bali',             -8.7262, 115.2384],
        ['summarecon-bekasi',      'Summarecon Mall Bekasi',  'Bekasi',     'Jawa Barat',       -6.2380, 106.9940],
        ['aeon-bsd',               'AEON Mall BSD',           'Tangerang Selatan','Banten',     -6.2980, 106.6640],
        // ── Universities ──
        ['universitas-indonesia',  'Universitas Indonesia',   'Depok',      'Jawa Barat',       -6.3610, 106.8200],
        ['ugm',                    'Universitas Gadjah Mada', 'Yogyakarta', 'D.I. Yogyakarta',  -7.7714, 110.3778],
        ['itb',                    'Institut Teknologi Bandung','Bandung',  'Jawa Barat',       -6.8915, 107.6107],
        ['unair',                  'Universitas Airlangga',   'Surabaya',   'Jawa Timur',       -7.2700, 112.7580],
        ['universitas-brawijaya',  'Universitas Brawijaya',   'Malang',     'Jawa Timur',       -7.9570, 112.6135],
        ['universitas-udayana',    'Universitas Udayana',     'Badung',     'Bali',             -8.6700, 115.2187],
        ['universitas-hasanuddin', 'Universitas Hasanuddin',  'Makassar',   'Sulawesi Selatan', -5.1327, 119.4908],
        ['universitas-diponegoro', 'Universitas Diponegoro',  'Semarang',   'Jawa Tengah',      -7.0048, 110.4354],
        // ── Hospitals ──
        ['rs-siloam',              'RS Siloam',               'Jakarta',    'DKI Jakarta',      -6.2000, 106.7820],
        ['rs-pondok-indah',        'RS Pondok Indah',         'Jakarta',    'DKI Jakarta',      -6.2650, 106.7850],
        ['rs-mayapada',            'RS Mayapada',             'Jakarta',    'DKI Jakarta',      -6.1920, 106.7780],
        ['rs-sanglah',             'RS Sanglah',              'Denpasar',   'Bali',             -8.6750, 115.2125],
        ['rs-hasan-sadikin',       'RS Hasan Sadikin',        'Bandung',    'Jawa Barat',       -6.8970, 107.6060],
        // ── Stadiums ──
        ['gelora-bung-karno',      'Gelora Bung Karno',       'Jakarta',    'DKI Jakarta',      -6.2186, 106.8023],
        ['gelora-bung-tomo',       'Gelora Bung Tomo',        'Surabaya',   'Jawa Timur',       -7.2290, 112.6190],
        ['jis',                    'Jakarta International Stadium','Jakarta','DKI Jakarta',     -6.1240, 106.8560],
        ['maguwoharjo',            'Stadion Maguwoharjo',     'Sleman',     'D.I. Yogyakarta',  -7.7760, 110.4150],
        ['kanjuruhan',             'Stadion Kanjuruhan',      'Malang',     'Jawa Timur',       -8.1520, 112.5710],
        // ── Convention Centers ──
        ['jcc-senayan',            'JCC Senayan',             'Jakarta',    'DKI Jakarta',      -6.2148, 106.8064],
        ['ji-expo',                'JIExpo Kemayoran',        'Jakarta',    'DKI Jakarta',      -6.1460, 106.8510],
        ['ice-bsd-city',           'ICE BSD',                 'Tangerang Selatan','Banten',     -6.2980, 106.6500],
        ['bcck',                   'Balai Sidang Jakarta',    'Jakarta',    'DKI Jakarta',      -6.2140, 106.8040],
        // ── Religious Sites ──
        ['masjid-istiqlal-jakarta', 'Masjid Istiqlal',         'Jakarta',    'DKI Jakarta',      -6.1698, 106.8318],
        ['gereja-katedral',        'Gereja Katedral',         'Jakarta',    'DKI Jakarta',      -6.1690, 106.8328],
        ['masjid-agung-demak',     'Masjid Agung Demak',      'Demak',      'Jawa Tengah',      -6.8970, 110.6360],
        ['masjid-istiqlal-semarang','Masjid Agung Semarang',  'Semarang',   'Jawa Tengah',      -6.9900, 110.4220],
        ['masjid-raya-medan-landmark','Masjid Raya Medan',    'Medan',      'Sumatera Utara',    3.5750,  98.6872],
        // ── Natural Attractions ──
        ['curug-lawe',             'Curug Lawe',              'Semarang',   'Jawa Tengah',      -7.1200, 110.3600],
        ['telaga-sarangan',        'Telaga Sarangan',         'Magetan',    'Jawa Timur',       -7.6740, 111.2150],
        ['ranu-kumbolo',           'Ranu Kumbolo',            'Lumajang',   'Jawa Timur',       -8.0420, 112.9100],
        ['danau-linow',            'Danau Linow',             'Tomohon',    'Sulawesi Utara',    1.2800, 124.8530],
        ['danau-kaco',             'Danau Kaco',              'Kerinci',    'Jambi',            -2.0960, 101.4960],
        ['gumuk-pasir',            'Gumuk Pasir Parangkusumo','Bantul',     'D.I. Yogyakarta',  -8.0300, 110.3200],
        ['air-terjun-madakaripura','Air Terjun Madakaripura', 'Probolinggo','Jawa Timur',       -7.7870, 112.9840],
        ['pantai-ora',             'Pantai Ora',              'Maluku Tengah','Maluku',         -2.8120, 129.5740],
        ['pulau-derawan-landmark', 'Kepulauan Derawan',       'Berau',      'Kalimantan Timur',  2.2858, 118.2441],
        // ── Historical / Cultural Sites ──
        ['candi-mendut',           'Candi Mendut',            'Magelang',   'Jawa Tengah',      -7.6040, 110.2290],
        ['candi-pawon',            'Candi Pawon',             'Magelang',   'Jawa Tengah',      -7.6060, 110.2190],
        ['museum-angkut-batu',     'Museum Angkut Batu',      'Batu',       'Jawa Timur',       -7.8805, 112.5238],
        ['museum-fatahillah',      'Museum Fatahillah',       'Jakarta',    'DKI Jakarta',      -6.1350, 106.8133],
        ['ruma-creat',             'Rumah Kreatif BUMN',      'Jakarta',    'DKI Jakarta',      -6.1850, 106.8230],
        ['kampung-warna-warni',    'Kampung Warna-Warni',     'Malang',     'Jawa Timur',       -7.9850, 112.6370],
    ];

    /** 80+ neighborhood slug per kota besar. */
    public const NEIGHBORHOODS = [
        'jakarta'   => [
            'kemang', 'menteng', 'sudirman', 'kuningan', 'kelapa-gading', 'pondok-indah',
            'pluit', 'sunter', 'cempaka-putih', 'rawamangun', 'pulo-gadung', 'cakung',
            'duren-sawit', 'kramat-jati', 'pasar-minggu', 'jagakarsa', 'cilandak',
            'kebayoran-baru', 'palmerah', 'grogol', 'taman-sari', 'cengkareng',
            'kalideres', 'tebet', 'manggarai', 'senayan', 'tharmin', 'pancoran',
        ],
        'bandung'   => [
            'dago', 'cihampelas', 'riau', 'pasteur', 'setiabudi', 'lembang',
            'ciumbuleit', 'sukajadi', 'kiaracondong', 'buah-batu', 'antapani',
            'cibiru', 'ujungberung', 'gedung-sate', 'braga', 'cicaheum',
        ],
        'surabaya'  => [
            'tunjungan', 'gubeng', 'darmo', 'rungkut', 'pakuwon', 'sukolilo',
            'manyar', 'kenjeran', 'dukuh-pakis', 'wonokromo', 'wonocolo',
            'gayungan', 'gunung-anyar', 'mulyorejo', 'tambaksari', 'sawahan',
        ],
        'yogyakarta'=> [
            'malioboro', 'prawirotaman', 'tugu', 'sleman', 'bantul', 'kaliurang',
            'kotagede', 'pakualaman', 'gondokusuman', 'ngaglik', 'depok-sleman',
            'godean', 'kasihan', 'ngemplak',
        ],
        'denpasar'  => ['sanur', 'renon', 'kuta', 'legian'],
        'ubud'      => ['monkey-forest', 'penestanan', 'tegallalang', 'sayn', 'kedewatan'],
        'seminyak'  => ['oberoi', 'petitenget', 'kerobokan', 'double-six', 'batubelig'],
        'canggu'    => ['echo-beach', 'berawa', 'pererenan', 'nelayan', 'batu-bolong'],
        'kuta'      => ['kuta-square', 'legian', 'tuban', 'kartika-plaza'],
        'sanur'     => ['sindhu', 'semawang', 'mertasari'],
        'nusa-dua'  => ['btcd', 'tanjung-benoa', 'sawangan'],
        'jimbaran'  => ['jimbaran-bay', 'kedonganan', 'bukit-jimbaran'],
        'uluwatu'   => ['pecatu', 'bingin', 'balangan', 'ungasan'],
        'medan'     => [
            'polonia', 'medan-baru', 'medan-petisah', 'medan-sunggal', 'medan-johor',
            'medan-tembung', 'helvetia', 'medan-marelan', 'medan-area',
        ],
        'semarang'  => [
            'simpang-lima', 'tembalang', 'kota-lama', 'candi', 'pedurungan',
            'banyumanik', 'semarang-barat', 'semarang-utara', 'gayamsari',
        ],
        'solo'      => ['solo-baru', 'jebres', 'laweyan', 'banjarsari', 'pasar-kliwon'],
        'malang'    => ['batu', 'klojen', 'soekarno-hatta', 'bledug', 'lowokwaru', 'sukun'],
        'makassar'  => [
            'panakkukang', 'losari', 'tanjung-bunga', 'rappocini', 'tamalate',
            'biringkanaya', 'mariso', 'ujung-pandang', 'wajo',
        ],
        'lombok'    => ['senggigi', 'kuta-lombok', 'mandalika', 'bangsal', 'teluk-nare'],
        'palembang' => ['ilir-barat', 'ilir-timur', 'seberang-ulu', 'sako', 'sukarami'],
        'batam'     => ['nagoya', 'batam-center', 'sekapang', 'nongsa', 'bengkong'],
        'bali'      => [
            'kuta', 'seminyak', 'canggu', 'ubud', 'sanur', 'nusa-dua', 'jimbaran',
            'uluwatu', 'amed', 'candidasa', 'lovina', 'pemuteran',
        ],
        'pekanbaru' => ['panam', 'marpoyan', 'sukajadi', 'tampan', 'limat-puluh', 'payung-sekaki'],
        'padang'    => ['padang-barat', 'padang-timur', 'padang-selatan', 'padang-utara', 'lubuk-begalung'],
        'pontianak' => ['pontianak-barat', 'pontianak-timur', 'pontianak-selatan', 'pontianak-utara', 'kubu-raya'],
        'banjarmasin'=> ['banjarmasin-tengah', 'banjarmasin-barat', 'banjarmasin-timur', 'banjarmasin-selatan', 'banjarmasin-utara'],
        'samarinda' => ['samarinda-ilir', 'samarinda-ulu', 'sungai-pinang', 'loa-janan', 'palaran'],
        'balikpapan' => ['balikpapan-selatan', 'balikpapan-timur', 'balikpapan-barat', 'balikpapan-utara', 'balikpapan-tengah'],
        'manado'    => ['wenang', 'tuminting', 'singkil', 'tikala', 'sario', 'malalayang', 'bunaken-kepulauan'],
        'ambon'     => ['sirimau', 'nusaniwe', 'teluk-ambon', 'baguala', 'leitimur-selatan'],
        'jayapura'  => ['jayapura-selatan', 'jayapura-utara', 'abepura', 'muara-tami', 'heram'],
        'kupang'    => ['kelapa-lima', 'oebobo', 'maulafa', 'alak', 'kota-lama-kupang'],
        'bengkulu'  => ['gading-cempaka', 'ratu-samban', 'ratu-agung', 'muara-bangkahulu', 'teluk-segara'],
        'jambi'     => ['telanaipura', 'pasar-jambi', 'danau-teluk', 'kotabaru-jambi', 'alam-barajo'],
        'palu'      => ['palu-timur', 'palu-selatan', 'palu-barat', 'palu-utara', 'tatanga'],
        'kendari'   => ['kendari-barat', 'mandonga', 'poasia', 'baruga', 'puwatu', 'kadia'],
        'gorontalo' => ['kota-timur', 'kota-barat', 'kota-selatan', 'dungingi', 'hulonthalangi'],
        'ternate'   => ['ternate-selatan', 'ternate-utara', 'motikota', 'pulau-ternate', 'batang-dua'],
        'sorong'    => ['sorong-barat', 'sorong-timur', 'sorong-manoi', 'sorong-klaurung', 'malaimsimsa'],
        'labuan-bajo'=> ['labuan-bajo-kota', 'batu-cermin', 'wae-kelambu', 'gorontalo-labuan', 'macang-tanggong'],
        'tangerang' => ['karawaci', 'ciledug', 'cipondoh', 'pinang', 'larangan', 'batuceper'],
        'cirebon'   => ['kejaksan', 'kesambi', 'pekalipan', 'harjamukti', 'lemahwungkuk'],
        'tasikmalaya'=> ['cihideung', 'tawang', 'mangkubumi', 'indihiang', 'cibeureum'],
        'banyuwangi'=> ['banyuwangi-kota', 'rogojampi', 'glenmore', 'genteng', 'cluring'],
        'jember'    => ['sumbersari', 'kaliwates', 'patrang', 'ajung', 'rambipuji'],
        'pandeglang' => ['pandeglang-kota', 'carita', 'labuan', 'panimbang', 'munjul'],
        'bogor'     => [
            'bogor-tengah', 'bogor-barat', 'bogor-utara', 'bogor-timur', 'bogor-selatan',
            'tanah-sareal', 'ciomas', 'cibinong', 'sukaraja', 'puncak',
        ],
        'depok'     => ['margonda', 'beji', 'pancoran-mas', 'sawangan', 'cimanggis', 'limo', 'cinere'],
        'bekasi'    => ['bekasi-timur', 'bekasi-barat', 'bekasi-utara', 'bekasi-selatan', 'rawalumbu', 'pondok-gede', 'jatiasih'],
        'batam'     => ['nagoya', 'batam-center', 'sekapang', 'nongsa', 'bengkong'],
        'bintan'    => ['tanjung-pinang-kota', 'teluk-sebong', 'bintan-timur', 'gunung-kijang', 'lagoi'],
    ];

    /** Kategori "best-{category}". */
    public const CATEGORIES = [
        'villa', 'family-room', 'suite', 'budget-hotel', 'boutique-hotel',
        'business-hotel', 'resort', 'beachfront-hotel', 'mountain-resort',
        'eco-lodge', 'pet-friendly-hotel', 'romantic-getaway',
    ];

    /** Tier harga. */
    public const PRICE_TIERS = ['300k', '500k', '1jt', '2jt'];

    /** Extended price points for granular pages. */
    public const GRANULAR_PRICES = ['100rb', '150rb', '200rb', '250rb', '300rb', '400rb', '500rb', '750rb', '1jt'];

    /** Occasion untuk occasion-stay. */
    public const OCCASIONS = ['honeymoon', 'family', 'business', 'romantic', 'wedding'];

    /** Extended occasions — 15 total. */
    public const ALL_OCCASIONS = [
        'honeymoon', 'family', 'business', 'romantic', 'wedding',
        'backpacking', 'staycation', 'workation', 'long-stay', 'short-stay',
        'luxury', 'budget', 'weekend-getaway', 'liburan-sekolah', 'trip-teman',
    ];

    /** Room types untuk /kamar-{type}-{city}. */
    public const ROOM_TYPES = [
        'standard', 'superior', 'deluxe', 'junior-suite', 'executive-suite',
        'presidential-suite', 'family-room', 'twin-room', 'single-room',
        'connecting-room', 'accessible-room', 'honeymoon-suite',
    ];

    /** Guest types untuk /hotel-untuk-{type}-{city}. */
    public const GUEST_TYPES = ['pasangan', 'keluarga', 'solo-traveler', 'rombongan', 'bisnis'];

    /** Seasons untuk /hotel-{city}-musim-{season}. */
    public const SEASONS = ['kemarau', 'hujan'];

    /** Holidays untuk /hotel-{city}-liburan-{holiday}. */
    public const HOLIDAYS = ['lebaran', 'natal', 'tahun-baru', 'imlek', 'waisak', 'galungan'];

    /** Distance tiers (km) untuk /hotel-{city}-jarak-{distance}-km-dari-pusat. */
    public const DISTANCES = ['1', '2', '3', '5', '10'];

    /** Question slug types untuk question-based pages. */
    public const QUESTIONS = ['aman-untuk-wisatawan', 'waktu-terbaik-ke', 'biaya-hotel-di', 'cara-ke', 'wisata-di'];

    /** Villa features. */
    public const VILLA_FEATURES = ['private-pool', 'ocean-view', 'rice-paddy-view', 'beachfront', 'jacuzzi'];

    /** Star ratings. */
    public const STARS = [1, 2, 3, 4, 5];

    /** Amenity filter. */
    public const AMENITIES = ['kolam-renang', 'sarapan-gratis', 'parkir-luas', 'ramah-keluarga', 'untuk-backpacker'];

    /** Short landmark slugs. */
    public const SHORT_LANDMARKS = [
        'monas', 'borobudur', 'prambanan', 'malioboro', 'kota-tua',
        'bromo', 'bali', 'raja-ampat', 'komodo', 'labuan-bajo',
        'ubud', 'kuta-beach', 'seminyak', 'nusa-dua', 'lembang',
        'ijen', 'dieng', 'tanah-lot', 'uluwatu', 'jimbaran',
    ];

    public static function eventYears(): array { $y = (int) date('Y'); return [$y - 1, $y, $y + 1]; }
    public const MONTHS = [
        'januari', 'februari', 'maret', 'april', 'mei', 'juni',
        'juli', 'agustus', 'september', 'oktober', 'november', 'desember',
    ];
    public const SEARCH_OCCASIONS = ['honeymoon', 'family', 'business', 'romantic', 'backpacker', 'budget', 'luxury', 'staycation', 'workation', 'wedding', 'weekend-getaway'];

    /** Top 50 cities for combinatorial compare. */
    public static function compareCities(): array
    {
        $top = [
            'jakarta', 'bandung', 'surabaya', 'yogyakarta', 'bali', 'denpasar',
            'medan', 'semarang', 'solo', 'malang', 'makassar', 'palembang',
            'padang', 'pekanbaru', 'banjarmasin', 'samarinda', 'balikpapan',
            'pontianak', 'manado', 'lombok', 'batam', 'bogor', 'depok',
            'tangerang', 'bekasi', 'batu', 'kediri', 'banyuwangi', 'jember',
            'cirebon', 'kupang', 'ambon', 'jayapura', 'sorong', 'labuan-bajo',
            'mataram', 'bukittinggi', 'lampung', 'jambi', 'bengkulu',
            'purwokerto', 'tegal', 'pekalongan', 'kudus', 'sukabumi',
            'tasikmalaya', 'garut', 'magelang', 'salatiga', 'ubud',
        ];
        $pairs = [];
        foreach ($top as $i => $c1) {
            foreach (array_slice($top, $i + 1) as $c2) {
                $pairs[] = [$c1, $c2];
            }
        }
        return $pairs;
    }

    /** Price range combos for top 30 cities: min × max in 50k increments. */
    public static function priceRanges(): array
    {
        $mins = [100, 150, 200, 250, 300, 400, 500];
        $maxs = [200, 300, 400, 500, 600, 750, 1000, 1500];
        $topCities = array_slice(array_keys(self::CITIES), 0, 35);
        $combos = [];
        foreach ($topCities as $city) {
            foreach ($mins as $min) {
                foreach ($maxs as $max) {
                    if ($max > $min) {
                        $combos[] = [$city, $min, $max];
                    }
                }
            }
        }
        return $combos;
    }

    public static function cityName(string $slug): ?string { return self::CITIES[$slug] ?? null; }
    public static function isCity(string $slug): bool { return array_key_exists($slug, self::CITIES); }

    // ═══ URL generators for sitemap ═══

    public static function allCityUrls(): array
    {
        $urls = [];
        foreach (array_keys(self::CITIES) as $city) {
            $urls[] = "/hotels-in-{$city}";
            $urls[] = "/best-time-to-visit-{$city}";
            $urls[] = "/pet-friendly-hotels-{$city}";
            foreach (self::years() as $y) { $urls[] = "/best-hotels-{$city}-{$y}"; }
            foreach (self::PRICE_TIERS as $p) { $urls[] = "/hotels-under-{$p}-{$city}"; }
            foreach (self::NEIGHBORHOODS[$city] ?? [] as $n) { $urls[] = "/hotels-in-{$city}-{$n}"; }
        }
        return $urls;
    }

    public static function allStarCityUrls(): array
    {
        $urls = [];
        foreach (self::STARS as $star) {
            foreach (array_keys(self::CITIES) as $city) { $urls[] = "/hotel-{$star}-bintang-{$city}"; }
        }
        return $urls;
    }

    public static function allPriceCityUrls(): array
    {
        $urls = [];
        foreach (array_keys(self::CITIES) as $city) {
            $urls[] = "/hotel-murah-{$city}";
            $urls[] = "/hotel-termurah-di-{$city}";
        }
        return $urls;
    }

    public static function allGranularPriceUrls(): array
    {
        $urls = [];
        foreach (self::GRANULAR_PRICES as $p) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/hotel-{$city}-di-bawah-{$p}";
            }
        }
        return $urls;
    }

    public static function allPriceRangeUrls(): array
    {
        $urls = [];
        foreach (self::priceRanges() as [$city, $min, $max]) {
            $urls[] = "/hotel-{$city}-{$min}-{$max}-ribu";
        }
        return $urls;
    }

    public static function allNearbyUrls(): array
    {
        $urls = [];
        foreach (array_keys(self::CITIES) as $city) {
            $urls[] = "/hotel-{$city}-dekat-bandara";
            $urls[] = "/hotel-{$city}-dekat-stasiun";
        }
        return $urls;
    }

    public static function allAmenityUrls(): array
    {
        $urls = [];
        foreach (array_keys(self::CITIES) as $city) {
            foreach (self::AMENITIES as $a) { $urls[] = "/hotel-{$city}-{$a}"; }
        }
        return $urls;
    }

    public static function allShortLandmarkUrls(): array
    {
        return array_map(fn ($lm) => "/hotel-dekat-{$lm}", self::SHORT_LANDMARKS);
    }

    public static function allAltAccommodationUrls(): array
    {
        $urls = [];
        foreach (['penginapan', 'apartemen', 'villa', 'guesthouse'] as $t) {
            foreach (array_keys(self::CITIES) as $city) { $urls[] = "/{$t}-{$city}"; }
        }
        return $urls;
    }

    public static function allContentUrls(): array
    {
        $urls = [];
        foreach (array_keys(self::CITIES) as $city) {
            $urls[] = "/tips-memilih-hotel-{$city}";
            $urls[] = "/panduan-wisata-{$city}";
        }
        return $urls;
    }

    public static function allWeatherUrls(): array
    {
        $urls = [];
        foreach (array_keys(self::CITIES) as $city) {
            foreach (self::MONTHS as $month) { $urls[] = "/cuaca-{$city}-bulan-{$month}"; }
        }
        return $urls;
    }

    public static function allEventUrls(): array
    {
        $urls = [];
        foreach (array_keys(self::CITIES) as $city) {
            foreach (self::eventYears() as $year) { $urls[] = "/event-{$city}-{$year}"; }
        }
        return $urls;
    }

    public static function allRecommendationUrls(): array
    {
        $urls = [];
        foreach (self::SEARCH_OCCASIONS as $occ) {
            foreach (array_keys(self::CITIES) as $city) { $urls[] = "/rekomendasi-hotel-{$occ}-{$city}"; }
        }
        return $urls;
    }

    public static function allAreaUrls(): array
    {
        $urls = [];
        foreach (self::NEIGHBORHOODS as $city => $neighborhoods) {
            foreach ($neighborhoods as $n) { $urls[] = "/area-{$n}-{$city}"; }
        }
        return $urls;
    }

    public static function allPopularNewUrls(): array
    {
        $urls = [];
        foreach (array_keys(self::CITIES) as $city) {
            $urls[] = "/hotel-populer-di-{$city}";
            $urls[] = "/hotel-baru-di-{$city}";
        }
        return $urls;
    }

    public static function allLandmarkUrls(): array
    {
        $urls = [];
        foreach (self::LANDMARKS as [$slug, , $city]) {
            $citySlug = \Illuminate\Support\Str::slug($city);
            $urls[] = "/things-to-do-near-{$slug}";
            $urls[] = "/{$citySlug}-hotels-near-{$slug}";
        }
        return $urls;
    }

    public static function allBestCategoryUrls(): array
    {
        $urls = [];
        foreach (self::CATEGORIES as $c) {
            $urls[] = "/best-{$c}";
            foreach (self::years() as $y) { $urls[] = "/best-{$c}-{$y}"; }
        }
        return $urls;
    }

    public static function allVillaFeatureUrls(): array
    {
        $urls = [];
        foreach (self::VILLA_FEATURES as $f) {
            foreach (array_keys(self::CITIES) as $loc) { $urls[] = "/villas-with-{$f}-{$loc}"; }
        }
        return $urls;
    }

    // ═══ NEW: Expanded URL generators ═══

    public static function allRoomTypeCityUrls(): array
    {
        $urls = [];
        foreach (self::ROOM_TYPES as $rt) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/kamar-{$rt}-{$city}";
                $urls[] = "/harga-kamar-{$rt}-{$city}";
            }
        }
        return $urls;
    }

    public static function allGuestTypeCityUrls(): array
    {
        $urls = [];
        foreach (self::GUEST_TYPES as $gt) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/hotel-untuk-{$gt}-{$city}";
            }
        }
        return $urls;
    }

    public static function allSeasonCityUrls(): array
    {
        $urls = [];
        foreach (self::SEASONS as $s) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/hotel-{$city}-musim-{$s}";
            }
        }
        return $urls;
    }

    public static function allHolidayCityUrls(): array
    {
        $urls = [];
        foreach (self::HOLIDAYS as $h) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/hotel-{$city}-liburan-{$h}";
            }
        }
        return $urls;
    }

    public static function allDistanceCityUrls(): array
    {
        $urls = [];
        foreach (self::DISTANCES as $d) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/hotel-{$city}-jarak-{$d}-km-dari-pusat";
            }
        }
        return $urls;
    }

    public static function allDistanceLandmarkUrls(): array
    {
        $urls = [];
        foreach (array_slice(self::SHORT_LANDMARKS, 0, 20) as $lm) {
            foreach (['500m', '1km', '2km', '5km'] as $d) {
                $urls[] = "/hotel-dekat-{$lm}-jarak-{$d}";
            }
        }
        return $urls;
    }

    public static function allQuestionUrls(): array
    {
        $urls = [];
        $prefixes = [
            'apakah' => 'aman-untuk-wisatawan',
            'kapan' => 'waktu-terbaik-ke',
            'berapa' => 'biaya-hotel-di',
            'bagaimana' => 'cara-ke',
            'apa-saja' => 'wisata-di',
        ];
        foreach ($prefixes as $prefix => $suffix) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/{$prefix}-{$city}-{$suffix}";
            }
        }
        return $urls;
    }

    public static function allCompareCityUrls(): array
    {
        $urls = [];
        foreach (self::compareCities() as [$c1, $c2]) {
            $urls[] = "/bandingkan-{$c1}-vs-{$c2}";
        }
        return $urls;
    }

    public static function allCompareNeighborhoodUrls(): array
    {
        $urls = [];
        foreach (self::NEIGHBORHOODS as $city => $neighborhoods) {
            if (count($neighborhoods) < 2) continue;
            foreach ($neighborhoods as $i => $n1) {
                foreach (array_slice($neighborhoods, $i + 1, 4) as $n2) {
                    $urls[] = "/bandingkan-hotel-{$city}-{$n1}-vs-{$n2}";
                }
            }
        }
        return $urls;
    }

    public static function years(): array
    {
        $year = (int) date('Y');
        return [$year, $year + 1];
    }

    // ═══════════════════════════════════════════════════════════════════════
    // SOURCE CODE SELLING PSEO — 100K pages dedicated to source code sales
    // ═══════════════════════════════════════════════════════════════════════

    /** Source code selling keywords (35). */
    public const SOURCE_CODE_KEYWORDS = [
        'aplikasi-hotel', 'software-hotel', 'sistem-hotel', 'aplikasi-penginapan',
        'source-code-hotel', 'source-code-aplikasi-hotel', 'software-manajemen-hotel',
        'program-hotel', 'sistem-reservasi-hotel', 'aplikasi-front-office-hotel',
        'aplikasi-pos-hotel', 'software-akuntansi-hotel', 'aplikasi-channel-manager',
        'sistem-booking-engine', 'software-pemesanan-kamar', 'aplikasi-manajemen-tamu',
        'hotel-management-system', 'hotel-software-indonesia', 'aplikasi-hotel-laravel',
        'aplikasi-hotel-php', 'source-code-laravel-hotel', 'software-hotel-murah',
        'beli-aplikasi-hotel', 'jual-source-code-hotel', 'harga-aplikasi-hotel',
        'download-source-code-hotel', 'aplikasi-hotel-premium',
        'aplikasi-hotel-reservasi', 'hotel-pms-indonesia', 'aplikasi-hotel-online',
        'aplikasi-hotel-web', 'sistem-informasi-hotel',
        'program-reservasi-hotel', 'aplikasi-booking-hotel', 'website-hotel',
        'aplikasi-hotel-full-source-code', 'ERP-hotel', 'PMS-hotel', 'hotel-PMS-software',
    ];

    /** Price tiers for source code selling. */
    public const SC_PRICE_TIERS = ['1jt', '2jt', '5jt', '10jt', '15jt'];

    /** Top 100 cities for source code PSEO (high commercial intent). */
    public static function scCities(): array
    {
        return array_keys(array_slice(self::CITIES, 0, 100, true));
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ROOM FEATURES — 43 features for combinatorial pages
    // ═══════════════════════════════════════════════════════════════════════

    public const ROOM_FEATURES = [
        'ac', 'wifi', 'tv-kabel', 'bathtub', 'shower-panas', 'balkon',
        'pemandangan-kota', 'pemandangan-laut', 'pemandangan-gunung',
        'kolam-renang-pribadi', 'jacuzzi', 'mini-bar', 'coffee-maker',
        'meja-kerja', 'sofa', 'ruang-tamu', 'dapur-pribadi', 'microwave',
        'kulkas', 'setrika', 'hairdryer', 'kamar-mandi-dalam', 'toilet-duduk',
        'air-panas', 'handuk', 'perlengkapan-mandi', 'sarapan-termasuk',
        'antar-jemput-bandara', 'laundry', 'room-service-24-jam',
        'check-in-24-jam', 'check-out-flexible', 'bebas-rokok', 'merokok',
        'hewan-peliharaan', 'connecting-room', 'interconnecting',
        'disable-access', 'lantai-atas', 'lantai-bawah',
        'dekat-lift', 'dekat-kolam-renang', 'dekat-restoran',
    ];

    /** Top 20 feature combos (popular pairs). */
    public const FEATURE_COMBOS = [
        ['ac', 'wifi'], ['ac', 'bathtub'], ['ac', 'balkon'], ['ac', 'shower-panas'],
        ['wifi', 'bathtub'], ['wifi', 'balkon'], ['wifi', 'shower-panas'],
        ['bathtub', 'balkon'], ['bathtub', 'pemandangan-laut'],
        ['bathtub', 'shower-panas'], ['kolam-renang-pribadi', 'balkon'],
        ['kolam-renang-pribadi', 'pemandangan-laut'],
        ['kolam-renang-pribadi', 'pemandangan-gunung'], ['jacuzzi', 'bathtub'],
        ['jacuzzi', 'balkon'], ['dapur-pribadi', 'balkon'],
        ['dapur-pribadi', 'connecting-room'], ['sarapan-termasuk', 'antar-jemput-bandara'],
        ['sarapan-termasuk', 'laundry'], ['room-service-24-jam', 'check-in-24-jam'],
    ];

    /** Districts/kelurahan for key cities — 500+ entries for hyperlocal pages. */
    public const DISTRICTS = [
        'jakarta' => [
            'menteng', 'tebet', 'setiabudi', 'kebayoran-baru', 'pasar-minggu', 'cilandak',
            'jagakarsa', 'pancoran', 'mampang', 'kemang', 'blok-m', 'sudirman', 'thamrin',
            'kuningan', 'grogol', 'taman-sari', 'tambora', 'cengkareng', 'kalideres',
            'pademangan', 'kelapa-gading', 'cilincing', 'koja', 'tanjung-priok',
            'pulo-gadung', 'cakung', 'duren-sawit', 'jatinegara', 'kramat-jati',
            'ciracas', 'pasar-rebo', 'makasar', 'cipayung', 'pademangan-timur',
            'penjaringan', 'taman-sari', 'gambir', 'sawah-besar', 'kemayoran',
            'senen', 'cempaka-putih', 'johar-baru', 'tanah-abang', 'menteng-dalam',
            'pegangsaan', 'rawamangun', 'matraman', 'palmerah', 'kebon-jeruk',
        ],
        'bandung' => [
            'dago', 'ciumbuleuit', 'cihampelas', 'pasteur', 'sukajadi', 'setiabudi',
            'lembang', 'kiaracondong', 'buah-batu', 'antapani', 'cibiru', 'ujung-berung',
            'gedebage', 'rancaekek', 'cimahi', 'soreang', 'dayeuhkolot', 'bojongsoang',
            'margaasih', 'batununggal', 'regol', 'lengkong', 'cicendo', 'andung',
            'astana-anyar', 'babakan-ciparay', 'bojongloa-kaler', 'bojongloa-kidul',
            'bandung-kulon', 'coblong', 'sumur-bandung', 'cibeunying-kaler',
            'cibeunying-kidul', 'mandalajati', 'arcamanik', 'rancasari', 'cinambo',
        ],
        'surabaya' => [
            'tunjungan', 'gubeng', 'darmo', 'rungkut', 'pakuwon', 'sukolilo',
            'manyar', 'kenjeran', 'dukuh-pakis', 'wonokromo', 'wonocolo',
            'gayungan', 'gunung-anyar', 'mulyorejo', 'tambaksari', 'sawahan',
            'bubutan', 'bubutan-surabaya', 'genteng', 'tegalsari', 'simokerto',
            'pabean-cantikan', 'semampir', 'krembangan', 'asem-rowo', 'tandes',
            'benowo', 'lakarsantri', 'karang-pilang', 'jambangan', 'wiyung',
        ],
        'yogyakarta' => [
            'malioboro', 'prawirotaman', 'tugu', 'sleman', 'bantul', 'kaliurang',
            'kotagede', 'pakualaman', 'gondokusuman', 'ngaglik', 'depok-sleman',
            'godean', 'kasihan', 'ngemplak', 'gedong-tengen', 'danurejan',
            'gondomanan', 'ngampilan', 'wirobrajan', 'mantrijeron', 'mergangsan',
            'umbulharjo', 'kotagede-yogya', 'jetis', 'tegalrejo', 'mlati',
            'gamping', 'berbah', 'prambanan-sleman', 'kalasan', 'ngaglik-sleman',
        ],
        'bali' => [
            'kuta', 'seminyak', 'canggu', 'ubud', 'sanur', 'nusa-dua', 'jimbaran',
            'uluwatu', 'amed', 'candidasa', 'lovina', 'pemuteran', 'legian',
            'kerobokan', 'petitenget', 'berawa', 'pererenan', 'batu-bolong',
            'sayan', 'kedewatan', 'tegalalang', 'gianyar', 'klungkung', 'karangasem',
            'bangli', 'tabanan', 'jembrana', 'buleleng', 'negara', 'singaraja-kota',
        ],
        'medan' => [
            'polonia', 'medan-baru', 'medan-petisah', 'medan-sunggal', 'medan-johor',
            'medan-tembung', 'helvetia', 'medan-marelan', 'medan-area', 'medan-kota',
            'medan-barat', 'medan-timur', 'medan-perjuangan', 'medan-labuhan',
            'medan-belawan', 'medan-amplas', 'medan-denai', 'medan-selayang',
            'medan-maimun', 'medan-polonia', 'medan-tuntungan', 'medan-helvetia',
            'percunt-sei-tuan', 'sunggal', 'delitua', 'patumbak', 'tanjung-morawa',
        ],
        'semarang' => [
            'simpang-lima', 'tembalang', 'kota-lama', 'candi', 'pedurungan',
            'banyumanik', 'semarang-barat', 'semarang-utara', 'gayamsari',
            'semarang-tengah', 'semarang-timur', 'semarang-selatan', 'ngaliyan',
            'genuk', 'tugu', 'gunungpati', 'mijen', 'gajah-mungkur',
            'candisari', 'tuntang', 'ungaran-barat', 'ungaran-timur', 'bergas',
        ],
        'solo' => [
            'solo-baru', 'jebres', 'laweyan', 'banjarsari', 'pasar-kliwon',
            'serengan', 'grogol-sukoharjo', 'kartasura', 'colomadu', 'baki',
            'ngemplak-solo', 'mojosongo', 'palur', 'jaten', 'wonogiri-kota',
        ],
        'malang' => [
            'batu', 'klojen', 'soekarno-hatta', 'bledug', 'lowokwaru', 'sukun',
            'kedung-kandang', 'dau', 'karang-ploso', 'singosari', 'pakis',
            'tumpang', 'wagir', 'ngantang', 'pujon', 'junrejo', 'bumiaji',
        ],
        'makassar' => [
            'panakkukang', 'losari', 'tanjung-bunga', 'rappocini', 'tamalate',
            'biringkanaya', 'mariso', 'ujung-pandang', 'wajo', 'ujung-tanah',
            'tallo', 'bontoala', 'mamajang', 'manggala', 'sangkarrang',
            'somba-opu', 'pallangga', 'barombong', 'tamalanrea',
        ],
        'palembang' => [
            'ilir-barat', 'ilir-timur', 'seberang-ulu', 'sako', 'sukarami',
            'kertapati', 'plaju', 'kalidoni', 'kemuning', 'bukit-kecil',
            'gandus', 'sematang-borang', 'alang-alang-lebar', 'jakabaring',
        ],
        'batam' => [
            'nagoya', 'batam-center', 'sekapang', 'nongsa', 'bengkong',
            'batu-ampar', 'batu-aji', 'lubuk-baja', 'sei-beduk', 'sagulung',
            'batam-kota', 'batu-selicin', 'belakang-padang', 'galang',
        ],
        'pekanbaru' => [
            'panam', 'marpoyan', 'sukajadi', 'tampan', 'limat-puluh', 'payung-sekaki',
            'sail', 'tenayan-raya', 'bukit-raya', 'rukun-tetangga',
            'senapelan', 'pekanbaru-kota', 'tuah-madani', 'kulim', 'binawidya',
        ],
        'padang' => [
            'padang-barat', 'padang-timur', 'padang-selatan', 'padang-utara',
            'lubuk-begalung', 'kuranji', 'nanggalo', 'bunut', 'lubuk-kilangan',
            'pauh', 'koto-tangah', 'bungus-teluk-kabung',
        ],
        'pontianak' => [
            'pontianak-barat', 'pontianak-timur', 'pontianak-selatan',
            'pontianak-utara', 'kubu-raya', 'sungai-raya', 'sui-ambawang',
            'rasau-jaya', 'sungai-kakap', 'kuala-mandor-b', 'mempawah',
        ],
        'banjarmasin' => [
            'banjarmasin-tengah', 'banjarmasin-barat', 'banjarmasin-timur',
            'banjarmasin-selatan', 'banjarmasin-utara', 'martapura-kota',
            'kertak-hanyar', 'gambut', 'aluh-aluh', 'tatah-makmur',
        ],
        'samarinda' => [
            'samarinda-ilir', 'samarinda-ulu', 'sungai-pinang', 'loa-janan',
            'palaran', 'sambutan', 'sungai-kunjang', 'loa-kulu',
            'muara-jawa', 'sanga-sanga', 'angkatan',
        ],
        'balikpapan' => [
            'balikpapan-selatan', 'balikpapan-timur', 'balikpapan-barat',
            'balikpapan-utara', 'balikpapan-tengah', 'balikpapan-kota',
            'samboja', 'sepinggan', 'batu-ampar-bpn', 'manggar',
        ],
        'manado' => [
            'wenang', 'tuminting', 'singkil', 'tikala', 'sario', 'malalayang',
            'bunaken-kepulauan', 'mapanget', 'wanea', 'paal-dua',
            'rano-tana', 'pineleng', 'airmadidi', 'kema', 'dimembe',
        ],
        'lombok' => [
            'senggigi', 'kuta-lombok', 'mandalika', 'bangsal', 'teluk-nare',
            'mataram-kota', 'praya', 'selong', 'tanjung', 'gerung',
            'kediri-lombok', 'narmada', 'labuapi', 'lembar', 'batulayar',
        ],
        'bogor' => [
            'bogor-tengah', 'bogor-barat', 'bogor-utara', 'bogor-timur',
            'bogor-selatan', 'tanah-sareal', 'ciomas', 'cibinong', 'sukaraja',
            'caringin', 'cijeroek', 'ciawi', 'megamendung', 'tajur', 'puncak',
        ],
        'depok' => [
            'margonda', 'beji', 'pancoran-mas', 'sawangan', 'cimanggis',
            'limo', 'cinere', 'cilodong', 'tapos', 'sukmajaya', 'bojongsari',
        ],
        'bekasi' => [
            'bekasi-timur', 'bekasi-barat', 'bekasi-utara', 'bekasi-selatan',
            'rawalumbu', 'pondok-gede', 'jatiasih', 'bantar-gebang',
            'jatibening', 'mustika-jaya', 'medan-satria', 'pondok-melati',
        ],
        'tangerang' => [
            'karawaci', 'ciledug', 'cipondoh', 'pinang', 'larangan', 'batuceper',
            'benda', 'jatiuwung', 'priuk', 'negla', 'kunciran',
            'tangerang-kota', 'periuk', 'cibodas', 'karang-tengah',
        ],
        'tangerang-selatan' => [
            'bsd', 'pamulang', 'serpong', 'serpong-utara', 'ciputat', 'ciputat-timur',
            'pondok-aren', 'setu', 'alam-sutera', 'bintaro',
        ],
    ];

    // ═══════════════════════════════════════════════════════════════════════
    // NEW URL GENERATORS — Massive combinatorial
    // ═══════════════════════════════════════════════════════════════════════

    /** Source code selling: /beli-{keyword} */
    public static function allSourceCodeUrls(): array
    {
        return array_map(fn ($kw) => "/beli-{$kw}", self::SOURCE_CODE_KEYWORDS);
    }

    /** Source code × city: /{keyword}-{city} */
    public static function allSourceCodeCityUrls(): array
    {
        $urls = [];
        foreach (self::SOURCE_CODE_KEYWORDS as $kw) {
            foreach (self::scCities() as $city) {
                $urls[] = "/{$kw}-{$city}";
            }
        }
        return $urls;
    }

    /** Source code × price: /harga-{keyword}-mulai-{price} */
    public static function allSourceCodePriceUrls(): array
    {
        $urls = [];
        foreach (self::SOURCE_CODE_KEYWORDS as $kw) {
            foreach (self::SC_PRICE_TIERS as $p) {
                $urls[] = "/harga-{$kw}-mulai-{$p}";
            }
        }
        return $urls;
    }

    /** Source code × city × price: /{keyword}-{city}-mulai-{price} */
    public static function allSourceCodeCityPriceUrls(): array
    {
        $urls = [];
        foreach (self::SOURCE_CODE_KEYWORDS as $kw) {
            foreach (self::scCities() as $city) {
                foreach (self::SC_PRICE_TIERS as $p) {
                    $urls[] = "/{$kw}-{$city}-mulai-{$p}";
                }
            }
        }
        return $urls;
    }

    /** Source code × download: /download-{keyword} */
    public static function allSourceCodeDownloadUrls(): array
    {
        return array_map(fn ($kw) => "/download-{$kw}", self::SOURCE_CODE_KEYWORDS);
    }

    /** Source code terbaik: /{keyword}-terbaik */
    public static function allSourceCodeBestUrls(): array
    {
        return array_map(fn ($kw) => "/{$kw}-terbaik", self::SOURCE_CODE_KEYWORDS);
    }

    /** Source code × city × murah: /{keyword}-{city}-murah */
    public static function allSourceCodeCityMurahUrls(): array
    {
        $urls = [];
        foreach (self::SOURCE_CODE_KEYWORDS as $kw) {
            foreach (self::scCities() as $city) {
                $urls[] = "/{$kw}-{$city}-murah";
            }
        }
        return $urls;
    }

    /** Jasa pembuatan × city: /jasa-pembuatan-{keyword}-{city} */
    public static function allSourceCodeJasaUrls(): array
    {
        $urls = [];
        foreach (self::SOURCE_CODE_KEYWORDS as $kw) {
            foreach (self::scCities() as $city) {
                $urls[] = "/jasa-pembuatan-{$kw}-{$city}";
            }
        }
        return $urls;
    }

    /** Paket × city: /paket-{keyword}-{city} */
    public static function allSourceCodePaketUrls(): array
    {
        $urls = [];
        foreach (self::SOURCE_CODE_KEYWORDS as $kw) {
            foreach (self::scCities() as $city) {
                $urls[] = "/paket-{$kw}-{$city}";
            }
        }
        return $urls;
    }

    /** Source code VS: /{kw1}-vs-{kw2} */
    public static function allSourceCodeVsUrls(): array
    {
        $urls = [];
        $kws = self::SOURCE_CODE_KEYWORDS;
        foreach ($kws as $i => $kw1) {
            foreach (array_slice($kws, $i + 1, 3) as $kw2) {
                $urls[] = "/{$kw1}-vs-{$kw2}";
            }
        }
        return $urls;
    }

    /** Source code × district: /{keyword}-{district}-{city} */
    public static function allSourceCodeDistrictUrls(): array
    {
        $urls = [];
        $topKws = array_slice(self::SOURCE_CODE_KEYWORDS, 0, 15);
        foreach ($topKws as $kw) {
            foreach (self::DISTRICTS as $city => $districts) {
                foreach (array_slice($districts, 0, 5) as $d) {
                    $urls[] = "/{$kw}-{$d}-{$city}";
                }
            }
        }
        return $urls;
    }

    /** Feature × city: /hotel-{city}-dengan-{feature} */
    public static function allFeatureCityUrls(): array
    {
        $urls = [];
        foreach (array_keys(self::CITIES) as $city) {
            foreach (self::ROOM_FEATURES as $f) {
                $urls[] = "/hotel-{$city}-dengan-{$f}";
            }
        }
        return $urls;
    }

    /** Double-feature × city: /hotel-{city}-{f1}-dan-{f2} */
    public static function allDoubleFeatureCityUrls(): array
    {
        $urls = [];
        foreach (array_keys(self::CITIES) as $city) {
            foreach (self::FEATURE_COMBOS as [$f1, $f2]) {
                $urls[] = "/hotel-{$city}-{$f1}-dan-{$f2}";
            }
        }
        return $urls;
    }

    /** Occasion × feature × city: /hotel-{occ}-{city}-dengan-{feature} */
    public static function allOccasionFeatureCityUrls(): array
    {
        $urls = [];
        $topFeatures = array_slice(self::ROOM_FEATURES, 0, 10);
        $topCities = array_slice(array_keys(self::CITIES), 0, 100);
        foreach (self::ALL_OCCASIONS as $occ) {
            foreach ($topFeatures as $f) {
                foreach ($topCities as $city) {
                    $urls[] = "/hotel-{$occ}-{$city}-dengan-{$f}";
                }
            }
        }
        return $urls;
    }

    /** Double-city: /hotel-{city1}-ke-{city2} */
    public static function allDoubleCityUrls(): array
    {
        $urls = [];
        $topCities = array_slice(array_keys(self::CITIES), 0, 60);
        foreach ($topCities as $c1) {
            foreach ($topCities as $c2) {
                if ($c1 !== $c2) {
                    $urls[] = "/hotel-{$c1}-ke-{$c2}";
                }
            }
        }
        return $urls;
    }

    /** City comparison expanded: /bandingkan-hotel-{c1}-vs-{c2} (more granular) */
    public static function allCompareCityExpandedUrls(): array
    {
        $urls = [];
        $top200 = array_slice(array_keys(self::CITIES), 0, 200);
        foreach ($top200 as $i => $c1) {
            foreach (array_slice($top200, $i + 1, 5) as $c2) {
                $urls[] = "/bandingkan-hotel-{$c1}-vs-{$c2}";
            }
        }
        return $urls;
    }

    /** Month×year archive: /hotel-{city}-{month}-{year} */
    public static function allMonthYearCityUrls(): array
    {
        $urls = [];
        $years = self::eventYears();
        $topCities = array_slice(array_keys(self::CITIES), 0, 120);
        foreach ($topCities as $city) {
            foreach ($years as $year) {
                foreach (self::MONTHS as $month) {
                    $urls[] = "/hotel-{$city}-{$month}-{$year}";
                }
            }
        }
        return $urls;
    }

    /** District-level hotel pages: /hotel-di-{district}-{city} */
    public static function allDistrictCityUrls(): array
    {
        $urls = [];
        foreach (self::DISTRICTS as $city => $districts) {
            foreach ($districts as $d) {
                $urls[] = "/hotel-di-{$d}-{$city}";
            }
        }
        return $urls;
    }

    /** Amenity × city × price: /{amenity}-{city}-{price} */
    public static function allAmenityCityPriceUrls(): array
    {
        $urls = [];
        $amenities = ['kolam-renang', 'sarapan-gratis', 'parkir-luas', 'ramah-keluarga', 'untuk-backpacker'];
        $prices = ['100rb', '200rb', '300rb', '400rb', '500rb', '750rb', '1jt'];
        foreach ($amenities as $a) {
            foreach (array_keys(self::CITIES) as $city) {
                foreach ($prices as $p) {
                    $urls[] = "/{$a}-{$city}-{$p}";
                }
            }
        }
        return $urls;
    }

    /** Star × price × city: /hotel-{star}-bintang-{city}-{price} */
    public static function allStarPriceCityUrls(): array
    {
        $urls = [];
        $prices = ['100rb', '200rb', '300rb', '500rb', '750rb', '1jt'];
        foreach (self::STARS as $star) {
            foreach (array_keys(self::CITIES) as $city) {
                foreach ($prices as $p) {
                    $urls[] = "/hotel-{$star}-bintang-{$city}-{$p}";
                }
            }
        }
        return $urls;
    }

    /** Guest type × feature × city: /hotel-{gt}-{city}-{feature} */
    public static function allGuestFeatureCityUrls(): array
    {
        $urls = [];
        $topFeatures = array_slice(self::ROOM_FEATURES, 0, 12);
        foreach (self::GUEST_TYPES as $gt) {
            foreach ($topFeatures as $f) {
                foreach (array_keys(self::CITIES) as $city) {
                    $urls[] = "/hotel-{$gt}-{$city}-{$f}";
                }
            }
        }
        return $urls;
    }

    /** Room type × feature × city: /kamar-{rt}-{city}-{feature} */
    public static function allRoomTypeFeatureCityUrls(): array
    {
        $urls = [];
        $topFeatures = array_slice(self::ROOM_FEATURES, 0, 8);
        $topCities = array_slice(array_keys(self::CITIES), 0, 150);
        foreach (self::ROOM_TYPES as $rt) {
            foreach ($topFeatures as $f) {
                foreach ($topCities as $city) {
                    $urls[] = "/kamar-{$rt}-{$city}-{$f}";
                }
            }
        }
        return $urls;
    }

    /** Extended price × city: /hotel-{city}-harga-{price} (combined patterns) */
    public static function allPriceCityExpandedUrls(): array
    {
        $urls = [];
        $priceTiers = ['100rb', '150rb', '200rb', '250rb', '300rb', '350rb', '400rb',
                       '450rb', '500rb', '600rb', '700rb', '750rb', '800rb', '900rb',
                       '1jt', '1-5jt', '2jt', '2-5jt', '3jt', '5jt', '7-5jt', '10jt'];
        foreach ($priceTiers as $p) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/hotel-{$city}-harga-{$p}";
            }
        }
        return $urls;
    }

    /** Hotel property comparison from DB (lazy generated in SitemapBuilder). */
    // Generated on-the-fly via SitemapBuilder::propertyCompareUrls()

    /** Content page × city × topic: /tips-{topic}-hotel-{city} for additional topics */
    public static function allContentTopicCityUrls(): array
    {
        $urls = [];
        $topics = ['booking', 'promo', 'refund', 'check-in', 'fasilitas', 'lokasi',
                   'review', 'cancel', 'upgrade', 'parkir', 'sarapan', 'pemandangan'];
        foreach ($topics as $topic) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/tips-{$topic}-hotel-{$city}";
            }
        }
        return $urls;
    }

    /** Source code page URL combos: high-volume fillers */
    public static function allSourceCodeMassiveUrls(): array
    {
        $urls = [];
        $kws = array_slice(self::SOURCE_CODE_KEYWORDS, 0, 20);
        $top60 = array_slice(array_keys(self::CITIES), 0, 60);
        foreach ($kws as $kw) {
            foreach ($top60 as $city) {
                $urls[] = "/beli-{$kw}-{$city}";
                $urls[] = "/harga-{$kw}-{$city}";
                $urls[] = "/source-code/{$kw}-{$city}";
            }
        }
        return $urls;
    }

    /** Final filler: massive expansion with keyword pairs × city × price × location */
    public static function allMegaFillerUrls(): array
    {
        $urls = [];
        $allCities = array_keys(self::CITIES);
        $patterns = [
            'aplikasi-hotel', 'software-hotel', 'sistem-hotel', 'penginapan',
            'hotel-murah', 'hotel-terbaik', 'hotel-modern', 'hotel-syariah',
            'hotel-keluarga', 'hotel-bisnis', 'hotel-resort', 'hotel-budget',
            'hotel-premium', 'hotel-luxury', 'hotel-baru', 'hotel-populer',
            'hotel-dekat', 'hotel-view', 'hotel-pusat', 'hotel-strategis',
        ];
        foreach ($patterns as $pat) {
            foreach ($allCities as $city) {
                $urls[] = "/{$pat}-{$city}";
                foreach (self::eventYears() as $y) {
                    $urls[] = "/{$pat}-{$city}-{$y}";
                }
                $urls[] = "/{$pat}-{$city}-murah";
            }
        }
        return $urls;
    }

    /** Second tier filler: target patterns for massive volume */
    public static function allSecondTierFillerUrls(): array
    {
        $urls = [];
        $allCities = array_keys(self::CITIES);
        $fillerPatterns = [
            'hotel-terdekat', 'hotel-termurah', 'hotel-terbaru', 'hotel-terfavorit',
            'hotel-rekomendasi', 'hotel-strategis', 'hotel-nyaman', 'hotel-bersih',
            'hotel-aman', 'hotel-modern', 'hotel-tradisional', 'hotel-syariah',
            'hotel-harian', 'hotel-mingguan', 'hotel-bulanan', 'hotel-transit',
            'hotel-ekonomi', 'hotel-menengah', 'hotel-atas', 'hotel-ekslusif',
        ];
        foreach ($fillerPatterns as $fp) {
            foreach ($allCities as $city) {
                $urls[] = "/{$fp}-{$city}";
            }
        }
        $years = self::eventYears();
        foreach ($fillerPatterns as $fp) {
            foreach ($allCities as $city) {
                foreach ($years as $y) {
                    $urls[] = "/{$fp}-{$city}-{$y}";
                }
            }
        }
        return $urls;
    }

    /** Third tier: massive combinatory filler to reach 1M */
    public static function allMassiveVolumeUrls(): array
    {
        $urls = [];
        $allCities = array_keys(self::CITIES);
        $hotelTypes = ['bintang', 'melati', 'butik', 'resor', 'kota', 'pantai',
                       'gunung', 'bisnis', 'keluarga', 'romantis', 'mewah', 'hemat',
                       'syariah', 'modern', 'tradisional', 'internasional', 'lokal',
                       'kapsul', 'hostel', 'motel', 'guest-house', 'homestay', 'villa',
                       'apartment', 'losmen', 'penginapan'];
        $tripTypes = ['liburan', 'bisnis', 'honeymoon', 'family-trip', 'backpacking',
                      'staycation', 'workation', 'short-trip', 'long-stay', 'transit',
                      'weekend', 'study-tour', 'company-gathering', 'reuni', 'outing',
                      'romantic-getaway', 'adventure', 'spiritual', 'culinary', 'belanja'];

        foreach ($hotelTypes as $ht) {
            foreach ($allCities as $city) {
                $urls[] = "/hotel-{$ht}-{$city}";
            }
        }
        foreach ($tripTypes as $tt) {
            foreach ($allCities as $city) {
                $urls[] = "/hotel-untuk-{$tt}-{$city}";
            }
        }

        // Additional massive cross-product: hotel type × city × year
        foreach (array_slice($hotelTypes, 0, 20) as $ht) {
            foreach ($allCities as $city) {
                foreach (self::eventYears() as $y) {
                    $urls[] = "/hotel-{$ht}-{$city}-{$y}";
                }
            }
        }

        // Trip type × city × year
        foreach ($tripTypes as $tt) {
            foreach ($allCities as $city) {
                foreach (self::eventYears() as $y) {
                    $urls[] = "/hotel-untuk-{$tt}-{$city}-{$y}";
                }
            }
        }

        // Hotel type × trip type × city (biggest generator)
        $topHt = array_slice($hotelTypes, 0, 12);
        $topTt = array_slice($tripTypes, 0, 8);
        foreach ($topHt as $ht) {
            foreach ($topTt as $tt) {
                foreach ($allCities as $city) {
                    $urls[] = "/hotel-{$ht}-untuk-{$tt}-{$city}";
                }
            }
        }

        return $urls;
    }

    /** Super mega filler #2 */
    public static function allSuperMegaUrls(): array
    {
        $urls = [];
        $allCities = array_keys(self::CITIES);
        $prefixes = [
            'rekomendasi-hotel', 'daftar-hotel', 'pilihan-hotel', 'rekomendasi-penginapan',
            'koleksi-hotel', 'panduan-hotel', 'review-hotel', 'cari-hotel',
            'booking-hotel', 'reservasi-hotel', 'pesan-hotel', 'cek-hotel',
            'hotel-pilihan', 'hotel-favorit', 'hotel-andalan', 'hotel-incaran',
        ];
        foreach ($prefixes as $p) {
            foreach ($allCities as $city) {
                $urls[] = "/{$p}-{$city}";
                foreach (self::eventYears() as $y) {
                    $urls[] = "/{$p}-{$city}-{$y}";
                }
            }
        }
        return $urls;
    }

    /** Super mega filler #3 */
    public static function allSuperMegaUrls2(): array
    {
        $urls = [];
        $allCities = array_keys(self::CITIES);
        $actionPrefixes = [
            'booking-cepat-hotel', 'reservasi-mudah-hotel', 'cek-harga-hotel',
            'info-hotel', 'jadwal-hotel', 'promo-hotel', 'diskon-hotel',
            'paket-hotel', 'hotel-plus', 'hotel-paket', 'hotel-deal',
            'hotel-promo', 'hotel-diskon', 'promo-spesial-hotel',
        ];
        foreach ($actionPrefixes as $ap) {
            foreach ($allCities as $city) {
                $urls[] = "/{$ap}-{$city}";
            }
        }
        return $urls;
    }

    /** Super mega filler #4: price-tier cityYear combos */
    public static function allPriceComboUrls(): array
    {
        $urls = [];
        $allCities = array_keys(self::CITIES);
        $pricePoints = ['100rb', '150rb', '200rb', '250rb', '300rb', '350rb', '400rb',
                        '450rb', '500rb', '600rb', '750rb', '800rb', '1jt', '1-5jt',
                        '2jt', '2-5jt', '3jt', '5jt'];
        $prefixes = ['hotel-dibawah', 'penginapan-dibawah', 'villa-dibawah', 'apartemen-dibawah'];
        foreach ($prefixes as $p) {
            foreach ($allCities as $city) {
                foreach ($pricePoints as $pp) {
                    $urls[] = "/{$p}-{$pp}-{$city}";
                }
            }
        }
        return $urls;
    }

    /** Super mega filler #5: star × price × city × year */
    public static function allStarCrossUrls(): array
    {
        $urls = [];
        $allCities = array_keys(self::CITIES);
        $prices = ['100rb', '200rb', '300rb', '500rb', '750rb', '1jt', '1-5jt'];
        foreach (self::STARS as $star) {
            foreach ($allCities as $city) {
                foreach ($prices as $p) {
                    $urls[] = "/hotel-bintang{$star}-{$city}-{$p}";
                }
            }
        }
        return $urls;
    }

    /** Super mega filler #6: status/label × city */
    public static function allStatusLabelUrls(): array
    {
        $urls = [];
        $allCities = array_keys(self::CITIES);
        $labels = [
            'hotel-rekomendasi', 'hotel-unggulan', 'hotel-terseleksi', 'hotel-terpercaya',
            'hotel-resmi', 'hotel-terverifikasi', 'hotel-berstandar', 'hotel-berkualitas',
            'hotel-profesional', 'hotel-terjamin', 'hotel-amanah', 'hotel-terhandal',
        ];
        foreach ($labels as $label) {
            foreach ($allCities as $city) {
                $urls[] = "/{$label}-{$city}";
            }
        }
        return $urls;
    }

    /** Massive #7: city-to-city bus/transportation routes */
    public static function allTransportRouteUrls(): array
    {
        $urls = [];
        $top80 = array_slice(array_keys(self::CITIES), 0, 80);
        $modes = ['pesawat', 'kereta', 'bus', 'travel', 'mobil', 'kapal'];
        foreach ($modes as $mode) {
            foreach ($top80 as $c1) {
                foreach ($top80 as $c2) {
                    if ($c1 !== $c2) {
                        $urls[] = "/{$mode}-{$c1}-{$c2}";
                    }
                }
            }
        }
        return $urls;
    }

    /** Massive #8: city × landmark × star combo */
    public static function allLandmarkStarCityUrls(): array
    {
        $urls = [];
        $topLandmarks = array_slice(self::SHORT_LANDMARKS, 0, 15);
        $topCities = array_slice(array_keys(self::CITIES), 0, 80);
        foreach (self::STARS as $star) {
            foreach ($topCities as $city) {
                foreach ($topLandmarks as $lm) {
                    $urls[] = "/hotel-bintang{$star}-{$city}-dekat-{$lm}";
                }
            }
        }
        return $urls;
    }

    /** Massive #9: city × occasion × landmark */
    public static function allOccasionLandmarkCityUrls(): array
    {
        $urls = [];
        $topOccasions = array_slice(self::ALL_OCCASIONS, 0, 8);
        $topLandmarks = array_slice(self::SHORT_LANDMARKS, 0, 10);
        $topCities = array_slice(array_keys(self::CITIES), 0, 80);
        foreach ($topOccasions as $occ) {
            foreach ($topCities as $city) {
                foreach ($topLandmarks as $lm) {
                    $urls[] = "/hotel-{$occ}-{$city}-dekat-{$lm}";
                }
            }
        }
        return $urls;
    }

    /** Massive #10: source code × feature combo */
    public static function allSourceCodeFeatureUrls(): array
    {
        $urls = [];
        $topKws = array_slice(self::SOURCE_CODE_KEYWORDS, 0, 10);
        $topFeatures = array_slice(self::ROOM_FEATURES, 0, 15);
        $topCities = array_slice(array_keys(self::CITIES), 0, 80);
        foreach ($topKws as $kw) {
            foreach ($topCities as $city) {
                foreach ($topFeatures as $f) {
                    $urls[] = "/{$kw}-{$city}-fitur-{$f}";
                }
            }
        }
        return $urls;
    }

    /** Massive #11: hotel × district × star */
    public static function allDistrictStarUrls(): array
    {
        $urls = [];
        foreach (self::DISTRICTS as $city => $districts) {
            foreach (array_slice($districts, 0, 10) as $d) {
                foreach (self::STARS as $star) {
                    $urls[] = "/hotel-bintang{$star}-di-{$d}-{$city}";
                }
            }
        }
        return $urls;
    }

    /** Massive #12: extended city comparison (all pairs) */
    public static function allCityPairsMassiveUrls(): array
    {
        $urls = [];
        $top150 = array_slice(array_keys(self::CITIES), 0, 150);
        foreach ($top150 as $i => $c1) {
            foreach (array_slice($top150, $i + 1, 3) as $c2) {
                $urls[] = "/hotel-{$c1}-atau-{$c2}";
                $urls[] = "/liburan-{$c1}-atau-{$c2}";
            }
        }
        return $urls;
    }

    /** Massive #13: hotel × city × various distance radius */
    public static function allDistanceRadiusUrls(): array
    {
        $urls = [];
        $distances = ['1km', '2km', '3km', '5km', '10km', '15km', '20km'];
        $centers = ['pusat-kota', 'bandara', 'stasiun', 'terminal', 'mall', 'alun-alun'];
        foreach ($centers as $center) {
            foreach (array_keys(self::CITIES) as $city) {
                foreach ($distances as $d) {
                    $urls[] = "/hotel-{$city}-dalam-{$d}-dari-{$center}";
                }
            }
        }
        return $urls;
    }

    /** Massive #14: hotel × all occassions × all cities */
    public static function allOccasionAllCityUrls(): array
    {
        $urls = [];
        foreach (self::ALL_OCCASIONS as $occ) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/hotel-{$occ}-di-{$city}";
                $urls[] = "/penginapan-{$occ}-di-{$city}";
            }
        }
        return $urls;
    }

    /** Massive #15: massive filling with × year combos */
    public static function allYearExtensionUrls(): array
    {
        $urls = [];
        $years = self::eventYears();
        $patterns = [
            'hotel-populer', 'hotel-favorit', 'hotel-rekomendasi', 'hotel-baru',
            'hotel-diskon', 'hotel-promo', 'hotel-spesial', 'hotel-edisi',
            'hotel-weekend', 'hotel-holiday', 'hotel-season', 'hotel-event',
        ];
        foreach ($patterns as $pat) {
            foreach (array_keys(self::CITIES) as $city) {
                foreach ($years as $y) {
                    $urls[] = "/{$pat}-{$city}-{$y}";
                }
            }
        }
        return $urls;
    }

    /** Massive #16: keyword × city × cheap/luxury tag */
    public static function allTagExpansionUrls(): array
    {
        $urls = [];
        $tags = ['murah', 'mahal', 'terbaik', 'termewah', 'terhemat', 'eksklusif',
                 'premium', 'standar', 'ekonomis', 'nyaman', 'bersih', 'aman'];
        foreach ($tags as $tag) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/hotel-{$tag}-{$city}";
                $urls[] = "/hotel-{$city}-{$tag}";
            }
        }
        return $urls;
    }

    /** Massive #17: month × city × year combos with more variations */
    public static function allMonthVariationUrls(): array
    {
        $urls = [];
        $years = self::eventYears();
        foreach (array_keys(self::CITIES) as $city) {
            foreach (self::MONTHS as $month) {
                foreach ($years as $y) {
                    $urls[] = "/hotel-{$city}-bulan-{$month}-{$y}";
                    $urls[] = "/liburan-{$city}-{$month}-{$y}";
                }
            }
        }
        return $urls;
    }

    /** Final push #1: sc keyword × ALL cities × ALL sc price tiers */
    public static function allSourceCodeFullCrossUrls(): array
    {
        $urls = [];
        foreach (self::SOURCE_CODE_KEYWORDS as $kw) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/{$kw}-website-{$city}";
                foreach (self::SC_PRICE_TIERS as $p) {
                    $urls[] = "/{$kw}-{$city}-harga-{$p}";
                }
                foreach (self::eventYears() as $y) {
                    $urls[] = "/{$kw}-{$city}-{$y}";
                }
            }
        }
        return $urls;
    }

    /** Final push #2: hotel type × district cross */
    public static function allHotelTypeDistrictUrls(): array
    {
        $urls = [];
        $hotelTypes = ['butik', 'resor', 'bisnis', 'keluarga', 'mewah', 'hemat',
                       'syariah', 'modern', 'tradisional', 'kapsul', 'hostel', 'motel'];
        foreach ($hotelTypes as $ht) {
            foreach (self::DISTRICTS as $city => $districts) {
                foreach (array_slice($districts, 0, 5) as $d) {
                    $urls[] = "/hotel-{$ht}-{$d}-{$city}";
                }
            }
        }
        return $urls;
    }

    /** Final push #3: occasion × hotel type × city */
    public static function allOccasionHotelTypeCityUrls(): array
    {
        $urls = [];
        $occasions = array_slice(self::ALL_OCCASIONS, 0, 10);
        $hotelTypes = ['butik', 'resor', 'bisnis', 'keluarga', 'mewah', 'hemat', 'syariah'];
        foreach ($occasions as $occ) {
            foreach ($hotelTypes as $ht) {
                foreach (array_keys(self::CITIES) as $city) {
                    $urls[] = "/hotel-{$occ}-{$ht}-{$city}";
                }
            }
        }
        return $urls;
    }

    /** Final push #4: /{keyword}-{city} for ALL source code keywords × ALL cities */
    public static function allFullKeywordCityUrls(): array
    {
        $urls = [];
        foreach (self::SOURCE_CODE_KEYWORDS as $kw) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/{$kw}-{$city}";
            }
        }
        return $urls;
    }

    /** Final push #5: room type × ALL cities × price */
    public static function allRoomTypeCityPriceUrls(): array
    {
        $urls = [];
        $prices = ['200rb', '300rb', '400rb', '500rb', '750rb', '1jt'];
        foreach (self::ROOM_TYPES as $rt) {
            foreach (array_keys(self::CITIES) as $city) {
                foreach ($prices as $p) {
                    $urls[] = "/kamar-{$rt}-{$city}-{$p}";
                }
            }
        }
        return $urls;
    }

    /** Final push #6: city comparison with all 200 cities every 5-pair skip */
    public static function allBulkCompareUrls(): array
    {
        $urls = [];
        $top200 = array_slice(array_keys(self::CITIES), 0, 200);
        foreach ($top200 as $i => $c1) {
            foreach (array_slice($top200, $i + 1, 8) as $c2) {
                $urls[] = "/perbandingan-hotel-{$c1}-{$c2}";
                $urls[] = "/hotel-{$c1}-vs-hotel-{$c2}";
            }
        }
        return $urls;
    }

    /** Final push #7: "kualitas" tags × city combos with all features */
    public static function allQualityFeatureUrls(): array
    {
        $urls = [];
        $qualities = ['terbaik', 'ternyaman', 'terbersih', 'teraman', 'terlengkap',
                      'termewah', 'terpopuler', 'terfavorit', 'rekomendasi'];
        foreach ($qualities as $q) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/hotel-{$q}-{$city}";
                $urls[] = "/hotel-{$city}-{$q}";
            }
        }
        return $urls;
    }

    /** Final push #8: BIG cross-product ×3 years */
    public static function allTripleYearUrls(): array
    {
        $urls = [];
        $years = self::eventYears();
        $qualities = ['terbaik', 'ternyaman', 'terbersih', 'teraman', 'terlengkap',
                      'termewah', 'terpopuler', 'terfavorit', 'rekomendasi'];
        $hotelTypes = ['butik', 'resor', 'bisnis', 'keluarga', 'mewah', 'hemat', 'syariah'];
        foreach ($qualities as $q) {
            foreach ($hotelTypes as $ht) {
                foreach (array_keys(self::CITIES) as $city) {
                    foreach ($years as $y) {
                        $urls[] = "/hotel-{$q}-{$ht}-{$city}-{$y}";
                    }
                }
            }
        }
        return $urls;
    }

    /** Final push #9: massive feature combinations */
    public static function allFeatureMassiveUrls(): array
    {
        $urls = [];
        $topFeatures = array_slice(self::ROOM_FEATURES, 0, 20);
        foreach ($topFeatures as $f) {
            foreach (array_keys(self::CITIES) as $city) {
                $urls[] = "/hotel-dengan-{$f}-{$city}";
                $urls[] = "/hotel-{$city}-fasilitas-{$f}";
            }
        }
        return $urls;
    }

    /** Final 1M push: source code keyword × ALL cities × ALL price tiers */
    public static function allScKwCityPriceAll(): array
    {
        $urls = [];
        foreach (self::SOURCE_CODE_KEYWORDS as $kw) {
            foreach (array_keys(self::CITIES) as $city) {
                foreach (self::SC_PRICE_TIERS as $p) {
                    $urls[] = "/{$kw}-{$city}-mulai-{$p}";
                }
            }
        }
        return $urls;
    }

    /** Final 1M push: room type × feature × ALL cities */
    public static function allRtFeatureAllCity(): array
    {
        $urls = [];
        $topFeatures = array_slice(self::ROOM_FEATURES, 0, 15);
        foreach (self::ROOM_TYPES as $rt) {
            foreach ($topFeatures as $f) {
                foreach (array_keys(self::CITIES) as $city) {
                    $urls[] = "/kamar-{$rt}-{$city}-dengan-{$f}";
                }
            }
        }
        return $urls;
    }

    /** Final 1M push: all occasions × all cities × all price tiers */
    public static function allOccasionCityPriceAll(): array
    {
        $urls = [];
        foreach (self::ALL_OCCASIONS as $occ) {
            foreach (array_keys(self::CITIES) as $city) {
                foreach (self::SC_PRICE_TIERS as $p) {
                    $urls[] = "/hotel-{$occ}-{$city}-harga-{$p}";
                }
            }
        }
        return $urls;
    }

    /** Final 1M push: guest type × all features × all cities */
    public static function allGuestFeatureAllCity(): array
    {
        $urls = [];
        $topFeatures = array_slice(self::ROOM_FEATURES, 0, 12);
        foreach (['pasangan', 'keluarga', 'solo-traveler', 'rombongan', 'bisnis', 'backpacker', 'budget', 'luxury'] as $gt) {
            foreach ($topFeatures as $f) {
                foreach (array_keys(self::CITIES) as $city) {
                    $urls[] = "/hotel-{$gt}-{$city}-fasilitas-{$f}";
                }
            }
        }
        return $urls;
    }

    /** Final 1M push: all star ratings × all price points × all cities */
    public static function allStarPriceAllCity(): array
    {
        $urls = [];
        $prices = ['200rb', '300rb', '500rb', '750rb', '1jt', '1-5jt', '2jt'];
        foreach (self::STARS as $star) {
            foreach ($prices as $p) {
                foreach (array_keys(self::CITIES) as $city) {
                    $urls[] = "/hotel-bintang-{$star}-{$city}-harga-{$p}";
                }
            }
        }
        return $urls;
    }

    /** Final 1M push: × year combos for major groups */
    public static function allYearFullCross(): array
    {
        $urls = [];
        $years = self::eventYears();
        $groups = [
            'hotel-murah', 'hotel-terbaik', 'hotel-populer', 'hotel-baru',
            'hotel-premium', 'hotel-luxury',
        ];
        foreach ($groups as $g) {
            foreach (array_keys(self::CITIES) as $city) {
                foreach ($years as $y) {
                    $urls[] = "/{$g}-{$city}-tahun-{$y}";
                    $urls[] = "/{$g}-{$city}-edisi-{$y}";
                }
            }
        }
        return $urls;
    }

    /** FINAL 1M PUSH: source code keywords × room types × all cities */
    public static function allScKwRoomTypeCity(): array
    {
        $urls = [];
        foreach (self::SOURCE_CODE_KEYWORDS as $kw) {
            foreach (self::ROOM_TYPES as $rt) {
                foreach (array_keys(self::CITIES) as $city) {
                    $urls[] = "/{$kw}-kamar-{$rt}-{$city}";
                }
            }
        }
        return $urls;
    }

    /** FINAL 1M PUSH: hotel type × all occasions × all cities */
    public static function allHtOccCityAll(): array
    {
        $urls = [];
        $ht = ['butik', 'resor', 'bisnis', 'keluarga', 'mewah', 'hemat', 'syariah', 'modern', 'tradisional', 'internasional', 'lokal'];
        $occs = array_slice(self::ALL_OCCASIONS, 0, 12);
        foreach ($ht as $h) {
            foreach ($occs as $occ) {
                foreach (array_keys(self::CITIES) as $city) {
                    $urls[] = "/hotel-{$h}-{$occ}-{$city}";
                }
            }
        }
        return $urls;
    }

    /** ONE MORE: room type × all occasions × all cities */
    public static function allRtOccCityAll(): array
    {
        $urls = [];
        $occs = array_slice(self::ALL_OCCASIONS, 0, 10);
        foreach (self::ROOM_TYPES as $rt) {
            foreach ($occs as $occ) {
                foreach (array_keys(self::CITIES) as $city) {
                    $urls[] = "/kamar-{$rt}-{$occ}-{$city}";
                }
            }
        }
        return $urls;
    }
}
