<?php

declare(strict_types=1);

namespace App\Adapters\Channel;

use App\Exceptions\ChannelSyncException;
use App\Models\AriSyncLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;

class MisterAladinAdapter extends BaseChannelAdapter
{
    protected function defaultBaseUrl(): string
    {
        return 'https://api.misteraladin.com/hotel/v1/';
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
                'Authorization' => 'Bearer ' . ($cred['token'] ?? $cred['access_token'] ?? $cred['api_key'] ?? ''),
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
                'rooms' => array_map(function ($room) {
                    return [
                        'room_type_code' => $room['channel_room_id'] ?? $room['room_type_id'] ?? '',
                        'date' => $room['date'] ?? $room['start_date'] ?? '',
                        'allotment' => $room['count'] ?? $room['available'] ?? 0,
                        'status' => ($room['count'] ?? $room['available'] ?? 0) <= 0 ? 'sold_out' : 'available',
                    ];
                }, $rooms),
            ];

            $response = $this->http()->put(
                'hotels/' . urlencode((string) $hotelId) . '/rooms/availability',
                ['json' => $payload]
            );

            return $this->parseJsonResponse($response, 'availability');
        });
    }

    public function syncRates(array $rates): array
    {
        return $this->executeSync('push_rates', function () use ($rates) {
            $hotelId = $this->channel->hotel_id_at_channel;
            $payload = [
                'prices' => array_map(function ($rate) {
                    return [
                        'room_type_code' => $rate['channel_room_id'] ?? $rate['room_type_id'] ?? '',
                        'rate_plan_code' => $rate['channel_rate_id'] ?? $rate['rate_plan_id'] ?? '',
                        'date' => $rate['date'] ?? $rate['start_date'] ?? '',
                        'price' => (float) ($rate['amount'] ?? 0),
                        'currency' => $rate['currency'] ?? 'IDR',
                    ];
                }, $rates),
            ];

            $response = $this->http()->put(
                'hotels/' . urlencode((string) $hotelId) . '/rooms/prices',
                ['json' => $payload]
            );

            return $this->parseJsonResponse($response, 'rates');
        });
    }

    public function syncRestrictions(array $restrictions): array
    {
        return $this->executeSync('push_restrictions', function () use ($restrictions) {
            $hotelId = $this->channel->hotel_id_at_channel;
            $payload = [
                'policies' => array_map(function ($r) {
                    return array_filter([
                        'room_type_code' => $r['channel_room_id'] ?? $r['room_type_id'] ?? '',
                        'rate_plan_code' => $r['channel_rate_id'] ?? $r['rate_plan_id'] ?? null,
                        'date' => $r['date'] ?? $r['start_date'] ?? '',
                        'min_nights' => $r['min_stay'] ?? $r['min_los'] ?? null,
                        'max_nights' => $r['max_stay'] ?? $r['max_los'] ?? null,
                        'closed_to_arrival' => $r['cta'] ?? null,
                        'closed_to_departure' => $r['ctd'] ?? null,
                        'stop_sell' => $r['closed'] ?? null,
                    ], fn ($v) => $v !== null);
                }, $restrictions),
            ];

            $response = $this->http()->put(
                'hotels/' . urlencode((string) $hotelId) . '/rooms/policies',
                ['json' => $payload]
            );

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
                'per_page' => 100,
            ];

            if ($since) {
                $params['updated_after'] = $since->format('Y-m-d\TH:i:s\Z');
            }

            $allBookings = [];
            $page = 1;

            do {
                $params['page'] = $page;
                $response = $this->http()->get(
                    'hotels/' . urlencode((string) $hotelId) . '/bookings?' . http_build_query($params)
                );
                $data = $this->parseJsonResponse($response, 'bookings');

                $bookings = $data['data']['bookings'] ?? $data['data']['data'] ?? [];
                $allBookings = array_merge($allBookings, $bookings);

                $hasMore = ! empty($bookings) && count($bookings) >= ($params['per_page'] ?? 100);
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
        if (empty($cred['token']) && empty($cred['access_token']) && empty($cred['api_key'])) {
            return ['ok' => false, 'message' => 'Bearer token not configured'];
        }
        try {
            $r = $this->http()->get('health');
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
            Log::channel('channel-manager')->error('Mister Aladin sync failed', [
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
            Log::channel('channel-manager')->error('Mister Aladin connection error', [
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
            Log::channel('channel-manager')->error('Mister Aladin sync unexpected error', [
                'channel_id' => $this->channel->id,
                'operation' => $operation,
                'error' => $e->getMessage(),
            ]);
            throw ChannelSyncException::networkError($this->channel->id, $operation, $e->getMessage());
        }
    }
}
