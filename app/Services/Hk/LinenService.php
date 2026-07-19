<?php

namespace App\Services\Hk;

use App\Models\LaundryTransaction;
use App\Models\LinenCategory;
use App\Models\UniformAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LinenService
{
    public function getStockLevels(int $propertyId): Collection
    {
        return LinenCategory::where('property_id', $propertyId)
            ->withCount('transactions')
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    public function recordTransaction(array $data): LaundryTransaction
    {
        return DB::transaction(function () use ($data) {
            $category = LinenCategory::findOrFail($data['linen_category_id']);
            $quantity = (int) $data['quantity'];

            switch ($data['transaction_type']) {
                case 'issue':
                    if ($category->current_stock < $quantity) {
                        throw new \RuntimeException('Stok tidak mencukupi. Tersedia: ' . $category->current_stock);
                    }
                    $category->decrement('current_stock', $quantity);
                    break;
                case 'return':
                    $category->increment('current_stock', $quantity);
                    break;
                case 'wash':
                    break;
                case 'discard':
                    if ($category->current_stock < $quantity) {
                        throw new \RuntimeException('Stok tidak mencukupi untuk discard.');
                    }
                    $category->decrement('current_stock', $quantity);
                    $category->increment('damaged_count', $quantity);
                    break;
                case 'audit':
                    $category->update(['current_stock' => $quantity]);
                    break;
            }

            $category->save();

            return LaundryTransaction::create([
                'property_id' => $category->property_id,
                'linen_category_id' => $category->id,
                'room_id' => $data['room_id'] ?? null,
                'transaction_type' => $data['transaction_type'],
                'quantity' => $quantity,
                'location_from' => $data['location_from'] ?? null,
                'location_to' => $data['location_to'] ?? null,
                'performed_by_user_id' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function getRoomUsage(int $propertyId, int $roomId): Collection
    {
        return LaundryTransaction::where('property_id', $propertyId)
            ->where('room_id', $roomId)
            ->with('linenCategory')
            ->latest()
            ->limit(50)
            ->get();
    }

    public function getParAlerts(int $propertyId): Collection
    {
        return LinenCategory::where('property_id', $propertyId)
            ->where('par_level', '>', 0)
            ->get()
            ->filter(fn ($c) => $c->current_stock < $c->par_level);
    }

    public function getRecentTransactions(int $propertyId, int $limit = 20): Collection
    {
        return LaundryTransaction::where('property_id', $propertyId)
            ->with(['linenCategory', 'performedBy'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function assignUniform(array $data): UniformAssignment
    {
        return UniformAssignment::create([
            'property_id' => $data['property_id'],
            'employee_id' => $data['employee_id'],
            'linen_category_id' => $data['linen_category_id'],
            'quantity_assigned' => $data['quantity_assigned'] ?? 1,
            'assigned_date' => $data['assigned_date'] ?? today()->toDateString(),
            'condition' => $data['condition'] ?? 'baik',
        ]);
    }

    public function returnUniform(int $assignmentId): UniformAssignment
    {
        $assignment = UniformAssignment::findOrFail($assignmentId);
        $assignment->update([
            'returned_date' => today()->toDateString(),
            'condition' => request()->input('condition', $assignment->condition),
        ]);
        return $assignment;
    }

    public function getUniforms(int $propertyId): Collection
    {
        return UniformAssignment::where('property_id', $propertyId)
            ->with(['employee', 'linenCategory'])
            ->latest('assigned_date')
            ->get();
    }
}
