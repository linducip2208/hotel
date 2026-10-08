<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ketersediaan Kamar</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        :root { --brand: #4f46e5; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #fff; color: #0f172a; padding: 16px; }
        .head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .title { font-size: 14px; font-weight: 700; }
        .nav a { text-decoration: none; color: #4f46e5; font-size: 12px; font-weight: 600; padding: 4px 8px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .nav a:hover { background: #eef2ff; }
        .grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
        .dow { text-align: center; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; padding: 4px 0; }
        .cell { border-radius: 10px; min-height: 48px; padding: 4px; text-align: center; font-size: 12px; border: 1px solid #f1f5f9; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .cell .num { font-weight: 600; }
        .cell .rate { font-size: 9px; color: #64748b; margin-top: 2px; }
        .avail { background: #f0fdf4; border-color: #bbf7d0; }
        .avail .num { color: #16a34a; }
        .limited { background: #fffbeb; border-color: #fde68a; }
        .limited .num { color: #d97706; }
        .soldout { background: #fff1f2; border-color: #fecdd3; }
        .soldout .num { color: #e11d48; text-decoration: line-through; }
        .past { opacity: 0.4; background: #f8fafc; }
        .empty { background: transparent; border: none; }
        .foot { margin-top: 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .legend { display: flex; gap: 10px; font-size: 10px; color: #64748b; }
        .legend span { display: inline-flex; align-items: center; gap: 4px; }
        .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .book { display: inline-block; background: var(--brand); color: #fff; text-decoration: none; font-size: 12px; font-weight: 700; padding: 8px 16px; border-radius: 10px; }
    </style>
</head>
<body>
    @php
        $monthName = \Carbon\Carbon::createFromDate($calendar['year'], $calendar['month'], 1)->translatedFormat('F Y');
        $dow = ['M', 'S', 'S', 'R', 'K', 'J', 'S'];
    @endphp

    <div class="head">
        <div class="title">{{ $property->name ?? 'Ketersediaan' }} <span style="color:#94a3b8;font-weight:500;">· {{ $monthName }}</span></div>
        <div class="nav">
            <a href="{{ request()->fullUrlWithQuery(['month' => $calendar['prev']]) }}">&larr;</a>
            <a href="{{ request()->fullUrlWithQuery(['month' => $calendar['next']]) }}">&rarr;</a>
        </div>
    </div>

    <div class="grid">
        @foreach($dow as $d)<div class="dow">{{ $d }}</div>@endforeach

        @for($i = 0; $i < $calendar['firstDay']; $i++)<div class="cell empty"></div>@endfor

        @foreach($calendar['days'] as $key => $day)
            @php
                $cls = 'avail';
                if ($day['in_past']) $cls = 'past';
                elseif ($day['sold_out']) $cls = 'soldout';
                elseif ($day['available'] > 0 && $day['available'] <= 2) $cls = 'limited';
            @endphp
            <div class="cell {{ $cls }}">
                <span class="num">{{ $day['date']->format('d') }}</span>
                @if($day['lowest_rate'] !== null)
                    <span class="rate">Rp{{ number_format($day['lowest_rate'], 0, ',', '.') }}</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="foot">
        <div class="legend">
            <span><i class="dot" style="background:#16a34a"></i>Tersedia</span>
            <span><i class="dot" style="background:#d97706"></i>Terbatas</span>
            <span><i class="dot" style="background:#e11d48"></i>Penuh</span>
        </div>
        <a class="book" href="{{ url('/booking') }}" target="_blank">Pesan Sekarang</a>
    </div>
</body>
</html>
