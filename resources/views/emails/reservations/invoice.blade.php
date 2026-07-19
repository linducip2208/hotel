<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $folio->folio_no }}</title>
<style>
  body { font-family: Arial, sans-serif; color: #333; background: #f5f5f5; margin: 0; padding: 0; }
  .container { max-width: 650px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; }
  .header { background: #1a3c5e; color: #fff; padding: 25px 30px; display: flex; justify-content: space-between; align-items: center; }
  .body { padding: 30px; }
  table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 13px; }
  th { background: #1a3c5e; color: #fff; padding: 8px 10px; text-align: left; }
  td { padding: 8px 10px; border-bottom: 1px solid #eee; }
  .total-row td { font-weight: bold; background: #f8f9fa; border-top: 2px solid #1a3c5e; }
  .badge { background: #e8f5e9; color: #2e7d32; padding: 3px 10px; border-radius: 12px; font-size: 12px; }
  .footer { background: #f8f9fa; padding: 20px 30px; font-size: 12px; color: #999; text-align: center; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div>
      <h2 style="margin:0;font-size:20px">INVOICE</h2>
      <p style="margin:4px 0 0;opacity:.8;font-size:13px">{{ $folio->folio_no }}</p>
    </div>
    <div style="text-align:right;font-size:13px">
      <div>{{ $folio->property->name }}</div>
      <div style="opacity:.8">{{ $folio->property->address_line1 ?? '' }}</div>
    </div>
  </div>
  <div class="body">
    <table style="margin-bottom:20px">
      <tr><td style="color:#666;border:none">Kepada</td><td style="border:none;font-weight:bold">{{ $folio->guest?->full_name ?? $folio->company?->name }}</td></tr>
      <tr><td style="color:#666;border:none">Tanggal</td><td style="border:none">{{ now()->format('d M Y') }}</td></tr>
      <tr><td style="color:#666;border:none">Status</td><td style="border:none"><span class="badge">{{ strtoupper($folio->status) }}</span></td></tr>
    </table>

    <table>
      <thead>
        <tr>
          <th>Keterangan</th>
          <th>Tgl</th>
          <th style="text-align:right">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        @foreach($folio->charges->where('is_void', false) as $charge)
        <tr>
          <td>{{ $charge->description }}</td>
          <td>{{ $charge->charge_date->format('d/m') }}</td>
          <td style="text-align:right">Rp {{ number_format($charge->amount + $charge->tax_amount, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
          <td colspan="2">TOTAL</td>
          <td style="text-align:right">Rp {{ number_format($folio->total_charges, 0, ',', '.') }}</td>
        </tr>
        <tr>
          <td colspan="2" style="color:#2e7d32">Dibayar</td>
          <td style="text-align:right;color:#2e7d32">Rp {{ number_format($folio->total_payments, 0, ',', '.') }}</td>
        </tr>
        @if($folio->balance > 0)
        <tr>
          <td colspan="2" style="color:#c62828;font-weight:bold">Sisa Tagihan</td>
          <td style="text-align:right;color:#c62828;font-weight:bold">Rp {{ number_format($folio->balance, 0, ',', '.') }}</td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>
  <div class="footer">
    Dokumen ini dibuat secara otomatis oleh sistem {{ $folio->property->name }}.
    Simpan email ini sebagai bukti pembayaran.
  </div>
</div>
</body>
</html>
