<?php

namespace App\Services\Sales;

use App\Models\EventBooking;
use App\Models\EventService;
use App\Models\EventType;
use App\Models\Guest;
use App\Models\Property;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventPlanningService
{
    public function getEventTypes(Property $property): array
    {
        return EventType::where('property_id', $property->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function createBooking(Property $property, array $data): EventBooking
    {
        $data['property_id'] = $property->id;
        $data['status'] = $data['status'] ?? 'inquiry';
        return EventBooking::create($data);
    }

    public function updateStatus(EventBooking $booking, string $status): EventBooking
    {
        $booking->update(['status' => $status]);
        return $booking->fresh();
    }

    public function addService(EventBooking $booking, array $data): EventService
    {
        return $booking->services()->create($data);
    }

    public function calculateTotal(EventBooking $booking): array
    {
        $servicesTotal = $booking->services()->sum('sell_price');
        $venueCost = 0;
        $total = $booking->total_quoted + $servicesTotal;
        $balance = $total - $booking->deposit_paid;

        return [
            'venue_base' => (float) $booking->total_quoted,
            'services_total' => (float) $servicesTotal,
            'grand_total' => (float) $total,
            'deposit_paid' => (float) $booking->deposit_paid,
            'balance_due' => (float) $balance,
        ];
    }

    public function getCalendar(Property $property, ?string $month = null): array
    {
        $start = $month
            ? Carbon::parse($month)->startOfMonth()
            : now()->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $bookings = EventBooking::where('property_id', $property->id)
            ->whereBetween('event_date', [$start, $end])
            ->with(['eventType', 'guest', 'venue', 'assignedUser'])
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        $events = $bookings->map(function ($b) {
            return [
                'id' => $b->id,
                'title' => $b->event_name,
                'start' => $b->event_date->format('Y-m-d') . 'T' . $b->start_time->format('H:i:s'),
                'end' => $b->event_date->format('Y-m-d') . 'T' . $b->end_time->format('H:i:s'),
                'status' => $b->status,
                'type' => $b->eventType?->name ?? '-',
                'guest' => $b->guest?->full_name ?? '-',
                'guests' => $b->expected_guests,
                'url' => route('panel.sales.events.show', $b->id),
                'backgroundColor' => $this->statusColor($b->status),
                'borderColor' => $this->statusColor($b->status),
            ];
        })->toArray();

        return [
            'events' => $events,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'bookings' => $bookings,
        ];
    }

    public function getUpcomingBookings(Property $property, int $limit = 10): array
    {
        return EventBooking::where('property_id', $property->id)
            ->where('event_date', '>=', now()->toDateString())
            ->with(['eventType', 'guest', 'venue'])
            ->orderBy('event_date')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    protected function statusColor(string $status): string
    {
        return match ($status) {
            'inquiry' => '#f59e0b',
            'tentative' => '#6366f1',
            'confirmed' => '#10b981',
            'cancelled' => '#ef4444',
            'completed' => '#6b7280',
            default => '#9ca3af',
        };
    }
}
