<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Now</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:500,600,700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; background: transparent; display: flex; align-items: center; justify-content: center; min-height: 100px; padding: 16px; }
        .wrap { text-align: center; }
        .btn { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 12px 24px; border-radius: 9999px; box-shadow: 0 8px 20px rgba(79, 70, 229, .35); transition: transform .15s; }
        .btn:hover { transform: translateY(-1px); }
        .btn svg { width: 16px; height: 16px; }
        .sub { margin-top: 8px; font-size: 12px; color: #64748b; }
        .sub strong { color: #0f172a; }
    </style>
</head>
<body>
    <div class="wrap">
        <a class="btn" href="{{ route('booking.search') }}" target="_blank">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Book Now
        </a>
        @if($roomType)
            <p class="sub">di <strong>{{ $property->name ?? 'Hotel' }}</strong> · {{ $roomType->name }} dari <strong>Rp {{ number_format($roomType->base_rate, 0, ',', '.') }}</strong>/malam</p>
        @elseif($property)
            <p class="sub">di <strong>{{ $property->name }}</strong></p>
        @endif
    </div>
</body>
</html>
