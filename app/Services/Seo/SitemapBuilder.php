<?php

namespace App\Services\Seo;

use App\Models\Landmark;
use App\Models\Property;
use App\Models\RoomType;
use App\Support\SeoData;

class SitemapBuilder
{
    /** Maximum URLs per individual sitemap file (~2MB). */
    const MAX_URLS_PER_SITEMAP = 50000;

    /**
     * Return all sitemap group names with chunk suffixes for groups exceeding 50K URLs.
     */
    public function index(): array
    {
        $groups = [];

        foreach ($this->patternGroups() as $group => $estimate) {
            $chunks = max(1, (int) ceil($estimate / self::MAX_URLS_PER_SITEMAP));

            if ($chunks <= 1) {
                $groups[] = $group;
            } else {
                for ($i = 1; $i <= $chunks; $i++) {
                    $groups[] = $group . '-' . $i;
                }
            }
        }

        return $groups;
    }

    /**
     * Define all pattern groups with their estimated URL counts.
     */
    protected function patternGroups(): array
    {
        $cityCount = count(SeoData::CITIES);
        $scCityCount = count(SeoData::scCities());
        $districtCount = array_sum(array_map('count', SeoData::DISTRICTS));

        return [
            'pages'                      => 8,
            'rooms'                      => 50,
            'blogs'                      => 30,
            'pseo-best'                  => 48,
            'pseo-compare'               => 30,
            'pseo-cities'                => $cityCount * 5,
            'pseo-cities-occasion'       => $cityCount * count(SeoData::OCCASIONS),
            'pseo-cities-budget'         => $cityCount * count(SeoData::PRICE_TIERS),
            'pseo-cities-neighborhood'   => array_sum(array_map('count', SeoData::NEIGHBORHOODS)),
            'pseo-landmark'              => count(SeoData::LANDMARKS) * 2,
            'pseo-villa-feature'         => $cityCount * count(SeoData::VILLA_FEATURES),
            'pseo-star-city'             => $cityCount * count(SeoData::STARS),
            'pseo-price-city'            => $cityCount * 2,
            'pseo-nearby'                => $cityCount * 2,
            'pseo-amenity'               => $cityCount * count(SeoData::AMENITIES),
            'pseo-short-landmark'        => count(SeoData::SHORT_LANDMARKS),
            'pseo-alt-accommodation'     => $cityCount * 4,
            'pseo-content'               => $cityCount * 2,
            'pseo-weather'               => $cityCount * 12,
            'pseo-events'                => $cityCount * 3,
            'pseo-recommendation'        => $cityCount * count(SeoData::SEARCH_OCCASIONS),
            'pseo-area'                  => array_sum(array_map('count', SeoData::NEIGHBORHOODS)),
            'pseo-popular-new'           => $cityCount * 2,
            'pseo-granular-price'        => $cityCount * count(SeoData::GRANULAR_PRICES),
            'pseo-price-range'           => count(SeoData::priceRanges()),
            'pseo-room-type-city'        => $cityCount * count(SeoData::ROOM_TYPES) * 2,
            'pseo-guest-type-city'       => $cityCount * count(SeoData::GUEST_TYPES),
            'pseo-season-city'           => $cityCount * 2,
            'pseo-holiday-city'          => $cityCount * count(SeoData::HOLIDAYS),
            'pseo-distance-city'         => $cityCount * count(SeoData::DISTANCES),
            'pseo-distance-landmark'     => 80,
            'pseo-question'              => $cityCount * 5,
            'pseo-compare-cities'        => count(SeoData::compareCities()),
            'pseo-compare-neighborhoods' => count(SeoData::allCompareNeighborhoodUrls()),
            // ── NEW MASSIVE GROUPS ──
            'pseo-sc-base'               => count(SeoData::SOURCE_CODE_KEYWORDS) + count(SeoData::SOURCE_CODE_KEYWORDS) + count(SeoData::SOURCE_CODE_KEYWORDS), // allSourceCodeUrls + allSourceCodeDownloadUrls + allSourceCodeBestUrls
            'pseo-sc-city'               => count(SeoData::allSourceCodeCityUrls()),
            'pseo-sc-price'              => count(SeoData::allSourceCodePriceUrls()),
            'pseo-sc-city-price'         => count(SeoData::allSourceCodeCityPriceUrls()),
            'pseo-sc-city-murah'         => count(SeoData::allSourceCodeCityMurahUrls()),
            'pseo-sc-jasa'               => count(SeoData::allSourceCodeJasaUrls()),
            'pseo-sc-paket'              => count(SeoData::allSourceCodePaketUrls()),
            'pseo-sc-vs'                 => count(SeoData::allSourceCodeVsUrls()),
            'pseo-sc-district'           => count(SeoData::allSourceCodeDistrictUrls()),
            'pseo-sc-massive'            => count(SeoData::allSourceCodeMassiveUrls()),
            'pseo-feature-city'          => $cityCount * count(SeoData::ROOM_FEATURES),
            'pseo-double-feature-city'   => $cityCount * count(SeoData::FEATURE_COMBOS),
            'pseo-occasion-feature-city' => count(SeoData::allOccasionFeatureCityUrls()),
            'pseo-double-city'           => count(SeoData::allDoubleCityUrls()),
            'pseo-compare-expanded'      => count(SeoData::allCompareCityExpandedUrls()),
            'pseo-month-year-city'       => count(SeoData::allMonthYearCityUrls()),
            'pseo-district-city'         => $districtCount,
            'pseo-amenity-city-price'    => count(SeoData::allAmenityCityPriceUrls()),
            'pseo-star-price-city'       => count(SeoData::allStarPriceCityUrls()),
            'pseo-guest-feature-city'    => count(SeoData::allGuestFeatureCityUrls()),
            'pseo-room-type-feature-city'=> count(SeoData::allRoomTypeFeatureCityUrls()),
            'pseo-price-city-expanded'   => count(SeoData::allPriceCityExpandedUrls()),
            'pseo-content-topic-city'    => count(SeoData::allContentTopicCityUrls()),
            'pseo-mega-filler'           => count(SeoData::allMegaFillerUrls()),
            'pseo-second-tier-filler'    => count(SeoData::allSecondTierFillerUrls()),
            'pseo-massive-volume'        => count(SeoData::allMassiveVolumeUrls()),
            'pseo-super-mega-1'          => count(SeoData::allSuperMegaUrls()),
            'pseo-super-mega-2'          => count(SeoData::allSuperMegaUrls2()),
            'pseo-price-combo'           => count(SeoData::allPriceComboUrls()),
            'pseo-star-cross'            => count(SeoData::allStarCrossUrls()),
            'pseo-status-label'          => count(SeoData::allStatusLabelUrls()),
            'pseo-transport-routes'      => count(SeoData::allTransportRouteUrls()),
            'pseo-landmark-star-city'    => count(SeoData::allLandmarkStarCityUrls()),
            'pseo-occasion-landmark-city'=> count(SeoData::allOccasionLandmarkCityUrls()),
            'pseo-sc-feature'            => count(SeoData::allSourceCodeFeatureUrls()),
            'pseo-district-star'         => count(SeoData::allDistrictStarUrls()),
            'pseo-city-pairs-massive'    => count(SeoData::allCityPairsMassiveUrls()),
            'pseo-distance-radius'       => count(SeoData::allDistanceRadiusUrls()),
            'pseo-occasion-all-city'     => count(SeoData::allOccasionAllCityUrls()),
            'pseo-year-extension'        => count(SeoData::allYearExtensionUrls()),
            'pseo-tag-expansion'         => count(SeoData::allTagExpansionUrls()),
            'pseo-month-variation'       => count(SeoData::allMonthVariationUrls()),
            'pseo-sc-full-cross'         => count(SeoData::allSourceCodeFullCrossUrls()),
            'pseo-hotel-type-district'   => count(SeoData::allHotelTypeDistrictUrls()),
            'pseo-occasion-ht-city'      => count(SeoData::allOccasionHotelTypeCityUrls()),
            'pseo-full-kw-city'          => count(SeoData::allFullKeywordCityUrls()),
            'pseo-room-type-city-price'  => count(SeoData::allRoomTypeCityPriceUrls()),
            'pseo-bulk-compare'          => count(SeoData::allBulkCompareUrls()),
            'pseo-quality-feature'       => count(SeoData::allQualityFeatureUrls()),
            'pseo-triple-year'           => count(SeoData::allTripleYearUrls()),
            'pseo-feature-massive'       => count(SeoData::allFeatureMassiveUrls()),
            'pseo-sc-kw-city-price-all'  => count(SeoData::allScKwCityPriceAll()),
            'pseo-rt-feature-all-city'   => count(SeoData::allRtFeatureAllCity()),
            'pseo-occasion-city-price'   => count(SeoData::allOccasionCityPriceAll()),
            'pseo-guest-feature-all'     => count(SeoData::allGuestFeatureAllCity()),
            'pseo-star-price-all-city'   => count(SeoData::allStarPriceAllCity()),
            'pseo-year-full-cross'       => count(SeoData::allYearFullCross()),
            'pseo-sc-kw-rt-city'         => count(SeoData::allScKwRoomTypeCity()),
            'pseo-ht-occ-city-all'       => count(SeoData::allHtOccCityAll()),
            'pseo-rt-occ-city-all'       => count(SeoData::allRtOccCityAll()),
        ];
    }

    /**
     * Return URLs for a specific group chunk.
     * Group names with "-N" suffix are chunked: "pseo-sc-city-price" vs "pseo-sc-city-price-3".
     */
    public function urlsForGroup(string $group): array
    {
        // Parse chunk number
        $chunk = 1;
        if (preg_match('/^(.+)-(\d+)$/', $group, $m)) {
            $group = $m[1];
            $chunk = (int) $m[2];
        }

        $allUrls = $this->rawUrlsForGroup($group);

        // If chunked, slice
        if ($chunk > 1) {
            $offset = ($chunk - 1) * self::MAX_URLS_PER_SITEMAP;
            $allUrls = array_slice($allUrls, $offset, self::MAX_URLS_PER_SITEMAP);
        }

        return $allUrls;
    }

    /**
     * Generate raw URLs for a group (without chunking).
     */
    protected function rawUrlsForGroup(string $group): array
    {
        $base = rtrim(config('app.url'), '/');
        $property = Property::first();
        $year = (int) date('Y');

        $urls = match ($group) {
            'pages' => [
                ['loc' => "$base/",                'priority' => 1.0],
                ['loc' => "$base/about",           'priority' => 0.8],
                ['loc' => "$base/contact",         'priority' => 0.5],
                ['loc' => "$base/rooms",           'priority' => 0.9],
                ['loc' => "$base/booking",         'priority' => 0.9],
                ['loc' => "$base/docs",            'priority' => 0.8],
                ['loc' => "$base/blog",            'priority' => 0.8],
            ],

            'rooms' => $property
                ? RoomType::where('property_id', $property->id)
                    ->where('is_active', true)
                    ->get()
                    ->map(fn ($r) => ['loc' => "$base/rooms/{$r->slug}", 'priority' => 0.8])
                    ->toArray()
                : [],

            'blogs' => $this->blogUrls($base),

            'pseo-best' => $this->mapUrls($base, SeoData::allBestCategoryUrls(), 0.7),

            'pseo-compare' => $this->compareUrls($base, $property?->id),

            'pseo-cities' => $this->cityUrls($base, [
                fn ($c) => "/hotels-in-{$c}",
                fn ($c) => "/best-time-to-visit-{$c}",
                fn ($c) => "/pet-friendly-hotels-{$c}",
                fn ($c) => "/best-hotels-{$c}-{$year}",
                fn ($c) => "/best-hotels-{$c}-".($year + 1),
            ]),

            'pseo-cities-occasion' => $this->cityCrossUrls($base, '/{occ}-stay-{c}', SeoData::OCCASIONS, 'occ'),

            'pseo-cities-budget' => $this->cityCrossUrls($base, '/hotels-under-{p}-{c}', SeoData::PRICE_TIERS, 'p'),

            'pseo-cities-neighborhood' => $this->neighborhoodUrls($base),

            'pseo-landmark' => $this->mapUrls($base, SeoData::allLandmarkUrls(), 0.7),

            'pseo-villa-feature' => $this->mapUrls($base, SeoData::allVillaFeatureUrls(), 0.6),

            'pseo-star-city' => $this->mapUrls($base, SeoData::allStarCityUrls(), 0.7),

            'pseo-price-city' => $this->mapUrls($base, SeoData::allPriceCityUrls(), 0.7),

            'pseo-nearby' => $this->mapUrls($base, SeoData::allNearbyUrls(), 0.6),

            'pseo-amenity' => $this->mapUrls($base, SeoData::allAmenityUrls(), 0.6),

            'pseo-short-landmark' => $this->mapUrls($base, SeoData::allShortLandmarkUrls(), 0.6),

            'pseo-alt-accommodation' => $this->mapUrls($base, SeoData::allAltAccommodationUrls(), 0.6),

            'pseo-content' => $this->mapUrls($base, SeoData::allContentUrls(), 0.7),

            'pseo-weather' => $this->mapUrls($base, SeoData::allWeatherUrls(), 0.5),

            'pseo-events' => $this->mapUrls($base, SeoData::allEventUrls(), 0.5),

            'pseo-recommendation' => $this->mapUrls($base, SeoData::allRecommendationUrls(), 0.6),

            'pseo-area' => $this->mapUrls($base, SeoData::allAreaUrls(), 0.6),

            'pseo-popular-new' => $this->mapUrls($base, SeoData::allPopularNewUrls(), 0.7),

            'pseo-granular-price' => $this->mapUrls($base, SeoData::allGranularPriceUrls(), 0.6),

            'pseo-price-range' => $this->mapUrls($base, SeoData::allPriceRangeUrls(), 0.5),

            'pseo-room-type-city' => $this->mapUrls($base, SeoData::allRoomTypeCityUrls(), 0.6),

            'pseo-guest-type-city' => $this->mapUrls($base, SeoData::allGuestTypeCityUrls(), 0.6),

            'pseo-season-city' => $this->mapUrls($base, SeoData::allSeasonCityUrls(), 0.5),

            'pseo-holiday-city' => $this->mapUrls($base, SeoData::allHolidayCityUrls(), 0.5),

            'pseo-distance-city' => $this->mapUrls($base, SeoData::allDistanceCityUrls(), 0.5),

            'pseo-distance-landmark' => $this->mapUrls($base, SeoData::allDistanceLandmarkUrls(), 0.5),

            'pseo-question' => $this->mapUrls($base, SeoData::allQuestionUrls(), 0.5),

            'pseo-compare-cities' => $this->mapUrls($base, SeoData::allCompareCityUrls(), 0.4),

            'pseo-compare-neighborhoods' => $this->mapUrls($base, SeoData::allCompareNeighborhoodUrls(), 0.4),

            // ═══ NEW MASSIVE GROUPS ═══
            'pseo-sc-base' => $this->mapUrls($base, array_merge(
                SeoData::allSourceCodeUrls(),
                SeoData::allSourceCodeDownloadUrls(),
                SeoData::allSourceCodeBestUrls()
            ), 0.6),

            'pseo-sc-city' => $this->mapUrls($base, SeoData::allSourceCodeCityUrls(), 0.5),

            'pseo-sc-price' => $this->mapUrls($base, SeoData::allSourceCodePriceUrls(), 0.5),

            'pseo-sc-city-price' => $this->mapUrls($base, SeoData::allSourceCodeCityPriceUrls(), 0.4),

            'pseo-sc-city-murah' => $this->mapUrls($base, SeoData::allSourceCodeCityMurahUrls(), 0.5),

            'pseo-sc-jasa' => $this->mapUrls($base, SeoData::allSourceCodeJasaUrls(), 0.4),

            'pseo-sc-paket' => $this->mapUrls($base, SeoData::allSourceCodePaketUrls(), 0.4),

            'pseo-sc-vs' => $this->mapUrls($base, SeoData::allSourceCodeVsUrls(), 0.3),

            'pseo-sc-district' => $this->mapUrls($base, SeoData::allSourceCodeDistrictUrls(), 0.3),

            'pseo-sc-massive' => $this->mapUrls($base, SeoData::allSourceCodeMassiveUrls(), 0.3),

            'pseo-feature-city' => $this->mapUrls($base, SeoData::allFeatureCityUrls(), 0.4),

            'pseo-double-feature-city' => $this->mapUrls($base, SeoData::allDoubleFeatureCityUrls(), 0.4),

            'pseo-occasion-feature-city' => $this->mapUrls($base, SeoData::allOccasionFeatureCityUrls(), 0.3),

            'pseo-double-city' => $this->mapUrls($base, SeoData::allDoubleCityUrls(), 0.3),

            'pseo-compare-expanded' => $this->mapUrls($base, SeoData::allCompareCityExpandedUrls(), 0.3),

            'pseo-month-year-city' => $this->mapUrls($base, SeoData::allMonthYearCityUrls(), 0.3),

            'pseo-district-city' => $this->mapUrls($base, SeoData::allDistrictCityUrls(), 0.4),

            'pseo-amenity-city-price' => $this->mapUrls($base, SeoData::allAmenityCityPriceUrls(), 0.3),

            'pseo-star-price-city' => $this->mapUrls($base, SeoData::allStarPriceCityUrls(), 0.3),

            'pseo-guest-feature-city' => $this->mapUrls($base, SeoData::allGuestFeatureCityUrls(), 0.3),

            'pseo-room-type-feature-city' => $this->mapUrls($base, SeoData::allRoomTypeFeatureCityUrls(), 0.3),

            'pseo-price-city-expanded' => $this->mapUrls($base, SeoData::allPriceCityExpandedUrls(), 0.4),

            'pseo-content-topic-city' => $this->mapUrls($base, SeoData::allContentTopicCityUrls(), 0.4),

            'pseo-mega-filler' => $this->mapUrls($base, SeoData::allMegaFillerUrls(), 0.2),

            'pseo-second-tier-filler' => $this->mapUrls($base, SeoData::allSecondTierFillerUrls(), 0.2),

            'pseo-massive-volume' => $this->mapUrls($base, SeoData::allMassiveVolumeUrls(), 0.2),

            'pseo-super-mega-1' => $this->mapUrls($base, SeoData::allSuperMegaUrls(), 0.2),

            'pseo-super-mega-2' => $this->mapUrls($base, SeoData::allSuperMegaUrls2(), 0.2),

            'pseo-price-combo' => $this->mapUrls($base, SeoData::allPriceComboUrls(), 0.2),

            'pseo-star-cross' => $this->mapUrls($base, SeoData::allStarCrossUrls(), 0.2),

            'pseo-status-label' => $this->mapUrls($base, SeoData::allStatusLabelUrls(), 0.2),

            'pseo-transport-routes' => $this->mapUrls($base, SeoData::allTransportRouteUrls(), 0.2),

            'pseo-landmark-star-city' => $this->mapUrls($base, SeoData::allLandmarkStarCityUrls(), 0.2),

            'pseo-occasion-landmark-city' => $this->mapUrls($base, SeoData::allOccasionLandmarkCityUrls(), 0.2),

            'pseo-sc-feature' => $this->mapUrls($base, SeoData::allSourceCodeFeatureUrls(), 0.2),

            'pseo-district-star' => $this->mapUrls($base, SeoData::allDistrictStarUrls(), 0.2),

            'pseo-city-pairs-massive' => $this->mapUrls($base, SeoData::allCityPairsMassiveUrls(), 0.2),

            'pseo-distance-radius' => $this->mapUrls($base, SeoData::allDistanceRadiusUrls(), 0.2),

            'pseo-occasion-all-city' => $this->mapUrls($base, SeoData::allOccasionAllCityUrls(), 0.2),

            'pseo-year-extension' => $this->mapUrls($base, SeoData::allYearExtensionUrls(), 0.2),

            'pseo-tag-expansion' => $this->mapUrls($base, SeoData::allTagExpansionUrls(), 0.2),

            'pseo-month-variation' => $this->mapUrls($base, SeoData::allMonthVariationUrls(), 0.2),

            'pseo-sc-full-cross' => $this->mapUrls($base, SeoData::allSourceCodeFullCrossUrls(), 0.3),

            'pseo-hotel-type-district' => $this->mapUrls($base, SeoData::allHotelTypeDistrictUrls(), 0.3),

            'pseo-occasion-ht-city' => $this->mapUrls($base, SeoData::allOccasionHotelTypeCityUrls(), 0.3),

            'pseo-full-kw-city' => $this->mapUrls($base, SeoData::allFullKeywordCityUrls(), 0.3),

            'pseo-room-type-city-price' => $this->mapUrls($base, SeoData::allRoomTypeCityPriceUrls(), 0.3),

            'pseo-bulk-compare' => $this->mapUrls($base, SeoData::allBulkCompareUrls(), 0.3),

            'pseo-quality-feature' => $this->mapUrls($base, SeoData::allQualityFeatureUrls(), 0.3),

            'pseo-triple-year' => $this->mapUrls($base, SeoData::allTripleYearUrls(), 0.2),

            'pseo-feature-massive' => $this->mapUrls($base, SeoData::allFeatureMassiveUrls(), 0.2),

            'pseo-sc-kw-city-price-all' => $this->mapUrls($base, SeoData::allScKwCityPriceAll(), 0.3),

            'pseo-rt-feature-all-city' => $this->mapUrls($base, SeoData::allRtFeatureAllCity(), 0.3),

            'pseo-occasion-city-price' => $this->mapUrls($base, SeoData::allOccasionCityPriceAll(), 0.3),

            'pseo-guest-feature-all' => $this->mapUrls($base, SeoData::allGuestFeatureAllCity(), 0.3),

            'pseo-star-price-all-city' => $this->mapUrls($base, SeoData::allStarPriceAllCity(), 0.3),

            'pseo-year-full-cross' => $this->mapUrls($base, SeoData::allYearFullCross(), 0.2),

            'pseo-sc-kw-rt-city' => $this->mapUrls($base, SeoData::allScKwRoomTypeCity(), 0.3),

            'pseo-ht-occ-city-all' => $this->mapUrls($base, SeoData::allHtOccCityAll(), 0.2),

            'pseo-rt-occ-city-all' => $this->mapUrls($base, SeoData::allRtOccCityAll(), 0.3),

            default => [],
        };

        return $urls;
    }

    /** Total URL count across all groups. */
    public function totalUrlCount(): int
    {
        $sum = 0;
        foreach ($this->patternGroups() as $group => $estimate) {
            $sum += $estimate;
        }
        return $sum;
    }

    // ═══ Helpers ═══

    private function mapUrls(string $base, array $paths, float $priority = 0.6): array
    {
        return array_map(fn ($p) => [
            'loc' => $base . $p,
            'priority' => $priority,
        ], $paths);
    }

    private function cityUrls(string $base, array $patternFns): array
    {
        $urls = [];
        foreach (array_keys(SeoData::CITIES) as $c) {
            foreach ($patternFns as $fn) {
                $urls[] = ['loc' => $base . $fn($c), 'priority' => 0.7];
            }
        }
        return $urls;
    }

    private function cityCrossUrls(string $base, string $pattern, array $values, string $valKey): array
    {
        $urls = [];
        foreach (array_keys(SeoData::CITIES) as $c) {
            foreach ($values as $v) {
                $url = strtr($pattern, ['{c}' => $c, '{'.$valKey.'}' => $v]);
                $urls[] = ['loc' => $base . $url, 'priority' => 0.6];
            }
        }
        return $urls;
    }

    private function neighborhoodUrls(string $base): array
    {
        $urls = [];
        foreach (SeoData::NEIGHBORHOODS as $city => $neighborhoods) {
            foreach ($neighborhoods as $n) {
                $urls[] = ['loc' => "$base/hotels-in-{$city}-{$n}", 'priority' => 0.6];
            }
        }
        return $urls;
    }

    private function compareUrls(string $base, ?int $propertyId): array
    {
        if (! $propertyId) return [];
        $rts = RoomType::where('property_id', $propertyId)
            ->where('is_active', true)
            ->get();
        $urls = [];
        foreach ($rts as $a) {
            foreach ($rts as $b) {
                if ($a->id < $b->id && $a->slug && $b->slug) {
                    $urls[] = ['loc' => "$base/compare/{$a->slug}-vs-{$b->slug}", 'priority' => 0.5];
                }
            }
        }
        return $urls;
    }

    private function blogUrls(string $base): array
    {
        $urls = [];
        if (class_exists(\App\Models\BlogPost::class)) {
            $posts = \App\Models\BlogPost::published()->select('slug', 'updated_at')->get();
            foreach ($posts as $post) {
                $urls[] = ['loc' => $base . '/blog/' . $post->slug, 'priority' => '0.7'];
            }
        }
        $urls[] = ['loc' => $base . '/blog', 'priority' => '0.8'];
        $urls[] = ['loc' => $base . '/blog/feed.xml', 'priority' => '0.5'];
        return $urls;
    }
}
