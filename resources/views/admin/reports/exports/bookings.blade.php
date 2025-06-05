<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }} - {{ $date }}</title>
    <style>
        @page {
            margin: 20px 10px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            page-break-after: avoid;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #333;
            padding: 0;
        }
        .header .date {
            color: #666;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            page-break-inside: auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
            font-size: 9px;
            word-wrap: break-word;
            max-width: 150px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 8px;
            color: #999;
            padding: 5px 10px;
            border-top: 1px solid #eee;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="date">Generated on: {{ $date }}</div>
    </div>

    <table>
        <thead>
            <tr>
                @if(isset($data[0]))
                    @foreach(array_keys((array)$data[0]) as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                @if($index > 0 && $index % 30 == 0)
                    </tbody>
                    </table>
                    <div class="page-break"></div>
                    <table>
                        <thead>
                            <tr>
                                @foreach(array_keys((array)$data[0]) as $header)
                                    <th>{{ $header }}</th>
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

    <div class="footer">
        Fleet Management System - {{ config('app.name') }} | Page <span class="page-number"></span>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 15;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>
