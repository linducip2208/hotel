<?php

namespace App\Http\Controllers\Panel\Pos;

use App\Http\Controllers\Controller;
use App\Models\Folio;
use App\Models\PosMenuItem;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOutlet;
use App\Services\Accounting\PpnCalculator;
use App\Services\Fo\FolioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        $data = $request->validate([
            'outlet_id' => ['required', 'integer'],
        ]);

        // Scoped to the current property — never expose another property's menu.
        $items = PosMenuItem::whereHas('outlet', fn ($q) => $q
            ->where('property_id', app('current_property')->id))
            ->where('outlet_id', $data['outlet_id'])
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return response()->json($items);
    }

    public function createOrder(Request $request)
    {
        $data = $request->validate([
            'outlet_id' => ['required', 'integer', Rule::exists('pos_outlets', 'id')->where('property_id', app('current_property')->id)],
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

            // Service charge configurable per property (default 10%).
            $settings = app('current_property')->settings;
            $servicePct = is_array($settings) && isset($settings['pos_service_charge_pct'])
                ? max(0.0, min(100.0, (float) $settings['pos_service_charge_pct']))
                : 10.0;

            $service = round($subtotal * $servicePct / 100, 2);
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
        $data = $request->validate([
            'status' => ['required', 'in:open,preparing,served,settled,cancelled'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Settled orders are financially final — status flips would open a
        // double-settlement window.
        if ($order->status === 'settled' && $data['status'] !== 'settled') {
            return response()->json(['message' => 'Order yang sudah settled tidak dapat diubah. Gunakan void dengan otorisasi.'], 422);
        }

        $order->update($data);

        return response()->json($order);
    }

    public function settleOrder(Request $request, int $id)
    {
        $order = PosOrder::where('property_id', app('current_property')->id)->findOrFail($id);

        // Idempotency guard: a settled order must never be settled again
        // (double folio charge / double paid_total).
        if ($order->status === 'settled') {
            return response()->json(['message' => 'Order ini sudah diselesaikan.'], 422);
        }

        $data = $request->validate([
            'method' => 'required|in:cash,card,qris,charge_to_room',
            'amount' => 'nullable|numeric|min:0.01',
            'folio_id' => 'nullable|integer',
            'reference_no' => 'nullable|string|max:100',
        ]);

        if ($data['method'] === 'charge_to_room') {
            if (empty($data['folio_id'])) {
                return response()->json(['message' => 'Folio tujuan wajib dipilih untuk charge to room.'], 422);
            }
            $folio = Folio::where('property_id', app('current_property')->id)
                ->where('status', 'open')
                ->find($data['folio_id']);
            if (! $folio) {
                return response()->json(['message' => 'Folio tidak ditemukan atau sudah ditutup.'], 422);
            }

            app(FolioService::class)->postCharge($folio, [
                'description' => 'POS '.$order->outlet?->name.' '.$order->order_no,
                'category' => 'fnb',
                // NET amount only — PPN is added by the tax engine below.
                // Posting grand_total (which already contains PPN) with
                // is_taxable=true would tax the tax (double-charge).
                'amount' => (float) $order->subtotal + (float) $order->service_charge,
                'tax_code' => 'PPN_OUT',
                'is_taxable' => true,
                'source_type' => 'pos_order',
                'source_ref' => (string) $order->id,
            ]);
            $order->update(['status' => 'settled', 'paid_total' => $order->grand_total, 'folio_id' => $folio->id]);
        } else {
            // Cash payments can include change — cap the recorded payment at
            // the remaining balance so paid_total never exceeds grand_total.
            $remaining = round((float) $order->grand_total - (float) $order->paid_total, 2);
            if ($remaining <= 0) {
                return response()->json(['message' => 'Order ini sudah lunas.'], 422);
            }
            if (empty($data['amount'])) {
                return response()->json(['message' => 'Jumlah pembayaran wajib diisi.'], 422);
            }
            $recorded = min((float) $data['amount'], $remaining);

            $order->payments()->create([
                'method' => $data['method'],
                'amount' => $recorded,
                'reference_no' => $data['reference_no'] ?? null,
            ]);
            $order->update(['status' => 'settled', 'paid_total' => (float) $order->paid_total + $recorded]);
        }

        return response()->json($order->fresh());
    }
}
