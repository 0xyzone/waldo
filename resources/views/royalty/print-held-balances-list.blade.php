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
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            color: #000;
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
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }

        .header-subtitle {
            font-size: 12px;
            font-weight: 600;
            color: #222;
            margin: 0;
        }

        .meta-box {
            margin: 10px 0 14px 0;
            padding: 8px 12px;
            background-color: #fafafa;
            border: 1px solid #000;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
        }

        table.sheet-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10px;
        }

        table.sheet-table th, table.sheet-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
        }

        table.sheet-table th {
            background-color: #f0f0f0 !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            text-align: center;
        }

        table.sheet-table tfoot td {
            font-weight: bold;
            background-color: #f5f5f5 !important;
            border-top: 2px solid #000;
            font-size: 10.5px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

        .badge {
            display: inline-block;
            padding: 2px 4px;
            border: 1px solid #000;
            border-radius: 3px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .print-actions {
            margin-bottom: 12px;
            text-align: right;
        }

        .btn-print {
            background: #111827;
            color: #fff;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: bold;
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
                <th style="width: 80px;" class="text-right">Total Held</th>
                <th style="width: 80px;" class="text-right">Released</th>
                <th style="width: 85px;" class="text-right">Remaining Balance</th>
                <th style="width: 80px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $rec)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="font-mono text-center">{{ $rec['employee']->employee_code }}</td>
                    <td><strong>{{ $rec['employee']->name }}</strong></td>
                    <td>{{ $rec['employee']->department?->name ?? 'N/A' }}</td>
                    <td>{{ $rec['employee']->designation?->name ?? 'N/A' }}</td>
                    <td class="text-right font-mono">Rs. {{ number_format($rec['total_held'], 2) }}</td>
                    <td class="text-right font-mono">Rs. {{ number_format($rec['total_released'], 2) }}</td>
                    <td class="text-right font-mono"><strong>Rs. {{ number_format($rec['remaining_balance'], 2) }}</strong></td>
                    <td class="text-center">
                        @if($rec['remaining_balance'] <= 0)
                            <span class="badge" style="background: #e2e8f0;">Fully Released</span>
                        @elseif($rec['has_full_hold'])
                            <span class="badge" style="background: #fee2e2;">Payout Held</span>
                        @else
                            <span class="badge" style="background: #fef3c7;">Deductions</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 14px;">No held bonus records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">Total:</td>
                <td class="text-right font-mono">Rs. {{ number_format($sumHeld, 2) }}</td>
                <td class="text-right font-mono">Rs. {{ number_format($sumReleased, 2) }}</td>
                <td class="text-right font-mono">Rs. {{ number_format($sumRemaining, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px; display: flex; justify-content: space-between; font-size: 11px;">
        <div style="border-top: 1px solid #000; width: 180px; text-align: center; padding-top: 4px;">
            Prepared By (HR)
        </div>
        <div style="border-top: 1px solid #000; width: 180px; text-align: center; padding-top: 4px;">
            Checked By (Finance)
        </div>
        <div style="border-top: 1px solid #000; width: 180px; text-align: center; padding-top: 4px;">
            Approved By (Management)
        </div>
    </div>
</body>
</html>
