<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $payment->invoice_number }}</title>
    <style>
        body {
            font-family: 'Inter', 'Helvetica', 'Arial', sans-serif;
            color: #1f2937;
            line-height: 1.5;
            margin: 0;
            padding: 40px;
            font-size: 13px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 2px solid #10b981;
            padding-bottom: 20px;
        }

        .logo-section h1 {
            color: #10b981;
            margin: 0;
            font-size: 28px;
            font-weight: 800;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-details h2 {
            margin: 0;
            font-size: 20px;
            color: #374151;
        }

        .grid {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }

        .col {
            display: table-cell;
            width: 50%;
        }

        .label {
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .value {
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        th {
            background-color: #f9fafb;
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-weight: 600;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
        }

        .total-section {
            margin-left: auto;
            width: 250px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .grand-total {
            border-top: 2px solid #e5e7eb;
            margin-top: 10px;
            padding-top: 10px;
            font-weight: 800;
            font-size: 18px;
            color: #10b981;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            color: #9ca3af;
            font-size: 11px;
            border-top: 1px solid #f3f4f6;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div style="width: 100%; display: table; margin-bottom: 30px;">
        <div style="display: table-cell; vertical-align: top;">
            <div style="color: #10b981; font-size: 24px; font-weight: 800;">EduBoard</div>
            <div style="color: #6b7280;">Modern School Management SaaS</div>
        </div>
        <div style="display: table-cell; text-align: right; vertical-align: top;">
            <div style="font-size: 20px; font-weight: 700;">INVOICE</div>
            <div style="color: #6b7280;">#{{ $payment->invoice_number }}</div>
        </div>
    </div>

    <div style="width: 100%; display: table; margin-bottom: 40px;">
        <div style="display: table-cell; width: 50%;">
            <div style="color: #6b7280; font-size: 11px; text-transform: uppercase; margin-bottom: 5px;">Billed To</div>
            <div style="font-weight: 700; font-size: 15px;">{{ $payment->tenant->school_name ?? 'School Tenant' }}</div>
            <div style="color: #4b5563;">{{ $payment->tenant->owner->email ?? '' }}</div>
            <div style="color: #4b5563;">ID: {{ $payment->tenant->id }}</div>
        </div>
        <div style="display: table-cell; width: 50%; text-align: right;">
            <div style="color: #6b7280; font-size: 11px; text-transform: uppercase; margin-bottom: 5px;">Payment Details</div>
            <div style="color: #4b5563;">Date: {{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('F d, Y') : now()->format('F d, Y') }}</div>
            <div style="color: #4b5563;">Status: <span style="color: #10b981; font-weight: 600;">{{ strtoupper($payment->payment_status) }}</span></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div style="font-weight: 600;">EduBoard {{ $payment->plan }} Subscription</div>
                    <div style="color: #6b7280; font-size: 12px;">Monthly subscription for premium features and support.</div>
                </td>
                <td style="text-align: right; font-weight: 600;">₱{{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="width: 100%; display: table;">
        <div style="display: table-cell; width: 60%;">
            <div style="color: #6b7280; font-size: 11px; text-transform: uppercase; margin-bottom: 5px;">Note</div>
            <div style="color: #4b5563; font-style: italic;">Thank you for choosing EduBoard. Your payment helps us continue improving our platform for schools worldwide.</div>
        </div>
        <div style="display: table-cell; width: 40%;">
            <div style="width: 100%; display: table; border-top: 2px solid #10b981; padding-top: 15px;">
                <div style="display: table-cell; font-weight: 800; font-size: 18px;">Total</div>
                <div style="display: table-cell; text-align: right; font-weight: 800; font-size: 18px; color: #10b981;">₱{{ number_format($payment->amount, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} EduBoard SaaS. All rights reserved.</p>
        <p>This is a computer-generated invoice and does not require a signature.</p>
    </div>
</body>
</html>
