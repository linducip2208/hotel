<?php

namespace App\Services\Marketing;

use App\Models\Review;
use App\Models\Property;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ReviewAggregatorService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30]);
    }

    public function pullGoogleReviews(Property $property): array
    {
        $provider = \App\Models\Provider::where('property_id', $property->id)
            ->where('integration_type', 'review')
            ->where('api_format', 'google_places')
            ->where('is_active', true)->first();

        if (! $provider || ! $property->google_place_id) {
            return [];
        }

        try {
            $apiKey = $provider->getApiKey();
            $resp = $this->http->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'query' => [
                    'place_id' => $property->google_place_id,
                    'fields' => 'reviews',
                    'key' => $apiKey,
                    'language' => 'id',
                ],
            ]);
            $data = json_decode((string) $resp->getBody(), true);
            $reviews = $data['result']['reviews'] ?? [];

            $imported = 0;
            foreach ($reviews as $r) {
                $extId = $r['author_name'].'_'.($r['time'] ?? '');
                $exists = Review::where('property_id', $property->id)
                    ->where('source', 'google')
                    ->where('external_id', $extId)
                    ->exists();
                if (! $exists) {
                    Review::create([
                        'property_id' => $property->id,
                        'source' => 'google',
                        'external_id' => $extId,
                        'rating' => $r['rating'] ?? 5,
                        'comment' => $r['text'] ?? '',
                        'author_name' => $r['author_name'] ?? 'Anonymous',
                        'reviewed_at' => isset($r['time']) ? date('Y-m-d H:i:s', $r['time']) : now(),
                        'is_public' => true,
                        'is_published' => true,
                    ]);
                    $imported++;
                }
            }

            Log::info("Imported {$imported} Google reviews for property {$property->name}");

            return ['imported' => $imported, 'total' => count($reviews)];
        } catch (\Exception $e) {
            Log::error("Google review import failed: {$e->getMessage()}");

            return ['error' => $e->getMessage()];
        }
    }

    public function getReviewStats(Property $property): array
    {
        $reviews = Review::where('property_id', $property->id)->where('is_public', true);
        $total = $reviews->count();
        $avgRating = $total > 0 ? round($reviews->avg('rating'), 1) : 0;

        $bySource = Review::where('property_id', $property->id)->where('is_public', true)
            ->selectRaw('source, COUNT(*) as count, AVG(rating) as avg_rating')
            ->groupBy('source')->get()->toArray();

        $byRating = [];
        for ($i = 1; $i <= 5; $i++) {
            $byRating[$i] = Review::where('property_id', $property->id)
                ->where('is_public', true)
                ->where('rating', $i)->count();
        }

        $recent = Review::where('property_id', $property->id)
            ->where('is_public', true)
            ->latest('reviewed_at')
            ->limit(10)->get();

        return compact('total', 'avgRating', 'bySource', 'byRating', 'recent');
    }
}
