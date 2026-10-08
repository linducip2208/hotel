<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\Folio;
use App\Services\Fo\FolioService;
use App\Services\Pdf\InvoicePdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FolioController extends Controller
{
    public function __construct(protected FolioService $svc) {}

    public function show(int $id)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->with(['charges', 'payments', 'reservation'])->findOrFail($id);

        return view('panel.fo.folios.show', compact('folio'));
    }

    public function addCharge(Request $request, int $id)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->findOrFail($id);
        if ($folio->status !== 'open') {
            return back()->withErrors(['charge' => 'Folio sudah ditutup — charge tidak dapat ditambahkan.']);
        }

        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(['room', 'fnb', 'minibar', 'laundry', 'spa', 'service_charge', 'pb1', 'ppn', 'addon', 'other', 'discount'])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'tax_code' => ['nullable', 'string', Rule::in(['PB1', 'PPN_OUT', 'PPH23'])],
            'is_taxable' => ['nullable', 'boolean'],
        ]);
        $data['posted_by_user_id'] = $request->user()?->id;
        $this->svc->postCharge($folio, $data);

        return back()->with('success', 'Charge berhasil diposting.');
    }

    public function addPayment(Request $request, int $id)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->findOrFail($id);
        if ($folio->status !== 'open') {
            return back()->withErrors(['payment' => 'Folio sudah ditutup — pembayaran tidak dapat ditambahkan.']);
        }
        $folio->recalculate();
        $outstanding = (float) $folio->balance;

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'method' => ['required', 'in:cash,card,qris,transfer,voucher,deposit,company_charge'],
            'reference_no' => ['nullable', 'string', 'max:100'],
        ]);

        // Overpayment guard: cash overpayment is change (returned at the desk),
        // so the recorded payment never exceeds the outstanding balance.
        // Only a genuine advance deposit (method=deposit) may exceed it.
        if ($data['method'] === 'deposit') {
            // Advance deposits may exceed the outstanding balance.
        } elseif ($outstanding <= 0.009) {
            return back()->withErrors(['payment' => 'Folio sudah lunas — tidak ada pembayaran yang perlu dicatat.']);
        } elseif ((float) $data['amount'] > $outstanding + 0.009) {
            return back()->withErrors([
                'payment' => 'Pembayaran melebihi saldo outstanding (Rp '.number_format($outstanding, 0, ',', '.').'). Kembalian tunai dikembalikan di kasir; gunakan metode deposit untuk simpanan.',
            ])->withInput();
        }

        // Duplicate-input guard: an identical payment recorded moments ago on
        // the same folio (double-click protection).
        $duplicate = $folio->payments()
            ->where('is_void', false)
            ->where('amount', (float) $data['amount'])
            ->where('method', $data['method'])
            ->when($data['reference_no'] ?? null, fn ($q, $ref) => $q->where('reference_no', $ref))
            ->when(blank($data['reference_no'] ?? null), fn ($q) => $q->whereNull('reference_no'))
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();
        if ($duplicate) {
            return back()->withErrors(['payment' => 'Pembayaran identik baru saja dicatat — kemungkinan double submit. Periksa daftar pembayaran.']);
        }

        $data['cashier_id'] = $request->user()?->id;
        $data['amount'] = $data['method'] === 'deposit'
            ? (float) $data['amount']
            : min((float) $data['amount'], $outstanding);
        $this->svc->postPayment($folio, $data);

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function addDiscount(Request $request, int $id)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->findOrFail($id);
        if ($folio->status !== 'open') {
            return back()->withErrors(['discount' => 'Folio sudah ditutup — diskon tidak dapat ditambahkan.']);
        }
        $folio->recalculate();

        $data = $request->validate(['amount' => 'required|numeric|min:0.01', 'reason' => 'required|string|max:255']);

        // Discount cannot exceed the outstanding balance.
        if ((float) $data['amount'] > (float) $folio->balance + 0.009) {
            return back()->withErrors(['discount' => 'Diskon melebihi saldo outstanding (Rp '.number_format(max(0, (float) $folio->balance), 0, ',', '.').').']);
        }

        $this->svc->applyDiscount($folio, (float) $data['amount'], $data['reason'], $request->user()?->id);

        return back()->with('success', 'Diskon berhasil diterapkan.');
    }

    public function transfer(Request $request, int $id)
    {
        $data = $request->validate([
            'to_folio_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $from = Folio::where('property_id', app('current_property')->id)->findOrFail($id);
        $to = Folio::where('property_id', app('current_property')->id)->findOrFail($data['to_folio_id']);

        if ($from->id === $to->id) {
            return back()->withErrors(['transfer' => 'Folio tujuan sama dengan folio asal.']);
        }
        if ($from->status !== 'open' || $to->status !== 'open') {
            return back()->withErrors(['transfer' => 'Transfer hanya dapat dilakukan antar folio yang masih terbuka.']);
        }
        $from->recalculate();
        if ((float) $data['amount'] > (float) $from->balance + 0.009) {
            return back()->withErrors(['transfer' => 'Jumlah transfer melebihi saldo folio asal.']);
        }

        $this->svc->transfer($from, $to, (float) $data['amount'], $data['description'] ?? 'Folio transfer');

        return back()->with('success', 'Transfer antar folio berhasil.');
    }

    public function settle(Request $request, int $id)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->findOrFail($id);
        if ($folio->status !== 'open') {
            return back()->withErrors(['balance' => 'Folio sudah ditutup sebelumnya.']);
        }

        // Always settle from fresh numbers.
        $folio->recalculate();
        if ((float) $folio->balance > 0) {
            return back()->withErrors(['balance' => 'Folio masih punya outstanding balance.']);
        }
        $folio->update(['status' => 'closed', 'closed_at' => now()]);

        return back()->with('success', 'Folio diselesaikan dan ditutup.');
    }

    public function invoice(int $id, Request $request)
    {
        $folio = Folio::where('property_id', app('current_property')->id)->with(['charges', 'payments', 'reservation.primaryGuest', 'property'])->findOrFail($id);
        if ($request->query('format') === 'pdf') {
            $pdf = app(InvoicePdfGenerator::class)->generate($folio);

            return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="invoice-'.$folio->folio_no.'.pdf"']);
        }

        return view('panel.fo.folios.invoice', compact('folio'));
    }
}
