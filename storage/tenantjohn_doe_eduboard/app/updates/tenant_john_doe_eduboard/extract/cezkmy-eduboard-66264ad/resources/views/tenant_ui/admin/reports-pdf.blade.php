<!DOCTYPE html>
<html>
<head>
    <title>System Report - {{ tenant('school_name') ?? 'Buksu Eduboard' }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0d9488; padding-bottom: 10px; }
        .header h1 { margin: 0 0 5px 0; color: #111827; font-size: 20px; }
        .header p { margin: 0; color: #6b7280; font-size: 12px; }
        
        .section-title { font-size: 14px; color: #0d9488; margin-top: 20px; margin-bottom: 10px; font-weight: bold; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f9fafb; font-weight: bold; color: #4b5563; font-size: 11px; text-transform: uppercase; }
        td { font-size: 11px; color: #111827; }
        .text-right { text-align: right; }
        
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <div style="margin-bottom: 10px;">
            @php
                $logoPath = public_path('board.png');
                $canShowLogo = extension_loaded('gd') && file_exists($logoPath);
            @endphp
            @if($canShowLogo)
                <img src="{{ $logoPath }}" style="height: 40px; vertical-align: middle; margin-bottom: 5px;">
            @endif
        </div>
        <h1>{{ tenant('school_name') ?? 'Buksu Eduboard' }}</h1>
        <p>System Activity Report - Date Generated: {{ date('M d, Y H:i A') }}</p>
        <p>Period: {{ $year ?? 'All Years' }} {{ $month ? '- ' . date('F', mktime(0, 0, 0, $month, 1)) : '' }} {{ $day ? '- Day ' . $day : '' }}</p>
    </div>

    <div style="margin-bottom: 30px;">
        <table style="width: 100%; border: none; margin-bottom: 0;">
            <tr>
                <td style="border: none; padding: 0;">
                    <div style="background: #f0fdf4; padding: 15px; border-radius: 10px; border: 1px solid #dcfce7;">
                        <div style="font-size: 10px; font-weight: bold; color: #166534; text-transform: uppercase;">Total Posts</div>
                        <div style="font-size: 18px; font-weight: bold; color: #14532d;">{{ $announcements->count() }}</div>
                    </div>
                </td>
                <td style="border: none; padding: 0 10px;">
                    <div style="background: #eff6ff; padding: 15px; border-radius: 10px; border: 1px solid #dbeafe;">
                        <div style="font-size: 10px; font-weight: bold; color: #1e40af; text-transform: uppercase;">New Users</div>
                        <div style="font-size: 18px; font-weight: bold; color: #1e3a8a;">{{ $users->count() }}</div>
                    </div>
                </td>
                <td style="border: none; padding: 0 10px;">
                    <div style="background: #fff1f2; padding: 15px; border-radius: 10px; border: 1px solid #ffe4e6;">
                        <div style="font-size: 10px; font-weight: bold; color: #9f1239; text-transform: uppercase;">Reactions</div>
                        <div style="font-size: 18px; font-weight: bold; color: #881337;">{{ number_format($periodStats['reactions']) }}</div>
                    </div>
                </td>
                <td style="border: none; padding: 0;">
                    <div style="background: #fffbeb; padding: 15px; border-radius: 10px; border: 1px solid #fef3c7;">
                        <div style="font-size: 10px; font-weight: bold; color: #92400e; text-transform: uppercase;">Comments</div>
                        <div style="font-size: 18px; font-weight: bold; color: #78350f;">{{ number_format($periodStats['comments']) }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Announcements Overview</div>
    @if($announcements->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 35%">Title</th>
                <th style="width: 15%">Category</th>
                <th style="width: 20%">Author</th>
                <th style="width: 15%">Engagement</th>
                <th style="width: 15%" class="text-right">Date Posted</th>
            </tr>
        </thead>
        <tbody>
            @foreach($announcements as $ann)
            <tr>
                <td>{{ \Illuminate\Support\Str::limit($ann->title, 50) }}</td>
                <td>{{ $ann->category }}</td>
                <td>{{ $ann->postedBy->name ?? 'Deleted User' }}</td>
                <td>{{ $ann->reactions_count }} ❤️ | {{ $ann->comments_count }} 💬</td>
                <td class="text-right">{{ $ann->created_at->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No announcements found for this period.</p>
    @endif

    <div class="section-title" style="margin-top: 30px;">New Registrations (Total: {{ $users->count() }})</div>
    @if($users->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 30%">Name</th>
                <th style="width: 30%">Email</th>
                <th style="width: 20%">Role</th>
                <th style="width: 20%" class="text-right">Joined Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td style="text-transform: capitalize;">{{ $user->role }}</td>
                <td class="text-right">{{ $user->created_at->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No new users registered in this period.</p>
    @endif

    <div class="footer">
        Generated by {{ tenant('school_name') ?? 'Buksu Eduboard' }} System
        <script type="text/php">
            if (isset($pdf)) {
                $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
                $size = 9;
                $font = $fontMetrics->getFont("helvetica");
                $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                $x = ($pdf->get_width() - $width) / 2;
                $y = $pdf->get_height() - 35;
                $pdf->page_text($x, $y, $text, $font, $size, array(0.6, 0.6, 0.6));
            }
        </script>
    </div>
</body>
</html>
