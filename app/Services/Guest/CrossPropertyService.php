<?php

namespace App\Services\Guest;

use App\Models\Guest;
use App\Models\Property;

class CrossPropertyService
{
    public function searchAcrossProperties(string $search): array
    {
        return Guest::where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        })
        ->where('is_blacklisted', false)
        ->with('property')
        ->orderBy('first_name')
        ->limit(50)
        ->get()
        ->groupBy(fn($guest) => $guest->first_name . ' ' . ($guest->last_name ?? '') . ' | ' . ($guest->email ?? $guest->phone ?? ''))
        ->map(function ($group) {
            $first = $group->first();
            return [
                'name' => $first->first_name . ' ' . ($first->last_name ?? ''),
                'email' => $first->email,
                'phone' => $first->phone,
                'properties' => $group->pluck('property.name')->unique()->values()->toArray(),
                'total_stays' => $group->sum(fn($g) => $g->reservations()->where('status', 'checked_out')->count()),
                'last_visit' => $group->map(fn($g) => $g->reservations()->where('status', 'checked_out')->max('check_out'))->filter()->max(),
                'guest_ids' => $group->pluck('id')->toArray(),
            ];
        })->values()->toArray();
    }

    public function getUnifiedProfile(array $guestIds): array
    {
        $guests = Guest::whereIn('id', $guestIds)->with('property')->get();
        if ($guests->isEmpty()) return [];

        $first = $guests->first();

        $reservationIds = \App\Models\ReservationGuest::whereIn('guest_id', $guestIds)->pluck('reservation_id');
        $reservations = \App\Models\Reservation::whereIn('id', $reservationIds)
            ->where('status', 'checked_out')
            ->with('property')
            ->orderBy('check_out', 'desc')
            ->get();

        $totalSpent = $reservations->sum('total');
        $avgRating = \App\Models\Review::whereIn('reservation_id', $reservationIds)->avg('rating');

        $byProperty = $reservations->groupBy('property.name')->map(fn($rs) => [
            'stays' => $rs->count(),
            'total_spent' => $rs->sum('total'),
            'last_visit' => $rs->max('check_out'),
        ]);

        return [
            'name' => $first->first_name . ' ' . ($first->last_name ?? ''),
            'email' => $first->email,
            'phone' => $first->phone,
            'total_stays' => $reservations->count(),
            'total_spent' => (float) $totalSpent,
            'avg_rating' => $avgRating ? round((float) $avgRating, 1) : null,
            'last_visit' => $reservations->first()?->check_out,
            'by_property' => $byProperty->toArray(),
            'preferences' => $first->preferences,
            'is_vip' => $guests->contains('is_vip', true),
            'is_blacklisted' => $guests->contains('is_blacklisted', true),
        ];
    }

    public function getCrossPropertyHistory(array $guestIds): array
    {
        return $this->getUnifiedProfile($guestIds);
    }
}
