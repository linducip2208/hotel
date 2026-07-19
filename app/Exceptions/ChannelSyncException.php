<?php

declare(strict_types=1);

namespace App\Exceptions;

final class ChannelSyncException extends HotelException
{
    public static function forChannel(
        int $channelId,
        string $channelName,
        string $reason = '',
        ?array $responseBody = null,
    ): self {
        $message = $reason
            ? sprintf('Sync failed for channel "%s" (#%d): %s', $channelName, $channelId, $reason)
            : sprintf('Sync failed for channel "%s" (#%d).', $channelName, $channelId);

        return new self(
            message: $message,
            errorCode: 'CHANNEL_SYNC_FAILED',
            httpStatusCode: 502,
            context: [
                'channel_id' => $channelId,
                'channel_name' => $channelName,
                'reason' => $reason,
                'response_body' => $responseBody,
            ],
        );
    }

    public static function forRoomMapping(
        int $channelRoomMappingId,
        string $reason = '',
    ): self {
        return new self(
            message: sprintf(
                'Sync failed for channel room mapping #%d: %s',
                $channelRoomMappingId,
                $reason ?: 'unknown error',
            ),
            errorCode: 'CHANNEL_SYNC_FAILED',
            httpStatusCode: 502,
            context: [
                'channel_room_mapping_id' => $channelRoomMappingId,
                'reason' => $reason,
            ],
        );
    }

    public static function networkError(int $channelId, string $endpoint, string $error): self
    {
        return new self(
            message: sprintf(
                'Network error syncing channel #%d (%s): %s',
                $channelId,
                $endpoint,
                $error,
            ),
            errorCode: 'CHANNEL_SYNC_FAILED',
            httpStatusCode: 502,
            context: [
                'channel_id' => $channelId,
                'endpoint' => $endpoint,
                'error' => $error,
            ],
        );
    }
}
