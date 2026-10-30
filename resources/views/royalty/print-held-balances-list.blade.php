<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Royalty Bonus - Held Balances Sheet</title>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4 portrait;
            margin: 12mm 10mm;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 10.5px;
            color: #0f172a;
            background: #fff;
            margin: 0;
            padding: 10px;
            width: 100%;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .header-container {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }

        .header-title {
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
            color: #0f172a;
        }

        .header-subtitle {
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }

        .meta-box {
            margin: 10px 0 14px 0;
            padding: 8px 12px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #334155;
        }

        table.sheet-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10px;
            border: 1px solid #000;
        }

        table.sheet-table th, table.sheet-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
        }

        table.sheet-table th {
            background-color: #f1f5f9 !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            text-align: center;
            letter-spacing: 0.3px;
        }

        table.sheet-table tfoot td {
            font-weight: 700;
            background-color: #f1f5f9 !important;
            border-top: 1.5px solid #000;
            font-size: 10.5px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-num { font-variant-numeric: tabular-nums; }

        .print-actions {
            margin-bottom: 12px;
            text-align: right;
        }

        .btn-print {
            background: #0f172a;
            color: #fff;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-row-group !important;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button onclick="window.print()" class="btn-print">Print Sheet</button>
    </div>

    <div class="header-container">
        <h1 class="header-title">Royalty Bonus - Held Balances Sheet</h1>
        <div class="header-subtitle">Overview of Held & Deducted Royalty Bonus Balances by Employee</div>
    </div>

    <div class="meta-box">
        <div>
            <strong>Department Filter:</strong> {{ $department }}
        </div>
        <div>
            <strong>Total Employees:</strong> {{ $records->count() }}
        </div>
        <div>
            <strong>Date Generated:</strong> {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>

    @php
        $sumHeld = $records->sum('total_held');
        $sumReleased = $records->sum('total_released');
        $sumRemaining = $records->sum('remaining_balance');
    @endphp

    <table class="sheet-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th style="width: 75px;">Code</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Designation</th>
                <th style="width: 85px;" class="text-right">Total Held</th>
                <th style="width: 85px;" class="text-right">Released</th>
                <th style="width: 95px;" class="text-right">Remaining Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $rec)
                <tr>
                    <td class="text-center font-num">{{ $index + 1 }}</td>
                    <td class="font-num text-center">{{ $rec['employee']->employee_code }}</td>
                    <td><strong>{{ $rec['employee']->name }}</strong></td>
                    <td>{{ $rec['employee']->department?->name ?? 'N/A' }}</td>
                    <td>{{ $rec['employee']->designation?->name ?? 'N/A' }}</td>
                    <td class="text-right font-num">Rs. {{ number_format($rec['total_held'], 2) }}</td>
                    <td class="text-right font-num">Rs. {{ number_format($rec['total_released'], 2) }}</td>
                    <td class="text-right font-num"><strong>Rs. {{ number_format($rec['remaining_balance'], 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 14px;">No held bonus records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">Total:</td>
                <td class="text-right font-num">Rs. {{ number_format($sumHeld, 2) }}</td>
                <td class="text-right font-num">Rs. {{ number_format($sumReleased, 2) }}</td>
                <td class="text-right font-num">Rs. {{ number_format($sumRemaining, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

