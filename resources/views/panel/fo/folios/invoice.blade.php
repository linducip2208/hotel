<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $folio->folio_no }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1f2937; background: #f9fafb; padding: 0; }
        .page { max-width: 720px; margin: 32px auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.08); overflow: hidden; }
        .header { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #fff; padding: 32px 40px; }
        .header-inner { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
        .hotel-name { font-size: 22px; font-weight: 700; letter-spacing: -.3px; }
        .hotel-address { font-size: 12px; opacity: .75; margin-top: 4px; }
        .invoice-tag { text-align: right; }
        .invoice-tag h2 { font-size: 28px; font-weight: 800; letter-spacing: 1px; opacity: .9; }
        .invoice-tag p { font-size: 13px; opacity: .8; margin-top: 4px; }
        .body { padding: 32px 40px; }
        .guest-block { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1.5px solid #f3f4f6; }
        .guest-block dl dt { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; margin-bottom: 3px; }
        .guest-block dl dd { font-size: 14px; font-weight: 600; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        thead tr { background: #f9fafb; border-bottom: 2px solid #e5e7eb; }
        thead th { padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; }
        thead th.right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:hover { background: #fafafa; }
        tbody td { padding: 10px 12px; color: #374151; vertical-align: middle; }
        tbody td.right { text-align: right; font-family: monospace; }
        tfoot tr { border-top: 2px solid #e5e7eb; }
        tfoot td { padding: 10px 12px; }
        tfoot td.right { text-align: right; font-family: monospace; }
        .total-row td { background: #f0fdf4; font-weight: 700; font-size: 14px; color: #166534; }
        .balance-row td { background: #eff6ff; font-weight: 700; font-size: 15px; color: #1d4ed8; border-top: 2px solid #bfdbfe !important; }
        .footer { padding: 20px 40px 28px; background: #fafafa; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
        .footer p { font-size: 11px; color: #9ca3af; }
        .print-btn { display: inline-flex; align-items: center; gap: 6px; background: #4f46e5; color: #fff; border: none; border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .print-btn:hover { background: #4338ca; }
        @media print {
            body { background: #fff; }
            .page { max-width: 100%; margin: 0; border-radius: 0; box-shadow: none; }
            .print-btn { display: none; }
            .footer { background: #fff; }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-inner">
            <div>
                <div class="hotel-name">{{ $folio->property->name }}</div>
                <div class="hotel-address">{{ $folio->property->address_line1 }}, {{ $folio->property->city }}</div>
                @if ($folio->property->npwp)
                <div class="hotel-address" style="margin-top:3px">NPWP: {{ $folio->property->npwp }}</div>
                @endif
            </div>
            <div class="invoice-tag">
                <h2>INVOICE</h2>
                <p>{{ $folio->folio_no }}</p>
                <p>{{ now()->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <div class="body">
        <div class="guest-block">
            <dl>
                <dt>Guest</dt>
                <dd>{{ $folio->reservation?->primaryGuest?->full_name ?? $folio->guest?->full_name ?? '—' }}</dd>
            </dl>
            <dl>
                <dt>Reservation</dt>
                <dd>{{ $folio->reservation?->ref ?? '—' }}</dd>
            </dl>
            <dl>
                <dt>Check-in</dt>
                <dd>{{ $folio->reservation?->check_in?->format('d M Y') ?? '—' }}</dd>
            </dl>
            <dl>
                <dt>Check-out</dt>
                <dd>{{ $folio->reservation?->check_out?->format('d M Y') ?? '—' }}</dd>
            </dl>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="right">Amount</th>
                    <th class="right">Tax</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($folio->charges->where('is_void', false) as $c)
                <tr>
                    <td style="color:#6b7280;font-size:12px">{{ $c->charge_date->format('d M') }}</td>
                    <td>{{ $c->description }}</td>
                    <td class="right">Rp {{ number_format($c->amount, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($c->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align:right;color:#6b7280">Total Charges</td>
                    <td class="right total-row" colspan="2" style="border-radius:0">
                        <span style="font-size:15px">Rp {{ number_format($folio->total_charges, 0, ',', '.') }}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:right;color:#6b7280">Total Paid</td>
                    <td class="right" colspan="2" style="color:#059669">Rp {{ number_format($folio->total_payments, 0, ',', '.') }}</td>
                </tr>
                <tr class="balance-row">
                    <td colspan="2" style="text-align:right">Balance Due</td>
                    <td class="right" colspan="2">Rp {{ number_format($folio->balance, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <p>Thank you for your stay. Generated by {{ config('app.name') }}.</p>
        <button class="print-btn" onclick="window.print()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print Invoice
        </button>
    </div>
</div>
</body>
</html>
