<?php

namespace App\Http\Controllers\Panel\Pos;

use App\Http\Controllers\Controller;
use App\Models\Folio;
use App\Models\PosMenuItem;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOutlet;
use App\Services\Accounting\PpnCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function __construct(protected PpnCalculator $ppn) {}

    public function index()
    {
        $outlets = PosOutlet::where('property_id', app('current_property')->id)->where('is_active', true)->get();
        return view('panel.pos.index', compact('outlets'));
    }

    public function tables(int $id)
    {
        $outlet = PosOutlet::where('property_id', app('current_property')->id)->findOrFail($id);
        return view('panel.pos.tables', compact('outlet'));
    }

    public function menu(Request $request)
    {
        $items = PosMenuItem::where('outlet_id', $request->query('outlet_id'))
            ->where('is_available', true)->orderBy('name')->get();
        return response()->json($items);
    }

    public function createOrder(Request $request)
    {
        $data = $request->validate([
            'outlet_id' => 'required|integer',
            'table_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.modifiers' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($data) {
            $order = PosOrder::create([
                'outlet_id' => $data['outlet_id'],
                'property_id' => app('current_property')->id,
                'table_id' => $data['table_id'] ?? null,
                'order_no' => 'ORD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'status' => 'open',
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $i) {
                $menu = PosMenuItem::whereHas('outlet', fn ($q) => $q->where('property_id', app('current_property')->id))->findOrFail($i['menu_id']);
                $line = PosOrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menu->id,
                    'name' => $menu->name,
                    'unit_price' => $menu->price,
                    'qty' => $i['qty'],
                    'modifiers' => $i['modifiers'] ?? null,
                    'subtotal' => $menu->price * $i['qty'],
                ]);
                $subtotal += $line->subtotal;
            }

            $service = round($subtotal * 0.10, 2);
            $tax = $this->ppn->calculate($subtotal + $service);
            $order->update([
                'subtotal' => $subtotal,
                'service_charge' => $service,
                'tax_total' => $tax,
                'grand_total' => $subtotal + $service + $tax,
            ]);

            return response()->json($order->load('items'));
        });
    }

    public function updateOrder(Request $request, int $id)
    {
        $order = PosOrder::where('property_id', app('current_property')->id)->findOrFail($id);
        $order->update($request->only(['status', 'notes']));
        return response()->json($order);
    }

    public function settleOrder(Request $request, int $id)
    {
        $order = PosOrder::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'method' => 'required|in:cash,card,qris,charge_to_room',
            'amount' => 'required|numeric|min:0.01',
            'folio_id' => 'nullable|integer',
        ]);

        if ($data['method'] === 'charge_to_room' && $data['folio_id']) {
            $folio = Folio::where('property_id', app('current_property')->id)->findOrFail($data['folio_id']);
            app(\App\Services\Fo\FolioService::class)->postCharge($folio, [
                'description' => 'POS '.$order->outlet?->name.' '.$order->order_no,
                'category' => 'fnb',
                'amount' => $order->grand_total,
                'tax_code' => 'PPN_OUT',
                'is_taxable' => true,
                'source_type' => 'pos_order',
                'source_ref' => (string) $order->id,
            ]);
            $order->update(['status' => 'settled', 'paid_total' => $order->grand_total, 'folio_id' => $folio->id]);
        } else {
            $order->payments()->create($data);
            $order->update(['status' => 'settled', 'paid_total' => $order->paid_total + $data['amount']]);
        }
        return response()->json($order);
    }
}
