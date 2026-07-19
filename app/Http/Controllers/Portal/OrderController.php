<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Folio;
use App\Models\FolioPayment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $guest = $request->user('customer');

        $reservations = Reservation::where('primary_guest_id', $guest->id)
            ->latest()
            ->paginate(10);

        return view('portal.orders.index', compact('guest', 'reservations'));
    }

    public function show(Request $request, $id)
    {
        $guest = $request->user('customer');

        $reservation = Reservation::where('primary_guest_id', $guest->id)
            ->where('id', $id)
            ->with(['rooms.roomType', 'rooms.room', 'addons', 'folios.charges', 'folios.payments'])
            ->firstOrFail();

        return view('portal.orders.show', compact('guest', 'reservation'));
    }

    public function invoices(Request $request)
    {
        $guest = $request->user('customer');

        $folios = $guest->folios()
            ->with('reservation')
            ->latest()
            ->paginate(10);

        return view('portal.invoices.index', compact('guest', 'folios'));
    }

    public function invoiceShow(Request $request, $id)
    {
        $guest = $request->user('customer');

        $folio = $guest->folios()
            ->where('id', $id)
            ->with(['reservation', 'charges', 'payments'])
            ->firstOrFail();

        return view('portal.invoices.show', compact('guest', 'folio'));
    }

    public function storePayment(Request $request, $id)
    {
        $guest = $request->user('customer');

        $folio = $guest->folios()->where('id', $id)->firstOrFail();

        $request->validate([
            'amount'         => 'required|numeric|min:1|max:' . $folio->balance,
            'payment_method' => 'required|string|in:bank_transfer,ewallet,credit_card,cash',
            'proof'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'note'           => 'nullable|string|max:500',
        ]);

        $paymentData = [
            'folio_id'        => $folio->id,
            'property_id'     => $folio->property_id,
            'amount'          => $request->input('amount'),
            'payment_date'    => now()->toDateString(),
            'payment_method'  => $request->input('payment_method'),
            'reference_no'    => 'CUST-' . now()->format('YmdHis') . '-' . $folio->id,
            'gateway_payload' => [
                'uploaded_by'   => 'customer',
                'guest_id'      => $guest->id,
                'note'          => $request->input('note'),
            ],
        ];

        if ($request->hasFile('proof')) {
            $path = $request->file('proof')->store('payment_proofs', 'public');
            $paymentData['gateway_payload']['proof_path'] = $path;
        }

        FolioPayment::create($paymentData);
        $folio->recalculate();

        return back()->with('status', 'Bukti pembayaran berhasil diupload. Tim kami akan memverifikasi.');
    }
}
