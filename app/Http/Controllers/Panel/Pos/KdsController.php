<?php

namespace App\Http\Controllers\Panel\Pos;

use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use Illuminate\Http\Request;

class KdsController extends Controller
{
    public function display()
    {
        return view('panel.pos.kds');
    }

    public function orders(Request $request)
    {
        $propertyId = app('current_property')->id;

        $activeOrders = PosOrder::where('property_id', $propertyId)
            ->whereIn('status', ['open', 'sent', 'preparing'])
            ->with(['items' => function ($q) {
                $q->where('is_void', false);
            }, 'table', 'outlet'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($order) {
                $secondsSince = $order->created_at->diffInSeconds(now());
                $minutesSince = round($secondsSince / 60, 1);

                $priority = 'normal';
                if ($secondsSince > 900) $priority = 'overdue';       // >15 min
                elseif ($secondsSince > 600) $priority = 'warning';   // >10 min

                return [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'table' => $order->table?->label ?? 'N/A',
                    'outlet' => $order->outlet?->name ?? 'N/A',
                    'status' => $order->status,
                    'type' => $order->type,
                    'minutes_elapsed' => $minutesSince,
                    'seconds_elapsed' => $secondsSince,
                    'priority' => $priority,
                    'items' => $order->items->map(fn ($i) => [
                        'id' => $i->id,
                        'name' => $i->name,
                        'qty' => $i->qty,
                        'notes' => $i->modifiers['notes'] ?? null,
                        'sent_to_kitchen' => $i->sent_to_kitchen,
                    ])->toArray(),
                    'notes' => $order->notes,
                ];
            });

        $completedOrders = PosOrder::where('property_id', $propertyId)
            ->whereIn('status', ['served', 'settled'])
            ->with(['items' => function ($q) {
                $q->where('is_void', false);
            }, 'table'])
            ->where('updated_at', '>=', now()->subHours(2))
            ->latest('updated_at')
            ->take(20)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'table' => $order->table?->label ?? 'N/A',
                    'status' => $order->status,
                    'minutes_since_done' => $order->updated_at->diffInMinutes(now()),
                ];
            });

        return response()->json([
            'active' => $activeOrders,
            'completed' => $completedOrders,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function startPreparing(int $id)
    {
        $order = PosOrder::where('property_id', app('current_property')->id)->findOrFail($id);
        $order->update(['status' => 'preparing']);

        // Mark all items as sent to kitchen
        $order->items()->update([
            'sent_to_kitchen' => true,
            'sent_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function markReady(int $id)
    {
        $order = PosOrder::where('property_id', app('current_property')->id)->findOrFail($id);
        $order->update(['status' => 'served']);

        return response()->json(['ok' => true]);
    }

    public function recall(int $id)
    {
        $order = PosOrder::where('property_id', app('current_property')->id)->findOrFail($id);
        $order->update(['status' => 'preparing']);

        return response()->json(['ok' => true]);
    }
}
