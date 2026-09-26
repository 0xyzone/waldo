<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report->title }} - {{ $department }} Payout Sheet</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11.5px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
        }

        .header-container {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }

        .header-subtitle {
            font-size: 14px;
            font-weight: 600;
            color: #111;
            margin: 0 0 6px 0;
        }

        .header-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 500;
            margin-top: 6px;
        }

        table.sheet-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        table.sheet-table th,
        table.sheet-table td {
            border: 1px solid #333;
            padding: 6px 7px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
        }

        table.sheet-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            text-transform: uppercase;
        }

        table.sheet-table td.text-center {
            text-align: center;
        }

        table.sheet-table td.text-right {
            text-align: right;
        }

        .sig-box {
            width: 100%;
            min-height: 38px;
            display: block;
        }

        .footer-signatures {
            margin-top: 36px;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }

        .sig-line-block {
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
            🖨️ Print Payout Sheet
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Close Window
        </button>
    </div>

    <div class="header-container">
        <h1 class="header-title">{{ $report->title }}</h1>
        <div class="header-subtitle">DEPARTMENT: {{ strtoupper($department) }} PAYOUT SHEET</div>
        <div class="header-meta">
            <div><strong>Tips Release Month|Year:</strong> {{ ucfirst($report->month) }} {{ $report->year }}</div>
            <div><strong>Cutoff Date:</strong> {{ \Carbon\Carbon::parse($report->cutoff_date)->format('d M, Y') }}</div>
            <div><strong>Total Department Payout:</strong> Rs. {{ number_format($items->sum('final_distribution_amount'), 0) }}</div>
        </div>
    </div>

    <table class="sheet-table">
        <thead>
            <tr>
                <th style="width: 32px;">S.N.</th>
                <th style="width: 60px;">Code</th>
                <th style="width: 170px;">Employee Name</th>
                <th style="width: 150px;">Designation</th>
                <th style="width: 48px;">Tips %</th>
                <th style="width: 72px;">Distribution</th>
                <th style="width: 150px;">Signature</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center"><strong>{{ $item->employee_code }}</strong></td>
                    <td style="font-weight: 500;">{{ $item->employee_name }}</td>
                    <td>{{ $item->designation ?? '-' }}</td>
                    <td class="text-center">{{ (int) round($item->tips_percentage) }}%</td>
                    <td class="text-right" style="font-weight: bold;">
                        {{ $item->is_blank || $item->final_distribution_amount === null ? '' : number_format($item->final_distribution_amount, 0) }}
                    </td>
                    <td class="text-center"><span class="sig-box"></span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 25px;">No employees found under this department.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f9f9f9; font-weight: bold;">
                <td colspan="4" class="text-right">TOTAL ({{ $items->count() }} Employees):</td>
                <td class="text-center">-</td>
                <td class="text-right">{{ number_format($items->sum('final_distribution_amount'), 0) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-signatures">
        <div class="sig-line-block">
            <div class="sig-line"></div>
            <strong>Prepared By</strong>
        </div>
        <div class="sig-line-block">
            <div class="sig-line"></div>
            <strong>Checked / Verified By</strong>
        </div>
        <div class="sig-line-block">
            <div class="sig-line"></div>
            <strong>Approved By</strong>
        </div>
    </div>

</body>
</html>
