<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report->title }} - None Employee Payout Sheet</title>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11.5px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
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

        .header-selected-departments {
            margin: 3px 0 8px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            flex-wrap: wrap;
            font-size: 11px;
        }

        .selected-dept-label {
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.4px;
            margin-right: 2px;
        }

        .selected-dept-tag {
            display: inline-block;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 1px 7px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 10.5px;
            color: #0f172a;
        }

        .header-meta-box {
            margin-top: 10px;
            padding: 8px 14px;
            background-color: #fafafa;
            border: 1px solid #d4d4d8;
            border-radius: 4px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
            font-size: 12px;
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

        .text-red {
            color: #dc2626;
        }

        .font-payout {
            font-size: 13.5px;
            color: #047857;
        }

        .print-footer {
            margin-top: 18px;
            text-align: right;
            font-size: 11px;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 6px;
        }

        table.sheet-table {
            width: 100%;
            max-width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 24px;
            box-sizing: border-box;
        }

        table.sheet-table th,
        table.sheet-table td {
            border: 1px solid #333;
            padding: 5px 6px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
            box-sizing: border-box;
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

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
            table.sheet-table {
                width: 100% !important;
                max-width: 100% !important;
                page-break-inside: auto;
            }
            table.sheet-table tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; display: flex; gap: 10px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            🖨️ Print None Employee Sheet
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Close Window
        </button>
    </div>

    <div class="header-container">
        <h1 class="header-title">{{ $report->title }}</h1>
        <div class="header-subtitle">DEPARTMENT: NONE EMPLOYEE PAYOUT SHEET</div>
        @if(!empty($selectedDepartments))
            <div class="header-selected-departments">
                <span class="selected-dept-label">Selected Departments:</span>
                @foreach($selectedDepartments as $deptName)
                    <span class="selected-dept-tag">{{ $deptName }}</span>
                @endforeach
            </div>
        @endif
        <div class="header-meta-box">
            <div class="meta-row">
                <div class="meta-col">
                    <span class="meta-label">Tips Release Period:</span>
                    <span class="meta-value">{{ ucfirst($report->month) }} {{ $report->year }}</span>
                </div>
                <div class="meta-col text-right">
                    <span class="meta-label">Cutoff Date:</span>
                    <span class="meta-value">{{ \Carbon\Carbon::parse($report->cutoff_date)->format('d M, Y') }}</span>
                </div>
            </div>
            <div class="meta-row">
                <div class="meta-col">
                    <span class="meta-label">Valid Till Date:</span>
                    <span class="meta-value text-red">{{ $report->valid_till_date }}</span>
                </div>
                <div class="meta-col text-right">
                    <span class="meta-label">Total Department Payout:</span>
                    <span class="meta-value font-payout">Rs. {{ number_format($items->sum('final_distribution_amount'), 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <table class="sheet-table">
        <thead>
            <tr>
                <th style="width: 5%;">S.N.</th>
                <th style="width: 10%;">Code</th>
                <th style="width: 25%;">Name</th>
                <th style="width: 22%;">Designation</th>
                <th style="width: 9%;">Tips %</th>
                <th style="width: 12%;">Amount</th>
                <th style="width: 17%;">Signature</th>
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
                        {{ $item->final_distribution_amount === null ? '' : number_format($item->final_distribution_amount, 0) }}
                    </td>
                    <td class="text-center"><span class="sig-box"></span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 25px;">No active non-employees found for this report.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f9f9f9; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL ({{ $items->count() }} Non Employees):</td>
                <td class="text-right">{{ number_format($items->sum('final_distribution_amount'), 0) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="print-footer">
        Printed on: {{ now()->format('d M, Y h:i A') }}
    </div>

</body>
</html>
