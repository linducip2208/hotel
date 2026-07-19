<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cashier Shift Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 24px; }
        h1 { font-size: 20px; font-weight: 700; margin: 0 0 4px 0; color: #0f172a; }
        .meta { font-size: 11px; color: #64748b; margin-bottom: 20px; }
        .shift { border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 16px; padding: 16px; }
        .shift-header { font-weight: 700; font-size: 13px; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9; }
        .shift-header span { color: #64748b; font-weight: 400; font-size: 10px; }
        .shift-row { display: flex; gap: 16px; margin-bottom: 6px; }
        .shift-item { flex: 1; }
        .shift-label { font-size: 9px; text-transform: uppercase; color: #94a3b8; margin-bottom: 2px; }
        .shift-value { font-size: 12px; font-weight: 600; }
        .text-red { color: #dc2626; }
        .text-emerald { color: #059669; }
        .open-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 9px; font-weight: 700; }
        .open-badge.open { background: #fef3c7; color: #92400e; }
        .open-badge.closed { background: #d1fae5; color: #065f46; }
        .footer { margin-top: 24px; font-size: 9px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 12px; }
    </style>
</head>
<body>
    <h1>Cashier Shift Report</h1>
    <p class="meta">{{ \Carbon\Carbon::parse($from)->isoFormat('D MMM Y') }} — {{ \Carbon\Carbon::parse($to)->isoFormat('D MMM Y') }} · {{ config('app.name') }}</p>

    @foreach ($shifts as $s)
    <div class="shift">
        <div class="shift-header">
            {{ $s['cashier'] }}
            <span>· {{ \Carbon\Carbon::parse($s['opened_at'])->isoFormat('D MMM Y HH:mm') }}
            @if ($s['closed_at']) — {{ \Carbon\Carbon::parse($s['closed_at'])->format('HH:mm') }} @endif</span>
            <span class="open-badge {{ $s['is_open'] ? 'open' : 'closed' }}">{{ $s['is_open'] ? 'Open' : 'Closed' }}</span>
        </div>
        <div class="shift-row">
            <div class="shift-item">
                <div class="shift-label">Opening Float</div>
                <div class="shift-value">Rp {{ number_format($s['opening_float'], 0, ',', '.') }}</div>
            </div>
            <div class="shift-item">
                <div class="shift-label">Expected Cash</div>
                <div class="shift-value">Rp {{ number_format($s['expected_cash'], 0, ',', '.') }}</div>
            </div>
            <div class="shift-item">
                <div class="shift-label">Actual Cash</div>
                <div class="shift-value">Rp {{ number_format($s['actual_cash'], 0, ',', '.') }}</div>
            </div>
            <div class="shift-item">
                <div class="shift-label">Variance</div>
                <div class="shift-value @if($s['variance'] < 0) text-red @elseif($s['variance'] > 0) text-emerald @endif">
                    Rp {{ number_format($s['variance'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div class="footer">Generated on {{ now()->isoFormat('dddd, D MMMM Y HH:mm') }} · {{ config('app.name') }} Hotel Management</div>
</body>
</html>
