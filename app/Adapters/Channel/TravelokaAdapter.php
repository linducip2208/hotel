<?php

declare(strict_types=1);

namespace App\Adapters\Channel;

use App\Exceptions\ChannelSyncException;
use App\Models\AriSyncLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;

class TravelokaAdapter extends BaseChannelAdapter
{
    protected function defaultBaseUrl(): string
    {
        return 'https://api.traveloka.com/v2/';
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
                'X-API-Key' => $cred['api_key'] ?? '',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    // ─── Public API ───────────────────────────────────────────────

    public function syncAvailability(array $rooms): array
    {
        return $this->executeSync('push_availability', function () use ($rooms) {
            $propertyId = $this->channel->hotel_id_at_channel;
            $payload = [
                'property_id' => $propertyId,
                'updates' => array_map(function ($room) {
                    return array_filter([
                        'room_type_id' => $room['channel_room_id'] ?? $room['room_type_id'] ?? '',
                        'date' => $room['date'] ?? $room['start_date'] ?? '',
                        'allotment' => $room['count'] ?? $room['available'] ?? 0,
                        'stop_sell' => ($room['count'] ?? $room['available'] ?? 0) <= 0,
                        'restrictions' => null,
                    ]);
                }, $rooms),
            ];

            $endpoint = 'inventory/bulk';
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $signature = $this->generateSignature('POST', $endpoint, $body);

            $response = $this->http()->post($endpoint, [
                'json' => $payload,
                'headers' => [
                    'X-Signature' => $signature,
                ],
            ]);

            return $this->parseJsonResponse($response, 'availability');
        });
    }

    public function syncRates(array $rates): array
    {
        return $this->executeSync('push_rates', function () use ($rates) {
            $propertyId = $this->channel->hotel_id_at_channel;
            $payload = [
                'property_id' => $propertyId,
                'updates' => array_map(function ($rate) {
                    return [
                        'room_type_id' => $rate['channel_room_id'] ?? $rate['room_type_id'] ?? '',
                        'rate_plan_id' => $rate['channel_rate_id'] ?? $rate['rate_plan_id'] ?? '',
                        'date' => $rate['date'] ?? $rate['start_date'] ?? '',
                        'price' => (float) ($rate['amount'] ?? 0),
                        'currency' => $rate['currency'] ?? 'IDR',
                    ];
                }, $rates),
            ];

            $endpoint = 'rates/bulk';
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $signature = $this->generateSignature('POST', $endpoint, $body);

            $response = $this->http()->post($endpoint, [
                'json' => $payload,
                'headers' => [
                    'X-Signature' => $signature,
                ],
            ]);

            return $this->parseJsonResponse($response, 'rates');
        });
    }

    public function syncRestrictions(array $restrictions): array
    {
        return $this->executeSync('push_restrictions', function () use ($restrictions) {
            $propertyId = $this->channel->hotel_id_at_channel;
            $payload = [
                'property_id' => $propertyId,
                'updates' => array_map(function ($r) {
                    return array_filter([
                        'room_type_id' => $r['channel_room_id'] ?? $r['room_type_id'] ?? '',
                        'rate_plan_id' => $r['channel_rate_id'] ?? $r['rate_plan_id'] ?? '',
                        'date' => $r['date'] ?? $r['start_date'] ?? '',
                        'min_stay' => $r['min_stay'] ?? $r['min_los'] ?? null,
                        'max_stay' => $r['max_stay'] ?? $r['max_los'] ?? null,
                        'closed_to_arrival' => $r['cta'] ?? null,
                        'closed_to_departure' => $r['ctd'] ?? null,
                        'stop_sell' => $r['closed'] ?? null,
                    ], fn ($v) => $v !== null);
                }, $restrictions),
            ];

            $endpoint = 'inventory/bulk';
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $signature = $this->generateSignature('POST', $endpoint, $body);

            $response = $this->http()->post($endpoint, [
                'json' => $payload,
                'headers' => [
                    'X-Signature' => $signature,
                ],
            ]);

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
                'per_page' => 100,
            ];

            if ($since) {
                $params['modified_since'] = $since->format('c');
            }

            $allBookings = [];
            $page = 1;
            $nextPageToken = null;

            do {
                $params['page'] = $page;
                if ($nextPageToken) {
                    $params['page_token'] = $nextPageToken;
                }

                $queryString = http_build_query($params);
                $endpoint = 'bookings?' . $queryString;
                $signature = $this->generateSignature('GET', $endpoint, '');

                $response = $this->http()->get('bookings?' . $queryString, [
                    'headers' => ['X-Signature' => $signature],
                ]);

                $data = $this->parseJsonResponse($response, 'bookings');

                $bookings = $data['data']['bookings'] ?? $data['data']['reservations'] ?? [];
                $allBookings = array_merge($allBookings, $bookings);

                $nextPageToken = $data['data']['next_page_token'] ?? null;
                $hasMore = ! empty($bookings) && ! empty($nextPageToken);
                $page++;
            } while ($hasMore && $page <= 10);

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
            $endpoint = 'bookings/' . urlencode($bookingId);
            $signature = $this->generateSignature('GET', $endpoint, '');

            $response = $this->http()->get('bookings/' . urlencode($bookingId), [
                'headers' => [
                    'X-Signature' => $signature,
                ],
            ]);

            return $this->parseJsonResponse($response, 'booking');
        });
    }

    public function cancelBooking(string $bookingId, string $reason = ''): array
    {
        return $this->executeSync('cancel_booking', function () use ($bookingId, $reason) {
            $payload = [
                'booking_id' => $bookingId,
                'reason' => $reason ?: 'Cancelled by hotel',
            ];

            $endpoint = 'bookings/' . urlencode($bookingId) . '/cancel';
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $signature = $this->generateSignature('POST', $endpoint, $body);

            $response = $this->http()->post($endpoint, [
                'json' => $payload,
                'headers' => [
                    'X-Signature' => $signature,
                ],
            ]);

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
        if (empty($cred['api_key'])) {
            return ['ok' => false, 'message' => 'API key not configured'];
        }
        try {
            $signature = $this->generateSignature('GET', 'health', '');
            $r = $this->http()->get('health', [
                'headers' => ['X-Signature' => $signature],
            ]);
            return ['ok' => $r->getStatusCode() < 400, 'message' => 'HTTP ' . $r->getStatusCode()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    // ─── Private HMAC-SHA256 Signing ──────────────────────────────

    protected function generateSignature(string $method, string $endpoint, string $body): string
    {
        $cred = $this->channel->getCredentials() ?? [];
        $apiSecret = $cred['api_secret'] ?? '';

        if (empty($apiSecret)) {
            Log::warning('Traveloka API secret not configured. Using empty signature.', [
                'channel_id' => $this->channel->id,
            ]);
            return '';
        }

        $timestamp = (string) time();
        $nonce = bin2hex(random_bytes(16));

        $stringToSign = strtoupper($method) . "\n"
            . '/' . ltrim($endpoint, '/') . "\n"
            . $timestamp . "\n"
            . $nonce . "\n"
            . hash('sha256', $body);

        $signature = hash_hmac('sha256', $stringToSign, $apiSecret);

        return $timestamp . ':' . $nonce . ':' . $signature;
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
            $errorMsg = $data['message'] ?? $data['error'] ?? $data['description'] ?? 'Unknown error';
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
            Log::channel('channel-manager')->error('Traveloka sync failed', [
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
            Log::channel('channel-manager')->error('Traveloka connection error', [
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
            Log::channel('channel-manager')->error('Traveloka sync unexpected error', [
                'channel_id' => $this->channel->id,
                'operation' => $operation,
                'error' => $e->getMessage(),
            ]);
            throw ChannelSyncException::networkError($this->channel->id, $operation, $e->getMessage());
        }
    }
}
