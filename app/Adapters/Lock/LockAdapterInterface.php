<?php

declare(strict_types=1);

namespace App\Adapters\Lock;

use Carbon\Carbon;

interface LockAdapterInterface
{
    /** Encode and issue a key/card for a room. */
    public function encodeKey(string $roomNumber, Carbon $validFrom, Carbon $validTo, array $guestInfo): array;

    /** Revoke a previously issued key. */
    public function revokeKey(string $roomNumber, string $keyId): bool;

    /** Get the current lock status of a room door. */
    public function getLockStatus(string $roomNumber): array;

    /** Get audit trail logs for a room. */
    public function getAuditTrail(string $roomNumber, Carbon $from, Carbon $to): array;
}
