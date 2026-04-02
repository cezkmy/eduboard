<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 60px 25px 65px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .muted { color: #6b7280; font-size: 11px; }

        h1 {
            font-size: 18px;
            margin: 0 0 6px;
        }

        hr { border: none; border-top: 1px solid #e5e7eb; margin: 14px 0; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 7px 8px;
            font-size: 11px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            text-align: left;
        }

        .header {
            position: fixed;
            top: -40px;
            left: 0;
            right: 0;
            height: 30px;
            border-bottom: 2px solid #10b981;
            padding-bottom: 10px;
        }
        
        .header table { border: none; }
        .header td { border: none; padding: 0; vertical-align: middle; }

        .logo-text {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            margin-left: 8px;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 35px;
            padding: 10px 0;
            border-top: 1.5px solid #10b981;
            font-size: 10px;
            color: #4b5563;
        }

        .footer table { border: none; }
        .footer td { border: none; padding: 0; }

        .page-number:before { content: counter(page); }
        .page-count:before { content: counter(pages); }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .page-meta {
            margin-bottom: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td style="width: 50%;">
                    @if(file_exists(public_path('board.png')))
                        <img src="{{ public_path('board.png') }}" style="height: 24px; vertical-align: middle;">
                    @endif
                    <span class="logo-text">EduBoard</span>
                </td>
                <td style="width: 50%;" class="text-right muted">
                    {{ $title }}
                </td>
            </tr>
        </table>
    </div>

    <div class="page-meta">
        <h1>{{ $title }}</h1>
        <div class="muted">Generated on {{ now()->format('Y | m | d') }} &nbsp;|&nbsp; {{ now()->format('h:i A') }}</div>
        <div class="muted" style="margin-top: 4px;">Period: {{ $periodLabel }}</div>
    </div>

    @if($kind === 'revenue')
        <div class="page-meta">
            <div class="muted" style="margin-bottom: 8px;">
                Total Revenue: <strong>₱{{ number_format($summary['totalRevenue'] ?? 0, 2) }}</strong>
            </div>

            <div class="muted">
                This Month Revenue: <strong>₱{{ number_format($summary['monthlyRevenueCurrent'] ?? 0, 2) }}</strong>
            </div>

            <div class="muted" style="margin-top: 6px;">
                Active Tenants: <strong>{{ $summary['activeTenants'] ?? 0 }}</strong>
            </div>
        </div>

        <table style="margin-top: 14px;">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Revenue (Paid)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($months as $idx => $month)
                    <tr>
                        <td>{{ $month }}</td>
                        <td>₱{{ number_format($revenueTrend[$idx] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @elseif($kind === 'tenant')
        <div class="page-meta">
            <div class="muted" style="margin-bottom: 8px;">
                Total Tenants: <strong>{{ $summary['totalTenants'] ?? 0 }}</strong>
            </div>

            <div class="muted">
                Active Tenants: <strong>{{ $summary['activeTenants'] ?? 0 }}</strong>
            </div>
        </div>

        <table style="margin-top: 14px;">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>New Tenants</th>
                </tr>
            </thead>
            <tbody>
                @foreach($months as $idx => $month)
                    <tr>
                        <td>{{ $month }}</td>
                        <td>{{ $tenantTrend[$idx] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @else
        <div class="page-meta">
            <div class="muted" style="margin-bottom: 8px;">
                Total Collected: <strong>₱{{ number_format($summary['totalAmount'] ?? 0, 2) }}</strong>
            </div>
            <div class="muted">
                Report Type: <strong>{{ strtoupper($paymentsType ?? '') }}</strong>
            </div>
        </div>

        <table style="margin-top: 14px;">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>School</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Paid At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment->invoice_number ?? 'N/A' }}</td>
                        <td>{{ $payment->tenant->school_name ?? 'N/A' }}</td>
                        <td>{{ $payment->plan ?? 'N/A' }}</td>
                        <td>₱{{ number_format((float)($payment->amount ?? 0), 2) }}</td>
                        <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('Y | m | d h:i A') : 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <table width="100%">
            <tr>
                <td style="width: 33%;">
                    {{ config('app.name', 'EduBoard') }}
                </td>
                <td style="width: 34%;" class="text-center">
                    Page <span class="page-number"></span> | <span class="page-count"></span>
                </td>
                <td style="width: 33%;" class="text-right">
                    @php
                        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                        $barcode = base64_encode($generator->getBarcode(now()->format('YmdHi'), $generator::TYPE_CODE_128));
                    @endphp
                    <img src="data:image/png;base64,{{ $barcode }}" style="height: 18px; vertical-align: middle;" alt="barcode">
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
