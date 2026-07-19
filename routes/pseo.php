<?php

use App\Http\Controllers\Pseo\PseoController;
use App\Http\Controllers\Pseo\SitemapController;
use Illuminate\Support\Facades\Route;

// Sitemap & robots — public, no license gate (crawlers must reach these)
Route::middleware(['throttle:pseo', 'pseo.cache'])->group(function () {
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
    Route::get('/sitemap-{group}.xml', [SitemapController::class, 'group'])
        ->where('group', '[a-z-]+')->name('sitemap.group');
});

Route::middleware(['throttle:pseo', 'license', 'pseo.cache'])->group(function () {
    // Wajib (sesuai global rule)
    Route::get('/best-{category}', [PseoController::class, 'bestCategory'])->name('pseo.best');
    Route::get('/best-{category}-{year}', [PseoController::class, 'bestCategoryYear'])
        ->where('year', '[0-9]{4}')->name('pseo.best.year');
    Route::get('/alternatives-to-{slug}', [PseoController::class, 'alternativesTo'])->name('pseo.alternatives');
    Route::get('/compare/{a}-vs-{b}', [PseoController::class, 'compare'])->name('pseo.compare');

    // Hotel-specific extras
    Route::get('/hotels-in-{city}', [PseoController::class, 'hotelsInCity'])->name('pseo.city');
    Route::get('/hotels-in-{city}-{neighborhood}', [PseoController::class, 'hotelsInNeighborhood'])->name('pseo.neighborhood');
    Route::get('/{city}-hotels-near-{landmark}', [PseoController::class, 'hotelsNearLandmark'])->name('pseo.landmark');
    Route::get('/best-hotels-{city}-{year}', [PseoController::class, 'bestHotelsCityYear'])
        ->where('year', '[0-9]{4}')->name('pseo.best-hotels-city');
    Route::get('/hotels-under-{price}-{city}', [PseoController::class, 'hotelsUnderPriceCity'])->name('pseo.budget');
    Route::get('/villas-with-{feature}-{location}', [PseoController::class, 'villasWithFeature'])->name('pseo.villa.feature');
    Route::get('/{occasion}-stay-{city}', [PseoController::class, 'occasionStay'])
        ->where('occasion', 'honeymoon|family|business|romantic|wedding')->name('pseo.occasion');
    Route::get('/things-to-do-near-{slug}', [PseoController::class, 'thingsToDo'])->name('pseo.things');
    Route::get('/best-time-to-visit-{city}', [PseoController::class, 'bestTimeToVisit'])->name('pseo.best-time');
    Route::get('/pet-friendly-hotels-{city}', [PseoController::class, 'petFriendly'])->name('pseo.pet');
    Route::get('/{landmark}-hotels', [PseoController::class, 'landmarkHotels'])->name('pseo.landmark-hotels');

    // OG image generation
    Route::get('/og/{type}/{slug}.png', [PseoController::class, 'ogImage'])->name('pseo.og');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Star rating — /hotel-{star}-bintang-{city}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-{star}-bintang-{city}', [PseoController::class, 'hotelByStar'])
        ->where('star', '[1-5]')->name('pseo.star');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Price-based — /hotel-murah-{city} & /hotel-termurah-di-{city}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-murah-{city}', [PseoController::class, 'cheapHotels'])->name('pseo.cheap');
    Route::get('/hotel-termurah-di-{city}', [PseoController::class, 'cheapestHotels'])->name('pseo.cheapest');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Nearby locations — /hotel-dekat-{landmark} (short)
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-dekat-{landmark}', [PseoController::class, 'nearLandmarkShort'])->name('pseo.near-landmark-short');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Near airport/station — /hotel-{city}-dekat-bandara & -stasiun
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-{city}-dekat-bandara', [PseoController::class, 'nearAirport'])->name('pseo.near-airport');
    Route::get('/hotel-{city}-dekat-stasiun', [PseoController::class, 'nearStation'])->name('pseo.near-station');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Amenity-based — /hotel-{city}-{amenity}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-{city}-kolam-renang', [PseoController::class, 'withPool'])->name('pseo.with-pool');
    Route::get('/hotel-{city}-sarapan-gratis', [PseoController::class, 'withBreakfast'])->name('pseo.with-breakfast');
    Route::get('/hotel-{city}-parkir-luas', [PseoController::class, 'withParking'])->name('pseo.with-parking');
    Route::get('/hotel-{city}-ramah-keluarga', [PseoController::class, 'familyFriendly'])->name('pseo.family');
    Route::get('/hotel-{city}-untuk-backpacker', [PseoController::class, 'backpacker'])->name('pseo.backpacker');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Alternative accommodation — /penginapan|apartemen|villa|guesthouse-{city}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/penginapan-{city}', [PseoController::class, 'lodging'])->name('pseo.lodging');
    Route::get('/apartemen-{city}', [PseoController::class, 'apartments'])->name('pseo.apartments');
    Route::get('/villa-{city}', [PseoController::class, 'villas'])->name('pseo.villas');
    Route::get('/guesthouse-{city}', [PseoController::class, 'guesthouses'])->name('pseo.guesthouses');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Content pages — /tips-memilih-hotel-{city} & /panduan-wisata-{city}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/tips-memilih-hotel-{city}', [PseoController::class, 'tips'])->name('pseo.tips');
    Route::get('/panduan-wisata-{city}', [PseoController::class, 'travelGuide'])->name('pseo.guide');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Weather & events — /cuaca-{city}-bulan-{month} & /event-{city}-{year}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/cuaca-{city}-bulan-{month}', [PseoController::class, 'weather'])->name('pseo.weather');
    Route::get('/event-{city}-{year}', [PseoController::class, 'events'])
        ->where('year', '[0-9]{4}')->name('pseo.events');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Recommendation — /rekomendasi-hotel-{occasion}-{city}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/rekomendasi-hotel-{occasion}-{city}', [PseoController::class, 'recommendation'])->name('pseo.recommendation');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Area/neighborhood — /area-{neighborhood}-{city}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/area-{neighborhood}-{city}', [PseoController::class, 'neighborhoodArea'])->name('pseo.area');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Popular & new — /hotel-populer-di-{city} & /hotel-baru-di-{city}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-populer-di-{city}', [PseoController::class, 'popularHotels'])->name('pseo.popular');
    Route::get('/hotel-baru-di-{city}', [PseoController::class, 'newHotels'])->name('pseo.new');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Granular price — /hotel-{city}-di-bawah-{price}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-{city}-di-bawah-{price}', [PseoController::class, 'granularPrice'])
        ->name('pseo.granular-price');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Price range — /hotel-{city}-{min}-{max}-ribu
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-{city}-{min}-{max}-ribu', [PseoController::class, 'priceRange'])
        ->where('min', '[0-9]+')->where('max', '[0-9]+')
        ->name('pseo.price-range');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Room type by city — /kamar-{type}-{city} & /harga-kamar-{type}-{city}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/kamar-{type}-{city}', [PseoController::class, 'roomTypeCity'])
        ->name('pseo.room-type');
    Route::get('/harga-kamar-{type}-{city}', [PseoController::class, 'roomTypePrice'])
        ->name('pseo.room-type-price');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Guest type by city — /hotel-untuk-{type}-{city}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-untuk-{type}-{city}', [PseoController::class, 'guestTypeCity'])
        ->name('pseo.guest-type');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Season by city — /hotel-{city}-musim-{season}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-{city}-musim-{season}', [PseoController::class, 'seasonCity'])
        ->name('pseo.season');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Holiday by city — /hotel-{city}-liburan-{holiday}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-{city}-liburan-{holiday}', [PseoController::class, 'holidayCity'])
        ->name('pseo.holiday');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Distance from center — /hotel-{city}-jarak-{distance}-km-dari-pusat
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-{city}-jarak-{distance}-km-dari-pusat', [PseoController::class, 'distanceCity'])
        ->where('distance', '[0-9]+')
        ->name('pseo.distance-city');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Distance from landmark — /hotel-dekat-{landmark}-jarak-{distance}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/hotel-dekat-{landmark}-jarak-{distance}', [PseoController::class, 'distanceLandmark'])
        ->name('pseo.distance-landmark');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Question-based pages — /{prefix}-{city}-{suffix}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/apakah-{city}-aman-untuk-wisatawan', [PseoController::class, 'questionSafe'])
        ->name('pseo.question.safe');
    Route::get('/kapan-{city}-waktu-terbaik-ke', [PseoController::class, 'questionWhen'])
        ->name('pseo.question.when');
    Route::get('/berapa-{city}-biaya-hotel-di', [PseoController::class, 'questionCost'])
        ->name('pseo.question.cost');
    Route::get('/bagaimana-{city}-cara-ke', [PseoController::class, 'questionHow'])
        ->name('pseo.question.how');
    Route::get('/apa-saja-{city}-wisata-di', [PseoController::class, 'questionWhat'])
        ->name('pseo.question.what');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Compare cities — /bandingkan-{a}-vs-{b}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/bandingkan-{a}-vs-{b}', [PseoController::class, 'compareCities'])
        ->name('pseo.compare-cities');

    // ═══════════════════════════════════════════════════════════════════════
    // NEW: Compare neighborhoods — /bandingkan-hotel-{city}-{n1}-vs-{n2}
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/bandingkan-hotel-{city}-{a}-vs-{b}', [PseoController::class, 'compareNeighborhoods'])
        ->name('pseo.compare-neighborhoods');

    // ═══════════════════════════════════════════════════════════════════════
    // CATCH-ALL GENERIC HANDLER — catches all remaining PSEO patterns
    // Must be the LAST route to avoid shadowing specific routes above.
    // ═══════════════════════════════════════════════════════════════════════
    Route::get('/{slug}', [PseoController::class, 'generic'])
        ->where('slug', '.*')
        ->name('pseo.generic');
});

// ═══════════════════════════════════════════════════════════════════════
// Source code selling routes — public facing, high SEO priority
// ═══════════════════════════════════════════════════════════════════════
Route::get('/source-code/{slug}', [PseoController::class, 'generic'])
    ->where('slug', '.*')
    ->middleware(['throttle:pseo', 'license', 'pseo.cache']);
