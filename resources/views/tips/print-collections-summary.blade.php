<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collections Summary - {{ ucfirst($report->month) }} {{ $report->year }}</title>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }

        @page {
            size: A4 landscape;
            margin: 12mm 15mm;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
            color: #0f172a;
            background: #fff;
            margin: 0;
            padding: 20px;
            width: 100%;
        }

        .no-print {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .btn-print {
            padding: 8px 18px;
            background: #0284c7;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background: #0369a1;
        }

        .btn-close {
            padding: 8px 16px;
            background: #64748b;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-close:hover {
            background: #475569;
        }

        .header-box {
            text-align: center;
            border: 2px solid #000;
            border-bottom: none;
            padding: 12px 16px;
            background-color: #fff;
        }

        .header-title {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-subtitle {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-top: 4px;
        }

        .meta-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 2px solid #000;
            border-bottom: none;
            padding: 6px 12px;
            font-size: 11.5px;
            background-color: #f8fafc;
            color: #334155;
        }

        table.collections-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 2px solid #000;
            font-size: 13px;
        }

        table.collections-table th,
        table.collections-table td {
            border: 1px solid #000;
            padding: 8px 12px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        table.collections-table thead th {
            background-color: #f1f5f9;
            font-weight: 700;
            font-size: 13px;
            border-bottom: 2px solid #000;
            letter-spacing: 0.3px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-num { font-variant-numeric: tabular-nums; }

        .footer-signatures {
            margin-top: 60px;
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

        .sig-label {
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
        }

        .print-footer {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 12mm 15mm;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                color: #000 !important;
            }
            .no-print {
                display: none !important;
            }
            table.collections-table {
                width: 100% !important;
                page-break-inside: auto;
            }
            table.collections-table tr {
                page-break-inside: avoid;
            }
            .footer-signatures {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">
            🖨️ Print Collections Summary
        </button>
        <button class="btn-close" onclick="window.close()">
            Close Window
        </button>
    </div>

    <div class="header-box">
        <h1 class="header-title">Department Collections Summary</h1>
        <div class="header-subtitle">{{ ucfirst($report->month) }} {{ $report->year }}</div>
    </div>

    <div class="meta-strip">
        <span><strong>Report Title:</strong> {{ $report->title }}</span>
        <span><strong>Cutoff Date:</strong> {{ \Carbon\Carbon::parse($report->cutoff_date)->format('d M, Y') }}</span>
        <span><strong>Period:</strong> {{ ucfirst($report->month) }} {{ $report->year }}</span>
    </div>

    <table class="collections-table">
        <thead>
            <tr>
                <th style="width: 12%;" class="text-center">S. No.</th>
                <th style="width: 58%;" class="text-left">Department</th>
                <th style="width: 30%;" class="text-right">Total Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td class="text-center font-num">{{ $index + 1 }}</td>
                    <td class="text-left font-bold">{{ $row['department'] }}</td>
                    <td class="text-right font-num font-bold">{{ number_format($row['total_amount'], 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="padding: 25px; color: #64748b;">
                        No collection data recorded for this tips report.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($rows) > 0)
            <tfoot>
                <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #000;">
                    <td colspan="2" class="text-center font-bold" style="font-size: 14px;">Total Collection</td>
                    <td class="text-right font-num font-bold" style="font-size: 14px;">Rs. {{ number_format($grandTotal, 0) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- <div class="footer-signatures">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Prepared By</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Checked By</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Approved By</div>
        </div>
    </div> --}}

    <div class="print-footer">
        Printed on: {{ now()->format('d M, Y h:i A') }}
    </div>

</body>
</html>