<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Bagaimana Pengalaman Anda?</title>
<style>
  body { font-family: Arial, sans-serif; color: #333; background: #f5f5f5; margin: 0; padding: 0; }
  .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; }
  .header { background: #7b1fa2; color: #fff; padding: 30px; text-align: center; }
  .body { padding: 30px; text-align: center; }
  .stars { font-size: 40px; margin: 20px 0; }
  .btn { display: inline-block; background: #7b1fa2; color: #fff; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-size: 16px; margin-top: 20px; }
  .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #999; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1 style="margin:0">Terima Kasih atas Kunjungan Anda!</h1>
    <p style="margin:8px 0 0;opacity:.8">{{ $reservation->property->name }}</p>
  </div>
  <div class="body">
    <p>Halo <strong>{{ $reservation->primaryGuest?->first_name }}</strong>,</p>
    <p>Kami berharap Anda menikmati masa menginap di {{ $reservation->property->name }}
    ({{ $reservation->check_in->format('d') }}–{{ $reservation->check_out->format('d M Y') }}).</p>

    <div class="stars">⭐⭐⭐⭐⭐</div>

    <p style="font-size:15px">Pendapat Anda sangat berarti bagi kami.<br>
    Luangkan 1 menit untuk memberikan review?</p>

    <a href="{{ url('/portal/review/'.$reservation->ref) }}" class="btn">Beri Review Sekarang</a>

    <p style="font-size:12px;color:#999;margin-top:30px">
      Atau kunjungi kami lagi — cek promo terbaru di website kami.
    </p>
  </div>
  <div class="footer">
    {{ $reservation->property->name }} · No. Booking: {{ $reservation->ref }}
  </div>
</div>
</body>
</html>
