<?php

declare(strict_types=1);

namespace App\Adapters\Channel;

use App\Exceptions\ChannelSyncException;
use App\Models\AriSyncLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ExpediaAdapter extends BaseChannelAdapter
{
    private string $tokenCacheKey;

    public function __construct(\App\Models\Channel $channel)
    {
        parent::__construct($channel);
        $this->tokenCacheKey = 'expedia_oauth_token:' . $channel->id;
    }

    protected function defaultBaseUrl(): string
    {
        return 'https://services.expediapartnercentral.com/products/v1/';
    }

    protected function oauthBaseUrl(): string
    {
        return $this->channel->config['oauth_base_url']
            ?? $this->channel->provider?->extra_config['oauth_base_url']
            ?? 'https://services.expediapartnercentral.com/';
    }

    protected function http(): Client
    {
        return new Client([
            'base_uri' => $this->getBaseUrl(),
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getOAuthToken(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    private function getOAuthToken(): string
    {
        $cached = Cache::get($this->tokenCacheKey);
        if ($cached && is_string($cached)) {
            return $cached;
        }

        $cred = $this->channel->getCredentials();
        $clientId = $cred['client_id'] ?? '';
        $clientSecret = $cred['client_secret'] ?? '';
        $refreshToken = $cred['refresh_token'] ?? '';

        if (empty($clientId) || empty($clientSecret)) {
            Log::warning('Expedia OAuth credentials not configured.', [
                'channel_id' => $this->channel->id,
            ]);
            return '';
        }

        try {
            $oauthClient = new Client([
                'base_uri' => rtrim($this->oauthBaseUrl(), '/') . '/',
                'timeout' => 30,
                'http_errors' => false,
            ]);

            $payload = [
                'grant_type' => 'refresh_token',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
            ];

            $response = $oauthClient->post('identity/v2/oauth2/token', [
                'form_params' => $payload,
            ]);

            $data = json_decode((string) $response->getBody(), true);

            if (! empty($data['access_token'])) {
                $token = $data['access_token'];
                $expiresIn = (int) ($data['expires_in'] ?? 3600);
                Cache::put($this->tokenCacheKey, $token, max($expiresIn - 60, 60));
                return $token;
            }

            Log::error('Expedia OAuth token refresh failed.', [
                'channel_id' => $this->channel->id,
                'response' => $data,
            ]);
            return '';

        } catch (\Throwable $e) {
            Log::error('Expedia OAuth token request error.', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
            ]);
            return '';
        }
    }

    // ─── Public API ───────────────────────────────────────────────

    public function syncAvailability(array $rooms): array
    {
        return $this->executeSync('push_availability', function () use ($rooms) {
            $propertyId = $this->channel->hotel_id_at_channel;
            $payload = [
                'property_id' => $propertyId,
                'rooms' => array_map(function ($room) {
                    return [
                        'room_id' => $room['channel_room_id'] ?? $room['room_type_id'] ?? '',
                        'date' => $room['date'] ?? $room['start_date'] ?? '',
                        'availability' => $room['count'] ?? $room['available'] ?? 0,
                        'restrictions' => [
                            'closed' => ($room['count'] ?? $room['available'] ?? 0) <= 0,
                        ],
                    ];
                }, $rooms),
            ];

            $response = $this->http()->put(
                'properties/' . urlencode((string) $propertyId) . '/rooms/availability',
                ['json' => $payload]
            );

            return $this->parseJsonResponse($response, 'availability');
        });
    }

    public function syncRates(array $rates): array
    {
        return $this->executeSync('push_rates', function () use ($rates) {
            $propertyId = $this->channel->hotel_id_at_channel;
            $payload = [
                'property_id' => $propertyId,
                'rates' => array_map(function ($rate) {
                    return [
                        'room_id' => $rate['channel_room_id'] ?? $rate['room_type_id'] ?? '',
                        'rate_plan_id' => $rate['channel_rate_id'] ?? $rate['rate_plan_id'] ?? '',
                        'date' => $rate['date'] ?? $rate['start_date'] ?? '',
                        'rate' => [
                            'amount' => (float) ($rate['amount'] ?? 0),
                            'currency' => $rate['currency'] ?? 'IDR',
                        ],
                    ];
                }, $rates),
            ];

            $response = $this->http()->put(
                'properties/' . urlencode((string) $propertyId) . '/rooms/rates',
                ['json' => $payload]
            );

            return $this->parseJsonResponse($response, 'rates');
        });
    }

    public function syncRestrictions(array $restrictions): array
    {
        return $this->executeSync('push_restrictions', function () use ($restrictions) {
            $propertyId = $this->channel->hotel_id_at_channel;
            $payload = [
                'property_id' => $propertyId,
                'restrictions' => array_map(function ($r) {
                    return array_filter([
                        'room_id' => $r['channel_room_id'] ?? $r['room_type_id'] ?? '',
                        'rate_plan_id' => $r['channel_rate_id'] ?? $r['rate_plan_id'] ?? null,
                        'date' => $r['date'] ?? $r['start_date'] ?? '',
                        'min_los' => $r['min_stay'] ?? $r['min_los'] ?? null,
                        'max_los' => $r['max_stay'] ?? $r['max_los'] ?? null,
                        'min_advance_booking' => $r['ctd'] ?? null,
                        'max_advance_booking' => $r['cta'] ?? null,
                    ], fn ($v) => $v !== null);
                }, $restrictions),
            ];

            $response = $this->http()->put(
                'properties/' . urlencode((string) $propertyId) . '/rooms/restrictions',
                ['json' => $payload]
            );

            return $this->parseJsonResponse($response, 'restrictions');
        });
    }

    public function fetchBookings(?\DateTimeInterface $since = null): array
    {
        return $this->executeSync('fetch_bookings', function () use ($since) {
            $propertyId = $this->channel->hotel_id_at_channel;
            if (empty($propertyId)) {
                throw ChannelSyncException::forChannel(
                    $this->channel->id,
                    $this->channel->name,
                    'Property ID at channel not configured.',
                );
            }

            $params = [
                'property_id' => $propertyId,
                'startDate' => $since ? $since->format('Y-m-d') : now()->subDays(30)->format('Y-m-d'),
                'endDate' => now()->addDays(180)->format('Y-m-d'),
                'limit' => 100,
            ];

            $allBookings = [];
            $offset = 0;

            do {
                $params['offset'] = $offset;
                $response = $this->http()->get('bookings?' . http_build_query($params));
                $data = $this->parseJsonResponse($response, 'bookings');

                $bookings = $data['data']['bookings'] ?? $data['data']['items'] ?? [];
                $allBookings = array_merge($allBookings, $bookings);

                $total = (int) ($data['data']['total'] ?? $data['data']['totalResults'] ?? 0);
                $offset += count($bookings);
            } while ($offset < $total && count($bookings) >= ($params['limit'] ?? 100));

            return [
                'success' => true,
                'data' => ['bookings' => $allBookings],
                'error' => null,
            ];
        });
    }

    public function fetchBooking(string $bookingId): array
    {
        return $this->executeSync('fetch_booking', function () use ($bookingId) {
            $response = $this->http()->get('bookings/' . urlencode($bookingId));
            return $this->parseJsonResponse($response, 'booking');
        });
    }

    // ─── BaseChannelAdapter required methods ──────────────────────

    public function pushAvailability(array $updates): array
    {
        return $this->syncAvailability($updates);
    }

    public function pushRates(array $updates): array
    {
        return $this->syncRates($updates);
    }

    public function pushRestrictions(array $updates): array
    {
        return $this->syncRestrictions($updates);
    }

    public function test(): array
    {
        $cred = $this->channel->getCredentials() ?? [];
        if (empty($cred['client_id']) || empty($cred['client_secret'])) {
            return ['ok' => false, 'message' => 'OAuth credentials not configured'];
        }
        try {
            $token = $this->getOAuthToken();
            if (empty($token)) {
                return ['ok' => false, 'message' => 'Failed to obtain OAuth token'];
            }
            return ['ok' => true, 'message' => 'OAuth token obtained successfully'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    // ─── Private Helpers ──────────────────────────────────────────

    protected function parseJsonResponse($response, string $operation): array
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (! is_array($data)) {
            return [
                'success' => $statusCode < 400,
                'data' => ['raw' => $body],
                'error' => $statusCode >= 400 ? 'Invalid JSON response' : null,
            ];
        }

        if ($statusCode === 429) {
            $retryAfter = $response->getHeader('Retry-After')[0] ?? 'unknown';
            return [
                'success' => false,
                'data' => [],
                'error' => 'Rate limited. Retry after ' . $retryAfter,
                'retry_after' => $retryAfter,
            ];
        }

        if ($statusCode >= 400) {
            $errorMsg = $data['message'] ?? $data['error'] ?? $data['status'] ?? 'Unknown error';
            return [
                'success' => false,
                'data' => $data,
                'error' => $errorMsg,
            ];
        }

        return [
            'success' => true,
            'data' => $data,
            'error' => null,
        ];
    }

    protected function executeSync(string $operation, callable $fn): array
    {
        $log = AriSyncLog::create([
            'channel_id' => $this->channel->id,
            'operation' => $operation,
            'status' => 'running',
            'started_at' => now(),
            'attempt' => 1,
        ]);

        try {
            $result = $fn();

            $log->update([
                'status' => $result['success'] ? 'success' : 'failed',
                'finished_at' => now(),
                'response_summary' => $result,
                'error' => $result['error'] ?? null,
            ]);

            return [
                'success' => $result['success'] ?? false,
                'data' => $result['data'] ?? $result,
                'error' => $result['error'] ?? null,
            ];

        } catch (ChannelSyncException $e) {
            $log->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error' => $e->getMessage(),
            ]);
            Log::channel('channel-manager')->error('Expedia sync failed', [
                'channel_id' => $this->channel->id,
                'operation' => $operation,
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
            ]);
            throw $e;

        } catch (ConnectException $e) {
            $log->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error' => $e->getMessage(),
            ]);
            Log::channel('channel-manager')->error('Expedia connection error', [
                'channel_id' => $this->channel->id,
                'operation' => $operation,
                'error' => $e->getMessage(),
            ]);
            throw ChannelSyncException::networkError($this->channel->id, $operation, $e->getMessage());

        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error' => $e->getMessage(),
            ]);
            Log::channel('channel-manager')->error('Expedia sync unexpected error', [
                'channel_id' => $this->channel->id,
                'operation' => $operation,
                'error' => $e->getMessage(),
            ]);
            throw ChannelSyncException::networkError($this->channel->id, $operation, $e->getMessage());
        }
    }
}
