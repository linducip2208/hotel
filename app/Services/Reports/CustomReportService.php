<?php

namespace App\Services\Reports;

use App\Models\CustomReport;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class CustomReportService
{
    protected array $availableWidgets = [
        'occupancy_chart' => ['name' => 'Grafik Okupansi', 'category' => 'revenue', 'type' => 'line_chart'],
        'revenue_chart' => ['name' => 'Grafik Revenue', 'category' => 'revenue', 'type' => 'bar_chart'],
        'channel_mix' => ['name' => 'Bauran Channel', 'category' => 'revenue', 'type' => 'doughnut_chart'],
        'guest_demographics' => ['name' => 'Demografi Tamu', 'category' => 'guests', 'type' => 'pie_chart'],
        'top_rooms' => ['name' => 'Kamar Terlaris', 'category' => 'operations', 'type' => 'table'],
        'housekeeping_stats' => ['name' => 'Statistik HK', 'category' => 'operations', 'type' => 'stats_cards'],
        'arrival_departure' => ['name' => 'Arrival/Departure', 'category' => 'operations', 'type' => 'stats_cards'],
        'revenue_breakdown' => ['name' => 'Breakdown Revenue', 'category' => 'finance', 'type' => 'bar_chart'],
        'payment_methods' => ['name' => 'Metode Pembayaran', 'category' => 'finance', 'type' => 'doughnut_chart'],
    ];

    public function getAvailableWidgets(): array
    {
        return $this->availableWidgets;
    }

    public function getWidgetData(Property $property, string $widgetKey, array $params = []): array
    {
        return match ($widgetKey) {
            'occupancy_chart' => $this->occupancyData($property),
            'revenue_chart' => $this->revenueData($property),
            'channel_mix' => $this->channelMixData($property),
            'guest_demographics' => $this->guestDemoData($property),
            'top_rooms' => $this->topRoomsData($property),
            'housekeeping_stats' => $this->hkStats($property),
            'arrival_departure' => $this->arrivalDepartureData($property),
            'revenue_breakdown' => $this->revenueBreakdownData($property),
            'payment_methods' => $this->paymentMethodData($property),
            default => [],
        };
    }

    protected function occupancyData(Property $property): array
    {
        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $days[] = $date;
        }

        $bookings = DB::table('reservation_rooms')
            ->join('reservations', 'reservations.id', '=', 'reservation_rooms.reservation_id')
            ->where('reservations.property_id', $property->id)
            ->whereBetween('reservations.check_in', [now()->subDays(29)->toDateString(), now()->toDateString()])
            ->selectRaw('reservations.check_in as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = [];
        $data = [];
        foreach ($days as $d) {
            $labels[] = \Carbon\Carbon::parse($d)->format('d M');
            $data[] = $bookings[$d] ?? 0;
        }
        return ['labels' => $labels, 'data' => $data];
    }

    protected function revenueData(Property $property): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }

        $revenue = DB::table('folio_payments')
            ->where('property_id', $property->id)
            ->where('paid_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $labels = [];
        $data = [];
        foreach ($months as $m) {
            $labels[] = \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y');
            $data[] = round($revenue[$m] ?? 0, 2);
        }
        return ['labels' => $labels, 'data' => $data];
    }

    protected function channelMixData(Property $property): array
    {
        $channels = DB::table('reservations')
            ->where('property_id', $property->id)
            ->where('created_at', '>=', now()->subMonths(3))
            ->selectRaw('source, count(*) as count')
            ->groupBy('source')
            ->get();

        $labels = [];
        $data = [];
        foreach ($channels as $c) {
            $labels[] = $c->source ?: 'Lainnya';
            $data[] = $c->count;
        }
        return ['labels' => $labels, 'data' => $data];
    }

    protected function guestDemoData(Property $property): array
    {
        $cities = DB::table('guests')
            ->where('property_id', $property->id)
            ->selectRaw('city, count(*) as count')
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        return [
            'labels' => $cities->pluck('city')->toArray(),
            'data' => $cities->pluck('count')->toArray(),
        ];
    }

    protected function topRoomsData(Property $property): array
    {
        $rooms = DB::table('reservation_rooms')
            ->join('rooms', 'rooms.id', '=', 'reservation_rooms.room_id')
            ->join('reservations', 'reservations.id', '=', 'reservation_rooms.reservation_id')
            ->where('reservations.property_id', $property->id)
            ->where('reservations.status', '!=', 'cancelled')
            ->selectRaw('rooms.room_number, count(*) as bookings')
            ->groupBy('rooms.room_number')
            ->orderByDesc('bookings')
            ->limit(10)
            ->get();

        return [
            'labels' => $rooms->pluck('room_number')->toArray(),
            'data' => $rooms->pluck('bookings')->toArray(),
        ];
    }

    protected function hkStats(Property $property): array
    {
        $done = \App\Models\HkTask::where('property_id', $property->id)
            ->where('status', 'done')->whereDate('completed_at', now())->count();
        $pending = \App\Models\HkTask::where('property_id', $property->id)
            ->where('status', 'pending')->count();
        $inProgress = \App\Models\HkTask::where('property_id', $property->id)
            ->where('status', 'in_progress')->count();

        return [
            'done_today' => $done,
            'pending' => $pending,
            'in_progress' => $inProgress,
        ];
    }

    protected function arrivalDepartureData(Property $property): array
    {
        $arrivals = DB::table('reservations')
            ->where('property_id', $property->id)
            ->whereDate('check_in', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        $departures = DB::table('reservations')
            ->where('property_id', $property->id)
            ->whereDate('check_out', now()->toDateString())
            ->where('status', 'checked_in')
            ->count();

        $inHouse = DB::table('reservations')
            ->where('property_id', $property->id)
            ->where('status', 'checked_in')
            ->count();

        return [
            'arrivals' => $arrivals,
            'departures' => $departures,
            'in_house' => $inHouse,
        ];
    }

    protected function revenueBreakdownData(Property $property): array
    {
        $charges = DB::table('folio_charges')
            ->where('property_id', $property->id)
            ->where('created_at', '>=', now()->subMonth())
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        return [
            'labels' => $charges->pluck('category')->toArray(),
            'data' => $charges->pluck('total')->map(fn($v) => round((float)$v, 2))->toArray(),
        ];
    }

    protected function paymentMethodData(Property $property): array
    {
        $methods = DB::table('folio_payments')
            ->where('property_id', $property->id)
            ->where('paid_at', '>=', now()->subMonth())
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->get();

        return [
            'labels' => $methods->pluck('method')->toArray(),
            'data' => $methods->pluck('total')->map(fn($v) => round((float)$v, 2))->toArray(),
        ];
    }
}
