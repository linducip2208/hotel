<?php

namespace App\Services\Marketing;

use App\Models\Guest;
use App\Models\GuestProfile;
use App\Models\Property;
use App\Models\Provider;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppBlastService
{
    protected Client $http;
    protected ?Provider $provider;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30]);
    }

    public function resolveProvider(Property $property): ?Provider
    {
        return Provider::where('property_id', $property->id)
            ->where('integration_type', 'whatsapp')
            ->where('api_format', 'chatgo')
            ->where('is_active', true)
            ->first();
    }

    public function send(string $phone, string $message, Property $property, ?string $accountPhone = null): array
    {
        $provider = $this->resolveProvider($property);
        if (! $provider) {
            return ['status' => 'error', 'message' => 'WhatsApp provider not configured'];
        }

        $baseUrl = $provider->base_url ?? 'https://chatgo.whitelabel.co.id';
        $apiKey = $provider->getApiKey();
        if (! $apiKey) {
            return ['status' => 'error', 'message' => 'API key not configured'];
        }

        try {
            $payload = ['phone' => $this->normalizePhone($phone), 'message' => $message];
            if ($accountPhone) {
                $payload['account_phone'] = $accountPhone;
            }

            $resp = $this->http->post("{$baseUrl}/api/send", [
                'headers' => ['X-API-Key' => $apiKey, 'Content-Type' => 'application/json'],
                'json' => $payload,
            ]);

            $body = json_decode((string) $resp->getBody(), true);
            Log::channel('whatsapp')->info('WhatsApp blast sent', ['phone' => $phone, 'response' => $body]);
            return $body;
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('WhatsApp blast failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function sendBlast(array $guestIds, string $message, Property $property, int $delaySeconds = 5): array
    {
        $guests = Guest::whereIn('id', $guestIds)
            ->where('property_id', $property->id)
            ->whereNotNull('phone')
            ->get();

        $sent = $failed = 0;
        foreach ($guests as $guest) {
            $personalized = $this->personalize($message, $guest);
            $result = $this->send($guest->phone, $personalized, $property);
            $result['status'] === 'ok' ? $sent++ : $failed++;
            if ($delaySeconds > 0) {
                sleep($delaySeconds);
            }
        }

        return ['sent' => $sent, 'failed' => $failed, 'total' => $guests->count()];
    }

    public function getTargetedGuests(Property $property, array $filters = []): array
    {
        $query = Guest::where('property_id', $property->id)->whereNotNull('phone');

        if (! empty($filters['segment'])) {
            $query->whereHas('profile', function ($q) use ($filters) {
                return match ($filters['segment']) {
                    'vip' => $q->where('upsell_score', '>=', 70),
                    'at_risk' => $q->where('churn_risk_score', '>=', 60),
                    'loyal' => $q->where('loyalty_score', '>=', 70),
                    'inactive' => $q->where('total_stays', '>', 0)->where('last_visit', '<', now()->subMonths(6)),
                    default => $q,
                };
            });
        }

        if (! empty($filters['min_stays'])) {
            $query->whereHas('profile', fn ($q) => $q->where('total_stays', '>=', $filters['min_stays']));
        }
        if (! empty($filters['min_ltv'])) {
            $query->whereHas('profile', fn ($q) => $q->where('total_lifetime_value', '>=', $filters['min_ltv']));
        }

        return $query->pluck('id', 'name')->toArray();
    }

    protected function personalize(string $message, Guest $guest): string
    {
        $profile = $guest->profile;
        $replace = [
            '{name}' => $guest->name ?? 'Pelanggan Setia',
            '{phone}' => $guest->phone ?? '',
            '{email}' => $guest->email ?? '',
            '{ltv}' => $profile ? number_format((float) $profile->total_lifetime_value, 0, ',', '.') : '0',
            '{stays}' => $profile?->total_stays ?? '0',
        ];
        return str_replace(array_keys($replace), array_values($replace), $message);
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }
        if (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }
        return $phone;
    }
}
