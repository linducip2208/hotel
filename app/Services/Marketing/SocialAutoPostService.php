<?php

namespace App\Services\Marketing;

use App\Models\Property;
use App\Models\Provider;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SocialAutoPostService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30]);
    }

    public function postRoomAvailability(Property $property): array
    {
        $rooms = \App\Models\Room::where('property_id', $property->id)
            ->where('fo_status', 'clean')
            ->whereHas('roomType', fn ($q) => $q->where('base_rate', '>', 0))
            ->with('roomType')
            ->limit(3)->get();

        if ($rooms->isEmpty()) {
            return ['posted' => false, 'message' => 'Tidak ada kamar tersedia'];
        }

        $lines = ["Kamar tersedia hari ini di {$property->name}:"];
        foreach ($rooms as $room) {
            $rate = $room->roomType->base_rate ?? 0;
            $lines[] = "• {$room->roomType->name} - Rp " . number_format($rate, 0, ',', '.') . "/malam";
        }
        $lines[] = '';
        $lines[] = "Booking sekarang: " . ($property->booking_url ?? url('/booking'));
        $caption = implode("\n", $lines);

        return $this->postToInstagram($property, $caption, $rooms->first()->roomType->image_url ?? null);
    }

    protected function postToInstagram(Property $property, string $caption, ?string $imageUrl = null): array
    {
        $provider = Provider::where('property_id', $property->id)
            ->where('integration_type', 'social')
            ->where('api_format', 'instagram_graph')
            ->where('is_active', true)->first();

        if (! $provider) {
            return ['posted' => false, 'message' => 'Provider Instagram belum dikonfigurasi'];
        }

        try {
            $apiKey = $provider->getApiKey();
            $pageId = $provider->extra_config['page_id'] ?? null;
            if (! $pageId) {
                return ['posted' => false, 'message' => 'Instagram page ID belum dikonfigurasi'];
            }

            $resp = $this->http->post("https://graph.facebook.com/v20.0/{$pageId}/media", [
                'query' => [
                    'caption'      => $caption,
                    'image_url'    => $imageUrl ?? $property->logo_url,
                    'access_token' => $apiKey,
                ],
            ]);

            $data    = json_decode((string) $resp->getBody(), true);
            $mediaId = $data['id'] ?? null;

            if ($mediaId) {
                $this->http->post("https://graph.facebook.com/v20.0/{$pageId}/media_publish", [
                    'query' => ['creation_id' => $mediaId, 'access_token' => $apiKey],
                ]);
                Log::info("Instagram post published for {$property->name}", ['media_id' => $mediaId]);
                return ['posted' => true, 'media_id' => $mediaId];
            }

            return ['posted' => false, 'message' => 'Gagal membuat media'];
        } catch (\Exception $e) {
            Log::error("Instagram post failed: {$e->getMessage()}");
            return ['posted' => false, 'message' => $e->getMessage()];
        }
    }

    public function generatePromoCaption(Property $property, string $type = 'weekend'): string
    {
        $bookingUrl = $property->booking_url ?? url('/booking');
        $promos = [
            'weekend' => "Weekend getaway? 🏖️\nNikmati akhir pekan di {$property->name}!\n• Kolam renang infinity\n• Spa & wellness\n• Breakfast untuk 2 orang\n\nMulai dari Rp " . number_format(rand(500000, 1500000), 0, ',', '.') . "/malam\n\nBooking: {$bookingUrl}\n\n#Staycation #HotelDiIndonesia #WeekendGetaway",
            'flash_sale' => "⚡ FLASH SALE 24 JAM!\nDiskon hingga 30% di {$property->name}\n\nJangan sampai kehabisan!\nBooking sekarang: {$bookingUrl}\n\n#FlashSale #HotelDeals #PromoHotel",
            'new_year' => "Sambut tahun baru di {$property->name}! ✨\n\nPaket Tahun Baru:\n• 2 malam menginap\n• Gala dinner\n• Live music\n• Fireworks view\n\nInfo & booking: {$bookingUrl}\n\n#NewYear #HotelPackage",
        ];

        return $promos[$type] ?? $promos['weekend'];
    }
}
