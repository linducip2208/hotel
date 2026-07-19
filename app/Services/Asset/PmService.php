<?php

namespace App\Services\Asset;

use App\Models\Asset;
use App\Models\PmLog;
use App\Models\PmSchedule;
use App\Models\Property;

class PmService
{
    public function autoSchedule(Property $property): int
    {
        $assets = Asset::where('property_id', $property->id)->get();
        $created = 0;

        foreach ($assets as $asset) {
            $templates = match ($asset->category) {
                'ac' => [
                    ['task' => 'Bersihkan filter AC', 'freq' => 'monthly', 'days' => 30],
                    ['task' => 'Service AC menyeluruh', 'freq' => 'quarterly', 'days' => 90],
                    ['task' => 'Periksa refrigeran', 'freq' => 'biannual', 'days' => 180],
                ],
                'water_heater' => [
                    ['task' => 'Periksa elemen pemanas', 'freq' => 'monthly', 'days' => 30],
                    ['task' => 'Flush tangki', 'freq' => 'quarterly', 'days' => 90],
                ],
                'elevator' => [
                    ['task' => 'Inspeksi bulanan', 'freq' => 'monthly', 'days' => 30],
                    ['task' => 'Sertifikasi tahunan', 'freq' => 'annual', 'days' => 365],
                ],
                default => [
                    ['task' => 'Inspeksi umum', 'freq' => 'quarterly', 'days' => 90],
                ],
            };

            foreach ($templates as $tpl) {
                $exists = PmSchedule::where('property_id', $property->id)
                    ->where('asset_id', $asset->id)
                    ->where('task_name', $tpl['task'])->exists();
                if ($exists) continue;

                PmSchedule::create([
                    'property_id' => $property->id,
                    'asset_id' => $asset->id,
                    'task_name' => $tpl['task'],
                    'frequency' => $tpl['freq'],
                    'interval_days' => $tpl['days'],
                    'next_due_at' => now()->addDays($tpl['days']),
                    'is_active' => true,
                ]);
                $created++;
            }
        }

        return $created;
    }

    public function getDueTasks(Property $property): array
    {
        return PmSchedule::where('property_id', $property->id)
            ->where('is_active', true)
            ->where('next_due_at', '<=', now()->addDays(7))
            ->with(['asset', 'vendor'])
            ->orderBy('next_due_at')
            ->get()->toArray();
    }

    public function completeTask(PmSchedule $schedule, array $results, int $userId, ?float $cost = 0, ?string $notes = null): void
    {
        PmLog::create([
            'property_id' => $schedule->property_id,
            'pm_schedule_id' => $schedule->id,
            'performed_at' => now()->toDateString(),
            'checklist_results' => $results,
            'performed_by_user_id' => $userId,
            'cost' => $cost,
            'notes' => $notes,
        ]);

        $schedule->update([
            'last_done_at' => now()->toDateString(),
            'next_due_at' => now()->addDays($schedule->interval_days),
        ]);
    }

    public function getOverdueCount(Property $property): int
    {
        return PmSchedule::where('property_id', $property->id)
            ->where('is_active', true)
            ->where('next_due_at', '<', now())->count();
    }

    public function getHistory(Property $property, int $limit = 50): array
    {
        return PmLog::where('property_id', $property->id)
            ->with(['schedule.asset', 'performedBy'])
            ->orderByDesc('performed_at')
            ->limit($limit)
            ->get()->toArray();
    }
}
