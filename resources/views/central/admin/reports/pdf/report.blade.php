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
        }
    </style>
</head>
<body>
    <div class="page-meta">
        <h1>{{ $title }}</h1>
        <div class="muted">Generated on {{ now()->format('Y | m | d') }} | {{ now()->format('h:i A') }}</div>
        <div class="muted">Period: {{ $periodLabel }}</div>
    </div>
    
    ... (rest of body remains same, omitted for brevity in thought, but I'll update the actual footer part) ...

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
                    {{ now()->format('Y | m | d') }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

