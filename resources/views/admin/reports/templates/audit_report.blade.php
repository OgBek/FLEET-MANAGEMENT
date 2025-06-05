<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }} - {{ $date->format('Y-m-d') }}</title>
    <style>
        @page {
            margin: 20px 25px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            page-break-after: avoid;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 16pt;
            color: #2c3e50;
            font-weight: bold;
        }
        .header .subtitle {
            font-size: 10pt;
            color: #7f8c8d;
            margin: 5px 0;
        }
        .header .date {
            font-size: 9pt;
            color: #7f8c8d;
            margin: 5px 0 0 0;
        }
        .report-info {
            margin-bottom: 15px;
            font-size: 9pt;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #2c3e50;
        }
        .report-info div {
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 20px 0;
            page-break-inside: auto;
            font-size: 8pt;
        }
        th {
            background-color: #2c3e50;
            color: #fff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #ddd;
            text-transform: uppercase;
            font-size: 7.5pt;
        }
        td {
            padding: 5px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #7f8c8d;
            border-top: 1px solid #e0e0e0;
            padding: 5px 0;
            background: #fff;
        }
        .page-number:after {
            content: counter(page);
        }
        .signature {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            width: 60%;
            margin-left: auto;
            margin-right: 0;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 200px;
            margin: 40px 0 5px 0;
        }
        .signature-label {
            font-size: 8pt;
            color: #7f8c8d;
        }
        .summary {
            margin: 15px 0;
            padding: 10px;
            background-color: #f0f7ff;
            border-left: 4px solid #3498db;
            border-radius: 4px;
        }
        .summary h3 {
            margin: 0 0 8px 0;
            color: #2c3e50;
            font-size: 10pt;
        }
        .summary p {
            margin: 5px 0;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'Fleet Management System') }}</h1>
        <div class="subtitle">{{ $title }}</div>
        <div class="date">Report Generated: {{ $date->format('F j, Y') }}</div>
    </div>

    <div class="report-info">
        <div><strong>Report Period:</strong> 
            @if(isset($filters['start_date']) && isset($filters['end_date']))
                {{ \Carbon\Carbon::parse($filters['start_date'])->format('M j, Y') }} to {{ \Carbon\Carbon::parse($filters['end_date'])->format('M j, Y') }}
            @else
                {{ $date->format('F Y') }}
            @endif
        </div>
        @if(isset($filters['status']) && $filters['status'])
        <div><strong>Status Filter:</strong> {{ ucfirst($filters['status']) }}</div>
        @endif
        @if(isset($filters['vehicle_id']) && $filters['vehicle_id'])
        <div><strong>Vehicle:</strong> {{ $filters['vehicle'] ?? 'N/A' }}</div>
        @endif
        @if(isset($filters['department_id']) && $filters['department_id'])
        <div><strong>Department:</strong> {{ $filters['department'] ?? 'N/A' }}</div>
        @endif
        <div><strong>Total Records:</strong> {{ number_format(count($data)) }}</div>
    </div>

    @if(isset($summary) && is_array($summary))
    <div class="summary">
        <h3>Report Summary</h3>
        @foreach($summary as $key => $value)
            <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</p>
        @endforeach
    </div>
    @endif

    <table>
        <thead>
            <tr>
                @if(isset($data[0]))
                    @foreach(array_keys((array)$data[0]) as $header)
                        <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                @if($index > 0 && $index % 30 == 0)
                    </tbody>
                    </table>
                    <div class="page-break">
                        <div class="header">
                            <h1>{{ config('app.name', 'Fleet Management System') }}</h1>
                            <div class="subtitle">{{ $title }} (Continued)</div>
                            <div class="date">Page {{ ceil(($index + 1) / 30) + 1 }}</div>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                @foreach(array_keys((array)$data[0]) as $header)
                                    <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                @endif
                <tr>
                    @foreach((array)$row as $value)
                        <td>{!! nl2br(e($value)) !!}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <div class="signature-line"></div>
        <div class="signature-label">Authorized Signature / Date</div>
    </div>

    <div class="footer">
        {{ config('app.name') }} | {{ $date->format('m/d/Y h:i A') }} | Page <span class="page-number"></span> of <span class="page-count"></span>
        <div style="font-size: 7pt; margin-top: 2px;">
            This is a system-generated report. For any discrepancies, please contact the system administrator.
        </div>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of ";
            $size = 8;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = ($pdf->get_width() - $width - 10) / 2;
            $y = $pdf->get_height() - 15;
            $pdf->page_text($x, $y, $text, $font, $size);
            
            // Add total pages
            $text = "{PAGE_COUNT}";
            $pdf->page_text($x + $width, $y, $text, $font, $size);
        }
    </script>
</body>
</html>
