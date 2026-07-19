<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Konfirmasi Booking</title>
<style>
  body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background: #f5f5f5; }
  .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
  .header { background: #1a3c5e; color: #fff; padding: 30px; text-align: center; }
  .header h1 { margin: 0; font-size: 22px; }
  .header p { margin: 8px 0 0; opacity: 0.8; font-size: 14px; }
  .body { padding: 30px; }
  .greeting { font-size: 16px; margin-bottom: 20px; }
  .booking-card { background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0; }
  .booking-card h3 { margin: 0 0 15px; color: #1a3c5e; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
  .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e9ecef; font-size: 14px; }
  .info-row:last-child { border-bottom: none; }
  .info-label { color: #666; }
  .info-value { font-weight: bold; }
  .ref-badge { background: #1a3c5e; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 13px; display: inline-block; }
  .btn { display: inline-block; background: #1a3c5e; color: #fff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-size: 14px; margin-top: 20px; }
  .total-row { background: #1a3c5e; color: #fff; border-radius: 6px; padding: 12px 16px; display: flex; justify-content: space-between; margin-top: 10px; font-size: 15px; }
  .footer { background: #f8f9fa; padding: 20px 30px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #e9ecef; }
  .footer a { color: #1a3c5e; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>✓ Booking Terkonfirmasi</h1>
    <p>{{ $reservation->property->name }}</p>
  </div>
  <div class="body">
    <div class="greeting">
      Halo <strong>{{ $reservation->primaryGuest?->first_name }}</strong>,<br>
      Booking Anda telah terkonfirmasi. Berikut detailnya:
    </div>

    <div class="booking-card">
      <h3>Detail Reservasi</h3>
      <div class="info-row">
        <span class="info-label">No. Booking</span>
        <span class="info-value"><span class="ref-badge">{{ $reservation->ref }}</span></span>
      </div>
      <div class="info-row">
        <span class="info-label">Hotel</span>
        <span class="info-value">{{ $reservation->property->name }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Check-in</span>
        <span class="info-value">{{ $reservation->check_in->format('d M Y') }} (14:00)</span>
      </div>
      <div class="info-row">
        <span class="info-label">Check-out</span>
        <span class="info-value">{{ $reservation->check_out->format('d M Y') }} (12:00)</span>
      </div>
      <div class="info-row">
        <span class="info-label">Durasi</span>
        <span class="info-value">{{ $reservation->nights }} malam</span>
      </div>
      <div class="info-row">
        <span class="info-label">Tamu</span>
        <span class="info-value">{{ $reservation->adults }} dewasa{{ $reservation->children ? ', '.$reservation->children.' anak' : '' }}</span>
      </div>
      @foreach($reservation->rooms as $room)
      <div class="info-row">
        <span class="info-label">Kamar</span>
        <span class="info-value">{{ $room->roomType?->name }}</span>
      </div>
      @endforeach
      <div class="total-row">
        <span>Total Pembayaran</span>
        <span>Rp {{ number_format($reservation->grand_total, 0, ',', '.') }}</span>
      </div>
    </div>

    <p style="font-size:13px;color:#666;">Kelola booking, lakukan pre-check in, atau lihat invoice melalui tautan di bawah:</p>
    <a href="{{ url('/portal/booking/'.$reservation->ref) }}" class="btn">Kelola Booking</a>

    @if($reservation->special_requests)
    <div style="margin-top:20px;padding:15px;background:#fff8e1;border-radius:6px;border-left:3px solid #ffa000;font-size:13px;">
      <strong>Permintaan Khusus Anda:</strong><br>
      {{ $reservation->special_requests }}
    </div>
    @endif
  </div>
  <div class="footer">
    Email ini dikirim otomatis. Jangan balas email ini.<br>
    <a href="{{ url('/portal/booking/'.$reservation->ref) }}">Kelola Booking</a> ·
    <a href="{{ url('/contact') }}">Hubungi Kami</a>
    <br><br>
    {{ $reservation->property->name }} · {{ $reservation->property->address_line1 ?? '' }}
  </div>
</div>
</body>
</html>
