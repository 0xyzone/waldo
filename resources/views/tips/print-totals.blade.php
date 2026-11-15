<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tips Amount Summary - {{ ucfirst($report->month) }} {{ $report->year }}</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        @page {
            size: A4 landscape;
            margin: 10mm 12mm;
        }

        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #0f172a;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .toolbar {
            max-width: 1060px;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.15s ease;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-primary {
            background: #0284c7;
            color: #fff;
        }

        .btn-secondary {
            background: #64748b;
            color: #fff;
        }

        .sheet {
            max-width: 1060px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 22px 26px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
        }

        .sheet-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .sheet-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 3px 0;
            color: #0f172a;
            letter-spacing: -0.2px;
        }

        .sheet-subtitle {
            font-size: 12px;
            color: #475569;
            margin: 0;
        }

        .sheet-meta {
            text-align: right;
            font-size: 11.5px;
            color: #475569;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1.65fr 1fr;
            gap: 20px;
            align-items: start;
        }

        .table-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
        }

        table.totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        table.totals-table thead th {
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.4px;
            padding: 10px 12px;
            border-bottom: 1.5px solid #cbd5e1;
            text-align: left;
        }

        table.totals-table tbody td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #0f172a;
        }

        table.totals-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        table.totals-table tbody tr:last-child td {
            border-bottom: none;
        }

        table.totals-table tfoot td {
            background: #f1f5f9;
            font-weight: 700;
            padding: 10px 12px;
            border-top: 1.5px solid #94a3b8;
            color: #0f172a;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .num {
            font-variant-numeric: tabular-nums;
            font-weight: 600;
        }

        .summary-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .summary-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 16px;
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-card.highlight {
            background: #eff6ff;
            border-color: #93c5fd;
        }

        .summary-label {
            font-size: 12.5px;
            font-weight: 600;
            color: #334155;
        }

        .summary-card.highlight .summary-label {
            color: #1e3a8a;
            font-weight: 700;
        }

        .summary-value {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            font-variant-numeric: tabular-nums;
        }

        .summary-card.highlight .summary-value {
            color: #1d4ed8;
            font-size: 18px;
        }

        .sheet-footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #64748b;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .sheet {
                max-width: 100%;
                border: none;
                border-radius: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>

    <div class="toolbar no-print">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Totals
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            Close Window
        </button>
    </div>

    <div class="sheet">
        <div class="sheet-header">
            <div>
                <h1 class="sheet-title">Tips Amount Summary</h1>
                <p class="sheet-subtitle">{{ ucfirst($report->month) }} {{ $report->year }} &bull; Department Mappings Breakdown</p>
            </div>
            <div class="sheet-meta">
                <div><strong>Period:</strong> {{ ucfirst($report->month) }} {{ $report->year }}</div>
                <div><strong>Printed on:</strong> {{ now()->format('d M, Y h:i A') }}</div>
            </div>
        </div>

        <div class="content-grid">
            <div class="table-card">
                <table class="totals-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;" class="text-center">S. No.</th>
                            <th style="width: 50%;">Department</th>
                            <th style="width: 15%;" class="text-center">Staff</th>
                            <th style="width: 25%;" class="text-right">To Distribute (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $index => $dept)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td style="font-weight: 600;">{{ $dept['name'] }}</td>
                                <td class="text-center num">{{ number_format($dept['count']) }}</td>
                                <td class="text-right num">{{ number_format($dept['amount'], 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right">Total</td>
                            <td class="text-center num">{{ number_format($totalStaffCount) }}</td>
                            <td class="text-right num">{{ number_format($totalToDistribute, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="summary-stack">
                <div class="summary-card">
                    <span class="summary-label">Actual Total Collection</span>
                    <span class="summary-value">Rs. {{ number_format($actualTotalCollection, 0) }}</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Left Outs</span>
                    <span class="summary-value">Rs. {{ number_format($adjustmentsLeftOuts, 0) }}</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Total to Distribute</span>
                    <span class="summary-value">Rs. {{ number_format($totalToDistribute, 0) }}</span>
                </div>
                <div class="summary-card highlight">
                    <span class="summary-label">Company Should Add</span>
                    <span class="summary-value">Rs. {{ number_format($companyShouldAdd, 0) }}</span>
                </div>
            </div>
        </div>

        <div class="sheet-footer">
            <span>Waldo Dynasty &bull; Tips Distribution Summary</span>
            <span>Printed on {{ now()->format('d M, Y h:i A') }}</span>
        </div>
    </div>

</body>

</html>
