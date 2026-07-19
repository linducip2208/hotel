<?php

namespace App\Services\Webhooks;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class WebhookDispatcher
{
    public function dispatch(int $propertyId, string $event, array $data): void
    {
        $webhooks = Webhook::where('property_id', $propertyId)
            ->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        $client = new Client(['timeout' => 10, 'http_errors' => false]);
        $eventId = 'evt_'.Str::ulid();

        foreach ($webhooks as $w) {
            $payload = [
                'id' => $eventId,
                'type' => $event,
                'created_at' => now()->toIso8601String(),
                'property_id' => $propertyId,
                'data' => $data,
            ];
            $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
            $signature = hash_hmac('sha256', $body, $w->secret_encrypted);

            $delivery = WebhookDelivery::create([
                'webhook_id' => $w->id,
                'event' => $event,
                'event_id' => $eventId,
                'payload' => $payload,
                'attempt' => 1,
                'status' => 'pending',
            ]);

            try {
                $r = $client->post($w->url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'X-HotelHub-Signature' => "t=".time().",v1=".$signature,
                        'X-HotelHub-Event' => $event,
                    ],
                    'body' => $body,
                ]);
                $delivery->update([
                    'status' => $r->getStatusCode() < 400 ? 'success' : 'failed',
                    'http_status' => $r->getStatusCode(),
                    'response_body' => substr((string) $r->getBody(), 0, 1000),
                    'delivered_at' => now(),
                ]);
                if ($r->getStatusCode() < 400) {
                    $w->update(['failed_consecutive' => 0, 'last_delivered_at' => now()]);
                } else {
                    $w->increment('failed_consecutive');
                }
            } catch (\Throwable $e) {
                $delivery->update(['status' => 'failed', 'response_body' => $e->getMessage()]);
                $w->increment('failed_consecutive');
            }
        }
    }
}
