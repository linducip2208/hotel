<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Flash Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 24px; }
        h1 { font-size: 20px; font-weight: 700; margin: 0 0 4px 0; color: #0f172a; }
        .meta { font-size: 11px; color: #64748b; margin-bottom: 20px; }
        .kpi-grid { display: flex; gap: 12px; margin-bottom: 24px; }
        .kpi-card { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; text-align: center; }
        .kpi-label { font-size: 9px; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; margin-bottom: 4px; }
        .kpi-value { font-size: 16px; font-weight: 700; color: #4f46e5; }
        .section { margin-bottom: 18px; }
        .section-title { font-weight: 700; font-size: 13px; padding: 6px 0; border-bottom: 2px solid #4f46e5; margin-bottom: 8px; color: #0f172a; }
        .row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #f1f5f9; }
        .row-label { color: #64748b; font-size: 11px; }
        .row-value { font-weight: 600; font-size: 11px; }
        .row-value.total { font-weight: 700; color: #059669; }
        .footer { margin-top: 24px; font-size: 9px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 12px; }
    </style>
</head>
<body>
    <h1>Daily Flash Report</h1>
    <p class="meta">{{ \Carbon\Carbon::parse($to)->isoFormat('dddd, D MMMM Y') }} · {{ config('app.name') }}</p>

    @php
        $roomsKpi = $report->rooms_kpi ?? [];
        $revBreak = $report->revenue_breakdown ?? [];
    @endphp

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Occupancy %</div>
            <div class="kpi-value">{{ $roomsKpi['occupancy_pct'] ?? 0 }}%</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Room Revenue</div>
            <div class="kpi-value">Rp {{ number_format((float)($revBreak['Rooms'] ?? $revBreak['Room Revenue'] ?? 0), 0, ',', '.') }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Total Revenue</div>
            <div class="kpi-value">Rp {{ number_format($report->total_revenue ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    @if (!empty($report->rooms_kpi))
    <div class="section">
        <div class="section-title">Rooms KPI</div>
        @foreach ($report->rooms_kpi as $k => $v)
        <div class="row">
            <span class="row-label">{{ $k }}</span>
            <span class="row-value">{{ is_numeric($v) ? number_format((float)$v, 2, ',', '.') : $v }}</span>
        </div>
        @endforeach
    </div>
    @endif

    @if (!empty($report->revenue_breakdown))
    <div class="section">
        <div class="section-title">Revenue Breakdown</div>
        @foreach ($report->revenue_breakdown as $k => $v)
        <div class="row">
            <span class="row-label">{{ $k }}</span>
            <span class="row-value">Rp {{ number_format((float)$v, 0, ',', '.') }}</span>
        </div>
        @endforeach
        @if (isset($report->total_revenue))
        <div class="row"><span class="row-label">TOTAL</span><span class="row-value total">Rp {{ number_format($report->total_revenue, 0, ',', '.') }}</span></div>
        @endif
    </div>
    @endif

    @if (!empty($report->payment_breakdown))
    <div class="section">
        <div class="section-title">Payment Methods</div>
        @foreach ($report->payment_breakdown as $k => $v)
        <div class="row">
            <span class="row-label capitalize">{{ $k }}</span>
            <span class="row-value">Rp {{ number_format((float)$v, 0, ',', '.') }}</span>
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">Generated on {{ now()->isoFormat('dddd, D MMMM Y HH:mm') }} · {{ config('app.name') }} Hotel Management</div>
</body>
</html>
