<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report->title }} - ID Card Distribution Sheet</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
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
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }

        .header-subtitle {
            font-size: 13px;
            font-weight: 600;
            color: #111;
            margin: 0 0 6px 0;
        }

        .header-meta-box {
            margin-top: 8px;
            padding: 7px 12px;
            background-color: #fafafa;
            border: 1px solid #d4d4d8;
            border-radius: 4px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 0;
            font-size: 11.5px;
        }

        .meta-col {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .meta-label {
            font-weight: 600;
            color: #4b5563;
        }

        .meta-value {
            font-weight: bold;
            color: #111;
        }

        .meta-col.text-right {
            justify-content: flex-end;
        }

        .print-footer {
            margin-top: 18px;
            text-align: right;
            font-size: 10.5px;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 6px;
        }

        table.sheet-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.sheet-table th,
        table.sheet-table td {
            border: 1px solid #333;
            padding: 5px 6px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
        }

        table.sheet-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 10.5px;
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
            min-height: 28px;
            display: block;
        }

        .footer-signatures {
            margin-top: 32px;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }

        .sig-line-block {
            text-align: center;
            width: 170px;
        }

        .sig-line {
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }

        .badge-status {
            display: inline-block;
            padding: 1px 5px;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 3px;
            border: 1px solid #999;
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

    <div class="no-print" style="margin-bottom: 12px; display: flex; gap: 10px;">
        <button onclick="window.print()" style="padding: 7px 15px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 12px;">
            🖨️ Print ID Card Sheet
        </button>
        <button onclick="window.close()" style="padding: 7px 15px; background: #64748b; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
            Close Window
        </button>
    </div>

    <div class="header-container">
        <h1 class="header-title">{{ $report->title }}</h1>
        <div class="header-subtitle">IT DEPARTMENT — EMPLOYEE ID CARD DISTRIBUTION SHEET</div>
        <div class="header-meta-box">
            <div class="meta-row">
                <div class="meta-col">
                    <span class="meta-label">Batch Date:</span>
                    <span class="meta-value">{{ $report->batch_date?->format('d M, Y') }}</span>
                </div>
                <div class="meta-col text-right">
                    <span class="meta-label">Department Filter:</span>
                    <span class="meta-value">{{ strtoupper($department) }}</span>
                </div>
            </div>
            <div class="meta-row">
                <div class="meta-col">
                    <span class="meta-label">Total ID Cards Listed:</span>
                    <span class="meta-value">{{ $items->count() }} Cards</span>
                </div>
                <div class="meta-col text-right">
                    <span class="meta-label">Status Filter:</span>
                    <span class="meta-value">{{ $status }}</span>
                </div>
            </div>
        </div>
    </div>

    <table class="sheet-table">
        <thead>
            <tr>
                <th style="width: 32px;">S.N.</th>
                <th style="width: 65px;">Code</th>
                <th style="width: 170px;">Employee Name</th>
                <th style="width: 130px;">Department</th>
                <th style="width: 130px;">Designation</th>
                <th style="width: 75px;">Status</th>
                <th style="width: 130px;">Signature / Receiver</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center"><strong>{{ $item->employee_code }}</strong></td>
                    <td style="font-weight: 500;">{{ $item->employee_name }}</td>
                    <td>{{ $item->department ?? '-' }}</td>
                    <td>{{ $item->designation ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge-status">{{ strtoupper($item->status) }}</span>
                    </td>
                    <td class="text-center"><span class="sig-box"></span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">No employee ID cards found matching this filter.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f9f9f9; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL CARDS LISTED:</td>
                <td class="text-center">{{ $items->count() }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-signatures">
        <div class="sig-line-block">
            <div class="sig-line"></div>
            <div><strong>Prepared By (IT)</strong></div>
        </div>
        <div class="sig-line-block">
            <div class="sig-line"></div>
            <div><strong>Verified By (HR)</strong></div>
        </div>
        <div class="sig-line-block">
            <div class="sig-line"></div>
            <div><strong>Received By (Department / Staff)</strong></div>
        </div>
    </div>

    <div class="print-footer">
        Printed on: {{ now()->format('d M, Y h:i A') }} | Waldo HRM & IT Management System
    </div>

</body>
</html>
