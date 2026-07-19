<?php

namespace App\Http\Controllers\Panel\Pos;

use App\Http\Controllers\Controller;
use App\Models\Folio;
use App\Models\Guest;
use App\Models\PosLaundryOrder;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LaundryController extends Controller
{
    public function index(Request $request)
    {
        $query = PosLaundryOrder::where('property_id', app('current_property')->id)
            ->with('room', 'guest');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->query('room_id'));
        }

        $orders = $query->latest()->paginate(25);

        $statusCounts = PosLaundryOrder::where('property_id', app('current_property')->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('panel.pos.laundry.index', compact('orders', 'statusCounts'));
    }

    public function create(Request $request)
    {
        $guests = [];
        $selectedRoom = null;

        if ($request->filled('room_id')) {
            $selectedRoom = Room::where('property_id', app('current_property')->id)
                ->findOrFail($request->query('room_id'));
            $reservation = Reservation::where('property_id', app('current_property')->id)
                ->where('status', 'checked_in')
                ->whereHas('rooms', fn ($q) => $q->where('room_id', $selectedRoom->id))
                ->with('primaryGuest')
                ->first();
            if ($reservation?->primaryGuest) {
                $guests = [$reservation->primaryGuest];
            }
        }

        $laundryItems = PosLaundryOrder::laundryItems();
        $rooms = Room::where('property_id', app('current_property')->id)
            ->where('is_active', true)
            ->orderBy('number')
            ->get();

        return view('panel.pos.laundry.create', compact('laundryItems', 'guests', 'selectedRoom', 'rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_id' => 'required|integer',
            'room_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|string',
            'items.*.service' => 'required|in:wash,dry_clean,iron',
            'items.*.qty' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $priceMap = [];
        foreach (PosLaundryOrder::laundryItems() as $li) {
            $priceMap[$li['key']] = $li;
        }

        $items = [];
        $total = 0;

        foreach ($data['items'] as $item) {
            $key = $item['item'];
            $service = $item['service'];
            $qty = (int) $item['qty'];

            $unitPrice = $priceMap[$key][$service] ?? 0;
            $lineTotal = $unitPrice * $qty;
            $total += $lineTotal;

            $items[] = [
                'item' => $key,
                'name' => $priceMap[$key]['name'] ?? $key,
                'service' => $service,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        $orderNumber = 'LDRY-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(3));

        $order = PosLaundryOrder::create([
            'property_id' => app('current_property')->id,
            'guest_id' => $data['guest_id'],
            'room_id' => $data['room_id'],
            'order_number' => $orderNumber,
            'status' => 'received',
            'items' => $items,
            'total_amount' => $total,
            'notes' => $data['notes'] ?? null,
            'received_by' => auth()->id(),
        ]);

        return redirect()->route('panel.pos.laundry.show', $order->id)
            ->with('success', "Laundry order {$orderNumber} created.");
    }

    public function show(int $id)
    {
        $order = PosLaundryOrder::where('property_id', app('current_property')->id)
            ->with('room', 'guest', 'receivedBy', 'deliveredBy')
            ->findOrFail($id);

        return view('panel.pos.laundry.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $order = PosLaundryOrder::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:received,washing,drying,folding,ready,delivered',
        ]);

        $order->update(['status' => $data['status']]);

        return back()->with('success', "Status updated to {$data['status']}.");
    }

    public function markDelivered(int $id)
    {
        $order = PosLaundryOrder::where('property_id', app('current_property')->id)
            ->where('payment_status', 'unpaid')
            ->findOrFail($id);

        // Find guest's folio to charge
        if ($order->guest_id && $order->total_amount > 0) {
            $folio = Folio::where('property_id', app('current_property')->id)
                ->whereHas('reservation', fn ($q) => $q->where('primary_guest_id', $order->guest_id)->where('status', 'checked_in'))
                ->first();

            if ($folio) {
                app(\App\Services\Fo\FolioService::class)->postCharge($folio, [
                    'description' => 'Laundry ' . $order->order_number,
                    'category' => 'laundry',
                    'amount' => $order->total_amount,
                    'tax_code' => 'PPN_OUT',
                    'is_taxable' => true,
                    'source_type' => 'pos_laundry',
                    'source_ref' => (string) $order->id,
                ]);
            }
        }

        $order->update([
            'status' => 'delivered',
            'payment_status' => 'charged_to_room',
            'delivered_by' => auth()->id(),
        ]);

        return back()->with('success', "Order {$order->order_number} delivered and charged to room.");
    }
}
