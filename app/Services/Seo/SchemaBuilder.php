<?php

namespace App\Services\Seo;

use App\Models\Property;

class SchemaBuilder
{
    public function hotel(Property $p): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Hotel',
            'name' => $p->name,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $p->address_line1,
                'addressLocality' => $p->city,
                'addressRegion' => $p->province,
                'postalCode' => $p->postal_code,
                'addressCountry' => $p->country,
            ],
            'geo' => $p->lat && $p->lng ? [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $p->lat,
                'longitude' => (float) $p->lng,
            ] : null,
            'starRating' => $p->star_rating ? ['@type' => 'Rating', 'ratingValue' => (string) $p->star_rating] : null,
            'checkinTime' => $p->check_in_time?->format('H:i') ?? '14:00',
            'checkoutTime' => $p->check_out_time?->format('H:i') ?? '12:00',
        ];
    }

    public function itemList(string $name, array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'itemListElement' => array_map(fn ($i, $n) => [
                '@type' => 'ListItem',
                'position' => $n + 1,
                'item' => $i,
            ], $items, array_keys($items)),
        ];
    }

    public function faqPage(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type' => 'Question',
                'name' => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $faqs),
        ];
    }

    public function breadcrumb(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn ($i, $n) => [
                '@type' => 'ListItem',
                'position' => $n + 1,
                'name' => $i['name'],
                'item' => $i['url'],
            ], $items, array_keys($items)),
        ];
    }
}
