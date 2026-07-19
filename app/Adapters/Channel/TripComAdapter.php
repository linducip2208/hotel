<?php

declare(strict_types=1);

namespace App\Adapters\Channel;

use App\Exceptions\ChannelSyncException;
use App\Models\AriSyncLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;

class TripComAdapter extends BaseChannelAdapter
{
    protected function defaultBaseUrl(): string
    {
        return 'https://api.trip.com/connect/v1/';
    }

    protected function http(): Client
    {
        return new Client([
            'base_uri' => $this->getBaseUrl(),
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    // ─── Public API ───────────────────────────────────────────────

    public function syncAvailability(array $rooms): array
    {
        return $this->executeSync('push_availability', function () use ($rooms) {
            $hotelId = $this->channel->hotel_id_at_channel;
            $payload = [
                'hotel_id' => $hotelId,
                'inventory' => array_map(function ($room) {
                    return [
                        'room_type_code' => $room['channel_room_id'] ?? $room['room_type_id'] ?? '',
                        'date' => $room['date'] ?? $room['start_date'] ?? '',
                        'availability' => $room['count'] ?? $room['available'] ?? 0,
                        'status' => ($room['count'] ?? $room['available'] ?? 0) <= 0 ? 'sold_out' : 'available',
                    ];
                }, $rooms),
            ];

            $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $signature = $this->generateSignature($body);

            $response = $this->http()->post('inventory/update', [
                'json' => $payload,
                'headers' => [
                    'X-API-Key' => ($this->channel->getCredentials() ?? [])['api_key'] ?? '',
                    'X-Signature' => $signature,
                    'X-Timestamp' => (string) time(),
                ],
            ]);

            return $this->parseJsonResponse($response, 'availability');
        });
    }

    public function syncRates(array $rates): array
    {
        return $this->executeSync('push_rates', function () use ($rates) {
            $hotelId = $this->channel->hotel_id_at_channel;
            $payload = [
                'hotel_id' => $hotelId,
                'rates' => array_map(function ($rate) {
                    return [
                        'room_type_code' => $rate['channel_room_id'] ?? $rate['room_type_id'] ?? '',
                        'rate_plan_code' => $rate['channel_rate_id'] ?? $rate['rate_plan_id'] ?? '',
                        'date' => $rate['date'] ?? $rate['start_date'] ?? '',
                        'price' => (float) ($rate['amount'] ?? 0),
                        'currency' => $rate['currency'] ?? 'IDR',
                    ];
                }, $rates),
            ];

            $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $signature = $this->generateSignature($body);

            $response = $this->http()->post('rates/update', [
                'json' => $payload,
                'headers' => [
                    'X-API-Key' => ($this->channel->getCredentials() ?? [])['api_key'] ?? '',
                    'X-Signature' => $signature,
                    'X-Timestamp' => (string) time(),
                ],
            ]);

            return $this->parseJsonResponse($response, 'rates');
        });
    }

    public function syncRestrictions(array $restrictions): array
    {
        return $this->executeSync('push_restrictions', function () use ($restrictions) {
            $hotelId = $this->channel->hotel_id_at_channel;
            $payload = [
                'hotel_id' => $hotelId,
                'restrictions' => array_map(function ($r) {
                    return array_filter([
                        'room_type_code' => $r['channel_room_id'] ?? $r['room_type_id'] ?? '',
                        'rate_plan_code' => $r['channel_rate_id'] ?? $r['rate_plan_id'] ?? null,
                        'date' => $r['date'] ?? $r['start_date'] ?? '',
                        'min_length_of_stay' => $r['min_stay'] ?? $r['min_los'] ?? null,
                        'max_length_of_stay' => $r['max_stay'] ?? $r['max_los'] ?? null,
                        'min_advance_booking' => $r['ctd'] ?? null,
                        'max_advance_booking' => $r['cta'] ?? null,
                        'closed' => $r['closed'] ?? null,
                    ], fn ($v) => $v !== null);
                }, $restrictions),
            ];

            $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $signature = $this->generateSignature($body);

            $response = $this->http()->post('restrictions/update', [
                'json' => $payload,
                'headers' => [
                    'X-API-Key' => ($this->channel->getCredentials() ?? [])['api_key'] ?? '',
                    'X-Signature' => $signature,
                    'X-Timestamp' => (string) time(),
                ],
            ]);

            return $this->parseJsonResponse($response, 'restrictions');
        });
    }

    public function fetchBookings(?\DateTimeInterface $since = null): array
    {
        return $this->executeSync('fetch_bookings', function () use ($since) {
            $hotelId = $this->channel->hotel_id_at_channel;
            if (empty($hotelId)) {
                throw ChannelSyncException::forChannel(
                    $this->channel->id,
                    $this->channel->name,
                    'Hotel ID at channel not configured.',
                );
            }

            $params = [
                'hotel_id' => $hotelId,
                'checkin_from' => $since ? $since->format('Y-m-d') : now()->subDays(30)->format('Y-m-d'),
                'checkin_to' => now()->addDays(180)->format('Y-m-d'),
                'page_size' => 100,
            ];

            $allBookings = [];
            $page = 1;

            do {
                $params['page'] = $page;
                $queryString = http_build_query($params);
                $signature = $this->generateSignature('');

                $response = $this->http()->get('orders?' . $queryString, [
                    'headers' => [
                        'X-API-Key' => ($this->channel->getCredentials() ?? [])['api_key'] ?? '',
                        'X-Signature' => $signature,
                        'X-Timestamp' => (string) time(),
                    ],
                ]);

                $data = $this->parseJsonResponse($response, 'bookings');

                $bookings = $data['data']['orders'] ?? $data['data']['data'] ?? [];
                $allBookings = array_merge($allBookings, $bookings);

                $total = (int) ($data['data']['total_count'] ?? $data['data']['total'] ?? 0);
                $page++;
            } while (count($bookings) >= ($params['page_size'] ?? 100) && $page <= 10);

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
            $signature = $this->generateSignature('');

            $response = $this->http()->get('orders/' . urlencode($bookingId), [
                'headers' => [
                    'X-API-Key' => ($this->channel->getCredentials() ?? [])['api_key'] ?? '',
                    'X-Signature' => $signature,
                    'X-Timestamp' => (string) time(),
                ],
            ]);

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
        if (empty($cred['api_key']) || empty($cred['api_secret'])) {
            return ['ok' => false, 'message' => 'API key or secret not configured'];
        }
        try {
            $signature = $this->generateSignature('');
            $r = $this->http()->get('ping', [
                'headers' => [
                    'X-API-Key' => $cred['api_key'],
                    'X-Signature' => $signature,
                    'X-Timestamp' => (string) time(),
                ],
            ]);
            return ['ok' => $r->getStatusCode() < 400, 'message' => 'HTTP ' . $r->getStatusCode()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    // ─── Private HMAC-SHA256 Signing ──────────────────────────────

    protected function generateSignature(string $body): string
    {
        $cred = $this->channel->getCredentials() ?? [];
        $apiSecret = $cred['api_secret'] ?? '';

        if (empty($apiSecret)) {
            Log::warning('Trip.com API secret not configured.', [
                'channel_id' => $this->channel->id,
            ]);
            return '';
        }

        $timestamp = (string) time();
        $nonce = bin2hex(random_bytes(16));

        $stringToSign = $timestamp . "\n"
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
            $errorMsg = $data['message'] ?? $data['error'] ?? $data['error_msg'] ?? 'Unknown error';
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
            Log::channel('channel-manager')->error('Trip.com sync failed', [
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
            Log::channel('channel-manager')->error('Trip.com connection error', [
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
            Log::channel('channel-manager')->error('Trip.com sync unexpected error', [
                'channel_id' => $this->channel->id,
                'operation' => $operation,
                'error' => $e->getMessage(),
            ]);
            throw ChannelSyncException::networkError($this->channel->id, $operation, $e->getMessage());
        }
    }
}
