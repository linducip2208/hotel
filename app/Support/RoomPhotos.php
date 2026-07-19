<?php

namespace App\Support;

use App\Models\Room;
use App\Models\RoomType;

/**
 * Memberikan URL foto deterministic per Room.
 *
 * Strategi (dengan urutan fallback):
 *   1. Pool curated per kode type (SUP/DLX/STE) — Unsplash hotel photos.
 *      Tiap kamar dapat foto berbeda berdasarkan index dalam pool.
 *   2. Foto dari kolom photos[] di RoomType (legacy fallback).
 *   3. Picsum seeded URL — dijamin selalu return image valid.
 *
 * Kenapa: setiap dari 100+ kamar harus punya foto walau type cuma punya 4 entry.
 */
class RoomPhotos
{
    /** Curated Unsplash hotel photo pools (stable IDs). */
    private const POOLS = [
        'SUP' => [
            'photo-1611892440504-42a792e24d32',
            'photo-1505693416388-ac5ce068fe85',
            'photo-1566665797739-1674de7a421a',
            'photo-1582719478250-c89cae4dc85b',
            'photo-1551776235-dde6d482980b',
            'photo-1540518614846-7eded433c457',
            'photo-1522771739844-6a9f6d5f14af',
            'photo-1512918728675-ed5a9ecdebfd',
            'photo-1564013799919-ab600027ffc6',
            'photo-1560448075-bb485b067938',
            'photo-1568495248636-6432b97bd949',
            'photo-1631049307264-da0ec9d70304',
            'photo-1592229505726-ca121723b8ef',
            'photo-1611892440504-42a792e24d32',
            'photo-1591088398332-8a7791972843',
        ],
        'DLX' => [
            'photo-1631049307264-da0ec9d70304',
            'photo-1590490360182-c33d57733427',
            'photo-1591088398332-8a7791972843',
            'photo-1578683010236-d716f9a3f461',
            'photo-1601565415267-b51f7c0d62a2',
            'photo-1542314831-068cd1dbfeeb',
            'photo-1586611292717-f828b167408c',
            'photo-1566073771259-6a8506099945',
            'photo-1444201983204-c43cbd584d93',
            'photo-1551882547-ff40c63fe5fa',
            'photo-1596386461350-326ccb383e9f',
            'photo-1525258946800-98cfd641d0de',
        ],
        'STE' => [
            'photo-1618773928121-c32242e63f39',
            'photo-1582719508461-905c673771fd',
            'photo-1560448204-e02f11c3d0e2',
            'photo-1602002418816-5c0aeef426aa',
            'photo-1578898887932-dce23a595ad4',
            'photo-1591088398332-8a7791972843',
            'photo-1590490360182-c33d57733427',
            'photo-1631049307264-da0ec9d70304',
        ],
    ];

    /** Width × height untuk URL Unsplash. */
    private const W = 800;
    private const Q = 80;

    /**
     * Foto deterministic untuk satu Room.
     *
     * @param  Room|object  $room  perlu punya id, room_type_id (atau ->roomType->code)
     */
    public static function forRoom($room): string
    {
        $code = self::resolveTypeCode($room);
        $pool = self::POOLS[$code] ?? null;

        if ($pool) {
            $index = ((int) $room->id - 1) % count($pool);
            $photoId = $pool[$index];
            return "https://images.unsplash.com/{$photoId}?auto=format&fit=crop&w=".self::W.'&q='.self::Q;
        }

        // Fallback: foto dari kolom photos[] di RoomType
        if (isset($room->roomType) && $room->roomType) {
            $photos = $room->roomType->photos;
            if (is_string($photos)) {
                $photos = json_decode($photos, true);
            }
            if (is_array($photos) && count($photos) > 0) {
                $index = ((int) $room->id - 1) % count($photos);
                return $photos[array_keys($photos)[$index]] ?? $photos[0];
            }
        }

        // Last resort: Picsum seeded — dijamin selalu return image
        return "https://picsum.photos/seed/hotel-room-{$room->id}/".self::W.'/600';
    }

    /**
     * Foto untuk satu RoomType (cover image, untuk listing tipe kamar).
     */
    public static function forRoomType(RoomType $rt, int $index = 0): string
    {
        $code = $rt->code ?? '';
        $pool = self::POOLS[$code] ?? null;

        if ($pool) {
            $idx = $index % count($pool);
            return "https://images.unsplash.com/{$pool[$idx]}?auto=format&fit=crop&w=1600&q=".self::Q;
        }

        // Fallback ke kolom photos[]
        $photos = $rt->photos;
        if (is_string($photos)) {
            $photos = json_decode($photos, true);
        }
        if (is_array($photos) && count($photos) > 0) {
            return $photos[$index % count($photos)] ?? $photos[0];
        }

        return "https://picsum.photos/seed/room-type-{$rt->id}/1600/900";
    }

    private static function resolveTypeCode($room): string
    {
        if (isset($room->roomType) && $room->roomType?->code) {
            return $room->roomType->code;
        }
        if (isset($room->room_type_code)) {
            return (string) $room->room_type_code;
        }
        // Fallback: lookup
        if (! empty($room->room_type_id)) {
            $rt = RoomType::find($room->room_type_id);
            return $rt?->code ?? '';
        }
        return '';
    }
}
