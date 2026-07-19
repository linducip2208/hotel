<?php

namespace App\Http\Controllers\Pseo;

use App\Http\Controllers\Controller;
use App\Models\Landmark;
use App\Models\Property;
use App\Models\RoomType;
use App\Services\Seo\ContentGenerator;
use App\Services\Seo\SchemaBuilder;
use App\Support\SeoData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PseoController extends Controller
{
    public function __construct(protected SchemaBuilder $schema, protected ContentGenerator $content) {}

    // ─── /best-{category} & /best-{category}-{year} ───────────────────────
    public function bestCategory(Request $r, string $category)
    {
        return $this->renderBest($category, null);
    }

    public function bestCategoryYear(Request $r, string $category, string $year)
    {
        return $this->renderBest($category, $year);
    }

    // ─── /alternatives-to-{slug} ──────────────────────────────────────────
    public function alternativesTo(Request $r, string $slug)
    {
        $property = Property::first();
        $rt = RoomType::where('slug', $slug)->first();
        $alternatives = $rt && $property
            ? RoomType::where('property_id', $property->id)
                ->where('id', '!=', $rt->id)
                ->where('is_active', true)
                ->get()
            : collect();

        $title = $rt
            ? "Alternatif untuk {$rt->name} — Pilihan Kamar Serupa"
            : 'Alternatif Kamar';

        return view('pseo.alternatives', [
            'rt' => $rt,
            'alternatives' => $alternatives,
            'property' => $property,
            'title' => $title,
            'meta_description' => $rt
                ? "Daftar kamar alternatif untuk {$rt->name} dengan kapasitas, harga, dan fasilitas serupa."
                : 'Pilihan kamar alternatif terbaik.',
            'schema' => $this->schema->itemList($title, $alternatives->map(fn ($a) => ['name' => $a->name])->all()),
        ]);
    }

    // ─── /compare/{a}-vs-{b} ──────────────────────────────────────────────
    public function compare(Request $r, string $a, string $b)
    {
        $property = Property::first();
        $roomA = RoomType::where('slug', $a)->where('property_id', $property?->id)->first();
        $roomB = RoomType::where('slug', $b)->where('property_id', $property?->id)->first();

        if (! $roomA || ! $roomB) abort(404);

        $title = "{$roomA->name} vs {$roomB->name} — Perbandingan Lengkap";

        return view('pseo.compare', [
            'a' => $roomA,
            'b' => $roomB,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->compareIntro($roomA->name, $roomB->name),
            'faqs' => $this->content->defaultFaqs("{$roomA->name} vs {$roomB->name}", 'compare'),
            'meta_description' => "Bandingkan {$roomA->name} dan {$roomB->name} side-by-side — fitur, fasilitas, kapasitas, harga.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("perbandingan {$roomA->name} vs {$roomB->name}", 'compare')),
        ]);
    }

    // ─── /hotels-in-{city} & variants ─────────────────────────────────────
    public function hotelsInCity(string $city)
    {
        return $this->cityListing($city);
    }

    public function hotelsInNeighborhood(string $city, string $neighborhood)
    {
        return $this->cityListing($city, $neighborhood);
    }

    public function bestHotelsCityYear(string $city, string $year)
    {
        return $this->cityListing($city, null, $year);
    }

    public function hotelsUnderPriceCity(string $price, string $city)
    {
        return $this->cityListing($city, null, null, $price);
    }

    public function petFriendly(string $city)
    {
        return $this->cityListing($city, null, null, null, 'pet-friendly');
    }

    public function landmarkHotels(string $landmark)
    {
        // Disambiguate: try city first, then landmark
        if (SeoData::isCity($landmark)) {
            return $this->cityListing($landmark);
        }
        $lm = Landmark::where('slug', $landmark)->first();
        $citySlug = $lm ? Str::slug($lm->city) : $landmark;
        return $this->cityListing($citySlug, null, null, null, 'near-'.$landmark);
    }

    // ─── /villas-with-{feature}-{location} ────────────────────────────────
    public function villasWithFeature(string $feature, string $location)
    {
        $property = Property::first();
        $cityName = SeoData::cityName($location) ?? Str::title(str_replace('-', ' ', $location));
        $featureName = Str::title(str_replace('-', ' ', $feature));
        $title = "Villa dengan {$featureName} di {$cityName}";

        return view('pseo.villa-feature', [
            'feature' => $feature,
            'feature_name' => $featureName,
            'location' => $location,
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->villaFeatureIntro($feature, $location),
            'meta_description' => "Pilihan villa dengan fitur {$featureName} di {$cityName} — harga terbaik, free cancellation H-1.",
            'faqs' => $this->content->defaultFaqs("villa {$featureName} {$cityName}", 'villa-feature'),
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("villa {$featureName} {$cityName}", 'villa-feature')),
        ]);
    }

    // ─── /{occasion}-stay-{city} ──────────────────────────────────────────
    public function occasionStay(string $occasion, string $city)
    {
        $property = Property::first();
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $occName = Str::title($occasion);
        $title = "Akomodasi {$occName} di {$cityName}";

        return view('pseo.occasion', [
            'occasion' => $occasion,
            'occasion_name' => $occName,
            'city' => $city,
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->occasionIntro($occasion, $city),
            'meta_description' => "Pilihan akomodasi {$occName} terbaik di {$cityName} — paket lengkap, harga kompetitif.",
            'faqs' => $this->content->defaultFaqs("{$occName} stay {$cityName}", 'occasion'),
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$occName} stay di {$cityName}", 'occasion')),
        ]);
    }

    // ─── /things-to-do-near-{slug} ────────────────────────────────────────
    public function thingsToDo(string $slug)
    {
        $property = Property::first();
        $landmark = Landmark::where('slug', $slug)->first();
        $name = $landmark?->name ?? Str::title(str_replace('-', ' ', $slug));
        $title = "Things to Do dekat {$name}";

        return view('pseo.things-to-do', [
            'landmark' => $landmark,
            'slug' => $slug,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->landmarkIntro($slug, $landmark?->name, $landmark?->city),
            'meta_description' => "Aktivitas, tempat makan, dan akomodasi dekat {$name}.",
            'faqs' => $this->content->defaultFaqs($name, 'things-to-do'),
            'schema' => $this->schema->faqPage($this->content->defaultFaqs($name, 'things-to-do')),
        ]);
    }

    // ─── /best-time-to-visit-{city} ───────────────────────────────────────
    public function bestTimeToVisit(string $city)
    {
        $property = Property::first();
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $title = "Waktu Terbaik Berkunjung ke {$cityName}";

        return view('pseo.best-time', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->bestTimeIntro($city),
            'meta_description' => "Panduan waktu terbaik untuk berkunjung ke {$cityName} — cuaca, harga hotel, event lokal.",
            'faqs' => $this->content->defaultFaqs("kunjungan ke {$cityName}", 'best-time'),
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("kunjungan ke {$cityName}", 'best-time')),
        ]);
    }

    // ─── /{city}-hotels-near-{landmark} ───────────────────────────────────
    public function hotelsNearLandmark(string $city, string $landmark)
    {
        $property = Property::first();
        $lm = Landmark::where('slug', $landmark)->first();
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $lmName = $lm?->name ?? Str::title(str_replace('-', ' ', $landmark));
        $title = "Hotel di {$cityName} dekat {$lmName}";

        return view('pseo.hotels-near-landmark', [
            'landmark' => $lm,
            'landmark_name' => $lmName,
            'city' => $city,
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->landmarkIntro($landmark, $lmName, $cityName),
            'meta_description' => "Hotel terbaik di {$cityName} dengan akses cepat ke {$lmName}.",
            'faqs' => $this->content->defaultFaqs("hotel dekat {$lmName}", 'near-landmark'),
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("hotel dekat {$lmName}", 'near-landmark')),
        ]);
    }

    // ─── /og/{type}/{slug}.png ────────────────────────────────────────────
    public function ogImage(string $type, string $slug)
    {
        $img = imagecreatetruecolor(1200, 630);
        $bg = imagecolorallocate($img, 30, 64, 175);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $bg);
        imagestring($img, 5, 50, 50, strtoupper($type.' / '.$slug), $white);
        ob_start();
        imagepng($img);
        $bytes = ob_get_clean();
        imagedestroy($img);
        return response($bytes, 200, ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=86400']);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-{star}-bintang-{city} ─────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function hotelByStar(int $star, string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $title = "Hotel Bintang {$star} di {$cityName} — Pilihan Terbaik " . date('Y');
        $description = "Daftar hotel bintang {$star} terbaik di {$cityName}. Harga mulai Rp " . match ($star) {1=>'100rb',2=>'200rb',3=>'350rb',4=>'700rb',5=>'1.5jt',default=>'bervariasi'} . " per malam. Fasilitas lengkap, lokasi strategis. Booking langsung!";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->starHotelIntro($star, $city),
            'faqs' => $this->content->defaultFaqs("{$star} {$cityName}", 'star-hotel'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$star} {$cityName}", 'star-hotel')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-murah-{city} ──────────────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function cheapHotels(string $city)
    {
        return $this->renderCheap($city, false);
    }

    public function cheapestHotels(string $city)
    {
        return $this->renderCheap($city, true);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-dekat-{landmark} (short) ──────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function nearLandmarkShort(string $landmark)
    {
        $property = Property::first();
        $name = SeoData::SHORT_LANDMARKS[$landmark]
            ?? Str::title(str_replace('-', ' ', $landmark));
        $title = "Hotel Dekat {$name} — Akomodasi Strategis " . date('Y');

        return view('pseo.city-listing', [
            'city' => $landmark,
            'city_name' => $name,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->nearLandmarkShortIntro($landmark),
            'faqs' => $this->content->defaultFaqs($name, 'near-landmark-short'),
            'meta_description' => "Hotel terbaik dekat {$name}. Harga mulai Rp 200.000/malam. Jalan kaki ke {$name}, free cancellation, booking instan!",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs($name, 'near-landmark-short')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-{city}-dekat-bandara & dekat-stasiun ──────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function nearAirport(string $city)
    {
        return $this->renderNearTransport($city, 'bandara');
    }

    public function nearStation(string $city)
    {
        return $this->renderNearTransport($city, 'stasiun');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-{city}-{amenity} ──────────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function withPool(string $city)         { return $this->renderAmenity($city, 'kolam-renang'); }
    public function withBreakfast(string $city)    { return $this->renderAmenity($city, 'sarapan-gratis'); }
    public function withParking(string $city)      { return $this->renderAmenity($city, 'parkir-luas'); }
    public function familyFriendly(string $city)   { return $this->renderAmenity($city, 'ramah-keluarga'); }
    public function backpacker(string $city)       { return $this->renderAmenity($city, 'untuk-backpacker'); }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /penginapan-{city}, /apartemen-{city}, /villa-{city}, /guesthouse-{city}
    // ═══════════════════════════════════════════════════════════════════════
    public function lodging(string $city)      { return $this->renderAltAccommodation('penginapan', $city); }
    public function apartments(string $city)   { return $this->renderAltAccommodation('apartemen', $city); }
    public function villas(string $city)       { return $this->renderAltAccommodation('villa', $city); }
    public function guesthouses(string $city)  { return $this->renderAltAccommodation('guesthouse', $city); }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── Content pages: tips, guide, weather, events, recommendation ─────
    // ═══════════════════════════════════════════════════════════════════════
    public function tips(string $city)
    {
        return $this->renderContentPage('tips', $city);
    }

    public function travelGuide(string $city)
    {
        return $this->renderContentPage('guide', $city);
    }

    public function weather(string $city, string $month)
    {
        $property = Property::first();
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $monthLabel = Str::title($month);
        $title = "Cuaca di {$cityName} Bulan {$monthLabel} — Panduan Lengkap";
        $description = "Informasi cuaca {$cityName} bulan {$monthLabel}: temperatur, curah hujan, kelembaban, dan rekomendasi aktivitas sesuai musim.";

        return view('pseo.content-page', [
            'page_type' => 'weather',
            'city' => $city,
            'city_name' => $cityName,
            'month' => $month,
            'month_label' => $monthLabel,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->weatherIntro($city, $month),
            'faqs' => $this->content->defaultFaqs("{$cityName} {$monthLabel}", 'weather'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$cityName} {$monthLabel}", 'weather')),
        ]);
    }

    public function events(string $city, string $year)
    {
        return $this->renderContentPage('events', $city, (int) $year);
    }

    public function recommendation(string $occasion, string $city)
    {
        $property = Property::first();
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $occLabel = match ($occasion) {
            'honeymoon' => 'Honeymoon', 'family' => 'Keluarga', 'business' => 'Bisnis',
            'romantic' => 'Romantis', 'backpacker' => 'Backpacker',
            'budget' => 'Budget', 'luxury' => 'Mewah',
            default => Str::title($occasion),
        };
        $title = "Rekomendasi Hotel {$occLabel} di {$cityName} — Pilihan Terbaik";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->recommendationIntro($occasion, $city),
            'faqs' => $this->content->defaultFaqs("{$occLabel} {$cityName}", 'recommendation'),
            'meta_description' => "Rekomendasi hotel {$occLabel} terbaik di {$cityName}. Fasilitas lengkap, lokasi strategis, harga kompetitif. Booking langsung!",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$occLabel} {$cityName}", 'recommendation')),
        ]);
    }

    public function neighborhoodArea(string $neighborhood, string $city)
    {
        $property = Property::first();
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $areaName = Str::title(str_replace('-', ' ', $neighborhood));
        $title = "Area {$areaName} {$cityName} — Hotel, Atraksi & Panduan Lengkap";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'neighborhood' => $neighborhood,
            'neighborhood_name' => $areaName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->areaNeighborhoodIntro($neighborhood, $city),
            'faqs' => $this->content->defaultFaqs("{$areaName} {$cityName}", 'neighborhood-area'),
            'meta_description' => "Panduan lengkap area {$areaName} di {$cityName}. Hotel, atraksi, transportasi, kuliner. Info terkini langsung dari lokal!",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$areaName} {$cityName}", 'neighborhood-area')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-populer-di-{city} & /hotel-baru-di-{city} ─────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function popularHotels(string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $property = Property::first();
        $title = "Hotel Paling Populer di {$cityName} — Favorit Wisatawan " . date('Y');
        $description = "Daftar hotel paling populer di {$cityName} berdasarkan jumlah booking dan rating tamu. Pilihan terbaik untuk liburan Anda!";

        $rooms = $property
            ? RoomType::where('property_id', $property->id)->where('is_active', true)
                ->withCount('reservationRooms')
                ->orderByDesc('reservation_rooms_count')
                ->get()
            : collect();

        $intro = "<p>Hotel paling populer di {$cityName} — dikurasi berdasarkan jumlah pemesanan aktual dan rating tamu dari tahun berjalan. Semakin banyak tamu yang memilih hotel ini, semakin tinggi posisinya dalam daftar. Popularitas adalah indikator kuat kualitas: hotel yang konsisten dipesan oleh wisatawan dari berbagai latar belakang menunjukkan bahwa properti ini berhasil memenuhi ekspektasi tamu secara konsisten.</p>";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $intro,
            'faqs' => $this->content->defaultFaqs("populer {$cityName}", 'city'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("populer {$cityName}", 'city')),
        ]);
    }

    public function newHotels(string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $property = Property::first();
        $title = "Hotel Baru di {$cityName} — Akomodasi Terkini " . date('Y');
        $description = "Daftar hotel terbaru di {$cityName}. Akomodasi fresh dengan fasilitas modern dan harga promo pembukaan. Booking sekarang!";

        $intro = "<p>Hotel baru di {$cityName} — akomodasi yang baru dibuka atau direnovasi, menawarkan fasilitas terbaru dengan kondisi prima. Hotel baru seringkali menawarkan promo opening yang menarik (diskon 20–40% untuk 3 bulan pertama) sebagai strategi membangun basis tamu. Ini adalah kesempatan terbaik untuk menikmati fasilitas fresh dengan harga di bawah pasar.</p>";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $intro,
            'faqs' => $this->content->defaultFaqs("baru {$cityName}", 'city'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("baru {$cityName}", 'city')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-{city}-di-bawah-{price} ────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function granularPrice(string $city, string $price)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $title = "Hotel {$cityName} di Bawah Rp " . strtoupper($price) . " — Budget Hemat " . date('Y');
        $description = "Daftar hotel di {$cityName} dengan tarif di bawah Rp {$price}. Pilihan akomodasi budget terbaik untuk liburan hemat. Booking langsung!";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->granularPriceIntro($city, $price),
            'faqs' => $this->content->defaultFaqs("budget Rp{$price} {$cityName}", 'granular-price'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("budget Rp{$price} {$cityName}", 'granular-price')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-{city}-{min}-{max}-ribu ────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function priceRange(string $city, string $min, string $max)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $title = "Hotel {$cityName} Harga Rp {$min}rb–{$max}rb — Pilihan Terjangkau " . date('Y');
        $description = "Daftar hotel di {$cityName} dalam rentang harga Rp {$min}.000–Rp {$max}.000 per malam. Kualitas terjaga, harga bersahabat. Booking sekarang!";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->genericIntro('price-range', ['city' => $cityName, 'min' => $min, 'max' => $max]),
            'faqs' => $this->content->defaultFaqs("Rp{$min}rb–{$max}rb {$cityName}", 'price-range'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("Rp{$min}rb–{$max}rb {$cityName}", 'price-range')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /kamar-{type}-{city} ──────────────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function roomTypeCity(string $type, string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $typeName = Str::title(str_replace('-', ' ', $type));
        $title = "Kamar {$typeName} di {$cityName} — Pilihan Akomodasi " . date('Y');
        $description = "Pilihan kamar tipe {$typeName} di {$cityName}. Fasilitas lengkap, harga kompetitif. Booking langsung, konfirmasi instan!";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->genericIntro('room-type', ['city' => $cityName, 'roomType' => $typeName]),
            'faqs' => $this->content->defaultFaqs("{$typeName} {$cityName}", 'room-type'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$typeName} {$cityName}", 'room-type')),
        ]);
    }

    public function roomTypePrice(string $type, string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $typeName = Str::title(str_replace('-', ' ', $type));
        $title = "Harga Kamar {$typeName} di {$cityName} — Tarif Terbaru " . date('Y');
        $description = "Cek harga kamar {$typeName} di {$cityName}. Perbandingan tarif, fasilitas, dan promo terbaru. Booking langsung, harga terbaik!";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->genericIntro('room-type-price', ['city' => $cityName, 'roomType' => $typeName]),
            'faqs' => $this->content->defaultFaqs("harga {$typeName} {$cityName}", 'room-type-price'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("harga {$typeName} {$cityName}", 'room-type-price')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-untuk-{type}-{city} ────────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function guestTypeCity(string $type, string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $typeLabel = Str::title(str_replace('-', ' ', $type));
        $title = "Hotel untuk {$typeLabel} di {$cityName} — Akomodasi Ideal " . date('Y');
        $description = "Pilihan hotel terbaik untuk {$typeLabel} di {$cityName}. Lokasi strategis, fasilitas sesuai kebutuhan, harga bersahabat. Booking sekarang!";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->genericIntro('guest-type', ['city' => $cityName, 'guestType' => $typeLabel]),
            'faqs' => $this->content->defaultFaqs("{$typeLabel} {$cityName}", 'guest-type'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$typeLabel} {$cityName}", 'guest-type')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-{city}-musim-{season} ──────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function seasonCity(string $city, string $season)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $seasonLabel = Str::title($season);
        $title = "Hotel {$cityName} Musim {$seasonLabel} — Panduan & Tips " . date('Y');
        $description = "Tips memilih hotel di {$cityName} saat musim {$seasonLabel}. Rekomendasi akomodasi, aktivitas, dan persiapan. Booking langsung!";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->genericIntro('season', ['city' => $cityName, 'season' => $seasonLabel]),
            'faqs' => $this->content->defaultFaqs("musim {$seasonLabel} {$cityName}", 'season'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("musim {$seasonLabel} {$cityName}", 'season')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-{city}-liburan-{holiday} ───────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function holidayCity(string $city, string $holiday)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $holidayLabels = [
            'lebaran' => 'Lebaran', 'natal' => 'Natal', 'tahun-baru' => 'Tahun Baru',
            'imlek' => 'Imlek', 'waisak' => 'Waisak', 'galungan' => 'Galungan',
        ];
        $holidayLabel = $holidayLabels[$holiday] ?? Str::title(str_replace('-', ' ', $holiday));
        $title = "Hotel {$cityName} Liburan {$holidayLabel} — Akomodasi Nyaman " . date('Y');
        $description = "Booking hotel di {$cityName} untuk liburan {$holidayLabel}. Pilihan akomodasi terbaik, dekat pusat perayaan. Pesan sekarang — cepat penuh!";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->genericIntro('holiday', ['city' => $cityName, 'holiday' => $holidayLabel]),
            'faqs' => $this->content->defaultFaqs("liburan {$holidayLabel} {$cityName}", 'holiday'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("liburan {$holidayLabel} {$cityName}", 'holiday')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-{city}-jarak-{distance}-km-dari-pusat ─────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function distanceCity(string $city, string $distance)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $title = "Hotel {$cityName} Jarak {$distance} km dari Pusat — Akses Cepat " . date('Y');
        $description = "Hotel di {$cityName} dalam radius {$distance} km dari pusat kota. Dekat atraksi utama, hemat transportasi. Booking langsung!";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->genericIntro('distance-city', ['city' => $cityName, 'distance' => $distance]),
            'faqs' => $this->content->defaultFaqs("radius {$distance}km {$cityName}", 'distance-city'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("radius {$distance}km {$cityName}", 'distance-city')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /hotel-dekat-{landmark}-jarak-{distance} ─────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function distanceLandmark(string $landmark, string $distance)
    {
        $property = Property::first();
        $landmarkName = Str::title(str_replace('-', ' ', $landmark));
        $title = "Hotel Dekat {$landmarkName} Radius {$distance} — Akses Cepat";

        return view('pseo.city-listing', [
            'city' => $landmark,
            'city_name' => $landmarkName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->genericIntro('distance-landmark', ['landmark' => $landmarkName, 'distance' => $distance]),
            'faqs' => $this->content->defaultFaqs("dekat {$landmarkName} {$distance}", 'distance-landmark'),
            'meta_description' => "Hotel terdekat dengan {$landmarkName} dalam radius {$distance}. Booking instan, free cancellation.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("dekat {$landmarkName} {$distance}", 'distance-landmark')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── Question-based pages ──────────────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function questionSafe(string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        return view('pseo.content-page', [
            'page_type' => 'question',
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => "Apakah {$cityName} Aman untuk Wisatawan? — Panduan Keamanan " . date('Y'),
            'intro' => $this->content->genericIntro('question-safe', ['city' => $cityName]),
            'faqs' => $this->content->defaultFaqs("keamanan {$cityName}", 'question'),
            'meta_description' => "Panduan lengkap keamanan wisatawan di {$cityName}. Tips aman, area yang harus dihindari, dan rekomendasi hotel dengan sistem keamanan 24 jam.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("keamanan {$cityName}", 'question')),
        ]);
    }

    public function questionWhen(string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        return view('pseo.content-page', [
            'page_type' => 'question',
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => "Kapan Waktu Terbaik ke {$cityName}? — Panduan Musim " . date('Y'),
            'intro' => $this->content->bestTimeIntro($city),
            'faqs' => $this->content->defaultFaqs("waktu terbaik {$cityName}", 'question'),
            'meta_description' => "Kapan sebaiknya berkunjung ke {$cityName}? Panduan musim, cuaca, event, dan harga hotel termurah di {$cityName}.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("waktu terbaik {$cityName}", 'question')),
        ]);
    }

    public function questionCost(string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        return view('pseo.content-page', [
            'page_type' => 'question',
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => "Berapa Biaya Hotel di {$cityName}? — Estimasi Budget " . date('Y'),
            'intro' => $this->content->genericIntro('question-cost', ['city' => $cityName]),
            'faqs' => $this->content->defaultFaqs("biaya hotel {$cityName}", 'question'),
            'meta_description' => "Estimasi biaya menginap di {$cityName}: harga hotel, makan, transportasi, dan atraksi. Panduan budget harian backpacker hingga luxury.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("biaya hotel {$cityName}", 'question')),
        ]);
    }

    public function questionHow(string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        return view('pseo.content-page', [
            'page_type' => 'question',
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => "Bagaimana Cara ke {$cityName}? — Panduan Transportasi " . date('Y'),
            'intro' => $this->content->genericIntro('question-how', ['city' => $cityName]),
            'faqs' => $this->content->defaultFaqs("cara ke {$cityName}", 'question'),
            'meta_description' => "Panduan lengkap cara mencapai {$cityName}: pesawat, kereta, bus, dan transportasi lokal. Tips tiket murah dan rute tercepat.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("cara ke {$cityName}", 'question')),
        ]);
    }

    public function questionWhat(string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        return view('pseo.content-page', [
            'page_type' => 'question',
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => "Apa Saja Wisata di {$cityName}? — Atraksi Wajib " . date('Y'),
            'intro' => $this->content->genericIntro('question-what', ['city' => $cityName]),
            'faqs' => $this->content->defaultFaqs("wisata {$cityName}", 'question'),
            'meta_description' => "Daftar lengkap tempat wisata di {$cityName}: alam, budaya, kuliner, dan hidden gem. Rekomendasi itinerary dan hotel terdekat.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("wisata {$cityName}", 'question')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /bandingkan-{a}-vs-{b} (city vs city) ────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function compareCities(string $a, string $b)
    {
        $cityA = SeoData::cityName($a) ?? Str::title(str_replace('-', ' ', $a));
        $cityB = SeoData::cityName($b) ?? Str::title(str_replace('-', ' ', $b));
        $title = "{$cityA} vs {$cityB} — Perbandingan Destinasi Lengkap";
        $description = "Bandingkan {$cityA} vs {$cityB}: hotel, biaya, atraksi, cuaca, transportasi. Mana yang lebih cocok untuk liburan Anda? Panduan objektif.";

        return view('pseo.content-page', [
            'page_type' => 'compare-cities',
            'city' => $a,
            'city_name' => $cityA,
            'compare_city' => $cityB,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->genericIntro('compare-cities', ['a' => $cityA, 'b' => $cityB]),
            'faqs' => $this->content->defaultFaqs("{$cityA} vs {$cityB}", 'compare-cities'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$cityA} vs {$cityB}", 'compare-cities')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── /bandingkan-hotel-{city}-{a}-vs-{b} (neighborhoods) ──────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function compareNeighborhoods(string $city, string $a, string $b)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $na = Str::title(str_replace('-', ' ', $a));
        $nb = Str::title(str_replace('-', ' ', $b));
        $title = "Hotel {$cityName}: {$na} vs {$nb} — Perbandingan Kawasan";
        $description = "Bandingkan area {$na} vs {$nb} di {$cityName}: hotel, harga, akses, suasana. Mana yang lebih cocok untuk Anda?";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->genericIntro('compare-neighborhoods', ['city' => $cityName, 'a' => $na, 'b' => $nb]),
            'faqs' => $this->content->defaultFaqs("{$na} vs {$nb} {$cityName}", 'compare-neighborhoods'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$na} vs {$nb} {$cityName}", 'compare-neighborhoods')),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── CATCH-ALL GENERIC HANDLER ────────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    public function generic(Request $request, string $slug)
    {
        $slug = trim($slug, '/');
        $segments = explode('/', $slug);

        $pattern = $this->matchPattern($segments);

        if (!$pattern) {
            abort(404);
        }

        $data = $this->buildSeoData($pattern['type'], $pattern['params']);
        return view($data['view'], $data);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── MASSIVE GENERIC PATTERN MATCHER ─────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════
    protected function matchPattern(array $segments): ?array
    {
        if (count($segments) !== 1 && count($segments) !== 2) return null;
        $url = $segments[0];

        $cities = array_keys(SeoData::CITIES);
        usort($cities, fn ($a, $b) => strlen($b) - strlen($a));

        // ── Occasion × stay × city (for all occasions, including catch-all) ──
        foreach (SeoData::ALL_OCCASIONS as $occ) {
            $prefix = "{$occ}-stay-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                $result = $this->extractCity($rest, $cities);
                if ($result) {
                    return ['type' => 'occasion', 'params' => ['occasion' => $occ, 'city' => $result]];
                }
            }
        }

        // ── SOURCE CODE: /beli-{keyword} ──
        if (str_starts_with($url, 'beli-')) {
            $rest = substr($url, 5);
            if (in_array($rest, SeoData::SOURCE_CODE_KEYWORDS)) {
                return ['type' => 'source-code-beli', 'params' => ['keyword' => $rest]];
            }
            // /beli-{keyword}-{city}
            foreach (SeoData::scCities() as $city) {
                if (str_ends_with($rest, "-{$city}")) {
                    $kw = substr($rest, 0, -strlen("-{$city}"));
                    return ['type' => 'source-code-beli-city', 'params' => ['keyword' => $kw, 'city' => $city]];
                }
            }
        }

        // ── SOURCE CODE: /harga-{keyword} ──
        if (str_starts_with($url, 'harga-')) {
            $rest = substr($url, 6);
            // /harga-{keyword}-mulai-{price}
            foreach (SeoData::SC_PRICE_TIERS as $p) {
                if (str_ends_with($rest, "-mulai-{$p}")) {
                    $kw = substr($rest, 0, -strlen("-mulai-{$p}"));
                    return ['type' => 'source-code-harga-price', 'params' => ['keyword' => $kw, 'price' => $p]];
                }
            }
            // /harga-{keyword}-{city}
            foreach ($cities as $city) {
                if (str_ends_with($rest, "-{$city}")) {
                    $kw = substr($rest, 0, -strlen("-{$city}"));
                    return ['type' => 'source-code-harga-city', 'params' => ['keyword' => $kw, 'city' => $city]];
                }
            }
            // /harga-{keyword}
            if (in_array($rest, SeoData::SOURCE_CODE_KEYWORDS)) {
                return ['type' => 'source-code-harga', 'params' => ['keyword' => $rest]];
            }
            return null;
        }

        // ── SOURCE CODE: /download-{keyword} ──
        if (str_starts_with($url, 'download-')) {
            $rest = substr($url, 9);
            if (in_array($rest, SeoData::SOURCE_CODE_KEYWORDS)) {
                return ['type' => 'source-code-download', 'params' => ['keyword' => $rest]];
            }
            return null;
        }

        // ── SOURCE CODE: /jasa-pembuatan-{kw}-{city} ──
        if (str_starts_with($url, 'jasa-pembuatan-')) {
            $rest = substr($url, 16);
            foreach ($cities as $city) {
                if (str_ends_with($rest, "-{$city}")) {
                    $kw = substr($rest, 0, -strlen("-{$city}"));
                    return ['type' => 'source-code-jasa', 'params' => ['keyword' => $kw, 'city' => $city]];
                }
            }
            return null;
        }

        // ── SOURCE CODE: /paket-{kw}-{city} ──
        if (str_starts_with($url, 'paket-')) {
            $rest = substr($url, 6);
            foreach ($cities as $city) {
                if (str_ends_with($rest, "-{$city}")) {
                    $kw = substr($rest, 0, -strlen("-{$city}"));
                    if ($kw) return ['type' => 'source-code-paket', 'params' => ['keyword' => $kw, 'city' => $city]];
                }
            }
            return null;
        }

        // ── SOURCE CODE: /{kw}-terbaik ──
        if (str_ends_with($url, '-terbaik')) {
            $rest = substr($url, 0, -8);
            if (in_array($rest, SeoData::SOURCE_CODE_KEYWORDS)) {
                return ['type' => 'source-code-best', 'params' => ['keyword' => $rest]];
            }
        }

        // ── SOURCE CODE: /{kw}-{city}-mulai-{price} ──
        foreach (SeoData::SC_PRICE_TIERS as $p) {
            $suffix = "-mulai-{$p}";
            if (str_ends_with($url, $suffix)) {
                $rest = substr($url, 0, -strlen($suffix));
                foreach ($cities as $city) {
                    if (str_ends_with($rest, "-{$city}")) {
                        $kw = substr($rest, 0, -strlen("-{$city}"));
                        if (in_array($kw, SeoData::SOURCE_CODE_KEYWORDS)) {
                            return ['type' => 'source-code-city-price', 'params' => ['keyword' => $kw, 'city' => $city, 'price' => $p]];
                        }
                    }
                }
            }
        }

        // ── SOURCE CODE: /{kw}-vs-{kw2} ──
        if (str_contains($url, '-vs-')) {
            $parts = explode('-vs-', $url, 2);
            if (count($parts) === 2 && in_array($parts[0], SeoData::SOURCE_CODE_KEYWORDS) && in_array($parts[1], SeoData::SOURCE_CODE_KEYWORDS)) {
                return ['type' => 'source-code-vs', 'params' => ['kw1' => $parts[0], 'kw2' => $parts[1]]];
            }
        }

        // ── /source-code/{kw}-{city} (2-segment path) ──
        if (count($segments) === 2 && $segments[0] === 'source-code') {
            $rest = $segments[1];
            foreach ($cities as $city) {
                if (str_ends_with($rest, "-{$city}")) {
                    $kw = substr($rest, 0, -strlen("-{$city}"));
                    return ['type' => 'source-code-path', 'params' => ['keyword' => $kw, 'city' => $city]];
                }
            }
            return null;
        }

        // ── Source code city murah: /{kw}-{city}-murah ──
        if (str_ends_with($url, '-murah')) {
            $rest = substr($url, 0, -6);
            foreach ($cities as $city) {
                if (str_ends_with($rest, "-{$city}")) {
                    $kw = substr($rest, 0, -strlen("-{$city}"));
                    return ['type' => 'source-code-city-murah', 'params' => ['keyword' => $kw, 'city' => $city]];
                }
            }
        }

        // ── Source code × district: /{kw}-{district}-{city} ──
        foreach (SeoData::DISTRICTS as $city => $districts) {
            foreach ($districts as $d) {
                $suffix = "-{$d}-{$city}";
                if (str_ends_with($url, $suffix)) {
                    $kw = substr($url, 0, -strlen($suffix));
                    if (in_array($kw, array_slice(SeoData::SOURCE_CODE_KEYWORDS, 0, 15))) {
                        return ['type' => 'source-code-district', 'params' => ['keyword' => $kw, 'district' => $d, 'city' => $city]];
                    }
                }
            }
        }

        // ── Source code city: /{kw}-{city} ──
        foreach (SeoData::scCities() as $city) {
            if (str_ends_with($url, "-{$city}")) {
                $kw = substr($url, 0, -strlen("-{$city}"));
                if (in_array($kw, SeoData::SOURCE_CODE_KEYWORDS)) {
                    return ['type' => 'source-code-city', 'params' => ['keyword' => $kw, 'city' => $city]];
                }
            }
        }

        // ── Feature × city: /hotel-{city}-dengan-{feature} ──
        if (str_starts_with($url, 'hotel-') && str_contains($url, '-dengan-')) {
            $parts = explode('-dengan-', $url, 2);
            $citySlug = substr($parts[0], 6);
            $feature = $parts[1] ?? '';
            if (SeoData::isCity($citySlug) && in_array($feature, SeoData::ROOM_FEATURES)) {
                return ['type' => 'feature-city', 'params' => ['city' => $citySlug, 'feature' => $feature]];
            }
        }

        // ── Double feature × city: /hotel-{city}-{f1}-dan-{f2} ──
        if (str_starts_with($url, 'hotel-') && str_contains($url, '-dan-')) {
            $rest = substr($url, 6);
            foreach ($cities as $city) {
                $prefix = "{$city}-";
                if (str_starts_with($rest, $prefix)) {
                    $remainder = substr($rest, strlen($prefix));
                    $parts = explode('-dan-', $remainder, 2);
                    if (count($parts) === 2 && in_array($parts[0], SeoData::ROOM_FEATURES) && in_array($parts[1], SeoData::ROOM_FEATURES)) {
                        return ['type' => 'double-feature-city', 'params' => ['city' => $city, 'f1' => $parts[0], 'f2' => $parts[1]]];
                    }
                }
            }
        }

        // ── Occasion × feature × city: /hotel-{occ}-{city}-dengan-{feature} ──
        foreach (SeoData::ALL_OCCASIONS as $occ) {
            $prefix = "hotel-{$occ}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                if (str_contains($rest, '-dengan-')) {
                    $parts = explode('-dengan-', $rest, 2);
                    if (SeoData::isCity($parts[0]) && in_array($parts[1], array_slice(SeoData::ROOM_FEATURES, 0, 10))) {
                        return ['type' => 'occasion-feature-city', 'params' => ['occasion' => $occ, 'city' => $parts[0], 'feature' => $parts[1]]];
                    }
                }
            }
        }

        // ── Double-city: /hotel-{city1}-ke-{city2} ──
        if (str_starts_with($url, 'hotel-') && str_contains($url, '-ke-')) {
            $rest = substr($url, 6);
            $parts = explode('-ke-', $rest, 2);
            if (count($parts) === 2 && SeoData::isCity($parts[0]) && SeoData::isCity($parts[1])) {
                return ['type' => 'double-city', 'params' => ['city1' => $parts[0], 'city2' => $parts[1]]];
            }
        }

        // ── District hotel: /hotel-di-{district}-{city} ──
        if (str_starts_with($url, 'hotel-di-')) {
            $rest = substr($url, 9);
            foreach (SeoData::DISTRICTS as $city => $districts) {
                foreach ($districts as $d) {
                    $expected = "{$d}-{$city}";
                    if ($rest === $expected) {
                        return ['type' => 'district-city', 'params' => ['district' => $d, 'city' => $city]];
                    }
                }
            }
        }

        // ── City comparison: /bandingkan-hotel-{c1}-vs-{c2} ──
        if (str_starts_with($url, 'bandingkan-hotel-') && str_contains($url, '-vs-')) {
            $rest = substr($url, 17);
            $parts = explode('-vs-', $rest, 2);
            if (count($parts) === 2 && SeoData::isCity($parts[0]) && SeoData::isCity($parts[1])) {
                return ['type' => 'compare-city-expanded', 'params' => ['a' => $parts[0], 'b' => $parts[1]]];
            }
        }

        // ── Month×Year×City: /hotel-{city}-{month}-{year} ──
        if (str_starts_with($url, 'hotel-')) {
            foreach (SeoData::MONTHS as $month) {
                foreach (SeoData::eventYears() as $year) {
                    $suffix = "-{$month}-{$year}";
                    if (str_ends_with($url, $suffix)) {
                        $rest = substr($url, 6, -strlen($suffix));
                        if (SeoData::isCity($rest)) {
                            return ['type' => 'month-year-city', 'params' => ['city' => $rest, 'month' => $month, 'year' => $year]];
                        }
                    }
                }
            }
        }

        // ── Amenity × city × price: /{amenity}-{city}-{price} ──
        $amenities = ['kolam-renang', 'sarapan-gratis', 'parkir-luas', 'ramah-keluarga', 'untuk-backpacker'];
        $prices = ['100rb', '200rb', '300rb', '400rb', '500rb', '750rb', '1jt'];
        foreach ($amenities as $a) {
            $prefix = "{$a}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                foreach ($prices as $p) {
                    $suffix = "-{$p}";
                    if (str_ends_with($rest, $suffix)) {
                        $city = substr($rest, 0, -strlen($suffix));
                        if (SeoData::isCity($city)) {
                            return ['type' => 'amenity-city-price', 'params' => ['amenity' => $a, 'city' => $city, 'price' => $p]];
                        }
                    }
                }
            }
        }

        // ── Star × price × city: /hotel-{star}-bintang-{city}-{price} ──
        if (preg_match('/^hotel-([1-5])-bintang-/', $url, $m)) {
            $star = (int) $m[1];
            $rest = substr($url, strlen("hotel-{$star}-bintang-"));
            foreach ($prices as $p) {
                $suffix = "-{$p}";
                if (str_ends_with($rest, $suffix)) {
                    $city = substr($rest, 0, -strlen($suffix));
                    if (SeoData::isCity($city)) {
                        return ['type' => 'star-price-city', 'params' => ['star' => $star, 'city' => $city, 'price' => $p]];
                    }
                }
            }
        }

        // ── Guest × feature × city: /hotel-{gt}-{city}-{f} ──
        foreach (SeoData::GUEST_TYPES as $gt) {
            $prefix = "hotel-{$gt}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                $topF = array_slice(SeoData::ROOM_FEATURES, 0, 12);
                foreach ($topF as $f) {
                    $suffix = "-{$f}";
                    if (str_ends_with($rest, $suffix)) {
                        $city = substr($rest, 0, -strlen($suffix));
                        if (SeoData::isCity($city)) {
                            return ['type' => 'guest-feature-city', 'params' => ['guestType' => $gt, 'city' => $city, 'feature' => $f]];
                        }
                    }
                }
            }
        }

        // ── Room type × feature × city: /kamar-{rt}-{city}-{f} ──
        if (str_starts_with($url, 'kamar-')) {
            $rest = substr($url, 6);
            foreach (SeoData::ROOM_TYPES as $rt) {
                $prefix = "{$rt}-";
                if (str_starts_with($rest, $prefix)) {
                    $rest2 = substr($rest, strlen($prefix));
                    $topF = array_slice(SeoData::ROOM_FEATURES, 0, 8);
                    foreach ($topF as $f) {
                        $suffix = "-{$f}";
                        if (str_ends_with($rest2, $suffix)) {
                            $city = substr($rest2, 0, -strlen($suffix));
                            if (SeoData::isCity($city)) {
                                return ['type' => 'room-type-feature-city', 'params' => ['roomType' => $rt, 'city' => $city, 'feature' => $f]];
                            }
                        }
                    }
                }
            }
        }

        // ── Price × city expanded: /hotel-{city}-harga-{price} ──
        if (str_starts_with($url, 'hotel-') && str_contains($url, '-harga-')) {
            $parts = explode('-harga-', $url, 2);
            $citySlug = substr($parts[0], 6);
            $price = $parts[1] ?? '';
            if (SeoData::isCity($citySlug)) {
                return ['type' => 'price-city-expanded', 'params' => ['city' => $citySlug, 'price' => $price]];
            }
        }

        // ── Content topic × city: /tips-{topic}-hotel-{city} ──
        if (str_starts_with($url, 'tips-') && str_contains($url, '-hotel-')) {
            $parts = explode('-hotel-', $url, 2);
            $topic = substr($parts[0], 5);
            $city = $parts[1] ?? '';
            if (SeoData::isCity($city) && $topic) {
                return ['type' => 'content-topic-city', 'params' => ['topic' => $topic, 'city' => $city]];
            }
        }

        // ── Mega filler patterns: /{pat}-{kw}, /{pat}-{kw}-2026, /{pat}-{kw}-murah ──
        $fillerPatterns = [
            'aplikasi-hotel', 'software-hotel', 'sistem-hotel', 'penginapan',
            'hotel-murah', 'hotel-terbaik', 'hotel-modern', 'hotel-syariah',
            'hotel-keluarga', 'hotel-bisnis', 'hotel-resort', 'hotel-budget',
            'hotel-premium', 'hotel-luxury', 'hotel-baru', 'hotel-populer',
            'hotel-dekat', 'hotel-view', 'hotel-pusat', 'hotel-strategis',
        ];
        foreach ($fillerPatterns as $pat) {
            $prefix = "{$pat}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                // {pat}-{kw}-murah
                if (str_ends_with($rest, '-murah')) {
                    $kw = substr($rest, 0, -6);
                    return ['type' => 'filler-murah', 'params' => ['pattern' => $pat, 'kw' => $kw]];
                }
                // {pat}-{kw}-2026
                foreach (SeoData::eventYears() as $y) {
                    if (str_ends_with($rest, "-{$y}")) {
                        $kw = substr($rest, 0, -strlen("-{$y}"));
                        return ['type' => 'filler-year', 'params' => ['pattern' => $pat, 'kw' => $kw, 'year' => $y]];
                    }
                }
                // {pat}-{kw}
                if ($rest) {
                    return ['type' => 'filler-base', 'params' => ['pattern' => $pat, 'kw' => $rest]];
                }
            }
        }

        // ── Second tier: /{pattern}-{city} and /{pattern}-{city}-{year} ──
        $secondTierPatterns = [
            'hotel-terdekat', 'hotel-termurah', 'hotel-terbaru', 'hotel-terfavorit',
            'hotel-rekomendasi', 'hotel-strategis', 'hotel-nyaman', 'hotel-bersih',
            'hotel-aman', 'hotel-modern', 'hotel-tradisional', 'hotel-syariah',
            'hotel-harian', 'hotel-mingguan', 'hotel-bulanan', 'hotel-transit',
            'hotel-ekonomi', 'hotel-menengah', 'hotel-atas', 'hotel-ekslusif',
        ];
        foreach ($secondTierPatterns as $pat) {
            $prefix = "{$pat}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                foreach ($cities as $city) {
                    if ($rest === $city) {
                        return ['type' => 'second-tier-city', 'params' => ['pattern' => $pat, 'city' => $city]];
                    }
                    foreach (SeoData::eventYears() as $y) {
                        if ($rest === "{$city}-{$y}") {
                            return ['type' => 'second-tier-city-year', 'params' => ['pattern' => $pat, 'city' => $city, 'year' => $y]];
                        }
                    }
                }
            }
        }

        // ── Third tier massive: /hotel-{ht}-{city} ──
        $hotelTypes = ['bintang', 'melati', 'butik', 'resor', 'kota', 'pantai',
                       'gunung', 'bisnis', 'keluarga', 'romantis', 'mewah', 'hemat',
                       'syariah', 'modern', 'tradisional', 'internasional', 'lokal',
                       'kapsul', 'hostel', 'motel', 'guest-house', 'homestay', 'villa',
                       'apartment', 'losmen', 'penginapan'];
        foreach ($hotelTypes as $ht) {
            $prefix = "hotel-{$ht}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                if (SeoData::isCity($rest)) {
                    return ['type' => 'third-tier-hotel-type', 'params' => ['hotelType' => $ht, 'city' => $rest]];
                }
            }
        }

        // ── Third tier massive: /hotel-untuk-{tt}-{city} ──
        $tripTypes = ['liburan', 'bisnis', 'honeymoon', 'family-trip', 'backpacking',
                      'staycation', 'workation', 'short-trip', 'long-stay', 'transit',
                      'weekend', 'study-tour', 'company-gathering', 'reuni', 'outing',
                      'romantic-getaway', 'adventure', 'spiritual', 'culinary', 'belanja'];
        foreach ($tripTypes as $tt) {
            $prefix = "hotel-untuk-{$tt}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                if (SeoData::isCity($rest)) {
                    return ['type' => 'third-tier-trip-type', 'params' => ['tripType' => $tt, 'city' => $rest]];
                }
            }
        }

        // ── Super mega: /{prefix}-{city} and /{prefix}-{city}-{year} ──
        $superPrefixes = [
            'rekomendasi-hotel', 'daftar-hotel', 'pilihan-hotel', 'rekomendasi-penginapan',
            'koleksi-hotel', 'panduan-hotel', 'review-hotel', 'cari-hotel',
            'booking-hotel', 'reservasi-hotel', 'pesan-hotel', 'cek-hotel',
            'hotel-pilihan', 'hotel-favorit', 'hotel-andalan', 'hotel-incaran',
        ];
        foreach ($superPrefixes as $sp) {
            $prefix = "{$sp}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                foreach ($cities as $city) {
                    if ($rest === $city) {
                        return ['type' => 'filler-base', 'params' => ['pattern' => $sp, 'kw' => $city]];
                    }
                    foreach (SeoData::eventYears() as $y) {
                        if ($rest === "{$city}-{$y}") {
                            return ['type' => 'filler-year', 'params' => ['pattern' => $sp, 'kw' => $city, 'year' => $y]];
                        }
                    }
                }
            }
        }

        // ── Super mega 2: /{actionPrefix}-{city} ──
        $actionPrefixes = [
            'booking-cepat-hotel', 'reservasi-mudah-hotel', 'cek-harga-hotel',
            'info-hotel', 'jadwal-hotel', 'promo-hotel', 'diskon-hotel',
            'paket-hotel', 'hotel-plus', 'hotel-paket', 'hotel-deal',
            'hotel-promo', 'hotel-diskon', 'promo-spesial-hotel',
        ];
        foreach ($actionPrefixes as $ap) {
            $prefix = "{$ap}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                if (SeoData::isCity($rest)) {
                    return ['type' => 'filler-base', 'params' => ['pattern' => $ap, 'kw' => $rest]];
                }
            }
        }

        // ── Price combo: /{prefix}-{price}-{city} ──
        $pricePrefixes = ['hotel-dibawah', 'penginapan-dibawah', 'villa-dibawah', 'apartemen-dibawah'];
        $pricePoints = ['100rb', '150rb', '200rb', '250rb', '300rb', '350rb', '400rb',
                        '450rb', '500rb', '600rb', '750rb', '800rb', '1jt', '1-5jt',
                        '2jt', '2-5jt', '3jt', '5jt'];
        foreach ($pricePrefixes as $pp) {
            $prefix = "{$pp}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                foreach ($pricePoints as $price) {
                    $ps = "{$price}-";
                    if (str_starts_with($rest, $ps)) {
                        $city = substr($rest, strlen($ps));
                        if (SeoData::isCity($city)) {
                            return ['type' => 'price-city-expanded', 'params' => ['city' => $city, 'price' => $price]];
                        }
                    }
                }
            }
        }

        // ── Star cross: /hotel-bintang{star}-{city}-{price} ──
        if (preg_match('/^hotel-bintang([1-5])-/', $url, $m)) {
            $star = (int) $m[1];
            $rest = substr($url, strlen("hotel-bintang{$star}-"));
            foreach ($pricePoints as $p) {
                $suffix = "-{$p}";
                if (str_ends_with($rest, $suffix)) {
                    $city = substr($rest, 0, -strlen($suffix));
                    if (SeoData::isCity($city)) {
                        return ['type' => 'star-price-city', 'params' => ['star' => $star, 'city' => $city, 'price' => $p]];
                    }
                }
            }
        }

        // ── Status label: /{label}-{city} ──
        $labels = [
            'hotel-rekomendasi', 'hotel-unggulan', 'hotel-terseleksi', 'hotel-terpercaya',
            'hotel-resmi', 'hotel-terverifikasi', 'hotel-berstandar', 'hotel-berkualitas',
            'hotel-profesional', 'hotel-terjamin', 'hotel-amanah', 'hotel-terhandal',
        ];
        foreach ($labels as $label) {
            $prefix = "{$label}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                if (SeoData::isCity($rest)) {
                    return ['type' => 'filler-base', 'params' => ['pattern' => $label, 'kw' => $rest]];
                }
            }
        }

        // ── Triple combo: /hotel-{ht}-untuk-{tt}-{city} ──
        $topHt = array_slice($hotelTypes, 0, 12);
        $topTt = array_slice($tripTypes, 0, 8);
        foreach ($topHt as $ht) {
            $prefix = "hotel-{$ht}-untuk-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                foreach ($topTt as $tt) {
                    $ts = "{$tt}-";
                    if (str_starts_with($rest, $ts)) {
                        $city = substr($rest, strlen($ts));
                        if (SeoData::isCity($city)) {
                            return ['type' => 'third-tier-hotel-type', 'params' => ['hotelType' => $ht, 'city' => $city]];
                        }
                    }
                }
            }
        }

        // ── Trips × city × year: /hotel-untuk-{tt}-{city}-{year} ──
        foreach ($tripTypes as $tt) {
            $prefix = "hotel-untuk-{$tt}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                foreach ($cities as $city) {
                    foreach (SeoData::eventYears() as $y) {
                        $full = "{$city}-{$y}";
                        if ($rest === $full) {
                            return ['type' => 'filler-year', 'params' => ['pattern' => "hotel-untuk-{$tt}", 'kw' => $city, 'year' => $y]];
                        }
                    }
                }
            }
        }

        // ── Hotel type × city × year: /hotel-{ht}-{city}-{year} ──
        foreach (array_slice($hotelTypes, 0, 20) as $ht) {
            $prefix = "hotel-{$ht}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                foreach ($cities as $city) {
                    foreach (SeoData::eventYears() as $y) {
                        $full = "{$city}-{$y}";
                        if ($rest === $full) {
                            return ['type' => 'filler-year', 'params' => ['pattern' => "hotel-{$ht}", 'kw' => $city, 'year' => $y]];
                        }
                    }
                }
            }
        }

        // ── Hotel type × ALL cities: /hotel-{ht}-{city} (global catch-all for all 26 ht + all cities) ──
        foreach ($hotelTypes as $ht) {
            $prefix = "hotel-{$ht}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                if (SeoData::isCity($rest)) {
                    return ['type' => 'third-tier-hotel-type', 'params' => ['hotelType' => $ht, 'city' => $rest]];
                }
            }
        }

        // ── Transport routes: /{mode}-{c1}-{c2} ──
        $modes = ['pesawat', 'kereta', 'bus', 'travel', 'mobil', 'kapal'];
        foreach ($modes as $mode) {
            $prefix = "{$mode}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                foreach ($cities as $c1) {
                    if (str_starts_with($rest, "{$c1}-")) {
                        $c2 = substr($rest, strlen("{$c1}-"));
                        if (SeoData::isCity($c2) && $c1 !== $c2) {
                            return ['type' => 'double-city', 'params' => ['city1' => $c1, 'city2' => $c2]];
                        }
                    }
                }
            }
        }

        // ── Landmark star city: /hotel-bintang{star}-{city}-dekat-{lm} ──
        if (preg_match('/^hotel-bintang([1-5])-/', $url, $m)) {
            $star = (int) $m[1];
            $rest = substr($url, strlen("hotel-bintang{$star}-"));
            if (str_contains($rest, '-dekat-')) {
                $parts = explode('-dekat-', $rest, 2);
                if (SeoData::isCity($parts[0]) && in_array($parts[1], SeoData::SHORT_LANDMARKS)) {
                    return ['type' => 'star-landmark-city', 'params' => ['star' => $star, 'city' => $parts[0], 'landmark' => $parts[1]]];
                }
            }
        }

        // ── Occasion × landmark × city: /hotel-{occ}-{city}-dekat-{lm} ──
        foreach (array_slice(SeoData::ALL_OCCASIONS, 0, 8) as $occ) {
            $prefix = "hotel-{$occ}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                if (str_contains($rest, '-dekat-')) {
                    $parts = explode('-dekat-', $rest, 2);
                    if (SeoData::isCity($parts[0]) && in_array($parts[1], array_slice(SeoData::SHORT_LANDMARKS, 0, 10))) {
                        return ['type' => 'occasion-landmark-city', 'params' => ['occasion' => $occ, 'city' => $parts[0], 'landmark' => $parts[1]]];
                    }
                }
            }
        }

        // ── Source code × feature × city: /{kw}-{city}-fitur-{f} ──
        foreach (array_slice(SeoData::SOURCE_CODE_KEYWORDS, 0, 10) as $kw) {
            $prefix = "{$kw}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                if (str_contains($rest, '-fitur-')) {
                    foreach ($cities as $city) {
                        $cityPref = "{$city}-fitur-";
                        if (str_starts_with($rest, $cityPref)) {
                            $f = substr($rest, strlen($cityPref));
                            if (in_array($f, array_slice(SeoData::ROOM_FEATURES, 0, 15))) {
                                return ['type' => 'source-code-feature', 'params' => ['keyword' => $kw, 'city' => $city, 'feature' => $f]];
                            }
                        }
                    }
                }
            }
        }

        // ── District star: /hotel-bintang{star}-di-{d}-{city} ──
        if (preg_match('/^hotel-bintang([1-5])-di-/', $url, $m)) {
            $star = (int) $m[1];
            $rest = substr($url, strlen("hotel-bintang{$star}-di-"));
            foreach (SeoData::DISTRICTS as $city => $districts) {
                foreach ($districts as $d) {
                    $expected = "{$d}-{$city}";
                    if ($rest === $expected) {
                        return ['type' => 'district-star-city', 'params' => ['star' => $star, 'district' => $d, 'city' => $city]];
                    }
                }
            }
        }

        // ── City pairs: /hotel-{c1}-atau-{c2} and /liburan-{c1}-atau-{c2} ──
        if (str_contains($url, '-atau-')) {
            $parts = explode('-atau-', $url, 2);
            $c1 = str_replace(['hotel-', 'liburan-'], '', $parts[0]);
            $c2 = $parts[1];
            if (SeoData::isCity($c1) && SeoData::isCity($c2)) {
                return ['type' => 'double-city', 'params' => ['city1' => $c1, 'city2' => $c2]];
            }
        }

        // ── Distance radius: /hotel-{city}-dalam-{d}-dari-{center} ──
        if (str_starts_with($url, 'hotel-') && str_contains($url, '-dalam-')) {
            $rest = substr($url, 6);
            if (str_contains($rest, '-dari-')) {
                $parts = explode('-dalam-', $rest, 2);
                $citySlug = $parts[0];
                $rest2 = explode('-dari-', $parts[1], 2);
                $distance = $rest2[0] ?? '';
                $center = $rest2[1] ?? '';
                if (SeoData::isCity($citySlug) && $distance && $center) {
                    return ['type' => 'distance-radius', 'params' => ['city' => $citySlug, 'distance' => $distance, 'center' => $center]];
                }
            }
        }

        // ── Occasion × all city: /hotel-{occ}-di-{city} and /penginapan-{occ}-di-{city} ──
        foreach (SeoData::ALL_OCCASIONS as $occ) {
            foreach (['hotel', 'penginapan'] as $type) {
                $prefix = "{$type}-{$occ}-di-";
                if (str_starts_with($url, $prefix)) {
                    $rest = substr($url, strlen($prefix));
                    if (SeoData::isCity($rest)) {
                        return ['type' => 'occasion-all-city', 'params' => ['occasion' => $occ, 'city' => $rest]];
                    }
                }
            }
        }

        // ── Year extension: /{pat}-{city}-{year} (for 12 patterns) ──
        $yearPatterns = [
            'hotel-populer', 'hotel-favorit', 'hotel-rekomendasi', 'hotel-baru',
            'hotel-diskon', 'hotel-promo', 'hotel-spesial', 'hotel-edisi',
            'hotel-weekend', 'hotel-holiday', 'hotel-season', 'hotel-event',
        ];
        foreach ($yearPatterns as $yp) {
            $prefix = "{$yp}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                foreach ($cities as $city) {
                    if ($rest === $city) {
                        return ['type' => 'filler-base', 'params' => ['pattern' => $yp, 'kw' => $city]];
                    }
                }
                foreach ($cities as $city) {
                    foreach (SeoData::eventYears() as $y) {
                        if ($rest === "{$city}-{$y}") {
                            return ['type' => 'filler-year', 'params' => ['pattern' => $yp, 'kw' => $city, 'year' => $y]];
                        }
                    }
                }
            }
        }

        // ── Tag expansion: /hotel-{tag}-{city} and /hotel-{city}-{tag} ──
        $tags = ['murah', 'mahal', 'terbaik', 'termewah', 'terhemat', 'eksklusif',
                 'premium', 'standar', 'ekonomis', 'nyaman', 'bersih', 'aman'];
        foreach ($tags as $tag) {
            $prefix = "hotel-{$tag}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                if (SeoData::isCity($rest)) {
                    return ['type' => 'second-tier-city', 'params' => ['pattern' => "hotel-{$tag}", 'city' => $rest]];
                }
            }
        }
        // reverse: /hotel-{city}-{tag}
        foreach ($tags as $tag) {
            $suffix = "-{$tag}";
            if (str_ends_with($url, $suffix)) {
                $rest = substr($url, 6, -strlen($suffix)); // skip 'hotel-'
                if (SeoData::isCity($rest)) {
                    return ['type' => 'second-tier-city', 'params' => ['pattern' => $tag, 'city' => $rest]];
                }
            }
        }

        // ── Month variation: /hotel-{city}-bulan-{month}-{year} and /liburan-{city}-{month}-{year} ──
        foreach (['hotel', 'liburan'] as $pref) {
            $prefix = "{$pref}-";
            if (str_starts_with($url, $prefix)) {
                $rest = substr($url, strlen($prefix));
                if (str_contains($rest, '-bulan-')) {
                    foreach ($cities as $city) {
                        $cityPref = "{$city}-bulan-";
                        if (str_starts_with($rest, $cityPref)) {
                            $rest2 = substr($rest, strlen($cityPref));
                            foreach (SeoData::MONTHS as $month) {
                                foreach (SeoData::eventYears() as $y) {
                                    if ($rest2 === "{$month}-{$y}") {
                                        return ['type' => 'month-year-city', 'params' => ['city' => $city, 'month' => $month, 'year' => $y]];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // ── New massive patterns for final push to 1M ──
        // /hotel-dengan-{f}-{city}
        foreach (array_slice(SeoData::ROOM_FEATURES, 0, 20) as $f) {
            $prefix = "hotel-dengan-{$f}-";
            if (str_starts_with($url, $prefix)) {
                $cityName = substr($url, strlen($prefix));
                if (SeoData::isCity($cityName)) {
                    return ['type' => 'feature-city', 'params' => ['city' => $cityName, 'feature' => $f]];
                }
            }
        }
        // /hotel-{city}-fasilitas-{f}
        if (str_starts_with($url, 'hotel-') && str_contains($url, '-fasilitas-')) {
            $rest = substr($url, 6);
            $parts = explode('-fasilitas-', $rest, 2);
            if (count($parts) === 2 && SeoData::isCity($parts[0]) && in_array($parts[1], array_slice(SeoData::ROOM_FEATURES, 0, 20))) {
                return ['type' => 'feature-city', 'params' => ['city' => $parts[0], 'feature' => $parts[1]]];
            }
        }
        // /perbandingan-hotel-{c1}-{c2}
        if (str_starts_with($url, 'perbandingan-hotel-')) {
            $rest = substr($url, 19);
            foreach ($cities as $c1) {
                $pref = "{$c1}-";
                if (str_starts_with($rest, $pref)) {
                    $c2 = substr($rest, strlen($pref));
                    if (SeoData::isCity($c2)) {
                        return ['type' => 'compare-city-expanded', 'params' => ['a' => $c1, 'b' => $c2]];
                    }
                }
            }
        }
        // /hotel-{c1}-vs-hotel-{c2}
        if (str_starts_with($url, 'hotel-') && str_contains($url, '-vs-hotel-')) {
            $parts = explode('-vs-hotel-', substr($url, 6), 2);
            if (count($parts) === 2 && SeoData::isCity($parts[0]) && SeoData::isCity($parts[1])) {
                return ['type' => 'compare-city-expanded', 'params' => ['a' => $parts[0], 'b' => $parts[1]]];
            }
        }
        // /hotel-{q}-{ht}-{city}-{year} triple year
        $qualities = ['terbaik', 'ternyaman', 'terbersih', 'teraman', 'terlengkap',
                      'termewah', 'terpopuler', 'terfavorit', 'rekomendasi'];
        $htShort2 = ['butik', 'resor', 'bisnis', 'keluarga', 'mewah', 'hemat', 'syariah'];
        foreach ($qualities as $q) {
            $pref = "hotel-{$q}-";
            if (str_starts_with($url, $pref)) {
                $rest = substr($url, strlen($pref));
                foreach ($htShort2 as $ht2) {
                    $htPref2 = "{$ht2}-";
                    if (str_starts_with($rest, $htPref2)) {
                        $rest2 = substr($rest, strlen($htPref2));
                        foreach ($cities as $c2) {
                            $cPref = "{$c2}-";
                            if (str_starts_with($rest2, $cPref)) {
                                $year = (int) substr($rest2, strlen($cPref));
                                if (in_array($year, SeoData::eventYears())) {
                                    return ['type' => 'triple-year', 'params' => ['quality' => $q, 'hotelType' => $ht2, 'city' => $c2, 'year' => $year]];
                                }
                            }
                        }
                    }
                }
            }
        }
        // /hotel-{q}-{city} and /hotel-{city}-{q}
        foreach ($qualities as $q) {
            $pq = "hotel-{$q}-";
            if (str_starts_with($url, $pq)) {
                $citySlug = substr($url, strlen($pq));
                if (SeoData::isCity($citySlug)) {
                    return ['type' => 'second-tier-city', 'params' => ['pattern' => "hotel-{$q}", 'city' => $citySlug]];
                }
            }
            $sq = "-{$q}";
            if (str_starts_with($url, 'hotel-') && str_ends_with($url, $sq)) {
                $citySlug = substr($url, 6, -strlen($sq));
                if (SeoData::isCity($citySlug)) {
                    return ['type' => 'second-tier-city', 'params' => ['pattern' => $q, 'city' => $citySlug]];
                }
            }
        }
        // /hotel-{occ}-{ht}-{city}
        foreach (array_slice(SeoData::ALL_OCCASIONS, 0, 10) as $occ) {
            $po = "hotel-{$occ}-";
            if (str_starts_with($url, $po)) {
                $rest = substr($url, strlen($po));
                foreach ($htShort2 as $ht2) {
                    $htPref2 = "{$ht2}-";
                    if (str_starts_with($rest, $htPref2)) {
                        $citySlug = substr($rest, strlen($htPref2));
                        if (SeoData::isCity($citySlug)) {
                            return ['type' => 'occasion-hotel-type-city', 'params' => ['occasion' => $occ, 'hotelType' => $ht2, 'city' => $citySlug]];
                        }
                    }
                }
            }
        }

        return null;
    }

    protected function extractCity(string $slug, array $cities): ?string
    {
        foreach ($cities as $city) {
            if ($slug === $city) return $city;
            if (str_ends_with($slug, "-{$city}")) return $city;
            if (str_starts_with($slug, "{$city}-")) return $city;
        }
        return null;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Source code selling page builder
    // ═══════════════════════════════════════════════════════════════════════
    protected function buildSourceCodePage(string $type, array $params, $property): array
    {
        $kw = $params['keyword'] ?? '';
        $kwLabel = Str::title(str_replace('-', ' ', $kw));

        $title = match ($type) {
            'source-code-beli' => "Beli {$kwLabel} — Source Code HotelHub HMS Lengkap",
            'source-code-harga' => "Harga {$kwLabel} — Biaya & Paket Source Code",
            'source-code-download' => "Download {$kwLabel} — Source Code Laravel 11",
            'source-code-best' => "{$kwLabel} Terbaik — HotelHub HMS All-in-One",
            'source-code-harga-price' => "Harga {$kwLabel} Mulai Rp " . strtoupper($params['price'] ?? '')
                . " — Source Code Hotel",
            default => match (true) {
                isset($params['city']) => "{$kwLabel} di " . (SeoData::cityName($params['city']) ?? Str::title(str_replace('-', ' ', $params['city'])))
                    . (isset($params['price']) ? " Mulai Rp " . strtoupper($params['price']) : ''),
                isset($params['kw2']) => "{$kwLabel} vs " . Str::title(str_replace('-', ' ', $params['kw2'])),
                default => $kwLabel,
            },
        };

        $description = match ($type) {
            'source-code-beli' => "Beli {$kwLabel} — source code lengkap Laravel 11 sistem manajemen hotel. HotelHub HMS: Front Office, POS, Accounting, Channel Manager, 23+ modul siap pakai. Chat WA 081296052010.",
            'source-code-download' => "Download {$kwLabel} — source code Laravel 11 HotelHub HMS all-in-one. Dapatkan sistem hotel lengkap dengan 122_test, dokumentasi, dan white-label siap deploy.",
            default => "{$title}. Source code Laravel 11 HotelHub HMS — sistem manajemen hotel all-in-one 23+ modul. Self-host, white-label, BYOK payment & AI. Info lengkap: 081296052010.",
        };

        return [
            'view' => 'pseo.source-code',
            'type' => $type,
            'params' => $params,
            'property' => $property,
            'title' => $title,
            'kw_label' => $kwLabel,
            'intro' => $this->content->genericIntro('source-code', $params),
            'faqs' => $this->content->defaultFaqs($kwLabel, 'source-code'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs($kwLabel, 'source-code')),
        ];
    }

    protected function buildFeaturePage(string $type, array $params, $property): array
    {
        $city = $params['city'] ?? 'jakarta';
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $feat = $params['feature'] ?? ($params['f1'] ?? '');
        $featName = Str::title(str_replace('-', ' ', $feat));

        $title = match ($type) {
            'double-feature-city' => "Hotel {$cityName} dengan {$featName} dan "
                . Str::title(str_replace('-', ' ', $params['f2'] ?? '')),
            'occasion-feature-city' => "Hotel " . Str::title($params['occasion'] ?? '')
                . " {$cityName} dengan {$featName}",
            default => "Hotel {$cityName} dengan {$featName} — Pilihan Kamar Terbaik",
        };

        return [
            'view' => 'pseo.city-listing',
            'city' => $city,
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->genericIntro($type, $params),
            'faqs' => $this->content->defaultFaqs("{$cityName} {$featName}", 'feature-city'),
            'meta_description' => "{$title}. HotelHub HMS — sistem manajemen hotel lengkap. Source code Laravel 11, 23+ modul, white-label. WA 081296052010.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$cityName} {$featName}", 'feature-city')),
        ];
    }

    protected function buildGeoPage(string $type, array $params, $property): array
    {
        $cityName = '';
        $title = '';

        if ($type === 'double-city') {
            $c1 = SeoData::cityName($params['city1']) ?? Str::title(str_replace('-', ' ', $params['city1']));
            $c2 = SeoData::cityName($params['city2']) ?? Str::title(str_replace('-', ' ', $params['city2']));
            $cityName = $c1;
            $title = "Hotel {$c1} ke {$c2} — Panduan Perjalanan Lengkap";
        } elseif ($type === 'district-city') {
            $c = SeoData::cityName($params['city']) ?? Str::title(str_replace('-', ' ', $params['city']));
            $d = Str::title(str_replace('-', ' ', $params['district']));
            $cityName = $c;
            $title = "Hotel di {$d}, {$c} — Akomodasi Strategis";
        } elseif ($type === 'compare-city-expanded') {
            $c1 = SeoData::cityName($params['a']) ?? Str::title(str_replace('-', ' ', $params['a']));
            $c2 = SeoData::cityName($params['b']) ?? Str::title(str_replace('-', ' ', $params['b']));
            $cityName = $c1;
            $title = "Bandingkan Hotel {$c1} vs {$c2} — Perbandingan Lengkap";
        } elseif ($type === 'month-year-city') {
            $c = SeoData::cityName($params['city']) ?? Str::title(str_replace('-', ' ', $params['city']));
            $m = Str::title($params['month']);
            $y = $params['year'];
            $cityName = $c;
            $title = "Hotel {$c} {$m} {$y} — Panduan & Promo Musiman";
        }

        return [
            'view' => 'pseo.content-page',
            'page_type' => $type,
            'city' => $params['city'] ?? $params['city1'] ?? 'jakarta',
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->genericIntro($type, $params),
            'faqs' => $this->content->defaultFaqs($cityName, 'city'),
            'meta_description' => "{$title}. HotelHub HMS — source code hotel Laravel 11. 23+ modul, self-host, responsive. Info: 081296052010.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs($cityName, 'city')),
        ];
    }

    protected function buildComboPage(string $type, array $params, $property): array
    {
        $city = $params['city'] ?? 'jakarta';
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));

        $title = match ($type) {
            'amenity-city-price' => "Hotel " . Str::title(str_replace('-', ' ', $params['amenity']))
                . " {$cityName} di Bawah Rp " . strtoupper($params['price']),
            'star-price-city' => "Hotel Bintang {$params['star']} {$cityName} Rp "
                . strtoupper($params['price']),
            'guest-feature-city' => "Hotel untuk " . Str::title(str_replace('-', ' ', $params['guestType']))
                . " {$cityName} dengan " . Str::title(str_replace('-', ' ', $params['feature'])),
            'room-type-feature-city' => "Kamar " . Str::title(str_replace('-', ' ', $params['roomType']))
                . " {$cityName} dengan " . Str::title(str_replace('-', ' ', $params['feature'])),
            'price-city-expanded' => "Hotel {$cityName} Harga Rp " . strtoupper($params['price']),
            'content-topic-city' => "Tips " . Str::title(str_replace('-', ' ', $params['topic']))
                . " Hotel {$cityName} — Panduan Lengkap",
            default => "Hotel {$cityName} — Pilihan Akomodasi Terbaik",
        };

        return [
            'view' => 'pseo.city-listing',
            'city' => $city,
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->genericIntro($type, $params),
            'faqs' => $this->content->defaultFaqs($cityName, 'city'),
            'meta_description' => "{$title}. HotelHub HMS — source code hotel Laravel 11. 23+ modul, self-host, white-label. WA 081296052010.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs($cityName, 'city')),
        ];
    }

    protected function buildFillerPage(string $type, array $params, $property): array
    {
        $pat = Str::title(str_replace('-', ' ', $params['pattern'] ?? $params['hotelType'] ?? $params['tripType'] ?? 'Hotel'));
        $city = $params['city'] ?? $params['kw'] ?? 'indonesia';
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));

        $title = match ($type) {
            'third-tier-hotel-type' => $pat . " {$cityName} — Pilihan Akomodasi Terbaik",
            'third-tier-trip-type' => "Hotel untuk " . Str::title(str_replace('-', ' ', $params['tripType']))
                . " {$cityName} — Akomodasi Ideal",
            'second-tier-city-year' => $pat . " {$cityName} {$params['year']} — Pilihan Update",
            default => $pat . " {$cityName} — Panduan Lengkap",
        };

        return [
            'view' => 'pseo.city-listing',
            'city' => $city,
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->genericIntro($type, $params),
            'faqs' => $this->content->defaultFaqs($cityName, 'city'),
            'meta_description' => "{$title}. HotelHub HMS — source code sistem hotel Laravel 11 all-in-one. Self-host, BYOK payment, 23+ modul. Info: 081296052010.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs($cityName, 'city')),
        ];
    }

    protected function buildSeoData(string $type, array $params): array
    {
        $property = Property::first();

        return match ($type) {
            'occasion' => $this->buildOccasionData($params['city'], $params['occasion'], $property),
            // Source code patterns
            'source-code-beli', 'source-code-harga', 'source-code-download',
            'source-code-best', 'source-code-beli-city', 'source-code-harga-city',
            'source-code-city', 'source-code-city-murah',
            'source-code-harga-price', 'source-code-city-price',
            'source-code-jasa', 'source-code-paket', 'source-code-vs',
            'source-code-district', 'source-code-path',
            => $this->buildSourceCodePage($type, $params, $property),
            // Feature patterns
            'feature-city', 'double-feature-city', 'occasion-feature-city',
            => $this->buildFeaturePage($type, $params, $property),
            // Geo patterns
            'double-city', 'district-city', 'compare-city-expanded',
            'month-year-city',
            => $this->buildGeoPage($type, $params, $property),
            // Amenity patterns
            'amenity-city-price', 'star-price-city', 'guest-feature-city',
            'room-type-feature-city', 'price-city-expanded', 'content-topic-city',
            => $this->buildComboPage($type, $params, $property),
            // Filler patterns
            'filler-base', 'filler-murah', 'filler-year',
            'second-tier-city', 'second-tier-city-year',
            'third-tier-hotel-type', 'third-tier-trip-type',
            'star-landmark-city', 'occasion-landmark-city', 'source-code-feature',
            'district-star-city', 'distance-radius', 'occasion-all-city',
            'triple-year', 'occasion-hotel-type-city', 'room-type-city-price',
            => $this->buildFillerPage($type, $params, $property),
            default => $this->buildFallbackData($type, $params, $property),
        };
    }

    protected function buildOccasionData(string $city, string $occasion, $property): array
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $occName = Str::title($occasion);
        $title = "Akomodasi {$occName} di {$cityName}";

        return [
            'view' => 'pseo.city-listing',
            'city' => $city,
            'city_name' => $cityName,
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->genericIntro('occasion', ['city' => $cityName, 'occasion' => $occName]),
            'faqs' => $this->content->defaultFaqs("{$occName} {$cityName}", 'occasion'),
            'meta_description' => "Pilihan akomodasi {$occName} terbaik di {$cityName} — paket lengkap, harga kompetitif.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$occName} {$cityName}", 'occasion')),
        ];
    }

    protected function buildFallbackData(string $type, array $params, $property): array
    {
        $title = Str::title(str_replace('-', ' ', $type ?? 'Halaman'));
        return [
            'view' => 'pseo.city-listing',
            'city' => $params['city'] ?? 'jakarta',
            'city_name' => $params['city'] ?? 'Jakarta',
            'property' => $property,
            'title' => $title,
            'intro' => $this->content->genericIntro($type, $params),
            'faqs' => $this->content->defaultFaqs(($params['city'] ?? ''), 'generic'),
            'meta_description' => "Halaman SEO untuk {$title}. Informasi lengkap dan panduan praktis.",
            'schema' => $this->schema->faqPage($this->content->defaultFaqs(($params['city'] ?? ''), 'generic')),
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ─── private render helpers ───────────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════

    protected function renderCheap(string $city, bool $isCheapest)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $prefix = $isCheapest ? 'Termurah' : 'Murah';
        $title = "Hotel {$prefix} di {$cityName} — Budget Hemat " . date('Y');
        $description = "Daftar hotel {$prefix} di {$cityName} — under budget, tetap nyaman. Harga mulai Rp 100rb/malam. Cocok untuk backpacker & hemat traveler.";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->cheapHotelIntro($city, $isCheapest),
            'faqs' => $this->content->defaultFaqs("{$prefix} {$cityName}", 'cheap'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$prefix} {$cityName}", 'cheap')),
        ]);
    }

    protected function renderNearTransport(string $city, string $type)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $label = $type === 'bandara' ? 'Bandara' : 'Stasiun';
        $title = "Hotel {$cityName} Dekat {$label} — Akses Cepat & Praktis";
        $description = "Hotel dekat {$type} di {$cityName}. Shuttle gratis, check-in fleksibel, kedap suara. Ideal untuk transit dan penerbangan pagi.";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->nearTransportIntro($city, $type),
            'faqs' => $this->content->defaultFaqs("dekat {$label} {$cityName}", 'near-transport'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("dekat {$label} {$cityName}", 'near-transport')),
        ]);
    }

    protected function renderAmenity(string $city, string $amenity)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $amenityLabels = [
            'kolam-renang' => 'Kolam Renang',
            'sarapan-gratis' => 'Sarapan Gratis',
            'parkir-luas' => 'Parkir Luas',
            'ramah-keluarga' => 'Ramah Keluarga',
            'untuk-backpacker' => 'Untuk Backpacker',
        ];
        $amenityLabel = $amenityLabels[$amenity] ?? Str::title(str_replace('-', ' ', $amenity));
        $title = "Hotel {$amenityLabel} di {$cityName} — Pilihan Nyaman " . date('Y');
        $description = "Daftar hotel dengan {$amenityLabel} di {$cityName}. Fasilitas lengkap, rating tamu tinggi. Booking langsung, harga terbaik!";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->amenityHotelIntro($city, $amenity),
            'faqs' => $this->content->defaultFaqs("{$amenityLabel} {$cityName}", 'amenity'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$amenityLabel} {$cityName}", 'amenity')),
        ]);
    }

    protected function renderAltAccommodation(string $type, string $city)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $labels = ['penginapan' => 'Penginapan Murah', 'apartemen' => 'Apartemen Sewa Harian', 'villa' => 'Villa', 'guesthouse' => 'Guesthouse'];
        $label = $labels[$type] ?? Str::title($type);
        $title = "{$label} di {$cityName} — Pilihan Akomodasi Alternatif";
        $description = "Daftar {$label} di {$cityName}. Harga mulai Rp 150rb/malam. Lebih privat, lebih lega, lebih fleksibel. Booking instan!";

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'property' => Property::first(),
            'title' => $title,
            'intro' => $this->content->altAccommodationIntro($type, $city),
            'faqs' => $this->content->defaultFaqs("{$label} {$cityName}", 'alt-accommodation'),
            'meta_description' => $description,
            'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$label} {$cityName}", 'alt-accommodation')),
        ]);
    }

    protected function renderContentPage(string $pageType, string $city, ?int $year = null)
    {
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $property = Property::first();

        return match ($pageType) {
            'tips' => view('pseo.content-page', [
                'page_type' => 'tips',
                'city' => $city,
                'city_name' => $cityName,
                'property' => $property,
                'title' => "Tips Memilih Hotel di {$cityName} — Panduan Lengkap " . date('Y'),
                'intro' => $this->content->tipsIntro($city),
                'faqs' => $this->content->defaultFaqs("{$cityName}", 'tips'),
                'meta_description' => "Tips memilih hotel terbaik di {$cityName}: lokasi, budget, fasilitas, dan strategi booking. Panduan praktis untuk first-timer & repeat visitor.",
                'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$cityName}", 'tips')),
            ]),
            'guide' => view('pseo.content-page', [
                'page_type' => 'guide',
                'city' => $city,
                'city_name' => $cityName,
                'property' => $property,
                'title' => "Panduan Wisata {$cityName} — Itinerary & Tips Lengkap " . date('Y'),
                'intro' => $this->content->travelGuideIntro($city),
                'faqs' => $this->content->defaultFaqs("{$cityName}", 'travel-guide'),
                'meta_description' => "Panduan wisata {$cityName} lengkap: waktu terbaik, cara ke sana, atraksi wajib, hotel rekomendasi, budget, dan tips lokal.",
                'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$cityName}", 'travel-guide')),
            ]),
            'events' => view('pseo.content-page', [
                'page_type' => 'events',
                'city' => $city,
                'city_name' => $cityName,
                'year' => $year ?? (int) date('Y'),
                'property' => $property,
                'title' => "Event & Festival di {$cityName} {$year} — Kalender Acara Lengkap",
                'intro' => $this->content->eventsIntro($city, $year ?? (int) date('Y')),
                'faqs' => $this->content->defaultFaqs("{$cityName} {$year}", 'events'),
                'meta_description' => "Event dan festival di {$cityName} tahun {$year}. Kalender acara lengkap: budaya, musik, kuliner, olahraga. Booking hotel sekarang!",
                'schema' => $this->schema->faqPage($this->content->defaultFaqs("{$cityName} {$year}", 'events')),
            ]),
            default => abort(404),
        };
    }

    // ─── helpers ──────────────────────────────────────────────────────────
    protected function renderBest(string $category, ?string $year)
    {
        $property = Property::first();
        $items = $property
            ? RoomType::where('property_id', $property->id)->where('is_active', true)->get()
            : collect();

        $catName = Str::title(str_replace('-', ' ', $category));
        $title = $year
            ? "Hotel/Akomodasi {$catName} Terbaik {$year}"
            : "Hotel/Akomodasi {$catName} Terbaik";

        $intro = $this->content->bestCategoryIntro($category, $year);
        $faqs = $this->content->defaultFaqs($category, 'best-category');

        return view('pseo.best-listing', [
            'title' => $title,
            'category' => $category,
            'category_name' => $catName,
            'year' => $year,
            'items' => $items,
            'property' => $property,
            'intro' => $intro,
            'faqs' => $faqs,
            'meta_description' => "Daftar terbaik {$catName}".($year ? " untuk {$year}" : '').'. Pilih akomodasi terbaik dengan harga kompetitif.',
            'schema' => array_merge(
                $this->schema->itemList($title, $items->map(fn ($r) => ['name' => $r->name])->all()),
                ['@graph_extra_faq' => $this->schema->faqPage($faqs)]
            ),
        ]);
    }

    protected function cityListing(
        string $city,
        ?string $neighborhood = null,
        ?string $year = null,
        ?string $price = null,
        ?string $variant = null,
    ) {
        $property = Property::first();
        $cityName = SeoData::cityName($city) ?? Str::title(str_replace('-', ' ', $city));
        $neighborhoodName = $neighborhood
            ? Str::title(str_replace('-', ' ', $neighborhood))
            : null;

        $title = match (true) {
            $variant === 'pet-friendly' => "Hotel Pet-Friendly di {$cityName}",
            (bool) $neighborhood        => "Hotel di {$cityName}, {$neighborhoodName}",
            (bool) $year                => "Hotel Terbaik di {$cityName} {$year}",
            (bool) $price               => "Hotel di {$cityName} di Bawah Rp ".strtoupper($price),
            default                     => "Hotel di {$cityName}",
        };

        $intro = $this->content->cityListingIntro($city, $neighborhood, $year, $price);
        $faqs = $this->content->defaultFaqs($cityName, 'city');

        return view('pseo.city-listing', [
            'city' => $city,
            'city_name' => $cityName,
            'neighborhood' => $neighborhood,
            'neighborhood_name' => $neighborhoodName,
            'year' => $year,
            'price' => $price,
            'variant' => $variant,
            'property' => $property,
            'title' => $title,
            'intro' => $intro,
            'faqs' => $faqs,
            'meta_description' => "Daftar hotel di {$cityName}".($neighborhood ? ", kawasan {$neighborhoodName}" : '').'. Reservasi langsung, harga terbaik.',
            'schema' => $this->schema->faqPage($faqs),
        ]);
    }
}
