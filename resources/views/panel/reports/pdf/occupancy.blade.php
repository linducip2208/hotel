<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Occupancy Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 24px; }
        h1 { font-size: 20px; font-weight: 700; margin: 0 0 4px 0; color: #0f172a; }
        .meta { font-size: 11px; color: #64748b; margin-bottom: 20px; }
        .kpi-grid { display: flex; gap: 12px; margin-bottom: 24px; }
        .kpi-card { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; text-align: center; }
        .kpi-label { font-size: 9px; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; margin-bottom: 4px; }
        .kpi-value { font-size: 16px; font-weight: 700; color: #4f46e5; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #f1f5f9; text-align: right; padding: 8px 12px; font-size: 9px; text-transform: uppercase; letter-spacing: .4px; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        th:first-child { text-align: left; }
        td { padding: 7px 12px; text-align: right; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
        td:first-child { text-align: left; font-weight: 600; }
        .text-emerald { color: #059669; }
        .text-amber { color: #d97706; }
        .text-indigo { color: #4f46e5; }
        tfoot td { font-weight: 700; background: #f8fafc; border-top: 2px solid #cbd5e1; }
        .footer { margin-top: 24px; font-size: 9px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 12px; }
    </style>
</head>
<body>
    <h1>Occupancy Report</h1>
    <p class="meta">{{ \Carbon\Carbon::parse($from)->isoFormat('D MMM Y') }} — {{ \Carbon\Carbon::parse($to)->isoFormat('D MMM Y') }} · {{ config('app.name') }}</p>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Avg Occupancy</div>
            <div class="kpi-value">{{ $avgOcc }}%</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Rooms Sold</div>
            <div class="kpi-value">{{ number_format($totalSold) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Avg ADR</div>
            <div class="kpi-value">Rp {{ number_format($avgAdr, 0, ',', '.') }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Avg RevPAR</div>
            <div class="kpi-value">Rp {{ number_format($avgRevpar, 0, ',', '.') }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Revenue</div>
            <div class="kpi-value">Rp {{ number_format($totalRev, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Available</th>
                <th>Sold</th>
                <th>OCC %</th>
                <th>ADR</th>
                <th>RevPAR</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
            <tr>
                <td>{{ \Carbon\Carbon::parse($r['date'])->isoFormat('ddd, D MMM') }}</td>
                <td>{{ $r['available'] }}</td>
                <td>{{ $r['sold'] }}</td>
                <td class="@if ($r['occ_pct'] >= 80) text-emerald @elseif ($r['occ_pct'] >= 50) text-indigo @else text-amber @endif">{{ $r['occ_pct'] }}%</td>
                <td>{{ number_format($r['adr'], 0, ',', '.') }}</td>
                <td>{{ number_format($r['revpar'], 0, ',', '.') }}</td>
                <td>Rp {{ number_format($r['total_rev'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total / Avg</td>
                <td>{{ number_format($totalAvail) }}</td>
                <td>{{ number_format($totalSold) }}</td>
                <td class="@if ($avgOcc >= 80) text-emerald @elseif ($avgOcc >= 50) text-indigo @else text-amber @endif">{{ $avgOcc }}%</td>
                <td>{{ number_format($avgAdr, 0, ',', '.') }}</td>
                <td>{{ number_format($avgRevpar, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($totalRev, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Generated on {{ now()->isoFormat('dddd, D MMMM Y HH:mm') }} · {{ config('app.name') }} Hotel Management</div>
</body>
</html>
