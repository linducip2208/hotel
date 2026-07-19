<?php

declare(strict_types=1);

namespace App\Adapters\Channel;

use App\Exceptions\ChannelSyncException;
use App\Models\AriSyncLog;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookingComAdapter extends BaseChannelAdapter
{
    protected function defaultBaseUrl(): string
    {
        return 'https://supply-xml.booking.com/hotels/ota/';
    }

    protected function http(): Client
    {
        $cred = $this->channel->getCredentials();
        return new Client([
            'base_uri' => $this->getBaseUrl(),
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => false,
            'auth' => [$cred['username'] ?? '', $cred['password'] ?? ''],
            'headers' => [
                'Content-Type' => 'application/xml',
                'Accept' => 'application/xml',
            ],
        ]);
    }

    // ─── Public API ───────────────────────────────────────────────

    public function syncAvailability(array $rooms): array
    {
        return $this->executeSync('push_availability', function () use ($rooms) {
            $xmlBody = $this->buildAvailNotifXml($rooms);
            $xml = $this->buildXmlEnvelope($xmlBody, 'OTA_HotelAvailNotifRQ');
            $response = $this->http()->post('OTA_HotelAvailNotif', ['body' => $xml]);

            $parsed = $this->parseOtaResponse($response);
            if (! $parsed['success']) {
                throw ChannelSyncException::forChannel(
                    $this->channel->id,
                    $this->channel->name,
                    $parsed['error'] ?? 'Availability sync failed',
                    $parsed,
                );
            }

            return $parsed;
        });
    }

    public function syncRates(array $rates): array
    {
        return $this->executeSync('push_rates', function () use ($rates) {
            $xmlBody = $this->buildRateAmountNotifXml($rates);
            $xml = $this->buildXmlEnvelope($xmlBody, 'OTA_HotelRateAmountNotifRQ');
            $response = $this->http()->post('OTA_HotelRateAmountNotif', ['body' => $xml]);

            $parsed = $this->parseOtaResponse($response);
            if (! $parsed['success']) {
                throw ChannelSyncException::forChannel(
                    $this->channel->id,
                    $this->channel->name,
                    $parsed['error'] ?? 'Rate sync failed',
                    $parsed,
                );
            }

            return $parsed;
        });
    }

    public function syncRestrictions(array $restrictions): array
    {
        return $this->executeSync('push_restrictions', function () use ($restrictions) {
            $xmlBody = $this->buildRestrictionXml($restrictions);
            $xml = $this->buildXmlEnvelope($xmlBody, 'OTA_HotelAvailNotifRQ');
            $response = $this->http()->post('OTA_HotelAvailNotif', ['body' => $xml]);

            $parsed = $this->parseOtaResponse($response);
            if (! $parsed['success']) {
                throw ChannelSyncException::forChannel(
                    $this->channel->id,
                    $this->channel->name,
                    $parsed['error'] ?? 'Restriction sync failed',
                    $parsed,
                );
            }

            return $parsed;
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

            $sinceStr = $since
                ? $since->format('Y-m-d\TH:i:s')
                : now()->subDays(7)->format('Y-m-d\TH:i:s');

            $xmlBody = '<OTA_HotelResNotifRQ xmlns="http://www.opentravel.org/OTA/2003/05" '
                . 'TimeStamp="' . now()->toISOString() . '" Version="1.0">'
                . '<HotelReservations>'
                . '<HotelReservation>'
                . '<UniqueID Type="16" ID="' . htmlspecialchars($hotelId) . '"/>'
                . '</HotelReservation>'
                . '</HotelReservations>'
                . '<Criteria>'
                . '<Criterion>'
                . '<LastModifyDateTime>' . $sinceStr . '</LastModifyDateTime>'
                . '</Criterion>'
                . '</Criteria>'
                . '</OTA_HotelResNotifRQ>';

            $xml = $this->buildXmlEnvelope($xmlBody, 'OTA_HotelResNotifRQ');
            $response = $this->http()->get('OTA_HotelResNotif?hotel_id=' . urlencode($hotelId));

            if ($response->getStatusCode() >= 400) {
                throw ChannelSyncException::forChannel(
                    $this->channel->id,
                    $this->channel->name,
                    'Fetch bookings HTTP ' . $response->getStatusCode(),
                );
            }

            return $this->parseReservationResponse($response);
        });
    }

    public function fetchReservation(string $bookingId): array
    {
        return $this->executeSync('fetch_booking', function () use ($bookingId) {
            $hotelId = $this->channel->hotel_id_at_channel;
            $xmlBody = '<OTA_HotelResNotifRQ xmlns="http://www.opentravel.org/OTA/2003/05" '
                . 'TimeStamp="' . now()->toISOString() . '" Version="1.0">'
                . '<HotelReservations>'
                . '<HotelReservation>'
                . '<UniqueID Type="16" ID="' . htmlspecialchars($hotelId) . '"/>'
                . '</HotelReservation>'
                . '</HotelReservations>'
                . '<ReservationID>'
                . '<UniqueID Type="14" ID="' . htmlspecialchars($bookingId) . '"/>'
                . '</ReservationID>'
                . '</OTA_HotelResNotifRQ>';

            $xml = $this->buildXmlEnvelope($xmlBody, 'OTA_HotelResNotifRQ');
            $response = $this->http()->get('OTA_HotelResNotif?res_id=' . urlencode($bookingId));

            if ($response->getStatusCode() >= 400) {
                throw ChannelSyncException::forChannel(
                    $this->channel->id,
                    $this->channel->name,
                    'Fetch reservation HTTP ' . $response->getStatusCode(),
                );
            }

            return $this->parseReservationResponse($response);
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
        if (empty($cred['username']) || empty($cred['password'])) {
            return ['ok' => false, 'message' => 'Credentials not configured'];
        }
        try {
            $r = $this->http()->get('ping');
            return ['ok' => $r->getStatusCode() < 400, 'message' => 'HTTP ' . $r->getStatusCode()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    // ─── Private Builders ─────────────────────────────────────────

    protected function buildAvailNotifXml(array $rooms): string
    {
        $hotelId = $this->channel->hotel_id_at_channel;

        $xml = '<OTA_HotelAvailNotifRQ xmlns="http://www.opentravel.org/OTA/2003/05" '
            . 'TimeStamp="' . now()->toISOString() . '" Version="1.0">'
            . '<AvailStatusMessages HotelCode="' . htmlspecialchars((string) $hotelId) . '">';

        foreach ($rooms as $room) {
            $xml .= '<AvailStatusMessage>'
                . '<StatusApplicationControl '
                . 'InvTypeCode="' . htmlspecialchars($room['channel_room_id'] ?? $room['room_type_id'] ?? '') . '" '
                . 'RatePlanCode="' . htmlspecialchars($room['channel_rate_id'] ?? $room['rate_plan_id'] ?? '') . '" '
                . 'Start="' . ($room['start_date'] ?? $room['date'] ?? '') . '" '
                . 'End="' . ($room['end_date'] ?? $room['date'] ?? '') . '" '
                . '/>';

            if (isset($room['count']) || isset($room['available'])) {
                $count = $room['count'] ?? $room['available'] ?? 0;
                $restriction = ($count <= 0) ? 'Closed' : 'Open';
                $xml .= '<RestrictionStatus Restriction="Master" Status="' . $restriction . '"/>';
            }

            $xml .= '</AvailStatusMessage>';
        }

        $xml .= '</AvailStatusMessages></OTA_HotelAvailNotifRQ>';
        return $xml;
    }

    protected function buildRateAmountNotifXml(array $rates): string
    {
        $hotelId = $this->channel->hotel_id_at_channel;

        $xml = '<OTA_HotelRateAmountNotifRQ xmlns="http://www.opentravel.org/OTA/2003/05" '
            . 'TimeStamp="' . now()->toISOString() . '" Version="1.0">'
            . '<RateAmountMessages HotelCode="' . htmlspecialchars((string) $hotelId) . '">';

        foreach ($rates as $rate) {
            $xml .= '<RateAmountMessage>'
                . '<StatusApplicationControl '
                . 'InvTypeCode="' . htmlspecialchars($rate['channel_room_id'] ?? $rate['room_type_id'] ?? '') . '" '
                . 'RatePlanCode="' . htmlspecialchars($rate['channel_rate_id'] ?? $rate['rate_plan_id'] ?? '') . '" '
                . 'Start="' . ($rate['start_date'] ?? $rate['date'] ?? '') . '" '
                . 'End="' . ($rate['end_date'] ?? $rate['date'] ?? '') . '" '
                . '/>'
                . '<Rates>'
                . '<Rate>'
                . '<BaseByGuestAmts>'
                . '<BaseByGuestAmt '
                . 'AmountAfterTax="' . number_format((float) ($rate['amount'] ?? 0), 2, '.', '') . '" '
                . 'CurrencyCode="' . ($rate['currency'] ?? 'IDR') . '" '
                . 'NumberOfGuests="1" '
                . '/>'
                . '</BaseByGuestAmts>'
                . '</Rate>'
                . '</Rates>'
                . '</RateAmountMessage>';
        }

        $xml .= '</RateAmountMessages></OTA_HotelRateAmountNotifRQ>';
        return $xml;
    }

    protected function buildRestrictionXml(array $restrictions): string
    {
        $hotelId = $this->channel->hotel_id_at_channel;

        $xml = '<OTA_HotelAvailNotifRQ xmlns="http://www.opentravel.org/OTA/2003/05" '
            . 'TimeStamp="' . now()->toISOString() . '" Version="1.0">'
            . '<AvailStatusMessages HotelCode="' . htmlspecialchars((string) $hotelId) . '">';

        foreach ($restrictions as $r) {
            $xml .= '<AvailStatusMessage>'
                . '<StatusApplicationControl '
                . 'InvTypeCode="' . htmlspecialchars($r['channel_room_id'] ?? $r['room_type_id'] ?? '') . '" '
                . 'RatePlanCode="' . htmlspecialchars($r['channel_rate_id'] ?? $r['rate_plan_id'] ?? '') . '" '
                . 'Start="' . ($r['start_date'] ?? $r['date'] ?? '') . '" '
                . 'End="' . ($r['end_date'] ?? $r['date'] ?? '') . '" '
                . '/>';

            if (isset($r['min_los'])) {
                $xml .= '<LengthsOfStay>'
                    . '<LengthOfStay TimeUnit="Day" Time="' . (int) $r['min_los'] . '" MinMaxMessageType="MinLOS"/>'
                    . '</LengthsOfStay>';
            }
            if (isset($r['max_los'])) {
                $xml .= '<LengthsOfStay>'
                    . '<LengthOfStay TimeUnit="Day" Time="' . (int) $r['max_los'] . '" MinMaxMessageType="MaxLOS"/>'
                    . '</LengthsOfStay>';
            }
            if (isset($r['cta']) && $r['cta']) {
                $xml .= '<RestrictionStatus Restriction="ClosedToArrival" Status="Closed"/>';
            }
            if (isset($r['ctd']) && $r['ctd']) {
                $xml .= '<RestrictionStatus Restriction="ClosedToDeparture" Status="Closed"/>';
            }
            if (isset($r['closed']) && $r['closed']) {
                $xml .= '<RestrictionStatus Restriction="Master" Status="Closed"/>';
            }

            $xml .= '</AvailStatusMessage>';
        }

        $xml .= '</AvailStatusMessages></OTA_HotelAvailNotifRQ>';
        return $xml;
    }

    protected function buildXmlEnvelope(string $body, string $rootElement): string
    {
        $rph = bin2hex(random_bytes(8));
        $messageId = dechex(time()) . '-' . $rph;

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" '
            . 'xmlns:ota="http://www.opentravel.org/OTA/2003/05">' . "\n"
            . '<soapenv:Header>'
            . '<ota:MessageID>' . $messageId . '</ota:MessageID>'
            . '<ota:RPH>' . $rph . '</ota:RPH>'
            . '</soapenv:Header>' . "\n"
            . '<soapenv:Body>' . "\n"
            . $body . "\n"
            . '</soapenv:Body>' . "\n"
            . '</soapenv:Envelope>';
    }

    protected function signRequest(string $xml): string
    {
        return $xml;
    }

    // ─── Response Parsing ─────────────────────────────────────────

    protected function parseOtaResponse($response): array
    {
        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 500) {
            return [
                'success' => false,
                'data' => [],
                'error' => 'Server error HTTP ' . $statusCode,
            ];
        }

        if ($statusCode === 429) {
            return [
                'success' => false,
                'data' => [],
                'error' => 'Rate limited. Retry after ' . ($response->getHeader('Retry-After')[0] ?? 'unknown'),
            ];
        }

        try {
            $xml = @simplexml_load_string($body);
            if (! $xml) {
                return [
                    'success' => $statusCode < 400,
                    'data' => ['raw' => Str::limit($body, 500)],
                    'error' => $statusCode >= 400 ? 'Invalid XML response' : null,
                ];
            }

            $xml->registerXPathNamespace('ota', 'http://www.opentravel.org/OTA/2003/05');
            $successAttrs = $xml->xpath('//@EchoToken') ?: [];
            $errorNodes = $xml->xpath('//ota:Errors/ota:Error');

            if (! empty($errorNodes)) {
                $errors = [];
                foreach ($errorNodes as $error) {
                    $errors[] = [
                        'code' => (string) ($error->attributes()->Code ?? ''),
                        'message' => (string) $error,
                    ];
                }
                return [
                    'success' => false,
                    'data' => [],
                    'error' => implode('; ', array_column($errors, 'message')),
                    'errors' => $errors,
                ];
            }

            $successNodes = $xml->xpath('//ota:Success');
            if (! empty($successNodes)) {
                return [
                    'success' => true,
                    'data' => ['status' => 'OK', 'message' => 'OTA Success'],
                    'error' => null,
                ];
            }

            return [
                'success' => true,
                'data' => ['status' => 'OK', 'raw' => $body],
                'error' => null,
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'data' => [],
                'error' => 'XML parse error: ' . $e->getMessage(),
            ];
        }
    }

    protected function parseReservationResponse($response): array
    {
        $body = (string) $response->getBody();

        try {
            $xml = @simplexml_load_string($body);
            if (! $xml) {
                return ['success' => false, 'data' => ['bookings' => []], 'error' => 'Invalid XML'];
            }

            $xml->registerXPathNamespace('ota', 'http://www.opentravel.org/OTA/2003/05');
            $reservations = $xml->xpath('//ota:HotelReservation');

            $bookings = [];
            foreach ($reservations as $res) {
                $resIdNodes = $res->xpath('.//ota:UniqueID[@Type="14"]');
                $customerNameNodes = $res->xpath('.//ota:PersonName/ota:GivenName | .//ota:PersonName/ota:Surname');
                $checkInNodes = $res->xpath('.//ota:TimeSpan/@Start');
                $checkOutNodes = $res->xpath('.//ota:TimeSpan/@End');
                $totalNodes = $res->xpath('.//ota:Total/@AmountAfterTax');
                $statusNodes = $res->xpath('.//@ReservationStatus');

                $bookings[] = [
                    'channel_ref' => isset($resIdNodes[0]) ? (string) $resIdNodes[0]->attributes()->ID : '',
                    'guest_name' => isset($customerNameNodes[0]) ? trim(
                        (string) ($customerNameNodes[0] ?? '') . ' ' . (string) ($customerNameNodes[1] ?? '')
                    ) : '',
                    'check_in' => isset($checkInNodes[0]) ? (string) $checkInNodes[0] : '',
                    'check_out' => isset($checkOutNodes[0]) ? (string) $checkOutNodes[0] : '',
                    'total' => isset($totalNodes[0]) ? (float) $totalNodes[0] : 0,
                    'status' => isset($statusNodes[0]) ? (string) $statusNodes[0] : 'unknown',
                ];
            }

            return [
                'success' => true,
                'data' => ['bookings' => $bookings],
                'error' => null,
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'data' => ['bookings' => []],
                'error' => 'Parse error: ' . $e->getMessage(),
            ];
        }
    }

    // ─── Sync Execution with Logging ──────────────────────────────

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
            Log::channel('channel-manager')->error('Booking.com sync failed', [
                'channel_id' => $this->channel->id,
                'operation' => $operation,
                'error' => $e->getMessage(),
                'context' => $e->getContext(),
            ]);
            throw $e;

        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error' => $e->getMessage(),
            ]);
            Log::channel('channel-manager')->error('Booking.com sync unexpected error', [
                'channel_id' => $this->channel->id,
                'operation' => $operation,
                'error' => $e->getMessage(),
            ]);
            throw ChannelSyncException::networkError($this->channel->id, $operation, $e->getMessage());
        }
    }
}
