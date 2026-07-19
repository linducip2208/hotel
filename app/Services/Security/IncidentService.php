<?php

namespace App\Services\Security;

use App\Models\IncidentFollowup;
use App\Models\IncidentReport;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class IncidentService
{
    public function generateReportNumber(Property $property): string
    {
        $prefix = 'INC-' . date('Ymd');
        $last = IncidentReport::where('property_id', $property->id)
            ->where('report_number', 'like', $prefix . '%')
            ->orderBy('report_number', 'desc')
            ->first();

        if ($last) {
            $seq = (int) substr($last->report_number, -4) + 1;
        } else {
            $seq = 1;
        }
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function create(Property $property, array $data): IncidentReport
    {
        $data['property_id'] = $property->id;
        $data['report_number'] = $this->generateReportNumber($property);
        $data['status'] = 'open';
        return IncidentReport::create($data);
    }

    public function resolve(IncidentReport $report, array $data): IncidentReport
    {
        $report->update([
            'status' => $data['close_permanently'] ?? false ? 'closed' : 'resolved',
            'resolution' => $data['resolution'],
            'resolved_by_user_id' => auth()->id(),
            'resolved_at' => now(),
        ]);
        return $report->fresh();
    }

    public function addFollowup(Property $property, IncidentReport $report, array $data): IncidentFollowup
    {
        $data['property_id'] = $property->id;
        $data['incident_report_id'] = $report->id;
        return IncidentFollowup::create($data);
    }

    public function completeFollowup(IncidentFollowup $followup): IncidentFollowup
    {
        $followup->update(['completed_at' => now()]);
        return $followup->fresh();
    }

    public function getStats(Property $property): array
    {
        return [
            'open' => IncidentReport::where('property_id', $property->id)->where('status', 'open')->count(),
            'investigating' => IncidentReport::where('property_id', $property->id)->where('status', 'investigating')->count(),
            'resolved_this_month' => IncidentReport::where('property_id', $property->id)
                ->whereIn('status', ['resolved', 'closed'])
                ->whereMonth('resolved_at', now()->month)
                ->whereYear('resolved_at', now()->year)
                ->count(),
            'critical' => IncidentReport::where('property_id', $property->id)
                ->where('severity', 'critical')
                ->whereIn('status', ['open', 'investigating'])
                ->count(),
            'total' => IncidentReport::where('property_id', $property->id)->count(),
        ];
    }

    public function trendReport(Property $property, int $months = 6): array
    {
        $trend = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $trend[$month->format('Y-m')] = [
                'total' => IncidentReport::where('property_id', $property->id)
                    ->whereYear('incident_date', $month->year)
                    ->whereMonth('incident_date', $month->month)
                    ->count(),
                'resolved' => IncidentReport::where('property_id', $property->id)
                    ->whereIn('status', ['resolved', 'closed'])
                    ->whereYear('resolved_at', $month->year)
                    ->whereMonth('resolved_at', $month->month)
                    ->count(),
            ];
        }
        return $trend;
    }

    public function notifyManagement(IncidentReport $report): void
    {
        if (in_array($report->severity, ['high', 'critical'])) {
            // Notification integration placeholder
        }
    }
}
