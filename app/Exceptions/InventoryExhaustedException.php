<?php

declare(strict_types=1);

namespace App\Exceptions;

final class InventoryExhaustedException extends HotelException
{
    public static function forItem(
        int $inventoryId,
        string $itemName,
        int $requested,
        int $available,
    ): self {
        return new self(
            message: sprintf(
                'Inventory item "%s" (#%d) is exhausted: %d requested, only %d available.',
                $itemName,
                $inventoryId,
                $requested,
                $available,
            ),
            errorCode: 'INVENTORY_EXHAUSTED',
            httpStatusCode: 409,
            context: [
                'inventory_id' => $inventoryId,
                'item_name' => $itemName,
                'requested' => $requested,
                'available' => $available,
            ],
        );
    }

    public static function forStock(
        int $inventoryId,
        string $itemName,
    ): self {
        return new self(
            message: sprintf(
                'Inventory item "%s" (#%d) is out of stock.',
                $itemName,
                $inventoryId,
            ),
            errorCode: 'INVENTORY_EXHAUSTED',
            httpStatusCode: 409,
            context: [
                'inventory_id' => $inventoryId,
                'item_name' => $itemName,
            ],
        );
    }
}
