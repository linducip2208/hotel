<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Reminder Check-in</title>
<style>
  body { font-family: Arial, sans-serif; color: #333; background: #f5f5f5; margin: 0; padding: 0; }
  .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; }
  .header { background: #2e7d32; color: #fff; padding: 30px; text-align: center; }
  .body { padding: 30px; }
  .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 14px; }
  .btn { display: inline-block; background: #2e7d32; color: #fff; padding: 12px 28px; text-decoration: none; border-radius: 6px; margin: 10px 5px 0 0; font-size: 14px; }
  .btn-outline { background: #fff; color: #2e7d32; border: 2px solid #2e7d32; }
  .checklist { background: #f1f8e9; border-radius: 8px; padding: 16px 20px; margin: 20px 0; }
  .checklist li { font-size: 13px; margin: 6px 0; }
  .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #999; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1 style="margin:0">🏨 Besok Saatnya Check-in!</h1>
    <p style="margin:8px 0 0;opacity:.8">{{ $reservation->property->name }}</p>
  </div>
  <div class="body">
    <p>Halo <strong>{{ $reservation->primaryGuest?->first_name }}</strong>,</p>
    <p>Kami mengingatkan bahwa check-in Anda dijadwalkan <strong>besok, {{ $reservation->check_in->format('d M Y') }}</strong>.</p>

    <div class="info-row"><span>No. Booking</span><strong>{{ $reservation->ref }}</strong></div>
    <div class="info-row"><span>Check-in</span><strong>{{ $reservation->check_in->format('d M Y') }} ab 14:00</strong></div>
    <div class="info-row"><span>Check-out</span><strong>{{ $reservation->check_out->format('d M Y') }} s.d. 12:00</strong></div>

    <div class="checklist">
      <strong>Persiapan sebelum tiba:</strong>
      <ul>
        <li>✅ Bawa KTP / Paspor asli untuk registrasi</li>
        <li>✅ Lakukan pre-check in online untuk proses lebih cepat</li>
        <li>✅ Parkir tersedia di {{ $reservation->property->name }}</li>
      </ul>
    </div>

    <a href="{{ url('/portal/pre-checkin/'.$reservation->ref) }}" class="btn">Pre-Check In Online</a>
    <a href="{{ url('/portal/booking/'.$reservation->ref) }}" class="btn btn-outline">Kelola Booking</a>
  </div>
  <div class="footer">
    {{ $reservation->property->name }} · {{ $reservation->property->phone ?? '' }}
  </div>
</div>
</body>
</html>
