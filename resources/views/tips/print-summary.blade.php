<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tips Distribution Summary - {{ ucfirst($report->month) }} {{ $report->year }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
        }

        .header-box {
            text-align: center;
            border: 2px solid #000;
            border-bottom: none;
            padding: 8px 0;
            background-color: #fff;
        }

        .header-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.5px;
        }

        table.summary-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            font-size: 12px;
        }

        table.summary-table th,
        table.summary-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
        }

        table.summary-table th {
            background-color: #fff;
            font-weight: bold;
            font-style: italic;
            text-align: center;
            font-size: 12px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .text-red { color: #cc0000; font-weight: bold; }

        .footer-signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            padding: 0 40px;
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
            🖨️ Print Summary
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Close Window
        </button>
    </div>

    <div class="header-box">
        <h1 class="header-title">Tips Distribution Summary - {{ ucfirst($report->month) }} {{ $report->year }}</h1>
    </div>

    <table class="summary-table">
        <thead>
            <tr>
                <th style="width: 38px;">S.N</th>
                <th style="width: 220px;" class="text-center">Department</th>
                <th style="width: 85px;">Chips</th>
                <th style="width: 85px;">Cash</th>
                <th style="width: 125px;">Total Amount in<br>{{ ucfirst($report->month) }} {{ $report->year }}</th>
                <th style="width: 110px;">Back Office<br>contribution</th>
                <th style="width: 90px;"></th>
                <th style="width: 135px;">Remaining amount<br>(After Deduction)</th>
                <th style="width: 95px;">Round up</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left font-bold">{{ $row['name'] }}</td>
                    <td class="text-right">{{ number_format($row['chips'], 0) }}</td>
                    <td class="text-right">{{ number_format($row['cash'], 0) }}</td>
                    <td class="text-right font-bold">{{ number_format($row['total'], 0) }}</td>
                    <td class="text-right {{ $row['bo'] < 0 ? 'text-red' : ($row['bo'] > 0 ? 'font-bold' : '') }}">
                        {{ $row['bo'] != 0 ? number_format($row['bo'], 0) : '' }}
                    </td>
                    <td class="text-right {{ $row['transfer'] < 0 ? 'text-red' : ($row['transfer'] > 0 ? 'font-bold' : '') }}">
                        {{ $row['transfer'] != 0 ? number_format($row['transfer'], 0) : '' }}
                    </td>
                    <td class="text-right font-bold">{{ number_format($row['remaining'], 0) }}</td>
                    <td class="text-right font-bold">{{ number_format($row['round_up'], 0) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #fff; font-weight: bold; border-top: 2px solid #000;">
                <td colspan="2" class="text-center font-bold" style="font-size: 13px;">Total</td>
                <td class="text-right font-bold">{{ number_format($totalChips, 0) }}</td>
                <td class="text-right font-bold">{{ number_format($totalCash, 0) }}</td>
                <td class="text-right font-bold">{{ number_format($totalAmount, 0) }}</td>
                <td class="text-right font-bold">{{ number_format($totalBO, 0) }}</td>
                <td class="text-right font-bold">{{ number_format($totalTransfer, 0) }}</td>
                <td class="text-right font-bold">{{ number_format($totalRemaining, 0) }}</td>
                <td class="text-right font-bold">{{ number_format($totalRoundUp, 0) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-signatures">
        <div class="sig-block">
            <div class="sig-line"></div>
            <strong>Prepared By</strong>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <strong>Checked / Verified By</strong>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <strong>Approved By</strong>
        </div>
    </div>

</body>
</html>
