<?php

namespace App\Services\Hk;

use App\Models\LostFoundItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LostFoundService
{
    public function list(int $propertyId, array $filters = []): LengthAwarePaginator
    {
        return LostFoundItem::where('property_id', $propertyId)
            ->with(['room', 'foundByUser', 'claimedByGuest'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['category'] ?? null, fn ($q, $c) => $q->where('category', $c))
            ->when($filters['search'] ?? null, fn ($q, $t) => $q->where(function ($qq) use ($t) {
                $qq->where('name', 'like', "%{$t}%")
                   ->orWhere('item_number', 'like', "%{$t}%")
                   ->orWhere('location_found', 'like', "%{$t}%");
            }))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('found_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('found_at', '<=', $d))
            ->orderByDesc('found_at')
            ->paginate(25);
    }

    public function statusCounts(int $propertyId): array
    {
        return LostFoundItem::where('property_id', $propertyId)
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public function store(int $propertyId, array $data, int $userId): LostFoundItem
    {
        $data['property_id'] = $propertyId;
        $data['found_by_user_id'] = $userId;
        $data['item_number'] = LostFoundItem::generateItemNumber();

        if (!empty($data['photos_upload'])) {
            $paths = [];
            foreach ($data['photos_upload'] as $file) {
                $paths[] = $file->store('lost-found', 'public');
            }
            $data['photos'] = $paths;
        }
        unset($data['photos_upload']);

        return LostFoundItem::create($data);
    }

    public function update(int $id, int $propertyId, array $data): LostFoundItem
    {
        $item = LostFoundItem::where('property_id', $propertyId)->findOrFail($id);

        if (!empty($data['photos_upload'])) {
            $paths = $data['photos'] ?? [];
            foreach ($data['photos_upload'] as $file) {
                $paths[] = $file->store('lost-found', 'public');
            }
            $data['photos'] = $paths;
        }
        unset($data['photos_upload']);

        $item->update($data);
        return $item;
    }

    public function claim(int $id, int $propertyId, array $data): LostFoundItem
    {
        $item = LostFoundItem::where('property_id', $propertyId)->findOrFail($id);
        $item->update([
            'status'               => 'claimed',
            'claimed_at'           => now(),
            'claimed_by_guest_id'  => $data['claimed_by_guest_id'] ?? null,
            'claim_verified_by'    => $data['claim_verified_by'] ?? null,
        ]);
        return $item;
    }

    public function dispose(int $id, int $propertyId): LostFoundItem
    {
        $item = LostFoundItem::where('property_id', $propertyId)->findOrFail($id);
        $item->update(['status' => 'disposed']);
        return $item;
    }

    public function donate(int $id, int $propertyId): LostFoundItem
    {
        $item = LostFoundItem::where('property_id', $propertyId)->findOrFail($id);
        $item->update(['status' => 'donated']);
        return $item;
    }

    public function returnToOwner(int $id, int $propertyId): LostFoundItem
    {
        $item = LostFoundItem::where('property_id', $propertyId)->findOrFail($id);
        $item->update([
            'status'     => 'returned',
            'claimed_at' => now(),
        ]);
        return $item;
    }

    public function find(int $id, int $propertyId): LostFoundItem
    {
        return LostFoundItem::where('property_id', $propertyId)
            ->with(['room', 'foundByUser', 'claimedByGuest'])
            ->findOrFail($id);
    }

    public function getExpiringItems(int $propertyId, int $days = 7): array
    {
        $threshold = now()->addDays($days);
        return LostFoundItem::where('property_id', $propertyId)
            ->where('status', 'found')
            ->whereRaw("DATE_ADD(found_at, INTERVAL disposal_days DAY) <= ?", [$threshold])
            ->orderBy('found_at')
            ->get()
            ->toArray();
    }
}
