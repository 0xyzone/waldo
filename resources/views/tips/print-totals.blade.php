<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tips Amount Summary - {{ ucfirst($report->month) }} {{ $report->year }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
        }

        .header-box {
            text-align: center;
            border: 2px solid #000;
            border-bottom: none;
            padding: 10px 0;
            background-color: #fff;
        }

        .header-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.5px;
        }

        table.totals-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            font-size: 13px;
        }

        table.totals-table th,
        table.totals-table td {
            border: 2px solid #000;
            padding: 9px 12px;
            vertical-align: middle;
        }

        table.totals-table th {
            background-color: #fff;
            font-weight: bold;
            text-align: left;
            font-size: 13.5px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .stat-label {
            font-size: 14px;
            font-weight: 500;
            text-align: left;
            padding-left: 15px;
        }

        .stat-value {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            padding-right: 15px;
        }

        .footer-signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            padding: 0 30px;
        }

        .sig-block {
            text-align: center;
            width: 180px;
        }

        .sig-line {
            border-top: 1px solid #000;
            margin-bottom: 6px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; display: flex; gap: 10px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            🖨️ Print Totals
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Close Window
        </button>
    </div>

    <div class="header-box">
        <h1 class="header-title">Tips Amount Summary - {{ ucfirst($report->month) }} {{ $report->year }}</h1>
    </div>

    <table class="totals-table">
        <thead>
            <tr>
                <th style="width: 55px;" class="text-center">S. No.</th>
                <th style="width: 200px;">Department</th>
                <th style="width: 130px;" class="text-right">To Distribute</th>
                <th colspan="2" style="background-color: #fff; border-left: 2px solid #000;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $index => $dept)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td class="text-left font-bold">{{ $dept['name'] }}</td>
                    <td class="text-right font-bold">{{ number_format($dept['amount'], 0, '', '') }}</td>

                    @if($index === $span1Start)
                        <td rowspan="{{ $span1Count }}" class="stat-label">Actual Total Collection</td>
                        <td rowspan="{{ $span1Count }}" class="stat-value">{{ number_format($actualTotalCollection, 0, '', '') }}</td>
                    @endif

                    @if($index === $span2Start)
                        <td rowspan="{{ $span2Count }}" class="stat-label">Adjustments / Left Outs</td>
                        <td rowspan="{{ $span2Count }}" class="stat-value">{{ number_format($adjustmentsLeftOuts, 0, '', '') }}</td>
                    @endif

                    @if($index === $span3Start)
                        <td rowspan="{{ $span3Count }}" class="stat-label">Total to Distribute</td>
                        <td rowspan="{{ $span3Count }}" class="stat-value">{{ number_format($totalToDistribute, 0, '', '') }}</td>
                    @endif

                    @if($index === $span4Start)
                        <td rowspan="{{ $span4Count }}" class="stat-label font-bold">Company Should Add</td>
                        <td rowspan="{{ $span4Count }}" class="stat-value font-bold">{{ number_format($companyShouldAdd, 0, '', '') }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: right; font-size: 11px; color: #555; border-top: 2px solid #000; padding-top: 6px;">
        Printed on: {{ now()->format('d M, Y h:i A') }}
    </div>

</body>
</html>
