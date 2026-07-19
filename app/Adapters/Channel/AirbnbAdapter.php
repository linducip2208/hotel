<?php

declare(strict_types=1);

namespace App\Adapters\Channel;

use App\Exceptions\ChannelSyncException;
use App\Models\AriSyncLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;

class AirbnbAdapter extends BaseChannelAdapter
{
    protected function defaultBaseUrl(): string
    {
        return 'https://api.airbnb.com/v2/';
    }

    protected function http(): Client
    {
        $cred = $this->channel->getCredentials();
        return new Client([
            'base_uri' => $this->getBaseUrl(),
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . ($cred['access_token'] ?? ''),
                'X-Airbnb-API-Key' => $cred['api_key'] ?? '',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    // ─── Public API ───────────────────────────────────────────────

    public function syncAvailability(array $rooms): array
    {
        return $this->executeSync('push_availability', function () use ($rooms) {
            $results = [];
            foreach ($rooms as $room) {
                $listingId = $room['channel_room_id'] ?? $room['room_type_id'] ?? '';
                $payload = [
                    'listing_id' => $listingId,
                    'calendar' => [
                        [
                            'date' => $room['date'] ?? $room['start_date'] ?? '',
                            'available' => ($room['count'] ?? $room['available'] ?? 0) > 0,
                            'price' => $room['amount'] ?? null,
                        ],
                    ],
                ];

                $response = $this->http()->put(
                    'listings/' . urlencode((string) $listingId) . '/calendar',
                    ['json' => $payload]
                );

                $result = $this->parseJsonResponse($response, 'availability');
                $results[] = $result;

                if (! $result['success']) {
                    return $result;
                }
            }

            return [
                'success' => true,
                'data' => ['results' => $results],
                'error' => null,
            ];
        });
    }

    public function syncRates(array $rates): array
    {
        return $this->executeSync('push_rates', function () use ($rates) {
            $byListing = [];
            foreach ($rates as $rate) {
                $listingId = $rate['channel_room_id'] ?? $rate['room_type_id'] ?? '';
                $byListing[$listingId][] = $rate;
            }

            $results = [];
            foreach ($byListing as $listingId => $listingRates) {
                $pricingRules = array_map(function ($rate) {
                    return [
                        'date' => $rate['date'] ?? $rate['start_date'] ?? '',
                        'base_price' => (float) ($rate['amount'] ?? 0),
                        'currency' => $rate['currency'] ?? 'IDR',
                    ];
                }, $listingRates);

                $payload = [
                    'listing_id' => $listingId,
                    'pricing' => $pricingRules,
                ];

                $response = $this->http()->post(
                    'listings/' . urlencode((string) $listingId) . '/pricing',
                    ['json' => $payload]
                );

                $result = $this->parseJsonResponse($response, 'rates');
                $results[] = $result;

                if (! $result['success']) {
                    return $result;
                }
            }

            return [
                'success' => true,
                'data' => ['results' => $results],
                'error' => null,
            ];
        });
    }

    public function syncRestrictions(array $restrictions): array
    {
        return $this->executeSync('push_restrictions', function () use ($restrictions) {
            $byListing = [];
            foreach ($restrictions as $r) {
                $listingId = $r['channel_room_id'] ?? $r['room_type_id'] ?? '';
                $byListing[$listingId][] = $r;
            }

            $results = [];
            foreach ($byListing as $listingId => $listingRestrictions) {
                $r = $listingRestrictions[0];

                $payload = array_filter([
                    'listing_id' => $listingId,
                    'booking_settings' => array_filter([
                        'min_nights' => $r['min_stay'] ?? $r['min_los'] ?? null,
                        'max_nights' => $r['max_stay'] ?? $r['max_los'] ?? null,
                        'advance_notice' => $r['ctd'] ?? null,
                        'preparation_time' => $r['cta'] ?? null,
                        'available' => ! ($r['closed'] ?? false),
                    ], fn ($v) => $v !== null),
                ], fn ($v) => $v !== null);

                $response = $this->http()->put(
                    'listings/' . urlencode((string) $listingId) . '/booking_settings',
                    ['json' => $payload]
                );

                $result = $this->parseJsonResponse($response, 'restrictions');
                $results[] = $result;

                if (! $result['success']) {
                    return $result;
                }
            }

            return [
                'success' => true,
                'data' => ['results' => $results],
                'error' => null,
            ];
        });
    }

    public function fetchBookings(?\DateTimeInterface $since = null): array
    {
        return $this->executeSync('fetch_bookings', function () use ($since) {
            $cred = $this->channel->getCredentials() ?? [];
            $hostId = $cred['host_id'] ?? '';
            if (empty($hostId)) {
                throw ChannelSyncException::forChannel(
                    $this->channel->id,
                    $this->channel->name,
                    'Host ID not configured.',
                );
            }

            $params = [
                'host_id' => $hostId,
                'start_date' => $since ? $since->format('Y-m-d') : now()->subDays(30)->format('Y-m-d'),
                'end_date' => now()->addDays(180)->format('Y-m-d'),
                '_limit' => 50,
            ];

            $allBookings = [];
            $offset = 0;

            do {
                $params['_offset'] = $offset;
                $response = $this->http()->get('reservations?' . http_build_query($params));
                $data = $this->parseJsonResponse($response, 'bookings');

                $bookings = $data['data']['reservations'] ?? $data['data']['results'] ?? [];
                $allBookings = array_merge($allBookings, $bookings);

                $hasMore = count($bookings) >= ($params['_limit'] ?? 50);
                $offset += count($bookings);
            } while ($hasMore);

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
            $response = $this->http()->get('reservations/' . urlencode($bookingId));
            return $this->parseJsonResponse($response, 'booking');
        });
    }

    public function cancelBooking(string $bookingId, string $reason = ''): array
    {
        return $this->executeSync('cancel_booking', function () use ($bookingId, $reason) {
            $payload = [
                'reason' => $reason ?: 'Cancelled by host',
            ];

            $response = $this->http()->post(
                'reservations/' . urlencode($bookingId) . '/cancel',
                ['json' => $payload]
            );

            return $this->parseJsonResponse($response, 'cancel');
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
        if (empty($cred['access_token']) || empty($cred['api_key'])) {
            return ['ok' => false, 'message' => 'Access token or API key not configured'];
        }
        try {
            $r = $this->http()->get('host/me');
            return ['ok' => $r->getStatusCode() < 400, 'message' => 'HTTP ' . $r->getStatusCode()];
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

        if ($statusCode === 401) {
            return [
                'success' => false,
                'data' => [],
                'error' => 'Authentication failed. Token may be expired.',
            ];
        }

        if ($statusCode >= 400) {
            $errorMsg = $data['message'] ?? $data['error'] ?? $data['error_description'] ?? 'Unknown error';
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
            Log::channel('channel-manager')->error('Airbnb sync failed', [
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
            Log::channel('channel-manager')->error('Airbnb connection error', [
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
            Log::channel('channel-manager')->error('Airbnb sync unexpected error', [
                'channel_id' => $this->channel->id,
                'operation' => $operation,
                'error' => $e->getMessage(),
            ]);
            throw ChannelSyncException::networkError($this->channel->id, $operation, $e->getMessage());
        }
    }
}
